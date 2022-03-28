<?php

namespace App\Updates\CallbackQueries;

use App\Updates\Update;

abstract class CallbackQuery extends Update {

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

    protected function getData(string $type) {
        return str_replace($type.':', '', $this->update->getCallbackQuery()->getData());
    }

}