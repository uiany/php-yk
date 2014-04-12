<?php

/**
 *      [568089266@qq.com] (C)2014-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: xss.php 2014-1-15$
 *      
 *      防xss攻击
 *      
 *      hashcode方式需要建立表
 *      CREATE TABLE `xss_hash` (
		   `uid` int(10) unsigned NOT NULL,
		   `xhash` char(8) NOT NULL,
		   PRIMARY KEY (`uid`)
		 ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
 */
 
namespace yk\helper;

class xss{
	
	public static function ckRequestUri(){
		if (!empty($_SERVER['QUERY_STRING'])) {
			$requestUri = urldecode(urldecode($_SERVER['QUERY_STRING']));
			if (strpos($requestUri, '<script') !== false || strpos($requestUri, '<iframe') !== false) {
				return false;
			}
		}
		return true;
	}
	
	public static function setXhash(){
		$xhash = ygetcookie('xhash');
		list($hash, $uid, $timestamp) = $xhash ? explode("\t", auth::decode($xhash)) : array(null, null, null);
		if (!$hash || !$uid || !$timestamp || $uid != g('uid') || $timestamp < TIMESTAMP) {
			$hash = yrandom(8);
			$timestamp = TIMESTAMP + 1800;
			$uid = g('uid');
			$xhash = auth::encode("$hash\t$uid\t$timestamp");
			ysetcookie('xhash', $xhash);
			self::_createXhash($uid, $hash);
		}
		return urlencode($xhash);
	}
	
	public static function xhashIsPass(){
		$xhash = urldecode($_POST['xhash']);
		if ($xhash) {
			list($hash, $uid, $timestamp) = explode("\t", auth::decode(urldecode($xhash)));
			if ($hash && $uid == g('uid') && $timestamp > TIMESTAMP) {
				$xhash_row = t('xss_hash')->fetch(g('uid'));
				if ($xhash_row['xhash'] == $hash) {
					return true;
				}
			}
		}
		return false;
	}
	
	private static function _createXhash($uid, $hash){
		t('xss_hash')->insert(array('uid' => $uid, 'xhash' => $hash), false, true);
	} 
}