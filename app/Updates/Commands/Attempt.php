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

    private $winStreak = 0;

    public function run() {
        $this->dieIfUnallowedChatType(['private']);
        ServerLog::log('Attempt > run');
        $game = Game::byUser($this->getUserId())->first();

        if($game === null) {
            $start = new Start($this->update, $this->bot, true);
            $start->chatType = $this->getChatType();
            $start->userId = $this->getUserId();
            $start->firstName = $this->getFirstName();
            $start->username = $this->getUsername();
            $start->run(false);
            $game = Game::byUser($this->getUserId())->first();
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
        $render = $this->getGameRender($attempt, $word->value, $attempts, User::find($this->getUserId())->alt_text);
        $render = FontHandler::replace($render);
        $attemptNumber = $this->addNewAttempt($attempt);
        $keyboard = null;

        if($attempt == $word->value) {
            ServerLog::log("game won by {$game->user_id} at attempt {$attemptNumber}");
            $this->winStreak++;
            $this->calculateWinStreak();
            $keyboard = $this->getShareButton($render, $attemptNumber);
            $render = $this->endGame($game, $render, $word, $attemptNumber);
        } else if($attemptNumber>=6) {
            ServerLog::log("game lost by {$game->user_id} at attempt {$attemptNumber}");
            $keyboard = $this->getShareButton($render, 'X');
            $render = $this->endGame($game, $render, $word);
        } else {
            $render = $this->renderKeyboard($render);
        }

        $this->sendMessage($render, $keyboard, false, 'HTML');
    }

    private function calculateWinStreak() {
        ServerLog::log("calculateWinStreak()", false);
        $gamesQuery = Game::where('user_id', $this->getUserId())
            ->whereNotNull('won_at')
            ->orderBy('word_date', 'desc');
        if($gamesQuery->count() == 0) {
            ServerLog::log("\$winStreak = 1");
            return;
        }
        $games = $gamesQuery->get();
        $winStreak = $this->winStreak;
        $last_date = date('Y-m-d', strtotime('- 1 days'));

        for ($i=0; isset($games[$i]) && $games[$i]->word_date == $last_date; $i++) {
            $last_date = date('Y-m-d', strtotime($games[$i]->word_date.' - 1 days'));
            $winStreak++;
        }

        ServerLog::log("\$winStreak = $winStreak");
        $this->winStreak = $winStreak;
    }

    private function getShareButton(string $render, $attemptNumber) {
        $date = date('d/m/y');
        $share = "{$date} - {$attemptNumber} / 6";
        if($this->winStreak > 1){
            $share.= ' 🔥 '.$this->winStreak;
        }
        $share.= "\n\n".FontHandler::hide($render);
        $shareTelegram = 'https://t.me/share/url?url='.rawurlencode('t.me/TermogramBot').'&text='.rawurlencode($share);
        $shareOthers = rawurlencode('Joguei t.me/TermogramBot'.PHP_EOL.$share);
        return new InlineKeyboardMarkup([
            [
                [
                    'text' => TextString::get('game.share_telegram'),
                    'url' => $shareTelegram
                ]
            ],
            [
                [
                    'text' => TextString::get('game.share_twitter'),
                    'url' => 'https://twitter.com/intent/tweet?text='.$shareOthers
                ]
            ],
            [
                [
                    'text' => TextString::get('game.share_whatsapp'),
                    'url' => 'https://api.whatsapp.com/send?text='.$shareOthers
                ]
            ]
        ]);
    }

    private function endGame(Game $game, string $render, $word, $wonAt = null) : string {
        $game->ended = 1;
        $game->won_at = $wonAt;
        $game->save();

        $data = $this->getScore($wonAt);
        if($wonAt) {
            $render.= TextString::get('game.won', $data);
        } else {
            $data['word'] = $word->value;
            $render.= TextString::get('game.lost', $data);
        }

        return $render;
    }

    private function getScore($wonAt) : array {
        $user = User::find($this->getUserId());
        if($user->is_banned) {
            $data['score'] = 'banido';
            $user->score = 0;
        } else if($wonAt) {
            $data['score'] = 7 - $wonAt;
            $user->score+= $data['score'];
        }
        if($user->record_streak < $this->winStreak) {
            $user->record_streak = $this->winStreak;
        }
        $user->current_streak = $this->winStreak;
        $user->save();
        $data['streak'] = $user->current_streak;
        $data['record'] = $user->record_streak;
        $data['total_score'] = $user->score;
        return $data;
    }

    private function getCurrentAttempt() {
        return strtoupper($this->getMessageText());
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

        $attemptAlreadyExists = AttemptModel::byUser($this->getUserId())
            ->where('word', $word)
            ->exists();
        if($attemptAlreadyExists) {
            return 'game.repeated_word';
        }

        $json = File::get(__DIR__.'/../../../resources/words.json');
        $words = json_decode($json);
        if(!in_array($word, $words)) {
            return 'game.invalid_word';
        }

        return false;
    }

    private function getGameRender(string $currentAttempt, string $word, $attempts, bool $altText) : string {
        $lines = [];
        foreach ($attempts as $attempt) {
            $lines[] = $this->getLineRender($attempt->word, $word);
        }
        $lines[] = $this->getLineRender($currentAttempt, $word, $altText);
        return implode(PHP_EOL, $lines);
    }

    private function getLineRender(string $attempt, string $word, bool $altText) : string {
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

        $result = implode(' ', $letters);
        if($altText) {
            foreach ($letters as $letter) {
                if(str_contains($letter, '(')) {
                    $alt = TextString::get('alt_text.wrong');
                } else if(str_contains($letter, '{')) {
                    $alt = TextString::get('alt_text.displaced');
                } else {
                    $alt = TextString::get('alt_text.correct');
                }
                $result.= PHP_EOL.$letter.' '.$alt;
            }
        }

        return $result;
    }

    private function renderKeyboard(string $render) {
        ServerLog::log("- renderKeyboard");
        $k = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        //shown order = ['Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P', 'A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'Z', 'X', 'C', 'V', 'B', 'N', 'M'];

        foreach ($k as $index => $key) {
            $right = FontHandler::replace("[{$key}]");
            $displaced = FontHandler::replace("{{$key}}");
            $wrong = FontHandler::replace("({$key})");
            if(str_contains($render, $right)) {
                $returnedLetter = $right;
            } else if(str_contains($render, $displaced)) {
                $returnedLetter = $displaced;
            } else if(str_contains($render, $wrong)) {
                $returnedLetter = "<tg-spoiler><u>{$wrong}</u></tg-spoiler>";
            } else {
                $returnedLetter = $wrong;
            }
            $k[$index] = $returnedLetter;
        }
        $return = "\n\n\n{$k[16]} {$k[22]} {$k[4]} {$k[17]} {$k[19]} {$k[24]} {$k[20]} {$k[8]} {$k[14]} {$k[15]}\n";
        $return.= "   {$k[0]} {$k[18]} {$k[3]} {$k[5]} {$k[6]} {$k[7]} {$k[9]} {$k[10]} {$k[11]}\n";
        $return.= "        {$k[25]} {$k[23]} {$k[2]} {$k[21]} {$k[1]} {$k[13]} {$k[12]}";

        return ($render.$return);
    }

    private function fillCorrects(array $attemptLetters, array $wordLetters) : array {
        ServerLog::log("- fillCorrects");
        $letters = [];
        foreach($attemptLetters as $letterPosition => $letter) {
            if($letter===$wordLetters[$letterPosition]) {
                //ServerLog::log("{$letter} === {$wordLetters[$letterPosition]}");
                $letters[$letterPosition] = '['.$letter.']';
                continue;
            }
            //ServerLog::log("{$letter} !!! {$wordLetters[$letterPosition]}");
        }
        //ServerLog::printR($letters);
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
        //ServerLog::printR($letters);
        return $letters;
    }

}
