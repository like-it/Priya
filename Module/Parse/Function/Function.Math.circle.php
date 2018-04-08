<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

use Priya\Module\Parse\Tag;

function function_math_circle($tag=array(), $parser=null){
    $argumentList = $tag[Tag::PARAMETER];
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $float = array_shift($argumentList);
    $type = array_shift($argumentList);

    if($type === null){
        $type = 'radius';
    }
    switch($type) {
        case 'radius':
            $radius = $float;
        break;
        case 'diameter':
            $radius = $float / 2;
        break;
        case 'circumflex' :
            throw new Exception('Math.circle:Undefined type: circumflex');
        break;
    }
    $pow = pow($radius, 2);
    $tag[Tag::EXECUTE] = $pow * M_PI;
    return $tag;
}
