<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 *  -    lowered the l of Autoload
 */

namespace Priya\Module;

use stdClass;

class Autoload {
    const DIR = __DIR__;
    const FILE = 'Autoload.json';

    const EXT_PHP = 'php';
    const EXT_TPL = 'tpl';
    const EXT_JSON = 'json';
    const EXT_CLASS_PHP = 'class.php';
    const EXT_TRAIT_PHP = 'trait.php';

    const PREFIX_NONE = 'none';

    protected $expose;

    protected $read;
    protected $fileList;

    public $prefixList = array();
    public $environment = 'production';

    public function register($method='load', $prepend=false){
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
        $list_length = array();
        foreach($list as $key => $value){
            if(isset($value['prefix'])){
                $list_length[strlen($value['prefix'])][] = $key;
            }
        }
        krsort($list_length, SORT_NATURAL);

        $list_prefix = array();
        foreach($list_length as $length => $sorted){
            foreach($sorted as $nr => $key){
                $list_prefix[] = $list[$key];
            }
        }
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

    public function fileList($item=array(), $url=''){
        if(empty($item)){
            return array();
        }
        if(empty($this->read)){
            $this->read = $this->read($url);
        }
        $data = array();
        $caller = get_called_class();
        if(
            isset($this->read->autoload) &&
            isset($this->read->autoload->{$caller}) &&
            isset($this->read->autoload->{$caller}->{$item['load']})
        ){
            $data[] = $this->read->autoload->{$caller}->{$item['load']};
        }
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
        if(empty($item['dirName'])){
            $data[] = $item['directory'] . 'Class' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_CLASS_PHP;
            $data[] = $item['directory'] . 'Trait' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_TRAIT_PHP;
            $data[] = $item['directory'] . 'Class' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_PHP;
            $data[] = $item['directory'] . 'Trait' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_PHP;
            $data[] =  '[---]';
        } else {
            $data[] = $item['directory'] . $item['dirName'] . DIRECTORY_SEPARATOR . 'Class' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_CLASS_PHP;
            $data[] = $item['directory'] . $item['dirName'] . DIRECTORY_SEPARATOR . 'Trait' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_TRAIT_PHP;
            $data[] = $item['directory'] . $item['dirName'] . DIRECTORY_SEPARATOR . 'Class' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_PHP;
            $data[] = $item['directory'] . $item['dirName'] . DIRECTORY_SEPARATOR . 'Trait' . DIRECTORY_SEPARATOR . $item['baseName'] . '.' . Autoload::EXT_PHP;
            $data[] =  '[---]';
        }

        $data[] = $item['directory'] . $item['file'] . '.' . Autoload::EXT_CLASS_PHP;
        $data[] = $item['directory'] . $item['file'] . '.' . Autoload::EXT_TRAIT_PHP;
        $data[] = $item['directory'] . $item['file'] . '.' . Autoload::EXT_PHP;
        $data[] = '[---]';
        $data[] = $item['directory'] . $item['baseName'] . '.' . Autoload::EXT_CLASS_PHP;
        $data[] = $item['directory'] . $item['baseName'] . '.' . Autoload::EXT_TRAIT_PHP;
        $data[] = $item['directory'] . $item['baseName'] . '.' . Autoload::EXT_PHP;
        $data[] = '[---]';

        $this->fileList[$item['file']][] = $data;

        $result = array();
        foreach($data as $nr => $file){
            if($file === '[---]'){
                $file = $file . $nr;
            }
            $result[$file] = $file;
        }
        return $result;
    }

    public function locate($load=null){
        $dir = dirname(Autoload::DIR) . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR;
        $url = $dir . Autoload::FILE;
        $load = str_replace('/', '\\', $load);
        $load = ltrim($load, '\\');
        $prefixList = $this->getPrefixList();
        $select = false;
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
                }
                elseif($item['prefix'] === Autoload::PREFIX_NONE){
                    $tmp = explode('.', $load);
                    if(count($tmp) >= 2){
                        array_pop($tmp);
                    }
                    $item['file'] = implode('.',$tmp);
                }
                else {
                    continue; //added
                }
                if(empty($item['file'])){
                    $item['file'] = $load;
                }
                if(!empty($item['file'])){
                    $item['load'] = $load;
                    $item['file'] = str_replace('\\', DIRECTORY_SEPARATOR, $item['file']);
                    $item['file'] = str_replace('.'  . DIRECTORY_SEPARATOR , DIRECTORY_SEPARATOR, $item['file']);
                    $item['baseName'] = basename(
                        $this->removeExtension(
                            $item['file'],
                            array(
                                Autoload::EXT_PHP,
                                Autoload::EXT_TPL
                            )
                        )
                    );
                    $item['baseName'] = explode(DIRECTORY_SEPARATOR, $item['baseName'], 2);
                    $item['baseName'] = end($item['baseName']);
                    $item['dirName'] = dirname($item['file']);
                    if($item['dirName'] == '.'){
                        unset($item['dirName']);
                    }
                    $fileList = $this->fileList($item, $url);
                    $select = $item;
                    if(is_array($fileList) && empty($this->expose())){
                        foreach($fileList as $nr => $file){
                            if(substr($file, 0, 5) == '[---]'){
                                continue;
                            }
                            if(file_exists($file)){
                                $this->cache($file, $load);
//                                 $this->write($url, $file, $load);
                                return $file;
                            }
                        }
                    }
                }
            }
        }
        if($this->environment() == 'development' || !empty($this->expose())){
            $object = new stdClass();
            $object->load = $load;
            $debug = debug_backtrace(true);
	    $object->debug = $debug;
	

            $attribute = 'Priya\Module\Exception\Error';
            if(!empty($this->expose())){
                $attribute = $load;
            }
            if(
                isset($select['file']) &&
                isset($this->fileList[$select['file']])
            ){
                $object->{$attribute} = $this->fileList[$select['file']];
            }
            if(ob_get_level() !== 0){
                ob_flush();
            }
            if(empty($this->expose())){
                echo '<pre>';
                echo json_encode($object, JSON_PRETTY_PRINT);
                echo '</pre>';
                die;
            } else {
                echo json_encode($object, JSON_PRETTY_PRINT);
            }
        }
        return false;
    }

    public function __destruct(){
        if(!empty($this->read)){
            $dir = dirname(Autoload::DIR) . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR;
            $url = $dir . Autoload::FILE;
            $this->write($url, $this->read);
        }
    }

    private function cache($file='', $class=''){
        if(empty($this->read)){
            $dir = dirname(Autoload::DIR) . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR;
            $url = $dir . Autoload::FILE;
            $this->read = $this->read($url);
        }
        if(empty($this->read->autoload)){
            $this->read->autoload = new stdClass();
        }
        $caller = get_called_class();
        if(empty($this->read->autoload->{$caller})){
            $this->read->autoload->{$caller}= new stdClass();
        }
        $this->read->autoload->{$caller}->{$class} = (string) $file;
    }

    protected function write($url='', $data=''){
        $data = (string) json_encode($data, JSON_PRETTY_PRINT);
        if(empty($data)){
            return false;
        }
        $fwrite = 0;
        if(empty($resource)){
            $dir = dirname($url);
            if(is_dir($dir) === false){
                mkdir($dir, 0740, true);
            }
            $resource = fopen($url, 'w');
        }
        if($resource === false){
            return $resource;
        }
        $lock = flock($resource, LOCK_EX);
        fseek($resource, 0);
        for ($written = 0; $written < strlen($data); $written += $fwrite) {
            $fwrite = fwrite($resource, substr($data, $written));
            if ($fwrite === false) {
                break;
            }
        }
        if(!empty($resource)){
            flock($resource, LOCK_UN);
        }
        fclose($resource);
        if($written != strlen($data)){
            return false;
        } else {
            return $fwrite;
        }
    }

    private function read($url=''){
        if(file_exists($url) === false){
            $this->read = new stdClass();
            return $this->read;
        }
        $this->read =  json_decode(implode('',file($url)));
        if(empty($this->read)){
            $this->read = new stdClass();
        }
        return $this->read;

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
