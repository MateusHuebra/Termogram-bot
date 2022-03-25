<?php

namespace App\Updates\CallbackQueries;

abstract class CallbackQuery {

    public function getUserId($update) {
        return $update->getCallbackQuery()->getFrom()->getId();
    }

    public function getMessageId($update) {
        return $update->getCallbackQuery()->getMessage()->getMessageId();
    }

    public function getChatId($update) {
        return $update->getCallbackQuery()->getMessage()->getChat()->getChatId();
    }

    public function getData($update, string $type) {
        return str_replace($type.':', '', $update->getCallbackQuery()->data());
    }

}