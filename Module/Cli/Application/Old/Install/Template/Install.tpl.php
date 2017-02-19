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

if($this->data('options')){	
	echo "\t" . 'install                  (this will install the newest Priya)' ."\n";
	echo "\t" . 'install --tag 0.0.5      (this will install Priya tagged version 0.0.5)' ."\n";
	echo "\t" . 'install --local          (this will install Priya from the local backup version instead of online)' ."\n";
	echo "\t" . 'install --options        (this will show tagged versions available)' ."\n";
} else {
	switch($this->data('step')){
		
		case 'config' :
			echo "\t" . 'Configuring Priya...' . "\n";
		break;
		case 'public-html-create' :
			echo "\t" . 'Public html ('. $this->data('public_html') .') created...' . "\n";
		break;
		case 'public-html-exists' :
			echo "\t" . 'Public html ('. $this->data('public_html') .') exists...' . "\n";
		break;
		case 'public-html-finish' :
			echo "\t" . 'Public html ('. $this->data('public_html') .') finished...' . "\n";
		break;
		case 'file-copy' :
			echo "\t" . 'Copying file...' . "\n";
		break;
		case 'module-symlink' :
			echo "\t" . 'Checking symlinks...' . "\n";
			break;
		case 'symlink-create' :
			echo "\t" . 'Symlink ('.$this->data('node')->module .') created...' . "\n";
		break;
		case 'symlink-exists' :
			echo "\t" . 'Symlink ('.$this->data('node')->module .') exists...' . "\n";
		break;
		case 'symlink-error' :
			echo $this->color('red') . '[error]' . $this->color('end') . "\t" . 'Cannot create symlink, file ('.$this->data('node')->module .') exists...' . "\n";
		break;
		case 'download-finish' :
			echo $this->color('green') . '[ok]' . $this->color('end') . "\t" . 'Download complete' . "\n";
		break;	
		case 'install' :
			echo "\tInstalling Priya... \n";
		case 'install-finish' :
			echo "\tInstallation finished... \n";
		break;
		default :
			echo $this->color('red') . '[error]' . $this->color('end') . "\t" . 'Unknown step (' . $this->data('step') . ') in installation...' . "\n";			
		break;
	}
}
?>