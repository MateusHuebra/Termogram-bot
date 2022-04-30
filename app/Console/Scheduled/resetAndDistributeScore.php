<?php

namespace App\Console\Scheduled;

use App\Services\Score;

class resetAndDistributeScore {

    public function __invoke()
    {
        $score = new Score();
        $date = date('Y/m/d');
        $score->resetScore();
        $score->distributeScore($date);
    }

}