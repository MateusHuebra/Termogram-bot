<?php

namespace App\Updates\Commands;

use App\Services\TextString;
use App\Services\ServerLog;

class Unban extends Command
{

    public function run()
    {
        $this->dieIfUnallowedChatType(['private']);
        $this->dieIfNotAdmin();
        ServerLog::log('Unban > started');

        $user = Ban::getUserFromMessage($this->getUserIdFromMessage());
        $successMessage = TextString::get('ban.success_unban', [
            'id' => $user->id,
            'score' => $user->score
        ]);
        $user->is_banned = false;
        $user->save();

        $this->bot->sendMessage(env('TG_MYID'), $successMessage, 'MarkdownV2');
        ServerLog::log('Unban > end');
    }

    private function getUserIdFromMessage()
    {
        return str_ireplace('/unban ', '', $this->getMessageText());
    }
}
