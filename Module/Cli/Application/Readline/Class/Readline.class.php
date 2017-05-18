<?php
/**
 * @author 		Remco van der Velde
 * @since 		2016-10-19
 * @version		1.0
 * @changeLog
 * 	-	all
 */

namespace Priya\Module\Cli\Application;

use Priya\Module\Core\Cli;

class Readline extends Cli {
    const DIR = __DIR__;

    public function run(){
        readline_completion_function(array($this, 'complete'));
//         $read = readline('enter username:');

//         var_dump(readline_info());
        $c = 1;
        readline_callback_handler_install("[$c] Enter something: ", array($this, 'input'));

        $read = array(STDIN);

        $write = null;
        $except = null;

        $n = stream_select($read, $write, $except, null);
        readline_callback_read_char();


        $c++;
        readline_callback_handler_install("[$c] Enter something: ", array($this, 'input'));

        readline_callback_handler_remove();


        var_dump($n);
        var_dump($read);

//         readline_add_history($read);
//         $array = readline_list_history();
        /*
        echo "Password: ";
        system('stty -echo');
        $password = trim(fgets(STDIN));
        system('stty echo');
        // add a new line since the users CR didn't echo
        echo "\n";
        var_dump($password);
//
        return $this->result('cli');
        */
//         var_dump($array);
    }

    public function complete(){
        $args = func_get_args();
        var_dump($args);

    }

    public function input($input=''){
        echo 'input: ' . $input . "\n";
    }


}
