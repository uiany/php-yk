<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: yk_cache_css.php 2013-9-1$
 */

namespace yk\view;

class css{
	
	public static function cache($file, $cachefile, $extra_params = null, $list = false){
		if (is_array($file)) {
			$cssdata = '';
			foreach ($file as $f){
				$cssdata .= implode('', file($f));
			}
		}else{
			$cssdata = implode('', file($file));
		}
		
		//对extra_parms内空进行行替换
		if (isset($extra_params['img']) && !preg_match('/^http(s)?:\/\//i', $extra_params['img'])) {
			$extra_params['img'] = "../../".$extra_params['img'];
		}
		$cssdata = preg_replace('/\{([a-z0-9\_\-]+)\}/ie', "\$extra_params[strtolower('$1')]", $cssdata);
		
		//对如 /* list */ 注释进行集合
		if ($list) {
			$cssdata = preg_replace('/\/\*\*\s*(.+?)\s\*\*\//is', "[$1]", $cssdata);
		}
		$cssdata = preg_replace(array('/\s*([,;:\{\}])\s*/', '/[\t\n\r]/', '/\/\*.+?\*\//'), array('\\1', '',''), $cssdata);
		$cssdata = preg_replace('/\[(.+?)\]\[end\]/', '', $cssdata);
		if(false !== ($fp = fopen($cachefile, 'w+')) ) {
			fwrite($fp, $cssdata);
			fclose($fp);
		} else {
			throw new \yk\exception\view('Can not write to cache files in ./data/ and ./data/cache/ .');
		}
	}
}