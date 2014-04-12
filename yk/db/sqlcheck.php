<?php

/**
 *      [568089266@qq.com] (C)2014-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: safecheck.php 2014-1-3$
 */

namespace yk\db;

class sqlcheck{

	private static $checkcmd = array('SELECT', 'INSERT', 'UPDATE', 'DELETE');
	private static $config;

	public static function setconfigstatus($data) {
		self::$config['status'] = $data ? 1 : 0;
	}

	public static function checksql($sql){
		if (self::$config === null) {
			self::$config = g('config/security/querysafe');
		}
		if (self::$config['status']) {
			$cmd = trim(strtoupper(substr($sql, 0, strpos($sql, ' '))));
			if (in_array($cmd, self::$checkcmd)) {
				$test = self::_do_query_safe($sql);
				if ($test < 1) {
					throw new \yk\exception\db('It is not safe to do this query: ('.$sql.')');
				}
			}
		}
		return true;

	}

	private static function _do_query_safe($sql){
		$sql = str_replace(array('\\\\', '\\\'', '\\"', '\'\''), '', $sql);
		$mark = $clean = '';
		if (strpos($sql, '/') === false && strpos($sql, '#') === false && strpos($sql, '-- ') === false) {
			$clean = preg_replace("/'(.+?)'/s", '', $sql);
		} else {
			$len = strlen($sql);
			$mark = $clean = '';
			for ($i = 0; $i < $len; $i++) {
				$str = $sql[$i];
				switch ($str) {
					case '\'':
						if (!$mark) {
							$mark = '\'';
							$clean .= $str;
						} elseif ($mark == '\'') {
							$mark = '';
						}
						break;
					case '/':
						if (empty($mark) && $sql[$i + 1] == '*') {
							$mark = '/*';
							$clean .= $mark;
							$i++;
						} elseif ($mark == '/*' && $sql[$i - 1] == '*') {
							$mark = '';
							$clean .= '*';
						}
						break;
					case '#':
						if (empty($mark)) {
							$mark = $str;
							$clean .= $str;
						}
						break;
					case "\n":
						if ($mark == '#' || $mark == '--') {
							$mark = '';
						}
						break;
					case '-':
						if (empty($mark) && substr($sql, $i, 3) == '-- ') {
							$mark = '-- ';
							$clean .= $mark;
						}
						break;

					default:

						break;
				}
				$clean .= $mark ? '' : $str;
			}
		}

		$clean = preg_replace('/[^a-z0-9_\-\(\)#\*\/\"]+/is', '', strtolower($clean));

		if (self::$config['afullnote']) {
			$clean = str_replace('/**/', '', $clean);
		}

		if (is_array(self::$config['dfunction'])) {
			foreach (self::$config['dfunction'] as $fun) {
				if (strpos($clean, $fun . '(') !== false)
					return -1;
			}
		}

		if (is_array(self::$config['daction'])) {
			foreach (self::$config['daction'] as $action) {
				if (strpos($clean, $action) !== false)
					return -3;
			}
		}

		if (self::$config['dlikehex'] && strpos($clean, 'like0x')) {
			return -2;
		}

		if (is_array(self::$config['dnote'])) {
			foreach (self::$config['dnote'] as $note) {
				if (strpos($clean, $note) !== false)
					return -4;
			}
		}

		return 1;
	}
}