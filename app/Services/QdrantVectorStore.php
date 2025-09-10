<?php

namespace App\Services;

use Qdrant\Qdrant;

use Qdrant\Models\PointsStruct;
use Qdrant\Models\PointStruct;
use Qdrant\Models\VectorStruct;

use Qdrant\Models\Request\SearchRequest;
use Qdrant\Models\Filter\Filter;
use Qdrant\Models\Filter\Condition\MatchString;

class QdrantVectorStore
{
    protected Qdrant $client;
    protected string $collection;
    protected string $vectorName;

    public function __construct()
    {
        $host        = trim((string) config('services.qdrant.host'));
        $apiKey      = (string) config('services.qdrant.key');
        $this->collection = (string) config('services.qdrant.collection', 'docs_embeddings');
        $this->vectorName = (string) config('services.qdrant.vector_name', 'content');

        $config = new \Qdrant\Config($host);
        $config->setApiKey($apiKey);

        $builder   = new \Qdrant\Http\Builder();
        $transport = $builder->build($config);

        $this->client = new \Qdrant\Qdrant($transport);
    }


    /**
     * @param dim dimension digi(มิติ) ต้องปรับตามแต่ละข่าย
     */
    public function ensureCollection(int $dim = 1536): void
    {
        try {
            $existsResp = $this->client->collections($this->collection)->exists();

            $exists = is_bool($existsResp) ? $existsResp : (bool)data_get($existsResp, 'result.exits', false);
            if ($exists === false) {
                return;
            }
        } catch (\Throwable $e) {
            //ถ้า Call exists
        }

        $create = new \Qdrant\Models\Request\CreateCollection();
        $create->addVector(
            new \Qdrant\Models\Request\VectorParams($dim, \Qdrant\Models\Request\VectorParams::DISTANCE_COSINE),
            $this->vectorName
        );

        try {
            $this->client->collections($this->collection)->create($create);
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                "Failed to create Qdrant collection '{$this->collection}' at host '" . config('services.qdrant.host') . "': "
                    . $e->getMessage(),
                previous: $e
            );
        }
    }

    public function upsert(array $points, bool $wait = true): void
    {
        $ps = new PointsStruct();

        foreach ($points as $p) {
            $ps->addPoint(
                new PointStruct(
                    $p['id'], // สามารถใช้ string id ได้
                    new VectorStruct($p['vector'], $this->vectorName),
                    [
                        'text' => $p['text'],
                        'doc_id' => $p['doc_id'],
                        'chunk_index' => $p['chunk_index'],
                    ]
                )
            );
        }

        $params = $wait ? ['wait' => 'true'] : [];
        $this->client->collections($this->collection)->points()->upsert($ps, $params);
    }


    public function search(array $queryVector, int $topK = 6, ?string $docId = null): array
    {
        $req = new SearchRequest(new VectorStruct($queryVector, $this->vectorName));
        $req->setLimit($topK)
            ->setParams([
                'hnsw_ef' => 128,
                'exact'   => false,
            ])
            ->setWithPayload(true);

        if ($docId) {
            $filter = (new Filter())->addMust(new MatchString('doc_id', $docId));
            $req->setFilter($filter);
        }

        $res = $this->client->collections($this->collection)->points()->search($req);

        $out = [];
        foreach (($res['result'] ?? []) as $item) {
            $payload = $item['payload'] ?? [];
            $out[] = [
                'text' => (string)($payload['text'] ?? ''),
                'score' => (float)($item['score'] ?? 0.0),
                'doc_id' => (string)($payload['doc_id'] ?? ''),
                'chunk_index' => (int)($payload['chunk_index'] ?? 0),
            ];
        }
        return $out;
    }
}
