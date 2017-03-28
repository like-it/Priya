<?php
use Priya\Application;

/**
 * Smarty plugin
*
* @package Smarty
* @subpackage PluginsFunction
* @version 1.0
* @author Remco van der Velde
* @param array/object/value                    $params   parameters
* @param Smarty_Internal_Template $template template object
* @return html
*/

function smarty_function_css($params, $template)
{
	if(isset($params['node'])){
		$node = $params['node'];
	}
	
	$css = '';
	if(isset($node['target'])){
		$css = $node['target'] . '{';
	}
	if(isset($node['tag'])){
		$key = '';
		$value = '';
		if(is_array($node['tag'])){
			foreach($node['tag'] as $key => $value){
				if(is_array($value)){
					foreach($value as $value_key => $value_value){
						$key .= '-' . $value_key;	
					}
					$value = $value_value;
				}
				$css .= "\n" . $key . ' : ' . $value . ';';
			}
		}
	}	
	if(isset($node['target'])){
		$css .= "\n}";
	}
	return $css;
	

		var_dump($params);
		die;
}
