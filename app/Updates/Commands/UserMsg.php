<?php

namespace App\Updates\Commands;

use App\Services\TextString;
use App\Services\ServerLog;
use Exception;

class UserMsg extends Feedback {

    public function run() {
        $this->dieIfUnallowedChatType(['private']);
        ServerLog::log('UserMsg > started');

        preg_match("/^#dev (\d*):/m", $this->getReplyToMessageText(), $matches);
        $replyMessageId = $matches[1];
        $message = TextString::parseMarkdownV2($this->getMessage());
        $message = $this->formatMessage($message);
        $this->tryToSendMessage($message, $replyMessageId);

        ServerLog::log('UserMsg > finished');
    }

}
