<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: xss.php 2013-9-7$
 *      
 *      对mca的频繁操作进行检查, 调用方式为一般为:
 *      if(\yk\helper\mcaCheck::isPass()){
 *      	//您的代码
 *      }else{
 *      	//操作频繁
 *      }
 *      
 *      本类需要建立二张表
 *      
 *      CREATE TABLE `mca_check` (
		   `sid` varchar(15) NOT NULL,
		   `uid` int(10) unsigned NOT NULL,
		   `ip` int(10) NOT NULL,
		   PRIMARY KEY (`sid`)
		 ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
 *      
 *      CREATE TABLE `mca_list` (
		   `sid` varchar(15) NOT NULL,
		   `mca` varchar(32) NOT NULL,
		   `lasttime` int(10) unsigned NOT NULL,
		   `count` tinyint(2) unsigned DEFAULT '1',
		   PRIMARY KEY (`sid`,`mca`)
		 ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
 *      
 */

namespace yk\helper;

class mcaCheck{
	
	/**
	 * $second 最小间隔时间
	 * $count 最大可操作次数
	 * @return boolean
	 */
	public static function isPass($second = 3, $count = 1){
		$sid = auth::decode(ygetcookie('cc_sid'));
		if (!$sid || !($xss = self::find($sid)) ) {
			$xss = self::create();
			$sid = $xss['sid'];
			t('mca_check')->insert($xss, false, true);
			ysetcookie('cc_sid', auth::encode($xss['sid']), 86400);
		}
		
		return self::check($sid, $second, $count);
	}
	
	public static function update(){
		$lastmca = md5(g('CURMCA'));
		$sid = auth::decode(ygetcookie('cc_sid'));
		if (!$sid || !($xss = self::find($sid)) ) {
			$xss = self::create();
			$sid = $xss['sid'];
			t('mca_check')->insert($xss, false, true);
			ysetcookie('cc_sid', auth::encode($xss['sid']), 86400);
		}
		return t('mac_list')->insert(array(
			'sid' => $sid,
			'mca' => $lastmca,
			'lasttime' => TIMESTAMP,
			'count' => 1
		), false, true);
	}
	
	private static function find($sid){
		$uid = g('uid');
		$ip = ip2long(g('clientip'));
		return t('mca_check')->fetch_record($sid, $uid, $ip);
	}
	
	private static function create(){
		return array(
			'sid' => uniqid(),	//13位
			'uid' => g('uid'),
			'ip' => ip2long(g('clientip'))
		);
	}
	
	private static function check($sid, $second = 3, $count = 1){
		$lastmca = md5(g('CURMCA'));
		$xss_row = t('mac_list')->fetch_last($sid, $lastmca);
		if (!empty($xss_row) && (TIMESTAMP - $xss_row['lasttime'] < $second)) {
			if ($xss_row['count'] >= $count) {
				return false;
			}
		}
		return true;
	}
}