<?php

namespace App\Services;

class TextChunker
{
    public function split(string $text, int $chunkSize = 800, int $overlap = 200): array
    {
        $text = preg_replace('/\s+/', ' ', $text ?? '');
        $len = mb_strlen($text);
        $chunks = [];

        for ($start = 0; $start < $len; $start += ($chunkSize - $overlap)) {
            $chunks[] = mb_substr($text, $start, $chunkSize);
        }
        return array_values(array_filter($chunks, fn($c) => mb_strlen(trim($c)) > 0));
    }
}
