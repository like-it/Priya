<?php
function smarty_modifier_size($string='', $type='byte')
{
    if($type == 'byte'){
        $string = $string + 0;
        if($string > (1024 * 1024 * 1024 * 1024 * 1024)){
            $string = round($string  / 1024 / 1024 / 1024 / 1024 / 1024, 2);
            $string .= ' PB';
        }
        elseif($string > (1024 * 1024 * 1024 * 1024)){
            $string = round($string  / 1024 / 1024 / 1024 / 1024, 2);
            $string .= ' TB';
        }
        elseif($string > (1024 * 1024 * 1024)){
            $string = round($string  / 1024 / 1024 / 1024, 2);
            $string .= ' GB';
        }
        elseif($string > (1024 * 1024)){
            $string = round($string  / 1024 / 1024, 2);
            $string .= ' MB';
        }
        elseif($string > 1024){
            $string = round($string  / 1024, 2);
            $string .= ' KB';
        } else {
            $string .= ' B';
        }
    }
    return $string;
}
