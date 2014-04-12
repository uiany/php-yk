<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: regex.php 2013-10-27$
 *      
 *      定义一段定则式（注意斜线需要转义），然后定义m, c, a的值，以及正则小括号解析对应的map
 *      xyz/(\w+)	m=Am, c=Bc, a=Ca, map = array(1=> 'pname')
 */

namespace yk\route;

class regex implements \yk\IRoute{
	
	private $_route;
	private $_module;
	private $_controller;
	private $_action;
	private $_map;
	
	public function __construct($rmcam){
		foreach (array('route', 'module', 'controller', 'action', 'map') as $v){
			if (isset($rmcam[$v])) {
				$var = "_$v";
				$this->$var = $rmcam[$v];
			}
		}
	}
	
	public function route(\yk\request\base $request){
		$request_uri = $request->getRequestUri();
		
		if ($this->_route && preg_match("/^$this->_route/", $request_uri, $matches)) {
			
			$this->_module && $request->setModuleName($this->_module);
			$this->_controller && $request->setControllerName($this->_controller);
			$this->_action && $request->setActionName($this->_action);
			
			$request->setParams(array_shift($matches));
			
			//URI剩余部分将被分为key/value
			$request->setParams(strbackslant(trim(str_replace($matches[0], '', $request_uri), '/')));
			//指令匹配部分的别名
			if (!empty($this->_map)) {
				foreach ($this->_map as $k => $name){
					$request->setParam($name, $matches[$k]);
				}
			}
			
			return true;
		}else{
			return false;
		}
		
	}
}