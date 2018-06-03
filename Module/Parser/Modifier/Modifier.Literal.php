<?php
/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function modifier_literal($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    return str_replace(
        array(
            '{literal}' . "\n",
            '{/literal}' . "\n",
            '{literal}',
            '{/literal}',
            '{',
            '}',
        ),
        array(
            '',
            '',
            '',
            '',
            '&#123;',
            '&#125;',
        ),
        $value
    );
}
