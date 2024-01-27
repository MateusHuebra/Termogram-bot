<?php

namespace App\Updates\CallbackQueries;

use App\Models\Word;
use App\Services\ServerLog;
use App\Services\TextString;

class Generate extends CallbackQuery {

    public function run() {
        ServerLog::log('Generate > run');
        $word = $this->getData('generate');

        $lastDBWord = Word::last()->first();
        if($lastDBWord === null) {
            $lastDate = date_create(date());
        } else {
            $lastDate = date_create($lastDBWord->date);
        }
        $lastDate->modify('+1 day');
        $newDate = date_format($lastDate, 'Y-m-d');

        $newWord = new Word();
        $newWord->value = $word;
        $newWord->date = $newDate;
        $newWord->save();

        ServerLog::log("saved word {$word} - {$newDate}");
        $text = TextString::get('generate.saved', [
            'word' => $word,
            'date' => date_format($lastDate, 'd/m/Y')
        ]);

        if($this->getMessageId() && $this->getChatId()) {
            $this->bot->editMessageText($this->getChatId(), $this->getMessageId(), $text);
        } else {
            $this->bot->sendMessage($this->getUserId(), $text);
        }
    }

}
