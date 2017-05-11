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
