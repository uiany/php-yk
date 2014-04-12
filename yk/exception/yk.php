<?php

/**
 *      [568089266@qq.com] (C)2014-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: yk.php 2014-1-8$
 */
 
namespace yk\exception;

class yk extends base{

	private $_code = 700;

	public function __construct($msg){
		parent::__construct($msg, $this->_code);
	}
}