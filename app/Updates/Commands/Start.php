<?php

namespace App\Updates\Commands;

use App\Models\Game;
use App\Models\Season;
use App\Models\User;
use App\Models\Word;
use App\Services\ServerLog;
use App\Services\TextString;

class Start extends Command {

    public function run() {
        $this->dieIfUnallowedChatType(['private'], 'start_in_private');
        ServerLog::log('Start > run');

        if(User::ofId($this->getUserId())->exists() === false) {
            ServerLog::log('user does\'n exist');
            $this->addNewUser($this->getUserId());
            $this->sendMessage(TextString::get('start.welcome'));
            return;
        }

        if(Game::byUser($this->getUserId())->exists() === false) {
            ServerLog::log('game does\'n exist');
            $resultString = $this->startNewGame($this->getUserId());
            $this->sendMessage(TextString::get($resultString));
            return;
        }

        if(Game::byUser($this->getUserId())->first()->ended) {
            ServerLog::log('game ended');
            $this->sendMessage(TextString::get('game.already_ended'));
            return;
        }

        ServerLog::log('game not ended');
        $this->sendMessage(TextString::get('placeholder.game_status'));

    }

    private function addNewUser() {
        ServerLog::log('creating new user: '.$this->getUserId());
        $user = new User();
        $user->id = $this->getUserId();
        $user->subscription_hour = 0;
        $user->first_name = mb_substr($this->getFirstName(), 0, 16);
        $user->save();
        ServerLog::log('new user created');
    }

    private function startNewGame() : string {
        $date = date('Y-m-d');
        $season = Season::current()->first();
        $word = Word::today()->first();

        if($word === null) {
            return 'error.no_todays_word';
        }
        ServerLog::log('saving first name for '.$this->getUserId().' as '.$this->getFirstName());
        $user = User::find($this->getUserId());
        $user->first_name = mb_substr($this->getFirstName(), 0, 16);
        $user->save();
        ServerLog::log('creating new game for '.$this->getUserId().' in '.$date.' from season '.$season->name);
        $game = new Game();
        $game->user_id = $this->getUserId();
        $game->word_date = $word->date;
        $game->won_at = null;
        $game->ended = false;
        $game->season_id = $season->id;
        $game->save();
        ServerLog::log('new game created');
        return 'start.game_started';
    }

}