<?php

/**
 *      [568089266@qq.com] (C)2014-2099 yukeyong All.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: template.php 2014-3-13$
 */
 
namespace yk\view;

class template{
	
	private $_func_store;	//方法查找替换存储区
	private $_func_index;	//方法查找替换索引
	private $_subtemplate_list = array();	//动态模版
	
	private $_css_data = '';
	
	private $_var_regexp;
	private $_const_regexp;
	
	/**
	 * var \yk\view\simple 
	*/
	private $_view;
	
	public function __construct(){
		$this->_func_store = array('search' => array(), 'replace' => array());
		$this->_subtemplate_list = array();
		$this->_func_index = 0;
		$this->_var_regexp = '((\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\-\>)?[a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)';
		$this->_const_regexp = '([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)';
		
		$this->_view = \yk\yk::app()->getDispatcher()->getView();
	}
	
	public function parse($tpl){
		
		$tplfile = $this->_view->getTplPath($tpl);
		$fp = fopen($tplfile, 'rb');
		if ($fp) {
			$content = fread($fp, filesize($tplfile));
			fclose($fp);
		}else{
			throw new \yk\exception\view("$tpl template file don't read!");
		}
		
		//变量
		$var_regexp = $this->_var_regexp;
		//常量
		$const_regexp = $this->_const_regexp;
		
		//解析动态模版, 最高允许5层
		$i = 0;
		while ($i < 5 && strpos($content, '{subtemplate') !== false){
			$content = preg_replace_callback('/[\n\r\t]*(?:\<\!\-\-)?\{subtemplate\s+(.+?)\}(\-\-\>)?[\r\r\t]*/is', array($this, '_ay_subtemplate'), $content);
			$i ++;
		}
		unset($i);
		
		//去掉tab
		$content = preg_replace('/([\n\r])+\t+/s', "$1", $content);
		//统一{}和<!--{}-->
		$content = preg_replace('/\<\!\-\-(\{.+?\})\-\-\>/s', "$1", $content);
		
		//保存不解析的内容
		$content = preg_replace_callback('/[\n\r\t]*\{html\}(.+?)\{\/html\}/is',
				array($this, '_ay_html'),
				$content
		);
		
		//扩展lang
		$content = preg_replace_callback('/[\n\r\t]*\{lang\s+(.+?)(?:\s+(.+?))?\s*\}/is',
				array($this, '_ay_lang'),
				$content
		);
		
		//解析date
		$content = preg_replace_callback('/[\n\r\t]*\{date\s+(.+?)(?:\s+(.+?))?\s*\}/is', 
				array($this, '_ay_date'), 
				$content
			);
		
		//解析avatar
		$content = preg_replace_callback('/[\n\r\t]*\{avatar\s+(.+?)(?:\s+(\w+?))?\s*\}/is', 
				array($this, '_ay_avatar'), 
				$content
			);
		
		//解析echo，php原形echo
		$content = preg_replace_callback('/[\n\r\t]*\{echo\s+(.+?)(?:;)?[\n\r\t]*\}/is', 
				array($this, '_ay_echo'), 
				$content
			);
		
		//解析eval，将内容当作php代码
		$content = preg_replace_callback('/[\n\r\t]*\{eval\s+(.+?)\s*\}/is', 
				array($this, '_ay_eval'), 
				$content
			);
		
		//解析插件
		$content = preg_replace_callback('/\{hook\s+(\w+?)(?:\.(.+?))?\}/i', 
				array($this, '_ay_hook'), 
				$content
			);
		
		//变量替换
		$content = preg_replace('/\{(\$[a-zA-Z0-9_\-\>\[\]\'\"\$\.\x7f-\xff]+)\}/s', "<?=$1?>", $content);
		
		$content = preg_replace_callback("/$var_regexp/s", 
				array($this, '_ay_var_revise'), 
				$content
			);
		
		$content = preg_replace_callback("/\<\?\=\<\?\=$var_regexp\?\>\?\>/s", 
				array($this, '_ay_var'), 
				$content
			);
		
		//对CSS进行全局替换
		$content = preg_replace_callback('/[\n\r\t]*\{csstemplate\}[\n\r\t]*/is',
				array($this, '_ay_css'),
				$content
		);
		
		hookscript('yk/template', $content);
		
		//----------------------------解析end-----------------------------------------
		
		//动态模版是包含的，所以需要额外检查是否更新了
		$header = '';
		if (!empty($this->_subtemplate_list)){
			$header .= "\n0 ";
			foreach ($this->_subtemplate_list as $file){
				$header .= "|| ckreftpl('$tpl', '$file', '".time()."')\n";
			}
			$header .= ';';
		}
		
		//加上头信息
		$content = "<?php {$header}?>\n$content";
		//解析嵌套模版
		$content = preg_replace_callback('/[\n\r\t]*\{template\s+(.+?)\}[\n\r\t]*/is', array($this, '_ay_template'), $content);
		
		//解析if elseif else /if
		$content = preg_replace_callback('/([\n\r\t]*)\{if\s*(.+?)\s*\}([\n\r\t]*)/is', 
				array($this, '_ay_if'), 
				$content
			);
		
		$content = preg_replace_callback('/([\n\r\t]*)\{elseif\s*(.+?)\s*\}([\n\r\t]*)/is', 
				array($this, '_ay_elseif'), 
				$content
			);
		
		$content = preg_replace('/([\n\r\t]*)\{else\s*\}([\n\r\t]*)/is', "$1<?php }else { ?>$2", $content);
		$content = preg_replace('/([\n\r\t]*)\{\/if\s*\}([\n\r\t]*)/is', "$1<?php } ?>$2", $content);
		
		//解析foreach // foreach[(]$array [as] [$k] [=>] $v[)]  其中[]内可省略
		//loop 是 foreach的别名
		$content = preg_replace('/\{foreach(.+?)\}/is', "{loop$1}", $content);
		//抽出()符号
		$content = preg_replace('/[\n\r\t]*\{loop[\s\(]*(.+?)[\s\)]*\}/is', "{loop $1}", $content);
		//抽出as
		$content = preg_replace('/\{loop[\s\(]*(.+?)\s+as\s+(.+?)[\s\)]*\}/is', "{loop $1 $2}", $content);
		//抽出=>
		$content = preg_replace('/\{loop[\s\(]*(.+?)\s*\=\>\s*(.+?)[\s\)]*\}/is', "{loop $1 $2}", $content);
		
		//正式解析loop
		$content = preg_replace_callback('/\{loop\s+(\S+)\s+(\S+)\}/is', 
				array($this, '_ay_loop'), 
				$content
			);
		$content = preg_replace_callback('/\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}/is', 
				array($this, '_ay_loop'), 
				$content
			);
		//解析loop foreach 的空数据
		//解析empty
		$content = preg_replace('/\{empty\}/', "<?php }else{ ?>", $content);
		
		$content = preg_replace('/([\n\r\t]*)\{\/(?:foreach|loop)\s*\}([\n\r\t]*)/is', "$1<?php } ?>$2", $content);
		
		//常量替换, 避免污染表达式,以及特定的符号
		$content = preg_replace("/\{$const_regexp\}/s", "<?=$1?>", $content);
		
		//恢复方法
		if ($this->_func_index > 0){
			$content = str_replace($this->_func_store['search'], $this->_func_store['replace'], $content);
		}
		
		/*恢复<?=$var?>*/
		$content = preg_replace('/\<\?\=(.+?)\?\>/is', "<?php echo $1; ?>", $content);
		
		/*去掉相邻的?><?php */
		$content = preg_replace('/\?\>[\n\r]*\<\?php/is', " ", $content);
		
		return $content;
	}
	
