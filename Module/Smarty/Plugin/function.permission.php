<?php
/**
 * Smarty plugin
*
* @package Smarty
* @subpackage PluginsFunction
*/

/**
 *
* @link http://www.smarty.net/manual/en/language.function.mailto.php {mailto}
*          (Smarty online manual)
* @version 1.0
* @author remco.vandervelde
* @param array/object/value                    $params   parameters
* @param Smarty_Internal_Template $template template object
* @return boolean
* @todo
* add param for method vars...
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
// 	echo '<a href="' .$vars['web']['root'] . 'logout/">logout</a>';
// 	die;
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
