<?php

namespace App\Console\Scheduled;

use App\Models\User;
use App\Models\Group;
use App\Services\Leaderboard as LeaderboardService;
use TelegramBot\Api\BotApi;
use App\Services\TextString;
use App\Services\ServerLog;
use Exception;

class SendFinalLeaderboard
{

    public function __invoke()
    {
        ServerLog::log('SendFinalLeaderboard > run');
        $bot = new BotApi(env('TG_TOKEN'));
        $LBservice = new LeaderboardService(30, true);

        $users = $LBservice->getUsers();
        $boardData = $LBservice->renderBoard($users, 'global');
        foreach ($users as $user) {
            try {
                $bot->sendMessage($user->id, $boardData['text'], 'MarkdownV2');
                //if(count($boardData['positions']) > 15) {
                $bot->sendMessage($user->id, TextString::get('leaderboard.yours', ['position' => $boardData['positions'][$user->id]]), 'MarkdownV2');
                //}
            } catch (Exception $e) {
                ServerLog::log($e->getMessage());
            }
        }

        try {
            $this->doGroups($bot);
        } catch (\Throwable $e) {
            $bot->sendMessage(env('TG_MYID'), 'Leaderboard DoGroups: ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getTraceAsString());
        }
    }

    private function doGroups($bot)
    {
        $groups = Group::all();
        foreach ($groups as $group) {
            $LBservice = new LeaderboardService(30, true);
            try {
                $membersList = $LBservice->getMembersList($group, $bot);
                $users = $LBservice->getUsers($membersList);
                $boardData = $LBservice->renderBoard($users, 'group');
                $bot->sendMessage($group->id, $boardData['text'], 'MarkdownV2');
            } catch (\Throwable $e) {
                ServerLog::log($e->getMessage());
                $bot->sendMessage(env('TG_MYID'), 'Leaderboard DoGroups ForEach: ' . $e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
            }
        }
    }
}
