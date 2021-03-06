<?php

namespace App\Updates\Commands;

use App\Services\TextString;
use App\Updates\Update;
use Exception;

abstract class Command extends Update {

    protected function dieIfUnallowedChatType(array $allowed, string $errorString = null, $doReply = true) {
        if(in_array($this->getChatType(), $allowed)) {
            return;
        }
        if ($errorString) {
            if ($doReply) {
                $this->bot->sendMessage($this->getChatId(), TextString::get('error.'.$errorString), null, false, $this->getMessageId());
            } else {
                $this->bot->sendMessage($this->getChatId(), TextString::get('error.'.$errorString));
            }
        }
        die;
    }

}
