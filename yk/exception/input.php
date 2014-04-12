<?php

/**
 *      [568089266@qq.com] (C)2014-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: input.php 2014-1-13$
 */
 
namespace yk\exception;

class input extends base{
	
	private $_code = 760;
	
	public function __construct($msg){
		parent::__construct($msg, $this->_code);
	}
}