<?php

namespace App\Updates\Commands;

use App\Services\ServerLog;
use TelegramBot\Api\Client;

class Factory {

    static function buildCommand($update) {
        ServerLog::log('Factory > buildCommand');
        $command = self::getCommand($update);

        if($command=='start') {
            return new Start();

        } else if($command=='attempt') {
            return new Attempt();

        } else if($command=='ajuda') {
            return new Help();

        } else if($command=='notificacoes') {
            return new Notifications();

        } else if($command=='ping') {
            return new Ping();

        } else if($command=='reset') {
            return new Reset();
            
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