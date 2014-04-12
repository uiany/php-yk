<?php

/**
 *      [568089266@qq.com] (C)2014-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: helper.php 2014-1-13$
 */
 
namespace yk\exception;

class helper extends base{
	
	private $_code = 770;
	
	public function __construct($helper, $msg){
		parent::__construct("[$helper]$msg", $this->_code);
	}
}