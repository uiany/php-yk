<?php

/**
 *      [568089266@qq.com] (C)2014-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: input.php 2014-3-14$
 */
 
namespace yk\helper;

class input{
	
	const POST = 1;
	const GET = 2;
	const REQUEST = 3;
	const COOKIE = 4;
	
	/**
	 * @param string $name	键值
	 * @param string $func	filter的类型
	 * @param string $extra  filter的额外参数
	 * @param string $msg			不正确时报错信息，以null|开头时表示非必需
	 * @param string $type			获取方法
	 */
	public static function get($name, $func, $msg, $extra, $type = self::REQUEST){
		
		$value = null;
		while ($type && $value === null){
			if ($type & self::GET) {
				$value = isset($_GET[$name]) ? $_GET[$name] : $value;
				$type ^= self::GET;
				continue;
			}
			if ($type & self::POST) {
				$value = isset($_POST[$name]) ? $_POST[$name] : $value;
				$type ^= self::POST;
				continue;
			}
			if ($type & self::COOKIE) {
				$tvalue = ygetcookie($name);
				$value = $tvalue !== null ? $tvalue : $value;
				$type ^= self::COOKIE;
				continue;
			}
			break;
		}
		
		$need = $msg == null || strpos($msg, 'null|') === 0 ? false : true;
		$msg = !$need && is_string($msg) ? substr($msg, 5) : $msg;
		
		if ( (!$need && $value === null) 
				|| ($value !== null && yfilter($value, $func, $extra)) ) {
			return $value;
		}else{
			throw new \yk\exception\input(!is_null($msg) ? $msg : "$name variable's value is wrong");
		}
	}
	
	
	public static function multiGet(array $keys){
		$data = array();
		foreach ($keys as $key => $var){
			$var[2] = isset($var[2]) ? $var[2] : null;
			$var[3] = isset($var[3]) ? $var[3] : null;
			$var[4] = isset($var[4]) ? $var[4] : self::REQUEST;
			
			list($name, $func, $msg, $extra, $type) = $var;
			
			$value = self::get($name, $func, $msg, $extra, $type);
			
			if (!is_null($value)) {
				$data[is_string($key) ? $key : $name] = $value;
			}
		}
		return $data;
	}
	
}