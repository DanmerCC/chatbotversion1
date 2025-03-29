<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

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

        sleep(rand(1, 10));

        return response()->json([
            'message' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
        ]);
    }
}