	private function _ay_template($matches){
		template($matches[1], 0);
		return '<?php include template(\''.$matches[1].'\', \''.time().'\');?>';
	}
	
	//动态模版处理
	private function _ay_subtemplate($matches){
		$content = implode('', file($this->_view->getTplPath($matches[1])));
		if ($content){
			$this->_subtemplate_list[] = $matches[1];
			return $content;
		}else{
			return "<!--{$matches[1]} no find-->";
		}
	}
	
	//语言包
	//lang module/filename/varname
	//lang filename/varname
	//lang plugin/plgid/varname
	//如果后面还带有   key/value/key1/value1将对varname的数据进行键值替换
	private function _ay_lang($matches){
		list($module, $file, $var) = explode('/', $matches[1].'//');
		if (empty($var)) {
			$var = $file;
			$file = $module;
		}else{
			$file = $module.'/'.$file;
		}
		
		if (isset($matches[2])) {
			return $this->_ay_func("'$file', '$var', \"{$matches[2]}\"", 'lang');
		}else{
			return $this->_ay_func("'$file', '$var'", 'lang');
		}
	}
	
	//date timestamp
	//date tmmestamp format
	private function _ay_date($matches){
		if (isset($matches[2])) {
			return $this->_ay_func("$matches[1], $matches[2]", 'ygmdate');
		}else{
			return $this->_ay_func("$matches[1]", 'ygmdate');
		}
	}
	
