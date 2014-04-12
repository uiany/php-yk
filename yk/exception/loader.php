<?php

/**
 *      [568089266@qq.com] (C)2014-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: loader.php 2014-1-7$
 */
 
namespace yk\exception;

class loader extends base{

	private $_code = 730;

	public function __construct($file){
		$file = str_replace(MAIN_ROOT, '', $file);
		parent::__construct('Loader failed '.$file, $this->_code);
	}
}