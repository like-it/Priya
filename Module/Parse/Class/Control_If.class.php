<?php

namespace Priya\Module\Parse;

class Control_If extends Core {

    public static function statement($statement=array()){
        if(in_array(
            $statement['operator'],
            Operator::Compare()
        )){
            switch($statement['operator']){
                case '&&' :
                    if($statement['left'] && $statement['right']){
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case '||' :
                    if($statement['left'] || $statement['right']){
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case 'and' :
                    if($statement['left'] and $statement['right']){
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case 'or' :
                    if($statement['left'] or $statement['right']){
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case 'xor' :
                    if($statement['left'] xor $statement['right']){
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case '==' :
                    if($statement['left'] == $statement['right']){
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case '===' :
                    if($statement['left'] === $statement['right']){
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case '<>' :
                    if($statement['left'] <> $statement['right']){
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case '!=' :
                    if($statement['left'] != $statement['right']){
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case '!==' :
                    if($statement['left'] !== $statement['right']){
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case '<' :
                    if($statement['left'] < $statement['right']){
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case '<=' :
                    if($statement['left'] <= $statement['right']){
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case '>' :
                    if($statement['left'] > $statement['right']){
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case '>=' :
                    if($statement['left'] >= $statement['right']){
                        var_dump('__STATEMENT______________________');
                        var_dump($statement['left']);
                        var_dump($statement['right']);
                        $statement['output'] = true;
                    } else {
                        $statement['output'] = false;
                    }
                break;
                case '<=>' :

                    /*
                     if($statement['left'] <=> $statement['right']){
                     $statement['output'] = true;
                     } else {
                     $statement['output'] = false;
                     }
                     break;
                     */
            }
        }
        return $statement;
    }
}