	//avatar uid
	//avatar uid type
	private function _ay_avatar($matches){
		if ($matches[2]) {
			return $this->_ay_func("$matches[1], $matches[2]", 'avatar');
		}else{
			return $this->_ay_func("$matches[1]", 'avatar');
		}
	}
	
	//echo var
	private function _ay_echo($matches){
		$echo = $this->_addquote($matches[1]);
		$this->_func_store['search'][$this->_func_index] = $search = '<!--FUNC-ECHO_'.$this->_func_index.'-->';
		$this->_func_store['replace'][$this->_func_index] =  "<?php echo $echo;?>";
		$this->_func_index ++;
		return $search;
	}
	
	//eval php
	private function _ay_eval($matches){
		$eval = $this->_addquote($matches[1]);
		$this->_func_store['search'][$this->_func_index] = $search = '<!--FUNC-EVAL_'.$this->_func_index.'-->';
		$this->_func_store['replace'][$this->_func_index] =  "<?php $eval;?>";	//后面加上;总是没错的
		$this->_func_index ++;
		return $search;
	}
	
	private function _ay_html($matches){
		$this->_func_store['search'][$this->_func_index] = $search = '<!--FUNC-HTML_'.$this->_func_index.'-->';
		$this->_func_store['replace'][$this->_func_index] = $matches[1];
		$this->_func_index ++;
		return $search;
	}
	
	//掉用plugin中的数据返回
	//hook id/action key/value/key/value...
	private function _ay_hook($matches){
		$hook = $matches[1];
		$vars = isset($matches[2]) ? $matches[2] : '';
		$this->_func_store['search'][$this->_func_index] = $search = '<!--FUNC-HOOK_'.$this->_func_index.'-->';
		$dev = '';
		if (g('config/plugindeveloper') == 2){
			$dev = "echo '<hook>[$hook]</hook>';";
		}
		
		$this->_func_store['replace'][$this->_func_index] =  $dev."<?php echo hookscript('$hook', '$vars');";
		$this->_func_index ++;
		return $search;
	}
	
	private function _ay_css($matches){
		$tplName = \yk\yk::app()->getDispatcher()->getView()->getTplName();
		$loadcss = '<link rel="stylesheet" type="text/css" href="data/css/style_'.$tplName.'_common.css" />';
		$modulecss = MAIN_ROOT.'data/css/style_'.$tplName.'_module.css';
		$content = implode('', file($modulecss));
		$md5MCA = md5(g('CURMCA'));
		$content = preg_replace_callback('/\[(.+?)\](.+?)\[end\]/is', 
				array($this, '_ay_css_aux'), 
				$content
			);
		if (!empty($this->_css_data)){
			$fp = fopen(MAIN_ROOT.'data/css/style_'.$tplName.'_'.$md5MCA.'.css', 'w');
			if($fp) {
				fwrite($fp, $this->_css_data);
				fclose($fp);
			} else {
				throw new \yk\exception\view('yk! Can not write to module css cache files .', 8729);
			}
			$loadcss .= "\n<link rel=\"stylesheet\" type=\"text/css\" href=\"data/css/style_{$tplName}_{$md5MCA}.css\" />";
		}
		return "\n$loadcss\n";
	}
	
