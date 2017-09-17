<?php

namespace Priya\Module\Parse;

class Assign extends Core {

    public function __construct($data=null){
        $this->data($data);
    }

    public static function is_variable($record=array()){
        if(isset($record['type']) && $record['type'] == Token::TYPE_VARIABLE){
            return true;
        }
        return false;
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
            $parse = Token::parse($value);
            $output = null;
            $type = null;
            var_dump('__ASSIGN_FIND_____________________________');
            foreach($parse as $nr => $record){
                if(Assign::is_variable($record)){
                    $value = $this->data(substr($record['value'], 1));
                    $record['value'] = $value;
                    $record['type'] = Variable::type($value);
                    $parse[$nr] = $record;
                }
            }
            foreach($parse as $nr => $record){
                $record = Token::record($record);
                $record = Token::cast($record);
                $record['attribute'] = $attribute;
                $record['token'] = $value;
                $record['input'] = $input;

                var_dump($record);
            }

                if(!isset($record['operator'])){
                    //name this in a function
                    if($output  === null){
                        $output = $record['value'];
                        $previous = $record; //	Token::cast($record);
                        $output = $previous['value'];
                    } else {
                        if($previous['type'] == 'string' || ($previous['type'] == 'mixed' && $record['type'] == 'string')){
                            var_dump($record);
                            $output .= $record['value'];
                        }
                        elseif($previous['type'] == 'int' || $previous['type'] == 'float'|| ($previous['type'] == 'mixed' && ($record['type'] == 'int' || $record['type'] == 'float'))){
                            if($record['type'] = Token::TYPE_MIXED){
                                $record['type'] = $previous['type'];
                            }
                            $output += $record['value']; //add minus ? as option with | modifier
                        }
                        elseif($previous['type'] == 'boolean'){
                            /**
                             * true & true = true
                             * false & false = true
                             * true & false = false
                             * false & true = false
                             * (++/--/+-/-+)
                             */
                            if(!isset($previous['value'])){
                                var_dump("__NO_PREVIOUS VALUE____________________");
                                var_Dump($previous);
                            }
                            if(!isset($record['value'])){
                                var_dump("__NO_RECORD VALUE____________________");
                                var_Dump($record);
                            }
                            if($previous['value'] == (bool) $record['value']){
                                $record['type'] = Token::TYPE_BOOLEAN;
                                //++/--
                                $output = true;
                            } else {
                                $output = false;
                            }
                        }
                    }
                } else {
                    //has operator

                }
            }
            $this->data($record['attribute'], $output);

            return;


