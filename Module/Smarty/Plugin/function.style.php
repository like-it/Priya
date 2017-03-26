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

function smarty_function_style($params, $template)
{
    $vars = $template->getTemplateVars();
    $caller = '';
    $fetch = '';
    $link = '';
    $app = new Priya\Application();
//     $result = new Priya\Module\Core\Result($app->handler(), $app->route(), $varss);

    if(isset($params['link'])){
        $link = $params['link'];
    }
    /*
    if(isset($vars['autoload'])){
        $result->data('autoload', $vars['autoload']);
    }
    */
    if(isset($vars['module'])){
        $caller = $vars['module'];
    }
    $url = explode('href="', $link, 2);
    array_shift($url);
    $url = explode('"', reset($url), 2);
    $url = array_shift($url);
    $url = explode('?', $url, 2);
    $url = array_shift($url);

    $url = $app->handler()->removeHost($url);

    if(isset($vars['dir']) && isset($vars['dir']['vendor'])){
        $url = $vars['dir']['vendor'] . str_replace('/', Priya\Application::DS, $url);
    }
    if(file_exists($url)){
        $file = new Priya\Module\File();
        if(isset($params['assign'])){
            $template->assign($params['assign'], $file->read($url));
        } else {
            return $file->read($url);
        }
    }
}
