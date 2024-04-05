<?php

namespace App\Updates\ChatMembers;

use App\Models\Group;
use App\Services\ServerLog;

class LeftMember extends ChatMember {

    public function run() {
        ServerLog::log('LeftMember > run');
        $member = $this->getLeftChatMember();
        $group = Group::findOrFail($this->getChatId());
        $membersList = json_decode($group->members_list);

        $key = array_search($member->getId(), $membersList);
        if ($key !== false) {
            unset($membersList[$key]);
        }
        
        $group->members_list = json_encode(array_values($membersList));
        $group->save();
    }

}