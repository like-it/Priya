<?php
/**
 * @author 		Remco van der Velde
 * @since 		19-07-2015
 * @version		1.0
 * @changeLog
 *  -	all
 *  -	lowered the l
 */

namespace Priya\Module;
use stdClass;

class Autoload{
    public $prefixList = array();
    public $environment = 'production';

    public function register($method='load', $prepend=false){
        $this->environment('development');
        $functions = spl_autoload_functions();
        if(is_array($functions)){
            foreach($functions as $function){
                $object = reset($function);
                if(is_object($object) && get_class($object) == get_class($this)){
                    return; //register once...
                }

            }
        }
        spl_autoload_register(array($this, $method), true, $prepend);
    }

    public function unregister($method='load'){
        return spl_autoload_unregister(array($this, $method));
    }

    public function priority(){
        $functions = spl_autoload_functions();
        $priority = false;
        foreach($functions as $nr => $function){
            $object = reset($function);
            if(is_object($object) && get_class($object) == get_class($this) && $nr > 0){
                $priority = $function;
                spl_autoload_unregister($function);
                spl_autoload_register($function, null, true); //prepend (prioritize)
            }
        }
    }

    private function setEnvironment($environment='production'){
        $this->environment = $environment;
    }

    private function getEnvironment(){
        return $this->environment;
    }

    public function environment($environment=null){
        if($environment !== null){
            $this->setEnvironment($environment);
        }
        return $this->getEnvironment();
    }

    public function addPrefix($prefix='', $directory='', $extension=''){
        $prefix = trim($prefix, '\\\/'); //.'\\';
        $directory = rtrim($directory,'\\\/') . DIRECTORY_SEPARATOR;
        $list = $this->getPrefixList();
        if(empty($extension)){
            $list[]  = array(
                'prefix' => $prefix,
                'directory' => $directory
            );
        } else {
            $list[]  = array(
                'prefix' => $prefix,
                'directory' => $directory,
                'extension' => $extension
            );
        }
        $this->setPrefixList($list);
    }

    private function setPrefixList($list = array()){
        $this->prefixList = $list;
    }

    private function getPrefixList(){
        return $this->prefixList;
    }

    public function load($load){
// 		$this->environment('development');
        $file = $this->locate($load);
        if (!empty($file)) {
            require $file;
            return true;
        }
        return false;
    }

    public function fileList($item=array()){
        if(empty($item)){
            return array();
        }
        $data = array();
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . 'Class' . DIRECTORY_SEPARATOR . $item['file'] . '.class.php';
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . 'Class' . DIRECTORY_SEPARATOR . $item['file'] . '.php';
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . 'Trait' . DIRECTORY_SEPARATOR . $item['file'] . '.trait.php';
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . 'Trait' . DIRECTORY_SEPARATOR . $item['file'] . '.php';
        $data[] = '[---]';
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . 'Class' . DIRECTORY_SEPARATOR . $item['baseName'] . '.class.php';
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . 'Class' . DIRECTORY_SEPARATOR . $item['baseName'] . '.php';
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . 'Trait' . DIRECTORY_SEPARATOR . $item['baseName'] . '.trait.php';
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . 'Trait' . DIRECTORY_SEPARATOR . $item['baseName'] . '.php';
        $data[] = '[---]';
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . $item['file'] . '.class.php';
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . $item['file'] . '.trait.php';
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . $item['file'] . '.php';
        $data[] = '[---]';
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . $item['baseName'] . '.class.php';
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . $item['baseName'] . '.trait.php';
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . $item['baseName'] . '.php';
        $data[] = '[---]';
        $data[] = $item['directory'] . $item['dirName'] . DIRECTORY_SEPARATOR. 'Class' . DIRECTORY_SEPARATOR . $item['baseName'] . '.class.php';
        $data[] = $item['directory'] . $item['dirName'] . DIRECTORY_SEPARATOR. 'Trait' . DIRECTORY_SEPARATOR . $item['baseName'] . '.trait.php';
        $data[] = $item['directory'] . $item['dirName'] . DIRECTORY_SEPARATOR. 'Class' . DIRECTORY_SEPARATOR . $item['baseName'] . '.php';
        $data[] = $item['directory'] . $item['dirName'] . DIRECTORY_SEPARATOR. 'Trait' . DIRECTORY_SEPARATOR . $item['baseName'] . '.php';
        $data[] =  '[---]';
        $data[] = $item['directory'] . $item['file'] . '.class.php';
        $data[] = $item['directory'] . $item['file'] . '.trait.php';
        $data[] = $item['directory'] . $item['file'] . '.php';
        $data[] = '[---]';
        $data[] = $item['directory'] . $item['baseName'] . '.class.php';
        $data[] = $item['directory'] . $item['baseName'] . '.trait.php';
        $data[] = $item['directory'] . $item['baseName'] . '.php';
        $data[] = '[---]';
//         var_dump($data);
        return $data;
    }

    public function locate($load=null){

        $load = ltrim($load, '\\');
        $prefixList = $this->getPrefixList();
        $list = array();
        /* causes wrong placement in Smarty
        if(ob_get_level() !== 0){
            ob_flush();
        }
        */
        if(!empty($prefixList)){
            foreach($prefixList as $nr => $item){
                if(empty($item['prefix'])){
                    continue;
                }
                if(empty($item['directory'])){
                    continue;
                }
                $item['file'] = false;
                if (strpos($load, $item['prefix']) === 0) {
                    $item['file'] = trim(substr($load, strlen($item['prefix'])),'\\');
                    $item['file'] = str_replace('\\', DIRECTORY_SEPARATOR, $item['file']);
                } else {
                    $tmp = explode('.', $load);
                    if(count($tmp) >= 2){
                        array_pop($tmp);
                    }
                    $item['file'] = implode('.',$tmp);
                }
                if(empty($item['file'])){
                    $item['file'] = $load;
                }
                if(!empty($item['file'])){
                    $item['baseName'] = basename($this->removeExtension($item['file'], array('.php','.tpl')));
                    $item['dirName'] = dirname($item['file']);
                    $fileList = $this->fileList($item);
                    if(is_array($fileList)){
                        foreach($fileList as $nr => $file){
                            if(file_exists($file)){
                                return $file;
                            }
                        }
                    }
                    $list[] = $fileList;
                }
            }
        } else {
            $fileList = $this->fileList();
            if(is_array($fileList)){
                foreach($fileList as $nr => $file){
                    if(file_exists($file)){
                        return $file;
                    }
                }
            }
            $list[] = $fileList;
        }
        if($this->environment()=='development'){
            $object = new stdClass();
            $object->{'Priya\Module\Exception\Error'} = $list;
            echo json_encode($object, JSON_PRETTY_PRINT);
            if(ob_get_level() !== 0){
                ob_flush();
            }
            die;
        }
        return false;
    }

    private function removeExtension($filename='', $extension=array()){
        foreach($extension as $ext){
            $filename = explode($ext, $filename);
            if(count($filename) > 1){
                array_pop($filename);
            }
            $filename = implode($ext, $filename);
        }
        return $filename;
    }
}