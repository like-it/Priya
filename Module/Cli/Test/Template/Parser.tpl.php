<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-11-07
 * @version		1.0
 * @changeLog
 * 	-	all
 * @note
 *  - In Smarty bash coloring isn't working.
 */

namespace Priya;

use stdClass;
use Priya\Module\Parser;
use Priya\Module\Data;

$parse = function(){
    $url = $this->parameter('url') ? $this->parameter('url') : $this->parameter(3);

    if(empty($url) || file_exists($url) === false){
        $url = $this->data('dir.module.data') . "Parser.json";
    }
    $parser = new Parser($this->data());
    $parser->route($this->route());
    $read = $parser->read($url);
    echo 'input:' . PHP_EOL;
    echo $this->object($parser->data('test'), 'json') . PHP_EOL;
    echo 'output:' . PHP_EOL;

    $data = new Data();
    $data->read($url);

    $test = $data->data('test');
    if(is_array($test) || is_object($test)){
        foreach($test as $key => $value){
            $result = new stdClass();
            $result->input__ = $value;
            $result->parser_ = $parser->data('test.' . $key);
            $result->output_ = $parser->data('output.' . $key);
            if($result->parser_ === $result->output_){
                $result->success = true;
            } else {
                $result->success = false;
            }
            $this->data('nodeList.' . $key, $result);
        }
        echo $this->object($this->data('nodeList'), 'json') . PHP_EOL;
    } else {
        echo 'empty input...' . PHP_EOL;
    }

};
$parse();