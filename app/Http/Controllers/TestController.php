<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Prism\Prism\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Exceptions\PrismException;
use Throwable;

class TestController extends Controller
{
    public function index()
    {

        // ini_set('max_execution_time', '-1');
        // return 

        try {
            $response = Prism::text()
                ->using(Provider::Ollama, 'gemma3:1b')
                ->withPrompt('1+1 เท่ากับเท่าไหร่ครับ')
                // ->withClientOptions(['timeout' => 30])
                ->asText();

            echo $response->text;
        } catch (PrismException $e) {
            Log::error('Text generation failed:', ['error' => $e->getMessage()]);
        } catch (Throwable $e) {
            Log::error('Generic error:', ['error' => $e->getMessage()]);
        }
    }
}
