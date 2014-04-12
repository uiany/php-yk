<?php

/**
 *      [568089266@qq.com] (C)2014-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: db.php 2014-1-10$
 */
 
namespace yk\exception;

class db extends base{

	private $_code = 750;

	public function __construct($msg = null){
		parent::__construct($msg ? $msg : 'Database error', $this->_code);
	}
}