<?php

/**
 *      [568089266@qq.com] (C)2014-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: imemory.php 2014-3-17$
 */
 
namespace yk;

interface IMemory{
	
	public function init($config);
	
	public function get($key);
	
	public function getmulti(array $keys);
	
	public function set($key, $value, $expire);
	
	public function delete($key);
	
	public function increment($key, $value = 1);
	
	public function decrement($key, $value = 1);
	
	public function flush();
	
	public function get_version();
}