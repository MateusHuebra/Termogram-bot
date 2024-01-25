<?php

namespace App\Services;

class ServerLog {

    static function log($value) {
        file_put_contents(
            storage_path('logs/logs.log'),
            "\n {$value}\n",
            FILE_APPEND
        );
    }

    static function printR($value) {
        self::log(print_r($value, true));
    }

}