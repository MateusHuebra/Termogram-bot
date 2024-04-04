<?php

namespace App\Updates\CallbackQueries;

use App\Models\User;
use App\Services\ServerLog;
use App\Services\TextString;
use App\Updates\Commands\Leaderboard as LeaderboardCommand;
use Exception;

class Leaderboard extends CallbackQuery {

    public function run() {
        ServerLog::log('Leaderboard callbackquery > run');
        try {
            $this->bot->answerCallbackQuery($this->getId(), TextString::get('settings.loading'));
        } catch(Exception $e) {
            // do nothing
        }
        $data = $this->getData('leaderboard');
        if($data == 'info') {
            return; // die
        }

        $leaderboard = new LeaderboardCommand($this->update, $this->bot, true);
        $leaderboard->chatType = $this->getChatType();
        $leaderboard->chat = $this->getChat();
        $leaderboard->userId = $this->getUserId();
        $data = $leaderboard->run($data, true);

        if($this->getMessageId() && $this->getChatId()) {
            try{
                $this->bot->editMessageText($this->getChatId(), $this->getMessageId(), $data['text'], 'MarkdownV2', true, $data['keyboard']);
            } catch(Exception $e) {
                // do nothing
            }
        }
    }

}
