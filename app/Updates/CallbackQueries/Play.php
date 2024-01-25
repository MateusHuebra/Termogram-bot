<?php

namespace App\Updates\CallbackQueries;

use App\Models\User;
use App\Services\ServerLog;
use App\Services\TextString;
use App\Updates\Commands\Start;

class Play extends CallbackQuery {

    public function run() {
        ServerLog::log('Open Notification > run');
        $start = new Start($update, $bot);
        $start->run();
    }

}