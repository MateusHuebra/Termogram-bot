<?php

namespace App\Updates\Commands;

use App\Services\ServerLog;
use TelegramBot\Api\Client;

class Factory {

    static function buildCommand($update, $bot) {
        ServerLog::log('Factory > buildCommand');
        $command = self::getCommand($update);

        if(in_array($command, ['start', 'jogar'])) {
            return new Start($update, $bot);

        } else if($command=='attempt') {
            return new Attempt($update, $bot);

        } else if($command=='notificacoes') {
            return new Notifications($update, $bot);

        } else if($command=='leaderboard') {
            return new Leaderboard($update, $bot);

        } else if($command=='estatisticas') {
            return new Statistics($update, $bot);

        } else if(in_array($command, ['help', 'ajuda'])) {
            return new Help($update, $bot);

        } else if($command=='ajuda_jogar') {
            return new HelpPlay($update, $bot);

        } else if($command=='ajuda_notificacoes') {
            return new HelpNotifications($update, $bot);

        } else if($command=='ping') {
            return new Ping($update, $bot);

        } else if($command=='reset') {
            return new Reset($update, $bot);

        } else {
            return false;
        }

    }

    private static function getCommand($update) {
        ServerLog::log('Factory > getCommand');

        $message = $update->getMessage();
        if (is_null($message) || !strlen($message->getText())) {
            ServerLog::log('empty message');
            return false;
        }

        preg_match(Client::REGEXP, $message->getText(), $matches);

        if (empty($matches)) {
            ServerLog::log('no matches, command: attempt');
            return 'attempt';
        }

        ServerLog::log('command: '.$matches[1]);
        return $matches[1];
    }

}
