<?php

namespace App\Updates\Commands;

use App\Services\TextString;

class HelpNotifications extends Command {

    public function run() {
        $this->dieIfUnallowedChatType(['private', 'group', 'supergroup']);

        $this->sendMessage(TextString::get('help.notifications'));
    }

}