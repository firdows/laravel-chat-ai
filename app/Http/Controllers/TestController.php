<?php

namespace App\Http\Controllers;

use App\Models\History;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Prism\Prism\Enums\ChunkType;
use Prism\Prism\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Throwable;

class TestController extends Controller
{
    public function index1()
    {
        // return Inertia("test/Index", ['text' => '123']);
        ini_set('max_execution_time', '-1');
        // return
        $text = "";
        try {
            // $response = Prism::text()
            //     ->using(Provider::Ollama, 'gemma3:1b')
            //     ->withPrompt('1+1 เท่ากับเท่าไหร่ครับ')
            //     // ->withClientOptions(['timeout' => 30])
            //     ->asText();

            // echo $response->text;
            // $response = Prism::text()
            //     ->using(Provider::OpenAI, 'gpt-5-nano')
            //     ->withSystemPrompt("คุณเป็นคนไทย")
            //     ->withPrompt('ช่วยอธิบเกี่ยวกับประเทศไทย')
            //     ->asText();

            $response = Prism::text()
                // ->using(Provider::OpenAI, 'gpt-5-nano')
                // ->using(Provider::Ollama, 'gemma3:1b')
                ->using(Provider::Ollama, 'qwen2.5:0.5b')
                // ->withSystemPrompt("คุณเป็นคนไทย")
                ->withPrompt('ช่วยอธิบเกี่ยวกับประเทศไทย')
                // ->usingTemperature(0.5)
                // ->withClientOptions(['timeout' => 30])
                // ->withClientRetry(3, 100) //retry 3 , sleep 100
                ->asText();

            $text = $response->text;
            // echo $text;
            // exit();
        } catch (PrismException $e) {
            Log::error('Text generation failed:', ['error' => $e->getMessage()]);
            echo  $e->getMessage();
        } catch (Throwable $e) {
            Log::error('Generic error:', ['error' => $e->getMessage()]);
            echo  $e->getMessage();
        }
        return Inertia("test/Index", ['text' => $text]);
    }

    public function index()
    {
        $userId = Auth::user()->id;
        $historyAll = History::where('user_id', $userId)->get();
        return Inertia("test/IndexNew", ['historyAll' => $historyAll]);
    }

    public function chat(Request $request)
    {
        // $messagePayload = (array) $request->input('messages', []);
        $messagesPayload = (array) $request->input('messages', []);
        $lastUser = collect($messagesPayload)->reverse()->firstWhere('role', 'user') ?? [];

        $userText = $lastUser['content'] ?? collect(Arr::get($lastUser, 'parts', []))->where('type', 'text')->pluck('text')->implode('\n');


        if ($userText == null) {
            // dd($messagesPayload);
            return [];
        }
        $userId = Auth::user()->id;
        History::create([
            'user_id' => $userId,
            'role' => 'user',
            'parts' => [ChunkType::Text->value => $userText]
        ]);
        $historyMessage = History::where('user_id', $userId)
            ->where('role', 'user')
            ->where('created_at', '>=', Carbon::now('-02:00'))->orderBy('created_at')->get()
            ->map(fn(History $history): UserMessage|AssistantMessage => match ($history->role) {
                'user' => new UserMessage(content: $history->parts['text'] ?? ''),
                'assistant' => new AssistantMessage(content: $history->parts['text'] ?? ''),
            })->toArray();


        // dd($historyMessage);


        try {
            // $response = Prism::text()
            //     ->using(Provider::Ollama, 'qwen2.5:0.5b')
            //     ->withSystemPrompt("คุณเป็นคนไทย")
            //     ->withPrompt('ช่วยอธิบเกี่ยวกับประเทศไทย')
            //     ->asText();

            // return ['messages' => [$response->text]];
            // return response()->json(['reply' => $response->text]);


            /** @disregard [OPTIONAL CODE] [OPTIONAL DESCRIPTION] */
            return response()->stream(function () use ($userText, $historyMessage, $userId) {
                // return response()->eventStream(function () use ($userText) {
                // $stream = Prism::text()
                //     ->using(Provider::OpenAI, 'gpt-4.1-nano')
                //     ->usingTemperature(0.2) // ถ้า gpt-4 จะปรับ temperature ได้ตามปกติ
                //     ->withSystemPrompt('คุณเป็นผู้ช่วยด้านการเขียนโค้ด Laravel Framework ใช้ภาษาทางการ กระชับ ชัดเจน')
                //     // ->withPrompt($userText)
                //     // ->withPrompt($historyMessage)
                //     ->withMessages($historyMessage)
                //     ->withClientRetry(3, 100)
                //     ->withClientOptions(['timeout' => 30])
                //     ->withProviderOptions([
                //         // 'language' => 'th', // ISO-639-1 code (optional) เฉพาะ OpenAI
                //     ])->asStream();

                $stream = Prism::text()
                    ->using(Provider::OpenAI, 'gpt-4.1-nano')
                    ->usingTemperature(0.2) // ถ้า gpt-4 จะปรับ temperature ได้ตามปกติ
                    ->withSystemPrompt('คุณเป็นผู้ช่วยด้านการเขียนโค้ด Laravel Framework ใช้ภาษาทางการ กระชับ ชัดเจน')
                    ->withMessages($historyMessage)
                    ->withClientRetry(3, 100)
                    ->withClientOptions(['timeout' => 30])
                    ->withProviderOptions([
                        // 'language' => 'th', // ISO-639-1 code (optional) เฉพาะ OpenAI
                    ])
                    ->asStream();


                $parts = [];
                foreach ($stream as $response) {
                    $key = $response->chunkType->value;
                    $parts[$key] ??= '';
                    $parts[$key] .= $response->text;

                    yield $response->text;
                }

                if ($parts !== []) {
                    History::create([
                        'user_id' => $userId,
                        'role' => 'assistant',
                        'parts' => $parts,
                    ]);
                }
            });
        } catch (PrismException $e) {
            Log::error('Text generation failed:', ['error' => $e->getMessage()]);
        } catch (Throwable $e) {
            Log::error('Generic error:', ['error' => $e->getMessage()]);
        }
    }


