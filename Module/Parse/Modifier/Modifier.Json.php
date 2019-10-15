<?php
/**
 * @author      Remco van der Velde
 * @since       2019-03-16
 * @version     1.0
 * @changeLog
 *     -    all
 */

use Priya\Module\Parse;

function modifier_json(Parse $parse, $variable=[], $parameter=[], $token=[], $keep=false){
    if(
        is_string($variable['execute']) &&
        (
            (
                substr($variable['execute'], 0, 1) == '{' &&
                substr($variable['execute'], 1, -1) == '}'
            ) ||
            (
                substr($variable['execute'], 0, 1) == '[' &&
                substr($variable['execute'], 1, -1) == ']'
            )
        )
    ){
        $decode = json_decode($variable['execute']);
        if($decode !== null){
            $variable['execute'] = $decode;
        } else {
            $source = $parse->data('priya.parse.read.url');
            if($source !== null){
                throw new Exception('Unable to decode json on line: ' . $variable['row'] . ' ' . $variable['column'] . ' in: ' . $source);
            } else {
                throw new Exception('Unable to decode json on line: ' . $variable['row'] . ' ' . $variable['column']);
            }
        }
    } else {
        $variable['execute'] = json_encode($variable['execute'], JSON_PRETTY_PRINT);
    }
    $variable['is_executed'] = true;
    $token[$variable['token']['nr']] = $variable;
    return $token;
}