<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */

namespace Priya\Module\Autoload;

use Priya\Application;
use Priya\Module\Autoload;

class Tpl extends Autoload {
    const DIR_CSS = 'Css';
    const DIR_JS = 'Js';

    private $seperator = false;

    public function register($method='tpl_load', $prepend=false){
        trigger_error('unable to register resulting data, no target specified.');
    }

    public function tpl_load($load){
        $load = str_replace(array('/','\\'), Application::DS, $load);
        $url = $this->locate($load);
        if (!empty($url)) {
            return $url;
        }
        return false;
    }

    public function filelist($item=array(), $url=''){
        if(empty($item)){
            return array();
        }
        if(empty($item['extension'])){
            $item['extension'] = Autoload::EXT_TPL;
        } else {
            $item['extension'] = ltrim($item['extension'], '.');
        }
        $data = array();

        $directory = explode(Application::DS, $item['file']);
        if(count($directory) == 1){
            if(stristr($item['file'], $item['extension']) !== false){
                $data[] = $item['directory'] . Application::TEMPLATE . Application::DS . $item['file'];
            } else {
                $data[] = $item['directory'] . $item['file'] . Application::DS . Application::TEMPLATE . Application::DS. $item['file'] . '.' . $item['extension'];
                $dir = explode(Application::TEMPLATE, $item['directory'], 2);
                $dir = implode('', $dir);
                $dir = rtrim($dir, Application::DS) . Application::DS;
                $data[] = $dir . $item['file'] . Application::DS . Application::TEMPLATE . Application::DS. $item['file'] . '.' . $item['extension'];
            }
            $data[] = $item['directory'] . $item['baseName'] . Application::DS. Application::TEMPLATE . Application::DS. $item['baseName'] . '.' . $item['extension'];
            $data[] = '[---]';
            $data[] = $item['directory'] . $item['baseName'] . Application::DS. $item['baseName'] . '.' . $item['extension'];
            $data[] = '[---]';
            $data[] = $item['directory'] . Application::TEMPLATE . Application::DS. $item['baseName'] . '.' . $item['extension'];
            $data[] = '[---]';
            $data[] = $item['directory'] . $item['file'] . '.' . $item['extension'];
            $data[] = '[---]';
            $data[] = $item['directory'] . $item['baseName'] . '.' . $item['extension'];
            $data[] = '[---]';
        } else {
            $file = array_pop($directory);
            $directory = implode(Application::DS, $directory) . Application::DS;
            if(stristr($file, $item['extension']) !== false){
                $data[] = $item['directory'] . $directory . Application::TEMPLATE . Application::DS . $file;
                $data[] = $item['directory'] . Application::TEMPLATE . Application::DS . $file;
            } else {
                $data[] = $item['directory'] . $directory . $file . Application::DS . Application::TEMPLATE. Application::DS . $file . '.' . $item['extension'];
                $data[] = $item['directory'] . $directory . Application::TEMPLATE . Application::DS . $file . '.' . $item['extension'];
                $data[] = $item['directory'] . $file . Application::DS . Application::TEMPLATE. Application::DS . $file . '.' . $item['extension'];
                //$data[] = $item['directory'] . Application::TEMPLATE . Application::DS . $file . '.' . $item['extension']; //not allowed
                $dir = explode(Application::TEMPLATE, $item['directory'], 2);
                $dir = implode('', $dir);
                $dir = rtrim($dir, Application::DS) . Application::DS;
                $data[] = $dir . $directory . Application::TEMPLATE. Application::DS . $file . '.' . $item['extension'];
//                 $data[] = $dir . Application::TEMPLATE. Application::DS . $file . '.' . $item['extension']; //not allowed
                if(count(explode(Application::DS, $item['dirName'], 3)) === 3){ //allowed
                    $data[] = $item['directory'] . Application::TEMPLATE . Application::DS . $file . '.' . $item['extension'];
                    $data[] = $dir . Application::TEMPLATE. Application::DS . $file . '.' . $item['extension'];
                }
            }
            $data[] = $item['directory'] . $item['baseName'] . Application::DS . Application::TEMPLATE . Application::DS . $item['baseName'] . '.' . $item['extension'];
            $data[] = '[---]';
            $data[] = $item['directory'] . $item['file'] . '.' . $item['extension'];
            $data[] = '[---]';
        }

        $this->fileList[$item['baseName']][] = $data;
//         var_dump($data);
        return $data;
    }
}