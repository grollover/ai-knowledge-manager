<?php

namespace App\Service;

class EmbeddingService
{
    public function embed(string $text): array
    {
        // временная заглушка
        $length = 1536;
        $vector = array_map(fn($i) => (float)(ord($text[$i % strlen($text)]) % 100) / 100, range(0, $length-1));
        return $vector;
    }
}
