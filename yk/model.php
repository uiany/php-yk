<?php

/**
 *      [568089266@qq.com] (C)2014-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: model.php 2014-3-17$
 */

namespace yk;

class model{
	
	private $_tablename;
	private $_server_id = 1;
	private $_fileds = '*';
	private $_oby;
	private $_order;
	private $_data;
	private $_where = false;
	private $_limit = '1';
	
	/**
	 * init tablename
	 * @param string $name
	 * @return \yk\model
	 */
	public function __construct($name){
		$this->_tablename = $name;
		return $this;
	}
	
	/**
	 * 初始化数据
	 */
	private function _init(){
		$this->_fileds = '*';
		$this->_oby = $this->_order = null;
		$this->_data = null;
		$this->_where = false;
		$this->_limit = '1';
	}
	
	/**
	 * @param string $type
	 * @param bool $silent 出错时不提示
	 * @throws exception\db
	 */
	private function _query($type, $silent = false){
		list($ttype) = explode(' ', trim($type));
		if ($ttype == 'select') {
			$sql = "$type $this->_fileds from %t where $this->_where ".($this->_oby ? "Order by $this->_oby $this->order " : '')." LIMIT $this->_limit";
		}elseif ($ttype == 'insert' || $ttype == 'replace'){
			$sql = "$type into %t set $this->_data";
		}elseif ($ttype == 'update'){
			$sql = "$type %t set $this->_data where $this->_where";
		}elseif ($ttype == 'delete'){
			$sql = "delete from %t where $this->_where";
		}else{
			throw new exception\db('The Model of query type is undefind');
		}
		$this->_init();
		return db::query($sql, array($this->_tablename), $silent, $this->_server_id);
	}
	
	/**
	 * init service No
	 * @param int $id
	 * @return \yk\model
	 */
	public function setServerId($id){
		$this->_server_id = $id;
		return $this;
	}
	
	/**
	 * init limit
	 * @param uint $pos
	 * @param uint $num
	 * @return \yk\model
	 */
	public function setLimit($pos, $num){
		$this->_limit = "$pos, $num";
		return $this;
	}
	
	/**
	 * init order by
	 * @param mixed $oby
	 * @param string $order
	 * @return \yk\model
	 */
	public function setSort($oby, $order = 'DESC'){
		$this->_oby = is_array($order) ? implode(',', $oby) : $oby;
		$this->_order = $order;
		return $this;
	}
	
	/**
	 * init where
	 * @param mixed $data
	 * @return \yk\model
	 */
	public function setWhere($data){
		$this->_where = is_array($data) ? \yk\db::implode($data, 'AND') : $data;
		return $this;
	}
	
	/**
	 * 获取一条数据
	 * @param string $fields
	 * @return array
	 */
	public function fetch($fields = '*'){
		$this->_fileds = '*';
		$result = $this->_query('select');
		$row = db::fetch($result);
		db::result_free($result);
		return $row;
	}
	
	/**
	 * 获取全部数据
	 * @param int $total	用于接收$page的值
	 * @param mixed $fields	查询的字段
	 * @param string $before 放在select后面的字符
	 * @return array
	 */
	public function all(&$total = null, $fields = '*', $before = ' SQL_CALC_FOUND_ROWS'){
		$this->_fileds = !is_array($fields) ? $fields : implode(',', $fields);
		$result = $this->_query('select'.$before);
		is_null($total) or $total = db::get_found_rows($this->_server_id);
		$rows = array();
		while (null !== ($row = db::fetch($result))){
			$rows[] = $row;
		}
		db::result_free($result);
		return $rows;
	}
	
	/**
	 * 插入数据
	 * @param mixed $data
	 * @param bool $insert_id	返回插入后的自增ID
	 * @param bool $replace		替换式插入
	 * @param bool $silent		出错时不提示
	 * @return bool || int		视条件返回
	 */
	public function insert($data, $insert_id = false, $replace = false, $silent = false){
		$this->_data = is_array($data) ? db::implode($data) : $data;
		$result = $this->_query(!$replace ? 'insert' : 'replace', $silent);
		return $result ? ($insert_id ? db::insert_id($this->_server_id) : true) : false; 
	}
	
	/**
	 * 更新数据
	 * @param mixed $data
	 * @param bool $low_priority	低优先级
	 * @param bool $silent	出错时不提示
	 * @return bool | int 返回值可是等于false的0值，请用 === 判断
	 */
	public function update($data, $low_priority = false, $silent = false){
		$this->_data = is_array($data) ? db::implode($data) : $data;
		$result = $this->_query('update'.(!$low_priority ? '' : ' LOW_PRIORITY'), $silent);
		return $result ? db::affected_rows($this->_server_id) : false;
	}
	
	/**
	 * 删除数据
	 * @param bool $silent	出错时不提示
	 * @return boolean | int 返回值可是等于false的0值，请用 === 判断
	 */
	public function delete($silent = false){
		$result = $this->_query('delete', $silent);
		return $result ? db::affected_rows($this->_server_id) : false;
	}
}