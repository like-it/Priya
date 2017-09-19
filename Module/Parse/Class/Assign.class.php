<?php

namespace Priya\Module\Parse;

use Priya\Module\Core\Object;

class Assign extends Core {

    public function __construct($data=null, $random=null){
        $this->data($data);
        $this->random($random);
    }

    public static function is_variable($record=array()){
        if(isset($record['type']) && $record['type'] == Token::TYPE_VARIABLE){
            return true;
        }
        return false;
    }

    public static function operator($parse=array()){
        foreach($parse as $nr => $record){
            if(Assign::is_operator($record)){
                return $record;
            }
        }
        return array();
    }

    public static function is_operator($record=array()){
        if(isset($record['type']) && $record['type'] == Token::TYPE_OPERATOR){
            return true;
        }
        return false;
    }

    public static function has_operator($parse=array()){
        foreach($parse as $nr => $record){
            if(isset($record['type']) && $record['type'] == Token::TYPE_OPERATOR){
                return true;
            }
        }

        return false;
    }

    public static function has_set($parse=array()){
        foreach ($parse as $nr => $record){
            if(!empty($record['is_set'])){
                return true;
            }
        }
        return false;
    }

    public static function remove_set($parse=array()){
        $set = array();
        foreach ($parse as $nr => $record){
            if(isset($record['set'])){
                continue;
            }
            $set[] = $record;
        }
        return $set;
    }

    public static function replace_set($parse=array(), $search=array(), $replace=array()){
        $match = reset($search); //deep //left to right
        $remove = false;
        $is_replace = false;
        foreach ($parse as $nr => $record){
            if(isset($record['set']) && $record['set']['depth'] == $match['set']['depth']){
                if(!empty($remove)){
                    unset($parse[$nr]);
                    return $parse;
                } else {
                    $remove = true;
                }
            }
            if(!empty($remove)){
                if(empty($is_replace)){
                    $parse[$nr] = $replace;
                    $is_replace = true;
                    continue;
                }
                unset($parse[$nr]);
            }
        }
        debug($parse, 'replace_set');
        return $parse;
    }

    public static function get_set($parse=array()){
        $highest = Assign::get_set_highest($parse);
        $is_set = false;
        $set = array();
        foreach ($parse as $nr => $record){
            if(isset($record['set']) && isset($record['set']['depth']) && $record['set']['depth'] == $highest){
                //first one found
                $is_set = true;
                //till first end parenthese
            }
            if($is_set === true){
                $set[] = $record;
                if(isset($record['parenthese']) && $record['parenthese'] == ')' && $record['set']['depth'] == $highest){
                    $is_set = false;
                    return $set;
                }
            }
        }
        return $set;
    }

