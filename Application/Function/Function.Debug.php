<?php

function debug($debug=null, $title=null, $is_export=false, $trace_extra=0){
    $trace = debug_backtrace(true);
    if(php_sapi_name() === 'cli'){
        echo strtoupper($title) . PHP_EOL;
        echo '-----------------------------------------------------------------' . PHP_EOL;
        if($is_export !== false){
            var_export($debug);
        } else {
            var_dump($debug);
        }

    } else {
        if(isset($trace[0 + $trace_extra])){
            echo '<section class="debug">';
            echo '<div class="head">';
            if(!empty($title)){
                echo '<h2>' .$title .'</h2>';
            } else {
                echo '<h2>' .$trace[1 + $trace_extra]['function'] .'</h2>';
            }

            echo '</div>';
            echo '<div class="info">';
            echo '<ul>';
            if(isset($trace[1 + $trace_extra]['class'])){
                echo '<li class="class" title="Class: ' . $trace[1 + $trace_extra]['class'] . '"><span class="title">Class:</span><span class="value">'. $trace[1  + $trace_extra]['class'] .'</span></li>';
            }
            echo '<li class="file" title="File: ' . $trace[0 + $trace_extra]['file'] . '"><span class="title">File:</span><span class="value">'. $trace[0]['file'] .'</span></li>';
            echo '<li class="line" title="Line: ' . $trace[0 + $trace_extra]['line'] . '"><span class="title">Line:</span><span class="value">'. $trace[0]['line'] .'</span></li>';
            echo '<li class="argument-list"><ul>';
            echo '</ul></li>';
            echo '</ul>';
            echo '</div>';
            echo '<div class="body">';
            if(
                    !is_array($debug) &&
                    !is_object($debug)
                    ){
                        if(is_null($debug)){
                            echo '<font color="#3465a4">null</font>' . "\r\n";
                        }
                        elseif($debug === false){
                            echo '<font color="#3465a4">false</font>' . "\r\n";
                        }
                        elseif($debug === true){
                            echo '<font color="#3465a4">true</font>' . "\r\n";
                        } else {
                            echo '<font color="#3465a4">' . $debug . '</font>' . "\r\n";
                        }
            }
            elseif(!empty($is_export)){
                var_export($debug);
            } else {
                var_dump($debug);
            }
            echo '<hr>';
            echo '</div>';
            echo '</section>';
        }

    }
}