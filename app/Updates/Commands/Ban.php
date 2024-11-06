<?php

namespace App\Updates\Commands;

use App\Services\TextString;
use App\Services\ServerLog;
use App\Models\User;

class Ban extends Command {

    public function run() {
        $this->dieIfUnallowedChatType(['private']);
        $this->dieIfNotAdmin();
        ServerLog::log('Ban > started');

        $user = self::getUserFromMessage($this->getUserIdFromMessage());
        $successMessage = TextString::get('ban.success', [
            'id' => $user->id,
            'score' => $user->score
        ]);
        $user->is_banned = true;
        $user->score = 0;
        $user->save();

        $this->bot->sendMessage(env('TG_MYID'), $successMessage, 'MarkdownV2');
        $this->bot->sendMessage($user->id, TextString::get('ban.banned'));
        ServerLog::log('Ban > end');
    }

    private function getUserIdFromMessage() {
        return str_ireplace('/ban ', '', $this->getMessageText());
    }

    static public function getUserFromMessage($userId) {
        if(str_contains($userId, '@')) {
            $username = substr($userId, 1);
            return User::where('username', $username)->first();
        } else {
            return User::find($userId);
        }
    }

}