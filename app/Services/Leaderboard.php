<?php

namespace App\Services;

use App\Updates\Commands\Notifications;
use App\Models\User;
use App\Models\Group;
use App\Services\TextString;
use Exception;
use Illuminate\Support\Facades\DB;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class Leaderboard {

    private $yourPosition = null;
    private $positions = [];

    public function __construct($limit = 15, $final = false) {
        $this->limit = $limit;
        $this->final = $final;
    }

    public function renderBoard($users, $type, $page = 0, $userId = 0) {
        ServerLog::log('Leaderboard > renderBoard');
        $text = TextString::get('leaderboard.'.$type)."\n";

        $this->limit = 15;
        $offset = $page*$this->limit;
        $position = 1;
        $keepFirstPositionForThePage = 1;
        $lastPosition = 1;
        $lastScore = 0;
        for($i = 0; $i < count($users); $i++) {
            $user = $users[$i];

            if($user->score==$lastScore) {
                $positionString = ' \=  ';
            } else {
                $positionString = '*'.Notifications::parseHour($position).' ';
                $positionString = str_replace(['*01 ', '*02 ', '*03 ', '*'], ['🥇', '🥈', '🥉', ''], $positionString);
                $lastPosition = $position;
                if($keepFirstPositionForThePage) {
                    $keepFirstPositionForThePage = $positionString;
                }
            }
            
            $this->positions[$user->id] = $lastPosition;
            if($user->id == $userId) {
                $this->yourPosition = $lastPosition;
            }

            if($user->mention && $type == 'group') {
                $path = 'leaderboard.line_with_mention';
            } else {
                $path = 'leaderboard.line';
            }

            if($i > $offset-1 && $i <= $offset+$this->limit-1) {
                if($keepFirstPositionForThePage) {
                    $positionString = $keepFirstPositionForThePage;
                    $keepFirstPositionForThePage = false;
                }
                $text.= TextString::get($path, [
                    'position' => $positionString,
                    'name' => TextString::parseMarkdownV2($user->first_name),
                    'id' => $user->id,
                    'score' => $user->score
                ]);
            }

            $lastScore = $user->score;
            $position++;
        }

        if($this->final) {
            $text.= TextString::get('leaderboard.final', ['date' => date('d/m/y')]);
        } else if($type == 'group') {
            $text.= TextString::get('leaderboard.dontseeyou');
            $text.= TextString::get('leaderboard.mention');
        }

        if(count($users) <= $this->limit) {
            $keyboard = false;
        } else if($page*$this->limit+$this->limit >= count($users)) {
            $keyboard = 'end';
        } else {
            $keyboard = true;
        }
        
        return [
            'text' => $text,
            'keyboard' => $keyboard,
            'your_position' => $this->yourPosition,
            'positions' => $this->positions
        ];
    }

    public function getPaginationKeyboard($page, $keyboard) {
        if(!$keyboard) {
            return null;
        }
        $buttons = [];
        if($page>0) {
            $buttons[0][] = [
                'text' => '<',
                'callback_data' => 'leaderboard:'.($page-1)
            ];
        }
        $buttons[0][] = [
            'text' => ($page+1).' ↻',
            'callback_data' => 'leaderboard:'.$page
        ];
        if($keyboard!=='end') {
            $buttons[0][] = [
                'text' => '>',
                'callback_data' => 'leaderboard:'.($page+1)
            ];
        }
        return new InlineKeyboardMarkup($buttons);
    }

    public function getUsers($membersList = null) {
        $today = date('Y-m-d');
        $limitDay = date('Y-m-d', strtotime($today. ' - 14 days'));
        $users = User::leftJoin('games', 'users.id', '=', 'games.user_id')
            ->select('users.id', 'users.score', 'users.first_name', 'users.mention', DB::raw('max(games.word_date) as last_game_date'))
            ->groupBy('users.id')
            ->having('last_game_date', '>', $limitDay)
            ->orderBy('users.score', 'DESC');
        if(!is_null($membersList)) {
            $users->whereIn('users.id', $membersList);
        }
        return $users->get();
    }

    public function getMembersList($chatId, $bot) {
        ServerLog::log('Leaderboard > getMembersList', false);
        $groupQuery = Group::where('id', $chatId);
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
            $group->id = $chatId;
        }

        $bot->sendMessage($chatId, TextString::get('leaderboard.wait'));
        ServerLog::log('- group or recent data not found, starting requests to Telegram API', false);
        $users = User::all();
        $membersList = [];
        foreach($users as $user) {
            if($this->isUserInGroup($user, $chatId, $bot)) {
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

    public function isUserInGroup(User $user, $chatId, $bot) {
        try {
            $TgUser = $bot->getChatMember($chatId, $user->id);
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

    public function hasUserPlayedRecently($user, $limitDay) {
        if($limitDay > $user->last_game_date) {
            return false;
        }
        return true;
    }

}