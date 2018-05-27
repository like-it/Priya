<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */
namespace Priya\Module\Memory;

use Priya\Module\Core\Cli as Cli_Core;
use Priya\Module\Memory;
use Priya\Module\File\Dir;
use Exception;
use Priya\Module\File;
class Cli extends Cli_Core{
    const DIR = __DIR__;
    const FILE = __FILE__;

    public function run(){
        $key = (string) $this->parameter(2);
        $url = (string) $this->parameter(3);
        $expiration = $this->parameter(4) ? (int) $this->parameter(4) : 0;
        if($key === false){
            throw new Exception('Please provide a key');
        }
        if($url === false){
            throw new Exception('Please provide an url');
        }
        $rand = rand(1000,9999) . '-' . rand(1000,9999) . '-' . rand(1000,9999) . '-' . rand(1000,9999);
        $cache = $this->data('module.dir.data') . $rand;
        mkdir($cache, Dir::CHMOD, true);
        chdir($cache);
        ob_start(); // we need echo off
        exec('wget ' . $url, $output);

        $dir = new Dir();
        $read = $dir->read($cache);
        if(!isset($read[0])){
            throw new Exception('Cannot find the downloaded file');
        }
        $wget = $read[0];

        $file = new File();
        $value = $file->read($wget->url);
        ob_clean();
        ob_end_clean();
        $this->write($key, $value, $expiration);
        $dir->delete($cache);
        $end = microtime(true);
        $duration = $end - $this->data('time.start');
        echo 'Duration: ' . $duration . PHP_EOL;
    }

    public function write($key='', $value=null, $expiration=0){
        $server = array(
            'host' => Memory::DEFAULT_HOST,
            'port' => Memory::DEFAULT_PORT,
            'weight' => Memory::DEFAULT_WEIGHT
        );
        $type =  Memory::DEFAULT_TYPE;

        if(empty($key)){
            throw new Exception('Please provide a key');
        }
        $dma = Memory::create($type);
        $dma = Memory::server($dma, $server);

        if(empty($dma)){
            throw new Exception('Couldn\'t get DMA');
        }
        $result = Memory::write($dma, $key, $value, $expiration);
        if($result['set'] === true){
            echo "Key: " . $result['key'] . PHP_EOL;
            echo "Expiration: " . $result['expiration'] . PHP_EOL;
            echo "Size: " . strlen($result['value']) . PHP_EOL;
        } else {
            Memory::stop();
            Memory::start();
            sleep(1);
            $result = Memory::write($dma, $key, $value, $expiration);
            if($result['set'] === true){
                echo "Key: " . $result['key'] . PHP_EOL;
                echo "Expiration: " . $result['expiration'] . PHP_EOL;
                echo "Size: " . strlen($result['value']) . PHP_EOL;
            }
        }
        $dma->quit();
    }
}