<?php

namespace App\Updates\Commands;

use App\Services\TextString;

class Help extends Command {

    public function run() {
        $this->dieIfUnallowedChatType(['private', 'group', 'supergroup']);
        
        $this->sendMessage(TextString::get('help.main'));
    }

}