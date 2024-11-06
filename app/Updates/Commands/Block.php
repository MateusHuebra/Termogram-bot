<?php

namespace App\Updates\Commands;

use App\Services\TextString;
use App\Services\ServerLog;
use App\Models\User;

class Block extends Command {

    public function run() {
        $this->dieIfUnallowedChatType(['private']);
        $this->dieIfNotAdmin();
        ServerLog::log('Block > started');

        $user = Ban::getUserFromMessage($this->getUserIdFromMessage());
        $successMessage = TextString::get('ban.success_block', [
            'id' => $user->id,
            'score' => $user->score
        ]);
        $user->is_banned = true;
        $user->is_blocked = true;
        $user->score = 0;
        $user->save();

        $this->bot->sendMessage(env('TG_MYID'), $successMessage, 'MarkdownV2');
        $this->bot->sendMessage($user->id, TextString::get('ban.blocked'));
        ServerLog::log('Block > end');
    }

    private function getUserIdFromMessage() {
        return str_ireplace('/block ', '', $this->getMessageText());
    }

}