<?php

namespace App\Services;

class ServerLog {

    static function log($value, bool $lineBreak = true) {
        if($lineBreak) {
            $value = "{$value}\n";
        }
        file_put_contents(
            storage_path('logs/logs.log'),
            "\n {$value}",
            FILE_APPEND
        );
    }

    static function printR($value) {
        self::log(print_r($value, true));
    }

}
