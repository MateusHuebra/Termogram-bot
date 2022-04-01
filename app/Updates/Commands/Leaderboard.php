<?php

namespace App\Updates\Commands;

use App\Models\User;
use App\Services\TextString;
use Exception;

class Leaderboard extends Command {

    public function run() {
        $this->dieIfUnallowedChatType(['group', 'supergroup'], 'only_groups', false);

        $users = User::orderBy('score', 'DESC')->get();
        $text = TextString::get('leaderboard.title')."\n";
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

            if($user->score==$last) {
                $positionString = ' \=  ';
            } else {
                $positionString = Notifications::parseHour($position).' ';
                $positionString = str_replace(['01 ', '02 ', '03 '], ['🥇', '🥈', '🥉'], $positionString);
            }
            $text.= TextString::get('leaderboard.line', [
                'position' => $positionString,
                'name' => $TgUser->getUser()->getFirstName(),
                'id' => $user->id,
                'score' => $user->score
            ]);
            $last = $user->score;
            $position++;
            if($position > 10) {
                break;
            }
        }

        $this->bot->sendMessage($this->getChatId(), $text, 'MarkdownV2', false, $this->getMessageId());
    }

}
