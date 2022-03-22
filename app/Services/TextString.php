<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class TextString {

    static function get(string $key) {
        $json = File::get(__DIR__.'/../../resources/strings.json');
        $string = json_decode($json);

        $keys = explode('.', $key);
        foreach ($keys as $key) {
            if(!property_exists($string, $key)) {
                return '- string not found -';
            }
            $string = $string->$key;
        }

        $variations = count($string);
        return $string[rand(0, $variations-1)];

    }

}