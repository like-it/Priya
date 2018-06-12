<?php
$start = microtime(true);
require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Class' . DIRECTORY_SEPARATOR . 'Memory.class.php';
require dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'File' . DIRECTORY_SEPARATOR . 'Class' . DIRECTORY_SEPARATOR . 'File.class.php';

use Priya\Module\Memory;
use Priya\Module\File;

$dma = Memory::create();
$attribute = array();
foreach($argv as $nr => $value){
    if($value=== '""' || $value=== ''){
        unset($attribute[$nr]);
        continue;
    }
    $attribute[$nr] = trim(escapeshellarg($value), '\'');
}

$write = parameter($attribute, 'write');
$read = parameter($attribute, 'read');
$delete = parameter($attribute, 'delete');
$touch = parameter($attribute, 'touch');
//add queue (which needs a json file to process and echo's a returning json file (use queue ? because we should process them one at a time)
/**
 * {"memory" : { "queue" : [], "result" : {"key_1" : "value", "key_2" : "value"}, "stat", {"duration" : "value"} }
 *
 * queue can contain commands to write, read, delete and touch, multiple commands enables highspeed memory access (2nd read was 0.5 ms)
 * first read (init the script) takes 10 ms...
 *
 * create a process that reads the route file and then memory write the routes 1 by 1 (priya website).
 * we might need to add cache: 0 or cache: 60 to the route for caching (the amount of time)
 * we shouldn't do that every minute (large sites will need more than 1 minute to process all routes)
 * only proces files which have a changed mtime in the route (how to detect)
 * the process should use get_included files for the check so the route needs to know the process
 * (METHOD=MEMORY) which will write a file somewhere with the get_included_files
 *
 */
$attempt = (int) (parameter($attribute, 'attempt', 1) + 0);
if($attempt === 0){
    $attempt = 3;
}
//might add server (config file)

$dma = Memory::server($dma);

if($write){
    //we have a write...
    if($read){
        throw new Exception('Cannot read and write at the same time...');
    }
    if($delete){
        throw new Exception('Cannot delete and write at the same time...');
    }
    if($touch){
        throw new Exception('Cannot touch and write at the same time...');
    }
    $key = parameter($attribute, 'write', 1);
    $url = parameter($attribute, 'url', 1); //url offset 1
    $value = parameter($attribute, 'value', 1); //value offset 1
    $expiration =(int) (parameter($attribute, 'expiration', 1) + 0); //expiration offset 1

    if(isset($url) && !isset($value)){
        $data = File::read($url);
        $result = write($dma, $key, $data, $expiration, $attempt);
        $end = microtime(true);
        $duration = ($end - $start);
        echo '[write] {$key=' . $key . '} ' .  '{$size=' . strlen($result['value']) . '} {$duration=' . $duration . '}' .PHP_EOL;
    }
    elseif(!isset($url) && isset($value)){
        $result = write($dma, $key, $value, $expiration, $attempt);
        $end = microtime(true);
        $duration = ($end - $start);
        echo '[write] {$key=' . $key . '} ' . '{$size=' . strlen($result['value']) . '} {$duration=' . $duration . '}' .PHP_EOL;
    }
    elseif(!isset($url) && !isset($value)){
        throw new Exception('Need a value or url parameter...');
    }
    else {
        throw new Exception('Cannot use url and value at the same time...');
    }
}
elseif($read){
    if($delete){
        throw new Exception('Cannot delete and read at the same time...');
    }
    if($touch){
        throw new Exception('Cannot touch and write at the same time...');
    }
    //add attempt & interval (read at a certain interval with attempt and key)
    $key = parameter($attribute, 'read', 1);
    $read =  Memory::read($dma, $key);
    $end = microtime(true);
    $duration = ($end - $start);
    echo '[read] {$key=' . $key . '} ' . '{$size=' . strlen($read) . '} {$duration=' . $duration . '}' .PHP_EOL;
    echo $read;
}
elseif($delete){
    if($touch){
        throw new Exception('Cannot touch and delete at the same time...');
    }
    $key = parameter($attribute, 'delete', 1);
    $time = (int) (parameter($attribute, 'time', 1) + 0);
    Memory::delete($dma, $key, $time);
    $end = microtime(true);
    $duration = ($end - $start);
    echo '[delete] {$key=' . $key . '} ' . '{$duration=' . $duration . '}' .PHP_EOL;
}
elseif($touch){
    $key = parameter($attribute, 'touch', 1);
    $expiration = (int) (parameter($attribute, 'expiration', 1) + 0);
    Memory::touch($dma, $key, $expiration);
    $end = microtime(true);
    $duration = ($end - $start);
    echo '[touch] {$key=' . $key . '} ' . '{$duration=' . $duration . '}' .PHP_EOL;
}

function write($dma, $key, $value='', $expiration=0, $attempt=3){
    $result = Memory::write($dma, $key, $value, $expiration);
    $retry = 0;
    while($result['set'] === false){
        Memory::restart();
        sleep(1);//time to restart...
        $result = Memory::write($dma, $key, $value, $expiration);
        $retry++;
        if($retry >= $attempt){
            break;
        }
    }
    return $result;
}

function parameter($array=array(), $parameter='', $offset=0){
    if(!is_array($array)){
        return;
    }
    foreach($array as $nr => $node){
        if(strtolower($node) == strtolower($parameter)){
            if(isset($array[$nr + $offset])){
                return $array[$nr + $offset];
            } else {
                return;
            }
        }
    }
}