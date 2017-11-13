<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-10-19
 * @version		1.0
 * @changeLog
 * 	-	all
 */

namespace Priya\Module\Core;

class Main extends Result {

    public function __construct($handler=null, $route=null, $data=null){
        parent::__construct($handler, $route, $data);
    }

}