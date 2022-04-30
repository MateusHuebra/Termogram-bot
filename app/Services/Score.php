<?php

namespace App\Services;

use App\Models\Game;
use App\Models\User;

class Score {

    const DEFAULT_DATE_OFFSET = '1900-01-01';

    public function resetScore() {
        $users = User::all();
        foreach ($users as $user) {
            $user->score = 0;
            $user->save();
        }
    }

    public function distributeScore(string $date_offset = self::DEFAULT_DATE_OFFSET) {
        $games = Game::whereNotNull('won_at')->where('word_date', '>=', $date_offset)->get();
        foreach ($games as $game) {
            $user = User::find($game->user_id);
            $user->score+= (7 - $game->won_at);
            $user->save();
        }
    }
}