<?php

namespace App\Http\Controllers;

use App\Services\ServerLog;
use App\Updates\Factory;
use Illuminate\Http\Request;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Types\Update;

class TermogramController extends Controller
{
    
    function listen(Request $request) {
        ServerLog::log('BotController > listen');
        $bot = new \TelegramBot\Api\Client(env('TG_TOKEN'));
        $update = Update::fromResponse(BotApi::jsonValidate($bot->getRawBody(), true));

        $updateHandler = Factory::buildUpdate($update);
        if($updateHandler) {
            $updateHandler->run($update, $bot);
        }

    }

}

        //$bot->getChe

        /*
        $bot->on(function (Update $update) use ($bot) {
            $bot->sendMessage($update->getMessage()->getChat()->getId(), 'pong');
        }, function() {
            return true;
        });
        */
        
        //$bot->sendMessage(, 'test');


        /*
        try {
            $bot = new \TelegramBot\Api\Client(env('TG_TOKEN'));
            
            $bot->command('ping', function ($message) use ($bot) {
                $bot->sendMessage($message->getChat()->getId(), 'pong!', $message->getId());
            });
            
            $bot->run();
        } catch (\TelegramBot\Api\Exception $e) {
            $e->getMessage();
        }
        */
