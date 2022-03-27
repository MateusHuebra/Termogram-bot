<?php

namespace App\Updates\Commands;

use App\Models\Game;
use App\Models\User;
use App\Services\TextString;

class Statistics extends Command {

    public function run($update, $bot) {
        if($userId = $this->getReplyToMessageUserId($update)) {
            $person = TextString::get('statistics.other');
        } else {
            $person = TextString::get('statistics.yours');
            $userId = $this->getUserId($update);
        }
        
        if(User::find($userId)===null) {
            $chatId = $this->getChatId($update);
            $messageId = $this->getMessageId($update);
            $bot->sendMessage($chatId, TextString::get('error.user_never_played'), null, false, $messageId);
            return;
        }
        $text = $this->getText($userId, $person);
        
        if($this->getChatType($update)=='private') {
            $bot->sendMessage($userId, $text);
        } else {
            $chatId = $this->getChatId($update);
            $messageId = $this->getMessageId($update);
            $bot->sendMessage($chatId, $text, null, false, $messageId);
        }
    }

    private function parseDistribution(array $wonAt) {
        $text = '';
        for ($i=1; $i<=7 ; $i++) {
            $number = 0;
            if(isset($wonAt[$i])) {
                $number = $wonAt[$i];
            }
            $originalString = ['1', '2', '3', '4', '5', '6', '7'];
            $betterString = ['１', '２', '３', '４', '５', '６', 'Ｘ'];
            $label = str_replace($originalString, $betterString, ''.$i);
            $text.= "\n".($label)."> {$number}";
        }
        return $text;
    }

    private function getText($userId, string $person) {
        $gameQuery = Game::where('user_id', $userId);
        $total = $gameQuery->count();
        $gameQuery = $gameQuery->where('ended', true);
        $ended = $gameQuery->count();
        $data = [
            'person' => $person,
            'total' => $total,
            'ended' => $ended
        ];
        $wonAt = $gameQuery->orderBy('won_at')->get()->countBy('won_at')->toArray();
        if(isset($wonAt[''])) {
            $wonAt[7] = $wonAt[''];
        }

        $text = TextString::get('statistics.text', $data);
        $text.= $this->parseDistribution($wonAt);
        return $text;
    }

}