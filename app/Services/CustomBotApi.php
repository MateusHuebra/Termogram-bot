<?php

namespace App\Services;

use TelegramBot\Api\BotApi;

class CustomBotApi extends BotApi {

    public function setMessageReaction($chatId, $messageId, $emoji = null)
    {
        return $this->call('setMessageReaction', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'reaction' => (is_null($emoji)) ? null : json_encode([
                ['type' => 'emoji', 'emoji' => $emoji]
            ]),
        ]);
    }

    public function sendMessage($chatId, $text, $parseMode = null, $disablePreview = false, $replyToMessageId = null, $replyMarkup = null, $disableNotification = false) {
        try {
            return parent::sendMessage($chatId, $text, $parseMode, $disablePreview, $replyToMessageId, $replyMarkup, $disableNotification);
        } catch (\Exception $e) {
            try {
                return parent::sendMessage($chatId, $text, $parseMode, $disablePreview, $replyToMessageId, $replyMarkup, $disableNotification);
            } catch (\Exception $e) {
                return parent::sendMessage($chatId, $text, $parseMode, $disablePreview, $replyToMessageId, $replyMarkup, $disableNotification);
            }
        }
    }

}