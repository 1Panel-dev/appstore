<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: template.class.php 1167 2014-11-03 03:06:21Z hypowang $
*/

class template {

	var $tpldir;
	var $objdir;

	var $tplfile;
	var $objfile;
	var $langfile;

	var $vars;
	var $force = 0;

	var $var_regexp = "\@?\\\$[a-zA-Z_]\w*(?:\[[\w\.\"\'\[\]\$]+\])*";
	var $vtag_regexp = "\<\?=(\@?\\\$[a-zA-Z_]\w*(?:\[[\w\.\"\'\[\]\$]+\])*)\?\>";
	var $const_regexp = "\{([\w]+)\}";

	var $languages = array();
	var $sid;

	function __construct() {
		$this->template();
	}

	function template() {
		ob_start();
		$this->defaulttpldir = UC_ROOT.'./view/default';
		$this->tpldir = UC_ROOT.'./view/default';
		$this->objdir = UC_DATADIR.'./view';
		$this->langfile = UC_ROOT.'./view/default/templates.lang.php';
		if (version_compare(PHP_VERSION, '5') == -1) {
			register_shutdown_function(array(&$this, '__destruct'));
		}
	}

	function assign($k, $v) {
		$this->vars[$k] = $v;
	}

	function display($file) {
		extract($this->vars, EXTR_SKIP);
		include $this->gettpl($file);
	}

	function gettpl($file) {
		isset($_REQUEST['inajax']) && ($file == 'header' || $file == 'footer') && $file = $file.'_ajax';
		isset($_REQUEST['inajax']) && ($file == 'admin_header' || $file == 'admin_footer') && $file = substr($file, 6).'_ajax';
		$this->tplfile = $this->tpldir.'/'.$file.'.htm';
		$this->objfile = $this->objdir.'/'.$file.'.php';
		$tplfilemtime = @filemtime($this->tplfile);
		if($tplfilemtime === FALSE) {
			$this->tplfile = $this->defaulttpldir.'/'.$file.'.htm';
		}
		if($this->force || !file_exists($this->objfile) || @filemtime($this->objfile) < filemtime($this->tplfile)) {
			if(empty($this->language)) {
				@include $this->langfile;
				if(is_array($languages)) {
					$this->languages += $languages;
				}
			}
			$this->complie();
		}
		return $this->objfile;
	}

	function complie() {
		$template = file_get_contents($this->tplfile);
		$template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);
		$template = preg_replace_callback("/\{lang\s+(\w+?)\}/is", array($this, 'complie_callback_lang_1'), $template);

		$template = preg_replace("/\{($this->var_regexp)\}/", "<?=\\1?>", $template);
		$template = preg_replace("/\{($this->const_regexp)\}/", "<?=\\1?>", $template);
		$template = preg_replace("/(?<!\<\?\=|\\\\)$this->var_regexp/", "<?=\\0?>", $template);

		$template = preg_replace_callback("/\<\?=(\@?\\\$[a-zA-Z_]\w*)((\[[\\$\[\]\w]+\])+)\?\>/is", array($this, 'complie_callback_arrayindex_12'), $template);

		$template = preg_replace_callback("/\{\{eval (.*?)\}\}/is", array($this, 'complie_callback_stripvtag_1'), $template);
		$template = preg_replace_callback("/\{eval (.*?)\}/is", array($this, 'complie_callback_stripvtag_1'), $template);
		$template = preg_replace_callback("/\{for (.*?)\}/is", array($this, 'complie_callback_stripvtag_for1'), $template);

		$template = preg_replace_callback("/\{elseif\s+(.+?)\}/is", array($this, 'complie_callback_stripvtag_elseif1'), $template);

		for($i=0; $i<2; $i++) {
			$template = preg_replace_callback("/\{loop\s+$this->vtag_regexp\s+$this->vtag_regexp\s+$this->vtag_regexp\}(.+?)\{\/loop\}/is", array($this, 'complie_callback_loopsection_1234'), $template);
			$template = preg_replace_callback("/\{loop\s+$this->vtag_regexp\s+$this->vtag_regexp\}(.+?)\{\/loop\}/is", array($this, 'complie_callback_loopsection_123'), $template);
		}
		$template = preg_replace_callback("/\{if\s+(.+?)\}/is", array($this, 'complie_callback_stripvtag_if1'), $template);

		$template = preg_replace("/\{template\s+(\w+?)\}/is", "<? include \$this->gettpl('\\1');?>", $template);
		$template = preg_replace_callback("/\{template\s+(.+?)\}/is", array($this, 'complie_callback_stripvtag_template1'), $template);


