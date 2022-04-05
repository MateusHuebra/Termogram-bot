<?php

namespace App\Updates\Commands;

use App\Models\User;
use App\Services\ServerLog;
use App\Services\TextString;
use Exception;
use Illuminate\Support\Facades\DB;

class Leaderboard extends Command {

    const RESERVED_CHARACTERS = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
    const ESCAPED_CHARACTERS = ['\_', '\*', '\[', '\]', '\(', '\)', '\~', '\`', '\>', '\#', '\+', '\-', '\=', '\|', '\{', '\}', '\.', '\!'];

    public function run() {
        $this->dieIfUnallowedChatType(['group', 'supergroup'], 'only_groups', false);

        $users = User::leftJoin('games', 'users.id', '=', 'games.user_id')
            ->select('users.id', 'users.score', DB::raw('max(games.word_date) as last_game_date'))
            ->groupBy('users.id')
            ->orderBy('users.score', 'DESC')
            ->get();

        $this->bot->sendChatAction($this->getChatId(), 'typing');
        $board = $this->renderBoard($users);
        
        $this->bot->sendMessage($this->getChatId(), $board, 'MarkdownV2', false, $this->getMessageId());
    }

    private function renderBoard($users) {
        $text = TextString::get('leaderboard.title')."\n";
        $today = date('Y-m-d');
        $limitDay = date('Y-m-d', strtotime($today. ' - 14 days'));

        $position = 1;
        $last = 0;
        foreach ($users as $user) {
            $TgUser = $this->getValidChatMemberOrFalse($user, $limitDay);
            if(!$TgUser) {
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
                'name' => self::parseMarkdownV2($TgUser->getUser()->getFirstName()),
                'id' => $user->id,
                'score' => $user->score
            ]);
            $last = $user->score;
            $position++;
        }
        return $text;
    }

    private function getValidChatMemberOrFalse($user, $limitDay) {
        if($limitDay > $user->last_game_date) {
            return false;
        }
        try {
            $TgUser = $this->bot->getChatMember($this->getChatId(), $user->id);
        } catch(Exception $e) {
            //Bad Request: user not found || chat not found
            return false;
        }
        if(in_array($TgUser->getStatus(), ['left', 'kicked'])) {
            return false;
        }
        return $TgUser;
    }

    static function parseMarkdownV2($string) {
        return str_replace(self::RESERVED_CHARACTERS, self::ESCAPED_CHARACTERS, $string);
    }

}
