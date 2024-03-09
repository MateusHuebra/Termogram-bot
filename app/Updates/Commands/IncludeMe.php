<?php

namespace App\Updates\Commands;
use App\Models\Group;
use App\Services\TextString;

class IncludeMe extends Command {

    public function run() {
        $this->dieIfUnallowedChatType(['group', 'supergroup']);
        
        $groupQuery = Group::where('id', $this->getChatId());
        $group = $groupQuery->first();

        if(!$groupQuery->exists()) {
            return;
        }

        $membersList = json_decode($group->members_list);
        if(!in_array($this->getUserId(), $membersList)) {
            array_push($membersList, $this->getUserId());
            $group->members_list = json_encode($membersList);
            $group->save();
        }
        
        $this->sendMessage(TextString::get('leaderboard.included'));
    }

}