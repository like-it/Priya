<?php
use Priya\Module\Handler;

/**
 * @author         Remco van der Velde
 * @since         19-01-2016
 * @version        1.0
 * @changeLog
 *  -    all
 */

function smarty_function_route($params, $template)
{
    $name = '';
    $attribute = array();
    if(isset($params['name'])){
        $name = $params['name'];
    }
    if(isset($params['attribute'])){
        if(is_array($params['attribute'])){
            $attribute = $params['attribute'];
        } else {
            $attribute = (array) $params['attribute'];
        }
    }
    $vars = $template->getTemplateVars();

    if(isset($vars['route'])){
        $found = false;
        foreach($vars['route'] as $routeName => $route){
            if(!is_array($route)){
                continue;
            }
            if(!isset($route['path'])){
                continue;
            }
            if(strtolower(str_replace(array('/', '\\'),'', $name)) == strtolower(str_replace(array('/', '\\'),'', $routeName))){
                $found = $route;
                break;
            }
        }
        if(empty($found)){
            throw new Exception('Route not found for ('. $name.')');
        } else {
            $route_path = explode('/', trim($route['path'], '/'));
            foreach($route_path as $part_nr => $part){
                if(substr($part,0, 2) == '{$' && substr($part, -1) == '}'){
                    $route_path[$part_nr] = array_shift($attribute);
                }
                if(empty($route_path[$part_nr])){
                    unset($route_path[$part_nr]);
                }
            }
            $path = implode('/', $route_path);
            if(!empty($path)){
                $path .= '/';
            }
            if(stristr($path, Handler::SCHEME_HTTP) === false){
                if(isset($vars['web']) && isset($vars['web']['root'])){
                    $path = $vars['web']['root'] . $path;
                }
            }
            return $path;
        }
    }

}
