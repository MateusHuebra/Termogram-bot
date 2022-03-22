<?php

namespace App\Commands;

use App\Models\Game;
use App\Models\User;
use App\Models\Word;
use App\Services\ServerLog;
use App\Services\TextString;
use Illuminate\Support\Facades\File;

class Attempt extends Command {

    public function run($update, $bot) {
        ServerLog::log('Attempt > run');
        $userId = $this->getUserId($update);
        $game = $this->getGame($userId);

        if($game === null) {
            ServerLog::log('game does\'n exist');
            $bot->sendMessage($userId, TextString::get('game.no_game'));
            return;
        }

        if($game->ended) {
            ServerLog::log('game already ended');
            $bot->sendMessage($userId, TextString::get('game.already_ended'));
            return;
        }

        $attempt = $this->getAttempt($update);

        if($invalidationString = $this->isInvalidWord($attempt)) {
            ServerLog::log('invalid word: '.$invalidationString);
            $bot->sendMessage($userId, TextString::get($invalidationString));
            return;
        }

        $word = $this->getWord();

        if($attempt == $word) {
            ServerLog::log('game won');

        }

        //$render = $this->getGameRender($attempt, $word);

    }

    public function getGame($userId) {
        $date = date('Y-m-d');
        return Game::where('user_id', $userId)
            ->where('word_date', $date)
            ->first();
    }

    public function getWord() {
        $date = date('Y-m-d');
        return Word::where('word_date', $date)->first();
    }

    public function getAttempt($update) {
        return strtoupper($update->getMessage()->getText());
    }


    public function isInvalidWord(string $word) {
        if(strlen($word) != 5) {
            return 'game.invalid_size';
        }

        if(!preg_match('/^[A-z]{5}$/', $word)) {
            return 'game.invalid_characters';
        }

        $json = File::get(__DIR__.'/../../resources/words.json');
        $words = json_decode($json);
        
        if(!in_array($word, $words)) {
            return 'game.invalid_word';
        }

        return false;
    }

    public function getGameRender(string $currentAttempt, string $word) {
        
    }

}