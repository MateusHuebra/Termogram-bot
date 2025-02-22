<?php

namespace App\Updates\Commands;

use App\Models\User;
use Carbon\Carbon;

class History extends Command {

    public function run() {
        $this->dieIfUnallowedChatType(['private']);
        if($this->getUserId() != env('TG_MYID')) {
            return;
        }
        
        $users = User::orderBy('score','desc')->limit(10)->get();
        foreach($users as $user) {
            $text = "Histórico de $user->first_name - $user->id:";
            $games = $user->games()->orderBy("word_date","desc")->limit(20)->get();
            foreach($games as $game) {
                $date = Carbon::parse($game->word_date)->format('d/m/y');
                $result = $game->won_at ? "venceu na tentativa $game->won_at" : 'perdeu';
                $text .= PHP_EOL."$date - $result";
            }
            $this->sendMessage($text);
        }
    }

}