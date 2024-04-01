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
            $message = TextString::parseMarkdownV2($this->getMessage());
            $message = $this->formatMessage($message);
            $this->tryToSendMessage($message);
        } else {
            $this->bot->sendMessage($this->getUserId(), TextString::get('feedback.no_message'));
        }

        ServerLog::log('Feedback > finished');
    }

    protected function getMessage() {
        return str_ireplace('/feedback ', '', $this->getMessageText());
    }

    protected function formatMessage(string $message) {
        $msg = "\#feedback de [{$this->getUserName()}](tg://user?id={$this->getUserId()}) {$this->getUserId()}\.{$this->getMessageId()}:\n{$message}";
        return str_ireplace("\n", "\n>", $msg);
    }

    protected function tryToSendMessage($message, $replyMessageId = null) {
        $userId = env('TG_MYID');
        try {
            ServerLog::log('trying to message '.$userId, false);
            $this->bot->sendMessage($userId, $message, 'MarkdownV2', false, $replyMessageId);
            $msg = $this->bot->sendMessage($this->getUserId(), TextString::get('feedback.success'));
            $this->bot->setMessageReaction($this->getUserId(), $this->getMessageId(), '👍');
            ServerLog::log('v success');
            sleep(3);
            $this->bot->deleteMessage($this->getUserId(), $msg->getMessageId());

        } catch(Exception $e) {
            ServerLog::log('x failed: '.$e->getMessage());
            $this->bot->sendMessage($this->getUserId(), TextString::get('feedback.fail'));
            $this->bot->setMessageReaction($this->getUserId(), $this->getMessageId(), '👎');
        }
    }

}
