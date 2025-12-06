<?php

namespace App\Controller;

use App\Entity\Document;
use App\Repository\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DocumentController extends AbstractController
{
    #[Route('/api/documents', name: 'api_documents_list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function list(DocumentRepository $repo): JsonResponse
    {
        $user = $this->getUser();
        $documents = $repo->findBy(['uploadedBy' => $user]);

        $data = array_map(fn($doc) => [
            'id' => $doc->getId(),
            'title' => $doc->getTitle(),
            'filePath' => $doc->getFilePath(),
            'createdAt' => $doc->getCreatedAt()->format('Y-m-d H:i:s'),
        ], $documents);

        return $this->json($data);
    }

    #[Route('/api/documents', methods: ['POST'])]
    public function upload(
        Request $request,
        EntityManagerInterface $em,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        /** @var string $authHeader */
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader) {
            return $this->json(['error' => 'Missing token'], 401);
        }

        $token = str_replace('Bearer ', '', $authHeader);
        $data = $jwtManager->parse($token);
        if (!$data) {
            return $this->json(['error' => 'Invalid token'], 401);
        }

        $userId = $data['id'] ?? $data['sub'] ?? null;
        if (!$userId) {
            return $this->json(['error' => 'User ID not found in token'], 400);
        }

        $file = $request->files->get('file');
        $title = $request->request->get('title');

        if (!$file || !$title) {
            return $this->json(['error' => 'Missing title or file'], 400);
        }

        $uploadsDir = $this->getParameter('uploads_directory');
        $filename = uniqid().'.'.$file->guessExtension();
        $file->move($uploadsDir, $filename);

        $doc = new Document();
        $doc->setTitle($title);
        $doc->setFilePath('/uploads/'.$filename);
        $doc->setUploadedByUserId($userId);

        $em->persist($doc);
        $em->flush();

        return $this->json(['message' => 'Document uploaded']);
    }
}
