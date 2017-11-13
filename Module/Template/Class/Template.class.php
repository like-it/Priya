<?php
/**
 * @author         Remco van der Velde
 * @since         2016-10-19
 * @version        1.0
 * @changeLog
 *     -    all
 */

namespace Priya\Module;

use Priya\Module\Core\Main;
use Priya\Module\Core\Parser;

class Template extends Main {
    const DIR = __DIR__;

    public function run(){
        $this->read(__CLASS__);

        //$url = str_replace('/Data/', '/Template/', $this->request('url'));
        $url = $this->data('priya.dir.public') . 'Template/' .basename($this->request('url'), '.json') . '.tpl';

        $file = new File();
        $data = Template::object_merge($this->data(), $this->request());
        $url = $this->parser('object')->compile($url, $data);
        $read =  $this->parser('object')->compile($file->read($url), $data);
        return $read;

    }
}
