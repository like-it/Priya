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

$this->autoload()->expose(true);

$route = $this->route()->route($this->parameter(2));

if(empty($route)){
    $this->autoload()->locate($this->parameter(2));
} else {
    $this->autoload()->locate($route);
}
echo PHP_EOL;