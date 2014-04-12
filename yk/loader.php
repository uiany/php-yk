<?php

/**
 *      [568089266@qq.com] (C)2014-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: loader.php 2014-1-7$
 */
 
namespace yk;

class loader{
	
	private static $_instance;
	
	private $_tables;
	private $_imports;
	
	public static function getInstance(){
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	private function __construct(){
		$this->_tables = array();
		$this->_imports = array();
	}
	
	private function _import($filename, $folder = '', $halt = true){
		$file = MAIN_ROOT.($folder ? $folder.'/'.$filename : $filename).'.php';
		return $this->import($file);
	}
	
	public function autoload($classname){
		$classname = strtolower($classname);
		$this->_import(str_replace('\\', '/', $classname));
	}
	
	public function table($name){
		if (!isset($this->_tables[$name])) {
			$tname = "\\application\\table\\$name";
			$this->_tables[$name] = new $tname;
		}
		return $this->_tables[$name];
	}
	
	public function import($file, $halt = true, $true_path = true){
		$key = md5($file);
		if (!isset($this->_imports[$key])) {
			$file = $true_path ? $file : MAIN_ROOT.$file;
			if (is_file($file)) {
				$this->_imports[$key] = 1;
				return include $file;
			}elseif ($halt){
				throw new exception\loader($file);
			}else{
				return false;
			}
		}
	}
}