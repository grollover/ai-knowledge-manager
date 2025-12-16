<?php

namespace App\MessageHandler;

use App\Message\DocumentProcessedMessage;
use App\Message\DocumentUploadedMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class DocumentUploadedMessageHandler
{
    public function __construct(private readonly LoggerInterface $logger, private MessageBusInterface $bus)
    {
    }

    public function __invoke(DocumentUploadedMessage $message): void
    {
        $docId = $message->getDocumentId();
        $filePath = $message->getFilePath();
        $userId = $message->getUploadedByUserId();


        sleep(30);
        // 🧠 Заглушка: имитация обработки документа
        $this->logger->info(sprintf(
            "[AI SERVICE] Received message: Document #%d uploaded by user #%d (%s)\n",
            $docId,
            $userId,
            $filePath
        ));

        // После "обработки" публикуем событие обратно
        $summary = sprintf("Document %d processed successfully!", $docId);

        $this->bus->dispatch(new DocumentProcessedMessage(
            $docId,
            $userId,
            $summary
        ));

        $this->logger->info(sprintf(
            "[AI SERVICE] Sent DocumentProcessedMessage for #%d\n",
            $docId
        ));

    }
}
