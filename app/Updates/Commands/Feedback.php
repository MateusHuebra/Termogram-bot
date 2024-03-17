<?php

namespace App\Updates\Commands;

use App\Services\TextString;
use App\Services\ServerLog;
use Exception;

class Feedback extends Command {

    public function run() {
        $this->dieIfUnallowedChatType(['private']);
        ServerLog::log('Feedback > started');

        if(strlen($this->update->getMessage()->getText()) > 10) {
            $message = $this->parseMarkdownV2($this->getMessage());
            $message = $this->formatMessage($message);
            $this->tryToSendMessage($message);
        } else {
            $this->bot->sendMessage($this->getUserId(), TextString::get('feedback.no_message'));
        }

        ServerLog::log('Feedback > finished');
    }

    private function getMessage() {
        return str_ireplace('/feedback ', '', $this->update->getMessage()->getText());
    }

    private function formatMessage(string $message) {
        return "\#feedback: [{$this->getUserName()}](tg://user?id={$this->getUserId()}) \({$this->getUserId()}:{$this->getMessageId()}\):\n```\n{$message}```";
    }

    private function tryToSendMessage($message, $replyMessageId = null) {
        $userId = env('TG_MYID');
        try {
            ServerLog::log('trying to message '.$userId, false);
            $this->bot->sendMessage($userId, $message, 'MarkdownV2', false, $replyMessageId);
            $this->bot->sendMessage($this->getUserId(), TextString::get('feedback.success'));
            ServerLog::log('v success');

        } catch(Exception $e) {
            ServerLog::log('x failed: '.$e->getMessage());
            $this->bot->sendMessage($this->getUserId(), TextString::get('feedback.fail'));
        }
    }

}
