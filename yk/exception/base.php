<?php

/**
 *      [568089266@qq.com] (C)2014-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: base.php 2014-1-8$
 *      
 *      //YK将保留700-799的错误代码
 *      YKERR_STARTUP = 700;
 *      YKERR_ROUTER = 710;
 *      YKERR_DISPATCH = 720;
 *      YKERR_DISPATCH_M = 721;
 *      YKERR_DISPATCH_C = 722;
 *      YKERR_DISPATCH_A = 723;
 *      YKERR_LOADER = 730;
 *      YKERR_VIEW = 740;
 *      YKERR_DB = 750;
 *      YKERR_INPUT = 760;
 *      YKERR_HELPER = 770;
 *      
 */
 
namespace yk\exception;

class base extends \Exception{

	public function __construct($message = null, $code = null, $previous = null){
		parent::__construct($message, $code, $previous);
	}
}