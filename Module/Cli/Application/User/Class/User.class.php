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
use Priya\Module\Core\Data;
use Priya\Application;
use stdClass;
use password_hash;

class User extends Cli {
	const DIR = __DIR__;	
			
	public function run(){	
		$read = $this->read($this->data('dir.data') . Application::CONFIG);		
		if(empty($read)){						
			$this->error('read', true);
		}			
		if($this->parameter('delete')){
			$this->createDelete();
		}
		elseif($this->parameter('password')){
			$this->createPassword();
		}
		elseif($this->parameter('create')){			
			$this->createUser();
			$this->error('delete', 'read');
		}
		return $this->result('cli');						
	}		
	

	private function createPassword(){
		$data = $this->request('data');
		array_shift($data);
		array_shift($data);
		$method = array_shift($data);
		$attribute = array_shift($data);
		$value = implode(' ', $data);
		
		$data = new Data();
		$data->read($this->data('dir.data') . Application::CONFIG);
		$user = $data->data('users.' . $attribute);		
		$user->salt = $this->createSalt(255);
		$options = array(
			'cost' => 11,
			'salt' => $user->salt,
		);
		$user->password = password_hash($value, PASSWORD_DEFAULT, $options);		
		$data->data('users.' . $attribute, $user);										
		$data->write($this->data('dir.data') . Application::CONFIG);
	}
	
	private function createDelete(){
		$data = $this->request('data');
		array_shift($data);
		array_shift($data);
		$method = array_shift($data);
		$attribute = array_shift($data);
		$value = implode(' ', $data);
		
		$data = new Data();
		$data->read($this->data('dir.data') . Application::CONFIG);
		$data->data('delete', 'users.' . $attribute);
		$data->write($this->data('dir.data') . Application::CONFIG);			
	}
	
	public function createUser($url=''){
		if(empty($url)){
			$url = $this->data('dir.data') . Application::CONFIG;
		}
		$data = $this->request('data');
		array_shift($data);
		array_shift($data);
		
		$method = array_shift($data);
		$attribute = array_shift($data);
		$value = implode(' ', $data);
		
		if(stristr($attribute, '.') !== false){
			$this->error('username', true);
			return false;
		}
		
		$user = new stdClass();
		$user->username = $attribute;
		$user->salt = $this->createSalt(255);
		$options = array(
				'cost' => 11,
				'salt' => $user->salt,
		);
		$user->password = password_hash($value, PASSWORD_DEFAULT, $options);
		
		$data = new Data();
		$data->read($url);	
		$read = $data->data('users.'. $user->username);
		if(!empty($read)){
			$this->error('user-exists', true);
			return;
		}				
		$data->data('users.' . $user->username, $user);		
		$data->write($url);		
	}
	
	private function createSalt($length=32){
		$data = array(
				'a','b','c','d','e','f','g','h','i',
				'j','k','l','m','n','o','p','q','r',
				's','t','u','v','w','x','y','z',
				'A','B','C','D','E','F','G','H','I',
				'J','K','L','M','N','O','P','Q','R',
				'S','T','U','V','W','X','Y','Z'
		);
		$max = count($data) -1;
		$salt = '';
		for($i=0; $i < $length; $i++){
			$rand = rand(0, $max);
			$salt.= $data[$rand];
		}
		return $salt;
	}
}
