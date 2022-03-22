<?php

namespace App\Commands;

class Ping extends Command {

    public function run($update, $bot) {
        $userId = $this->getUserId($update);
        $bot->sendMessage($userId, 'pong');
    }

}