<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-11-07
 * @version		1.0
 * @changeLog
 * 	-	all
 * @note
 *  - In Smarty bash coloring isn't working.
 */

namespace Priya;

$cols = $this->tput('columns');
$rows = $this->tput('rows');

$author = 
    $this->color(12) .
    'Remco van der Velde' .
    $this->color('reset')
;

$version = 
    $this->color(10) . 
    $this->data('priya.major') .
    $this->color('reset') .
    '.' .
    $this->color(15) .
    $this->data('priya.minor') .
    $this->color('reset') .
    '.' .
    $this->color(9) .
    $this->data('priya.patch') .
    $this->color('reset')
;    

$year_start =
    $this->color(4) .
    '2012' .
    $this->color('reset')
;

$year_current =
    $this->color(13) .
    date('Y') .
    $this->color('reset')
;

$copyright =
    $this->color(3) .
    '(c)' .
    $this->color('reset')
;
    
if($rows >= 25 && $cols >= 83){
    $this->output(str_repeat('-', $cols) . PHP_EOL);
    $this->output('                                                               ' . PHP_EOL);
    $this->output('    ########       #######     ##    ##       ##    #######    ' . PHP_EOL);
    $this->output('   ##       ##    ##     ##     ##   ##      ##    ##     ##   ' . PHP_EOL);
    $this->output('   ##       ##   ##      ##     ##    ##    ##    ##       ##  ' . PHP_EOL);
    $this->output('   ##       ##   ##      ##     ##     ##  ##     ##       ##  ' . PHP_EOL);
    $this->output('   ##       ##   ##     ##      ##      ####      ###########  ' . PHP_EOL);
    $this->output('   #########     ##    ##       ##       ##       ##       ##  ' . PHP_EOL);
    $this->output('   ##            ##     ##      ##       ##       ##       ##  ' . PHP_EOL);
    $this->output('   ##            ##      ##     ##       ##       ##       ##  ' . PHP_EOL);
    $this->output('   ##            ##      ##    ##       ##        ##       ##  ' . PHP_EOL);
    $this->output('                                                               ' . PHP_EOL);
    $this->output('   '. $copyright . ' ' . $year_start . '-'. $year_current . ' ' . $author . '                  ' . $version . PHP_EOL);
    $this->output('                                                               ' . PHP_EOL);
    $this->output(str_repeat('-', $cols) . PHP_EOL);
}