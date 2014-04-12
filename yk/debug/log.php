<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: log.php 2013-7-8$
 */

namespace yk\debug;

class log{
	
	public static function runlog($name, $message, $halt = false, $repeatcheck = false){
		$env = array(
			'clientip' => g('clientip'),
			'uid' => g('uid'),
			'siteurl' => g('siteurl')
		);
		$log = $env['clientip']."\t{$env['uid']}\t{$env['siteurl']}\t"."\n".str_replace(array("\r", "\n"), array(' ', ' '), trim($message));
		self::write($log, $name, $repeatcheck);
		if ($halt) {
			exit();
		}
	}
	
	public static function write($log, $name, $repeat_ck = false){
		
		if (empty($log)) {
			return ;
		}
		
		if (is_array($log)) {
			$log = implode("\n", $log);
		}
		
		$hash = md5($log);
		$time = time();
		$ymd = '_'.date('Ymd', $time);
		$filename = $name.$ymd.'.php';
		$dir = MAIN_ROOT.'data/log/';
		$file = $dir.$filename;
		$size = file_exists($file) ? filesize($file) : 0;
		
		if ($repeat_ck) {
			
			$fp = fopen($file, 'a+');
			if (!$fp) {
				exit('data/ 或  data/log/ 无法写入');
			}
			
			$last_pos = 10000;
			$maxtime = 60 * 10;
			$offset = $size - $last_pos;
			if($offset > 0) {
				fseek($fp, $offset);
			}
			if(($data = fread($fp, $last_pos)) != false) {
				$array = explode("\n\n", $data);
				if(is_array($array)) foreach($array as $key => $val) {
					$row = explode("\n", $val);
					if($row[0] != '<?php exit(); ?>') continue;
					list($thash, $ttime) = explode("\t", $row[1]);
					if($thash == $hash && (ytimestamp($ttime) > $time - $maxtime)) {
						return;
					}
				}
			}
		}
		
		if (!file_exists($file) || filesize($file) > 2097152) {
			$dir = opendir($dir);
			$name_len = strlen($name) + 1;
			$len = $name_len + 9;	//加上下划线跟Ymd格式的长度下划线  _20130721_
			$tmp_name = $name.'_';
			$maxid = 0;
			while (($entry = readdir($dir)) != false){
				if(substr($entry, 0, $name_len) != $tmp_name){
					continue;
				}
				$id = intval(substr($entry, $len, -4));
				$id > $maxid && $maxid = $id;
			}
			closedir($dir);
			$logfiledbk = $dir.$name.$ymd.'_'.($maxid + 1).'.php';
			@rename($file, $logfiledbk);
		}
		
		error_log('<?PHP exit;?>'."\n$hash\t".ygmdate($time, 'Y-m-d H:i:s')."\n".$log."\n\n", 3, $file);
	}
	
}