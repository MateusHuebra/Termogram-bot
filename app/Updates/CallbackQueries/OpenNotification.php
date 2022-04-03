<?php

namespace App\Updates\CallbackQueries;

use App\Models\User;
use App\Services\ServerLog;
use App\Services\TextString;
use App\Updates\Commands\Notifications;

class OpenNotification extends CallbackQuery {

    public function run() {
        ServerLog::log('Open Notification > run');
        $this->bot->answerCallbackQuery($this->getId(), TextString::get('settings.loading'));
        
        $currentSubscriptionHour = User::find($this->getUserId())->subscription_hour;

        $keyboard = Notifications::getNotificationsKeyboard($currentSubscriptionHour);
        
        $this->bot->sendMessage($this->getUserId(), TextString::get('settings.notifications'), null, false, null, $keyboard);
    }

}