<?php

namespace App\Updates\Commands;

use App\Models\Game;
use App\Models\Season;
use App\Models\User;
use App\Models\Word;
use App\Services\ServerLog;
use App\Services\TextString;

class Start extends Command {

    public function run(bool $sendMessage = true) {
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
            if($sendMessage) {
                $this->sendMessage(TextString::get($resultString));
            }
            return;
        }

        if(Game::byUser($this->getUserId())->first()->ended) {
            ServerLog::log('game ended');
            $this->sendMessage(TextString::get('game.already_ended'));
            return;
        }

        ServerLog::log('game not ended');
        $this->sendMessage(TextString::get('placeholder.game_status'));
        return;
    }

    private function addNewUser() {
        ServerLog::log('creating new user: '.$this->getUserId());
        $user = new User();
        $user->id = $this->getUserId();
        $user->subscription_hour = rand(1,20);
        $user->username = $this->getUsername();
        $user->first_name = mb_substr($this->getFirstName(), 0, 16);
        $user->status = 'actived';
        $user->save();
        ServerLog::log('new user created');
    }

    public function startNewGame() : string {
        $date = date('Y-m-d');
        $season = Season::current()->first();
        $word = Word::today()->first();

        if($word === null) {
            return 'error.no_todays_word';
        }
        
        ServerLog::log('saving info for '.$this->getUserId().' as '.$this->getFirstName());
        $user = User::find($this->getUserId());
        $user->username = $this->getUsername();
        if(!$user->is_banned) {
            $user->first_name = mb_substr($this->getFirstName(), 0, 16);
        }
        $user->status = 'actived';
        $user->last_time_notified = date('Y-m-d');
        $user->save();

        if($user->is_blocked) {
            return 'ban.error';
        }

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