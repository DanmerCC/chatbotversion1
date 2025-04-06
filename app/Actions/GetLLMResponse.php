<?php

namespace App\Actions;

use App\Models\Message;
use Illuminate\Support\Facades\Log;

class GetLLMResponse
{
    use \Lorisleiva\Actions\Concerns\AsAction;

    /**
     * Get a response from the LLM.
     *
     * @param string $sessionId
     * @param string $userMessage
     * @return array
     */
    public function handle(string $sessionId, string $userMessage): array
    {
        $llm = new \App\Services\LocalLLMAdapter();

        // Retrieve message history
        $history = Message::whereSessionId($sessionId)->get();
        $history = $history->map(function ($item) {
            return [
                'role' => $item->type,
                'content' => $item->message,
            ];
        })->toArray();

        Log::info('History:', $history);
        Log::info('User Message:', [$userMessage]);

        // Get response from LLM
        $response = $llm->chat($userMessage, $history);

        Log::info('LLM Response:', $response);

        return $response;
    }
}
