<?php

namespace App\Updates;

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update as UpdateType;

abstract class Update {

    protected $update;
    protected $bot;

    public function __construct(UpdateType $update, BotApi $bot)
    {
        $this->update = $update;
        $this->bot = $bot;
    }

    protected function sendMessage(string $text, $replyMarkup = null, bool $forceReply = false) {
        if(!$forceReply && $this->isChatType('private')) {
            $this->bot->sendMessage($this->getUserId(), $text, null, false, null, $replyMarkup);
        } else {
            $this->bot->sendMessage($this->getChatId(), $text, null, false, $this->getMessageId(), $replyMarkup);
        }
    }

    protected function getUserId() {
        if(!isset($this->userId)) {
            $this->userId = $this->update->getMessage()->getFrom()->getId();
        }
        return $this->userId;
    }

    protected function getChatType() {
        if(!isset($this->chatType)) {
            $this->chatType = $this->update->getMessage()->getChat()->getType();
        }
        return $this->chatType;
    }

    protected function isChatType(string $type) {
        return $this->getChatType()==$type;
    }

    protected function getChatId() {
        if(!isset($this->chatId)) {
            $this->chatId = $this->update->getMessage()->getChat()->getId();
        }
        return $this->chatId;
    }

    protected function getMessageId() {
        if(!isset($this->messageId)) {
            $this->messageId = $this->update->getMessage()->getMessageId();
        }
        return $this->messageId;
    }

    protected function getReplyToMessage() {
        if(!isset($this->replyToMessage)) {
            $this->replyToMessage = $this->update->getMessage()->getReplyToMessage();
        }
        return $this->replyToMessage;
    }

    protected function getReplyToMessageId() {
        if(!isset($this->replyToMessageId)) {
            if($this->getReplyToMessage()) {
                $this->replyToMessageId = $this->update->getMessage()->getReplyToMessage()->getMessageId();
            } else {
                $this->replyToMessageId = null;
            }
        }
        return $this->replyToMessageId;
    }

    protected function getReplyToMessageUserId() {
        if(!isset($this->replyToMessageUserId)) {
            if($this->getReplyToMessage()) {
                $this->replyToMessageUserId = $this->update->getMessage()->getReplyToMessage()->getFrom()->getId();
            } else {
                $this->replyToMessageUserId = null;
            }
        }
        return $this->replyToMessageUserId;
    }
    
}