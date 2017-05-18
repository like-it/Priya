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
    $cwd = getcwd();
    chdir($this->data('dir.module.data') . 'Parser' );
    while (file_exists($url) === false){
        if(!empty($url)){
            $this->color(0,1);
            $this->write('output', 'File not found (' . $url . ')');
            $this->write('output', PHP_EOL);
        }
        $url = $this->read('input', 'Please provide the parser file: ');
    }
    $parser = new Parser($this->data());
    $parser->route($this->route());
    $read = $parser->read($url);
    $output = new Parser();
    $output->data('input', $parser->data('test'));
    $data = new Data();
    $data->read($url);
    chdir($cwd);
    $test = $data->data('test');
    if(is_array($test) || is_object($test)){
        foreach($test as $key => $value){
            $result = new stdClass();
            $result->input__ = $value;
            $result->parser_ = $parser->data('test.' . $key);
            $result->output_ = $parser->data('output.' . $key);

            if(is_float($result->parser_) && $result->output_ <> 0){
                if (abs(($result->parser_ - $result->output_)/$result->output_) < 0.00001) {
                    $result->success = true;
                } else {
                    $result->success = false;
                }
            } else {
                if(is_object($result->output_)){
                    if($result->output_ == $result->parser_){
                        $result->success = true;
                    } else {
                        $result->success = false;
                    }
                } else {
                    if($result->parser_ === $result->output_){
                        $result->success = true;
                    } else {
                        $result->success = false;
                    }
                }
            }
            if(!empty($parser->data('note.' . $key))){
                $result->note___ = $parser->data('note.' . $key);
            }
            $this->data('nodeList.' . $key, $result);
        }
        $output->data('output', $this->data('nodeList'));
        echo $this->object($output->data(), 'json') . PHP_EOL;
    } else {
        echo 'empty input...' . PHP_EOL;
    }

};
$parse();