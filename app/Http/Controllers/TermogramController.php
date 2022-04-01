<?php

namespace App\Http\Controllers;

use App\Console\Scheduled\NotificateSubscribedUsers;
use App\Models\User;
use App\Services\ServerLog;
use App\Services\TextString;
use App\Updates\Factory;
use Exception;
use Illuminate\Http\Request;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Update;

class TermogramController extends Controller
{
    
    public function listen(Request $request) {

        ServerLog::log('BotController > listen');
        $client = new Client(env('TG_TOKEN'));
        $bot = new BotApi(env('TG_TOKEN'));
        $update = Update::fromResponse(BotApi::jsonValidate($client->getRawBody(), true));
        ServerLog::log('update json: '.$client->getRawBody());

        $updateHandler = Factory::buildUpdate($update, $bot);
        if($updateHandler) {
            $updateHandler->run();
        }

    }

    public function testForcedNotification(Request $request) {
        if($request->token!=env('TG_TOKEN')){
            return;
        }

        $bot = new BotApi(env('TG_TOKEN'));
        NotificateSubscribedUsers::tryToSendMessage($bot, env('TG_MYID'));
    }

    public function broadcast(Request $request) {

        if($request->token!=env('TG_TOKEN') || !isset($request->string)){
            return;
        }

        if(is_null($request->string)) {
            return;
        }

        $bot = new BotApi(env('TG_TOKEN'));

        $users = User::all();
        foreach ($users as $user) {
            try {
                $bot->sendMessage($user->id, TextString::get('broadcast.'.$request->string));
            } catch (Exception $e) {
                $bot->sendMessage(env('TG_MYID'), "error on trying to broadcast to {$user->id}: {$e->getMessage()}");
            }
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
