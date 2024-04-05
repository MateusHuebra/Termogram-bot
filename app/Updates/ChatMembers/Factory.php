<?php

namespace App\Updates\ChatMembers;

use App\Services\ServerLog;

class Factory {

    static function buildChatMembers($update, $bot) {
        ServerLog::log('Factory > buildChatMember');
        $type = self::getChatMemberType($update);

        if($type=='new_members') {
            return new NewMembers($update, $bot);

        } else if($type=='bot_removed') {
            return new BotRemoved($update, $bot);

        } else if($type=='left_member') {
            return new LeftMember($update, $bot);

        } else {
            return false;
        }
    }

    private static function getChatMemberType($update) {
        $message = $update->getMessage();

        if($message->getNewChatMembers()) {
            return 'new_members';
        }
        
        if($message->getLeftChatMember()) {
            if($message->getLeftChatMember()->getUsername()==="TermogramBot") {
                return 'bot_removed';
            }
            return 'left_member';
        }
    }

}
