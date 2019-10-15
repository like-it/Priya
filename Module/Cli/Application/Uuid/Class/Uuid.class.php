<?php
/**
 * @author         Remco van der Velde
 * @since         2016-10-19
 * @version        1.0
 * @changeLog
 *     -    all
 */
namespace Priya\Module\Cli\Application;

use Priya\Module\Core\Cli;

class Uuid extends Cli {
    const DIR = __DIR__;

    public function run($object=null){
        if($object === null){
            $object = $this;
        }
        $object->data('node.uuid', Uuid::uuid());
        return Uuid::view($object);
    }
}