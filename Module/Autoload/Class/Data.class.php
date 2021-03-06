<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */

namespace Priya\Module\Autoload;

use Exception;
use Priya\Application;
use Priya\Module\Autoload;
use Priya\Module\File;

class Data extends Autoload {
    const EXCEPTION_REGISTER = 'unable to register resulting data, no target specified.';

    public function __destruct(){
        if(!empty($this->read)){
            $dir = dirname(Autoload::DIR) . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR;
            $url = $dir . Autoload::FILE;
            $this->write($url, $this->read);
        }
    }

    public function register($method='data_load', $prepend=false){
        throw new Exception(Data::EXCEPTION_REGISTER);
    }

    public function data_load($load){
        if(file_exists($load)){
            $url = $load;
        } else {
            $url = $this->locate($load, true);
        }
        if (!empty($url)) {
            return $url;
        }
        return false;
    }

    public function filelist($item=array(), $url=''){
        $data = array();
        if(empty($item)){
            $data[] = dirname(Application::DIR) . Application::DS . Application::DATA .  Application::DS . 'Application' . '.' . Autoload::EXT_JSON;
            $data[] = dirname(Application::DIR) . Application::DS . Application::DATA .  Application::DS . 'Application' . '.' . Autoload::EXT_JSON;
            $data[] = dirname(dirname(Application::DIR)) . Application::DS . Application::DATA .  Application::DS . 'Application' . Application::DS . 'Application' . '.' . Autoload::EXT_JSON;
            return $data;
        }
        $data[] = $item['directory'] . $item['file'] . Application::DS . Application::DATA . Application::DS . $item['baseName'] . '.' . Autoload::EXT_JSON;
        $data[] = '[---]';
        $data[] = $item['directory'] . dirname($item['file']) . Application::DS . Application::DATA . Application::DS . $item['baseName'] . '.' . Autoload::EXT_JSON;
        $data[] = '[---]';
        // not allowed anymore        $data[] = dirname(Application::DIR) . Application::DS . Application::DATA .  Application::DS . $item['baseName'] . '.' . Autoload::EXT_JSON;
        $data[] = '[---]';
        $data[] = $item['directory'] . $item['file'] . Application::DS . Application::DATA . Application::DS . $item['file'] . '.' . Autoload::EXT_JSON;
        $data[] = '[---]';
        // not allowed anymore        $data[] = dirname(Application::DIR) . Application::DS . Application::DATA .  Application::DS . $item['file'] . '.' . Autoload::EXT_JSON;
        $data[] = '[---]';
        // not allowed anymore        $data[] = dirname(dirname(Application::DIR)) . Application::DS . Application::DATA .  Application::DS . $item['baseName'] . Application::DS . $item['baseName'] . '.' . Autoload::EXT_JSON;
        $data[] = '[---]';
        // not allowed anymore        $data[] = dirname(dirname(Application::DIR)) . Application::DS . Application::DATA .  Application::DS . $item['baseName'] . '.' . Autoload::EXT_JSON;
        $data[] = '[---]';
        // not allowed anymore        $data[] = dirname(dirname(Application::DIR)) . Application::DS . Application::DATA .  Application::DS . $item['file'] . '.' . Autoload::EXT_JSON;
        $data[] = '[---]';
        $data[] = $item['directory'] . $item['file'] . Application::DS . $item['file'] . '.' . Autoload::EXT_JSON;
        $data[] = '[---]';
        $data[] = $item['directory'] . $item['file'] . Application::DS . $item['baseName'] . '.' . Autoload::EXT_JSON;
        $data[] = '[---]';
        return $data;
    }
}