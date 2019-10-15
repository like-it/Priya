<?php
/**
 * @author Remco van der Velde
 * @copyright(c) Remco van der Velde
 * @since 2019-03-18
 *
 *
 * multiple parameters uses a logical AND
 */

use Priya\Module\Parse;
use Priya\Module\Parse\Token;
use Priya\Module\Cli;

function function_system_user_name(Parse $parse, $method=[], $token=[], $keep=false, $tag_remove=true){

    if(
        !isset($method['method']) ||
        !isset($method['token']) ||
        !isset($method['token']['nr'])
    ){
        return $token;
    }
    if($method['method']['name'] != 'system.user.name'){
        return $token;
    }
    Cli::execute_shell('whoami', $output);

    $method['execute'] = $outout;
    $method['type'] = Token::TYPE_STRING;
    $token[$method['token']['nr']] = $method;
    return $token;
}