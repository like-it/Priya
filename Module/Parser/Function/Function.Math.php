<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_math($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    static $allowed_functions = array(
        'math' => true,
        'int' => true,
        'abs' => true,
        'ceil' => true,
        'cos' => true,
        'exp' => true,
        'floor' => true,
        'log' => true,
        'max' => true,
        'min' => true,
        'pi' => true,
        'pow' => true,
        'rand' => true,
        'round' => true,
        'sin' => true,
        'sqrt' => true,
        'srand' => true,
        'tan' => true,
        'oct' => true,
        'dec' => true,
        'deg' => true,
        'rad' => true,
        'hex' => true,
        'bin' => true,
    );

    $equation = array_shift($argumentList);

    // make sure parenthesis are balanced
    if (substr_count($equation, "(") != substr_count($equation, ")")) {
        return false;
    }
    // disallow backticks
    if (strpos($equation, '`') !== false) {
        return false;
    }
    // also disallow dollar signs
    if (strpos($equation, '$') !== false) {
        return false;
    }
    // match all vars in equation, make sure all are passed
    preg_match_all('!(?:0x[a-fA-F0-9]+)|([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)!', $equation, $match);

    foreach ($match[1] as $function) {
        if ($function && !isset($params[$function]) && !isset($allowed_functions[$function])) {
            trigger_error("math: function call '{$function}' not allowed, or missing parameter '{$function}'", E_USER_WARNING);
            return false;
        }
    }
    $equation = str_replace('math.deg.rad', 'deg2rad', $equation);
    $equation = str_replace('math.rad.deg', 'rad2deg', $equation);
    $equation = str_replace('math.dec.bin', 'decbin', $equation);
    $equation = str_replace('math.bin.dec', 'bindec', $equation);
    $equation = str_replace('math.dec.hex', 'dechex', $equation);
    $equation = str_replace('math.hex.dec', 'hexdec', $equation);
    $equation = str_replace('math.dec.oct', 'decoct', $equation);
    $equation = str_replace('math.oct.dec', 'octdec', $equation);
    $equation = str_replace('math.', '', $equation);
    $result = false;
    eval("\$result = " . $equation . ";");
    return $result;
}
