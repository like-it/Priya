<?php

use Priya\Application;
use Priya\Module\Handler;

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_priya($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $data = array();
    $data['testing'] = true;
    $data['parser']['request'] = $parser->request('request');
    $request = array_shift($argumentList);
    if($request === null){
        return false;
    }
    $contentType = array_shift($argumentList);
    $method = array_shift($argumentList);
    if(empty($method) && (!empty($contentType) && $contentType != Handler::CONTENT_TYPE_CLI)){
        $method = 'GET';
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
    $result = $app->run();
    return $result;
}
