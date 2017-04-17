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
echo "\t" . 'Version: ' . $this->data('version') . PHP_EOL;
echo "\t" . 'Options:' . PHP_EOL;
echo "\t" . 'clear                      (clear cache)' . PHP_EOL;
echo "\t" . 'restore --list             (this will show available restore points)' . PHP_EOL;
echo "\t" . 'route --list               (this will show available cli routes)' . PHP_EOL;
echo "\t" . 'route --list-all           (this will show all available routes)' . PHP_EOL;
?>