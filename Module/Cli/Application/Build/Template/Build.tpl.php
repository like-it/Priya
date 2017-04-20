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

echo 'build in "' .$this->parameter(3) . '" with version (' . $this->data('version') . ')' . PHP_EOL;