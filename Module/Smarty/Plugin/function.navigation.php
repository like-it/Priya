<?php
/**
 * @author 		Remco van der Velde
 * @since 		19-01-2016
 * @version		1.0
 * @changeLog
 *  -	all
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
                if(!empty($node['method']) && !empty($node['target'])){
                    $tag_a = '<a href="#" data-request="' . $node['href'] . '" data-method="' . $node['method'] .'" data-target="' . $node['target'] .'">';
                } else {
                    if(!empty($node['target']) && (!empty($node['target']['name']) || !empty($node['target']['labeled']))){
                        if(!empty($node['target']['name'])){
                            $tag_a = '<a href="' . $node['href'] . '" target="' . $node['target']['name'] . '">';
                        } else {
                            $tag_a = '<a href="' . $node['href'] . '" target="' . $node['target']['labeled'] . '">';
                        }
                    } else {
                        $tag_a = '<a href="' . $node['href'] . '">';
                    }
                }
                if (!empty($request) && !empty($request['name']) && $request['name'] == $node['name']){
                    $class_active = $class . ' active';
                    $class_active = ltrim($class_active, ' ');
                    $html .= '<li class="' . $class_active . '">' . $tag_a . '<p>' . $node['title'] .'</p></a></li>';
                } else {
                    if(empty($class)){
                        $html .= '<li>' . $tag_a . '<p>' . $node['title'] .'</p></a></li>';
                    } else {
                        $html .= '<li class="' . $class . '">' . $tag_a . '<p>' . $node['title'] .'</p></a></li>';
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
