<?php

/**
 *      [568089266@qq.com] © 2012-2099 yukeyong. All Rights Reserved 
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: memory_redis.php UTF-8 2012-11-26 下午4:08:08CN xiaoan $
 *      https://github.com/nicolasff/phpredis
 */

namespace yk\memory;

class redis implements \yk\IMemory{
	
	private $_object;
	public $enabled;
	
	public function __construct(){
		$this->enabled = false;
	}
	
	public function init($config){
		if (!empty($config['server'])){
			$this->_object = new \Redis();
			if ($config['pconnect']){
				$connect = $this->_object->pconnect($config['server'], $config['port'], $config['timeout']);
			}else{
				$connect = $this->_object->connect($config['server'], $config['port'], $config['timeout']);
			}
			$this->enabled = $connect ? true : false;
			if ($this->enabled){
				$this->_object->setOption(Redis::OPT_SERIALIZER, $config['serializer'] ? Redis::SERIALIZER_PHP : Redis::SERIALIZER_NONE);
			}
		}
	}
	
	public function get($key){
		if (is_array($key)){
			return $this->getmulti($key);
		}else{
			return $this->_object->get($key);
		}
	}
	
	public function getmulti($keys){
		$result = $this->_object->getMultiple($keys);
		$response = array();
		for ($i=0, $len = count($keys); $i<$len; $i++){
			$response[$keys[$i]] = $result[$i];
		}
		return $response;
	}
	
	public function set($key, $value, $expire = 0){
		if ($expire){
			return $this->_object->setex($key, $expire, $value);
		}else{
			return $this->_object->set($key, $value);
		}
	}
	
	public function delete($key){
		return $this->_object->delete($key);
	}
	
	public function increment($key, $value = 1){
		return $this->_object->incr($key, $value);
	}
	
	public function decrement($key, $value = 1){
		return $this->_object->decr($key, $value);
	}
	
	public function flush(){
		return $this->_object->flushAll();
	}
	
	public function get_version(){
		return $this->_object->getversion();
	}
	
}