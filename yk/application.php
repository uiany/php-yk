<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: yk_application.php 2013-7-8$
 */
 
namespace yk;

class application{
	
	private static $_instance;
	
	private $_var;
	
	private $_dispatcher;
	
	public static function getInstance(){
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	private function __construct(){
		$this->_init_environment();
		$this->_init_config();
		$this->_init_iosystem();
	}
	
	public function run($core = null){
		
		$this->_dispatcher = new dispatcher();
		//载入bootstrap
		if ($core !== null && is_object($core)) {
			$reflect = new \ReflectionClass($core);
			$methods = array(); $i = 0;
			$this->_bootstrap_sort($reflect->getMethods(\ReflectionMethod::IS_PUBLIC), $methods, $i);
			while ($i){
				foreach ($methods[$i--] as $reflect_func){
					if (strpos($reflect_func->name, 'init_') === 0) {
						$reflect_func->invoke($core);
					}
				}
			}
		}
		
		//如果在bootstrap中未载入替换的函数
		if (!defined('FUNC_INIT')) {
			if (!@include YK_CORE_ROOT.'func.php') {
				exit('yk reserve func not find !');
			}
		}
		
		$this->_dispatcher->run();
	}
	
	public function &G(){
		return $this->_var;
	}
	
	public function getDispatcher(){
		return $this->_dispatcher;
	}
	
	private function _bootstrap_sort($methods, &$refs, &$i = 0){
		$last_class = null; $i = 0;
		foreach ($methods as $method){
			if ($last_class != $method->class) {
				$last_class = $method->class;
				$i++;
			}
			$refs[$i][] = $method;
		}
	}
	
	private function _init_environment(){
		error_reporting(E_ERROR);
		define('TIMESTAMP', time());
		
		$superglobal = array(
				'GLOBALS' => 1, '_SERVER' => 1, '_GET' => 1, '_POST' => 1, '_FILES' => 1, '_COOKIE' => 1, '_SESSION' => 1, '_REQUEST' => 1, '_ENV' => 1
			);
		
		// 删除非允许的全局变量
		foreach ($GLOBALS as $key => $value){
			if (!isset($superglobal[$key]) || !$superglobal[$key]) {
				unset($GLOBALS[$key]);
			}
		}
		
		if (!@include YK_CORE_ROOT.'yk_func.php') {
			exit('yk core function file is not found !');
		}
		
		$this->_var = array(
				'timestamp' => TIMESTAMP,
				'starttime' => microtime(true),
				'clientip' => $_SERVER['REMOTE_ADDR'],
				'clientport' => $_SERVER['REMOTE_PORT'],
				
				'sitename' => '',
				'siteurl' => '',
				
				'uid' => 0
			);
	}
	
	private function _init_config(){
		$config = @include MAIN_ROOT.'conf/config.php';
		if (empty($config)) {
			exit('yk config file is not found');
		}
		
		if(empty($config['security']['authkey'])) {
			$config['security']['authkey'] = md5($config['cookie']['cookiepre'].$config['db'][1]['dbname']);
		}
		
		$config['debug'] = isset($config['debug']) ? $config['debug'] : 0;
		define('MAIN_DEBUG', $config['debug'] > 0 ? true : false);
		
		MAIN_DEBUG && ini_set('display_errors', 'On');
		error_reporting($config['debug'] > 0 ? ($config['debug'] > 1 ? E_ALL : E_ERROR) : 0);
		
		$cookie = &$config['cookie'];
		if(strpos($cookie['cookiepath'], '/') !== 0) {
			$cookie['cookiepath'] = '/'.$cookie['cookiepath'];
		}
		
		$cookie['cookiepre'] = substr(md5($cookie['cookiepre'].$cookie['cookiepath'].$cookie['cookiedomain']), 8, 8).'_';
		
		$this->_var['config'] = &$config;
	}
	
	private function _init_iosystem(){
		
		$config = &$this->_var['config'];
		
		$cplen = strlen($config['cookie']['cookiepre']);
		$cookie = array();
		foreach ($_COOKIE as $key => $value){
			if (substr($key, 0, $cplen) == $config['cookie']['cookiepre']){
				$cookie[substr($key, $cplen)] = $value;
			}
		}
		$this->_var['cookie'] = $cookie;
		unset($cookie);
		
		if (!isset($_SERVER['HTTP_ACCEPT_ENCODING']) || strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') === false || !function_exists('ob_gzhandler') ){
			$config['output']['gzip'] = false;
		}
		
		$this->_var['gzipcompress'] = $config['output']['gzip'];
		
		define('CHARSET', 'utf-8');
		
		if($config['output']['forceheader']) {
			@header('Content-Type: text/html; charset='.CHARSET);
		}
		
	}
}