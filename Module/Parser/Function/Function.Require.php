<?php

use Priya\Module\Parser\Literal;
use Priya\Module\Parser\Newline;
use Priya\Module\Parser\Token;

use Priya\Module\Parser;

/**
 * @author          Remco van der Velde
 * @since           2017-04-20
 * @version         1.0
 * @changeLog
 *     -    all
 *
 * @todo
 *  - require so error on fail
 *  - add include as require without fail
 */

function function_require($function=array(), $argumentList=array(), $parser=null, $data=null){
    if(!is_array($argumentList)){
        $argumentList = (array) $argumentList;
    }
    $original = $data;
    $url = array_shift($argumentList);
    if($data === null){
        $data = $parser->data();
    }
    $file = new Priya\Module\File();

    $tmp = $parser->data('require.' . $parser->random());
    if($tmp){
        $data = $parser->object_merge($data, $tmp);
    }
    $read = '';
    $exists = true;
    if(
        substr($url, 0, 4) !== \Priya\Module\File::SCHEME_HTTP &&
        file_exists($url) === false
    ){
        $exists = false;
        if($file->extension($url) == 'js'){
            echo '<script>console.error("File: ' . $url . ' doesn\'t exists");</script>';
        } else {
            throw new Exception('File (' . $url . ') doesn\'t exists');
        }
    }
    elseif(
        $exists === true &&
        $file->extension($url) == 'json'
    ){
        $parser->read($url);
        $read = '';
    }
    elseif(
        $exists === true
    ) {
        if(is_dir($url)){
            throw new Exception('File (' . $url . ') is a directory...');
        }
//         debug($url, __LINE__ . '::' . __FILE__);

        $parser->data('compile', true);
        $read = $parser->read($url);

//         $compiler = new Parser($parser->handler(), $parser->route(), $parser->data());
//         $compiler->data('compile', true);
//         $read = $compiler->read($url);



//         $read = $parser->read($url);

//         debug($read, __LINE__ . '::' . __FILE__);
        /*
        $read = Literal::extra($read);
        $read = Newline::replace($read, $parser->random());
        $read = Literal::replace($read, $parser->random());
        /*
        $read = $parser->compile($read, $parser->data(), false, false);
        $read = str_replace(
                array('{literal}', '{/literal}'),
                array('[literal][rand:' .  $parser->random() .']{literal}', '[/literal][rand:' .  $parser->random() .']{/literal}'),
                $read
                );
//         $read = Literal::remove($read);
        $read= Token::remove_comment($read);
        $read = Literal::extra($read);
        $read = Newline::replace($read, $parser->random());
        $read = Literal::replace($read, $parser->random());
        */
        /*
        $read = str_replace(
            array('[literal][rand:' .  $parser->random() .']', '[/literal][rand:' .  $parser->random() .']'),
            array('{literal}', '{/literal}'),
            $read
        );
        */
//         var_dump($url);
//         var_dump($read);
    }
    $function['execute'] = $read;
    return $function;
}
