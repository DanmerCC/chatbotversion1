<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LocalLLMAdapter
{
    protected string $baseUrl = 'http://172.28.160.1:11434';
    protected string $model = 'llama3.1:latest';

    public function chat(string $prompt, array $context = []): array
    {
        try {

            //request without timeout
            $response = Http::timeout(0)->post("{$this->baseUrl}/api/chat", [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'Eres un asistente respondes con respuestas breves de menos de 300 maximos.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'context' => $context,
                'stream' => false,
            ]);

            return $this->formatResponse($response, 'Respuesta generada correctamente.');
        } catch (\Throwable $e) {
            return $this->formatError('Error inesperado durante la conversación.', $e);
        }
    }

    public function generate(string $prompt): array
    {
        try {
            $response = Http::post("{$this->baseUrl}/api/generate", [
                'model' => $this->model,
                'prompt' => $prompt,
                'stream' => false,
            ]);

            return $this->formatResponse($response, 'Generación completada correctamente.');
        } catch (\Throwable $e) {
            return $this->formatError('Error inesperado durante la generación.', $e);
        }
    }

    protected function formatResponse($response, string $successMessage): array
    {
        if ($response->successful()) {
            $raw = $response->json();

            return [
                'status' => 'success',
                'message' => $successMessage,
                'data' => [
                    'output' => $raw['message']['content'] ?? null,
                    'model_used' => $raw['model'] ?? $this->model,
                    'total_duration_ms' => isset($raw['total_duration']) ? $raw['total_duration'] / 1e6 : null,
                ],
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Error al obtener una respuesta del modelo.',
            'data' => $response->json(),
        ];
    }


    protected function formatError(string $message, \Throwable $e): array
    {
        Log::error($message . ' ' . $e->getMessage());

        return [
            'status' => 'error',
            'message' => $message,
            'data' => [
                'exception' => $e->getMessage(),
            ],
        ];
    }
}
