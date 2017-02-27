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

use Priya\Module\Handler;

if($this->data('options')){
    echo "\t" . 'restore                  (this will restore the installation from ' . $this->data('dir.priya.data') . 'Restore/' . PHP_EOL;
}
if($this->error() && $this->handler()->method() == Handler::METHOD_POST){
    echo json_encode($this->object(array('error' => $this->error())));
} else {
    if($this->error('permission')){
        echo 'permission denied...' . PHP_EOL;
    }
    elseif($this->error('exists')){
        echo 'Patch already exists, first pull the server...' . PHP_EOL;
    } else {
        if($this->data('step') == 'create'){
            echo 'Creating restore point patch (' . $this->data('patch') . ') for version (' . $this->data('version') . ')...' . PHP_EOL;
        }
        if($this->data('step') == 'push'){
            echo 'Uploading to the server (' . $this->data('server.url') . ')...' . PHP_EOL;
        }
        if($this->data('step') == 'extract'){
            echo 'Extracting archive...' . PHP_EOL;
        }
        if($this->data('step') == 'finish'){
            echo 'Succesfully pushed to the server (' . $this->data('server.url') . ') ...' . PHP_EOL;
        }
    }
}
?>