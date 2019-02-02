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

use Priya\Module\Core\Cli;

$this->cli('create', 'Logo');

echo PHP_EOL;
echo 'Priya <command> <parameter(s)>' . PHP_EOL;

$line_width = $this->tput('width');

$command_start = ' ';
$command_end = ' ';
$command_width = ceil($line_width / 2);
$command_color = $this->tput('bold') . $this->color(CLi::COLOR_BLACK, Cli::COLOR_WHITE);


$parameter_start = '<';
$parameter_end = '> ';
$parameters_color = $this->color(Cli::COLOR_PINK, Cli::COLOR_WHITE);

$help_start = '(';
$help_end = ')';
$help_width = $line_width - $command_width;
$help_color = $this->color(Cli::COLOR_BLACK, Cli::COLOR_YELLOW);

$list = (array) $this->data('command');

ksort($list, SORT_NATURAL);

foreach($list as $command => $help){

    $space_width = $command_width - (strlen($command_start) + strlen($command) + strlen($command_end));

    $arguments = $this->data('command.' . $command . '.parameter');
    $parameters = '';

    if(is_array($arguments)){
        foreach($arguments as $parameter){
            $parameters .= $parameter_start . $parameter . $parameter_end;
        }
    }
    $field_command = $command_start . $command . $command_end . $parameters;
    if(strlen($field_command) > $command_width){
        $field_command = substr($field_command, 0, ($command_width - 3)) . '...';
    } else {
        $field_command .= str_repeat(' ', ($command_width - strlen($field_command)));
    }
    $field_command = $command_color . str_replace($parameter_start, $this->color('reset') . $parameters_color . $parameter_start, $field_command);
//     $field_command = str_replace($parameter_end, $parameter_end . $this->color('reset'), $field_command);

    $field_help = $help_start . $this->data('command.' . $command . '.info') . $help_end;
    if(strlen($field_help) > $help_width){
        $field_help = substr($field_help, 0, ($help_width - 3 - strlen($help_end))) . '...' . $help_end;
    } else {
        $field_help .= str_repeat(' ', ($help_width - strlen($field_help)));
    }
    $field_help = $this->color('reset') . $help_color . $field_help . $this->color('reset');
    echo $field_command . $field_help . PHP_EOL;
}




/*
echo "  " . 'help                                          (Priya help)' . PHP_EOL;
echo "  " . 'version                                       (Priya version)' . PHP_EOL;
echo "  " . 'license                                       (Priya license)' . PHP_EOL;
echo "  " . 'locate <route>                                (locate a route <route> name in json format)' . PHP_EOL;
echo "  " . 'route --list                                  (this will show available cli routes)' . PHP_EOL;
echo "  " . 'route --list-all                              (this will show all available routes)' . PHP_EOL;
# echo "  " . 'test <class> <file>                           (this will test the <class> with <file>)' . PHP_EOL;
echo "  " . 'cache/clear                                   (clear cache)' . PHP_EOL;
echo "  " . 'config                                        (current configuration in json format)' . PHP_EOL;
# echo "  " . 'parser                                        (generates a parser file (speed up parsing))' . PHP_EOL;
echo "  " . 'javascript create                             (generates the javascript source)' . PHP_EOL;
echo "  " . 'host redirect add <host> <target>             (adds a host to the redirect)' . PHP_EOL;
echo "  " . 'host redirect delete <host>                   (deletes a host from the redirect)' . PHP_EOL;
echo "  " . 'javascript create                             (generates the javascript source)' . PHP_EOL;

*/
