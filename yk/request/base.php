<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: abstract.php 2013-11-24$
 */
 
namespace yk\request;

abstract class base{
	
	private $_method;
	
	private $_module;
	private $_controller;
	private $_action;
	
	private $_dispatched = false;
	private $_routed = false;
	
	private $_params = array();
	private $_request_uri;
	private $_base_uri;
	
	public function setMethod($name){
		$this->_method = strtolower($name);
	}
	
	public function getMethod(){
		return $this->_method;
	}
	
	public function getModuleName(){
		return $this->_module;
	}
	
	public function setModuleName($name){
		$this->_module = $name;
		return $name;
	}
	
	public function getControllerName(){
		return $this->_controller;
	}
	
	public function setControllerName($name){
		$this->_controller = $name;
		return $name;
	}
	
	public function getActionName(){
		return $this->_action;
	}
	
	public function setActionName($name){
		$this->_action = $name;
		return $name;
	}
	
	public function isDispatched(){
		return (bool) $this->_dispatched;
	}
	
	public function setDispatched(){
		$this->_dispatched = true;
	}
	
	public function setUndispatched(){
		$this->_dispatched = false;
	}
	
	public function isRouted(){
		return (bool) $this->_routed;
	}
	
	public function setRouted(){
		$this->_routed = true;
	}
	
	public function getParams(){
		return $this->_params;
	}
	
	public function getParam($name, $default = null){
		return isset($this->_params[$name]) ? $this->_params[$name] : $default;
	}
	
	public function setParam($name, $value){
		$this->_params[$name] = $value;
	}
	
	public function setParams(array $arr){
		$this->_params = array_merge($this->_params, $arr);
	}
	
	public function isXMLHttpRequest(){
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' || g('inajax'); 
	}
	
	public function isPost(){
		return $this->_method == 'post';
	}
	
	public function isGet(){
		return $this->_method == 'get';
	}
	
	public function isPut(){
		return $this->_method == 'put';
	}
	
	public function isDelete(){
		return $this->_method == 'delete';
	}
	
	public function isCli(){
		return strtolower(php_sapi_name()) == 'cli';
	}
}