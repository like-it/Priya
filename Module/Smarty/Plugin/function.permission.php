<?php
/**
 * @author 		Remco van der Velde
 * @since 		19-01-2016
 * @version		1.0
 * @changeLog
 *  -	all
 */

function smarty_function_permission($params, $template)
{
    $ruleList = array();
    $groupList = array();
    $user_ruleList = array();
    $user_groupList = array();

    if(isset($params['rule'])){
        $ruleList = explode(',', $params['rule']);
    }
    if(isset($params['group'])){
        $groupList = explode(',', $params['group']);
    }
    $vars = $template->getTemplateVars();
    if(isset($vars['user']) && isset($vars['user']['rule'])){
        $user_ruleList = $vars['user']['rule'];
    }
    if(isset($vars['user']) && isset($vars['user']['group'])){
        $user_groupList = $vars['user']['group'];
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
