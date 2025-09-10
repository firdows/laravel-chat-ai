<?php

namespace App\Http\Controllers;

use App\Models\History;
use App\Models\User;
use App\Services\RagService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Prism\Prism\Enums\ChunkType;
use Prism\Prism\Prism;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Enums\ToolChoice;
use Prism\Prism\Exceptions\PrismException;
use Prism\Prism\Facades\Tool;
use Prism\Prism\ValueObjects\Messages\AssistantMessage;
use Prism\Prism\ValueObjects\Messages\UserMessage;
use Throwable;

class ChatController extends Controller
{
    public function __construct(protected RagService $rag,) {}

    public function index()
    {
        $userId = Auth::user()->id;
        $historyAll = History::where('user_id', $userId)->get();
        return Inertia("chat/IndexNew", ['historyAll' => $historyAll]);
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
            $tools = $this->makeTools();

            /** @disregard [OPTIONAL CODE] [OPTIONAL DESCRIPTION] */
            return response()->stream(function () use ($userText, $historyMessage, $userId, $tools) {
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
                    // ->using(Provider::Ollama, 'qwen2.5:0.5b')
                    ->usingTemperature(0.2) // ถ้า gpt-4 จะปรับ temperature ได้ตามปกติ
                    // ->withSystemPrompt('คุณเป็นผู้ช่วยด้านการเขียนโค้ด Laravel Framework ใช้ภาษาทางการ กระชับ ชัดเจน')
                    ->withSystemPrompt('คุณเป็นผู้ช่วยส่วนตัวด้านการเขียนโปรแกรม ผู้เชี่ยวชาญด้านสภาพอากาศ เป็นผู้จัดการฝ่าย HR ตอบคำถามนโยบายบริษัท ใช้ภาษาทางการ กระชับ ชัดเจน')
                    ->withMessages($historyMessage)
                    ->withMaxSteps(4)
                    ->withTools($tools)
                    ->withToolChoice(ToolChoice::Auto)
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


    /**
     * Tools & function Calling
     *
     * https://prismphp.com/core-concepts/tools-function-calling.html#tool-concept-overview
     */
    private function makeTools()
    {
        $weatherTool = Tool::as('weather')
            ->for('Get current weather conditions')
            ->withStringParameter('city', 'The city to get weather for')
            ->using(function (string $city): string {
                // Your weather API logic here
                return "The weather in {$city} is sunny and 72°F.";
            });

        // $searchTool = Tool::as('user')
        //     ->for('ค้นหาข้อมูลผู้ใช้งานในระบบด้วยการใช้รหัสผู้ใช้งาน')
        //     ->withStringParameter('user_id', 'คิวรีสำหรับค้นหาผู้ใช้ด้วยรหัส')
        //     ->using(function (string $user_id): string {
        //         // Your search implementation
        //         $user = User::find($user_id);
        //         return $user;
        //     });

        /**
         * Search database
         */
        $searchTool = Tool::as('user')
            ->for('ค้นหาข้อมูลผู้ใช้งานในระบบด้วยการใช้รหัสผู้ใช้งาน')
            ->withStringParameter('user_id', 'คิวรีสำหรับค้นหาผู้ใช้ด้วยรหัส')
            ->using(function (string $user_id): string {
                // Your search implementation
                $user = User::where($user_id)->select('id', 'name', 'email', 'created_at')->first();
                return json_encode([
                    'ok' => true,
                    'data' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'created_at' => $user->created_at->toISOString(),
                    ]
                ], JSON_UNESCAPED_UNICODE);
            });

        /**
         *  Call restfull API
         * https://api.codingthailand.com/api/course
         */

        $apiTool = Tool::as('course')
            ->for('ค้นหาข้อมูลคอร์สเรียนจากเว็บ codingthailand.com')
            ->withStringParameter('course', 'คิวรีสำหรับค้นหาผู้ใช้ด้วยรหัส')
            ->using(function (string $course): string {
                // Your search implementation

                $response = Http::timeout(30)->retry(2, 100)->get('https://api.codingthailand.com/api/course');

                if ($response->failed()) {
                    return json_encode([
                        'error' => true,
                        'message' => 'ไม่พบข้อมูล'
                    ], JSON_UNESCAPED_UNICODE);
                }

                $rows = collect(data_get($response->json(), 'data'), [])->map(fn($c) => [
                    'course_title' => (string) data_get($c, 'title', ''),
                    'course_detail' => (string) data_get($c, 'detail', ''),
                    'course_view_count' => (string) data_get($c, 'view', 0),
                    'course_created_at' => (string) data_get($c, 'date', ''),
                ])->take(100)->values();

                return $rows->toJson(JSON_UNESCAPED_UNICODE);
            });

        //Rag Tools

        $ragTool = Tool::as('ragHR')
            ->for('ค้นหาข้อมูลนโยบายการลางาน')
            ->withStringParameter('question', 'คำถามจากผู้ใช้เพื่อตอบเกี่ยวกับ HR')
            ->using(function (string $question): string {
                // Your search implementation
                $result = $this->rag->askRag($question);
                return json_encode($result, JSON_UNESCAPED_UNICODE);
            });



        return [$weatherTool, $searchTool, $apiTool, $ragTool];
    }
}
