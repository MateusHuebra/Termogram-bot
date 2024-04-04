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

        $offset = $page*$this->limit;
        $position = 1;
        $keepFirstPositionForThePage = 1;
        $lastPosition = 1;
        $lastScore = -1;
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
            } else if(!is_null($user->username) && $type == 'group'){
                $path = 'leaderboard.line_with_link';
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
                    'score' => $user->is_banned ? '_banido_' : $user->score
                ]);
            }

            $lastScore = $user->score;
            $position++;
        }

        if($this->final) {
            $initialDay = date('d') > 16 ? 16 : 1 ;
            $text.= TextString::get('leaderboard.final', [
                'initial_day' => $initialDay,
                'date' => date('d/m/y')
            ]);
        } else if($type == 'group') {
            $text.= TextString::get('leaderboard.dontseeyou');
            $text.= TextString::get('leaderboard.mention');
        }

        if(count($users) <= $this->limit) {
            $keyboard = false;
        } else {
            $keyboard = true;
        }
        $totalPages = intdiv(count($users)-1, $this->limit)+1;
        
        return [
            'text' => $text,
            'keyboard' => $keyboard,
            'your_position' => $this->yourPosition,
            'positions' => $this->positions,
            'total_pages' => $totalPages
        ];
    }

    public function getPaginationKeyboard($page, $keyboard, $totalPages) {
        if(!$keyboard) {
            return null;
        }
        $previous = ($page>0) ? ($page-1) : 0;
        $next = ($page < $totalPages-1) ? ($page+1) : $page;
        $last = ($page < $totalPages-1) ? ($totalPages-1) : $page;
        $buttons = [
            [
                [
                    'text' => '◁',
                    'callback_data' => 'leaderboard:'.$previous
                ],
                [
                    'text' => ($page+1).' / '.$totalPages.' ↻',
                    'callback_data' => 'leaderboard:'.$page
                ],
                [
                    'text' => '▷',
                    'callback_data' => 'leaderboard:'.$next
                ]
                
            ],
            [
                [
                    'text' => '|◁ ',
                    'callback_data' => 'leaderboard:0'
                ],
                [
                    'text' => ' ▷|',
                    'callback_data' => 'leaderboard:'.$last
                ]
            ]
        ];
        return new InlineKeyboardMarkup($buttons);
    }

    public function getUsers($membersList = null) {
        $today = date('Y-m-d');
        $limitDay = date('Y-m-d', strtotime($today. ' - 14 days'));
        $users = User::leftJoin('games', 'users.id', '=', 'games.user_id')
            ->select('users.id', 'users.score', 'users.username', 'users.first_name', 'users.mention', 'users.is_banned', DB::raw('max(games.word_date) as last_game_date'))
            ->groupBy('users.id')
            ->having('last_game_date', '>', $limitDay)
            ->orderBy('users.score', 'DESC')
            ->orderBy('users.is_banned', 'ASC');
        if(!is_null($membersList)) {
            $users->whereIn('users.id', $membersList);
        }
        return $users->get();
    }

    public function getMembersList($chat, $bot) {
        ServerLog::log('Leaderboard > getMembersList', false);
        $chatId = $chat->getId();
        $groupQuery = Group::where('id', $chatId);
        $group = $groupQuery->first();

        if($groupQuery->exists()) {
            ServerLog::log('- group exists', false);
            $oneWeekAgo = date('Y-m-d', strtotime('- 7 days'));

            if($group->members_list_updated_at > $oneWeekAgo) {
                ServerLog::log('- recent data', false);
                $group->username = $chat->getUsername();
                $group->title = mb_substr($chat->getTitle(), 0, 32);
                $group->save();
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

        $group->username = $chat->getUsername();
        $group->title = mb_substr($chat->getTitle(), 0, 32);
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
            $user->username = $TgUser->getUser()->getUsername();
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