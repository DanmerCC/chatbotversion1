<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        $message = new Message();
        $message->session_id = $session_id;
        $message->type = 'user';
        $message->message = $request->input('message');
        $message->save();

        $llm = new \App\Services\LocalLLMAdapter();

        $history = Message::whereSessionId($session_id)->get();
        $history = $history->map(function ($item) {
            return [
                'role' => $item->type,
                'content' => $item->message,
            ];
        })->toArray();

        Log::info('History:');
        Log::info($history);

        Log::info('Message:');
        Log::info($message->message);
        Log::info('Session ID:');
        Log::info($session_id);



        $response = $llm->chat($message->message, $history);
        Log::info('Response');
        Log::info($response);
        if ($response['status'] === 'error') {
            return response()->json([
                'status' => 'error',
                'message' => $response['message'],
            ], 500);
        }
        //add message to db
        $message = new Message();
        $message->session_id = $session_id;
        $message->type = 'assistant';
        $message->message = $response['data']['output'];
        $message->save();

        return response()->json([
            'status' => 'success',
            'message' => $response['data']['output']
        ]);
    }
}
