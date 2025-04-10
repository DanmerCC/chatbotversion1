<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Actions\GetLLMResponse;
use App\Actions\SaveMessage;

class ChatController extends Controller
{
    public function index(Request $request)
    {

        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $offset = ($page - 1) * $limit;

        $messages= Message::whereSessionId($request->session_id)->paginate($limit, ['*'], 'page', $page);

        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:300'
        ]);

        $session_id = session()->getId();

        if (!$session_id) {
            throw new \Exception('Session ID not found');
        }

        // Save user message
        SaveMessage::run($session_id, 'user', $request->input('message'));

        // Get response from LLM
        $response = GetLLMResponse::run($session_id, $request->input('message'));

        if ($response['status'] === 'error') {
            return response()->json([
                'status' => 'error',
                'message' => $response['message'],
            ], 500);
        }

        // Save assistant message
        SaveMessage::run($session_id, 'assistant', $response['data']['output']);

        return response()->json([
            'coockies' => $request->cookies,
            'status' => 'success',
            'message' => $response['data']['output']
        ]);
    }
}
