<?php
/**
 * @author         Remco van der Velde
 * @since         2016-10-19
 * @version        1.0
 * @changeLog
 *     -    all
 */

namespace Priya\Module\Cli\Application;

use Priya\Application;
use Priya\Module\Core\Cli;


class Host extends Cli {
    const DIR = __DIR__;
    const REDIRECT = 'redirect';

    public function run(){
        //host redirect command
        Host::redirect($this);

        $explode = explode('.',  $this->parameter('host', 1));
        $extension = array_pop($explode);
        $domain = array_pop($explode);
        $this->data('server.name', $domain . '.' . $extension);
        $this->data('server.alias', '*.' . $this->data('server.name'));
        $this->data('server.port', null !== $this->parameter('port', 1) ? $this->parameter('port', 1) : 80);
        $this->data('server.administrator', 'administrator@' . $this->data('server.name'));
        $this->data('server.directory', $this->data('dir.public'));

        var_dump($this->data('server'));
        die;
        /**
         * proposal: priya-memory
         * priya memory read <key>
         * priya memory write <key> url <url> value <value>
         * priya memory append <key> url <url> value <value>
         * priya memory prepend <key> url <url> value <value>
         *add when exist (skip things)
         *
         * host <domain.extension> port <port>
         *
         * parameter for domain name (might include subdomain.domain.extension) (so explode on domain (and pop the extension and domain))
         * server-alias is automated (*.domain.extension) for the host to work)
         * ask for port number (default 80)
         * server directory is automated ($dir.public)
         * server e-mail is automated (administrator@domain.extension)
         * when {$dir.host}{$server.name} exist
         *      check
         * dir read /etc/apache2/sites-available
         * read config files on existing domain.extension
         * create an (amount of files) apache config file in /etc/apache2/sites-available if not exist
         * exec a2ensite domain.extension
         * exec systemctl apache reload
         * create directory {$dir.host}{$server.name}{$public_html}
         * create directory {$dir.host}{$server.name}Data/
         * create directory {$dir.host}{$server.name}Execute/
         * copy {$module.dir.data}default.exe to {$dir.host}{$server.name}Execute/Index.exe (if file not exist)
         * copy {$module.dir.data}Route.json to {$dir.host}{$server.name}Data/Route.json (if file not exist)
         * Cli priya {$server.name} (what is happening?)
         * parse {$dir.host}{$server.name}Execute/Index.exe which writes an {$dir.host}{$server.name}{$public_html}Index.html (1)
         * parse{$dir.host}{$server.name}Data/Route.json which overwites itself needs variable domain.extension (create www.domain.extension)
         * this will create a default route to the index.html in post / get and in cli creates a new Index.html (see 1)
         * also need to be added to the main route file in {$dir.data}Route.json
         *      which needs to check if a resource exists of: {$dir.host}{$domain}\/Data\/Route.json
         * and namespace to autoload... in {$dir.data}Config.json ("LikeIt\\Cloud":"{$dir.host}like-it.cloud/Source")
         * exec certbot (to automatically enable https) (needs expect) (version 2) for domain.extension, www.domain.extension
         */

        /*
        $this->data('type', $this->parameter('type', 1));
        if($this->data('type') == 'document'){
            $execute = Copyright::document(Copyright::execute($this));
        } else {
            $execute = Copyright::execute($this);
        }
        return $execute;
        */
    }

    public static function redirect($object){
        $redirect = $object->parameter('host', 1);
        if(strtolower($redirect) == Host::REDIRECT){
            $data = $object->request('data');
            $data[0] = $object->data('priya.bin');
            $exec = implode(' ', $data);
            $exec = str_ireplace('host redirect', 'host/redirect', $exec);
            exec($exec, $output);
            echo implode(PHP_EOL, $output);
            exit;
        }
    }

    public static function document($output=''){
        $result = '/**';
        $explode = explode(PHP_EOL, $output);
        foreach($explode as $line){
            $result .= ' * ' . $line . PHP_EOL;
        }
        $result .= '**/';
        return $result;
    }
}