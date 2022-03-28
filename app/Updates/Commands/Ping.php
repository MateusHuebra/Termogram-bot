<?php

namespace App\Updates\Commands;

class Ping extends Command {

    public function run() {
        $this->dieIfUnallowedChatType(['private', 'group', 'supergroup']);
        
        $this->sendMessage('pong');
    }

}