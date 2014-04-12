<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: yk.php 2013-7-8$
 */

namespace yk;

define('YK_CORE_DEBUG', E_ERROR | E_PARSE | E_COMPILE_ERROR | E_USER_ERROR | E_STRICT);
//define('YK_CORE_DEBUG', 0);
define('YK_CORE_ROOT', dirname(__FILE__).DIRECTORY_SEPARATOR);
define('MAIN_ROOT', substr(YK_CORE_ROOT, 0, -3));

// yk初始化
yk::init();

class yk{
	
	// yk 基础对象
	private static $_app;
	private static $_loader;
	
	public static function init(){
		date_default_timezone_set('Etc/GMT+0');
		
		require YK_CORE_ROOT.'loader.php';
		self::$_loader = loader::getInstance();
		
		if (!spl_autoload_register(array(self::$_loader, 'autoload'), true, true)) {
			exit('Error, spl_autoload_register must exist !');
		}
		
		self::$_app = application::getInstance();
	}
	
	public static function app(){
		return self::$_app;
	}
	
	public static function loader(){
		return self::$_loader;
	}
	
	public static function t($table){
		return self::$_loader->table($table);
	}
	
	public static function m($table){
		return new model($table);
	}
	
	public static function g($key, $value = null, $isset = false){
		$keys = explode('/', $key);
		$p = &self::$_app->G();
		foreach ($keys as $k){
			if (!isset($p[$k])) {
				if ($value === null && !$isset) {
					return null;
				}else{
					$p[$k] = array();
				}
			}
			$p = &$p[$k];
		}
		if ($value !== null || $isset) {
			$p = $value;
		}
		return $p;
	}
}

final class db extends db\database{}