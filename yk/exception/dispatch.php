<?php

/**
 *      [568089266@qq.com] (C)2014-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: dispatch.php 2014-1-7$
 */
 
namespace yk\exception;

class dispatch extends base{
	
	private $_code = 720;
	
	public function __construct(){
		parent::__construct('Dispatch failed', $this->_code);
	}
}