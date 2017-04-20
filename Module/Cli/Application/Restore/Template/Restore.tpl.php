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

if($this->data('options')){
    echo "\t" . 'restore                  (this will restore the installation from ' . $this->data('dir.priya.data') . 'Restore/' . PHP_EOL;
}