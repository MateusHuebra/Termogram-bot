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

        } else if($command=='meincluir') {
            return new IncludeMe($update, $bot);

        } else if($command=='mencionar') {
            return new Mention($update, $bot);

        } else if($command=='estatisticas') {
            return new Statistics($update, $bot);

        } else if(in_array($command, ['help', 'ajuda'])) {
            return new Help($update, $bot);

        } else if($command=='ajuda_jogar') {
            return new HelpPlay($update, $bot);

        } else if($command=='ajuda_notificacoes') {
            return new HelpNotifications($update, $bot);

        } else if($command=='ajuda_leaderboard') {
            return new HelpLeaderboard($update, $bot);

        } else if($command=='ajuda_feedback') {
            return new HelpFeedback($update, $bot);

        } else if($command=='ping') {
            return new Ping($update, $bot);

        } else if($command=='reset') {
            return new Reset($update, $bot);

        } else if($command=='broadcast') {
            return new Broadcast($update, $bot);

        } else if($command=='feedback') {
            return new Feedback($update, $bot);

        } else if($command=='ban') {
            return new Ban($update, $bot);

        } else if($command=='devmsg') {
            return new DevMsg($update, $bot);

        } else if($command=='usermsg') {
            return new UserMsg($update, $bot);

        } else if($command=='gerar') {
            return new Generate($update, $bot);

        } else if($command=='imagemsecreta') {
            return new SecretImage($update, $bot);

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

        preg_match(Client::REGEXP.'m', $message->getText(), $matches);

        if (empty($matches)) {
            if($message->getReplyToMessage() && str_contains($message->getReplyToMessage()->getText(), '#feedback')) {
                return 'devmsg';
            } else if($message->getReplyToMessage() && str_contains($message->getReplyToMessage()->getText(), '#dev')) {
                return 'usermsg';
            } else {
                ServerLog::log('no matches, command: attempt');
                return 'attempt';
            }
        }

        ServerLog::log('command: '.$matches[1]);
        return $matches[1];
    }

}
