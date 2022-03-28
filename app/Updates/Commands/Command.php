<?php

namespace App\Updates\Commands;

use App\Services\TextString;
use App\Updates\Update;
use Exception;

abstract class Command extends Update {

    protected function dieIfUnallowedChatType(array $allowed, string $errorString = null) {
        if(in_array($this->getChatType(), $allowed)) {
            return;
        }
        if($errorString) {
            $this->bot->sendMessage($this->getChatId(), TextString::get('error.'.$errorString), null, false, $this->getMessageId());
        }
        die;
}

}