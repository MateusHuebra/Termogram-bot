<?php

namespace App\Updates\Commands;

use App\Models\User;
use App\Services\TextString;
use App\Services\ServerLog;
use Exception;

class Broadcast extends Command {

    private $usersNotified = 0;
    private $usersNotNotified = 0;
    private $errors = [];

    public function run() {
        $this->dieIfUnallowedChatType(['private']);
        if($this->getUserId() != env('TG_MYID')) {
            ServerLog::log('Broadcast > no permission');
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
        foreach ($this->errors as $index=>$error) {
            $this->bot->sendMessage(env('TG_MYID'), "$index\n\n$error");
        }
    }

    private function getMessage() {
        return str_ireplace('/broadcast ', '', $this->update->getMessage()->getText());
    }

    private function tryToSendMessage($userId, $message) {
        try {
            ServerLog::log('trying to message '.$userId, false);
            $this->bot->sendMessage($userId, $message);
            $this->usersNotified++;
            ServerLog::log('v success');

        } catch(Exception $e) {
            ServerLog::log('x failed: '.$e->getMessage());
            if(!array_key_exists($e->getMessage(), $this->errors)) {
                $this->errors[$e->getMessage()] = '';
            }
            $this->errors[$e->getMessage()].= "{$userId}\n";
            $this->usersNotNotified++;
        }
    }

}
