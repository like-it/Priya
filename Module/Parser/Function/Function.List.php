<?php
/*
Priya 0.1.16 (built: 2017-05-17 18:54:21)
Copyright (c) 2015-2017 Remco van der Velde
Generated File (do not modify) (built: 2017-05-18 11:49:24)
*/

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 * @todo
 *  -	value = argument original ?
 *
 */

use Priya\Application;
use Priya\Module\Parser;

function control_if($value=null, $node='', $parser=null){
    if(!is_object($node)){
        return $value;
    }
    $explode = explode($node->if_replace, $value, 2);
    if(count($explode) == 1){
        $node->result = 'ignore';
        return $value;
    }
    $dir = dirname(Parser::DIR) . Application::DS . 'Function' . Application::DS;
    $compile_list = array();
    if(empty($node->methodList)){
        $node = evaluate($node);
    }
    $node->statement = $parser->execMethodList($node->methodList, $node->statement);
    $node = evaluate($node);
    if($node->condition === true){
        $node->result = $node->true;
    } else {
        $node->result = $node->false;
    }
    $value = str_replace($node->string, $node->result, $value);
    $value = str_replace('[quote]', '', $value);
    return $value;
}

function evaluate($node = ''){
    /*
     * @todo
     * only own methods can pass and we should add forbidden methods here
     * add elseif statements
     */
    if(!is_object($node)){
        return $node;
    }
    $result = false;
    $eval = 'if(' . $node->statement .'){ $result = true; } else { $result = false; }';
    if (version_compare(PHP_VERSION, Parser::PHP_MIN_VERSION) >= 0) {
        error_clear_last();
    }
    $error = error_get_last();
    @eval($eval);
    if ($error != error_get_last()){
        var_dump($eval);
        //add to parser->error();
        print_r(error_get_last());
    }
    $node->condition = $result;
    return $node;
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_cookie($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $attribute = array_shift($argumentList);
    $value = array_shift($argumentList);
    $duration = array_shift($argumentList);
    return $parser->cookie($attribute, $value, $duration);
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_date($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $format = array_shift($argumentList);
    if(empty($format)){
        $format = 'Y-m-d H:i:s';
    }
    elseif($format === true){
        $format = 'Y-m-d H:i:s P';
    }
    elseif(defined($format)){
        $format = constant($format);
    }
    $timestamp = array_shift($argumentList);
    if($timestamp === null){
        return date($format);
    }
    return date($format, $timestamp);
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_empty($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    foreach($argumentList as $nr => $argument){
        $argument = str_replace('"null"', '', $argument);
        $argument = str_replace('"false"', '', $argument);
        $argument = str_replace('""', '', $argument);
        $argument = str_replace('"0.0"', '', $argument);
        $argument = str_replace('"0"', '', $argument);
        $argument = str_replace('0.0', '', $argument);
        $argument = str_replace('0', '', $argument);
        if(empty($argument)){
            unset($argumentList[$nr]);
        }
    }
    return empty($argumentList);
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_error($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $type = array_shift($argumentList);
    $attribute = array_shift($argumentList);
    $val = array_shift($argumentList);
    $result = $parser->error($type, $parser->random() . '.' . $attribute, $val);
    $random = $parser->error($parser->random());
    if(is_object($random)){
        $hasKey = false;
        foreach($random as $key){
            $hasKey = true;
            break;
        }
        if(empty($hasKey)){
            $parser->error('delete', $parser->random());
        }
    }
//     $parser->debug($result);
    return $result;
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_explode($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $delimiter = array_shift($argumentList);
    $string = array_shift($argumentList);
    $limit = array_shift($argumentList);

    if(!empty($limit)){
        return explode($delimiter, $string, $limit);
    } else {
        return explode($delimiter, $string);
    }
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_file_exists($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $argument = reset($argumentList);
    return file_exists($argument);
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_implode($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $glue = '';
    if(count($argumentList) == 1){
        $pieces = array_shift($argumentList);
    } else {
        $glue = array_shift($argumentList);
        $pieces = array_shift($argumentList);
    }
    return implode($glue, $pieces);

}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 * @note
 * - when |more is enabled without PHP_EOL it isnt working (same as readline) thats why
 */

function function_input($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $input = '';
    $text= array_shift($argumentList);
    $text= str_replace('PHP_EOL', PHP_EOL, $text);
    $hidden = array_shift($argumentList);
    if(!empty($hidden)){
        echo $text;
        system('stty -echo');
        $input = trim(fgets(STDIN));
        system('stty echo');
        echo PHP_EOL;
    } else {
        $input = rtrim(readline($text), ' ');
    }

    return $input;
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_isset($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $argument = reset($argumentList);
//     var_dump($argumentList);
    return isset($argument);
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_is_array($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    return is_array(array_shift($argumentList));
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_is_dir($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    return is_dir(array_shift($argumentList));
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_is_executable($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    return is_executable(array_shift($argumentList));
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_is_file($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    return is_file(array_shift($argumentList));
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_is_float($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    return is_float(array_shift($argumentList));
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_is_int($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    return is_int(array_shift($argumentList));
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_is_link($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    return is_link(array_shift($argumentList));
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_is_numeric($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    return is_numeric(array_shift($argumentList));
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_is_object($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    return is_object(array_shift($argumentList));
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_is_readable($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    return is_readable(array_shift($argumentList));
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_is_string($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    return is_string(array_shift($argumentList));
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_is_upload($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    return is_uploaded_file(array_shift($argumentList));
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_is_writeable($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    return is_writeable(array_shift($argumentList));
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_math_round($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $val = array_shift($argumentList);
    $precision = count($argumentList) >= 1 ? array_shift($argumentList) : 0;
    $mode= count($argumentList) >= 1 ? array_shift($argumentList) : PHP_ROUND_HALF_UP;

    return round($val, $precision, $mode);
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_memory_usage($value=null, $argumentList=array(), $parser=null){
    $usage = memory_get_peak_usage(true);
    $format = array_shift($argumentList);
    switch (strtoupper($format)){
        case 'GB' :
            return round($usage/1024/1024/1024, 2) . ' GB';
           break;
        case 'MB' :
            return round($usage/1024/1024, 2) . ' MB';
        break;
        case 'KB' :
            return round($usage/1024, 2) . ' KB';
        break;
        case 'B':
            return $usage . ' B';
        break;
        default:
            return $usage;
        break;

    }

}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_output($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    $hide = array_shift($argumentList);
    $toHide = false;
    if(
        in_array($hide, array(
            'hide',
            'hidden',
            'off'
        )) ||
        $hide === false
       ){
        $toHide = true;
        $parser->data($parser->random() . '.Parser.output.hidden', true);
    }
    elseif(
        in_array($hide, array(
            'show',
            'unhide',
            'on'
        )) ||
        $hide === true
    ){
//         $parser->debug('!!!');
//         $parser->debug($string);
        $parser->data('delete', $parser->random() . '.Parser.output.hidden');
    }

    $hide = $parser->data($parser->random() . '.Parser.output.hidden');

    if(is_object($string) && method_exists($string, '__toString')){
        if(empty($hide) || (!empty($hide) && $toHide === true)){
            echo $string;
        }
        return $string;
    }
    elseif(is_object($string) || is_array($string)){
        $json = $parser->object($string, 'json');
        if(empty($hide) || (!empty($hide) && $toHide === true)){
            echo $json;
        }
        return $string;
    } else {
        $string = str_replace('PHP_EOL', PHP_EOL, $string);
        if(empty($hide) || (!empty($hide) && $toHide === true)){
            echo $string;
        }
        return $string;
    }

}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_parameter($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $parameter= array_shift($argumentList);
    return $parser->parameter($parameter);
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_permission($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $groupList = array();
    $ruleList = array();
    if(isset($argumentList['0'])){
        if(is_string($argumentList['0'])){
            $ruleList = explode(',', $argumentList['0']);
        } else {
            $ruleList = $argumentList['0'];
        }
    }
    if(isset($argumentList['1'])){
        if(is_string($argumentList['1'])){
            $groupList = explode(',', $argumentList['1']);
        } else {
            $groupList = $argumentList['1'];
        }
    }

    $user = $parser->session('user');
    if(empty($user)){
        $username = $parser->parameter('user');
        $password = $parser->parameter('password');

        $user = new Priya\Module\User();
        $user->data($parser->data());
        $validate = $user->validate($username, $password);

        if($validate === false){
            return false;
        } else {
            $user = $parser->session('user', $validate);
        }

    }
    if(isset($user) && isset($user['rule'])){
        $user_ruleList = $user['rule'];
    }
    if(isset($user) && isset($user['group'])){
        $user_groupList = $user['group'];
    }
    foreach($ruleList as $rule){
        $rule = strtolower(trim($rule));
        if(is_array($user_ruleList)){
            foreach($user_ruleList as $user_rule){
                $user_rule = strtolower(trim($user_rule));
                if($rule == $user_rule){
                    return true;
                }
            }
        }
    }
    foreach($groupList as $group){
        $group = strtolower(trim($group));
        if(is_array($user_groupList)){
            foreach($user_groupList as $user_group){
                $user_group = strtolower(trim($user_group));
                if($group == $user_group){
                    return true;
                }
            }
        }
    }
    return false;
}

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

use Priya\Application;
use Priya\Module\Handler;

function function_priya($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $request = array_shift($argumentList);
    if($request === null){
        return false;
    }
    $contentType = array_shift($argumentList);
    $method = array_shift($argumentList);
    $data = array_shift($argumentList);
    if(empty($method) && (!empty($contentType) && $contentType != Handler::CONTENT_TYPE_CLI)){
        $method = Handler::METHOD_GET;
    }
    $app = new Application($parser->autoload(), $data);
    if(!empty($contentType)){
        $app->request('contentType', $contentType);
        $app->handler()->contentType($contentType);
    }
    if(!empty($method)){
        $app->handler()->method($method);
    }
    $app->request('request', $request);
    $app->parser('object')->random($parser->random());
    $result = $app->run();

    $parser->message($app->message());
    $parser->error($app->error());

    return $result;
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_request($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $attribute = array_shift($argumentList);
    $value = array_shift($argumentList);
    return $parser->request($attribute, $value);
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

use Priya\Module\Handler;

function function_route($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
//     var_dump($argumentList);
//     die;
    if(isset($argumentList['name'])){
        $name = $argumentList['name'];
    } else {
        $name = false;
    }
    if(isset($argumentList['attribute'])){
        $attribute = $argumentList['attribute'];
    } else {
        $attribute = false;
    }
    if($name === false){
        $name = array_shift($argumentList);
    }
    if($attribute === false){
        $attribute = array_shift($argumentList);
    }
    if(!is_array($attribute)){
        $attribute = (array) $attribute;
    }
    $route = $parser->route();
    if(empty($route)){
        trigger_error('Route not found');
        //maybe empty string trigger error ?
        return $value;
    }
    $route->data($parser->data());
    return $route->route($name, $attribute);
    /*
    foreach($route->data() as $routeName => $route){
        if(!is_object($route)){
            continue;
        }
        if(!isset($route->path)){
            continue;
        }
        if(strtolower(str_replace(array('/', '\\'),'', $name)) == strtolower(str_replace(array('/', '\\'),'', $routeName))){
            $found = $route;
            break;
        }
    }
    if(empty($found)){
        die('Route not found for (' . $name . ')');
        trigger_error('Route not found for ('. $name.')', E_USER_ERROR);
    } else {
        $route_path = explode('/', trim(strtolower($route->path), '/'));
        foreach($route_path as $part_nr => $part){
            if(substr($part,0, 2) == '{$' && substr($part, -1) == '}'){
                $route_path[$part_nr] = array_shift($attribute);
            }
            if(empty($route_path[$part_nr])){
                unset($route_path[$part_nr]);
            }
        }
        $path = implode('/', $route_path);
        if(stristr($path, Handler::SCHEME_HTTP) === false){
            $path = $parser->data('web.root') . $path;
        }
        return $path;
    }
    return $value;
    */
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_session($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $attribute = array_shift($argumentList);
    $value = array_shift($argumentList);
    return $parser->session($attribute, $value);
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_str_format($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    $string = sprintf($string,
        array_shift($argumentList),
        array_shift($argumentList),
        array_shift($argumentList),
        array_shift($argumentList),
        array_shift($argumentList),
        array_shift($argumentList),
        array_shift($argumentList),
        array_shift($argumentList),
        array_shift($argumentList),
        array_shift($argumentList)
    );
    return $string;
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_str_lc($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    return strtolower($string);
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_str_lc_first($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    return lcfirst($string);
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_str_lc_word($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    $delimiters = array_shift($argumentList);
    if(empty($delimiters)){
        $delimiters = " \t\r\n\f\v";
    }
    $delimiters = str_split($delimiters);
    $list = $parser->explode_single($delimiters, $string);
    foreach($list as $nr => $word){
        $string = str_replace($word, lcfirst($word), $string);
    }
    return $string;

}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_str_reverse($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    return strrev($string);
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_str_scan($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    $format = array_shift($argumentList);
    $scan = sscanf($string, $format);
    if(count($argumentList) > 0){
        $object = new stdClass();
        foreach($argumentList as $attribute){
            $object->{$attribute} = array_shift($scan);
        }
        return $object;
    }
    return $scan;
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_str_translate($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    $from = array_shift($argumentList);
    $to = array_shift($argumentList);
    if(is_object($from)){
        $array = $parser->object($from, 'array');
        if(is_array($array)){
            return strtr($string, $array);
        }
    }
    return strtr($string, $from, $to);
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_str_uc($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    return strtoupper($string);
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_str_uc_first($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    return ucfirst($string);
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_str_uc_word($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $string = array_shift($argumentList);
    $delimiters = array_shift($argumentList);
    if(empty($delimiters)){
        $delimiters = " \t\r\n\f\v";
    }
    $delimiters = str_split($delimiters);
    $list = $parser->explode_single($delimiters, $string);
    foreach($list as $nr => $word){
        $string = str_replace($word, ucfirst($word), $string);
    }
    return $string;
}


/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_time($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $argument = array_shift($argumentList);
    if(empty($argument)){
        return time();
    } else {
        if(is_bool($argument)){
            return microtime(true);
        } else{
            switch(count($argumentList)){
                case 5:
                    return mktime($argument,
                        array_shift($argumentList),
                        array_shift($argumentList),
                        array_shift($argumentList),
                        array_shift($argumentList),
                        array_shift($argumentList)
                    );
                break;
                case 4:
                    return mktime($argument,
                        array_shift($argumentList),
                        array_shift($argumentList),
                        array_shift($argumentList),
                        array_shift($argumentList)
                    );
                break;
                case 3:
                    return mktime($argument,
                        array_shift($argumentList),
                        array_shift($argumentList),
                        array_shift($argumentList)
                    );
                break;
                case 2:
                    return mktime($argument,
                        array_shift($argumentList),
                        array_shift($argumentList)
                    );
                break;
                case 1:
                    return mktime($argument,
                        array_shift($argumentList)
                    );
                break;
                case 0:
                    return mktime($argument);
                   break;
            }
            if(count($argumentList) == 5){
            }
        }
    }
}

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function modifier_basename($value=null, $argumentList=array()){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $value = str_replace(array('\\', '\/'), DIRECTORY_SEPARATOR, $value);
    $basename = basename($value, end($argumentList));
    if(empty($basename)){
        return false;
    }
    return $basename;
}

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function modifier_date_format($value=null, $argumentList=array()){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    if(empty($value) && $value !== 0 && count($argumentList) > 1){
        return end($argumentList);
    }
    if(empty($value) && $value !== 0){
        return false;
    }
    if(is_numeric($value) === false){
        if(count($argumentList) > 1){
            return end($argumentList);
        } else {
            return false;
        }
    }
    return date(reset($argumentList), $value);
}

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function modifier_default($value=null, $argumentList=array()){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    if(empty($value) && count($argumentList) >= 1){
        return end($argumentList);
    }
    return $value;
}

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function modifier_dir_name($value=null, $argumentList=array()){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $value = str_replace(array('\\', '\/'), DIRECTORY_SEPARATOR, $value);
    $dirname = dirname($value);
    if(empty($dirname)){
        return false;
    }
    return $dirname . DIRECTORY_SEPARATOR;
}

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function modifier_json($value=null, $argumentList=array()){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    if(is_object($value)){
        return $value;
    }
    if(is_array($value)){
        return $value;
    }
    if(substr($value,0,1) == '{' && substr($value,1,-1) == '}'){
        return json_decode($value);
    }
    return false;
    /*
    if(is_object($value) || is_array($value)){
        $json = json_encode($value); //remove PRETTY_PRINT
    } else {
        $json = json_decode($value);
    }
    if(!empty($json)){
        return json_encode($json); //remove PRETTY_PRINT
    } else {
        return $value;
    }
    */
}

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function modifier_quote($value=null, $argumentList=array()){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $argument = array_shift($argumentList);
    $escape = array_shift($argumentList);
    if(!empty($escape)){
        $value = str_replace('\'', $escape, $value);
    }
    return  $argument .  $value. $argument;
}
