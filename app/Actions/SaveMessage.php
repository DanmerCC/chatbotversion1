<?php

namespace App\Actions;

use App\Models\Message;

class SaveMessage
{

    use \Lorisleiva\Actions\Concerns\AsAction;
    /**
     * Save a message to the database.
     *
     * @param string $sessionId
     * @param string $type
     * @param string $content
     * @return Message
     */
    public function handle(string $sessionId, string $type, string $content): Message
    {
        $message = new Message();
        $message->session_id = $sessionId;
        $message->type = $type;
        $message->message = $content;
        $message->save();

        return $message;
    }
}
