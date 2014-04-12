<?php

/**
 *      [568089266@qq.com] (C)2014-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: memcached.php 2014-3-28$
 *      
 *      config:
 *      
 *      'memory' = array{     
 *      	'memcached' => array(
 *      		array('host' => '', 'port' => '11211', 'weight' => 0),
 *      		array('host' => '', 'port' => '11211', 'weight' => 0),
 *      		...
 *      	)
 *      }
 *      
 */

namespace yk\memory;

class memcached implements \yk\IMemory{
	
	private $_object;
	public $enabled;
	
	public function __construct(){
		$this->enabled = false;
	}
	
	public function init($config){
		$this->_object = new \Memcached('story_pool');
		$servers = array();
		foreach ($config as $conf){
			$servers[] = array($conf['host'], $conf['port'], $conf['weight']);
		}
		$this->enabled = $this->_object->addServers($servers);
	}
	
	public function get($key){
		return $this->_object->get($key);
	}
	
	public function getmulti(array $key){
		return $this->_object->getMulti($key);
	}
	
	public function set($key, $value, $expire = 0){
		return $this->_object->set($key, $value, $expire);
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
		return $this->_object->getVersion();
	}
	
}