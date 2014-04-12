<?php

/**
 *      [568089266@qq.com] (C)2014-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: simple.php 2014-3-13$
 */
 
namespace yk\view;

class simple implements \yk\IView{
	
	private $_tpl_vars = array();
	private $_tpl_name;		//模版名
	private $_tpl_ext_name;	//模版文件后缀
	private $_css_refresh;	//CSS更新检测
	private $_tpl_refresh;	//模版更新检测
	private $_tpl_cache;	//view缓存目录，在其它下对应css、template
	
	public function __construct(){
		$config = g('config');
		$this->_tpl_name = $config['default_tpl'];
		$this->_tpl_ext_name = '.html';
		$this->_css_refresh = $config['output']['cssrefresh'];
		$this->_tpl_refresh = $config['output']['tplrefresh'];
		$this->_tpl_cache = MAIN_ROOT.'data';
	}
	
	public function refreshCss(){
		$dir = MAIN_ROOT.'resource/template/'.$this->_tpl_name.'/common/';
		$cache_dir = $this->_tpl_cache.'/css/';
		
		//定义扩展的变量
		$extra_params = array(
				'img' => './resource/image',
				'imgex' => './resource/template/'.$this->_tpl_name.'/image'
		);
		
		$cf_common = $cache_dir.'style_'.$this->_tpl_name.'_common.css';
		if (!file_exists($cf_common) || filemtime($dir.'common.css') > $cf_common) {
			css::cache($dir.'common.css',
				$cache_dir.'style_'.$this->_tpl_name.'_common.css',
				$extra_params,
				false);
		}
		
		$cf_time = 0; $cf_modules = array();
		$pdir = dir($dir);
		while (false !== ($entry = $pdir->read()) ){
			if (strpos($entry, 'module.css') !== false) {
				$cf_modules[] = $dir.$entry;
				$cf_time = max($cf_time, filemtime($dir.$entry));
			}
		}
		
		$cf_module = $cache_dir.'style_'.$this->_tpl_name.'_module.css';
		if (!file_exists($cf_module) || $cf_time > filemtime($cf_module)) {
			css::cache($cf_modules,
				$cache_dir.'style_'.$this->_tpl_name.'_module.css',
				$extra_params,
				true);
		}
	}
	
	public function getTplName(){
		return $this->_tpl_name;
	}
	
	public function setTplName($name){
		$this->_tpl_name = $name;
	}
	
	//获取模版的源文件位置
	public function getTplPath($tpl){
		if (strpos(':', $tpl)) {	//取向了另一个模版
			list($tpl_name, $tpl) = explode(':', $tpl);
		}else{
			$tpl_name = $this->_tpl_name;
		}
		return MAIN_ROOT."resource/template/{$tpl_name}/$tpl{$this->_tpl_ext_name}";
	}
	
	//获取模版的缓存位置
	public function getTplCache($tpl){
		if (strpos(':', $tpl)) {	//取向了另一个模版
			list($tpl_name, $tpl) = explode(':', $tpl);
		}else{
			$tpl_name = $this->_tpl_name;
		}
		return MAIN_ROOT."data/template/{$tpl_name}_".str_replace('/', '_', $tpl).'.php';
	}
	
	//刷新模版
	public function refreshTpl($tpl, $cftime = 0){
		$src_tpl = $this->getTplPath($tpl);
		$dst_tpl = $this->getTplCache($tpl);
		if (file_exists($src_tpl)) {
			if (!file_exists($dst_tpl) || filemtime($src_tpl) > $cftime) {
				$template = $this->render($tpl);
				$fp = fopen($dst_tpl, 'wb');
				if ($fp) {
					fwrite($fp, $template);
					fclose($fp);
				}else{
					runlog('view', "$tpl file can't write");
					throw new \yk\exception\view();
				}
			}
			return $dst_tpl;
		}else{
			runlog('view', "$tpl file not found");
			throw new \yk\exception\view();
		}
	}
	
	public function assgin($key, $value){
		$this->_tpl_vars[$key] = $value;
	}
	
	public function render($tpl){
		$template = new template();
		return $template->parse($tpl);
	}
	
	public function display($tpl, array $vars){
		
		$this->_css_refresh && $this->refreshCss();
		
		$cf_tpl = $this->getTplCache($tpl);
		if (!file_exists($cf_tpl) || $this->_tpl_refresh == 2 
			|| ($this->_tpl_refresh && filemtime($cf_tpl) < filemtime($this->getTplPath($tpl)) ) ) {
			$content = $this->render($tpl);
			$fp = fopen($cf_tpl, 'wb');
			if ($fp) {
				fwrite($fp, $content);
				fclose($fp);
			}else{
				runlog('view', "$tpl cache file can't write");
				throw new \yk\exception\view();
			}
		}
		$_G = \yk\yk::app()->G();
		!empty($vars) && extract($vars);
		!empty($this->_tpl_vars) && extract($this->_tpl_vars);
		include $cf_tpl;
	}
}