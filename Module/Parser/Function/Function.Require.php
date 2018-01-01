<?php

/**
 * @author          Remco van der Velde
 * @since           2017-04-20
 * @version         1.0
 * @changeLog
 *     -    all
 *
 * @todo
 *  - require so error on fail
 *  - add include as require without fail
 */

function function_require($function=array(), $argumentList=array(), $parser=null, $data=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $url = array_shift($argumentList);
    if($data === null){
        $data = $parser->data();
    }
    $file = new Priya\Module\File();

    $tmp = $parser->data('require.' . $parser->random());
    if($tmp){
        $data = $parser->object_merge($data, $tmp);
    }
    if($file->extension($url) == 'json'){
        $require = new Priya\Module\Parser($parser->handler(), $parser->route(), $data);
        $require->read($url);
        $read = '';
    } else {
        $require = new Priya\Module\Parser($parser->handler(), $parser->route(), $data);
        $read = $require->compile($file->read($url));
    }
    $parser->data('require.' . $parser->random(), $require->data());
    $function['execute'] = $read;
    return $function;
}
