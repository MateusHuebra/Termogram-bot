<?php

namespace App\Update\Commands;

use App\Models\User;
use App\Services\ServerLog;
use App\Services\TextString;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class Notifications extends Command {

    public function run($update, $bot) {
        ServerLog::log('Notifications > run');
        $userId = $this->getUserId($update);
        $currentSubscriptionHour = User::find($userId)->subscription_hour;

        $keyboard = $this->getNotificationsKeyboard($currentSubscriptionHour);

        $bot->sendMessage($userId, TextString::get('settings.notifications'), null, false, null, $keyboard);
    }

    public function getNotificationsKeyboard($current) {
        $buttons = [];
        for ($i=0; $i < 12; $i++) {
            $am = $i;
            $pm = ''.(12+$i);
            if($i<10) {
                $am = '0'.$am;
            }

            $buttons[] = [
                [
                    'text' => ($current==$am)?$am.TextString::get('settings.selected'):$am,
                    'callback_data' => 'notification:'.$am
                ],
                [
                    'text' => ($current==$pm)?$pm.TextString::get('settings.selected'):$pm,
                    'callback_data' => 'notification:'.$pm
                ]
            ];
        }
        $turnOff = TextString::get('settings.turn_notifications_off');
        $buttons[] = [
            [
                'text' => ($current===null)?$turnOff.TextString::get('settings.selected'):$turnOff,
                'callback_data' => 'notification:'.'off'
            ]
        ];

        return new InlineKeyboardMarkup($buttons);
    }

}