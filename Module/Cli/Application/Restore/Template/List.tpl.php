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
            echo "\t" . $node['name'] . ' ' . date('Y-m-d H:i:s', $node['mtime']) . PHP_EOL;
        }
    }
}