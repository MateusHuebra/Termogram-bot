<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ServerLog;
use App\Services\TextString;
use App\Updates\Factory;
use Illuminate\Http\Request;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Update;

class TermogramController extends Controller
{
    
    function listen(Request $request) {

        ServerLog::log('BotController > listen');
        $bot = new Client(env('TG_TOKEN'));
        $update = Update::fromResponse(BotApi::jsonValidate($bot->getRawBody(), true));
        ServerLog::log('update json: '.$bot->getRawBody());

        $updateHandler = Factory::buildUpdate($update);
        if($updateHandler) {
            $updateHandler->run($update, $bot);
        }

    }

    function broadcast(Request $request) {

        if($request->token!=env('TG_TOKEN') || !isset($request->string)){
            return;
        }

        if(is_null($request->string)) {
            return;
        }

        $bot = new BotApi(env('TG_TOKEN'));

        $users = User::all();
        foreach ($users as $user) {
            $bot->sendMessage($user->id, TextString::get('broadcast.'.$request->string));
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
