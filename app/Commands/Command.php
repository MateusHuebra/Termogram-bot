<?php

namespace App\Commands;

abstract class Command {

    public function getUserId($update) {
        return $update->getMessage()->getFrom()->getId();
    }

}