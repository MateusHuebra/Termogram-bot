<?php

namespace App\Updates;

use App\Services\ServerLog;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update as UpdateType;
use App\Models\TelegramUpdate;
use Exception;

abstract class Update {

    const RESERVED_CHARACTERS = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
    const ESCAPED_CHARACTERS = ['\_', '\*', '\[', '\]', '\(', '\)', '\~', '\`', '\>', '\#', '\+', '\-', '\=', '\|', '\{', '\}', '\.', '\!'];

    protected $update;
    protected $bot;

    public function __construct(UpdateType $update, BotApi $bot, bool $fakeUpdate = false)
    {
        $this->updateId = $update->getUpdateId();
        if($this->updateId === 'fake') {
            $fakeUpdate = true;
        }
        if($fakeUpdate === false) {
            if(TelegramUpdate::where('id', $this->updateId)->exists() === true) {
                ServerLog::log("Update id {$this->updateId} already received. ending...");
                die();
            }
            $tu = new TelegramUpdate();
            $tu->id = $this->updateId;
            $tu->save();
        }

        $this->update = $update;
        $this->bot = $bot;
    }

    public function getUpdateId() {
        return $this->updateId;
    }

    public function parseMarkdownV2($string) {
        return str_replace(self::RESERVED_CHARACTERS, self::ESCAPED_CHARACTERS, $string);
    }

    public function getMessageId() {
        if(!isset($this->messageId)) {
            $this->messageId = $this->update->getMessage()->getMessageId();
        }
        return $this->messageId;
    }

    protected function sendMessage(string $text, $replyMarkup = null, bool $forceReply = false, $parseMode = null) {
        if(!$forceReply && $this->isChatType('private')) {
            $this->bot->sendMessage($this->getUserId(), $text, $parseMode, false, null, $replyMarkup);
        } else {
            try {
                $this->bot->sendMessage($this->getChatId(), $text, $parseMode, false, $this->getMessageId(), $replyMarkup);
            } catch(Exception $e) {
                $this->bot->sendMessage($this->getChatId(), $text, $parseMode, false, null, $replyMarkup);
            }
        }
    }

    protected function getUserId() {
        if(!isset($this->userId)) {
            $this->userId = $this->update->getMessage()->getFrom()->getId();
        }
        return $this->userId;
    }

    protected function getFirstName() {
        if(!isset($this->firstName)) {
            $this->firstName = $this->update->getMessage()->getFrom()->getFirstName();
        }
        return $this->firstName;
    }

    protected function getUserName() {
        if(!isset($this->userName)) {
            $this->userName = $this->update->getMessage()->getFrom()->getFirstName();
            if($lastName = $this->update->getMessage()->getFrom()->getLastName()) {
                $this->userName.= ' '.$lastName;
            }
        }
        return $this->userName;
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

    protected function getReplyToMessage() {
        if(!isset($this->replyToMessage)) {
            $this->replyToMessage = $this->update->getMessage()->getReplyToMessage();
        }
        return $this->replyToMessage;
    }

    protected function getReplyToMessageText() {
        if(!isset($this->replyToMessageText)) {
            $this->replyToMessageText = $this->update->getMessage()->getReplyToMessage()->getText();
        }
        return $this->replyToMessageText;
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
