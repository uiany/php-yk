<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table.php 2013-8-4$
 */

namespace yk;

abstract class table{
	
	private $_allowmem = false;
	
	private $_server_id = 1;
	private $_table;
	private $_pk;
	
	protected $_pre_cache_key;
	protected $_cache_ttl = -1;
	
	protected function __construct(){
		
		if ($this->_cache_ttl > -1 && memory('check')) {
			if (!$this->_pre_cache_key) {
				$this->_pre_cache_key = $this->_table;
			}
			$this->_allowmem = true;
		}
	}
	
	private function _pk_combine($val){
		$ret = array_combine($this->_pk, (array)$val);
		if ($ret !== false) {
			return $ret;
		}else{
			throw new exception\db('Table '.$this->_table.' val unequal primary key nums!');
		}
	}
	
	public function getServerId(){
		return $this->_server_id;
	}
	
	protected function setServerId($id){
		$this->_server_id = $id;
	}
	
	public function getTable(){
		return $this->_table;
	}
	
	protected function setTable($name){
		$this->_table = $name;
	}
	
	protected function setPk($pk){
		$this->_pk = is_array($pk) ? $pk : array($pk);
	}
	
	public function checkPk($val){
		if (empty($this->_pk)) {
			throw new exception\db('Table '.$this->_table.' has not primary key defined !');
		}
		if ($val && count($this->_pk) != count($val)) {
			throw new exception\db('Table '.$this->_table.' val unequal primary key nums!');
		}
	}
	
	public function count(){
		return db::result_row_firstfield('SELECT count(*) FROM %t', array($this->_table), $this->_server_id);
	}
	
	//单条更新，$val与$pk对应
	final public function update($data, $val, $low_priority = false, $silent = false){
		if (!empty($val) && !empty($data)) {
			$this->checkPk($val);
			$ret = db::update($this->_table, $data, $this->_pk_combine($val), $low_priority, $silent, $this->_server_id);
			$ret && $this->update_cache($val, $data);
			return $ret;
		}else{
			return false;
		}
	}
	
	//$val与$pk对应
	final public function delete($val) {
		if(isset($val)) {
			$this->checkPk($val);
			$ret = db::delete($this->_table, $this->_pk_combine($val), null, false, $this->_server_id);
			$ret && $this->delete_cache($val);
			return $ret;
		}
		return false;
	}
	
	final public function insert($data, $return_insert_id = false, $replace = false, $silent = false) {
		return db::insert($this->_table, $data, $return_insert_id, $replace, $silent, $this->_server_id);
	}
	
	//$val与$pk对应
	final public function fetch($val, $force_from_db = false){
		if (!empty($val)) {
			$this->checkPk($val);
			if ($force_from_db || ($data = $this->fetch_cache($val)) === false) {
				$data = db::fetch_first('SELECT * FROM %t WHERE '.
						db::implode($this->_pk_combine($val), 'AND'), array($this->_table), $this->_server_id);
				if(!empty($data)){ 
					$this->store_cache($val, $data);
				}
			}
			return $data;
		}
		return array();
	}
	
	public function all(){
		$data = db::fetch_all('SELECT * FROM %t WHERE 1 LIMIT 500', array($this->_table), null,  $this->_server_id);
		return $data;
	}
	
	public function range($pos, $num, &$page, $order = 'DESC'){
		$data = db::fetch_all('SELECT SQL_CALC_FOUND_ROWS * FROM %t order by %i %i LIMIT %d,%d', array($this->_table, $this->_pk[0], $order, $pos, $num), null, $this->_server_id);
		$page = ceil(db::get_found_rows($this->_server_id) / $num);
		return $data;
	}
	
	//是否存在行记录
	public function exist(array $where_args){
		if(!empty($where_args)){
			$this->checkPk(null);
			$row = db::fetch_first('SELECT '.$this->_pk[0].' FROM %t WHERE '.db::implode($where_args, 'AND').' LIMIT 1', array($this->_table), $this->_server_id);
			return !empty($row);
		}else{
			throw new exception\db('Check exist must has where args');
		}
	}
	
	public function fetch_fields(){
		$query = db::query("SHOW FIELDS FROM %t", array($this->_table), false, $this->_server_id);
		if($query) {
			$data = array();
			while(false !== ($row = db::fetch($query))) {
				$data[$row['Field']] = $row;
			}
			return $data;
		}
		return array();
	}
	
	public function optimize() {
		return db::query("OPTIMIZE TABLE %t", array($this->_table), false, $this->_server_id);
	}
	
	public function truncate() {
		return db::query("TRUNCATE %t", array($this->_table), false, $this->_server_id);
	}
	
	protected function key_cache($key){
		return !is_array($key) ? $key : implode('-', $key);
	}
	
	protected function fetch_cache($key, $pre_cache_key = '') {
		$data = false;
		if($this->_allowmem) {
			$pre_cache_key = $this->_pre_cache_key.$pre_cache_key;
			$data = memory(memory::GET, $this->key_cache($key), $pre_cache_key);
		}
		return $data;
	}
	
	protected function store_cache($key, $data, $cache_ttl = 0, $pre_cache_key = '') {
		$ret = false;
		if($this->_allowmem) {
			$pre_cache_key = $this->_pre_cache_key.$pre_cache_key;
			$cache_ttl = $cache_ttl ? $cache_ttl : $this->_cache_ttl;
			$ret = memory(memory::SET, $this->key_cache($key), $data, $cache_ttl, $pre_cache_key);
		}
		return $ret;
	}
	
	protected function delete_cache($key, $pre_cache_key = null) {
		$ret = false;
		if($this->_allowmem) {
			$pre_cache_key = $this->_pre_cache_key.$pre_cache_key;
			$ret = memory(memory::FLUSH, $this->key_cache($key), $pre_cache_key);
		}
		return $ret;
	}
	
	protected function update_cache($key, $data, $cache_ttl = null, $pre_cache_key = null) {
		$ret = false;
		if($this->_allowmem) {
			$pre_cache_key = $this->_pre_cache_key.$pre_cache_key;
			$cache_ttl = $cache_ttl ? $cache_ttl : $this->_cache_ttl;
			$key = $this->key_cache($key);
			$ret = memory(memory::SET, $this->key_cache($key), $data, $cache_ttl, $pre_cache_key);
		}
		return $ret;
	}
}