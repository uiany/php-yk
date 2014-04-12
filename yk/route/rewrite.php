<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: rewrite.php 2013-10-27$
 *      
 *      定义一段路径，路径的某个名称可用正则式指令要求,以及动态路径
 *      module/contr/action\w+
 *      mobule/contr/action/:name
 */

namespace yk\route;

class rewrite implements \yk\IRoute{
	
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
					if (strpos($rule_arr[$i], ':') === 0) {
						$rule_str = substr($rule_arr[$i], 1);
						if (($pos = strpos($rule_str, '\\')) !== false) {	//指令了正则表达式
							$name_regex = substr($rule_str, $pos);
							$rule_str = substr($rule_str, 0, $pos);
							if (!preg_match("/^$name_regex$/", $uri_arr[$i])) {
								return false;
							}
						}
						$request->setParam($rule_str, $uri_arr[$i]);
					}else{
						if (strcasecmp($rule_arr[$i], $uri_arr[$i]) != 0) {
							return false;
						}
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