<?php

namespace App\Update\Commands;

abstract class Command {

    public function getUserId($update) {
        return $update->getMessage()->getFrom()->getId();
    }

}