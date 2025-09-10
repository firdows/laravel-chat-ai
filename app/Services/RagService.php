<?php

namespace App\Services;

use Ramsey\Uuid\Uuid;

class RagService
{
    /**
     * Create a new class instance.
     */
    public function __construct(protected EmbeddingService $embedding, protected QdrantVectorStore $store,) {}


    /**
     * @param $docId
     */
    public function indexDocument(string $docId, string $text)
    {
        $this->store->ensureCollection(1536);

        $chunker = new TextChunker();
        $chunks = $chunker->split($text, 500, 50);

        $vectors = $this->embedding->embedTexts($chunks, config('services.rag.embed_model'));

        $points = [];
        foreach ($chunks as $i => $chunk) {
            $pointId = Uuid::uuid5(Uuid::NAMESPACE_URL, "{$docId}:{$i}")->toString();
            $points[] = [
                'id' => $pointId,
                'vector' => $vectors[$i],
                'text' => $chunk,
                'doc_id' => $docId,
                'chunk_index' => $i,
            ];
        }

        $this->store->upsert($points, true);

        return ['doc_id' => $docId, 'chunks' => count($chunks)];
    }
}
