<?php

namespace Priya\Module\Parse;

use Priya\Module\Parse;
use Exception;

class Code extends Core {
    const STATUS = 'is_code';

    const TAG_NAME = 'priya';
    const TAG_OPEN = '{';
    const TAG_CLOSE = '}';

    public static function find(Parse $parse, $record=[]){
        if(!is_array($record)){
            return [];
        }
        if(isset($record['execute'])){
            return $record;
        }
        if(!isset($record['tag'])){
            throw new Exception(' tag attribute required in $record');
        }
        $explode = explode(Code::TAG_OPEN . '/' . Code::TAG_NAME . Code::TAG_CLOSE, $record['tag'], 2);
        $explode = explode(Code::TAG_OPEN . Code::TAG_NAME . Code::TAG_CLOSE, $explode[0], 2);
        if(!isset($explode[1])){
            throw new Exception(' tag value needs to contain ' . Code::TAG_OPEN . Code::TAG_NAME . Code::TAG_CLOSE);
        }
        $code = $explode[1];

        $parse->data('priya.parse.is.code', true);
        $record['execute'] = $parse->compile($code);
        $record['status'] = Code::STATUS;

        return $record;
    }
}