<?php
namespace Priya\Module;

use Exception;

$dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Class' . DIRECTORY_SEPARATOR;

require $dir . 'Memory.Class.php';

$server = array(
    'host' => isset($_GET['host']) ? (string) $_GET['host'] : Memory::DEFAULT_HOST,
    'port' => isset($_GET['port']) ? (int) $_GET['port'] : Memory::DEFAULT_PORT,
    'weight' => isset($_GET['weight']) ? (int) $_GET['weight'] : Memory::DEFAULT_WEIGHT
);

$type =  isset($_GET['type']) ? (string) $_GET['type'] : Memory::DEFAULT_TYPE;
$key =  isset($_GET['key']) ? (string) $_GET['key'] : '';

$explode = explode(DIRECTORY_SEPARATOR, __DIR__);

$public = array_pop($explode);

$key = $public . '\\' . $key; //only public keys are allowed

if(empty($key)){
    throw new Exception('Please provide a key');
}
$dma = Memory::dma($type);
$dma = Memory::server($dma, $server);

if(empty($dma)){
    throw new Exception('Could\'nt get DMA');
}
$result = Memory::read($dma, $key);
echo $result;
$dma->quit();