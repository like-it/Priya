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


if($this->error('package')){
//     $this->data('style.package.color', );
    $this->data('style.package.background.color', 1);
    $this->write('output', 'File not found (' . $this->request('package') . ') ', 'style.package');
    $this->write('output', PHP_EOL);
} else {
    echo 'Build in "' .$this->request('target') . '" with version (' . $this->data('version') . ')' . PHP_EOL;

}