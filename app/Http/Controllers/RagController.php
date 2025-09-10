<?php

namespace App\Http\Controllers;

use App\Services\RagService;
use App\Services\TextExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RagController extends Controller
{
    public function __construct(
        protected TextExtractor $extractor,
        protected RagService $rag,
    ) {}

    public function upload(Request $request)
    {
        $file = $request->file('file');

        $path = $file->store('rag_uploads', 'local');

        $text = $this->extractor->extractFromStorage($path, $file->getMimeType(), 'local');

        $result = $this->rag->indexDocument(Str::uuid()->toString(), $text);

        return response()->json([
            'message' => 'Indexed successfully',
            'doc_id' => $result['doc_id'],
            'chunks' => $result['chunks'],
        ]);
    }
}
