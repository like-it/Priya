<?php
/**
 * @author 		Remco van der Velde
 * @since 		19-07-2015
 * @version		1.0
 * @changeLog
 *  -	all
 */

namespace Priya\Module\Autoload;

use Priya\Application;
use Priya\Module\Autoload;
use Priya\Module\File;

class Data extends Autoload {
    use \Priya\Module\Core\Object;

    public function register($method='data_load', $prepend=false){
        trigger_error('unable to register resulting data, no target specified.');
    }

    public function data_load($load){
        if(file_exists($load)){
            $url = $load;
        } else {
            $url = $this->locate($load);
        }
        if (!empty($url)) {
            return $url;

            $file = new File();
            $data = $file->read($url);
            $data = $this->object($data);
            if($this->environment() == 'development' && empty($data)){
                trigger_error('File (' . $url . '): empty or corrupted. (No valid Json)');
            }
            return $data;
        }
        return false;
    }

    public function filelist($item=array()){		//name to configure ?
        $data = array();
        if(empty($item)){

            $data[] = dirname(Application::DIR) . Application::DS . Application::DATA .  Application::DS . 'Application' . '.json';
            $data[] = dirname(Application::DIR) . Application::DS . Application::DATA .  Application::DS . 'Application' . '.json';
            $data[] = dirname(dirname(Application::DIR)) . Application::DS . Application::DATA .  Application::DS . 'Application' . Application::DS . 'Application' . '.json';
            return $data;
        }
        $data[] = $item['directory'] . $item['file'] . Application::DS . Application::DATA . Application::DS . $item['baseName'] . '.json';
        $data[] = '[---]';
        $data[] = $item['directory'] . dirname($item['file']) . Application::DS . Application::DATA . Application::DS . $item['baseName'] . '.json';
        $data[] = '[---]';
        $data[] = dirname(Application::DIR) . Application::DS . Application::DATA .  Application::DS . $item['baseName'] . '.json';
        $data[] = '[---]';
        $data[] = $item['directory'] . $item['file'] . Application::DS . Application::DATA . Application::DS . $item['file'] . '.json';
        $data[] = '[---]';
        $data[] = dirname(Application::DIR) . Application::DS . Application::DATA .  Application::DS . $item['file'] . '.json';
        $data[] = '[---]';
        $data[] = dirname(dirname(Application::DIR)) . Application::DS . Application::DATA .  Application::DS . $item['baseName'] . Application::DS . $item['baseName'] . '.json';
        $data[] = '[---]';
        $data[] = dirname(dirname(Application::DIR)) . Application::DS . Application::DATA .  Application::DS . $item['baseName'] . '.json';
        $data[] = '[---]';
        $data[] = dirname(dirname(Application::DIR)) . Application::DS . Application::DATA .  Application::DS . $item['file'] . '.json';
        $data[] = '[---]';
        $data[] = $item['directory'] . $item['file'] . Application::DS . $item['file'] . '.json';
        $data[] = '[---]';
        $data[] = $item['directory'] . $item['file'] . Application::DS . $item['baseName'] . '.json';
        $data[] = '[---]';
        return $data;
    }
}