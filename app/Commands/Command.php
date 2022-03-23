<?php

namespace App\Commands;

use App\Models\Game;
use App\Models\User;

abstract class Command {

    public function getUserId($update) {
        return $update->getMessage()->getFrom()->getId();
    }

}