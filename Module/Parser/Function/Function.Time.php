<?php
/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_time($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $argument = array_shift($argumentList);
    if(empty($argument)){
        $function['execute'] = time();
    } else {
        if(is_bool($argument)){
            $function['execute'] = microtime(true);
        } else{
            switch(count($argumentList)){
                case 5:
                    $function['execute'] = mktime($argument,
                        array_shift($argumentList),
                        array_shift($argumentList),
                        array_shift($argumentList),
                        array_shift($argumentList),
                        array_shift($argumentList)
                    );
                break;
                case 4:
                    $function['execute'] = mktime($argument,
                        array_shift($argumentList),
                        array_shift($argumentList),
                        array_shift($argumentList),
                        array_shift($argumentList)
                    );
                break;
                case 3:
                    $function['execute'] = mktime($argument,
                        array_shift($argumentList),
                        array_shift($argumentList),
                        array_shift($argumentList)
                    );
                break;
                case 2:
                    $function['execute'] = mktime($argument,
                        array_shift($argumentList),
                        array_shift($argumentList)
                    );
                break;
                case 1:
                    $function['execute'] = mktime($argument,
                        array_shift($argumentList)
                    );
                break;
                case 0:
                    $function['execute'] = mktime($argument);
                   break;
            }
            if(count($argumentList) == 5){
            }
        }
    }
    return $function;
}
