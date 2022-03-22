<?php

namespace App\Commands;

use App\Services\ServerLog;
use TelegramBot\Api\Client;

class Factory {

    static function buildCommand($update, $bot) {
        ServerLog::log('Factory > buildCommand');
        $command = self::getCommand($update);

        if($command=='start') {
            return new Start();

        } else if($command=='help') {
            return new Help();

        } else if($command=='ping') {
            return new Ping();
            
        } else {
            return false;
        }
        
        /*
        $bot->command('ping', function ($message) use ($bot) {
            
        });
        */
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
            ServerLog::log('no matches');
            return false;
        } 

        ServerLog::log('command: '.$matches[1]);
        return $matches[1];
    }

}