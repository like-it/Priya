<?php

use Priya\Module\Data;

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

function function_dom_node($function=array(), $argumentList=array(), $parser=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $node =  array_shift($argumentList);

    if(empty($node)){
        $function['execute'] = '';
        return $function;
    }
    $module = new Priya\Module\Data();
    $module->data('node', $node);
    $module->data('attribute', clone $node);
    $module->data('delete', 'attribute.route');
    $module->data('delete', 'attribute.data');
    $module->data('delete', 'attribute.tag');
    $module->data('delete', 'attribute.content');

    $attribute = '';
    $tmp = $module->data('attribute');

    if(is_array($tmp) || is_object($tmp)){
        foreach($tmp as $key => $value){
            $attribute .= $key . '="' . $value . '" ';
        }
    }
    $attribute = rtrim($attribute, ' ');

    $data = '';
    if($module->data('node.route') && empty($module->data('node.data.request'))){
        $data .= 'data-request="' . $module->data('node.route') . '" ';
    }

    $tmp = $module->data('node.data');

    if(is_array($tmp) || is_object($tmp)){
        foreach($tmp as $key => $value){
            $data .= 'data-' . $key . '="' . $value . '" ';
        }
    }
    $data = rtrim($data, ' ');

    $dom = '<' . $module->data('node.tag') . ' ';
    $dom .= $attribute . ' ';
    $dom .= $data . ' ';
    $dom = rtrim($dom, ' ') . '>';
    if($module->data('node.content')){
        $dom .= "\n";
        $dom .= $module->data('node.content');
        $dom .= "\n";
    }
    $dom .= '</' . $module->data('node.tag') . '>';
    $function['execute'] = $dom;
    return $function;
}
