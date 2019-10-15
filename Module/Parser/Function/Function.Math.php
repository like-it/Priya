<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_math($function=array(), $argumentList=array(), $parser=null){
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
        'random' => true,
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
            throw new Exception("math: function call '{$function}' not allowed, or missing parameter '{$function}'");
            return false;
        }
    }
    $equation = str_ireplace('math.deg.rad', 'deg2rad', $equation);
    $equation = str_ireplace('math.rad.deg', 'rad2deg', $equation);
    $equation = str_ireplace('math.dec.bin', 'decbin', $equation);
    $equation = str_ireplace('math.bin.dec', 'bindec', $equation);
    $equation = str_ireplace('math.dec.hex', 'dechex', $equation);
    $equation = str_ireplace('math.hex.dec', 'hexdec', $equation);
    $equation = str_ireplace('math.dec.oct', 'decoct', $equation);
    $equation = str_ireplace('math.oct.dec', 'octdec', $equation);
    $equation = str_ireplace('math.cos.arc.inv', 'acosh', $equation);
    $equation = str_ireplace('math.cos.arc', 'acos', $equation);
    $equation = str_ireplace('math.cos.inv', 'cosh', $equation);
    $equation = str_ireplace('math.sin.arc.inv', 'asinh', $equation);
    $equation = str_ireplace('math.sin.arc', 'asin', $equation);
    $equation = str_ireplace('math.sin.inv', 'sinh', $equation);
    $equation = str_ireplace('math.tan.arc.inv', 'atanh', $equation);
    $equation = str_ireplace('math.tan.arc', 'atan', $equation);
    $equation = str_ireplace('math.tan.inv', 'tanh', $equation);
    if(version_compare(PHP_VERSION, $parser::PHP_MIN_VERSION, '>=')){
        $equation = str_ireplace('math.random', 'random_int', $equation);
    } else {
        $equation = str_ireplace('math.random', 'rand', $equation);
    }
    $equation = str_ireplace('math.', '', $equation);
    $result = false;
    eval("\$result = " . $equation . ";");
    $function['execute'] = $result;
    return $function;
}
