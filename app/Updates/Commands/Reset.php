<?php

namespace App\Updates\Commands;

use App\Models\Attempt;
use App\Models\Game;
use App\Services\TextString;

class Reset extends Command {

    public function run() {
        $this->dieIfUnallowedChatType(['private']);
        $this->dieIfNotAdmin();

        Attempt::byUser($this->getUserId())->delete();
        Game::byUser($this->getUserId())->delete();
        $this->sendMessage(TextString::get('settings.reset'));
    }

}