<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: controller.php 2013-11-28$
 */

namespace yk;

class controller{
	
	private $_view;
	
	/**
	 * @var \yk\request\base
	 */
	protected $request;
	
	final public function __construct(){
		$dispatcher = yk::app()->getDispatcher();
		$this->request = $dispatcher->getRequest();
		if (method_exists($this, 'init')) {
			$this->init();
		}
		$this->_view = null;
	}
	
	public function initView(){
		if (!$this->_view) {
			$this->_view = yk::app()->getDispatcher()->getView();
		}
	}
	
	/**
	 * @return \yk\IView
	 */
	public function getView(){
		$this->_view or $this->initView();
		return $this->_view;
	}
	
	public function input(array $keys, $callback = null, $args = null){
		$data = \yk\helper\input::multiGet($keys);
		if($callback !== null){
			return call_user_func($callback, $data, $args);
		}else{
			return $data;
		}
	}
	
	public function render($name){
		$this->_view or $this->initView();
		return $this->_view->render($name);
	}
	
	// [folder/]*name
	public function display($name, array $vars = array()){
		$this->_view or $this->initView();
		$this->_view->display($name, $vars);
	}
	
	public function json($code, $msg, $data = array(), array $args = array()){
		$output = array('code' => $code, 'msg' => $msg, 'data' => $data);
		empty($args) or $output = array_merge($output, $args);
		echo defined('JSON_UNESCAPED_UNICODE') ? json_encode($output, JSON_UNESCAPED_UNICODE) : json_encode($output);
	}
	
	//$action, $controller, $module
	public function forward(){
		$args = func_get_args();
		switch (func_num_args()){
			case 3:
				$this->request->setModuleName($args[2]);
				//no break
			case 2:
				$this->request->setControllerName($args[1]);
				//no break
			case 1:
				$this->request->setActionName($args[0]);
				$this->request->setUndispatched();
				break;
			default:
				break;
		}
	}
	
	public function redirect($url){
		header("location: $url");
	}
}