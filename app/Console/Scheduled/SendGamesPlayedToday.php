<?php

namespace App\Console\Scheduled;

use App\Models\Game;
use TelegramBot\Api\BotApi;

class SendGamesPlayedToday {

    public function __invoke()
    {
        $bot = new BotApi(env('TG_TOKEN'));
        $date = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime($date. ' - 1 days'));

        $games = Game::where('word_date', $yesterday);
        $total = $games->count();
        $ended = $games->where('ended', true)->count();

        $bot->sendMessage(env('TG_MYID'), "{$total} jogos realizados em {$yesterday}\n{$ended} finalizados");
    }

}