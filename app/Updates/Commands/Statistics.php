<?php

namespace App\Updates\Commands;

use App\Models\Game;
use App\Services\TextString;

class Statistics extends Command {

    public function run($update, $bot) {
        $userId = $this->getUserId($update);
        $gameQuery = Game::where('user_id', $userId);
        $total = $gameQuery->count();
        $gameQuery = $gameQuery->where('ended', true);
        $ended = $gameQuery->count();
        $data = [
            'total' => $total,
            'ended' => $ended
        ];
        $wonAt = $gameQuery->orderBy('won_at')->get()->countBy('won_at')->toArray();
        if(isset($wonAt[''])) {
            $wonAt[7] = $wonAt[''];
        }
        $text = TextString::get('statistics.text', $data);
        $text.= $this->getDistribution($wonAt);

        $bot->sendMessage($userId, $text);
    }

    public function getDistribution(array $wonAt) {
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

}