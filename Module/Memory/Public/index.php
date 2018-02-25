<?php

namespace Memory;

use Memcached;
use Exception;

const DEFAULT_HOST = '127.0.0.1';
const DEFAULT_PORT = 11211; //public memcache server, secure memcache server is on a different port...
const DEFAULT_WEIGHT = 1;
const DEFAULT_TYPE = 'Memcached';
const DEFAULT_METHOD = 'read';
const NOT_FOUND = 16;
const TIMEOUT = 32;

$dir_class = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Class' . DIRECTORY_SEPARATOR;

$server = array(
    'host' => isset($_POST['host']) ? (string) $_POST['host'] : DEFAULT_HOST,
    'port' => isset($_POST['port']) ? (int) $_POST['port'] : DEFAULT_PORT,
    'weight' => isset($_POST['weight']) ? (int) $_POST['weight'] : DEFAULT_WEIGHT
);

$type =  isset($_POST['type']) ? (string) $_POST['type'] : DEFAULT_TYPE;
$method = isset($_POST['method']) ? (string) $_POST['method'] : DEFAULT_METHOD;

$key = isset($_POST['key']) ? (string) $_POST['key'] : null;
$value = isset($_POST['value']) ? $_POST['value'] : null;
$expiration = isset($_POST['expiration']) ? (int) $_POST['expiration'] : 0;
$expiration = isset($_POST['time']) ? (int) $_POST['time'] : 0;

if($value && $method == 'read'){
    $method = 'write';
}

$dma = memory($type);
$dma->addServer(
    $server['host'],
    $server['port'],
    $server['weight']
);

if(empty($dma)){
    throw new Exception('Could\'nt get DMA');
}


switch($method){
    case 'touch':
        $result = touch($dma, $key, $expiration);
        break;
    case 'delete':
        $result = delete($dma, $key, $time);
        break;
    case 'write':
        $result = write($dma, $key, $value, $expiration);
        break;
    default :
        $result = read($dma, $key);
}

echo $result;

function read(Memcached $dma, $key=''){
    return $dma->get($key);
}

function write(Memcached $dma, $key='', $value='', $expiration=0){
    if(is_array($key)){
        return $dma->setMulti($key, $value);
    } else{
        return $dma->set($key, $value, $expiration);
    }
}

function delete(Memcached $dma, $key='', $time=0){
    if(is_array($key)){
        return $dma->deleteMulti($key, $time);
    } else {
        return $dma->delete($key, $time);
    }
}

function touch(Memcached $dma, $key='', $expiration=0){
    return $dma->touch($key, $expiration);
}

$dma->quit();
function memory($type='Memcached'){
    return  new Memcached();
}