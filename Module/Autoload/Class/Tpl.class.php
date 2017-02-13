<?php
/**
 * @author 		Remco van der Velde
 * @since 		19-07-2015
 * @version		1.0
 * @changeLog
 *  -	all
 */

namespace Priya\Module\Autoload;

use Priya\Module\Autoload;
use Priya\Application;

class Tpl extends Autoload {
    const TEMPLATE = 'Template';
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

    public function filelist($item=array()){		//name to configure ?
        if(empty($item)){
            return array();
        }
        if(empty($item['extension'])){
            $item['extension'] = 'tpl';
        }
        $data = array();

        $directory = explode(Application::DS, $item['file']);
        if(count($directory) == 1){
            if(stristr($item['file'], $item['extension']) !== false){
                $data[] = $item['directory'] . Tpl::TEMPLATE . Application::DS . $item['file'];
            } else {
                $data[] = $item['directory'] . $item['file'] . Application::DS . Tpl::TEMPLATE . Application::DS. $item['file'] . '.' . $item['extension'];
                $dir = explode(Tpl::TEMPLATE, $item['directory'], 2);
                $dir = implode('', $dir);
                $dir = rtrim($dir, Application::DS) . Application::DS;
                $data[] = $dir . $item['file'] . Application::DS . Tpl::TEMPLATE . Application::DS. $item['file'] . '.' . $item['extension'];
            }
            $data[] = $item['directory'] . $item['baseName'] . DIRECTORY_SEPARATOR . 'Template' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' .$item['extension'];
            $data[] = '[---]';
            $data[] = $item['directory'] . $item['baseName'] . DIRECTORY_SEPARATOR . $item['baseName'] . '.' .$item['extension'];
            $data[] = '[---]';
            $data[] = $item['directory'] . 'Template' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' .$item['extension'];
            $data[] = '[---]';
            $data[] = $item['directory'] . $item['file'] . '.' .$item['extension'];
            $data[] = '[---]';
            $data[] = $item['directory'] . $item['baseName'] . '.' .$item['extension'];
            $data[] = '[---]';
        } else {
            $file = array_pop($directory);
            $directory = implode(Application::DS, $directory) . Application::DS;
            if(stristr($file, $item['extension']) !== false){
                $data[] = $item['directory'] . $directory . Tpl::TEMPLATE . Application::DS . $file;
            } else {
                $data[] = $item['directory'] . $directory . $file . Application::DS . Tpl::TEMPLATE . Application::DS . $file . '.' . $item['extension'];
                $data[] = $item['directory'] . $directory . Tpl::TEMPLATE . Application::DS . $file . '.' . $item['extension'];
                $dir = explode(Tpl::TEMPLATE, $item['directory'], 2);
                $dir = implode('', $dir);
                $dir = rtrim($dir, Application::DS) . Application::DS;
                $data[] = $dir . $directory . Tpl::TEMPLATE . Application::DS . $file . '.' . $item['extension'];
            }
            $data[] = $item['directory'] . $item['baseName'] . Application::DS . 'Template' . Application::DS . $item['baseName'] . '.' .$item['extension'];
            $data[] = '[---]';
            $data[] = $item['directory'] . $item['file'] . '.' .$item['extension'];
            $data[] = '[---]';
//             $data[] = $item['directory'] . $item['baseName'] . '.' .$item['extension'];
            $data[] = '[---]';
            //         var_dump($data);
        }
        return $data;
    }
}