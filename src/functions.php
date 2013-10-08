<?php

if (!function_exists('translate')) {
    function translate($message, $textDomain=null, $locale=null) {
        return $message;
    }
}

if (!function_exists('translatePlural')) {
    function translatePlural($message, $plural=null, $count=null, $textDomain=null, $locale=null) {
        return $message;
    }
}
