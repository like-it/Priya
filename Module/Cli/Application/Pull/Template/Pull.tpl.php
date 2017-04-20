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
    elseif($this->error('user')){
        echo 'user required...' . PHP_EOL;
    }
    elseif($this->error('password')){
        echo 'password required...' . PHP_EOL;
    }
    elseif($this->error('server')){
        echo 'server required...' . PHP_EOL;
    } else {
        if($this->data('step') == 'download'){
            echo 'Downloading server (' . $this->data('server.url') . ')...' . PHP_EOL;
        }
        if($this->data('step') == 'extract'){
            echo 'Extracting archive...' . PHP_EOL;
        }
        if($this->data('step') == 'finish'){
            echo 'Succesfully pulled the server...' . PHP_EOL;
        }
    }
}