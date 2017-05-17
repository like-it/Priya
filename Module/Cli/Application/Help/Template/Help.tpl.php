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

$this->cli('create', 'Logo');

echo PHP_EOL;
echo 'Options: (parameter)' . PHP_EOL;
echo "  " . 'version            	    	(Priya version)' . PHP_EOL;
echo "  " . 'install            	    	(install Priya)' . PHP_EOL;
echo "  " . 'build <package> <target>      (build Priya)' . PHP_EOL;
echo "  " . 'help            	    	(Priya help)' . PHP_EOL;
echo "  " . 'push            	    	(push Priya to server)' . PHP_EOL;
echo "  " . 'pull            	    	(pull Priya from server)' . PHP_EOL;
echo "  " . 'cache/clear                	(clear cache)' . PHP_EOL;
echo "  " . 'restore --list             	(this will show available restore points)' . PHP_EOL;
echo "  " . 'route --list               	(this will show available cli routes)' . PHP_EOL;
echo "  " . 'route --list-all           	(this will show all available routes)' . PHP_EOL;
echo "  " . 'test parser <file>         	(this will test the parser with <file>)' . PHP_EOL;