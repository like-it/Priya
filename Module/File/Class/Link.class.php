<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */

namespace Priya\Module\File;

class Link {
    const TYPE = 'Link';

    public static function is($url){
        return is_link($url);
    }

    public static function read($url){
        return readlink($url);
    }

    public static function create($source, $destination){
        system('ln -s ' . $source . ' ' . $destination);
    }

}