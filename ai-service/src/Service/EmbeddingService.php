<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class EmbeddingService
{

    private string $apiUrl;
    private string $modelName;

    public function __construct(private HttpClientInterface $client)
    {
        // LM Studio работает как OpenAI API
        $this->apiUrl = 'http://host.docker.internal:1234/v1/embeddings';
        $this->modelName = 'frida'; // выбери свою модель
    }

    public function embed(string $text): array
    {
        try {
            $response = $this->client->request('POST', $this->apiUrl, [
                'json' => [
                    'model' => $this->modelName,
                    'input' => $text,
                ],
                'timeout' => 30,
            ]);

            $data = $response->toArray();

            if (!isset($data['data'][0]['embedding'])) {
                throw new \RuntimeException('No embedding returned from LM Studio');
            }

            return $data['data'][0]['embedding'];
        } catch (\Throwable $e) {
            file_put_contents('php://stderr', "[AI SERVICE] Embedding error: {$e->getMessage()}\n");
            // возвращаем нулевой вектор, чтобы пайплайн не ломался
            return array_fill(0, 1536, 0.0);
        }
    }

//    public function embed(string $text): array
//    {
//        // временная заглушка
//        $length = 1536;
//        $vector = array_map(fn($i) => (float)(ord($text[$i % strlen($text)]) % 100) / 100, range(0, $length-1));
//        return $vector;
//    }
}
