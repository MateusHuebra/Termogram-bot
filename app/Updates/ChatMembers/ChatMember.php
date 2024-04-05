<?php

namespace App\Updates\ChatMembers;

use App\Services\ServerLog;
use App\Services\TextString;
use App\Updates\Update;
use Exception;

abstract class ChatMember extends Update {
    
    protected function getNewChatMembers() {
        if(!isset($this->newChatMembers)) {
            $this->newChatMembers = $this->update->getMessage()->getNewChatMembers();
        }
        return $this->newChatMembers;
    }
    
    protected function getLeftChatMember() {
        if(!isset($this->leftChatMember)) {
            $this->leftChatMember = $this->update->getMessage()->getLeftChatMember();
        }
        return $this->leftChatMember;
    }
}
