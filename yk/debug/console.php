<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: error.php 2013-7-8$
 */

namespace yk\debug;

class console{
	
	public static function exception_error($exception){

		list($_show, $_log) = self::_debug_backtrace();
		log::write($_log, 'system');
		self::_show('Exception：'.$exception->getCode(), $exception->getMessage(), $_show);
	}
	
	public static function system_error($type, $message, $file = '', $line = 0, $log = true, $show = true){
		
		list($_show, $_log) = self::_debug_backtrace();
		$log && log::write($_log, 'system');
		$show && self::_show($type, $message, $_show);
	}
	
	private static function _debug_backtrace(){
		
		$debugs = version_compare(PHP_VERSION, '5.4.0') < 0 ? debug_backtrace() :  debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 10);
		$log = array();
		$show = array();
		
		$skip_func_list = array(
				'yk\debug\console::_debug_backtrace',
				'yk\debug\console::system_error',
				'yk\error::errorHandler',
				'yk\debug\console::exception_error',
				'yk\error::exceptionHandler',
			);
		
		$isdebug = !!MAIN_DEBUG;
		
		foreach ($debugs as $debug){
			$func = (isset($debug['class']) ? $debug['class'].$debug['type'] : '').$debug['function'];
			
			if (in_array($func, $skip_func_list)) {
				continue;
			}
			
			if (!empty($debug['args'])) {
				$args = array();
				foreach ($debug['args'] as $arg){
					if (is_bool($arg)) {
						$args[] = $arg ? 'true' : 'false';
					}elseif (is_int($arg)){
						$args[] = $isdebug ? $arg : '%d';
					}elseif (is_float($arg)){
						$args[] = $isdebug ? $arg : '%d';
					}elseif (is_array($arg)){
						$args[] = 'Array';
					}elseif (is_object($arg)){
						$args[] = 'Object';
					}else{
						$args[] = $isdebug ? htmlspecialchars(self::_clear($arg)) : '%s';
					}
				}
				$func .= '('.implode(',', $args).')';
			}else{
				$func .= '()';
			}
			
			$debug['file'] = self::_clear($debug['file']);
			
			$show[] = array(
					'file' => $debug['file'],
					'line' => $debug['line'],
					'func' => $func
				);
			
			$log[] =  $func."\t".$debug['line']."\t".$debug['file'];
			
		}
		
		return array($show, $log);
	}
	
	private static function _clear($msg){
		$msg = str_replace(MAIN_ROOT, 'MAIN_ROOT/', $msg);
		return str_replace(array("\\", MAIN_ROOT, "\t", "\n", "\r"), array('/', '', ' '), $msg);
	}
	
	private static function _show($type, $message, $debug = array()){
		
		echo <<<EOT
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>yk! log</title>
<style style="text/css">
body{font-size:12px;}
</style>
</head>
<body>
<h4>($type) $message</h4>
<hr/>
<h4>PHP Debug:</h4>
EOT;
		if (!empty($debug)) {
			echo '<table border="0" width="100%" cellSpacing="1" bgcolor="#cccccc"><tr><td width="40">No</td><td width="350">File</td><td width="80">Line</td><td>Function</td></tr>';
			foreach ($debug as $k => $row){
				echo '<tr bgcolor="#fff"><td>', $k ,'</td><td>', $row['file'] ,'</td><td>', $row['line'] ,'</td><td>', $row['func'] ,'</td>';
			}
			echo '</table>';
		}else{
			echo '<p>Sorry, We can\'t collect more debug message';
		}
		if (\yk\yk::g('config/debug')) {
			try{
				$db = \yk\db::getInstance();
				$sqls = $db->debug();
				if (!empty($sqls)) {
					echo "<h4>SQL Debug:</h4>";
					foreach ($sqls as $sql){
						echo "<p>$sql";
					}
				}
			}catch (\Exception $e){
				//
			}
		}
		echo '<p>给您浏览带来的不便我们深感歉意，本次异常将会自动记录在日志，你也可以报告给网站管理人员！</p></body></html>';
		
		exit(0);
	}
	
}