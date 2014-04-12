<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: http.php 2013-11-24$
 */

namespace yk\request;

class http extends base{
	
	public function __construct(){
		
		if (!$this->isCli()) {
			$base_uri = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
			$siteurl = htmlspecialchars(($this->_check_https() ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].substr($base_uri, 1));
			
			$this->setBaseUri($base_uri);
			$this->setRequestUri($_SERVER['REQUEST_URI']);
			
			g('inajax', isset($_REQUEST['inajax']), true);
			g('siteurl', $siteurl);
			
			$this->setMethod(strtolower($_SERVER['REQUEST_METHOD']));
		}else{
			$this->cli_init();
		}
	}
	
	public function cli_init(){
		$argv = $_SERVER['argv'];
		if ($argv[1] == '-h') {
			echo "Usage: php ".basename(__FILE__)." [args]\n\n",
				 " request_uri=M/C/A\tdefined the reuqest_uri string.\n",
				 " request_uri=C/A\tdefined the reuqest_uri string.\n",
				 " key=value\t\tdefined some params name and value.\n";
			exit;
		}else{
			for($i=1; $i<$_SERVER['argc']; $i++){
				list($k, $v) = explode('=', $argv[$i]);
				if ($k == 'request_uri') {
					$this->setRequestUri($v);
				}else{
					if (preg_match('/^\w+$/', $k)){
						$v = strip_tags($v);
						$_GET[$k] = $v;
						$this->setParam($k, $v);
					}
				}
			}
		}
	}
	
	private function _check_https(){
		if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
			return true;
		}
		if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1)) {
			return true;
		}
		return false;
	}
	
	//request_uri是以反斜线开头的
	public function setRequestUri($request_uri){
		$request_uri = substr($request_uri, strlen($this->_base_uri));
		if (($pos = strpos($request_uri, '?')) !== false) {
			$request_uri = substr($request_uri, 0, $pos);
		}
		$this->_request_uri = str_replace('//', '/', trim($request_uri, '/'));
	}
	
	//方便route的操作，返回uri已经过前后去反斜线，多个连续的反斜线合一
	public function getRequestUri(){
		return $this->_request_uri;
	}
	
	//$base_uri是以反斜线开头的
	public function setBaseUri($base_uri){
		$this->_base_uri = $base_uri;
		$this->setRequestUri($_SERVER['REQUEST_URI']);
	}
	
	public function getBaseUri(){
		return $this->_base_uri;
	}
}