<?php

namespace App\Commands;

use App\Models\Game;
use App\Models\Season;
use App\Models\User;
use App\Services\ServerLog;

class Start extends Command {

    public function run($update, $bot) {
        ServerLog::log('Start > run');
        $userId = $this->getUserId($update);
        $userExists = User::where('id', $userId)->exists();

        if($userExists === false) {
            ServerLog::log('user does\'n exist');
            $this->addNewUser($userId);
            $bot->sendMessage($userId, "Boas vindas ao Palavreco!!\nEnvie /start para começar um novo jogo");
            return;
        }

        $date = date('Y-m-d');
        $gameExists = Game::where('user_id', $userId)
            ->where('word_date', $date)
            ->exists();
                   
        if($gameExists === false) {
            ServerLog::log('game does\'n exist');
            $this->startNewGame($userId, $date);
            $bot->sendMessage($userId, "Jogo iniciado, qual o seu primeiro chute?");
            return;
        }

        ServerLog::log('user and game exist');
        $bot->sendMessage($userId, "*Placeholder\nSituação atual do jogo");
        
    }

    public function addNewUser($userId) {
        ServerLog::log('creating new user: '.$userId);
        $user = new User();
        $user->id = $userId;
        $user->subscribed = false;
        $user->subscription_hour = null;
        $user->save();
    }

    public function startNewGame($userId, $date) {
        $season = Season::where('from', '<=', $date)
            ->where('to', '>=', $date)
            ->first();
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