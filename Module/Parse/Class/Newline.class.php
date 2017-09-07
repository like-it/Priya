<?php

namespace Priya\Module\Parse;

//use Priya\Module\Parse\Data;

class Newline extends Core {

    public function __construct($input=null, $random=null){
        $this->input($input);
        $this->random($random);
    }

    public function replace($replace=null){
        if($replace=== null){
            $replace = $this->input();
        } else {
            $this->input($replace);
        }
        $replace= str_replace("\r", '[' . $this->random() . '][return]', $replace);
        $replace= str_replace("\n", '[' . $this->random() . '][newline]', $replace);
        return $this->output($replace);
    }

    public function restore($restore=null){
        if($restore=== null){
            $restore= $this->output();
        }
        $restore= str_replace('[' . $this->random() . '][return]', "\r", $restore);
        $restore= str_replace( '[' . $this->random() . '][newline]', "\n", $restore);
        return $this->output($restore);
    }

}