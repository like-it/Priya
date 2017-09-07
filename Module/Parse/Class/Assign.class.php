<?php

namespace Priya\Module\Parse;

class Assign extends Core {

    public function __construct($data=null){
        $this->data($data);
    }

    public function find($input=null){
        if($input === null){
            $input = $this->input();
        } else {
            $this->input($input);
        }
        if(empty($input)){
            return;
        }
        $tag = key($input);

        $explode = explode('=', trim($tag, '{}'), 2);

        if(!empty($explode[0]) && substr($explode[0], 0, 1) == '$' && count($explode) == 2){
            $attribute = substr(rtrim($explode[0]), 1);
            $value = trim($explode[1], ' ');
            //Ternary operators
            if($this->ternary(Ternary::SHORT, $attribute, $value) === true){
                return;
            }
            if($this->ternary(Ternary::QUESTION, $attribute, $value) === true){
                return;
            }
            $value = trim($value, '\'"');
            $this->data($attribute, $value);
//             $start = explode(Ternary::QUESTION, $value, 2);
//             $end = explode(Ternary::COLON, $value, 2);

            /*
            if(count($start) == 2 && count($end) == 2){
                foreach($start as $key => $value){

                }
                var_dump($value);
                var_dump($start);
                var_dump($end);
                var_dump($attribute);
//                 var_dump($this->data());
                            die;
            }
*/


        }
    }

    private function variable($string=''){
        $pattern = '/\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/';
        preg_match_all($pattern, $string, $matches, PREG_SET_ORDER);
        if(count($matches) == 1){
            $result = null;
            foreach ($matches[0] as $key => $search){
                $replace = $this->data(substr($search, 1));
                if((is_bool($replace) || is_null($replace) || is_array($replace) || is_object($replace)) && $string === null){
                    $result= $replace;
                    break;
                } else {
                    $result= str_replace($search, $replace, $string);
                }
            }
        } else {
            $result = $string;
        }
        return $result;
    }

    private function ternary($explode='', $attribute, $value=''){
        if($explode == Ternary::SHORT){
            $short = explode( $explode, $value, 2);
            if(count($short) == 2){
                $start = rtrim($short[0], ' ');
                $end = ltrim($short[1], ' ');
                //add operator support
                if(substr($start, 0, 1) == '$'){
                    $search = substr($start, 1);
                    $replace = $this->data($search);
                    if(!$replace){
                        $replace = $end;
                    }
                    $this->data($attribute, $replace);
                    return true;
                }
            }
        }
        elseif ($explode == Ternary::QUESTION){
            $start = explode($explode, $value, 2);
            foreach($start as $key => $value){
                $start[$key] = trim($value, ' ');
            }
            $end = explode(Ternary::COLON, $value, 2);
            foreach($end as $key => $value){
                $end[$key] = trim($value, ' ');
            }
            if(count($start) == 2 && count($end) == 2){
                if(substr($start[1], 0, 1) == ':'){
                    $start = $start[0];
                    //add operator support
                    if(substr($start, 0, 1) == '$'){
                        $search = substr($start, 1);
                        $replace = $this->data($search);
                        if(!$replace){
                            $replace = $end[1];
                        }
                        $this->data($attribute, $replace);
                        return true;
                    }
                } else {
                    $pattern = '/\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/';
//                 	$pattern = '/\{.*\}/';
                    $pattern = '/(\S+)(\s+)(\S+)(\s+)(\S+)/';
                    preg_match_all($pattern, $start[0], $matches, PREG_SET_ORDER);
                    if(count($matches) == 1){

                        $statement['left'] = trim($matches[0][1], '\'"');
                        $statement['operator'] = $matches[0][3];
                        $statement['right'] = trim($matches[0][5], '\'"');
                        $statement['true'] = trim($end[0], '\'"');
                        $statement['false'] = trim($end[1], '\'"');

                        $statement['left'] = $this->variable($statement['left']);
                        $statement['right'] = $this->variable($statement['right']);
                        $statement['true'] = $this->variable($statement['true']);
                        $statement['false'] = $this->variable($statement['false']);

                        if(in_array(
                            $statement['operator'],
                            Operator::Compare()
                        )){
                            switch($statement['operator']){
                                case '==' :
                                    if($statement['left'] == $statement['right']){
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
                            }
                        }
                        if(!empty($statement['output'])){
                            $this->data($attribute, $statement['true']);
                        } else {
                            $this->data($attribute, $statement['false']);
                        }
                        return true;
                    } else {
                        //maybe a pattern without spaces
                    }
                }
            }

        }
    }
}