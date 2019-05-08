<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *     -    all
 */
$autoload = null;
require_once __DIR__ . DIRECTORY_SEPARATOR . 'Autoload.php';

$app = new Priya\Application($autoload);
echo $app->run();