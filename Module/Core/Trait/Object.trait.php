<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *     -    all
 */

namespace Priya\Module\Core;

use stdClass;

trait Object {

    public static function object($input='', $output='object',$type='root'){
        if(is_bool($input)){
            if($output == 'object' || $output == 'json'){
                $data = new stdClass();
                if(empty($input)){
                    $data->false = false;
                } else {
                    $data->true = true;
                }
                if($output == 'json'){
                    $data = json_encode($data);
                }
                return $data;
            }
            elseif($output == 'array') {
                return array($input);
            } else {
                trigger_error('unknown output in object');
            }
        }
        if(is_null($input)){
            if($output == 'object'){
                return new stdClass();
            }
            elseif($output == 'array'){
                return array();
            }
            elseif($output == 'json'){
                return '{}';
            }
        }
        if(is_array($input) && $output == 'object'){
            return self::array_object($input);
        }
        if(is_string($input)){
            $input = trim($input);
            if($output=='object'){
                if(substr($input,0,1)=='{' && substr($input,-1,1)=='}'){
                    $input = str_replace(
                        array(
                            "\r",
                            "\n"
                        ),
                        array(
                            '',
                            ''
                        ),
                        $input
                    );
                    $json = json_decode($input);
                    if(json_last_error()){
                        debug($input, 'input');
                        trigger_error(json_last_error_msg(), E_USER_ERROR);
                    }
                    return $json;
                }
                elseif(substr($input,0,1)=='[' && substr($input,-1,1)==']'){
                    $input = str_replace(
                        array(
                            "\r",
                            "\n"
                        ),
                        array(
                            '',
                            ''
                        ),
                        $input
                    );
                    $json = json_decode($input);
                    if(json_last_error()){
                        debug($input, 'input');
                        trigger_error(json_last_error_msg(), E_USER_ERROR);
                    }
                    return $json;
                }
            }
            elseif(stristr($output, 'json') !== false){
                if(substr($input,0,1)=='{' && substr($input,-1,1)=='}'){
                    $input = json_decode($input);
                }
            }
            elseif($output=='array'){
                if(substr($input,0,1)=='{' && substr($input,-1,1)=='}'){
                    return json_decode($input, true);
                }
                elseif(substr($input,0,1)=='[' && substr($input,-1,1)==']'){
                    return json_decode($input, true);
                }
            }
        }
        if(stristr($output, 'json') !== false && stristr($output, 'data') !== false){
            $data = str_replace('"', '&quot;',json_encode($input));
        }
        elseif(stristr($output, 'json') !== false && stristr($output, 'line') !== false){
            $data = json_encode($input);
        } else {
            $data = json_encode($input, JSON_PRETTY_PRINT);
        }
        if($output=='object'){
            return json_decode($data);
        }
        elseif(stristr($output, 'json') !== false){
            if($type=='child'){
                return substr($data,1,-1);
            } else {
                return $data;
            }
        }
        elseif($output=='array'){
            return json_decode($data,true);
        } else {
            trigger_error('unknown output in object');
        }
    }

    public static function array_object($array=array()){
        $object = new stdClass();
        foreach ($array as $key => $value){
            if(is_array($value)){
                $object->{$key} = self::array_object($value);
            } else {
                $object->{$key} = $value;
            }
        }
        return $object;
    }

    public static function is_nested_array($array=array()){
        $array = (array) $array;
        foreach($array as $key => $value){
            if(is_array($value)){
                return true;
            }
        }
        return false;
    }

    public function explode_single($delimiter=array(), $string='', $internal=array()){
        $result = array();
        if(is_array($delimiter)){
            foreach($delimiter as $nr => $delim){
                if(strpos($string, $delim) === false){
                    continue; //speed... & always >=2
                }
                $tmp = $this->explode_single($delim, $string, $result);
                foreach ($tmp as $tmp_nr => $tmp_value){
                    $result[] = $tmp_value;
                }
            }
            $list = array();
            foreach ($result as $nr => $part){
                $splitted = false;
                foreach ($delimiter as $delim){
                    if(strpos($part, $delim) === false){
                        continue; //speed... & always >=2
                    }
                    $tmp = explode($delim, $part);
                    $splitted = true;
                    foreach($tmp as $part_splitted){
                        $list[$part_splitted][] = $part_splitted;
                    }
                }
                if(empty($splitted)){
                    $list[$part][] = $part;
                }
            }
            foreach($list as $part => $value){
                foreach ($delimiter as $delim){
                    if(strpos($part, $delim) !== false){
                        unset($list[$part]);
                    }
                }
            }
            $result = array();
            foreach($list as $part => $value){
                $result[] = $part;
            }
            if(empty($result)){
                $result[] = $string;
            }
            return $result;
        } else {
            $result = explode($delimiter, $string);
        }
        if(empty($result)){
            $result[] = $string;
        }
        return $result;
    }

    public function explode_multi($delimiter=array(), $string='', $limit=array()){
        $result = array();
        if(!is_array($limit)){
            $limit = explode(',', $limit);
            $value = reset($limit);
            if(count($delimiter) > count($limit)){
                for($i = count($limit); $i < count($delimiter); $i++){
                    $limit[$i] = $value;
                }
            }
        }
        foreach($delimiter as $nr => $delim){
            if(isset($limit[$nr])){
                $tmp = explode($delim, $string, $limit[$nr]);
            } else {
                $tmp = explode($delim, $string);
            }
            if(count($tmp)==1){
                continue;
            }
            foreach ($tmp as $tmp_nr => $tmp_value){
                $result[] = $tmp_value;
            }
        }
        if(empty($result)){
            $result[] = $string;
        }
        return $result;
    }

