<?php

/**
 *      [568089266@qq.com] (C)2014-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: iview.php 2014-1-2$
 */
 
namespace yk;

interface IView{
	
	public function assgin($key, $value);
	
	public function render($tpl_file);
	
	public function display($tpl_file, array $tpl_vars);
}