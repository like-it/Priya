<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-11-07
 * @version		1.0
 * @changeLog
 * 	-	all
 * @note
 *  - In Smarty bash coloring isn't working.
 */

namespace Priya;

$nodeList = $this->data('nodeList');

if(is_array($nodeList)){
    foreach ($nodeList as $node){
        if(isset($node['name']) && isset($node['mtime'])){
            $this->output($node['name'] . ' ' . size(filesize($node['url'])) . ' ' . date('Y-m-d H:i:s', $node['mtime']) . PHP_EOL);
        }
    }
} else {
    $this->output('No restore points found...' .  PHP_EOL);
}


function size($size=0, $unit=null){
    $b = $size . 'B';
    $kb = round($size / 1024, 2) . ' KB';
    $mb = round($size / 1024 / 1024, 2) . ' MB';
    $gb = round($size / 1024 / 1024 / 1024, 2) . ' GB';

    $break = 0.75;
    switch ($unit){
        case 'B':
            return $b;
        break;
        case 'KB':
            return $kb;
        break;
        case 'MB':
            return $mb;
        break;
        case 'GB':
            return $gb;
        break;
        default :
            if($gb >= $break){
                return $gb;
            }elseif($mb >= $break){
                return $mb;
            }
            elseif($kb >= $break){
                return $kb;
            } else {
                return $b;
            }
    }
}