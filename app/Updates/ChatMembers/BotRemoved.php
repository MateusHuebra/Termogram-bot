<?php

namespace App\Updates\ChatMembers;

use App\Models\Group;
use App\Services\ServerLog;

class BotRemoved extends ChatMember {

    public function run() {
        ServerLog::log('BotRemoved > run');
        $member = $this->getLeftChatMember();
        $group = Group::findOrFail($this->getChatId());
        $group->delete();
    }

}