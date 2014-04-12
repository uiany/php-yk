<?php

/**
 *      [568089266@qq.com] (C)2014-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: error.php 2014-1-7$
 */
 
namespace yk;

class error{
	
	const ERR_DEFAULT = 0;		//默认，启用yk\debug\console
	const ERR_CATCH = 3;		//捕捉后输出中止
	const ERR_CATCHSHOW	= 5;	//捕捉后并dispatcher module\showmsg\exception
	
	private static $_initialized;
	
	private static $_callback;
	
	private static $_throwSwitch = 0;
	private static $_catchSwitch = 0;
	
	public static function init(){
		if (!self::$_initialized) {
			set_error_handler(array('yk\error', 'errorHandler'));
			set_exception_handler(array('yk\error', 'exceptionHandler'));
			self::$_initialized = true;
		}
	}
	
	public static function errorHandler($errno, $errstr, $errfile, $errline, $errcontext = array()){
		hookscript('yk/error');
		if (self::$_callback) {
			call_user_func_array(self::$_callback, func_get_args());
			return false;
		}else{
			if (($errno & YK_CORE_DEBUG)) {
				if (class_exists('\yk\debug\console')) {
					\yk\debug\console::system_error('system', $errstr, $errfile, $errline, true, true);
				}else{
					exit($errstr);
				}
			}
		}
	}
	
	public static function exceptionHandler(\Exception $e){
		hookscript('yk/exception');
		if (self::$_catchSwitch) {
			if (self::$_throwSwitch){
				echo '[', $e->getCode(), ']', $e->getMessage();
				exit;
			}else{
				$dispatcher = \yk\yk::app()->getDispatcher();
				$request = $dispatcher->getRequest();
				$request->setControllerName('showmsg');
				$request->setActionName('exception');
				$request->setParam('exception', $e);
				$dispatcher->dispatch();
			}
		}else{
			if (class_exists('\yk\debug\console')) {
				\yk\debug\console::exception_error($e);
			}else{
				trigger_error($e->getMessage(), E_USER_ERROR);
			}
		}
	}
	
	public static function throwException(\Exception $e){
		if (self::$_catchSwitch > 1) {
			self::$_catchSwitch ++;
		}
		self::exceptionHandler($e);
	}
	
	public static function setErrorHandler($callback){
		is_callable($callback) or trigger_error('ErrorHandler is not can callback method', E_USER_ERROR);
		self::$_callback = $callback;
	}
	
	public static function setExcetiponType($status = 0){
		self::$_catchSwitch = $status & 1 ? 1 : 0;
		self::$_throwSwitch = $status & 2 ? 1 : 0;
	}
}