<?php

namespace App\Updates\Commands;

use App\Services\TextString;
use App\Services\ServerLog;
use Exception;

class DevMsg extends Command {

    public function run() {
        $this->dieIfUnallowedChatType(['private']);
        $this->dieIfNotAdmin();
        ServerLog::log('DevMsg > started');

        preg_match("/^#feedback:.*\((\d*):(\d*)\)/m", $this->getReplyToMessageText(), $matches);
        $replyUserId = $matches[1];
        $replyMessageId = $matches[2];
        $message = $this->parseMarkdownV2($this->getMessage());
        $message = $this->formatMessage($message);
        $this->tryToSendMessage($message, $replyUserId, $replyMessageId);

        ServerLog::log('DevMsg > finished');
    }

    private function getMessage() {
        return $this->update->getMessage()->getText();
    }

    private function formatMessage(string $message) {
        $msg = "\#dev {$this->getMessageId()}:\n{$message}\n".TextString::get('feedback.ask_reply');
        return str_ireplace("\n", "\n>", $msg);
    }

    private function tryToSendMessage($message, $replyUserId, $replyMessageId) {
        $userId = env('TG_MYID');
        try {
            ServerLog::log('trying to message '.$replyUserId, false);
            $this->bot->sendMessage($replyUserId, $message, 'MarkdownV2', false, $replyMessageId);
            $this->bot->sendMessage($userId, TextString::get('feedback.success'));
            ServerLog::log('v success');

        } catch(Exception $e) {
            ServerLog::log('x failed: '.$e->getMessage());
            $this->bot->sendMessage($userId, TextString::get('feedback.fail'));
        }
    }

}
