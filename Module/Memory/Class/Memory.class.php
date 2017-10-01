<?php
/**
 * @author 		Remco van der Velde
 * @since 		19-07-2015
 * @version		1.0
 * @changeLog
 *  -	all
 */
namespace Priya\Module;

use Memcached;
use stdClass;

class Memory extends Data{
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = 11211;
    const DEFAULT_WEIGHT = 1;

    const NOT_FOUND = 16;
    const TIMEOUT = 32;

    private $id;
    private $memory;
    private $nodeList;
    private $keyList;
    private $serverList;

    public function __construct($memory='', $id=null){
        if($id === null){
            $this->memory = new Memcached();
        } else {
            $this->id = $id;
            $this->memory = new Memcached($id);
        }
        if(is_object($memory)){
            $server = $this->createServer($memory);
            $this->memory->addserver(
                $server->host,
                $server->port,
                $server->weight
            );
        }
        if(is_array($memory)){
            foreach ($memory as $server){
                $server = $this->createServer($server);
                $this->memory->addserver(
                   $server->host,
                   $server->port,
                   $server->weight
                );
            }
        }
    }

    public function __destruct(){
        if($this->id === null){
            $this->stop();
        }
        $this->stop();
    }

    public function result($result='code'){
        if($result == 'code'){
            return $this->memory->getResultCode();
        }
    }

    public function restart(){
//         exec('service memcached stop');
//         exec('service memcached start');
        exec('service memcached restart');
    }

    private function createServer($server=''){
        $object = new stdClass();
        $object->host = Memory::DEFAULT_HOST;
        $object->port = Memory::DEFAULT_PORT;
        $object->weight = Memory::DEFAULT_WEIGHT;
        $object = Memory::object_merge($object, $server);
        return $object;
    }

    public function write($key='', $value='', $expiration=0){
        if(is_array($key)){
            return $this->memory->setMulti($key, $value);
        } else{
            return $this->memory->set($key, $value, $expiration);
        }
    }

    public function read($key=''){
        return $this->memory->get($key);
    }

    public function delete($key='', $time=0){
        if(is_array($key)){
            return $this->memory->deleteMulti($key, $time);
        } else {
            return $this->memory->delete($key, $time);
        }
    }

    public function touch($key='', $expiration=0){
        return $this->memory->touch($key, $expiration);
    }

    public function keyList(){
        $this->keyList = $this->memory->getAllKeys();
        return $this->keyList;
    }

    public function nodeList($attribute=''){
        $keys = $this->memory->getAllKeys();
        if($attribute == 'delete'){
            return $this->delete($keys);
        }
        if(!empty($attribute) && is_array($attribute)){
            $write = $this->write($attribute);
            if(!empty($write)){
                return $this->nodeList();
            }
            return false;
        }
        else {
            $this->memory->getDelayed($keys);
            $this->nodeList = $cache->fetchAll();
            return $this->nodeList;
        }
    }

    public function serverList(){
        $this->serverList = $this->memory->getServerList();
    }

    public function statistic(){
        return $this->memory->getStats();
    }

    public function version(){
        return $this->memory->version();
    }

    public function stop(){
        return $this->memory->quit();
    }

    public function option($option=null, $value=null){
        if($option === null){
            return;
        }
        if($value === null){
            $this->memory->getOption($option);
        } else {
            $this->memory->setOption($option, $value);
        }
    }
}