<?php
use Priya\Application;
use Priya\Module\Core\Object;

/**
 * Smarty plugin
*
* @package Smarty
* @subpackage PluginsFunction
* @version 1.0
* @author Remco van der Velde
* @param array/object/value                    $params   parameters
* @param Smarty_Internal_Template $template template object
* @return html
*/

function smarty_function_text_time($params, $template)
{
    $result = '';
    $time = '';
    if(isset($params['time'])){
        $time = $params['time'];
    }
    $now  = time();
    $current = $now - $time;
    $hour  = 60 * 60;
    $day = $hour * 24;

//     return $current/ 60;
    $amount_day = 0;
    if($current > $day){
        $amount_day = floor($current / $day);
    }
    $amount_hour = floor(($current - ($amount_day * $day)) / $hour);
    $amount_minute = floor(($current - ($amount_day * $day)) / 60);
//     $amount_second = $current - ($amount_day * $day) - ($amount_minute * 60);

    if($amount_day > 0){
        if($amount_day == 1){
            return '1 day ago';
        } else{
            return $amount_day . ' days ago';
        }
    }
    elseif($amount_hour > 0){
        if($amount_hour == 1){
            return '1 hour ago';
        } else {
            return $amount_hour . ' hours ago';
        }
    } else {
        if($amount_minute < 1){
            return 'just now';
        }
        elseif($amount_minute < 2){
            return  '1 minute ago';
        } else {
            return  $amount_minute . ' minutes ago';
        }
    }
}
