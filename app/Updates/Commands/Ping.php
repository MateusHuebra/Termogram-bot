<?php

namespace App\Updates\Commands;

class Ping extends Command {

    public function run($update, $bot) {
        $userId = $this->getUserId($update);
        $bot->sendMessage($userId, 'pong');
    }

}