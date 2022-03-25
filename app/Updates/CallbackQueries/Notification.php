<?php

namespace App\Updates\CallbackQueries;

use App\Models\User;
use App\Services\ServerLog;
use App\Services\TextString;

class Notification extends CallbackQuery {

    public function run($update, $bot) {
        ServerLog::log('CallbackQuery > run');
        $userId = $this->getUserId($update);
        $chatId = $this->getChatId($update);
        $messageId = $this->getMessageId($update);
        $data = $this->getData($update, 'notification');

        $text = TextString::get('notification.setted');
        if($data==='off') {
            $data = null;
            $text.= TextString::get('notification.off');
        } else {
            $text.= $data.'h';
        }

        $user = User::find($userId);
        $user->subscription_hour = $data;
        $user->save();

        if($messageId && $chatId) {
            $bot->editMessageText($chatId, $messageId, $text);
        } else {
            $bot->sendMessage($chatId, $text);
        }
    }

}