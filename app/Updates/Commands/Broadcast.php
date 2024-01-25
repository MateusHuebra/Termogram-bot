<?php

namespace App\Updates\Commands;

use App\Models\Attempt;
use App\Models\User;
use App\Services\TextString;
use TelegramBot\Api\BotApi;
use App\Services\ServerLog;


class Broadcast extends Command {

    private $usersNotified = 0;
    private $usersNotNotified = 0;

    public function run() {
        $this->dieIfUnallowedChatType(['private']);
        if($this->getUserId() != env('TG_MYID')) {
            return;
        }

        ServerLog::log('Broadcast > started');
        $this->bot->sendMessage(env('TG_MYID'), TextString::get('broadcast.started'));

        $users = User::all();
        $message = $this->getMessage();

        foreach ($users as $user) {
            $this->tryToSendMessage($user->id, $message);
        }

        $result = TextString::get('broadcast.done', [
            'notified' => $this->usersNotified,
            'not_notified' => $this->usersNotNotified
        ]);

        ServerLog::log('Broadcast > ended: '.$result);
        $this->bot->sendMessage(env('TG_MYID'), $result);
    }

    private function getMessage() {
        return str_replace('/broadcast ', '', $this->update->getMessage()->getText());
    }

    private function tryToSendMessage($userId, $message) {
        try {
            ServerLog::log('trying to message '.$userId);
            $this->bot->sendMessage($userId, $message);
            $this->usersNotified++;
            ServerLog::log('- success');
        } catch (Exception $e) {
            ServerLog::log('- failed');
            $this->bot->sendMessage(env('TG_MYID'), "error on trying to broadcast to {$userId}: {$e->getMessage()}");
            $this->usersNotNotified++;
        }
    }

}
