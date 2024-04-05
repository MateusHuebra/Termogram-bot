<?php

namespace App\Updates;

use App\Updates\Commands\Factory as CommandsFactory;
use App\Updates\CallbackQueries\Factory as CallbackQueriesFactory;
use App\Updates\ChatMembers\Factory as ChatMembersFactory;
use App\Services\ServerLog;

class Factory {

    static function buildUpdate($update, $bot) {
        ServerLog::$updateId = $update->getUpdateId();
        ServerLog::log('Factory > buildUpdate');
        $type = self::getUpdateType($update);

        if($type=='command') {
            return CommandsFactory::buildCommand($update, $bot);

        } else if($type=='callback_query') {
            return CallbackQueriesFactory::buildCallbackQuery($update, $bot);

        } else if($type=='chat_members') {
            return ChatMembersFactory::buildChatMembers($update, $bot);

        } else {
            return false;
        }
        
    }

    private static function getUpdateType($update) {
        $message = $update->getMessage();
        $callbackQuery = $update->getCallbackQuery();

        if (!is_null($message)) {
            if($message->getNewChatMembers() || $message->getLeftChatMember()) {
                return 'chat_members';
            }
            return 'command';
        }

        if (!is_null($callbackQuery)) {
            return 'callback_query';
        }

        return null;
    }

}