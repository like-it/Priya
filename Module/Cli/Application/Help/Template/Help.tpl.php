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
echo "  " . 'locate <route>                 (this will list all available locations of the class <route> name)' . PHP_EOL;
echo "  " . 'route --list                   (this will show available cli routes)' . PHP_EOL;
echo "  " . 'route --list-all               (this will show all available routes)' . PHP_EOL;
echo "  " . 'test <class> <file>            (this will test the <class> with <file>)' . PHP_EOL;
echo "  " . 'cache/clear                    (clear cache)' . PHP_EOL;
echo "  " . 'config                         (current configuration in json format)' . PHP_EOL;
/*
echo "  " . 'install                        (install Priya)' . PHP_EOL;
echo "  " . 'update --version=<version>       (update Priya to latest version)' . PHP_EOL;
echo "  " . 'package                        (create a package)' . PHP_EOL;
echo "  " . 'build <package> <target>      (build Priya)' . PHP_EOL;
echo "  " . 'help                        (Priya help)' . PHP_EOL;
echo "  " . 'push                        (push Priya to server)' . PHP_EOL;
echo "  " . 'pull                        (pull Priya from server)' . PHP_EOL;
echo "  " . 'restore create                 (this will create a restore point)' . PHP_EOL;
echo "  " . 'restore --list                 (this will show available restore points)' . PHP_EOL;
echo "  " . 'route --list                   (this will show available cli routes)' . PHP_EOL;
echo "  " . 'route --list-all               (this will show all available routes)' . PHP_EOL;
echo "  " . 'test <class> <file>             (this will test the <class> with <file>)' . PHP_EOL;
echo "  " . 'locate <route>             (this will list all available locations of the class <route> name)' . PHP_EOL;
*/