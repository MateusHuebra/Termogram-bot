<?php

namespace App\Updates\Commands;

use App\Services\ServerLog;
use App\Services\TextString;
use App\Services\Leaderboard as LeaderboardService;
use Exception;

class Leaderboard extends Command {

    private $yourPosition = null;

    public function run($page = 0, $returnBoard = false) {
        ServerLog::log('Leaderboard > run');
        $LBservice = new LeaderboardService();

        if($this->getChatType() == 'private') {
            $users = $LBservice->getUsers();
            $type = 'global';
        } else if (in_array($this->getChatType(), ['group', 'supergroup'])) {
            $membersList = $LBservice->getMembersList($this->getChatId(), $this->bot);
            $users = $LBservice->getUsers($membersList);
            $type = 'group';
        }
        
        $boardData = $LBservice->renderBoard($users, $type, $page, $this->getUserId());
        $keyboard = $LBservice->getPaginationKeyboard($page, $boardData['keyboard']);
        if($returnBoard) {
            $boardData['keyboard'] = $keyboard;
            return $boardData;
        }
        $board = $boardData['text'];
        
        if ($this->yourPosition && $boardData['keyboard']) {
            $this->bot->sendMessage($this->getChatId(), $board, 'MarkdownV2', false, null, $keyboard);
            try {
                $this->bot->sendMessage($this->getChatId(), TextString::get('leaderboard.yours', ['position' => $this->yourPosition]), 'MarkdownV2', false, $this->getMessageId());
            } catch(Exception $e) {
                //
            }
        } else {
            try {
                $this->bot->sendMessage($this->getChatId(), $board, 'MarkdownV2', false, $this->getMessageId(), $keyboard);
            } catch(Exception $e) {
                $this->bot->sendMessage($this->getChatId(), $board, 'MarkdownV2', false, null, $keyboard);
            }
        }
        
    }

}
