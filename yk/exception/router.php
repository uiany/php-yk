<?php

/**
 *      [568089266@qq.com] (C)2014-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: router.php 2014-1-7$
 */
 
namespace yk\exception;

class router extends base{
	
	private $_code = 710;
	
	public function __construct(){
		parent::__construct('Route failed', $this->_code);
	}
}