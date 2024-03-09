<?php

namespace App\Updates\Commands;
use App\Models\Group;
use App\Models\User;
use App\Services\TextString;

class Mention extends Command {

    public function run() {
        $user = User::find($this->getUserId());
        $user->mention = !$user->mention;
        $user->save();
        if($user->mention) {
            $string = TextString::get('leaderboard.mention_on');
        } else {
            $string = TextString::get('leaderboard.mention_off');
        }
        $this->sendMessage($string);
    }

}