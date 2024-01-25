<?php

namespace App\Console\Scheduled;

use App\Models\Game;
use App\Models\User;
use App\Services\TextString;
use Exception;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class NotificateSubscribedUsers {

    public function __invoke()
    {
        $bot = new BotApi(env('TG_TOKEN'));
        $hour = date('H');
        $users = User::whereSubscriptionHour($hour)->get('id');
        foreach ($users as $user) {
            if(!Game::byUser($user->id)->exists()) {
                self::tryToSendMessage($bot, $user->id);
            }
        }
    }

    static function tryToSendMessage(BotApi $bot, $userId) {
        try {
            $keyboard = self::getNotificationKeyboard();
            $bot->sendMessage($userId, TextString::get('notification.new_word'), null, false, null, $keyboard);
        } catch (Exception $e) {
            $user = User::find($userId);
            $user->subscription_hour = null;
            $user->save();
            $bot->sendMessage(env('TG_MYID'), "error on trying to notificate to {$userId}: {$e->getMessage()}\n\nturning off his notifications");
        }
    }

    static function getNotificationKeyboard() {
        $buttons = [
            [
                [
                    'text' => TextString::get('settings.play'),
                    'callback_data' => 'play:'
                ]
            ],
            [
                [
                    'text' => TextString::get('settings.notifications_settings'),
                    'callback_data' => 'open_notification:'
                ]
            ]
        ];

        return new InlineKeyboardMarkup($buttons);
    }

}