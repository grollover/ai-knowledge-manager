<?php

namespace App\Controller;

use App\Service\EmbeddingService;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Routing\Annotation\Route;

class QueryController extends AbstractController
{
    public function __construct(
        private EmbeddingService $embeddings,
        private Connection $db,
        private HttpClientInterface $http
    ) {}

    #[Route('/api/query', name: 'api_query', methods: ['POST'])]
    public function query(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $question = trim($data['question'] ?? '');
        $userId = $data['userId'] ?? null;

        if (!$question) {
            return $this->json(['error' => 'Missing question'], 400);
        }

        //Получаем embedding для вопроса
        $questionEmbedding = $this->embeddings->embed($question);

        $vectorLiteral = '[' . implode(',', $questionEmbedding) . ']';

        $sql = "
    SELECT chunk_text, embedding <-> '{$vectorLiteral}' AS distance
    FROM chunk
    ORDER BY embedding <-> '{$vectorLiteral}'
    LIMIT 5
";

        $results = $this->db->executeQuery($sql)->fetchAllAssociative();

        $context = implode("\n---\n", array_column($results, 'chunk_text'));

        //Формируем промпт
        $prompt = <<<PROMPT
        You are an assistant that answers questions based on the following context.

        Context:
        $context

        Question:
        $question

        Answer clearly and concisely:
        PROMPT;

        //Отправляем в LM Studio /v1/chat/completions
        try {
            $response = $this->http->request('POST', 'http://host.docker.internal:1234/v1/chat/completions', [
                'json' => [
                    'model' => 'openai/gpt-oss-20b',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.7,
                ],
                'timeout' => 60,
            ]);

            $data = $response->toArray();
            $answer = $data['choices'][0]['message']['content'] ?? '[No answer returned]';

            return $this->json([
                'question' => $question,
                'answer' => $answer,
                'context' => $results,
            ]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
