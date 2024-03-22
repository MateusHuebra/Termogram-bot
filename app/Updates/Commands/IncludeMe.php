<?php

namespace App\Updates\Commands;
use App\Models\Group;
use App\Models\User;
use App\Services\TextString;

class IncludeMe extends Command {

    public function run() {
        $this->dieIfUnallowedChatType(['group', 'supergroup']);

        $user = User::find($this->getUserId());
        $user->first_name = mb_substr($this->getFirstName(), 0, 16);
        $user->save();
        
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
        
        //$this->sendMessage(TextString::get('leaderboard.included'));
        $this->bot->call('setMessageReaction', [
            'chat_id' => $this->getChatId(),
            'message_id' => $this->getMessageId(),
            'reaction' => json_encode([
                ['type' => 'emoji', 'emoji' => '👍']
            ])
        ]);
    }

}