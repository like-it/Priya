<?php
/**
 * @author 		Remco van der Velde
 * @since 		19-07-2015
 * @version		1.0
 * @changeLog	
 * 	-	all	
 */
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Vendor/Priya/Application/Autoload.php';

$app = new Priya\Application($autoload);
$app->run();