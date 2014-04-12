<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: memory.php 2013-8-17$
 */

namespace yk;

class memory{
	
	private static $_instance;
	
	const GET = 1;
	const SET = 2;
	const DEL = 3;
	const FLUSH = 5;
	const INC = 6;
	const DEC = 7;
	
	/**
	 * @var \yk\IMemory
	 */
	private $_memory;
	private $_config;
	private $_extension = array();
	private $_prefix;
	
	public $type;
	public $enable = false;
	public $debug = array();
	
	public static function getInstance(){
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	private function __construct(){
		$this->_extension['memcached'] =
			extension_loaded('memcached') ? '\yk\memory\memcached' : null;
		$this->_extension['redis'] = 
			extension_loaded('redis') ? '\yk\memory\redis' : null;
		$this->_extension['memcache'] =
			extension_loaded('memcache') ? '\yk\memory\memcache' : null;
		
		hookscript('yk/memory/init', $this->_extension);
		
		$this->_init(g('config/memory'));
	}
	
	private function _key($key, $prefix) {
		if (!is_array($key)) {
			return $this->_prefix.$prefix.$key;
		}else{
			foreach ($key as $k => $v){
				$key[$k] = $this->_prefix.$prefix.$v;
			}
			return $key;
		}
	}
	
	private function _trim_key($key, $prefix) {
		return substr($key, strlen($this->_prefix.$prefix));
	}
	
	private function _init($config){
		$this->_config = $config;
		$this->_prefix = !empty($this->_config['prefix']) ? $this->_config['prefix'] : substr(md5($_SERVER['HTTP_HOST']), 4, 6).'_';
		
		foreach ($this->_extension as $name => $classname){
			if ($classname && !($this->_memory instanceof \yk\IMemory)){
				
				$memory = new $classname();
				$memory->init(is_array($this->_config[$name]) ? $this->_config[$name] : null);
				if($memory->enabled){
					$this->_memory = $memory;
					break;
				}
			}
		}
		if (is_object($this->_memory)){
			$this->enable = true;
			$this->type =  get_class($this->_memory);
		}
	}
	
	public function get($key, $prefix = ''){
		if ($this->enable){
			if (is_array($key)){
				$ret = $this->_memory->getmulti($this->_key($key, $prefix));
				if ($ret !== false && !empty($ret)){
					$rows = array();
					foreach ((array)$ret as $key => $value){
						$rows[$this->_trim_key($key, $prefix)] = $value;
					}
					return $rows;
				}else{
					return false;
				}
			}else{
				return $this->_memory->get($this->_key($key, $prefix));
			}
		}else{
			return false;
		}
	}
	
	public function set($key, $value, $ttl = 0, $prefix = ''){
		return $this->enable ? $this->_memory->set($this->_key($key, $prefix), $value, $ttl) : false;
	}
	
	public function delete($key, $prefix = ''){
		return $this->enable ? $this->_memory->delete($this->_key($key, $prefix)) : false;
	}
	
	public function increment($key, $value = 1, $prefix = ''){
		return $this->enable ? $this->_memory->increment($this->_key($key, $prefix), $value) : false;
	}
	
	public function decrement($key, $value = 1, $prefix = ''){
		return $this->enable ? $this->_memory->decrement($this->_key($key, $prefix), $value) : false;
	}
	
	public function get_version(){
		return $this->enable ? $this->_memory->get_version() : false;
	}
	
	public function flush() {
		$ret = false;
		if($this->enable && method_exists($this->_memory, 'clear')) {
			$ret = $this->_memory->clear();
		}
		return $ret;
	}
	
	public function getextension() {
		return $this->_extension;
	}
}