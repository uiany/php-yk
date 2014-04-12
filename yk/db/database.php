<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: database.php 2013-7-24$
 */

namespace yk\db;

class database{
	
	/**
	 * @var \yk\IDatabase
	 */
	private static $_db;
	
	/**
	 *
	 * @throws \yk\exception\db
	 * @return \yk\IDatabase
	 */
	public static function getInstance(){
		if (!self::$_db) {
			throw new \yk\exception\db('Database is not initialized');
		}
		return self::$_db;
	}
	
	public static function init($config, $type = '\yk\db\mysqli'){
		if (!self::$_db) {
			if (!(self::$_db instanceof $type)) {
				self::$_db = new $type($config);
			}
			self::$_db->connect();
		}
	}
	
	public static function chsrvid($serverid){
		self::$_db->chsrvid($serverid);
	}
	
	public static function addpre($name, $serverid = 1){
		return self::$_db->addpre($name, $serverid);
	}
	
	public static function transaction_start($serverid = 1){
		self::$_db->trsa_start($serverid);
	}
	
	public static function transaction_commit($serverid = null){
		self::$_db->trsa_commit($serverid);
	}
	
	public static function transaction_rollback($serverid = null){
		self::$_db->trsa_rollback($serverid);
	}
	
	public static function found_rows($serverid = 1){
		return self::$_db->found_rows($serverid);
	}
	
	public static function insert($tablename, $data, $return_insert_id = false, $replace_insert = false, $silent = false, $serverid = 1){
		$sql = ($replace_insert ? 'REPLACE' : 'INSERT').' into '.self::addpre($tablename, $serverid).' set '.self::implode($data, ',');
		$ret = self::query($sql, null, $silent, $serverid);
		return $ret ? ($return_insert_id ? self::insert_id($serverid) : self::affected_rows($serverid)) : false;
	}
	
	public static function update($tablename, $data, $condition = '1', $low_priority = false, $silent = false, $serverid = 1){
		$sql = 'UPDATE '.(!$low_priority ? '' : 'LOW_PRIORITY ').self::addpre($tablename, $serverid).' set '.self::implode($data, ',').' where '.(is_string($condition) ? $condition : self::implode($condition, 'and'));
		$ret = self::query($sql, null, $silent, $serverid);
		return $ret ? self::affected_rows($serverid) : false;
	}
	
	public static function delete($tablename, $condition, $limit = 0, $silent = false, $serverid = 1){
		if (!empty($condition)){
			$sql = 'DELETE from '.self::addpre($tablename, $serverid).' where '.(is_string($condition) ? $condition : self::implode($condition, 'AND')).($limit ? " limit $limit" : '');
			$ret = self::query($sql, null, $silent, $serverid);
			return $ret ? self::affected_rows($serverid) : false;
		}else{
			throw new \yk\exception\db('delete table condition can\'t for empty');
		}
		
	}
	
	public static function fetch_first($sql, $args = null, $serverid = 1){
		stripos($sql, ' limit') !== false or $sql .= ' LIMIT 0,1';
		$result = self::query($sql, $args, false, $serverid);
		if ($result) {
			$row = self::fetch($result);
			self::result_free($result);
			return empty($row) ? array() : $row;
		}else{
			return array();
		}
	}
	
	public static function fetch_all($sql, $args = null, $keyfield = null, $serverid = 1){
		$result = self::query($sql, $args, false, $serverid);
		if ($result) {
			if ($keyfield) {
				$rows = array();
				while (null != ($row = self::fetch($result))){
					$rows[$row[$keyfield]] = $row;
				}
			}else{
				$rows = self::$_db->fetch_all($result);
			}
			self::result_free($result);
			return $rows;
		}else{
			return array();
		}
	}
	
	//SQL_CALC_FOUND_ROWS
	public static function get_found_rows($serverid = 1){
		return self::$_db->found_rows($serverid);
	}
	
	public static function result_row($sql, $args = null, $rowno = 0, $serverid = 1){
		$result = self::query($sql, $args, false, $serverid);
		$row = self::$_db->result_row($result, $rowno);
		self::result_free($result);
		return $row;
	}
	
	public static function result_row_firstfield($sql, $args = null, $serverid = 1){
		$result = self::query($sql, $args, false, $serverid);
		$value = self::$_db->result_first_field($result, 0);
		self::result_free($result);
		return $value;
	}
	
	public static function query($sql, $args = null, $silent = false, $serverid = 1){
		if (!empty($args)) {
			$sql = self::format($sql, $args, $serverid);
		}
		sqlcheck::checksql($sql);
		return self::$_db->query($sql, $silent, $serverid);
	}
	
