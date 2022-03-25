<?php

namespace App\Updates\CallbackQueries;

use App\Services\ServerLog;

class Factory {

    static function buildCallbackQuery($update) {
        ServerLog::log('Factory > buildCallbackQuery');
        $type = self::getCallbackType($update);

        if($type=='notification') {
            return new Notification();

        } else {
            return false;
        }
    }

    private static function getCallbackType($update) {
        $data = $update->getCallbackQuery()->getData();
        if (is_null($data) || !strlen($data)) {
            return false;
        }

        preg_match('/^([A-z]+:)/', $data, $matches);

        if (empty($matches)) {
            return false;
        } 

        return $matches[1];
    }

}