<?php

namespace App\Http\Controllers;

use App\Console\Scheduled\NotificateSubscribedUsers;
use App\Models\Game;
use App\Models\User;
use App\Services\Score;
use App\Services\ServerLog;
use App\Services\TextString;
use App\Updates\Factory;
use Exception;
use Illuminate\Http\Request;
use App\Services\CustomBotApi as BotApi;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Update;

class TermogramController extends Controller
{

    public function listen(Request $request) {

        ServerLog::log('start -----> BotController > listen');
        $client = new Client(env('TG_TOKEN'));
        $bot = new BotApi(env('TG_TOKEN'));
        $updateData = $client->getRawBody();
        $update = Update::fromResponse(BotApi::jsonValidate($updateData, true));
        ServerLog::log('update json: '.$updateData);

        $updateHandler = Factory::buildUpdate($update, $bot);
        if($updateHandler) {
            try {
                $updateHandler->run();
            } catch(Exception $e) {
                ServerLog::log('exception class: '.get_class($e));
                ServerLog::log('exception message: '.$e->getMessage());
                ServerLog::log("exception on main controller: \n\n".$e->getTraceAsString());
                $text = '#error: '.$updateHandler->getUpdateId().PHP_EOL.$e->getMessage();
                $bot->sendMessage(env('TG_MYID'), $text);
                echo PHP_EOL.$e->getFile().' line '.$e->getLine()."\n\n\n".$e->getTraceAsString();
            }
        }
        ServerLog::log('end <----- BotController > listen');

    }

    public function testForcedNotification(Request $request) {
        if($request->token!=env('TG_TOKEN')){
            return;
        }

        $bot = new BotApi(env('TG_TOKEN'));
        NotificateSubscribedUsers::tryToSendMessage($bot, env('TG_MYID'));
    }

    public function forceNotificationAll(Request $request) {
        if($request->token!=env('TG_TOKEN')){
            return;
        }

        $method = new NotificateSubscribedUsers();
        $method();
    }

    public function resetAndDistributeScore(Request $request) {
        if(($request->token)!=env('TG_TOKEN')){
            return;
        }

        $score = new Score();
        $score->resetScore();
        $score->distributeScore($request->date_offset??Score::DEFAULT_DATE_OFFSET);
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