    public function object_horizontal($verticalArray=array(), $value=null, $return='object'){
        if(empty($verticalArray)){
            return false;
        }
        $object = new stdClass();
        if(is_object($verticalArray)){
            $attributeList = get_object_vars($verticalArray);
            $list = array_keys($attributeList);
            $last = array_pop($list);
            if($value===null){
                $value = $verticalArray->$last;
            }
            $verticalArray = $list;
        } else {
            $last = array_pop($verticalArray);
        }
        if(empty($last)){
            return false;
        }
        foreach($verticalArray as $key => $attribute){
            if(empty($attribute)){
                continue;
            }
            if(!isset($deep)){
                $object->{$attribute} = new stdClass();
                $deep = $object->{$attribute};
            } else {
                $deep->{$attribute} = new stdClass();
                $deep = $deep->{$attribute};
            }
        }
        if(!isset($deep)){
            $object->$last = $value;
        } else {
            $deep->$last = $value;
        }
        if($return=='array'){
            $json = json_encode($object);
            return json_decode($json,true);
        } else {
            return $object;
        }
    }

    public function object_delete($attributeList=array(), $object='', $parent='', $key=null){
        if(is_string($attributeList)){
            $attributeList = $this->explode_multi(array('.', ':', '->'), $attributeList);
        }
        if(is_array($attributeList)){
            $attributeList = $this->object_horizontal($attributeList);
        }
        if(!empty($attributeList)){
            foreach($attributeList as $key => $attribute){
                if(isset($object->{$key})){
                    return $this->object_delete($attribute, $object->{$key}, $object, $key);
                } else {
                    unset($object->{$key}); //to delete nulls
                    return false;
                }
            }
        } else {
            unset($parent->{$key});    //unset $object won't delete it from the first object (parent) given
            return true;
        }
    }

    public function object_set($attributeList=array(), $value=null, $object='', $return='child'){
        if(empty($object)){
            return;
        }
        if(is_string($return) && $return != 'child'){
            if($return == 'root'){
                $return = $object;
            } else {
                $return = $this->object_get($return, $object);
            }
        }
        if(is_string($attributeList)){
            $attributeList = $this->explode_multi(array('.', ':', '->'), $attributeList);
        }
        if(is_array($attributeList)){
            $attributeList = $this->object_horizontal($attributeList);
        }
        if(!empty($attributeList)){
            foreach($attributeList as $key => $attribute){
                if(isset($object->{$key}) && is_object($object->{$key})){
                    if(empty($attribute) && is_object($value)){
                        foreach($value as $value_key => $value_value){
                            if(isset($object->$key->$value_key)){
//                                 unset($object->$key->$value_key);   //so sort will happen, request will tak forever and apache2 crashes needs reboot apache2
                            }
                            $object->{$key}->{$value_key} = $value_value;
                        }
                        return $object->{$key};
                    }
                    return $this->object_set($attribute, $value, $object->{$key}, $return);
                }
                elseif(is_object($attribute)){
                    $object->{$key} = new stdClass();
                    return $this->object_set($attribute, $value, $object->{$key}, $return);
                } else {
                    $object->{$key} = $value;
                }
            }
        }
        if($return == 'child'){
            return $value;
        }
        return $return;
    }

    public function object_get($attributeList=array(), $object=''){
        if(empty($object)){
            return $object;
        }
        if(is_string($attributeList)){
            $attributeList = $this->explode_multi(array('.',':', '->'), $attributeList);

            foreach($attributeList as $nr => $attribute){
                if(empty($attribute)){
                    unset($attributeList[$nr]);
                }
            }
        }
        if(is_array($attributeList)){
            $attributeList = $this->object_horizontal($attributeList);
        }
        if(empty($attributeList)){
            return $object;
        }
        foreach($attributeList as $key => $attribute){
            if(empty($key)){
                continue;
            }
            if(isset($object->{$key})){
                return $this->object_get($attributeList->{$key}, $object->{$key});
            }
        }
        return null;
    }

    public static function object_merge(){
        $objects = func_get_args();
        $main = array_shift($objects);
        if(empty($main) && !is_array($main)){
            $main = new stdClass();
        }
        foreach($objects as $nr => $object){
            if(is_array($object)){
                foreach($object as $key => $value){
                    if(is_object($main)){
                        var_dump($main);
                        var_dump($object);
                        var_dump(debug_backtrace(true));
                        die;
                    }
                    if(!isset($main[$key])){
                        $main[$key] = $value;
                    } else {
                        if(is_array($value) && is_array($main[$key])){
                            $main[$key] = self::object_merge($main[$key], $value);
                        } else {
                            $main[$key] = $value;
                        }
                    }
                }
            }
            elseif(is_object($object)){
                foreach($object as $key => $value){
                    if((!isset($main->{$key}))){
                        $main->{$key} = $value;
                    } else {
                        if(is_object($value) && is_object($main->{$key})){
                            $main->{$key} = self::object_merge($main->{$key}, $value);
                        } else {
                            $main->{$key} = $value;
                        }
                    }
                }
            }
        }
        return $main;
    }

    public function array_trim($array=array(), $split=',', $trim=null){
        if(is_string($array)){
            $array = explode($split, $array);
        }
        foreach($array as $key => $value){
            if(is_array($value)){
                $array[$key] = $this->array_trim($value, $split, $trim);
            } else {
                if($trim === null){
                    $value = trim($value);
                } else {
                    $value = trim($value, $trim);
                }
                if(empty($value)){
                    unset($array[$key]);
                    continue;
                }
                $array[$key] = $value;
            }
        }
        return $array;
    }
}