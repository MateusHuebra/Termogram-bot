<?php

namespace App\Updates\CallbackQueries;

use App\Services\TextString;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update as UpdateType;
use App\Updates\Update;

abstract class CallbackQuery extends Update {

    public function __construct(UpdateType $update, BotApi $bot)
    {
        parent::__construct($update, $bot);
        if($this->getMessage()==null) {
            $this->bot->answerCallbackQuery($this->getId(), TextString::get('error.too_old_message'));
            die;
        }
    }

    protected function getId() {
        if(!isset($this->id)) {
            $this->id = $this->update->getCallbackQuery()->getId();
        }
        return $this->id;
    }

    protected function getUserId() {
        if(!isset($this->userId)) {
            $this->userId = $this->update->getCallbackQuery()->getFrom()->getId();
        }
        return $this->userId;
    }

    protected function getMessage() {
        if(!isset($this->message)) {
            $this->message = $this->update->getCallbackQuery()->getMessage();
        }
        return $this->message;
    }

    protected function getMessageId() {
        if(!isset($this->messageId)) {
            if($this->getMessage()) {
                $this->messageId = $this->update->getCallbackQuery()->getMessage()->getMessageId();
            } else {
                $this->messageId = null;
            }

        }
        return $this->messageId;
    }

    protected function getChatId() {
        if(!isset($this->chatId)) {
            if($this->getMessage()) {
                $this->chatId = $this->update->getCallbackQuery()->getMessage()->getChat()->getId();
            } else {
                $this->chatId = null;
            }

        }
        return $this->chatId;
    }

    protected function getChatType() {
        if(!isset($this->chatType)) {
            $this->chatType = $this->update->getCallbackQuery()->getMessage()->getChat()->getType();
        }
        return $this->chatType;
    }

    protected function getData(string $type) {
        return str_replace($type.':', '', $this->update->getCallbackQuery()->getData());
    }

}