		$template = preg_replace("/\{else\}/is", "<? } else { ?>", $template);
		$template = preg_replace("/\{\/if\}/is", "<? } ?>", $template);
		$template = preg_replace("/\{\/for\}/is", "<? } ?>", $template);

		$template = preg_replace("/$this->const_regexp/", "<?=\\1?>", $template);

		$template = "<? if(!defined('UC_ROOT')) exit('Access Denied');?>\r\n$template";
		$template = preg_replace("/(\\\$[a-zA-Z_]\w+\[)([a-zA-Z_]\w+)\]/i", "\\1'\\2']", $template);

		$template = preg_replace("/\<\?(\s{1})/is", "<?php\\1", $template);
		$template = preg_replace("/\<\?\=(.+?)\?\>/is", "<?php echo \\1;?>", $template);

		$fp = fopen($this->objfile, 'w');
		fwrite($fp, $template);
		fclose($fp);
	}

	function complie_callback_lang_1($matches) {
		return $this->lang($matches[1]);
	}

	function complie_callback_arrayindex_12($matches) {
		return $this->arrayindex($matches[1], $matches[2]);
	}

	function complie_callback_stripvtag_1($matches) {
		return $this->stripvtag('<? '.$matches[1].'?>');
	}

	function complie_callback_stripvtag_for1($matches) {
		return $this->stripvtag('<? for('.$matches[1].') {?>');
	}

	function complie_callback_stripvtag_elseif1($matches) {
		return $this->stripvtag('<? } elseif('.$matches[1].') { ?>');
	}

	function complie_callback_loopsection_1234($matches) {
		return $this->loopsection($matches[1], $matches[2], $matches[3], $matches[4]);
	}

	function complie_callback_loopsection_123($matches) {
		return $this->loopsection($matches[1], '', $matches[2], $matches[3]);
	}

	function complie_callback_stripvtag_if1($matches) {
		return $this->stripvtag('<? if('.$matches[1].') { ?>');
	}

	function complie_callback_stripvtag_template1($matches) {
		return $this->stripvtag('<? include $this->gettpl('.$matches[1].'); ?>');
	}

	function arrayindex($name, $items) {
		$items = preg_replace("/\[([a-zA-Z_]\w*)\]/is", "['\\1']", $items);
		return "<?=$name$items?>";
	}

	function stripvtag($s) {
		return preg_replace("/$this->vtag_regexp/is", "\\1", str_replace("\\\"", '"', $s));
	}

	function loopsection($arr, $k, $v, $statement) {
		$arr = $this->stripvtag($arr);
		$k = $this->stripvtag($k);
		$v = $this->stripvtag($v);
		$statement = str_replace("\\\"", '"', $statement);
		return $k ? "<? foreach((array)$arr as $k => $v) {?>$statement<? }?>" : "<? foreach((array)$arr as $v) {?>$statement<? } ?>";
	}

	function lang($k) {
		return !empty($this->languages[$k]) ? $this->languages[$k] : "{ $k }";
	}

	function _transsid($url, $tag = '', $wml = 0) {
		$sid = $this->sid;
		$tag = stripslashes($tag);
		if(!$tag || (!preg_match("/^(https?:\/\/|mailto:|#|javascript)/i", $url) && !strpos($url, 'sid='))) {
			if($pos = strpos($url, '#')) {
				$urlret = substr($url, $pos);
				$url = substr($url, 0, $pos);
			} else {
				$urlret = '';
			}
			$url .= (strpos($url, '?') ? ($wml ? '&amp;' : '&') : '?').'sid='.$sid.$urlret;
		}
		return $tag.$url;
	}

	function __destruct() {
		if($_COOKIE['sid']) {
		}
		$sid = rawurlencode($this->sid);
		$content = preg_replace_callback("/\<a(\s*[^\>]+\s*)href\=([\"|\']?)([^\"\'\s]+)/is", array($this, 'destruct_callback_transsid_312'), ob_get_contents());
		$content = preg_replace("/(\<form.+?\>)/is", "\\1\n<input type=\"hidden\" name=\"sid\" value=\"".rawurldecode(rawurldecode(rawurldecode($sid)))."\" />", $content);
		ob_end_clean();
		echo $content;
	}

	function destruct_callback_transsid_312($matches) {
		return $this->_transsid($matches[3],'<a'.$matches[1].'href='.$matches[2]);
	}
}