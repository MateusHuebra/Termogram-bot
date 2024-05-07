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

        if(!$this->update->getMessage()->getReplyToMessage()) {
            return;
        }

        ServerLog::log('Broadcast > started');
        $this->bot->sendMessage(env('TG_MYID'), TextString::get('broadcast.started'));

        $users = User::where('status', 'actived')->get();

        foreach ($users as $user) {
            $this->tryToSendMessage($user->id, $this->update->getMessage()->getReplyToMessage());
        }

        $result = TextString::get('broadcast.done', [
            'notified' => $this->usersNotified,
            'not_notified' => $this->usersNotNotified
        ]);

        ServerLog::log('Broadcast > ended: '.$result);
        $this->bot->sendMessage(env('TG_MYID'), $result);
        $this->setUsersStatusForErrors();
    }

    private function getMessage() {
        return str_ireplace('/broadcast ', '', $this->update->getMessage()->getText());
    }

    private function tryToSendMessage($userId, $message) {
        try {
            ServerLog::log('trying to message '.$userId, false);
            //$this->bot->sendMessage($userId, $message);
            $this->bot->copyMessage($userId, $message->getChat()->getId(), $message->getMessageId(), $message->getCaption()??null);
            $this->usersNotified++;
            ServerLog::log('v success');

        } catch(Exception $e) {
            ServerLog::log('x failed: '.$e->getMessage());
            if(!array_key_exists($e->getMessage(), $this->errors)) {
                $this->errors[$e->getMessage()] = [];
            }
            $this->errors[$e->getMessage()][] = $userId;
            $this->usersNotNotified++;
        }
    }

    private function setUsersStatusForErrors() {
        foreach ($this->errors as $error=>$usersId) {
            $this->bot->sendMessage(env('TG_MYID'), "$error\n\n".implode("\n", $usersId));
            $status = null;
            if($error==='Forbidden: bot was blocked by the user') {
                $status = 'blocked';
            } else if($error==='Forbidden: user is deactivated') {
                $status = 'deleted';
            } else {
                continue;
            }
            foreach ($usersId as $userId) {
                $user = User::find($userId);
                $user->status = $status;
                $user->save();
            }
        }
    }

}
