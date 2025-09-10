<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser as PdfParser;

class TextExtractor
{
    public function extractFromStorage(string $relativePath, string $mime, string $disk = 'local'): string
    {
        // ถ้าเป็น PDF ต้องการ absolute path จาก Storage (เฉพาะ local driver)
        if ($mime === 'application/pdf' || str_ends_with(strtolower($relativePath), '.pdf')) {
            $absolute = Storage::disk($disk)->path($relativePath); // e.g. /.../storage/app/rag_uploads/xxx.pdf
            $parser = new PdfParser();
            $pdf = $parser->parseFile($absolute);
            return trim($pdf->getText());
        }

        return trim((string) Storage::disk($disk)->get($relativePath));
    }
}
