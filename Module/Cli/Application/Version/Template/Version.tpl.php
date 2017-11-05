<?php
/**
 * @author         Remco van der Velde
 * @since         2016-11-07
 * @version        1.0
 * @changeLog
 *     -    all
 * @note
 *  - In Smarty bash coloring isn't working.
 */

namespace Priya;

$version = 'Priya ' . $this->data('priya.version') . ' (built: ' . $this->data('priya.built').')' . PHP_EOL;
$copyright = 'Copyright (c) 2015-' . date('Y') . ' Remco van der Velde' . PHP_EOL;
$dir = 'Installation dir: ' . $this->data('dir.root') . PHP_EOL;
$php = 'PHP ' . PHP_VERSION . ' Copyright (c) The PHP Group' . PHP_EOL;

$this->output($version);
$this->output($dir);
$this->output($copyright);
$this->output($php);