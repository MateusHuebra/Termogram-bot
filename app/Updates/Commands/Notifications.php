<?php

namespace App\Updates\Commands;

use App\Models\User;
use App\Services\ServerLog;
use App\Services\TextString;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class Notifications extends Command {

    public function run() {
        $this->dieIfUnallowedChatType(['private']);
        ServerLog::log('Notifications > run');
        $currentSubscriptionHour = User::find($this->getUserId())->subscription_hour;

        $keyboard = self::getNotificationsKeyboard($currentSubscriptionHour);

        $this->sendMessage(TextString::get('settings.notifications'), $keyboard);
    }

    static function getNotificationsKeyboard($current) : InlineKeyboardMarkup {
        $buttons = [];
        for ($row=0; $row<4; $row++) {
            $arrayRow = [];
            for ($i=($row*6); $i<=($row*6)+5; $i++) {
                $hour = $i;
                if($hour==24) {
                    $hour = 0;
                }
                $arrayRow[] = [
                    'text' => self::parseCurrent($hour, $current),
                    'callback_data' => 'notification:'.self::parseHour($hour)
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

    static function parseHour($hour) : string {
        $hour = ''.$hour;
        if(strlen($hour)<2) {
            return '0'.$hour;
        }
        return $hour;
    }

    static function parseCurrent($hour, $current) : string {
        $hour = self::parseHour($hour);
        return ($current==$hour)?'• '.$hour.' •':$hour;
    }

}