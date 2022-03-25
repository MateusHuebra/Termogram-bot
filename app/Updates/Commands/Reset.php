<?php

namespace App\Updates\Commands;

use App\Models\Attempt;
use App\Models\Game;
use App\Services\TextString;

class Reset extends Command {

    public function run($update, $bot) {
        $userId = $this->getUserId($update);
        if($userId != env('TG_MYID')) {
            return;
        }

        Attempt::byUser($userId)->delete();
        Game::byUser($userId)->delete();
        $bot->sendMessage($userId, TextString::get('settings.reset'));
    }

}