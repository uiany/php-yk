<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: process.php 2013-8-18$
 */

namespace yk\helper;

class process{

	public static function islocked($process, $ttl = 0){
		$ttl = $ttl < 1 ? 600 : intval($ttl);
		return self::_status('get', $process) || self::_find($process, $ttl);
	}

	public static function unlock($process){
		self::_status('rm', $process);
		self::_cmd('rm', $process);
	}

	private static function _status($cmd, $process){
		static $plist = array();
		$ret = true;
		switch ($cmd){
			case 'get':
				$ret = isset($plist[$process]) && $plist[$process] ? true : false; break;
			case 'set':
				$plist[$process] = 1; break;
			case 'rm':
				$plist[$process] = 0; break;
			case 'clear':
				$plist = array(); break;
			default:
				break;
		}
		return $ret;
	}

	private static function _find($process, $ttl){
		$ret = true;
		if(!self::_cmd('get', $process)){
			self::_cmd('set', $process, $ttl);
			$ret = false;
		}
		self::_status('set', $process);
		return $ret;
	}


	private static function _cmd($cmd, $process, $ttl = 0){
		static $allowmem = null;
		if ($allowmem === null){
			$allowmem = in_array(memory('check'), array('memcache', 'redis')) ? 1 : 0;
		}
		if ($allowmem){
			return self::_cmd_memory($cmd, $process, $ttl);
		}else{
			return self::_cmd_db($cmd, $process, $ttl);
		}
	}

	private static function _cmd_memory($cmd, $process, $ttl = 0){
		$ret = '';
		switch ($cmd){
			case 'get':
				$ret = memory('get', $process, 'y_p_lock_'); break;
			case 'set':
				$ret = memory('set', $process, time(), $ttl, 'y_p_lock_'); break;
			case 'rm':
				$ret = memory('rm', $process, 'y_p_lock_'); break;
			default:
				$ret = false; break;
		}
		return $ret;
	}

	private static function _cmd_db($cmd, $process, $ttl = 0){
		$ret = '';
		switch ($cmd){
			case 'get':
				$ret = $ret = t('sys_process')->fetch_first($process);
				if (empty($ret) || $ret['expiry'] < time()){
					$ret = false;
				}else{
					$ret = true;
				}
				break;
			case 'set':
				$ret = t('sys_process')->insert(array('processid' => $process, 'expiry' => time() + $ttl), false, true);
				break;
			case 'rm':
				$ret = $ret = t('sys_process')->delete_process($process, time()); break;
			default:
				$ret = false; break;
		}
		return $ret;
	}

}