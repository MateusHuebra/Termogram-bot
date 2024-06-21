<?php

namespace App\Updates\Commands;
use App\Models\User;
use App\Services\TextString;

class AltText extends Command {

    public function run() {
        $this->dieIfUnallowedChatType(['private']);

        $user = User::find($this->getUserId());
        $user->first_name = mb_substr($this->getFirstName(), 0, 16);
        $user->alt_text = !$user->alt_text;
        $user->save();
        
        if($user->alt_text) {
            $value = TextString::get('notification.on');
        } else {
            $value = TextString::get('notification.off');
        }
        
        $text = TextString::get('alt_text.desc', [
            'value' => $value
        ]);

        $this->bot->sendMessage($this->getChatId(), $text);
    }

}