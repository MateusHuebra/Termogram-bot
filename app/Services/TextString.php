<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class TextString {

    static function get(string $path, array $variables = null) {
        $json = File::get(__DIR__.'/../../resources/strings.json');
        $string = json_decode($json);

        $keys = explode('.', $path);
        foreach ($keys as $key) {
            if(!property_exists($string, $key)) {
                return '- string not found: '.$path;
            }
            $string = $string->$key;
        }

        $variations = count($string);
        $string = $string[rand(0, $variations-1)];
        
        if($variables) {
            foreach ($variables as $index => $variable) {
                $string = str_replace('{'.$index.'}', $variable, $string);
            }
        }

        return $string;

    }

}