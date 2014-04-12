<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: simple.php 2013-10-27$
 *      
 *      定义一段字符串路径用来指定一个mca
 *      login   指向   m=logging, c=login, a=do
 */

namespace yk\route;

class simple implements \yk\IRoute{
	
	private $_route;
	private $_module;
	private $_controller;
	private $_action;
	
	public function __construct($rmca){
		foreach (array('route', 'module', 'controller', 'action') as $v){
			if (isset($rmca[$v])) {
				$var = "_$v";
				$this->$var = $rmca[$v];
			}
		}
	}
	
	public function route(\yk\request\base $request){
		$rule_count = substr_count($this->_route, '/') + 1;
		$uri_count = substr_count($request->getRequestUri(), '/') + 1;
		
		if ($uri_count >= $rule_count) {
			$uri_arr = explode('/', $request->getRequestUri());
			$rule_arr = explode('/', $this->_route);
			for ($i=0; $i<$uri_count; $i++){
				if ($i < $rule_count) {
					if (strcasecmp($rule_arr[$i], $uri_arr[$i]) != 0) {
						return false;
					}
				}else{
					//剩余的部分一并处理，并break;
					$last_uri = array_slice($uri_arr, $i);
					$request->setParams(strbackslant($last_uri, true));
					break;
				}
			}
			
			$this->_module && $request->setModuleName($this->_module);
			$this->_controller && $request->setControllerName($this->_controller);
			$this->_action && $request->setActionName($this->_action);
			
			return true;
		}else{
			return false;
		}
	}
}