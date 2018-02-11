<?php

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

    $module = new Priya\Module\Data();
    $module->data('node', $node);
    $module->data('attribute', clone $node);
    $module->data('delete', 'attribute.route');
    $module->data('delete', 'attribute.data');
    $module->data('delete', 'attribute.tag');
    $module->data('delete', 'attribute.content');


    $attribute = '';
    foreach($module->data('attribute') as $key => $value){
        $attribute .= $key . '="' . $value . '" ';
    }
    $attribute = rtrim($attribute, ' ');

    $data = '';

    //null assignment should be debugged...

    var_dump($module->data('node'));
    var_dump($attribute);
    var_dump($module);
    $data = '';
//     unset($attribute)

//     $data = '<' . $node.tag . '>'
    $function['execute'] = $data;
    return $function;
}
