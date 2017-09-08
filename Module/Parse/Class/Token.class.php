<?php

namespace Priya\Module\Parse;

class Token extends Core {

    public static function all($string=''){
        $tokens = token_get_all('<?php print(' . $string . ');');
        array_shift($tokens); //remove open tag
        array_shift($tokens); //remove print
        array_shift($tokens); //remove (
        array_pop($tokens); //remove ;
        array_pop($tokens); //remove )

        foreach ($tokens as $key => $token){
            if(is_array($token)){
                $tokens[$key][2] = token_name($token[0]);
            } else {
                $tokens[$key] = array(0 => -1, 1 => $token);
            }
            if(empty($tokens[$key][2])){
                switch($tokens[$key][1]){
                    case '(' :
                        $tokens[$key][2] = 'T_PARENTHESE_OPEN';
                    break;
                    case ')' :
                        $tokens[$key][2] = 'T_PARENTHESE_CLOSE';
                    break;
                    case '[' :
                        $tokens[$key][2] = 'T_SQUARE_BRACKET_OPEN';
                    break;
                    case ']' :
                        $tokens[$key][2] = 'T_SQUARE_BRACKET_CLOSE';
                    break;
                    case '{' :
                        $tokens[$key][2] = 'T_BRACKET_OPEN';
                    break;
                    case '}' :
                        $tokens[$key][2] = 'T_BRACKET_CLOSE';
                    break;
                    case ',' :
                        $tokens[$key][2] = 'T_COMMA';
                    break;
                    case ';' :
                        $tokens[$key][2] = 'T_SEMI_COLON';
                    break;
                }
            }
            if(empty($tokens[$key][2])){
                $operators = Operator::Arithmetic();
                if(in_array($tokens[$key][1], $operators)){
                    $tokens[$key][0] = -2;
                    $tokens[$key][2] = 'T_OPERATOR_ARITHMETIC';
                }
                $operators = Operator::Bitwise();
                if(in_array($tokens[$key][1], $operators)){
                    $tokens[$key][0] = -3;
                    $tokens[$key][2] = 'T_OPERATOR_BITWISE';
                }
            }
        }
        return $tokens;
    }

}



