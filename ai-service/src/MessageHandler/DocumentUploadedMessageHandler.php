<?php

namespace App\MessageHandler;

use App\Message\DocumentUploadedMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Psr\Log\LoggerInterface;

#[AsMessageHandler]
class DocumentUploadedMessageHandler
{
    public function __construct(private readonly LoggerInterface $logger){}

    public function __invoke(DocumentUploadedMessage $message): void
    {
        $docId = $message->getDocumentId();
        $filePath = $message->getFilePath();
        $userId = $message->getUploadedByUserId();

        // 🧠 Заглушка: имитация обработки документа
        $this->logger->info(sprintf(
            "[AI SERVICE] Received message: Document #%d uploaded by user #%d (%s)\n",
            $docId,
            $userId,
            $filePath
        ));
    }
}