            //Ternary operators
            if($this->ternary(Ternary::SHORT, $attribute, $value) === true){
                return;
            }
            if($this->ternary(Ternary::QUESTION, $attribute, $value) === true){
                return;
            }
            //array or objects ?
            $value = trim($value, '\'"');
            if(is_numeric($value)){
                $pos = strpos($value,'0');
                if($pos === 0 && is_numeric(substr($value, 1, 1))){
                } else {
                    $value= $value+ 0;
                }
            }
            elseif(is_bool($value) || $value == 'true' || $value == 'false') {
                $value = (bool) $value;
            }
            elseif(is_null($value) || $value== 'null'){
                $value = null;
            }
            $this->data('set', $attribute, $value);
        }
    }

    private function variable($string='', $type=null){
        $has = false;
        $result = null;
        if($string == 'has' && $type !== null){
            $has = true;
            $string = $type;
        }
        if(
            is_bool($string) ||
            $string== 'true' ||
            $string== 'false' ||
            is_null($string) ||
            $string== 'null' ||
            is_numeric($string) ||
            is_array($string) ||
            is_object($string)
        ){
            if(is_numeric($string)){
                $pos = strpos($string,'0');
                if($pos === 0 && is_numeric(substr($string, 1, 1))){
                } else {
                    $result = $string+ 0;
                }
            }
            elseif(is_bool($string) || $string== 'true' || $string== 'false') {
                $result= (bool) $string;
            }
            elseif(is_null($string) || $string== 'null'){
                $result= null;
            }
            if($has === true){
                return !false; //this means not true but needed for statement default trigger
            }
            return $result;
        }
        $string = trim($string, '\'"');
        $pattern = '/\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/';
        preg_match_all($pattern, $string, $matches, PREG_SET_ORDER);
        if(count($matches) == 1){
            $result = null;
            foreach ($matches[0] as $key => $search){
                $replace = $this->data(substr($search, 1));
                $is_variable = !(bool) str_replace($search, '', $string);

                if(is_null($replace) && $has === true){
                    return false;
                } elseif($has === true){
                    return true;
                }
                if(
                    (
                        is_bool($replace) ||
                        $replace== 'true' ||
                        $replace== 'false' ||
                        is_null($replace) ||
                        $replace== 'null' ||
                        is_numeric($replace) ||
                        is_array($replace) ||
                        is_object($replace)
                    ) &&
                    $result === null &&
                    $is_variable === true
                ){
                    if(is_numeric($replace)){
                        $pos = strpos($replace,'0');
                        if($pos === 0 && is_numeric(substr($replace, 1, 1))){
                        } else {
                            $result = $replace + 0;
                        }
                    }
                    elseif(is_bool($replace) || $replace== 'true' || $replace== 'false') {
                        $result= (bool) $value;
                    }
                    elseif(is_null($replace) || $replace== 'null'){
                        $result= null;
                    } else {
                        $result = $replace;
                    }
                    break;
                } else {
                    $result = str_replace($search, $replace, $string);
                }
            }
        } else {
            if($has === true){
                var_dump('HERE IT IS....');
                var_dump($string);
            }
            if(is_numeric($string)){
                $pos = strpos($string,'0');
                if($pos === 0 && is_numeric(substr($string, 1, 1))){
                } else {
                    $result = $string+ 0;
                }
            }
            elseif(is_bool($string) || $string== 'true' || $string== 'false') {
                $result= (bool) $string;
            }
            elseif(is_null($string) || $string== 'null'){
                $result= null;
            }
            else {
                $result = $string;
            }
        }
        return $result;
    }

    private function statement($string='', $default=null, $true=true, $false=false){
        $tokens = Token::all($string);



        var_dump($tokens);


        $statement = array();
        $pattern = '/(\S+)(\s+)(\S+)(\s+)(\S+)/';
        preg_match_all($pattern, $string, $matches, PREG_SET_ORDER);
        if(count($matches) == 1){
            $statement['left'] = $matches[0][1];
            $statement['operator'] = $matches[0][3];
            $statement['right'] = $matches[0][5];
            $statement['true'] = $true;
            $statement['false'] = $false; // $default;
            $statement['left'] = $this->variable($statement['left']);
            $statement['right'] = $this->variable($statement['right']);

            /*
            $pattern = '/\d+/';
            preg_match_all($pattern, $statement['left'], $match, PREG_SET_ORDER);
            var_dump('__MATCHES__________________________');
            var_dump($statement['left']);
            var_dump($match);
            $tokens = token_get_all('<?php print(' . $statement['left'] . ');');

            foreach ($tokens as $key => $token){
                $tokens[$key][2] = token_name($token[0]);
            }
            var_dump($tokens);


            ob_start();
            $statement['left'] ='print ("'.$statement['left'].'");';
            eval($statement['left']);
            $statement['left'] = ob_get_contents();
            ob_end_clean();

            ob_start();
            $statement['right'] ='print ('.$statement['right'].');';
            eval($statement['right']);
            $statement['right'] = ob_get_contents();
            ob_end_clean();

            $statement['left'] = $this->variable($statement['left']);
            $statement['right'] = $this->variable($statement['right']);

            */

            if(!$this->variable('has', $statement['left']) || !$this->variable('has', $statement['right'])){
                return $default;
            }
            $statement['true'] = $this->variable($statement['true']);
            $statement['false'] = $this->variable($statement['false']);

            $statement = Control_If::statement($statement);

            if(!empty($statement['output'])){
                $statement['result'] = $statement['true']; //$this->data($attribute, $statement['true']);
            } else {
                $statement['result'] = $statement['false']; //$this->data($attribute, $statement['false']);
            }
        } else {
            $replace= $this->variable($string);
            if(!$replace){
                $replace = $this->variable($default);
            }
            $statement['result'] = $replace;
        }
        return $statement;
    }

    private function ternary($explode='', $attribute, $value=''){
        if($explode == Ternary::SHORT){
            $short = explode( $explode, $value, 2);
            if(count($short) == 2){
                $start = rtrim($short[0], ' ');
                $end = ltrim($short[1], ' ');

                $statement = $this->statement($start, $this->statement($end));
                $statement['result'] = $this->variable($statement['result']);

                //$statement['result'] = $this->variable($this->statement($start, $this->statement($end)));
                $this->data('set', $attribute, $statement['result']);
                return true;

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
                        $this->data('set', $attribute, $replace);
                        return true;
                    }
                } else {
                    $original = $value;
                    $statement = $this->statement($start[0], $this->statement($end[1]), $end[0], $end[1]);
                    $statement['result'] = $this->variable($statement['result']);


                    var_dump($statement);
                    /*
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

                        $statement = Control_If::statement($statement);

                        if(!empty($statement['output'])){
                            var_dump($statement['true']);
                            $this->data('set', $attribute, $statement['true']);
                        } else {
                            var_dump($statement['true']);
                            $this->data('set', $attribute, $statement['false']);
                        }
                        return true;
                    } else {
                        //no comparison
                        //maybe a pattern without spaces
                    }
                    */
                }
            }

        }
    }
}