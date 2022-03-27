<?php

namespace App\Updates\Commands;

use App\Services\TextString;
use Exception;

abstract class Command {

    public function getUserId($update) {
        return $update->getMessage()->getFrom()->getId();
    }

    public function getChatType($update) {
        return $update->getMessage()->getChat()->getType();
    }

    public function getChatId($update) {
        return $update->getMessage()->getChat()->getId();
    }

    public function getMessageId($update) {
        return $update->getMessage()->getMessageId();
    }

    public function getReplyToMessageId($update) {
        if($update->getMessage()->getReplyToMessage()) {
            return $update->getMessage()->getReplyToMessage()->getMessageId();
        }
        return null;
    }

    public function getReplyToMessageUserId($update) {
        if($update->getMessage()->getReplyToMessage()) {
            return $update->getMessage()->getReplyToMessage()->getFrom()->getId();
        }
        return null;
    }

    public function dieIfUnallowedChatType($update, $bot, array $allowed, string $errorString = null) {
        $chatType = $this->getChatType($update);
        if(!in_array($chatType, $allowed)) {
            if($errorString) {
                $chatId = $this->getChatId($update);
                $messageId = $this->getMessageId($update);
                $bot->sendMessage($chatId, TextString::get('error.'.$errorString), null, false, $messageId);
            }
            die;
        }
    }

}