<?php

namespace App\Console\Scheduled;

use App\Models\Game;
use TelegramBot\Api\BotApi;

class ScheduleTest {

    public function __invoke()
    {
        $bot = new BotApi(env('TG_TOKEN'));
        $date = date('d/m/Y H:i:s');

        $bot->sendMessage(env('TG_MYID'), $date);
    }

}