<?php

/**
 *      [568089266@qq.com] (C)2013-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: yk_func.php 2013-8-9$
 */

function g($key, $value = null, $isset = false){
	return \yk\yk::g($key, $value, $isset);
}

/**
 * @param string $table
 * @return \yk\table
 */
function t($table){
	return \yk\yk::t($table);
}

/**
 * @param string $table
 * @return \yk\model
 */
function m($table){
	return \yk\yk::m($table);
}

//将key/value形式转成数组
function strbackslant($str, $strIsArray = false){
	$return = array();
	//使用正则式在速度会慢一些
	//preg_replace('/(\w+)\/([^\/]+)/e', "\$return['$1']=strip_tags('$2')", $str);
	$ar = $strIsArray ? $str : explode('/', $str);
	for ($i=0, $len = count($ar); $i<$len; $i+=2){
		$j = $i+1;
		if (preg_match('/^\w+$/', $ar[$i])){
			$return[$ar[$i]] = isset($ar[$j]) ? strip_tags($ar[$j]) : null;
		}
	}
	return $return;
}

//如果加载为 resource/lang/lang_a.php, $file = a;
//如果加载为 resource/lang/ext/lang_b.php, $file = ext/b;
function lang($file, $var, $replace = array(), $default = null){
	$_G = &\yk\yk::app()->G();
	
	$path = '';
	if (strpos($file, '/') > 0){
		list($path, $file) = explode('/', $file);
	}
	
	$lang = array();
	if ($path != 'plugin'){
		$key = $path ? $path.'_'.$file : $file;
		if (!isset($_G['lang'][$key])){
			$lang = include MAIN_ROOT.'resource/lang/'.($path ? $path.'/' : '').'lang_'.$file.'.php';
			$_G['lang'][$key] = $lang;
		}
		$returnvalue = &$_G['lang'];
	}else{
		$key = $file;
		if (!isset($_G['cache']['pluginlang'][$key])){
			if (($lang = @include MAIN_ROOT.'application/plugin/'.$file.'/lang_'.$file.'.php') !== false){
				$_G['cache']['pluginlang'][$key] = $lang;
			}
		}
		$returnvalue = &$_G['cache']['pluginlang'];
	}
	
	$return = $var !== null ? (isset($returnvalue[$key][$var]) ? $returnvalue[$key][$var] : null) : $returnvalue[$key];
	$return = $return !== null ? $return : ($default === null ? $var : $default);
	//替换{}变量
	$searchs = $replaces = array();
	if ($replace && is_string($replace)) {
		$replace = strbackslant($replace);
	}
	if ($replace && is_array($replace)){
		foreach ($replace as $k => $v){
			$searchs[] = '{'.$k.'}';
			$replaces[] = $v;
		}
	}
	if (is_string($return) && strpos($return, '{_G/') !== false){
		preg_match_all('/\{_G\/(.+?)\}/', $return, $matches, PREG_PATTERN_ORDER);
		foreach ($matches[0] as $k => $v){
			$searchs[] = $k;
			$replaces[] = g($matches[1][$k]);
		}
	}
	
	return str_replace($searchs, $replaces, $return);
}

function yfilter($value, $method, $extra = null){
	return yk\helper\filter::verify($value, $method, $extra);
}

function yis_type($value, $method){
	return yk\helper\filter::is_type($value, $method);
}

function yset_timeoffset($offset = 0){
	date_default_timezone_set('Etc/GMT'.($offset > 0 ? '-' : '+' ).abs($offset));
}

function ytimestamp($datestr){
	if (false !== ($timestamp = strtotime($datestr)) ) {
		return $timestamp;
	}else{
		return false;
	}
}

function ygmdate($timestamp, $format = null){
	if ($format) {
		return date($format, $timestamp);
	}
	$differ = TIMESTAMP - $timestamp;
	$lang = lang('core', 'date');
	if ($differ >= 0) {
		//过去
		if ($differ > 172800) {	//大于2天
			return $returnstr = date('n'.$lang['month'].'d'.$lang['day'].' H:i', $timestamp);
		}elseif ($differ > 86400){	//天于1天
			return $returnstr = $lang['yesterday'].' '.date('H:i', $timestamp);
		}elseif ($differ > 3600){	//大于1小时
			return $lang['today'].' '.date('H:i', $timestamp);
		}elseif ($differ > 60){		//大于1分钟
			return round($differ / 60).$lang['minute'].$lang['ago'];
		}else{
			return $lang['about'].$differ.$lang['second'].$lang['ago'];
		}
	}else{
		//将来
		$differ = abs($differ);
		if ($differ < 60) {	//1分钟内
			return $differ ? $lang['more'].$differ.$lang['seconed'] : $lang['now'];
		}elseif ($differ < 3600){	//1个小时内
			return $lang['more'].round($differ / 60).$lang['minute'];
		}elseif ($differ < 86400){	//一天内
			return $lang['more'].round($differ / 3600).$lang['hour'];
		}else{
			return date('Y'.$lang['year'].'n'.$lang['month'].'j'.$lang['day'], $timestamp);
		}
	}
}

