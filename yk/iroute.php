<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: interface.php 2013-11-24$
 */

namespace yk;

interface IRoute{
	
	function route(request\base $request);
}