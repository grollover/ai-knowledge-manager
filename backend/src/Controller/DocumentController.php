<?php

namespace App\Controller;

use App\Entity\Document;
use App\Repository\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
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

    #[Route('/api/documents', name: 'api_documents_upload', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function upload(Request $request, EntityManagerInterface $em): JsonResponse
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('file');
        $title = $request->request->get('title');

        if (!$file || !$title) {
            return $this->json(['error' => 'Не указаны title или file'], 400);
        }

        $uploadsDir = $this->getParameter('uploads_directory');
        $newFilename = uniqid().'.'.$file->guessExtension();

        try {
            $file->move($uploadsDir, $newFilename);
        } catch (FileException $e) {
            return $this->json(['error' => 'Ошибка сохранения файла'], 500);
        }

        $document = new Document();
        $document->setTitle($title);
        $document->setFilePath('/uploads/'.$newFilename);
        $document->setUploadedBy($this->getUser());
        $document->setCreatedAt(new \DateTimeImmutable());
        $document->setUpdatedAt(new \DateTimeImmutable());

        $em->persist($document);
        $em->flush();

        return $this->json(['message' => 'Файл загружен']);
    }
}
