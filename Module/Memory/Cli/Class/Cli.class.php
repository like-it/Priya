<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */
namespace Priya\Module\Memory;

use Priya\Module\Core\Main;
use Priya\Module\Memory;
use Priya\Module\File\Dir;

class Cli extends Main {
    const DIR = __DIR__;
    const FILE = __FILE__;

    public function run(){
        $key = $this->parameter(2);
        $url = $this->parameter(3);

        $rand = rand(1000,9999) . '-' . rand(1000,9999) . '-' . rand(1000,9999) . '-' . rand(1000,9999);
        $cache = $this->data('module.dir.data') . $rand;// . 'wget-' . $rand;
        mkdir($cache, Dir::CHMOD, true);
        chdir($cache);

        exec('wget ' . $url . ' ' . $cache, $output);
        var_dump($output);

//         $dir = new Dir();
//         $dir->delete($cache);

    }


}