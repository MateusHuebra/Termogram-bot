<?php

namespace App\Updates\ChatMembers;

use App\Services\ServerLog;
use App\Models\Group;
use App\Models\User;

class NewMembers extends ChatMember {

    public function run() {
        ServerLog::log('NewMembers > run');
        $group = Group::find($this->getChatId());

        if(!$group) {
            $group = $this->addGroup();
        }
        
        $group->members_list = $this->getListWithNewMembers($group);
        $group->save();
    }

    private function addGroup() {
        ServerLog::log('NewMembers > add group');
        $group = new Group();
        $group->id = $this->getChatId();
        $group->title = $this->getChatTitle();
        $group->username = $this->getChatUsername();
        $group->members_list_updated_at = null;
        $group->members_list = json_encode([]);
        return $group;
    }

    private function getListWithNewMembers($group) {
        ServerLog::log('NewMembers > addMembersToGroup');
        $members = $this->getNewChatMembers();
        $membersList = json_decode($group->members_list);

        foreach ($members as $member) {
            ServerLog::log($member->getId().' '.$member->getFirstName(), false);
            if($member->isBot()) {
                ServerLog::log('ignore bot');
                continue;
            }
            if(!User::find($member->getId())){
                ServerLog::log('user dont exist');
                continue;
            }
            ServerLog::log('add user');
            array_push($membersList, $member->getId());
        }
        ServerLog::log('saving list: '.json_encode($membersList));
        return json_encode($membersList);
    }

}