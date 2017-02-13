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

function smarty_function_require($params, $template)
{
    $vars = $template->getTemplateVars();
    $caller = '';
    $fetch = '';
    $result = new Priya\Module\Core\Result(new Priya\Module\Handler());

    if(isset($params['environment'])){
        $result->data('environment', Application::ENVIRONMENT);
    }
    if(isset($vars['autoload'])){
        $result->data('autoload', $vars['autoload']);
    }
    if(isset($vars['module'])){
        $caller = $vars['module'];
    }
    $url = $result->locateTemplate($params['file'], 'tpl', $caller);
    if(!empty($url)){
        $fetch = $template->fetch($url);
    }
    if(isset($params['assign'])){
        $template->assign($params['assign'], $fetch);
    } else {
        return $fetch;
    }
}
