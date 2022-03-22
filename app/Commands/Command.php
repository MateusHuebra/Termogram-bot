<?php

namespace App\Commands;

use App\Models\Game;
use App\Models\User;

abstract class Command {

    public function getUserId($update) {
        return $update->getMessage()->getFrom()->getId();
    }

    public function UserExists($userId) {
        return User::where('id', $userId)->exists();
    }

    public function GameExists($userId) {
        $date = date('Y-m-d');
        return Game::where('user_id', $userId)
            ->where('word_date', $date)
            ->exists();
    }

}