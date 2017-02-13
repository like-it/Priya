<?php
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

function smarty_function_navigation($params, $template)
{
    $class = array();
    $menu = array();
    $request = array();
    $route = array();
    if(isset($params['class'])){
        if(!is_array($params['class'])){
            $class = explode(',', $params['class']);
            foreach ($class as $nr => $value){
                $class[$nr] = trim($value);
            }
        } else {
            $class = $params['class'];
        }
    }
    $vars = $template->getTemplateVars();

    if(isset($vars['menu'])){
        $menu = $vars['menu'];
    }
    if(isset($vars['request'])){
        $request = $vars['request'];
    }
    $html = '<ul class="' . $class[0] . '">';
    if(empty($class[1])){
        $html .= build('', $menu, $request, $template);
    } else {
        $html .= build($class[1], $menu, $request, $template);
    }
    $html .= '</ul>';
    echo $html;
}

function build($class='', $nodeList=array(), $request=array(), Smarty_Internal_Template $template, $indent=0){
    if(is_array($nodeList)){
        $html = '';
        foreach($nodeList as $key => $node){
            if(!empty($node['route'])){
                $params = $node['route'];
                $node['href'] = smarty_function_route($params, $template);
            }
            if(isset($node['href']) && !empty($node['name']) && !empty($node['title'])){
                if (!empty($request) && !empty($request['name']) && $request['name'] == $node['name']){
                    $class_active = $class . ' active';
                    $class_active = ltrim($class_active, ' ');
                    $html .= '<li class="' . $class_active . '"><a href="' . $node['href'] . '"><p>' . $node['title'] .'</p></a></li>';
                } else {
                    if(empty($class)){
                        $html .= '<li><a href="' . $node['href'] . '"><p>' . $node['title'] .'</p></a></li>';
                    } else {
                        $html .= '<li class="' . $class . '"><a href="' . $node['href'] . '"><p>' . $node['title'] .'</p></a></li>';
                    }
                }
            }
            if(!empty($node['nodeList']) && is_array($node['nodeList'])){
                $class_indent = $class;
                $class_indent .= ' indent';
                if(!empty($indent)){
                    $class_indent .= '-'. ($indent +1);
                }
                $class_indent = ltrim($class_indent, ' ');
                $indent++;
                $html .= build($class_indent, $node['nodeList'], $request, $template, $indent);
                $indent--;
            }
        }
        return $html;
    } else {
        return '';
    }
}