    public static function get_set_highest($parse=array()){
        $depth = 0;
        foreach ($parse as $nr => $record){
            if(!empty($record['is_set']) && $record['set']['depth'] > $depth){
                $depth = $record['set']['depth'];
            }
        }
        return $depth;
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
        $assign = false;
        $parse = array();
        $count = 0;
//         debug($tag, 'tag');
        $explode = explode('=', substr($tag, 1, -1), 2);

        if(!empty($explode[0]) && substr($explode[0], 0, 1) == '$' && count($explode) == 2){
            $attribute = substr(rtrim($explode[0]), 1);
            $value = trim($explode[1], ' ');
            if(
                substr($attribute,-1) == '-' ||
                substr($attribute,-1) == '+' ||
                substr($attribute,-1) == '.' ||
                substr($attribute,-1) == '!'
            ){
                $assign = substr($attribute, -1) . '=';
                $attribute = substr($attribute, 0, -1);
                $attribute = rtrim($attribute,' ');
            }
            //before create_object assign variable needed
            $create = Token::restore_return($value, $this->random());
            $original = $create;
            $create = Token::all($create);
            $object = Token::create_object($create);
            if(!empty($object)){
                $variable = new Variable($this->data(), $this->random());
                $object['value'] = $variable->replace($object['value']);
                //is variable data changed?
                $object = Token::cast($object);
                $this->data($attribute, $object['value']);
                return;
            }
            $array = Token::create_array($create);
            if(!empty($array)){
                $variable = new Variable($this->data(), $this->random());
                $array['value'] = $variable->replace($array['value']);
                //is variable data changed?
                $array = Token::cast($array);
                $this->data($attribute, $array['value']);
                return;
            }
            $variable = new Variable($this->data(), $this->random());
            // an equation can be a variable, in that case it will be + 0
            // original, spaces have to be removed to replace the parts

            $parse = Token::parse($create);
            $parse = Token::variable($parse, $variable);

            $math = Token::create_equation($parse);
            if($math !== false){
                $this->data($attribute, $math);
                return;
            } else {
                $item = array();
                foreach ($parse as $nr => $record){
                    if(empty($record['type'])){
                        continue;
                    }
                    if($record['type'] == Token::TYPE_WHITESPACE){
                        continue;
                    }
                    if(empty($item)){
                        $item = $record;
                        continue;
                    }
                    if(!empty($item['type']) && $item['type'] != $record['type']){
                        $item['type'] = Token::TYPE_MIXED;
                    }
                    if(!empty($item['value']) && isset($record['value']) && $item['type'] == Token::TYPE_STRING || $item['type'] == Token::TYPE_MIXED){
                        $item['value'] .= $record['value'];
                    }
                    elseif(!empty($item['value']) && !empty($record['value'])){
                        debug($record, 'record, item already set');
                        debug($item, 'item already set');
                    }
                }
                switch($assign){
                    case '+=' :
                        $plus = $this->data($attribute) + 0;
                        $this->data($attribute, $plus += $item['value']);
                    break;
                    case '-=' :
                        $min = $this->data($attribute) + 0;
                        $this->data($attribute, $min -= $item['value']);
                    break;
                    case '.=' :
                        $add = $this->data($attribute);
                        $this->data($attribute, $add .= $item['value']);
                    break;
                    default :
                        $item = Token::cast($item);
                        $this->data($attribute, $item['value']);
                       break;
                }
//                 debug($assign, 'assign');
//                 debug($item, 'pre cast');

//                 debug($item, 'item');
                return;
            }
//             $value = Token::restore_return($value, $this->random());
            debug($value, 'value');

            /*


            $variable = new Variable($this->data(), $this->random());
            $equation = $variable->replace($create);

                $array = Token::cast($array);
                $this->data($attribute, $equation);
                debug($equation);
            }
            debug($create);
            */
            $parse = Token::parse2($value);
            /*
            debug('create array');
            die;
            $array =  Token::create_array($create);
            debug($array, 'create array');
            */


            //debug($assign, 'assign');
            //debug($value, 'value');
            //debug($attribute, 'attribute');
            //debug($parse, 'parse in find');
            $type = null;
            foreach($parse as $nr => $record){
                if(Assign::is_variable($record)){
                    $record['value'] = $this->data(substr($record['value'], 1));
                    $record['type'] = Variable::type($record['value']);
                } else {
                    if(!isset($record['value'])){
                        continue;
                    }
                    $record['type'] = Variable::type($record['value']);
                    if($record['type'] == Token::TYPE_STRING){
                        /*
                        if(substr($record['value'], 0, 1) == '\'' && substr($record['value'], -1, 1) == '\''){
//                             $record['value'] = substr($record['value'], 1, -1);
//                             $record['value'] = str_replace('\\\'', '\'', $record['value']);
//                             $record['is_escaped'] = true;
                        }
                        elseif(substr($record['value'], 0, 1) == '"' && substr($record['value'], -1, 1) == '"'){
//                             $record['value'] = substr($record['value'], 1, -1);
//                             $record['value'] = str_replace('\"', '"', $record['value']);
                        }
                        */
                    }
                    if($record['type'] == Token::TYPE_INT){
                        $record['value'] = $record['value'] + 0;
                    }
                    //debug($record, 'no variable');
                }
                $parse[$nr] = $record;
            }
            $count = count($parse);
            if($count > 1){
                //move up without the count
                $counter = 0;
                while(Assign::has_set($parse)){
                    $counter++;
                    $set = Assign::get_set($parse);
                    $statement = Assign::remove_set($set);
                    if(Assign::has_operator($statement)){
                        $record = Operator::statement($statement);
                        $parse = Assign::replace_set($parse, $set, $record);
                    } else {
                        debug($statement, 'statement');
                        debug($set, 'set');
                        debug($parse, 'parse');

                    }
                    //remove counter
                    if($counter > 10){
                        break;
                    }
                }
                //debug($parse);
            }
            if($assign == '.='){
                //                     $parse = Token::parse($value);
//                 debug($parse, 'parse with assign', true);
//                 die;
            }
            $count = count($parse);
            if($count > 1){
                if(isset($parse[0]) && !empty($parse[0]['is_cast'])){
                    //maybe detect earlier
                    $cast = array_shift($parse);
                    foreach($parse as $nr => $record){
                        $record['is_cast'] = $cast['is_cast'];
                        $record['cast'] = $cast['cast'];
                        $parse[$nr] = $record;
                    }
                }
                elseif(isset($parse[0]) && !isset($parse[0]['value'])){
                    if(isset($parse[0]['type']) && $parse[0]['type']==  Token::TYPE_OPERATOR){
                        //keep in parse
                    } else {
                        array_shift($parse);
                    }
                }
                //add operator assignment so + .= !
                //                 while(Assign::has_operator($parse)){
                $has_operator = Assign::has_operator($parse);
                if($has_operator === true){
                    var_dump($has_operator);
                    $record = Operator::statement($parse);
                    debug($parse, 'parse');
                    debug($record, 'while has operator');
                    die;
                    $parse = Assign::replace_set($parse, $set, $record);
                    debug($parse, 'do thing');
                    die;
                }

            }
        } else {
            /*
            $explode = explode('+', trim($tag, '{}'), 2);
            var_dump($explode);
            die;
            */
        }
        foreach($parse as $nr => $record){
            $record = Token::cast($record);
            $record['attribute'] = $attribute;
            $record['original'] = $value;
            $record['input'] = $input;
            $parse[$nr] = $record;
        }
        $count = count($parse);
        if($assign == '-='){
            if($count > 1){
                debug($parse, 'parse with assign -=');
            }
            $variable = $this->data($attribute) + 0;
            $variable -= ($record['value'] + 0);
            $this->data($attribute, $variable);
            return;
        }
        elseif($assign == '+='){
            if($count > 1){
                debug($parse, 'parse with assign +=');
            }
            $variable = $this->data($attribute) + 0;
            $variable += ($record['value'] + 0);
            $this->data($attribute, $variable);
            return;
        }
        elseif($assign == '.='){
            if($count > 1){
                debug($parse, 'parse with assign .=');
            }
            $string = (string) $this->data($attribute);
            $string .= (string) $record['value'];
            $this->data($attribute, $string);
            return;
        }
        elseif($assign == '!='){
            if($count > 1){
                debug($parse, 'parse with assign !=');
            }
            $this->data($attribute, $this->data($attribute) != $record['value']);
            return;
        }
        if($count == 1){
            $this->data($record['attribute'], $record['value']);
        }
        elseif($count == 0){
            return;
        } else {
            debug($parse, 'do thing');
        }
        return;
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