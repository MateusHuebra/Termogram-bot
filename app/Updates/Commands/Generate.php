<?php

namespace App\Updates\Commands;

use App\Models\Word;
use App\Services\ServerLog;
use App\Services\TextString;
use Illuminate\Support\Facades\File;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class Generate extends Command {

    public function run() {
        $this->dieIfUnallowedChatType(['private']);
        if($this->getUserId() != env('TG_MYID')) {
            return;
        }

        ServerLog::log("Generate > run");
        $json = File::get(__DIR__.'/../../../resources/words.json');
        $words = json_decode($json);

        $wordsCount = count($words);
        $word = $words[rand(0, $wordsCount-1)];
        $lastTimeUsed = $this->getLastTimeUsed($word);
        ServerLog::log("{$wordsCount} words. Selected: {$word}. Last time used: {$lastTimeUsed}");

        $text = TextString::get('generate.generated', [
            'word' => $word,
            'last' => $lastTimeUsed
        ]);
        $keyboard = self::getKeyboard($word);
        $this->sendMessage($text, $keyboard);
    }

    private function getLastTimeUsed($word) {
        $lastMatch = Word::lastTimeUsed($word)->first();
        if($lastMatch === null) {
            return TextString::get('generate.never');
        }
        return self::parseDate($lastMatch->date);
    }

    static function getKeyboard($word) : InlineKeyboardMarkup {
        $accept = TextString::get('generate.accept');
        $buttons[] = [
            [
                'text' => $accept,
                'callback_data' => 'generate:'.$word
            ]
        ];

        return new InlineKeyboardMarkup($buttons);
    }

    static function parseDate($date) : string {
        $date = date_create($date);
        $date = date_format($date, 'd/m/Y');
        return $date;
    }

}