    public function store(Request $request)
    {
        try {
            $textInput = (string) $request->input('text');

            /** @disregard [OPTIONAL CODE] [OPTIONAL DESCRIPTION] */
            return response()->eventStream(function () use ($textInput) { // response()->eventStream ใช้กับ 12.x ถูกแล้ว
                $stream = Prism::text()
                    ->using(Provider::OpenAI, 'gpt-4.1-nano')
                    ->usingTemperature(1) // ถ้า gpt-5 จะปรับ temperature ไม่ได้ (ไม่มีผล) default คือ 1 ถ้า gpt-4 ปรับได้ตามปกติ (0.0-2.0)
                    ->withSystemPrompt('คุณเป็นผู้ช่วยด้านการเขียนโค้ด Laravel Framework ใช้ภาษาทางการ กระชับ ชัดเจน')
                    ->withPrompt($textInput)
                    ->withClientRetry(3, 100)
                    ->withClientOptions(['timeout' => 30])
                    ->withProviderOptions([
                        'reasoning' => ['effort' => 'low'], // default เป็น medium
                        // 'language' => 'th',           // ISO-639-1 code (optional)
                    ])
                    ->asStream();

                foreach ($stream as $response) {
                    yield $response->text;
                }
            });
        } catch (PrismException $e) {
            Log::error('Text generation failed:', ['error' => $e->getMessage()]);
        } catch (Throwable $e) {
            Log::error('Generic error:', ['error' => $e->getMessage()]);
        }
    }


    public function store1(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);
        $response = Prism::text()
            // ->using(Provider::OpenAI, 'gpt-5-nano')
            // ->using(Provider::Ollama, 'gemma3:1b')
            ->using(Provider::Ollama, 'qwen2.5:0.5b')
            // ->withSystemPrompt("คุณเป็นคนไทย")
            ->withPrompt($validated['title'])
            // ->usingTemperature(0.5)
            // ->withClientOptions(['timeout' => 30])
            // ->withClientRetry(3, 100) //retry 3 , sleep 100
            ->asText();

        return $response->text;

        // try {
        //     return response()->eventStream(function () {
        //         $stream = Prism::text()
        //             ->using(Provider::OpenAI, 'gpt-5-nano')
        //             ->withSystemPrompt("คุณเป็นคนไทย")
        //             ->withPrompt('ช่วยอธิบเกี่ยวกับประเทศไทย')
        //             ->asStream();

        //         foreach ($stream as $response) {
        //             yield $response->text;
        //         }
        //     });
        // } catch (PrismException $e) {
        //     Log::error('Text generation failed:', ['error' => $e->getMessage()]);
        // } catch (Throwable $e) {
        //     Log::error('Generic error:', ['error' => $e->getMessage()]);
        // }


        // return redirect()->route('test.index');
    }
}
