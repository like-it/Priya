<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

use Priya\Application;
use Priya\Module\Handler;
use Priya\Module\Parser;

function control_if($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $dir = dirname(Parser::DIR) . Application::DS . 'Function' . Application::DS;
    $result = '';
    foreach($argumentList as $key => $argument){
        if(empty($argument['statement']) && isset($argument['false'])){
            $result .= $argument['false'];
        }
        elseif(!empty($argument['statement']) && isset($argument['true'])){
            if(empty($argument['methodList'])){
                $result .= $argument['true'];
            } else {
                foreach($argument['methodList'] as $nr => $methodList){
                    foreach ($methodList as $method_nr => $methodCollection){
                        foreach ($methodCollection as $method_collection_key => $method){
                            if(empty($method['function'])){
                                continue;
                            }
                            $function = $method['function'];
                            $url = $dir . 'function.' . $function . '.php';
                            $function = 'function_' . $function;

                            if(file_exists($url)){
                                require_once $url;
                            } else {
                                var_dump('missing file: ' . $url);
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
                            $argument['statement'] = str_replace($method_collection_key, $res, $argument['statement']);
                        }

                    }
                }
                //break on comparison
                if("{$argument['statement']}"){
                    var_dump('yes');
                } else {
                    var_dump('no');
                }
                /*
                if(eval('<?php if (' . $argument['statement'] . '){ return true;} else { return false;} ?>')){
                    var_dump('yes');
                } else {
                    var_dump('no');
                }
                */
                var_dump($argument);
                die;
            }
        }
    }
    return $result;
}
