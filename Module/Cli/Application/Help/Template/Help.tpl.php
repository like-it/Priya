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

echo "\t" . 'Options:' ."\n";
echo "\t" . 'route --list             (this will show available routes)' ."\n";
echo "\t" . 'config list              (this will show the configuration list)' ."\n";
echo "\t" . 'config set $var $value   (this will set the variable in the configuration)' ."\n";
echo "\t" . 'config get $var          (this will show the variable value)' ."\n";
echo "\t" . 'install                  (this will install the newest Priya)' ."\n";
echo "\t" . 'install --tag 0.0.5      (this will install Priya tagged version 0.0.5)' ."\n";
echo "\t" . 'install --local          (this will install Priya from the local backup version instead of online)' ."\n";
echo "\t" . 'install --options        (this will show tagged versions available)' ."\n";
echo "\t" . 'restore                  (this will restore the installation from Application/Data/Backup/Restore)' ."\n";
echo "\t" . 'update                   (this will update the installation with the newest Priya)' ."\n";
?>