function yob_start($gzip = true){
	static $_output_inited = false;
	if (!$_output_inited) {
		$gzip = $gzip && g('gzipcompress');
		$gzip ? ob_start('ob_gzhandler') : ob_start();
		$_output_inited = true;
	}
}

function ckreftpl($tpl, $cftpl, $cftime){
	static $view;
	if (!$view) {
		$view = \yk\yk::app()->getDispatcher()->getView();
	}
	if (filemtime($view->getTplPath($cftpl)) > $cftime) {
		$view->refreshTpl($tpl);
	}
}

function template($name, $cachetime){
	return \yk\yk::app()->getDispatcher()->getView()->refreshTpl($name, $cachetime);
}

function yrandom($len, $isnumeric = false){
	$seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $isnumeric ? 10 : 35);
	if (!$isnumeric) {
		$seed .= 'zZ'.strtoupper($seed);
	}else{
		$seed .= '0123456789';
	}
	
	$max = strlen($seed) - 1;
	$hash = '';
	while ($len-- > 0){
		$hash .= $seed[mt_rand(0, $max)];
	}
	return $hash;
}

function hookscript($name, $params = null){
	static $plugins = null;
	if ($plugins === null) {
		$plugins = array();
		foreach (\yk\yk::app()->getDispatcher()->getPlugins() as $id){
			$plugin_name = "application\\plugin\\$id\\portal";
			$plugins[$id] = new $plugin_name;
			if ($plugins[$id] instanceof yk\plugin) {
				$plugins[$id]->init();
			}
		}
	}
	
	$value = g($name);
	if (!$value) {
		$value = array();
	}
	$methodname = str_replace('/', '_', $name);
	foreach ($plugins as $plugin){
		if (method_exists($plugin, $methodname)) {
			$value[] = $plugin->$methodname($params);
		}
	}
	g($name, $value, true);
	return $value;
}

function memory($cmd, $key = null, $value = null, $ttl = 0, $prefix = null){
	$memory = yk\memory::getInstance();
	if ($cmd == 'check') {
		return $memory->enable ? $memory->type : null;
	}else{
		if (defined('YK_CORE_DEBUG') && YK_CORE_DEBUG) {
			$memory->debug[$cmd][] = ($cmd == 'get' || $cmd == 'rm' ? $value : '').$prefix.$key;
		}
		$ret = false;
		switch ($cmd){
			case \yk\memory::SET:
				$ret = $memory->set($key, $value, $ttl, $prefix);
				break;
			case \yk\memory::GET:
				$ret = $memory->get($key, !empty($prefix) ? $prefix : $value);
				break;
			case \yk\memory::DEL:
				$ret = $memory->delete($key, !empty($prefix) ? $prefix : $value);
				break;
			case \yk\memory::FLUSH:
				$ret = $memory->flush();
				break;
			case \yk\memory::INC:
				$ret = $memory->increment($key, $value, $prefix);
				break;
			case \yk\memory::DEC:
				$ret = $memory->decrement($key, $value, $prefix);
				break;
			default:
				break;
		}
		return $ret;
	}
}

function yfsocketopen($hostname, $port = 80, &$errno, &$errstr, $timeout = 15) {
	$fp = '';
	if(function_exists('fsockopen')) {
		$fp = @fsockopen($hostname, $port, $errno, $errstr, $timeout);
	} elseif(function_exists('pfsockopen')) {
		$fp = @pfsockopen($hostname, $port, $errno, $errstr, $timeout);
	} elseif(function_exists('stream_socket_client')) {
		$fp = @stream_socket_client($hostname.':'.$port, $errno, $errstr, $timeout);
	}
	return $fp;
}

function fileext($file){
	if (($pos = strrpos($file, '.')) !== false) {
		return substr($file, $pos + 1);
	}
	return null;
}

//存入cookie
function ysetcookie($var, $value = '', $life = 0, $prefix = 1, $httponly = false) {
	static $config = null;
	if ($config === null) {
		$config = g('config/cookie');;
	}
	
	if($value == '' || $life < 0) {
		$value = ''; $life = -1;
	}

	$var = ($prefix ? $config['cookiepre'] : '').$var;
	
	$life = $life > 0 ? TIMESTAMP + $life : ($life < 0 ? TIMESTAMP - 31536000 : 0);
	$path = $httponly && PHP_VERSION < '5.2.0' ? $config['cookiepath'].'; HttpOnly' : $config['cookiepath'];

	$secure = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;
	setcookie($var, $value, $life, $path, $config['cookiedomain'], $secure, $httponly);
	
	g("cookie/$var", $value, true);
	$_COOKIE[$var] = $value;
}

function ygetcookie($var){
	return g("cookie/$var");
}

function ypage($page, $total, $view_num = 6){
	if ($total <= $view_num) {
		return range(1, $total, 1);
	}else{
		//在$page的左右二边各显示最大数量
		$num = ceil($view_num / 2);
		
		$start = $page - $num;
		if ($start > 0) {
			$end = $page + $num;
			if ($total < $end) { //后面不够,用全部补全$num位
				$start = max(1, $start + $total - $end);
				$end = $total;
			}
		}else{
			$start = 1;
			$end = $page + $num + $num - $page + 1;
		}
		while ($start <= $end){
			$return[] = $start++;
		}
		return $return;
	}
}