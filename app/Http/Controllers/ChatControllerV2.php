<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Session;
use Illuminate\Http\Request;
use App\Actions\SaveMessage;
use App\Actions\GetLLMResponse;

class ChatControllerV2 extends Controller
{
    public function index(Request $request)
    {

        $coockie = $request->cookie('session_v2_id');
        if (!$coockie) {
            throw new \Exception('Session ID not found');
        }

        $session = Session::find($coockie);
        if (!$session) {
            throw new \Exception('Session ID not found');
        }

        $messages = Message::whereSessionId($session->id)->orderBy('created_at', 'desc')->paginate(10, ['*'], 'page', $request->input('page', 1));
        return response()->json($messages);
    }

    public function getSessionId(Request $request)
    {
        $session = Session::create();
        $session->save();
        return response()->json(['session_id' => $session->id]);
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
            'status' => 'success',
            'data' => [
                'output' => $response['data']['output'],
            ],
        ]);
    }

}
