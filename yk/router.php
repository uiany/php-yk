<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: yk_router.php 2013-11-25$
 */
namespace yk;

class router{
	
	private $_routes = array();
	
	public function __construct(){
		
		$conf = g('config/routes');
		foreach ($conf as $v){
			switch ($v['type']){
				case 'get':
					$this->addRoute(new route\get($v));
					break;
				case 'simple':
					$this->addRoute(new route\simple($v));
					break;
				case 'rewrite':
					$this->addRoute(new route\rewrite($v));
					break;
				case 'regex':
					$this->addRoute(new route\regex($v));
					break;
				default:
					break;
			}
		}
	}
	
	public function addRoute(IRoute $route){
		$this->_routes[] = $route;
	}
	
	public function getRoutes(){
		return $this->_routes;
	}
	
	public function run(request\base $request){
		hookscript('yk/router/before', $request);
		foreach ($this->getRoutes() as $route){
			if ($route->route($request)) {
				$request->setRouted();
				break;
			}
		}
		if (!$request->isRouted() && $request->getRequestUri()) {
			$default_route = new route\mcap();
			if ($default_route->route($request)) {
				$request->setRouted();
			}
		}
		hookscript('yk/router/after', $request);
	}
}