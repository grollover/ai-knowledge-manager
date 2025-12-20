<?php

namespace App\MessageHandler;

use App\Message\DocumentProcessedMessage;
use App\Message\DocumentUploadedMessage;
use App\Service\DocumentProcessor;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
class DocumentUploadedMessageHandler
{
    public function __construct(
        private DocumentProcessor        $processor,
        private readonly LoggerInterface $logger,
        private MessageBusInterface      $bus,
        private HttpClientInterface      $http,
        private string                   $documentServiceBaseUrl,
    )
    {
    }

    public function __invoke(DocumentUploadedMessage $message): void
    {
        $url = $this->documentServiceBaseUrl . '/uploads/' . $message->getFilePath(); // скачиваем через HTTP
        $localPath = sys_get_temp_dir() . '/' . basename($message->getFilePath());
        $docId = $message->getDocumentId();
        $userId = $message->getUploadedByUserId();

        try {
            $response = $this->http->request('GET', $url);
            file_put_contents($localPath, $response->getContent());

            $text = $this->processor->extractText($localPath);
            $chunks = $this->processor->chunkText($text);

            $this->logger->info(sprintf(
                "[AI SERVICE] Extracted %d chunks from document #%d\n",
                count($chunks), $docId
            ));

            // После "обработки" публикуем событие обратно
            $summary = sprintf("Document contains %d text chunks ready for embedding.", count($chunks));

            $this->bus->dispatch(new DocumentProcessedMessage(
                $docId,
                $userId,
                $summary
            ));

        } catch (\Throwable $e) {
            $this->logger->error("[AI SERVICE] ERROR: {$e->getMessage()}\n");
        } finally {
            @unlink($localPath);
        }

    }
}
