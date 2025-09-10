<?php

namespace App\Services;

use Prism\Prism\Prism;
use Prism\Prism\Enums\Provider;

class EmbeddingService
{
    public function embedTexts(array $texts, string $model): array
    {
        $builder = Prism::embeddings()->using(Provider::OpenAI, $model);
        foreach ($texts as $t) {
            $builder->fromInput($t);
        }
        $response = $builder->asEmbeddings();
        return array_map(fn($e) => $e->embedding, $response->embeddings);
    }

    public function embedOne(string $text, string $model): array
    {
        $response = Prism::embeddings()
            ->using(Provider::OpenAI, $model)
            ->fromInput($text)
            ->asEmbeddings();

        return $response->embeddings[0]->embedding;
    }
}
