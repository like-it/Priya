<?php
/**
 * @author         Remco van der Velde
 * @since         19-01-2016
 * @version        1.0
 * @changeLog
 *  -    all
 */

use Priya\Application;

function smarty_function_style($params, $template)
{
    $vars = $template->getTemplateVars();
    $caller = '';
    $fetch = '';
    $link = '';
    $app = new Priya\Application();

    if(isset($params['link'])){
        $link = $params['link'];
    }
    if(
        isset($vars['module']) &&
        isset($vars['module']['name'])
    ){
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
        $url = $vars['dir']['vendor'] . str_replace(array('/', '\\'), Priya\Application::DS, $url);
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
