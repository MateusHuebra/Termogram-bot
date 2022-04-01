<?php

namespace App\Console\Scheduled;

use App\Models\Game;
use App\Models\User;
use App\Services\TextString;
use Exception;
use TelegramBot\Api\BotApi;

class NotificateSubscribedUsers {

    public function __invoke()
    {
        $bot = new BotApi(env('TG_TOKEN'));
        $hour = date('H');
        $users = User::whereSubscriptionHour($hour)->get('id');
        foreach ($users as $user) {
            if(!Game::byUser($user->id)->exists()) {
                try {
                    $keyboard = $this->getNotificationKeyboard();
                    $bot->sendMessage($user->id, TextString::get('notification.new_word'), null, false, $keyboard);
                } catch (Exception $e) {
                    $bot->sendMessage(env('TG_MYID'), "error on trying to notificate to {$user->id}: {$e->getMessage()}");
                }
            }
        }
    }

    private function getNotificationKeyboard() {
        return [
            [
                [
                    'text' => TextString::get('settings.notification_settings'),
                    'callback_data' => 'open_notification:'
                ]
            ]
        ];
    }

}