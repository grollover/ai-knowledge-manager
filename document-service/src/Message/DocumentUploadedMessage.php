<?php

namespace App\Message;

class DocumentUploadedMessage
{
    public function __construct(
        private int $documentId,
        private string $filePath,
        private int $uploadedByUserId
    ) {}

    public function getDocumentId(): int
    {
        return $this->documentId;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getUploadedByUserId(): int
    {
        return $this->uploadedByUserId;
    }
}
