<?php

namespace App\Commands;

use App\Models\User;
use App\Services\ServerLog;
use App\Services\TextString;

class Attempt extends Command {

    public function run($update, $bot) {
        ServerLog::log('Attempt > run');
        $userId = $this->getUserId($update);

        if($this->GameExists($userId) === false) {
            ServerLog::log('game does\'n exist');
            $bot->sendMessage($userId, TextString::get('game.no_game'));
            return;
        }



    }

}