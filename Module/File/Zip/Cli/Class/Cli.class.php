<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */
namespace Priya\Module\File\Zip;

use Priya\Module\File\Dir;
use Priya\Module\Core\Main;

class Cli extends Main {
    const DIR = __DIR__;
    const FILE = __FILE__;

    public function run(){
        $pack = $this->parameter('pack');

        if($pack){
            $source = Cli::dir($this, $this->parameter('pack', 1));
            $target = Cli::dir($this, $this->parameter('pack', 2));

            Cli::pack($this, $source, $target);


            var_dump($source);
            var_dump($target);
            die;
        }
    }

    private static function dir($object, $url=''){
        $name = Dir::name($url);
        var_dump($object->cwd());
        die;
        if(empty($name)){
            return $object->data('dir.current') . $url;
        }
        return $url;
    }

    public static function pack($object, $source, $target, $ignore=array()){

    }
}