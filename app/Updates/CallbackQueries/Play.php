<?php

namespace App\Updates\CallbackQueries;

use App\Models\User;
use App\Services\ServerLog;
use App\Services\TextString;
use App\Updates\Commands\Notifications;

class Play extends CallbackQuery {

    public function run() {
        ServerLog::log('Open Notification > run');

        
    }

}