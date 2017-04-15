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

function smarty_function_nodelist($params, $template)
{
    $class = array();
    $nodeList = array();
    $name = '';
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

    if(isset($params['name'])){
        $name = $params['name'];
    } else {
        $name = 'name';
    }
    $attribute = '';
    $node = '';
    foreach ($params as $key => $value){
        if(substr($key,0, 5) == 'data-'){
            $attribute .= $key . '="'.$value .'" ';
        }
        if(substr($key,0, 10) == 'node-data-'){
            $node .= substr($key, 5) . '="'.$value .'" ';
        }
    }
    $attribute = rtrim($attribute, ' ');
    $node = rtrim($node, ' ');

    if(isset($vars['nodeList'])){
        $nodeList = $vars['nodeList'];
    }
    if(!empty($attribute)){
        $html = '<ul class="' . $class[0] . '" ' . $attribute . '>';
    } else {
        $html = '<ul class="' . $class[0] . '">';
    }
    if(empty($class[1])){
        $html .= build('', $node, $nodeList, $name, $template);
    } else {
        $html .= build($class[1], $node, $nodeList, $name, $template);
    }
    $html .= '</ul>';
    echo $html;
}

function build($class='', $item='', $nodeList=array(), $name='', Smarty_Internal_Template $template, $indent=0){
    if(is_array($nodeList)){
        $html = '';
        foreach($nodeList as $key => $node){
            $data = $item;
            $data = str_replace('"', '}"', $data);
            $data = str_replace('=}"', '="{$', $data);

            $parser = new Priya\Module\Core\Parser();
            $data = $parser->data('object')->compile($data, $node);

            if(isset($node[$name])){
                if(empty($class)){
                    $html .= '<li id="nodeList-' . $node['jid'] . '" data-jid="' .  $node['jid'] . '" '. $data .'><p>' . $node[$name] .'</p></li>';
                } else {
                    $html .= '<li id="nodeList-' . $node['jid'] . '" data-jid="' .  $node['jid'] . '"  '. $data .' class="' . $class . '"><p>' . $node['title'] .'</p></li>';
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
                $html .= build($class_indent, $item, $node['nodeList'], $name, $template, $indent);
                $indent--;
            }
        }
        return $html;
    } else {
        return '';
    }
}
