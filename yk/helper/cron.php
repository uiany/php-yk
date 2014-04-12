<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cron.php 2013-8-4$
 */
 

/**
 * Example
 * type				day|weekday		hour		minute
 * 说明				每月几号或者每周几	小时			分钟
 * 每天				-1	0
 * 每周一，三			0	1|4
 * 每天11点			-1	0			11			0
 * 每月1号11点5分		1	0			11			5
 */

/**
 * 
CREATE TABLE `yk_sys_cron` (
   `cronid` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
   `available` tinyint(1) NOT NULL DEFAULT '0',
   `type` enum('user','system') NOT NULL DEFAULT 'user',
   `name` char(50) NOT NULL DEFAULT '',
   `filename` char(50) NOT NULL DEFAULT '',
   `lastrun` int(10) unsigned NOT NULL,
   `nextrun` int(10) unsigned NOT NULL DEFAULT '0',
   `weekday` tinyint(2) NOT NULL DEFAULT '0',
   `day` tinyint(2) NOT NULL DEFAULT '0',
   `hour` tinyint(2) NOT NULL DEFAULT '0',
   `minute` tinyint(2) NOT NULL DEFAULT '1',
   PRIMARY KEY (`cronid`),
   KEY `nextrun` (`available`,`nextrun`)
 ) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8
 *
 */

namespace yk\helper;

class cron{
	
	//星期对应的数值
	private static $_weekday = array(
		1 => 1,
		2 => 2,
		3 => 4,
		4 => 8,
		5 => 16,
		6 => 32,
		7 => 64
	); 
	
	public static function getWeek(){
		return self::$_weekday;
	}
	
	public static function run($id = null){
		
		$cron = $id ? t('sys_cron')->fetch_first($id) : t('sys_cron')->fetch_nextrun(time());
		
		if (empty($cron)) {
			return;
		}
		$id = $cron['cronid'];
		
		$processid = 'y_cron_'.$id;
		if (!$id && process::islocked($processid, 600)) {
			return false;
		}
		
		$cronfile = MAIN_ROOT.'application/cron/'.($cron['type'] == 'system' ? 'sys/' : 'user/').$cron['filename'];
		if (!file_exists($cronfile)) {
			return;
		}
		
		@set_time_limit(1000);
		@ignore_user_abort(true);
		defined('YK_CRON_OPEN') or define('YK_CRON_OPEN', 1);
		if(!@include $cronfile) {
			return false;
		}
		
		//设置下一次运行的时间
		self::_set_next_time($cron);
		process::unlock($processid);
		
		return true;
	}
	
	private static function _set_next_time($cron){
		$timeoffset = g('config/timeoffset');
		list($curyear, $curmonth, $curday, $curweek, $curhour, $curminute, $days) = explode('_', gmdate('Y_n_j_N_G_i_t', time() + $timeoffset * 3600 ));
		
		//获取下一次运行的时间
		$nextcron = array(
			'year' => $curyear,
			'month' => $curmonth,
			'day' => 0,
			'hour' => $cron['hour'],
			'minute' => $cron['minute'],
			'second' => 1
		);
		
		
		//周启作用
		if ($cron['weekday'] > 0) {
			$week = self::_make_week(($curweek + 1) % 7);
			foreach ($week as $k => $w){
				if ($cron['weekday'] & self::$_weekday[$w]) {
					$nextcron['day'] = $curday + $k + 1;
					break;
				}
			}
		}else{	//月启作用
			$cron['day'] %= 31;
			if ($cron['day'] > 0) {		//每月某一天
				$nextcron['day'] = $cron['day'];
				$nextcron['month'] = $cron['month'] + 1;
			}else{
				//每天某时刻
				$nextcron['day'] = $curday + 1;
			}
		}

		$nextrun = gmmktime($nextcron['hour'], $nextcron['minute'], $nextcron['second'], $nextcron['month'], $nextcron['day'], $nextcron['year']) - $timeoffset * 3600;
		
		$data = array('lastrun' => time(), 'nextrun' => $nextrun, 'available' => 1);
		if(!($nextrun > time())) {
			$data['available'] = '0';
		}
		
		return t('sys_cron')->update($data, $cron['cronid']);
	}
	
	private static function _make_week($curweek){
		$week_list = array(0,1,2,3,4,5,6,7,1,2,3,4,5,6,7);
		return array_slice($week_list, $curweek, 7);
	}
	
}