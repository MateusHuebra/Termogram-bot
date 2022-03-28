<?php

namespace App\Updates\Commands;

use App\Models\Game;
use App\Models\User;
use App\Services\TextString;

class Statistics extends Command {

    public function run() {
        if($userId = $this->getReplyToMessageUserId()) {
            $person = TextString::get('statistics.other');
        } else {
            $person = TextString::get('statistics.yours');
            $userId = $this->getUserId();
        }
        
        if(User::find($userId)===null) {
            $this->sendMessage(TextString::get('error.user_never_played'), null, true);
            return;
        }
        
        $text = $this->getText($userId, $person);
        $this->sendMessage($text);
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
        $wonAt = $gameQuery->orderBy('won_at')->get()->countBy('won_at')->toArray();
        if(isset($wonAt[''])) {
            $wonAt[7] = $wonAt[''];
        } else {
            $wonAt[7] = 0;
        }
        $winnings = ($wonAt[0]??0)+($wonAt[1]??0)+($wonAt[2]??0)+($wonAt[3]??0)+($wonAt[4]??0)+($wonAt[5]??0)+($wonAt[6]??0);
        $winRate = $winnings*100/($winnings+$wonAt[7]);
        $data = [
            'person' => $person,
            'total' => $total,
            'win_rate' => $winRate,
            'ended' => $ended
        ];

        $text = TextString::get('statistics.text', $data);
        $text.= $this->parseDistribution($wonAt);
        return $text;
    }

}