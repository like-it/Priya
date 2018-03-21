<?php
/**
 * @author         Remco van der Velde
 * @since         19-07-2015
 * @version        1.0
 * @changeLog
 *  -    all
 */
namespace Priya\Module;

use Memcached;

class Memory {
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = 11211;
    const DEFAULT_WEIGHT = 1;
    const DEFAULT_TYPE = 'Memcached';

    const NOT_FOUND = 16;
    const TIMEOUT = 32;

    public static function server(Memcached $dma, $server=array()){
        $server = array_merge($server, array(
            'host' => Memory::DEFAULT_HOST,
            'port' => Memory::DEFAULT_PORT,
            'weight' => Memory::DEFAULT_WEIGHT
        ));
        $dma->addServer(
            $server['host'],
            $server['port'],
            $server['weight']
        );
        return $dma;
    }

    public static function read(Memcached $dma, $key=''){
        return $dma->get($key);
    }

    public static function write(Memcached $dma, $key='', $value='', $expiration=0){
        $result = array();
        $result['key'] = $key;
        $result['value'] = $value;
        $result['expiration'] = $expiration;
        if(is_array($key)){
            $result['set'] = $dma->setMulti($key, $value);
        } else{
            $result['set'] = $dma->set($key, $value, $expiration);
        }
        return $result;
    }

    public static function delete(Memcached $dma, $key='', $time=0){
        if(is_array($key)){
            return $dma->deleteMulti($key, $time);
        } else {
            return $dma->delete($key, $time);
        }
    }

    public static function touch(Memcached $dma, $key='', $expiration=0){
        return $dma->touch($key, $expiration);
    }

    public static function dma($type='Memcached'){
        return new Memcached();
    }


}