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

        if($this->getReplyToMessage()) {
            preg_match("/^#feedback de .* (\d*)\.(\d*):/m", $this->getReplyToMessageText(), $matches);
            $replyUserId = $matches[1];
            $replyMessageId = $matches[2];
            $message = $this->getMessageText();
        } else {
            preg_match("/^\/devmsg (\d*) ([\w\W]*)/m", $this->getMessageText(), $matches);
            var_dump($matches);
            $replyUserId = $matches[1];
            $replyMessageId = null;
            $message = $matches[2];
        }
        
        $message = TextString::parseMarkdownV2($message);
        $message = $this->formatMessage($message);
        $this->tryToSendMessage($message, $replyUserId, $replyMessageId);

        ServerLog::log('DevMsg > finished');
    }

    private function formatMessage(string $message) {
        $msg = "\#dev {$this->getMessageId()}:\n{$message}";
        return str_ireplace("\n", "\n>", $msg)."\n".TextString::get('feedback.ask_reply');
    }

    private function tryToSendMessage($message, $replyUserId, $replyMessageId) {
        $userId = env('TG_MYID');
        try {
            ServerLog::log('trying to message '.$replyUserId, false);
            $this->bot->sendMessage($replyUserId, $message, 'MarkdownV2', false, $replyMessageId);
            $msg = $this->bot->sendMessage($userId, TextString::get('feedback.success'));
            $this->bot->call('setMessageReaction', [
                'chat_id' => $this->getUserId(),
                'message_id' => $this->getMessageId(),
                'reaction' => json_encode([
                    ['type' => 'emoji', 'emoji' => '👍']
                ])
            ]);
            ServerLog::log('v success');
            sleep(3);
            $this->bot->deleteMessage($userId, $msg->getMessageId());

        } catch(Exception $e) {
            ServerLog::log('x failed: '.$e->getMessage());
            $this->bot->sendMessage($userId, TextString::get('feedback.fail'));
            $this->bot->call('setMessageReaction', [
                'chat_id' => $this->getUserId(),
                'message_id' => $this->getMessageId(),
                'reaction' => json_encode([
                    ['type' => 'emoji', 'emoji' => '👎']
                ])
            ]);
        }
    }

}
