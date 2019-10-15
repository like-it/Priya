<?php

namespace Priya\Module\Parser;

class Random extends Core {

    public static function create(){
        return rand(1000,9999) . '-' . rand(1000,9999) . '-' . rand(1000,9999) . '-' . rand(1000,9999);
    }
}