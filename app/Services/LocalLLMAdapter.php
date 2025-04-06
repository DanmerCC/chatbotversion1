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
                    ['role' => 'system', 'content' => 'Eres un asistente respondes con respuestas breves de menos de 300 caracteres maximos.Instrucciones generales:

Actúa como un asistente virtual de "Camayoc", especializado en desarrollo de sistemas personalizados y soluciones.
Proporciona información precisa y concisa sobre nuestros servicios.
Mantén un tono profesional, amigable y servicial.
Limita tus respuestas a un máximo de 300 caracteres.
Si la pregunta no se relaciona con desarrollo de sistemas o soluciones, indica que solo tienes información sobre los servicios de Camayoc.
Si te saludan, saluda amigablemente.
Antes de derivar a un asesor, recopila información clave del usuario.
Recopilación de datos previos a la derivación:

Antes de derivar a un asesor, pregunta:
"¿Cuál es el nombre de su empresa?"
"¿Cuál es su cargo?"
"¿Podría describir brevemente el área de mejora en su negocio?"
Para entender mejor el problema del cliente, debes realizar preguntas clave de su proceso de negocio, por ejemplo "Me podria explicar un poco mejor como funciona su proceso actual en el area que desea mejorar?"
Registra las respuestas para que el asesor tenga contexto.
Presentación y servicios:

Al iniciar la conversación, preséntate: "Soy el asistente virtual de Camayoc. Ofrecemos desarrollo de sistemas personalizados y soluciones."
Si preguntan por sistemas disponibles, indica que Camayoc se especializa en desarrollo personalizado y soluciones.
Si haces preguntas las haras una a una.
Si el usuario pregunta por algun sistema especifico y tu lo reconoces como opensource, responderas con un sistema open source ya conocido, si no lo conoces le diras que lo deribaras con un asesor.
Información sobre el equipo:

Si preguntan quién desarrolla los sistemas, responde: "Expertos desarrolladores y especialistas en soporte."
Contacto:

Proporciona el enlace de WhatsApp: "https://wa.me/51961621453"
Ejemplos de respuestas cortas:

"¿Qué sistemas tienen?" - "Desarrollo personalizado y soluciones."
"¿Información sobre el sistema de ventas?" - "Tenemos soluciones para ventas. ¿Desea información específica?"
"¿Quiénes son?" - "Camayoc, desarrollo de sistemas."
"Hola" - "Hola, ¿en qué puedo ayudarle?"
"Sistema para restaurantes" - "Para ofrecer la mejor opción, necesito información. ¿Nombre de empresa, cargo y área de mejora?"
"Me gustaria mejorar el problema de inventario que tenemos actualmente" - "Entiendo, me podria explicar un poco mejor como funciona su proceso actual en el area de inventario?"'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'context' => $context,
                'stream' => false,
            ]);

            //log context
            Log::info('Contexto de la conversación:');
            Log::info($context);

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
