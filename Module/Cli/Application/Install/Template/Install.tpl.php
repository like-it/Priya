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

$object = array();

echo 'Please create an administrator account:' . PHP_EOL;
$this->data('node.user.username', $this->read('input', 'username: '));
$this->data('node.user.email', $this->read('input', 'email: '));
$this->data('node.user.password.main', $this->read('input-hidden', 'password: '));
$this->data('node.user.password.repeat', $this->read('input-hidden', 'password again: '));

echo 'What is the dirname of the public_html directory?' . PHP_EOL;
$this->data('config.public_html', $this->read('input', 'public_html: '));
echo 'What is the website host?' . PHP_EOL;
$this->data('config.server.host', $this->read('input', 'host: '));
$this->data('config.server.timeout', 600);
echo 'What is the mailserver host?' . PHP_EOL;
$this->data('config.mail.host', $this->read('input', 'host: '));
echo 'What is the mailserver port?' . PHP_EOL;
$this->data('config.mail.port', intval($this->read('input', 'port (587): ')));
echo 'What is the mailserver security?' . PHP_EOL;
$this->data('config.mail.secure', $this->read('input', 'security (tls): '));
echo 'What is the mailserver username?' . PHP_EOL;
$this->data('config.mail.username', $this->read('input', 'username: '));
echo 'What is the mailserver password?' . PHP_EOL;
$this->data('config.mail.password.main', $this->read('input-hidden', 'password: '));
$this->data('config.mail.password.repeat', $this->read('input-hidden', 'password again: '));


var_Dump($this->data());
