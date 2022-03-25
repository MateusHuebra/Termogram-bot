<?php

namespace App\Console\Scheduled;

use App\Models\User;
use App\Services\TextString;
use TelegramBot\Api\BotApi;

class NotificateSubscribedUsers {

    public function __invoke()
    {
        $bot = new BotApi(env('TG_TOKEN'));
        $hour = date('H');
        $users = User::whereSubscriptionHour($hour)->get('id');
        foreach ($users as $user) {
            $bot->sendMessage($user->id, TextString::get('notification.new_word'));
        }
    }

}