<?php

namespace Priya\Module\Parser;

class Core extends \Priya\Module\Data {
    protected $input;
    protected $output;
    protected $random;

    public function input($input=null){
        if($input !== null){
            $this->setInput($input);
        }
        return $this->getInput();
    }

    private function setInput($input=''){
        $this->input= $input;
    }

    private function getInput(){
        return $this->input;
    }

    public function output($output=null){
        if($output !== null){
            $this->setOutput($output);
        }
        return $this->getOutput();
    }

    private function setOutput($output=''){
        $this->output= $output;
    }

    private function getOutput(){
        return $this->output;
    }

    public function random($random=null){
        if($random !== null){
            if($random == 'create'){
                $this->setRandom(Core::random_create());
            } else {
                $this->setRandom($random);
            }
        }
        return $this->getRandom();
    }

    public static function random_create(){
        return rand(1000,9999) . '-' . rand(1000,9999) . '-' . rand(1000,9999) . '-' . rand(1000,9999);
    }

    private function setRandom($random=''){
        $this->random = $random;
    }

    private function getRandom(){
        return $this->random;
    }

    public static function tag_lower($string='', $tag=''){
        if(empty($tag)){
            return $string;
        }
        $string= str_ireplace('{' . $tag, '{' . $tag, $string);
        $string= str_ireplace('{/' . $tag . '}', '{/' . $tag . '}', $string);
        return $string;
    }

}