<?php

namespace App\MessageHandler;

use App\Message\DocumentProcessedMessage;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class DocumentProcessedMessageHandler
{
    public function __construct(private EntityManagerInterface $em, private readonly LoggerInterface $logger)
    {
    }

    public function __invoke(DocumentProcessedMessage $message): void
    {
        $docId = $message->getDocumentId();
        $summary = $message->getSummary();

        $doc = $this->em->getRepository('App\Entity\Document')->find($docId);
        if (!$doc) {
            $this->logger->info("[DOC SERVICE] Document not found: $docId\n");
            return;
        }

        // Обновляем статус документа
        $doc->setStatus('processed');
        $doc->setSummary($summary);
        $this->em->flush();

        $this->logger->info(sprintf(
            "[DOC SERVICE] Document #%d processed: %s\n",
            $docId, $summary
        ));
    }
}
