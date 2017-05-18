<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_time($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $argument = array_shift($argumentList);
    if(empty($argument)){
        return time();
    } else {
        if(is_bool($argument)){
            return microtime(true);
        } else{
            switch(count($argumentList)){
                case 5:
                    return mktime($argument,
                        array_shift($argumentList),
                        array_shift($argumentList),
                        array_shift($argumentList),
                        array_shift($argumentList),
                        array_shift($argumentList)
                    );
                break;
                case 4:
                    return mktime($argument,
                        array_shift($argumentList),
                        array_shift($argumentList),
                        array_shift($argumentList),
                        array_shift($argumentList)
                    );
                break;
                case 3:
                    return mktime($argument,
                        array_shift($argumentList),
                        array_shift($argumentList),
                        array_shift($argumentList)
                    );
                break;
                case 2:
                    return mktime($argument,
                        array_shift($argumentList),
                        array_shift($argumentList)
                    );
                break;
                case 1:
                    return mktime($argument,
                        array_shift($argumentList)
                    );
                break;
                case 0:
                    return mktime($argument);
                   break;
            }
            if(count($argumentList) == 5){
            }
        }
    }
}
