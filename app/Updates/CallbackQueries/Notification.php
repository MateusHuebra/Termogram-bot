<?php

namespace App\Updates\CallbackQueries;

use App\Models\User;
use App\Services\ServerLog;
use App\Services\TextString;

class Notification extends CallbackQuery {

    public function run() {
        ServerLog::log('Notification > run');
        $data = $this->getData('notification');

        $text = TextString::get('notification.setted');
        if($data==='off') {
            $data = null;
            $text.= TextString::get('notification.off');
        } else {
            $text.= $data.'h';
        }

        $user = User::find($this->getUserId());
        $user->subscription_hour = $data;
        $user->save();

        if($this->getMessageId() && $this->getChatId()) {
            $this->bot->editMessageText($this->getChatId(), $this->getMessageId(), $text);
        } else {
            $this->bot->sendMessage($this->getUserId(), $text);
        }
    }

}