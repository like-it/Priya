<?php
/**
 * @author 		Remco van der Velde
 * @since 		19-07-2015
 * @version		1.0
 * @changeLog
 *  -	all
 *  -	lowered the l of Autoload
 */

namespace Priya\Module;

use stdClass;

class Autoload{
    const EXT_PHP = 'php';
    const EXT_TPL = 'tpl';
    const EXT_JSON = 'json';
    const EXT_CLASS_PHP = 'class.php';
    const EXT_TRAIT_PHP = 'trait.php';

    private $expose;

    public $prefixList = array();
    public $environment = 'production';

    public function register($method='load', $prepend=false){
//         $this->environment('development');
        $functions = spl_autoload_functions();
        if(is_array($functions)){
            foreach($functions as $function){
                $object = reset($function);
                if(is_object($object) && get_class($object) == get_class($this)){
                    return true; //register once...
                }
            }
        }
        return spl_autoload_register(array($this, $method), true, $prepend);
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
        $directory = str_replace('\\\/', DIRECTORY_SEPARATOR, rtrim($directory,'\\\/')) . DIRECTORY_SEPARATOR; //see File::dir()
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
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . 'Class' . DIRECTORY_SEPARATOR . $item['file'] . '.' . Autoload::EXT_CLASS_PHP;
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . 'Class' . DIRECTORY_SEPARATOR . $item['file'] . '.' . Autoload::EXT_PHP;
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . 'Trait' . DIRECTORY_SEPARATOR . $item['file'] . '.' . Autoload::EXT_TRAIT_PHP;
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . 'Trait' . DIRECTORY_SEPARATOR . $item['file'] . '.' . Autoload::EXT_PHP;
        $data[] = '[---]';
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . 'Class' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_CLASS_PHP;
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . 'Class' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_PHP;
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . 'Trait' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_TRAIT_PHP;
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . 'Trait' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_PHP;
        $data[] = '[---]';
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . $item['file'] . '.' . Autoload::EXT_CLASS_PHP;
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . $item['file'] . '.' . Autoload::EXT_TRAIT_PHP;
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . $item['file'] . '.' . Autoload::EXT_PHP;
        $data[] = '[---]';
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_CLASS_PHP;
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_TRAIT_PHP;
        $data[] = $item['directory'] . $item['file'] . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_PHP;
        $data[] = '[---]';
        $data[] = $item['directory'] . $item['dirName'] . DIRECTORY_SEPARATOR. 'Class' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_CLASS_PHP;
        $data[] = $item['directory'] . $item['dirName'] . DIRECTORY_SEPARATOR. 'Trait' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_TRAIT_PHP;
        $data[] = $item['directory'] . $item['dirName'] . DIRECTORY_SEPARATOR. 'Class' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_PHP;
        $data[] = $item['directory'] . $item['dirName'] . DIRECTORY_SEPARATOR. 'Trait' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_PHP;
        $data[] =  '[---]';
        $data[] = $item['directory'] . $item['file'] . '.' . Autoload::EXT_CLASS_PHP;
        $data[] = $item['directory'] . $item['file'] . '.' . Autoload::EXT_TRAIT_PHP;
        $data[] = $item['directory'] . $item['file'] . '.' . Autoload::EXT_PHP;
        $data[] = '[---]';
        $data[] = $item['directory'] . $item['baseName'] . '.' . Autoload::EXT_CLASS_PHP;
        $data[] = $item['directory'] . $item['baseName'] . '.' . Autoload::EXT_TRAIT_PHP;
        $data[] = $item['directory'] . $item['baseName'] . '.' . Autoload::EXT_PHP;
        $data[] = '[---]';
        return $data;
    }

    public function locate($load=null){
        $load = ltrim($load, '\\');
        $prefixList = $this->getPrefixList();
        $list = array();
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
                    $item['file'] =
                        trim(substr($load, strlen($item['prefix'])),'\\');
                    $item['file'] =
                        str_replace('\\', DIRECTORY_SEPARATOR, $item['file']);
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
                    $item['baseName'] = basename(
                        $this->removeExtension($item['file'],
                            array(
                                Autoload::EXT_PHP,
                                Autoload::EXT_TPL

                            )
                    ));
                    $item['dirName'] = dirname($item['file']);
                    $fileList = $this->fileList($item);
                    if(is_array($fileList) && empty($this->expose())){
                        foreach($fileList as $nr => $file){
                            if(file_exists($file)){
                                return $file;
                            }
                        }
                    }
                    $list[] = $fileList;
                }
            }
        }
        if($this->environment() == 'development' || !empty($this->expose())){
            $object = new stdClass();
            $attribute = 'Priya\Module\Exception\Error';
            if(!empty($this->expose())){
                $attribute = $load;
            }
            $object->{$attribute} = $list;
            echo json_encode($object, JSON_PRETTY_PRINT);
            if(ob_get_level() !== 0){
                ob_flush();
            }
            if(empty($this->expose())){
                die;
            }
        }
        return false;
    }

    private function removeExtension($filename='', $extension=array()){
        foreach($extension as $ext){
            $ext = '.' . ltrim($ext, '.');
            $filename = explode($ext, $filename, 2);
            if(count($filename) > 1 && empty(end($filename))){
                array_pop($filename);
            }
            $filename = implode($ext, $filename);
        }
        return $filename;
    }

    public function expose($expose=null){
        if(!empty($expose) || $expose === false){
            $this->expose = (bool) $expose;
        }
        return $this->expose;
    }
}