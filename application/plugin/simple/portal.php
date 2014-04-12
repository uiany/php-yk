<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portal.php 2013-8-16$
 */

/*
	init方法在初始化本插件时将自动执行一次。
	插件的方法分为三大类
	1、 mca、ca形式，即 module_controller_action 或 controller_action；这类调用将发生在action执行之前
	2、_tpl_id_action,向模版中插入数据的扩展，以tpl开头；这类调用发生在模版显示过程中
	3、_yk_pluginname,yk在初始化过程调用；这类调用视情况执行；
		现以支持以下命名：
		1. yk/router/before 传递request对象
		2. yk/router/after 传递request对象
		3. 

*/

namespace application\plugin\simple;

class portal extends \yk\plugin{
	
	public function init(){
		
	}
	
	public function login(){
		
	}
	
	public function yk_router_before(){
		
	}
}