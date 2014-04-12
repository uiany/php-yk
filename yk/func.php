<?php

/**
 *      [568089266@qq.com] (C)2014-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: func.php 2014-1-13$
 *      
 *      如果重载这里的全部方法了，那么请在bootstrap里定义:define('FUNC_INIT', 1)
 *      这样YK就不会再次包含本文件了。
 */

if (!function_exists('avatar')) {
	function avatar($uid, $type){
		return g('siteurl')."/avatar/$type/$uid";
	}
}

if (!function_exists('runlog')) {
	function runlog($file, $message, $halt = false, $repeatcheck = false){
		yk\debug\log::runlog($file, $message, $halt, $repeatcheck);
	}
}

if (!function_exists('seccode_check')) {
	function seccode_check($hash, $value, $time = 300){
		$sec_auth = ygetcookie("seccoe_$hash");
		if (!empty($sec_auth)) {
			list($val, $t) = \yk\helper\auth::decode($hash);
			return $val == $value && ($t + $time >= TIMESTAMP) ? true : false;
		}else{
			return false;
		}
	}
}