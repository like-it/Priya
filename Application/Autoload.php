<?php
/**
 * @author 		Remco van der Velde
 * @since 		19-07-2015
 * @version		1.0
 * @changeLog
 * 	-	all
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 	
	'Module' . DIRECTORY_SEPARATOR . 
	'Autoload' . DIRECTORY_SEPARATOR . 
	'Class'  . DIRECTORY_SEPARATOR . 
	'Autoload.class.php';

$autoload = new \Priya\Module\Autoload();
$autoload->addPrefix('Priya',  dirname(__DIR__) . DIRECTORY_SEPARATOR);
$autoload->register();
$autoload->environment(Priya\Application::ENVIRONMENT);