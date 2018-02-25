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
    $original = $data;
    $url = array_shift($argumentList);
    if($data === null){
        $data = $parser->data();
    }
    $file = new Priya\Module\File();

    $tmp = $parser->data('require.' . $parser->random());
    if($tmp){
        $data = $parser->object_merge($data, $tmp);
    }
    $read = '';
    $exists = true;
    if(file_exists($url) === false && substr($url, 0, 4) !== \Priya\Module\File::SCHEME_HTTP){
        $exists = false;
        throw new Exception('File (' . $url . ') doesn\'t exists');
    }
    elseif(
        $exists === true &&
        $file->extension($url) == 'json'
    ){
        $parser->read($url);
        $read = '';
    }
    elseif(
        $exists === true
    ) {
        $read = $file->read($url);
        $read = $parser->compile($read, $data);
    }
    $function['execute'] = $read;
    return $function;
}
