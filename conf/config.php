<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: config.php 2013-7-8$
 */
 
return array(
		// debug 0关闭、1为error、2为all
		'debug' => 0,
		
		//系统时区
		'timeoffset' => '8',
		
		'db' => array(
			1 => array(
				'host' => 'localhost',
				'port' => '3306',
				'user' => '',
				'password' => '',
				'charset' => 'utf8',
				'dbname' => '',
				'tableprefix' => ''
			),
		),
		
		'default_mca' => array(
			'module' => 'index',
			'controller' => 'index',
			'action' => 'index'
		),
		
		'default_tpl' => 'default',
		
		'modules' => array(
			'index'
		),
		
		'plugins' => array(
			
		),
		
		'routes' => array(
			
		),
			
		'memory' => array(
				'prefix' => 'bGMlKf_',
				'redis' => array(
						'server' => '',
						'port' => '',
						'pconnect' => 1,
						'timeout' => 0,
						'serializer' => 1
					),
				'memcached' => array(
							array('host' => '127.0.0.1', 'port' => '11211', 'weight' => 0)
					),
				'memcache' => array(
						'server' => '127.0.0.1',
						'port' => '11211',
						'pconnect' => 1,
						'timeout' => 1
					),
				
			),
		
		'output' => array(
				'forceheader' => 1,
				'charset' => 'utf-8',
				'gzip' => 1,
				'tplrefresh' => 2,	//2时强制刷新，1检查刷新，0不更新
				'cssrefresh' => 1,	//1检查刷新，0不更新
				'language' => 'zh_cn'
			),
		
		'cookie' => array(
				'cookiepre' => 'Fsis_',
				'cookiedomain' => '',
				'cookiepath' => '/'
			),
		
		'security' => array(
				'authkey' => '8862b8tWlveWMkNw',
				'querysafe' => array(
						'status' => 1,
						'dfunction' => array('load_file', 'hex', 'substring', 'if', 'ord', 'char'),
						'daction' => array('intooutfile', 'intodumpfile', 'unionselect', '(select', 'unionall', 'uniondistinct'),
						'dnote' => array('/*', '*/', '#', '--', '"'),
						'dlikehex' => 1,
						'afullnote' => 0
					)
			),
			
		'privatekey' => '97893K8ix8Y98k3Ykp8I8YP2d881I1T0',

		'plugindeveloper' => 2
			
	);