<?php

/**
 *      [568089266@qq.com] © 2012-2099 yukeyong. All Rights Reserved 
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: memory_memcache.php UTF-8 2012-11-26 下午3:43:15CN xiaoan $
 */

namespace yk\memory;

class memcache implements \yk\IMemory{
	
	private $_object;
	public $enabled;
	
	public function __construct(){
		$this->enabled = false;
	}
	
	public function init($config){
		if (!empty($config['server'])){
			$this->_object = new \Memcache;
			if ($config['pconnect']){
				$connect = @$this->_object->pconnect($config['server'], $config['port'], $config['timeout']);
			}else{
				$connect = @$this->_object->connect($config['server'], $config['port'], $config['timeout']);
			}
			
			$this->enabled = $connect ? true : false;
		}
	}
	
	public function get($key){
		return $this->_object->get($key);
	}
	
	public function getmulti(array $key){
		return $this->_object->get($key);
	}
	
	public function set($key, $value, $expire = 0){
		return $this->_object->set($key, $value, MEMCACHE_COMPRESSED, $expire);
	}
	
	public function delete($key){
		return $this->_object->delete($key);
	}
	
	public function increment($key, $value = 1){
		return $this->_object->increment($key, $value);
	}
	
	public function decrement($key, $value = 1){
		return $this->_object->decrement($key, $value);
	}
	
	public function flush(){
		return $this->_object->flush();
	}
	
	public function get_version(){
		return $this->_object->getversion();
	}
}