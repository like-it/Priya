<?php
function smarty_modifier_date($string='', $format='byte')
{
    if($string === null){
        return '';
    }
    return date($format, $string);
}