	public static function fetch($result){
		return self::$_db->fetch($result);
	}
	
	public static function result_free($result){
		return self::$_db->result_free($result);
	}
	
	//Returns the number of rows affected by the last INSERT, UPDATE, REPLACE or DELETE query
	public static function affected_rows($serverid = 1){
		return self::$_db->affected_rows($serverid);	
	}
	
	//Returns the number of rows in the result set, like select etc query
	public static function num_rows($result){
		return self::$_db->num_rows($result);
	}
	
	public static function insert_id($serverid = 1){
		return self::$_db->insert_id($serverid);
	}
	
	public static function limit($limit, $start = 0) {
		if ($start) {
			return " LIMIT $start $limit";
		}else{
			return " LIMIT $limit"; 
		}
	}
	
	public static function order($oby, $sort = 'ASC'){
		return " order by $oby $sort";
	}
	
	public static function format($sql, $args, $serverid = 1){
		$newsql = strtok($sql, '%');
		$pos = 0;
		while (($tok = strtok('%')) !== false && array_key_exists($pos, $args)){
			switch (substr($tok, 0, 1)){
				case 't':
					$replaces = '`'.self::addpre($args[$pos], $serverid).'`';
					break;
				case 'd':
					$replaces = intval($args[$pos]);
					break;
				case 's':
					$replaces = self::quote(is_array($args[$pos]) ? serialize($args[$pos]) : $args[$pos]);
					break;
				case 'f':
					$replaces = sprintf('%f', $args[$pos]);
					break;
				case 'i':
					$replaces = $args[$pos];
					break;
				case 'n':
					$replaces = is_array($args[$pos]) ? implode(',', self::quote($args[$pos])) : self::quote($args[$pos]);
					break;
				default:
					throw new \yk\exception\db('Sql format not support![SQL:'.$sql.']');
					break;
			}
			$newsql .= $replaces.substr($tok, 1);
			$pos++;
		}
		return $newsql;
	}
	
	public static function quote($str, $noarray = false){
		if (is_string($str)){
			return '\''.addcslashes($str, "\n\r\\'\"\032").'\'';
		}elseif (is_int($str) || is_float($str)){
			return '\''.$str.'\'';
		}elseif(is_bool($str)){
			return $str ? '1' : '0';
		}elseif (is_array($str)){
			if($noarray === false) {
				foreach ($str as &$v) {
					$v = self::quote($v, true);
				}
				return $str;
			} else {
				return '\'\'';
			}
		}else{
			return '\'\'';
		}
	}
	
	public static function quote_fields($fields){
		$t = null;
		if (strpos($fields, '.') !== false) {
			list($t, $fields) = explode('.', $fields);
		}
		$fields = trim($fields, '`');
		return (!$t ? '' : "$t.").$fields;
	}
	
	public static function implode($data, $glue = ','){
		$glue = ' '.trim($glue).' ';
		$sql = $comma = '';
		foreach ($data as $k => $v){
			$sql .= $comma.self::quote_fields($k).'='.self::quote($v);
			$comma = $glue;
		}
		return $sql;
	}
	
	public static function field($fields, $val, $glue = '='){
		$fields = self::quote_fields($fields);
	
		if (is_array($val)){
			$glue = $glue == 'notin' ? $glue : 'in';
		}else{
			$glue = in_array($glue, array('notin', 'in')) ? '=' : $glue;
		}
	
		switch ($glue){
			case '=':
				return $fields.$glue.self::quote($val); break;
			case '-':
			case '+':
				return $fields = $fields.$glue.self::quote((array)$val); break;
			case '>':
			case '<':
			case '<>':
			case '>=':
			case '<=':
				return $fields.$glue.self::quote($val); break;
			case '|':
			case '&':
			case '^':
				return $fields.$glue.self::quote((int)$val); break;
			case 'in':
			case 'notin':
				$val = $val ? implode(',', self::quote($val)) : '\'\'';
				return $fields . ($glue == 'notin' ? ' NOT' : '') . ' IN (' . $val . ')'; break;
			case 'like':
				return $fields . ' LIKE (' . self::quote("%$val%") . ')'; break;
			case 'llike':
				return $fields . ' LIKE (' . self::quote('%'.$val) . ')'; break;
			case 'rlike':
				return $fields . ' LIKE (' . self::quote($val.'%') . ')'; break;
			default:
				throw new \yk\exception\db('Not allow this glue between field and value: "' . $glue . '"');
		}
	
	}
}