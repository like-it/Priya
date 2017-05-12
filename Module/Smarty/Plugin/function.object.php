<?php
/**
 * @author 		Remco van der Velde
 * @since 		19-01-2016
 * @version		1.0
 * @changeLog
 *  -	all
 */

use Priya\Application;
use Priya\Module\Core\Object;

function smarty_function_object($params, $template)
{
    $input = '';
    $output = 'object';
    $type = 'root';
    $ignoreList = array();
    if(isset($params['input'])){
        $input = $params['input'];
    }
    if(isset($params['output'])){
        $output = $params['output'];
    }
    if(isset($params['type'])){
        $type = $params['type'];
    }
    if(isset($params['ignore'])){
        $ignoreList = $params['ignore'];
    }
    if(!is_array($ignoreList)){
        $ignoreList = explode(',', $ignoreList);
    }
    $data = new Priya\Module\Core\Data();
    $data->data('node', $data->object($input));
    foreach ($ignoreList as $ignore){
        $data->data('delete', 'node.' . $ignore);
    }
    $input = $data->data('node');
    return $data->object($input, $output, $type);
}
