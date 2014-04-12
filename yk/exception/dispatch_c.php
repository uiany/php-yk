<?php

/**
 *      [568089266@qq.com] (C)2014-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: dispatch_c.php 2014-1-8$
 */
 
namespace yk\exception;

class dispatch_c extends base{

	private $_code = 722;

	public function __construct(){
		parent::__construct('Dispatch Controller failed', $this->_code);
	}
}