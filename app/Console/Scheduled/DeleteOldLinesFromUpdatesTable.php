<?php

namespace App\Console\Scheduled;

use App\Models\TelegramUpdate;
use TelegramBot\Api\BotApi;

class DeleteOldLinesFromUpdatesTable {

    public function __invoke()
    {
        $bot = new BotApi(env('TG_TOKEN'));
        $date = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime($date. ' - 1 days'));

        $updates = TelegramUpdate::where('added_at', '<', $yesterday);
        $total = $updates->count();
        $updates->delete();

        $bot->sendMessage(env('TG_MYID'), "{$total} registros deletados da tabela Telegram Updates");
    }

}