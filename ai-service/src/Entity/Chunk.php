<?php

namespace App\Entity;

use App\Repository\ChunkRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChunkRepository::class)]
class Chunk
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(type: Types::INTEGER)]
    private int $documentId;

    #[ORM\Column(type: Types::TEXT)]
    private string $chunkText;

    #[ORM\Column(type: 'vector', options: ["dimensions" => 1536])]
    private mixed $embedding = [];

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct(int $documentId, string $chunkText, array $embedding)
    {
        $this->documentId = $documentId;
        $this->chunkText = $chunkText;
        $this->embedding = $embedding;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDocumentId(): ?int
    {
        return $this->documentId;
    }

    public function setDocumentId(int $documentId): static
    {
        $this->documentId = $documentId;

        return $this;
    }

    public function getChunkText(): ?string
    {
        return $this->chunkText;
    }

    public function setChunkText(string $chunkText): static
    {
        $this->chunkText = $chunkText;

        return $this;
    }

    public function getEmbedding(): mixed
    {
        return $this->embedding;
    }

    public function setEmbedding(mixed $embedding): static
    {
        $this->embedding = $embedding;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }
}
