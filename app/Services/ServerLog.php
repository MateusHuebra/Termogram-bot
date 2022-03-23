<?php

namespace App\Services;

class ServerLog {

    static function log($value) {
        file_put_contents('php://stderr', "\n {$value}\n");
    }

    static function printR($value) {
        self::log(print_r($value, true));
    }

}