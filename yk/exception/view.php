<?php

/**
 *      [568089266@qq.com] (C)2014-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: view.php 2014-1-8$
 */
 
namespace yk\exception;

class view extends base{

	private $_code = 740;

	public function __construct($msg = null){
		parent::__construct('View failed '.$msg, $this->_code);
	}
}