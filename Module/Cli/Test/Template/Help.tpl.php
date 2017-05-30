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

echo "\t" . 'Version: ' . $this->data('priya.version') . PHP_EOL;
echo "\t" . 'Options:' . PHP_EOL;
echo "\t" . 'test all                   (testing all available tests)' . PHP_EOL;
echo "\t" . 'test parser                (testing parser)' . PHP_EOL;
