<?php

/**
 * @author 		Remco van der Velde
 * @since 		2017-04-20
 * @version		1.0
 * @changeLog
 * 	-	all
 */

function function_permission($value=null, $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $groupList = array();
    $ruleList = array();
    if(isset($argumentList['rule'])){
        if(is_string($argumentList['rule'])){
            $ruleList = explode(',', $argumentList['rule']);
        } else {
            $ruleList = $argumentList['rule'];
        }
    }
    if(isset($argumentList['group'])){
        if(is_string($argumentList['group'])){
            $groupList = explode(',', $argumentList['group']);
        } else {
            $groupList = $argumentList['group'];
        }
    }

    $user = $parser->session('user');
    if(empty($user)){
        $username = $parser->parameter('user');
        $password = $parser->parameter('password');

        $user = new Priya\Module\User();
        $user->data($parser->data());
        $validate = $user->validate($username, $password);

        if($validate === false){
            return false;
        } else {
            $user = $parser->session('user', $validate);
        }

    }
    if(isset($user) && isset($user['rule'])){
        $user_ruleList = $user['rule'];
    }
    if(isset($user) && isset($user['group'])){
        $user_groupList = $user['group'];
    }
    foreach($ruleList as $rule){
        $rule = strtolower(trim($rule));
        if(is_array($user_ruleList)){
            foreach($user_ruleList as $user_rule){
                $user_rule = strtolower(trim($user_rule));
                if($rule == $user_rule){
                    return true;
                }
            }
        }
    }
    foreach($groupList as $group){
        $group = strtolower(trim($group));
        if(is_array($user_groupList)){
            foreach($user_groupList as $user_group){
                $user_group = strtolower(trim($user_group));
                if($group == $user_group){
                    return true;
                }
            }
        }
    }
    return false;
}
