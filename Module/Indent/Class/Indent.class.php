<?php
/**
 * @author         Remco van der Velde
 * @since         2016-10-19
 * @version        1.0
 * @changeLog
 *     -    all
 */

namespace Priya\Module;

use Priya\Application;
use Priya\Module\Core\Main;

class Indent extends Main {
    const DIR = __DIR__;
    const FILE = 'Indent.css';

    public function css(){
        $this->header('Content-Type: ' . $this->data('priya.contentType.css'));
        $this->read(__CLASS__);
        $result =  $this->result('template');

        $url = dirname(Indent::DIR) .
            Application::DS .
            Application::PUBLIC_HTML .
            Application::DS .
            Application::CSS .
            Application::DS .
            Indent::FILE;

        $dir = dirname($url);
        if(is_dir($dir) === false){
            mkdir($dir, 0777, true);
        }
        $file = new File();
        $file->write($url, $result->html);
        return $result;
    }
}