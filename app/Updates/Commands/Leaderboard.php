<?php

namespace App\Updates\Commands;

use App\Models\User;
use App\Models\Group;
use App\Services\ServerLog;
use App\Services\TextString;
use Exception;
use Illuminate\Support\Facades\DB;

class Leaderboard extends Command {

    const RESERVED_CHARACTERS = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
    const ESCAPED_CHARACTERS = ['\_', '\*', '\[', '\]', '\(', '\)', '\~', '\`', '\>', '\#', '\+', '\-', '\=', '\|', '\{', '\}', '\.', '\!'];

    public function run() {
        ServerLog::log('Leaderboard > run');
        $this->dieIfUnallowedChatType(['group', 'supergroup'], 'only_groups', false);
 
        $membersList = $this->getMembersList();
        $users = User::leftJoin('games', 'users.id', '=', 'games.user_id')
            ->select('users.id', 'users.score', 'users.first_name', 'users.mention', DB::raw('max(games.word_date) as last_game_date'))
            ->whereIn('users.id', $membersList)
            ->groupBy('users.id')
            ->orderBy('users.score', 'DESC')
            ->get();

        $this->bot->sendChatAction($this->getChatId(), 'typing');
        $board = $this->renderBoard($users);
        
        try {
            $this->bot->sendMessage($this->getChatId(), $board, 'MarkdownV2', false, $this->getMessageId());
        } catch(Exception $e) {
            $this->bot->sendMessage($this->getChatId(), $board, 'MarkdownV2', false);
        }
        
    }

    private function getMembersList() {
        ServerLog::log('Leaderboard > getMembersList', false);
        $groupQuery = Group::where('id', $this->getChatId());
        $group = $groupQuery->first();

        if($groupQuery->exists()) {
            ServerLog::log('- group exists', false);
            $oneWeekAgo = date('Y-m-d', strtotime('- 7 days'));

            if($group->members_list_updated_at > $oneWeekAgo) {
                ServerLog::log('- recent data', false);
                return json_decode($group->members_list);
            }

        } else {
            $group = new Group();
            $group->id = $this->getChatId();
        }

        $this->bot->sendMessage($this->getChatId(), TextString::get('leaderboard.wait'));
        ServerLog::log('- group or recent data not found, starting requests to Telegram API', false);
        $users = User::all();
        $membersList = [];
        foreach($users as $user) {
            if($this->isUserInGroup($user)) {
                $membersList[] = $user->id;
            }
        }
        ServerLog::log('- requests ended', false);

        $group->members_list_updated_at = date('Y-m-d');
        $group->members_list = json_encode($membersList);
        $group->save();
        ServerLog::log('- data saved to DB');

        return $membersList;
    }

    private function isUserInGroup(User $user) {
        try {
            $TgUser = $this->bot->getChatMember($this->getChatId(), $user->id);
            //get user again to avoid rewriting data with old data
            $user = User::find($user->id);
            $user->first_name = mb_substr($TgUser->getUser()->getFirstName(), 0, 16);
            $user->save();
        } catch(Exception $e) {
            //Bad Request: user not found || chat not found
            return false;
        }
        if(in_array($TgUser->getStatus(), ['left', 'kicked'])) {
            return false;
        }
        return true;
    }

    private function renderBoard($users) {
        ServerLog::log('Leaderboard > renderBoard');
        $text = TextString::get('leaderboard.title')."\n";
        $today = date('Y-m-d');
        $limitDay = date('Y-m-d', strtotime($today. ' - 14 days'));

        $position = 1;
        $last = 0;
        foreach ($users as $user) {
            if(!$this->hasUserPlayedRecently($user, $limitDay)) {
                continue;
            }

            if($user->score==$last) {
                $positionString = ' \=  ';
            } else {
                $positionString = Notifications::parseHour($position).' ';
                $positionString = str_replace(['01 ', '02 ', '03 '], ['🥇', '🥈', '🥉'], $positionString);
            }

            if($user->mention) {
                $path = 'leaderboard.line_with_mention';
            } else {
                $path = 'leaderboard.line';
            }
            $text.= TextString::get($path, [
                'position' => $positionString,
                'name' => self::parseMarkdownV2($user->first_name),
                'id' => $user->id,
                'score' => $user->score
            ]);
            $last = $user->score;
            $position++;
        }
        $text.= TextString::get('leaderboard.dontseeyou');
        $text.= TextString::get('leaderboard.mention');
        return $text;
    }

    private function hasUserPlayedRecently($user, $limitDay) {
        if($limitDay > $user->last_game_date) {
            return false;
        }
        return true;
    }

    static function parseMarkdownV2($string) {
        return str_replace(self::RESERVED_CHARACTERS, self::ESCAPED_CHARACTERS, $string);
    }

}
