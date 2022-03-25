<?php

namespace App\Updates\Commands;

use App\Models\Game;
use App\Models\Word;
use App\Services\ServerLog;
use App\Services\TextString;
use Illuminate\Support\Facades\File;
use App\Models\Attempt as AttemptModel;
use App\Services\FontHandler;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

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

        $attempts = AttemptModel::byUser($userId)->get();
        $render = $this->getGameRender($attempt, $word->value, $attempts);
        $render = FontHandler::replace($render);
        $attemptNumber = $this->addNewAttempt($userId, $attempt);
        $keyboard = null;

        if($attempt == $word->value) {
            ServerLog::log("game won by {$game->user_id} at attempt {$attemptNumber}");
            $keyboard = $this->getShareButton($render, $attemptNumber);
            $render = $this->endGame($game, $render, $word, $attemptNumber);
        } else if($attemptNumber>=6) {
            ServerLog::log("game lost by {$game->user_id} at attempt {$attemptNumber}");
            $keyboard = $this->getShareButton($render, 'X');
            $render = $this->endGame($game, $render, $word);
        }

        $bot->sendMessage($userId, $render, null, false, null, $keyboard);

        // tests:
        /*
        $photo = new CURLFile(storage_path('app/test.png'), 'image/png');
        $bot->setCurlOption(CURLOPT_TIMEOUT, 10);
        $bot->sendPhoto($userId, $photo);
        */
    }

    public function getShareButton(string $render, $attemptNumber) {
        $date = date('d/m/Y');
        $share = "{$date} - {$attemptNumber} / 6\n\n";
        $share.= FontHandler::hide($render);
        return new InlineKeyboardMarkup([
            [
                [
                    'text' => TextString::get('game.share_telegram'),
                    'url' => 'https://t.me/share/url?url=t.me/TermogramBot&text='.$share
                ]
            ],
            [
                [
                    'text' => TextString::get('game.share_twitter'),
                    'url' => 'https://twitter.com/intent/tweet?text=Joguei t.me/TermogramBot'.PHP_EOL.$share
                ]
            ],
            [
                [
                    'text' => TextString::get('game.share_whatsapp'),
                    'url' => 'https://api.whatsapp.com/send?text=Joguei t.me/TermogramBot'.PHP_EOL.$share
                ]
            ]
        ]);
    }

    public function endGame(Game $game, string $render, $word, $wonAt = null) {
        $game->ended = 1;
        $game->won_at = $wonAt;
        $game->save();

        if($wonAt) {
            $render.= TextString::get('game.won');
        } else {
            $render.= TextString::get('game.lost').$word->value;
        }

        return $render;
    }

    public function getCurrentAttempt($update) {
        return strtoupper($update->getMessage()->getText());
    }

    public function addNewAttempt($userId, string $word) {
        $number = AttemptModel::byUser($userId)->count() + 1;
        $date = date('Y-m-d');
        ServerLog::log('creating attempt '.$number.' in '.$date.' for '.$userId.': '.$word);

        $attempt = new AttemptModel();
        $attempt->user_id = $userId;
        $attempt->word_date = $date;
        $attempt->number = $number;
        $attempt->word = $word;
        $attempt->save();

        return $number;
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
            $lines[] = $this->getLineRender($attempt->word, $word);
        }
        $lines[] = $this->getLineRender($currentAttempt, $word);
        return implode(PHP_EOL, $lines);
    }

    public function getLineRender(string $attempt, string $word) {
        ServerLog::log("- - getLineRender - {$attempt} > {$word}");
        $letters = [];
        $attemptLetters = str_split($attempt);
        $wordLetters = str_split($word);

        if($attempt==$word) {
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
        ServerLog::log("- fillCorrects");
        $letters = [];
        foreach($attemptLetters as $letterPosition => $letter) {
            if($letter===$wordLetters[$letterPosition]) {
                ServerLog::log("{$letter} === {$wordLetters[$letterPosition]}");
                $letters[$letterPosition] = '['.$letter.']';
                continue;
            }
            ServerLog::log("{$letter} !!! {$wordLetters[$letterPosition]}");
        }
        ServerLog::printR($letters);
        return $letters;
    }

    public function fillDisplacedsAndWrongs(array $attemptLetters, array $wordLetters, array $letters) {
        ServerLog::log("- fillDisplacedsAndWrongs");
        $wordLetters = array_diff_key($wordLetters, $letters);
        foreach($attemptLetters as $letterPosition => $letter) {
            if(isset($letters[$letterPosition])) {
                continue;
            }
            $key = array_search($letter, $wordLetters);
            if($key !== false) {
                $letters[$letterPosition] = '{'.$letter.'}';
                unset($wordLetters[$key]);
                continue;
            }
            $letters[$letterPosition] = '('.$letter.')';
        }
        ksort($letters);
        ServerLog::printR($letters);
        return $letters;
    }

}