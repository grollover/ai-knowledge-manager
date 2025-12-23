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

    public function chunkText(string $text, int $maxLength = 500, $encoding = 'UTF-8'): array
    {
        $chunks = [];
        $textLength = mb_strlen($text, $encoding);

        for ($i = 0; $i < $textLength; $i += $maxLength) {
            $chunk = mb_substr($text, $i, $maxLength, $encoding);

            // Если это не последний чанк и следующий символ не пробел
            if ($i + $maxLength < $textLength && mb_substr($text, $i + $maxLength, 1, $encoding) !== ' ') {
                // Ищем последний пробел в чанке
                $lastSpace = mb_strrpos($chunk, ' ', 0, $encoding);

                if ($lastSpace !== false) {
                    $chunk = mb_substr($chunk, 0, $lastSpace, $encoding);
                    $i -= ($maxLength - $lastSpace - 1);
                }
            }

            $chunks[] = $chunk;
        }

        return $chunks;
    }
}
