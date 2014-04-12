<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: yk_dispatcher.php 2013-11-25$
 */

namespace yk;

class dispatcher{
	
	/**
	 * @var \yk\request\base
	 */
	private $_request;
	
	/**
	 * @var \yk\IView
	 */
	private $_view;
	
	/**
	 * @var \yk\router
	 */
	private $_router;
	
	private $_plugins = array();
	private $_modules = array();
	
	public function __construct(){
		
		$this->_request = new request\http();
		$this->_router = new router();
		
		$default_mca = g('config/default_mca');
		$this->addModule($default_mca['module']);
		$this->setDefaultModule($default_mca['module']);
		$this->setDefaultController($default_mca['controller']);
		$this->setDefaultAction($default_mca['action']);
		
		$conf_modules = g('config/modules');
		if (!empty($conf_modules)) {
			foreach ($conf_modules as $module){
				$this->addModule($module);
			}
		}
		
		$conf_plugins = g('config/plugins');
		if (!empty($conf_plugins)) {
			foreach ($conf_plugins as $plugin){
				$this->addPlugin($plugin);
			}
		}
		
		error::init();
	}
	
	public function setRequest(request\base $request){
		$this->_request = $request;
	}
	
	/**
	 * @return \yk\request\base
	 */
	public function getRequest(){
		return $this->_request;
	}
	
	/**
	 * @return \yk\router
	 */
	public function getRouter(){
		return $this->_router;
	}
	
	public function addModule($name){
		$this->_modules[$name] = $name;
	}
	
	public function getModules(){
		return $this->_modules;
	}
	
	public function addPlugin($name){
		$this->_plugins[$name] = $name;
	}
	
	public function getPlugins(){
		return $this->_plugins;
	}
	
	public function setView(IView $view){
		$this->_view = $view;
	}
	
	public function getView(){
		if (!$this->_view) {
			$this->_view = new view\simple();
		}
		return $this->_view;
	}
	
	public function setDefaultModule($name){
		$this->_request->setModuleName($name);
		return $name;
	}
	
	public function setDefaultController($name){
		$this->_request->setControllerName($name);
		return $name;
	}
	
	public function setDefaultAction($name){
		$this->_request->setActionName($name);
		return $name;
	}
	
	//如果不设置错语处理方法，YK会调用自己的方法进行处理
	public function setErrorHandler($callback){
		error::setErrorHandler($callback);
	}
	
	public function setExcetiponType($type = 0){
		error::setExcetiponType($type);
	}
	
	public function run(){
		$this->_router->run($this->_request);
		while (!$this->_request->isDispatched()){
			$this->_request->setDispatched();
			hookscript('yk/dispatcher/before', $this->_request);
			$this->dispatch();
			hookscript('yk/dispatcher/after', $this->_request);
		}
	}
	
	public function dispatch(){
		
		$module = $this->_request->getModuleName();
		$controller = $this->_request->getControllerName();
		$action = $this->_request->getActionName();
		
		!is_null($module) or error::throwException(new exception\dispatch_m());
		!is_null($controller) or error::throwException(new exception\dispatch_c());
		!is_null($controller) or error::throwException(new exception\dispatch_a());
		
		$mca_name = '\modules\\'.$module.'\\'.$controller;
		
		try{
			$mac_ref = new \ReflectionClass($mca_name);
			$mac_method = $mac_ref->getMethod($action);
			g('CURMCA', $module.'/'.$controller.'/'.$action);
			hookscript(g('CURMCA'));
			$mac_method->invoke(new $mca_name);
		}catch (\Exception $e){
			error::throwException($e);
		}
	}
}