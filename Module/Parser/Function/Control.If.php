<?php

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

function control_if($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $dir = dirname(Parser::DIR) . Application::DS . 'Function' . Application::DS;
    $compile_list = array();
    foreach($argumentList as $key => $argument){
        if(empty($argument['methodList'])){
            $argumentList[$key] = evaluate($argumentList[$key]);
            if($argumentList[$key]['condition'] === true){
                $argumentList[$key]['result'] = $argumentList[$key]['true'];
            }
            elseif($argumentList[$key]['condition'] == 'ignore'){
                continue;
            }
            else {
                $argumentList[$key]['result'] = $argumentList[$key]['false'];
            }
            continue;
        }
        $argumentList[$key]['statement'] = $parser->execMethodList($argumentList[$key]['methodList'], $argumentList[$key]['statement']);
        $argumentList[$key] = evaluate($argumentList[$key]);
        if($argumentList[$key]['condition'] === true){
            $argumentList[$key]['result'] = $argument['true'];
        }
        elseif($argumentList[$key]['condition'] == 'ignore'){
            continue;
        }
        else {
            $argumentList[$key]['result'] = $argument['false'];
        }
    }
    $result = '';
//     var_dump('*************************');
//     var_dump($argumentList);
    foreach ($argumentList as $key => $argument){
        if(isset($argument['result'])){
            $result .= $argument['result'];
        }
    }
    $parser->argument($argumentList);
    return $result;
}

function evaluate($argument = array()){
    /*
     * @todo
     * only own methods can pass and we should add forbidden methods here
     * add elseif statements
     */
    if(!is_array($argument)){
        $argument = (array) $argument;
    }
    $result = false;
    $eval = 'if(' . $argument['statement'] .'){ $result = true; } else { $result = false; }';
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
    $argument['condition'] = $result;
    return $argument;
}