	private function _ay_css_aux($matches){
		$modules = $matches[1]; 
		$cssdata = $matches[2];
		@list($cur_m, $cur_c, $cur_a) = explode('/', g('CURMCA'), 3);
		foreach (explode(',', $modules) as $module){
			$mca = explode('/', $module);
			$addto = false;
			switch (count($mca)){
				case 1:
					$addto = $cur_m == $mca[0];
					break;
				case 2:
					$addto = $cur_m == $mca[0] && $cur_c == $mca[1];
					break;
				case 3:
					$addto = $cur_m == $mca[0] && $cur_c == $mca[1] && $cur_a == $mca[2];
					break;
					
			}
			if ($addto) {
				$this->_css_data .= stripslashes($cssdata);
			}
		}
		return '';
	}
	
	//变量的中括号修正
	private function _ay_var_revise($matches){
		$var = $matches[1];
		$varstr = $var; $extrastr = '';
		//对中括号进行修正,避免过多匹配产生的错误
		if (strpos($var, '[') !== false){
			$cut_pos = $this->_ay_var_revise_search($var);
			if ($cut_pos > -1){
				$varstr = substr($var, 0, $cut_pos);
				$extrastr = substr($var, $cut_pos);
			}
		}
		
		if ($extrastr) {
			$extrastr = preg_replace_callback("/$this->_var_regexp/s",
				array($this, '_ay_var_revise'),
				$extrastr
			);
		}
		
		return'<?='.$this->_addquote($varstr).'?>'.$extrastr;
	}
	
	private function _ay_var_revise_search($content){
		$start_tag = '[';
		$end_tag = ']';
		$s_pos = $this->_ay_var_revise_pos($content, $start_tag);
		$e_pos = $this->_ay_var_revise_pos($content, $end_tag);
		$s_next_pos = $e_next_pos = 0;
		$cut_pos = -1;
		while (true){
			if ($s_pos !== false && $e_pos !== false  && $s_pos < $e_pos){
				$s_next_pos = $s_pos; $e_next_pos = $e_pos;
				$s_pos = $this->_ay_var_revise_pos($content, $start_tag, $s_pos + 1);
				$e_pos = $this->_ay_var_revise_pos($content, $end_tag, $e_pos + 1);
				if (substr($content, $e_next_pos + 1, 1) != '[') {
					$s_pos = false;		//修正如这样的字符串设别问题	 adj$ad[a]jdsk[c]
				}
			}else{
				$cut_pos = $e_next_pos + 1;
				break;
			}
		}
		return $cut_pos;
	}
	
	private function _ay_var_revise_pos(&$content, $tag, $start_pos = 0){
		return stripos($content, $tag, $start_pos);
	}
	
	private function _ay_var($matches){
		return $this->_addquote('<?='.$matches[1].'?>');
	}
	
	private function _ay_loop($matches){
		$str = "<?php if(is_array({$matches[1]}) && !empty({$matches[1]})) foreach({$matches[1]} as ".(isset($matches[3]) ? $matches[2].' => '.$matches[3] : $matches[2])."){ ?>";
		return $this->_stripvtags($str);
	}
	
	private function _ay_if($matches){
		return $this->_stripvtags($matches[1].'<?php if('.$matches[2].') { ?>'.$matches[3]);
	}
	
	private function _ay_elseif($matches){
		return $this->_stripvtags($matches[1].'<?php }elseif('.$matches[2].') { ?>'.$matches[3]);
	}
	
	private function _ay_func($args, $func, $prefunc = 'echo'){
		$this->_func_store['search'][$this->_func_index] = $search = '<!--FUNC-'.strtoupper($func).'_'.$this->_func_index.'-->';

		$this->_func_store['replace'][$this->_func_index] =  "<?php $prefunc $func($args);?>";
		
		$this->_func_index ++;
		return $search;
	}
	
	//对数组变量的键值进行加上引号
	private function _addquote($var){
		$var = preg_replace('/\[[\'\"]*([a-zA-Z0-9_\-\.\x7f-\xff]+)[\'\"]*\]/', "['$1']", $var);
		return str_replace('\\"', '"', $var);
	}
	
	//去除变量二次污染
	private function _stripvtags($expr, $statement = '') {
		$expr = str_replace("\\\"", "\"", preg_replace('/\<\?\=\s*(\$.+?)\?\>/s', "$1", $expr));
		$statement && $statement = str_replace("\\\"", "\"", $statement);
		return $expr.$statement;
	}
	
	
}