<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: mysqli.php 2013-7-23$
 */

namespace yk\db;

class mysqli implements \yk\IDatabase{
	
	private $config;
	private $serverid;
	private $links;
	private $sqls;
	private $sqlnum;
	private $trans;
	
	public function __construct($config){
		$this->config = $config;
		$this->sqlnum = 0;
		$this->sqls = array();
		$this->trans = array();
	}
	
	public function __destruct(){
		$this->trsa_rollback(null);
	}
	
	public function debug(){
		return $this->sqls;
	}
	
	public function connect($serverid = 1){
		$conf = $this->config[$serverid];
		if (empty($conf)) {
			$this->halt('DB conf empty, the server id NO:'.$serverid);
		}
		
		$mysqli = new \mysqli($conf['host'], $conf['user'], $conf['password'], $conf['dbname'], $conf['port']);
		
		if ($mysqli->connect_error) {
			\yk\debug\log::runlog('database', '['.$mysqli->connect_errno.']Connect error : '.$mysqli->connect_error);
			exit("database No $serverid can't connect");
		}
		
		$charset = $conf['charset'] ? $conf['charset'] : $this->config[1]['charset'];
		$mysqli->set_charset($charset);
		
		$this->links[$serverid] = $mysqli;
	}
	
	public function addpre($name, $serverid = 1){
		return $this->config[$serverid]['tableprefix'].$name;
	}
	
	public function chsrvid($serverid){
		if (!$this->links[$serverid]) {
			$this->connect($serverid);
		}
	}
	
	public function query($query, $silent = false, $serverid = 1){
		$this->chsrvid($serverid);
		$result = $this->links[$serverid]->query($query);
		if ($result === false) {
			if (in_array($this->errno($serverid), array(2006, 2013)) && substr($silent, 0, 5) != 'RETRY') {
				$this->connect($serverid);
				return $this->query($query, 'RETRY'.$silent, $serverid);
			}
			if (!$silent) {
				$this->halt($this->errstr($serverid)."[SQL:$query]", $this->errno($serverid));
			}
		}
		if (MAIN_DEBUG) {
			$this->sqls[$this->sqlnum++] = $query;
		}
		return $result;
	}
	
	public function result_free($result){
		$result->free();
	}
	
	public function result_row($result, $pos){
		return $result->data_seek($pos) ? $result->fetch_array(MYSQLI_NUM) : array();
	}
	
	public function result_first_field($result, $pos){
		$row = $this->result_row($result, $pos);
		return isset($row[0]) ? $row[0] : null;
	}
	
	public function insert_id($serverid = 1){
		return $this->links[$serverid]->insert_id;
	}
	
	public function affected_rows($serverid = 1){
		return $this->links[$serverid]->affected_rows;
	}
	
	public function num_rows($result){
		return $result->num_rows;
	}
	
	public function fetch($result){
		return $result->fetch_array(MYSQLI_ASSOC);
	}
	
	public function fetch_all($result){
		if (method_exists($result, 'fetch_all')) {
			return $result->fetch_all(MYSQLI_ASSOC);
		}else{
			$rows = array();
			while (null !== ($row = $result->fetch_array(MYSQLI_ASSOC)) ){
				$rows[] = $row;
			}
			return $rows;
		}
	}
	
	public function found_rows($serverid = 1){
		return $this->result_first_field($this->query('SELECT FOUND_ROWS()', false, $serverid), 0);
	}
	
	public function trsa_start($serverid = 1){
		if (!isset($this->trans[$serverid])) {
			$this->trans[] = $serverid;
			$this->links[$serverid]->autocommit(false);
		}
	}
	
	public function trsa_commit($serverid = null){
		if (is_null($serverid)) {
			foreach ($this->trans as $id){
				$this->links[$id]->commit();
				$this->links[$id]->autocommit(true);
			}
		}else{
			$this->links[$serverid]->commit();
			$this->links[$serverid]->autocommit(true);
		}
	}
	
	public function trsa_rollback($serverid = null){
		if (is_null($serverid)) {
			foreach ($this->trans as $id){
				$this->links[$id]->rollback();
				$this->links[$id]->autocommit(true);
			}
		}else{
			$this->links[$serverid]->rollback();
			$this->links[$serverid]->autocommit(true);
		}
	}
	
	public function errstr($serverid = 1){
		return $this->links[$serverid]->error;
	}
	
	public function errno($serverid = 1){
		return $this->links[$serverid]->errno;
	}
	
	private function halt($msg, $code = 0){
		throw new \yk\exception\db('['.$code.']'.$msg);
	} 
}