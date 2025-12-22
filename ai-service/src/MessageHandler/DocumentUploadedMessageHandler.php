<?php

namespace App\MessageHandler;

use App\Entity\Chunk;
use App\Message\DocumentProcessedMessage;
use App\Message\DocumentUploadedMessage;
use App\Service\DocumentProcessor;
use App\Service\EmbeddingService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsMessageHandler]
class DocumentUploadedMessageHandler
{
    public function __construct(
        private readonly DocumentProcessor   $processor,
        private readonly LoggerInterface     $logger,
        private readonly MessageBusInterface $bus,
        private readonly HttpClientInterface $http,
        private readonly string              $documentServiceBaseUrl,
        private EmbeddingService             $embedder,
        private EntityManagerInterface       $em,
    )
    {
    }

    public function __invoke(DocumentUploadedMessage $message): void
    {
        $documentId = $message->getDocumentId();
        $userId = $message->getUploadedByUserId();

        try {
            $localPath = $this->downloadDocument($message->getFilePath());
            $chunks = $this->processDocument($localPath);

            foreach ($chunks as $chunkText) {
                $embedding = $this->embedder->embed($chunkText);
                $chunk = new Chunk($documentId, $chunkText, $embedding);
                $this->em->persist($chunk);
            }

            $this->em->flush();

            $summary = sprintf("Saved %d chunks for document #%d", count($chunks), $documentId);

            $this->logger->info('[AI SERVICE] Document processed', [
                'document_id' => $documentId,
                'chunks' => count($chunks),
            ]);

            $this->bus->dispatch(new DocumentProcessedMessage($documentId, $userId, $summary));
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('[AI SERVICE] Failed to download document', [
                'error' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('[AI SERVICE] Unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private function downloadDocument(string $remotePath): string
    {
        $url = rtrim($this->documentServiceBaseUrl, '/') . '/uploads/' . ltrim($remotePath, '/');
        $localPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . basename($remotePath);

        $response = $this->http->request('GET', $url);
        file_put_contents($localPath, $response->getContent());

        return $localPath;
    }

    private function processDocument(string $path): array
    {
        try {
            $text = $this->processor->extractText($path);
            return $this->processor->chunkText($text);
        } finally {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }
}
