<?php

namespace Priya\Module\Parse;

class Method extends Core {


    public static function execute($function=array(), Variable $variable){
        $url = __DIR__ . '/../Function/Function.' . ucfirst($function['value']) . '.php';
        $name = 'function_' . strtolower($function['value']);
        if(file_exists($url)){
            require_once $url;
        } else {
            debug($url . ' not found');
        }
        if(function_exists($name)){
            $argument = array();
            if(isset($function['parameter'])){
                foreach ($function['parameter'] as $parameter){
                    if(isset($parameter['value']) || $parameter['value'] === null){
                        $argument[] = $parameter['value'];
                    }
                }
            }
            $function['execute'] = $name($argument, $variable);
            $function['value'] = $function['execute'];
        }
        return $function;
    }

}