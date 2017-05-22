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

if($rows > 50){
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
    $this->output('   (c) 2015-'. date('Y') . ' Remco van der Velde                   ' . $this->data('version') . PHP_EOL);
    $this->output('                                                               ' . PHP_EOL);
    $this->output(str_repeat('-', $cols) . PHP_EOL);
}