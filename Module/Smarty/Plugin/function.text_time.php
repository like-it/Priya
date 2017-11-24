<?php
/**
 * @author         Remco van der Velde
 * @since         19-01-2016
 * @version        1.0
 * @changeLog
 *  -    all
 */

function smarty_function_text_time($params, $template)
{
    $result = '';
    $now  = time();
    $time = $now;
    if(isset($params['time'])){
        $time = $params['time'];
    }
    $current = $now - $time;
    $hour  = 60 * 60;
    $day = $hour * 24;

    $amount_day = 0;
    if($current > $day){
        $amount_day = floor($current / $day);
    }
    $amount_hour = floor(($current - ($amount_day * $day)) / $hour);
    $amount_minute = floor(($current - ($amount_day * $day)) / 60);
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
