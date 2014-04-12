<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: get.php 2013-10-27$
 *      
 *      定义m, c, a的名字，此路由会自动从GET中获取三者的值
 */

namespace yk\route;

class get implements \yk\IRoute{
	
	private $_mca;
	
	public function __construct(array $mca){
		$this->_mca = $mca;
	}
	
	public function route(\yk\request\base $request){
		
		foreach ($this->_mca as $k => $v){
			switch ($k){
				case 'module':
					if (isset($_GET[$v])){
						$request->setModuleName($_GET[$v]);
					}else{
						return false;
					}
					break;
				case 'controller':
					if (isset($_GET[$v])){
						$request->setControllerName($_GET[$v]);
					}else{
						return false;
					}
					break;
				case 'action':
					if (isset($_GET[$v])){
						$request->setActionName($_GET[$v]);
					}else{
						return false;
					}
					break;
				default:
					break;
			}	
		}
		
		parse_str($_SERVER['QUERY_STRING'], $params);
		if (!empty($params)) {
			$request->setParams($params);
		}
		
		return true;
	}
}