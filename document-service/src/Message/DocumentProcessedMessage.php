<?php

namespace App\Message;

class DocumentProcessedMessage
{
    public function __construct(
        private int $documentId,
        private int $uploadedByUserId,
        private string $summary
    ) {}

    public function getDocumentId(): int
    {
        return $this->documentId;
    }

    public function getUploadedByUserId(): int
    {
        return $this->uploadedByUserId;
    }

    public function getSummary(): string
    {
        return $this->summary;
    }
}
