<?php

namespace App\Services;

class ServerLog {

    static function log($value) {
        file_put_contents('php://stderr', "\n {$value}\n");
    }

}