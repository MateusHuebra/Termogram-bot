<?php

namespace App\Commands;

use App\Models\Game;
use App\Models\User;
use App\Models\Word;
use App\Services\ServerLog;
use App\Services\TextString;
use Illuminate\Support\Facades\File;
use App\Models\Attempt as AttemptModel;

class Attempt extends Command {

    public function run($update, $bot) {
        ServerLog::log('Attempt > run');
        $userId = $this->getUserId($update);
        $game = Game::byUser($userId)->first();

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

        $attempt = $this->getCurrentAttempt($update);

        if($invalidationString = $this->isInvalidWord($attempt)) {
            ServerLog::log('invalid word: '.$invalidationString);
            $bot->sendMessage($userId, TextString::get($invalidationString));
            return;
        }

        $word = Word::today()->first();

        if($attempt == $word) {
            ServerLog::log('game won');

        }

        $attempts = AttemptModel::byUser($userId)->get();
        $render = $this->getGameRender($attempt, $word, $attempts);

        $bot->sendMessage($userId, $render);

    }

    public function getCurrentAttempt($update) {
        return strtoupper($update->getMessage()->getText());
    }

    public function isInvalidWord(string $word) {
        if(!preg_match('/^[A-z]*$/', $word)) {
            return 'game.invalid_characters';
        }

        if(strlen($word) != 5) {
            return 'game.invalid_size';
        }

        $json = File::get(__DIR__.'/../../resources/words.json');
        $words = json_decode($json);
        
        if(!in_array($word, $words)) {
            return 'game.invalid_word';
        }

        return false;
    }

    public function getGameRender(string $currentAttempt, string $word, $attempts) {
        $lines = [];
        foreach ($attempts as $attempt) {
            $lines[] = $this->getLineRender($attempt, $word);
        }
        $lines[] = $this->getLineRender($currentAttempt, $word);
        return implode(PHP_EOL, $lines);
    }

    public function getLineRender(string $attempt, string $word) {
        $letters = [];
        $attemptLetters = str_split($attempt);
        $wordLetters = str_split($word);

        if($attempt===$word) {
            $letters = array_map(function($letter) {
                return '['.$letter.']';
            }, $wordLetters);
        } else {
            $letters = $this->fillCorrects($attemptLetters, $wordLetters);
            $letters = $this->fillDisplacedsAndWrongs($attemptLetters, $wordLetters, $letters);
        }

        return implode(' ', $letters);
    }

    public function fillCorrects(array $attemptLetters, array $wordLetters) {
        $letters = [];
        foreach($attemptLetters as $letterPosition => $letter) {
            if($letter===$wordLetters[$letterPosition]) {
                $letters[$letterPosition] = '['.$letter.']';
            }
        }
        return $letters;
    }

    public function fillDisplacedsAndWrongs(array $attemptLetters, array $wordLetters, array $letters) {
        $wordLetters = array_diff_key($wordLetters, $letters);
        $letters = [];
        foreach($attemptLetters as $letterPosition => $letter) {
            if(isset($letters[$letterPosition])) {
                continue;
            }
            if($key = array_search($letter, $wordLetters)) {
                $letters[$letterPosition] = '{'.$letter.'}';
                unset($wordLetters[$key]);
                continue;
            }
            $letters[$letterPosition] = '('.$letter.')';
        }
        return $letters;
    }

}