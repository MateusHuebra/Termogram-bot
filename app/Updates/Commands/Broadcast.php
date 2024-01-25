<?php

namespace App\Updates\Commands;

use App\Models\Attempt;
use App\Models\Game;
use App\Services\TextString;
use TelegramBot\Api\BotApi;

class Broadcast extends Command {

    public function run() {
        $this->dieIfUnallowedChatType(['private']);
        if($this->getUserId() != env('TG_MYID')) {
            return;
        }

        $this->bot->sendMessage(env('TG_MYID'), TextString::get('broadcast.started'));

        $users = User::all();
        $usersNotified = 0;
        $usersNotNotified = 0;
        $message = $this->getMessage();

        foreach ($users as $user) {
            $this->tryToSendMessage($user->id, $message);
        }

        $result = TextString::get('broadcast.done', [
            'notified' => $usersNotified,
            'not_notified' => $usersNotNotified
        ]);
        $this->bot->sendMessage(env('TG_MYID'), $result);
    }

    private function getMessage() {
        return str_replace('/broadcast ', '', $this->update->getMessage()->getText());
    }

    private function tryToSendMessage($userId, $message) {
        try {
            $this->bot->sendMessage($userId, $message);
            $this->usersNotified++;
        } catch (Exception $e) {
            $this->bot->sendMessage(env('TG_MYID'), "error on trying to broadcast to {$userId}: {$e->getMessage()}");
            $this->usersNotNotified++;
        }
    }

}
