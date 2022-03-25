<?php

namespace App\Commands;

use App\Models\Game;
use App\Models\Season;
use App\Models\User;
use App\Services\ServerLog;
use App\Services\TextString;

class Start extends Command {

    public function run($update, $bot) {
        ServerLog::log('Start > run');
        $userId = $this->getUserId($update);

        if(User::ofId($userId)->exists() === false) {
            ServerLog::log('user does\'n exist');
            $this->addNewUser($userId);
            $bot->sendMessage($userId, TextString::get('start.welcome'));
            return;
        }

        if(Game::byUser($userId)->exists() === false) {
            ServerLog::log('game does\'n exist');
            $this->startNewGame($userId);
            $bot->sendMessage($userId, TextString::get('start.game_started'));
            return;
        }

        ServerLog::log('user and game exist');
        $bot->sendMessage($userId, TextString::get('placeholder.game_status'));
        
    }

    public function addNewUser($userId) {
        ServerLog::log('creating new user: '.$userId);
        $user = new User();
        $user->id = $userId;
        $user->subscription_hour = 0;
        $user->save();
    }

    public function startNewGame($userId) {
        $date = date('Y-m-d');
        $season = Season::current()->first();
        ServerLog::log('creating new game for '.$userId.' in '.$date.' from season '.$season->name);

        $game = new Game();
        $game->user_id = $userId;
        $game->word_date = $date;
        $game->won_at = null;
        $game->ended = false;
        $game->season_id = $season->id;
        $game->save();
    }

}