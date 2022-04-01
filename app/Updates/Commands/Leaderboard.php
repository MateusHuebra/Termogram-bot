<?php

namespace App\Updates\Commands;

use App\Models\User;
use Exception;

class Leaderboard extends Command {

    public function run() {
        $this->dieIfUnallowedChatType(['group', 'supergroup'], 'only_groups', false);

        $users = User::orderBy('score', 'DESC')->get();
        $text = '';
        $position = 1;
        $last = 0;
        $this->bot->sendChatAction($this->getChatId(), 'typing');
        foreach ($users as $user) {
            try {
                $TgUser = $this->bot->getChatMember($this->getChatId(), $user->id);
            } catch(Exception $e) {
                //Bad Request: user not found
                //Bad Request: chat not found
                continue;
            }
            if(in_array($TgUser->getStatus(), ['left', 'kicked'])) {
                continue;
            }

            $name = $TgUser->getUser()->getFirstName();
            if($user->score==$last) {
                $positionString = '  ';
            } else {
                $positionString = Notifications::parseHour($position);
            }
            $text.= "\n{$positionString} 》[{$name}](tg://user?id={$user->id})  {$user->score}";
            $last = $user->score;
            $position++;
            if($position > 10) {
                break;
            }
        }

        $this->bot->sendMessage($this->getChatId(), $text, 'MarkdownV2', false, $this->getMessageId());
    }

}
