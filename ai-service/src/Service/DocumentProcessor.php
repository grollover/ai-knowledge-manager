<?php

namespace App\Service;

use Smalot\PdfParser\Parser as PdfParser;
//use PhpOffice\PhpWord\IOFactory;

class DocumentProcessor
{
    public function extractText(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $text = '';

        switch ($ext) {
            case 'pdf':
                $parser = new PdfParser();
                $pdf = $parser->parseFile($path);
                $text = $pdf->getText();
                break;

//            case 'docx':
//            case 'doc':
//                $phpWord = IOFactory::load($path);
//                foreach ($phpWord->getSections() as $section) {
//                    foreach ($section->getElements() as $element) {
//                        if (method_exists($element, 'getText')) {
//                            $text .= $element->getText() . "\n";
//                        }
//                    }
//                }
//                break;

            case 'txt':
                $text = file_get_contents($path);
                break;

            default:
                throw new \RuntimeException("Unsupported file type: $ext");
        }

        return trim($text);
    }

    public function chunkText(string $text, int $maxLength = 500): array
    {
        $chunks = [];
        $paragraphs = preg_split('/\n+/', $text);
        $current = '';

        foreach ($paragraphs as $p) {
            if (strlen($current) + strlen($p) > $maxLength) {
                $chunks[] = trim($current);
                $current = $p;
            } else {
                $current .= ' ' . $p;
            }
        }

        if (!empty(trim($current))) {
            $chunks[] = trim($current);
        }

        return $chunks;
    }
}
