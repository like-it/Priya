<?php

/**
 * @author         Remco van der Velde
 * @since         2017-04-20
 * @version        1.0
 * @changeLog
 *     -    all
 */

use Priya\Module\Memory;

function function_memory_read($function=array(), $argumentList=array(), $parser=null){
    $key = array_shift($argumentList);

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
        throw new Exception('Could\'nt get DMA');
    }
    $result = Memory::read($dma, $key);
    $function['execute'] = $result;
    $dma->quit();
    return $function;
}
