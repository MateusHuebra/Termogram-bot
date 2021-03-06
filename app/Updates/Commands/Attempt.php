<?php

namespace App\Updates\Commands;

use App\Models\Game;
use App\Models\Word;
use App\Services\ServerLog;
use App\Services\TextString;
use Illuminate\Support\Facades\File;
use App\Models\Attempt as AttemptModel;
use App\Models\User;
use App\Services\FontHandler;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class Attempt extends Command {

    public function run() {
        $this->dieIfUnallowedChatType(['private']);
        ServerLog::log('Attempt > run');
        $game = Game::byUser($this->getUserId())->first();

        if($game === null) {
            ServerLog::log('game does\'n exist');
            $this->sendMessage(TextString::get('game.no_game'));
            return;
        }

        if($game->ended) {
            ServerLog::log('game already ended');
            $this->sendMessage(TextString::get('game.already_ended'));
            return;
        }

        $attempt = $this->getCurrentAttempt();

        if($invalidationString = $this->isInvalidWord($attempt)) {
            ServerLog::log('invalid word: '.$invalidationString);
            $this->sendMessage(TextString::get($invalidationString));
            return;
        }

        $word = Word::today()->first();

        $attempts = AttemptModel::byUser($this->getUserId())->get();
        $render = $this->getGameRender($attempt, $word->value, $attempts);
        $render = FontHandler::replace($render);
        $attemptNumber = $this->addNewAttempt($attempt);
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

        $this->sendMessage($render, $keyboard);

        // tests:
        /*
        $photo = new CURLFile(storage_path('app/test.png'), 'image/png');
        $this->bot->setCurlOption(CURLOPT_TIMEOUT, 10);
        $this->bot->sendPhoto($this->getUserId(), $photo);
        */
    }

    private function getShareButton(string $render, $attemptNumber) {
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

    private function endGame(Game $game, string $render, $word, $wonAt = null) : string {
        $game->ended = 1;
        $game->won_at = $wonAt;
        $game->save();

        if($wonAt) {
            $data = $this->addScore($wonAt);
            $render.= TextString::get('game.won', $data);
        } else {
            $render.= TextString::get('game.lost', ['word' => $word->value]);
        }

        return $render;
    }

    private function addScore(int $wonAt) : array {
        $data['score'] = 7 - $wonAt;
        $user = User::find($this->getUserId());
        $user->score+= $data['score'];
        $user->save();
        $data['total_score'] = $user->score;
        return $data;
    }

    private function getCurrentAttempt() {
        return strtoupper($this->update->getMessage()->getText());
    }

    private function addNewAttempt(string $word) : int {
        $number = AttemptModel::byUser($this->getUserId())->count() + 1;
        $date = date('Y-m-d');
        ServerLog::log('creating attempt '.$number.' in '.$date.' for '.$this->getUserId().': '.$word);

        $attempt = new AttemptModel();
        $attempt->user_id = $this->getUserId();
        $attempt->word_date = $date;
        $attempt->number = $number;
        $attempt->word = $word;
        $attempt->save();

        return $number;
    }

    private function isInvalidWord(string $word) {
        if(!preg_match('/^[A-z]*$/', $word)) {
            return 'game.invalid_characters';
        }

        if(strlen($word) != 5) {
            return 'game.invalid_size';
        }

        $json = File::get(__DIR__.'/../../../resources/words.json');
        $words = json_decode($json);
        
        if(!in_array($word, $words)) {
            return 'game.invalid_word';
        }

        return false;
    }

    private function getGameRender(string $currentAttempt, string $word, $attempts) : string {
        $lines = [];
        foreach ($attempts as $attempt) {
            $lines[] = $this->getLineRender($attempt->word, $word);
        }
        $lines[] = $this->getLineRender($currentAttempt, $word);
        return implode(PHP_EOL, $lines);
    }

    private function getLineRender(string $attempt, string $word) : string {
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

    private function fillCorrects(array $attemptLetters, array $wordLetters) : array {
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

    private function fillDisplacedsAndWrongs(array $attemptLetters, array $wordLetters, array $letters) : array {
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