<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

use Priya\Application;
use Priya\Module\Parser;

function control_if($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $dir = dirname(Parser::DIR) . Application::DS . 'Function' . Application::DS;
    foreach($argumentList as $key => $argument){
        if(empty($argument['methodList'])){
//             echo 'empty methodList';
            continue;
        }
        foreach($argument['methodList'] as $nr => $methodList){
            foreach ($methodList as $method_nr => $methodCollection){
                foreach ($methodCollection as $method_collection_key => $method){
                    if(empty($method['function'])){
                        continue;
                    }
                    $function = $method['function'];
                    $url = $dir . 'Function.' . ucfirst(strtolower($function)) . '.php';
                    $function = 'function_' . $function;

                    if(file_exists($url)){
                        require_once $url;
                    } else {
                        var_dump('(Control.If) missing file: ' . $url);
                        //remove function ?
                        continue;
                    }

                    if(function_exists($function) === false){
                        var_dump('missing function: ' . $function);
                        //trigger error?
                        continue;
                    }
                    $argList = array();
                    if(!empty($method['argumentList'])){
                        $argList = $method['argumentList'];
                    }
                    $res =  $function($method_collection_key, $argList, $parser);
                    if($res === false || $res === null){
                        $res = 0;	//for good if statement
                    }
                    $argument['statement'] = str_replace($method_collection_key, $res, $argument['statement']);
                    $before = explode('(', $method_collection_key, 2);
                    $count = substr_count($before[0], '!');
                    for($i=0; $i < $count; $i++){
                        $argument['statement'] = '!' . $argument['statement'];
                    }
                }
            }
        }
        /*
         * @todo
         * only own methods can pass and we should add forbidden methods here
         * add elseif statements
         */
        $result = false;
        $eval = 'if(' . $argument['statement'] .'){ $result = true; } else { $result = false; }';
        ob_start();
        eval($eval);
        $error = ob_end_clean();
//         var_dump($error);
        $argument['result'] = $result;
        if(empty($result)){
            return $argument['false'];
        } else {
            return $argument['true'];
        }
    }
}
