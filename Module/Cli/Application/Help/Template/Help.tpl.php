<?php
/**
 * @author         Remco van der Velde
 * @since         2016-11-07
 * @version        1.0
 * @changeLog
 *     -    all
 * @note
 *  - In Smarty bash coloring isn't working.
 */

namespace Priya;

$this->cli('create', 'Logo');

echo PHP_EOL;
echo 'Priya <route> <parameter(s)>' . PHP_EOL;
echo "  " . 'help                           (Priya help)' . PHP_EOL;
echo "  " . 'version                        (Priya version)' . PHP_EOL;
echo "  " . 'license                        (Priya license)' . PHP_EOL;
echo "  " . 'locate <route>                 (locate a route <route> name in json format)' . PHP_EOL;
echo "  " . 'route --list                   (this will show available cli routes)' . PHP_EOL;
echo "  " . 'route --list-all               (this will show all available routes)' . PHP_EOL;
echo "  " . 'test <class> <file>            (this will test the <class> with <file>)' . PHP_EOL;
echo "  " . 'cache clear                    (clear cache)' . PHP_EOL;
echo "  " . 'config                         (current configuration in json format)' . PHP_EOL;
echo "  " . 'parser                         (generates a parser file (speed up parsing))' . PHP_EOL;
# echo "  " . 'javascript create              (generates the javascript source)' . PHP_EOL;