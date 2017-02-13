<?php
/**
 * @author 		Remco van der Velde
 * @since 		19-07-2015
 * @version		1.0
 * @changeLog
 *  -	all
 */ 
namespace Priya\Module\File;

use Priya\Application;
use stdClass;

class Dir {
	private $node;
	
	public function ignore($ignore=null, $attribute=null){
		$node = $this->node();
		if(!isset($node)){
			$node = new stdClass();
		}
		if(!isset($node->ignore)){
			$node->ignore = array();
		}
		if($ignore !== null){
			if($ignore=='list' && $attribute !== null){
				$node->ignore = $attribute;
			}
			elseif($ignore=='find'){
				if(substr($attribute,-1) !== Application::DS){
					$attribute .= Application::DS;
				}
				foreach ($node->ignore as $nr => $item){
					if(stristr($attribute, $item) !== false){
						return true;
					}
				}
				return false;
			}
			else {
				if(substr($ignore,-1) !== Application::DS){
					$ignore .= Application::DS;
				}
				$node->ignore[] = $ignore;
			}
		}
		$node = $this->node($node);
		return $node->ignore;
	}
	
	public function read($url='', $recursive=false, $format='flat'){
		if(substr($url,-1) !== Application::DS){
			$url .= Application::DS;
		}
		if($this->ignore('find', $url)){
			return array();
		}
		$list = array();
		$cwd = getcwd();
		chdir($url);		
		if ($handle = opendir($url)) {
			while (false !== ($entry = readdir($handle))) {
				$recursiveList = array();
				if($entry == '.' || $entry == '..'){
					continue;
				}
				$file = new stdClass();
				$file->url = $url . $entry;
				if(is_dir($file->url)){
					$file->url .= Application::DS;
					$file->type = 'dir';					
				}
				if($this->ignore('find', $file->url)){
					continue;
				}
				$file->name = $entry;
				if(isset($file->type)){
					if(!empty($recursive)){
						$directory = new dir();
						$directory->ignore('list', $this->ignore());
						$recursiveList = $directory->read($file->url, $recursive, $format);
	
						if($format !== 'flat'){
							$file->list = $recursiveList;
							unset($recursiveList);
						}
					}
				} else {
					$file->type = 'file';
				}
				if(is_link($entry)){
					$file->link = true;
				}
				/* absolute url is_link wont work probably the targeting type
				if(is_link($file->url)){
					$file->link = true;
				}
				*/
				$list[] = $file;
				if(!empty($recursiveList)){
					foreach ($recursiveList as $recursive_nr => $recursive_file){
						$list[] = $recursive_file;
					}
				}
			}
		}
		closedir($handle);
		return $list;
	}
	
	public function delete($url='', $recursive=true){
		$this->debug('yup permission',true);
		die;
		if(file_exists($url)===false){
			return true;
		}
		$list = $this->read($url, $recursive);
		if(!empty($list)){
			krsort($list);
			foreach ($list as $nr => $file){
				if(isset($file->type) && $file->type == 'file'){
					$unlink = unlink($file->url);
					if(!empty($unlink)){
						unset($list[$nr]);
					}
				}
			}
			foreach ($list as $nr => $file){
				if(isset($file->type) && $file->type == 'dir'){
					$unlink = @rmdir($file->url);
					if(!empty($unlink)){
						unset($list[$nr]);
					}
				}
			}
		}
		@rmdir($url);
		if(empty($list)){
			return true;
		} else {
			return false;
		}
	}
	

	public function node($node=null){
		if($node !== null){
			$this->setNode($node);
		}
		return $this->getNode();
	}
	
	private function setNode($node=null){
		$this->node = $node;
	}
	
	private function getNode(){
		return $this->node;
	}
}