<?php

namespace App\Updates\Commands;

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
        for ($row=0; $row<4; $row++) {
            $arrayRow = [];
            for ($i=($row*6); $i<=($row*6)+5; $i++) {
                $hour = $i;
                if($hour==24) {
                    $hour = 0;
                }
                $arrayRow[] = [
                    'text' => $this->parseCurrent($hour, $current),
                    'callback_data' => 'notification:'.$this->parseHour($hour)
                ];
            }
            $buttons[] = $arrayRow;
        }

        $turnOff = TextString::get('settings.turn_notifications_off');
        $buttons[] = [
            [
                'text' => ($current===null)?'• '.$turnOff.' •':$turnOff,
                'callback_data' => 'notification:'.'off'
            ]
        ];

        return new InlineKeyboardMarkup($buttons);
    }

    public function parseHour($hour) {
        if(strlen(''.$hour)<2) {
            return '0'.$hour;
        }
        return $hour;
    }

    public function parseCurrent($hour, $current) {
        $hour = $this->parseHour($hour);
        return ($current==$hour)?'• '.$hour.' •':$hour;
    }

}