<?php

/**
 *      [568089266@qq.com] (C)2014-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: idatabase.php 2014-3-15$
 */

namespace yk;

interface IDatabase{
	
	public function __construct($config);
	
	public function connect($serverid);
	
	public function addpre($tablename, $serverid);
	
	public function chsrvid($serverid);
	
	public function query($query, $silent, $serverid);
	
	public function result_free($result);
	
	public function result_row($result, $pos);
	
	public function result_first_field($result, $pos);
	
	public function insert_id($serverid);
	
	public function affected_rows($serverid);
	
	public function fetch($result);
	
	public function fetch_all($result);
	
	public function found_rows($serverid);
	
	public function num_rows($result);
	
	public function trsa_start($serverid);
	
	public function trsa_commit($serverid);
	
	public function trsa_rollback($serverid);
	
	public function errstr($serverid);
	
	public function errno($serverid);
	
	public function debug();
}