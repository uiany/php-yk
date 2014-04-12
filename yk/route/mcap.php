<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: static.php 2013-10-27$
 */

namespace yk\route;

class mcap implements \yk\IRoute{
	
	private $_modules;
	
	public function __construct(){
		$this->_modules = \yk\yk::app()->getDispatcher()->getModules();
	}
	
	public function route(\yk\request\base $request){
		$request_uri = $request->getRequestUri();
		$matches = explode('/', $request_uri);
		
		$count = count($matches);
		if (isset($this->_modules[$matches[0]]) && $count >= 3) {
			$request->setModuleName(array_shift($matches));
			$count--;
		}
		
		if ($count >= 2) {
			$request->setControllerName($matches[0]);
			$request->setActionName($matches[1]);
			$request->setParams(strbackslant(array_slice($matches, 2), true));
			return true;
		}else{
			return false;
		}	
	}
}