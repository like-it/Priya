<?php
/**
 * @author 		Remco van der Velde
 * @since 		19-01-2016
 * @version		1.0
 * @changeLog
 *  -	all
 */

use Priya\Application;

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
