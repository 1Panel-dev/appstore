<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_styles.php 36353 2017-01-17 07:19:28Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_styles() {
	global $_G;

	$stylevars = $styledata = array();
	$defaultstyleid = $_G['setting']['styleid'];
	foreach(C::t('common_stylevar')->range() as $var) {
		$stylevars[$var['styleid']][$var['variable']] = $var['substitute'];
	}
	foreach(C::t('common_style')->fetch_all_data(true) as $data) {
		$data['tpldir'] = $data['directory'];
		$data = array_merge($data, (array)$stylevars[$data['styleid']]);
		$datanew = array();
		$data['imgdir'] = $data['imgdir'] ? $data['imgdir'] : STATICURL.'image/common';
		$data['styleimgdir'] = $data['styleimgdir'] ? $data['styleimgdir'] : $data['imgdir'];
		foreach($data as $k => $v) {
			if(substr($k, -7, 7) == 'bgcolor') {
				$newkey = substr($k, 0, -7).'bgcode';
				$datanew[$newkey] = setcssbackground($data, $k);
			}
		}
		$data = array_merge($data, $datanew);
		if(strstr($data['boardimg'], ',')) {
			$flash = explode(",", $data['boardimg']);
			$flash[0] = trim($flash[0]);
			$flash[0] = preg_match('/^(https?:)?\/\//i', $flash[0]) ? $flash[0] : $data['styleimgdir'].'/'.$flash[0];
			$data['boardlogo'] = "<embed src=\"".$flash[0]."\" width=\"".trim($flash[1])."\" height=\"".trim($flash[2])."\" type=\"application/x-shockwave-flash\" wmode=\"transparent\"></embed>";
		} else {
			$data['boardimg'] = preg_match('/^(https?:)?\/\//i', $data['boardimg']) ? $data['boardimg'] : $data['styleimgdir'].'/'.$data['boardimg'];
			$data['boardlogo'] = "<img src=\"$data[boardimg]\" alt=\"".$_G['setting']['bbname']."\" border=\"0\" />";
		}
		$data['bold'] = $data['nobold'] ? 'normal' : 'bold';
		$contentwidthint = intval($data['contentwidth']);
		$contentwidthint = $contentwidthint ? $contentwidthint : 600;
		if($data['extstyle']) {
			list($data['extstyle'], $data['defaultextstyle']) = explode('|', $data['extstyle']);
			$extstyle = explode("\t", $data['extstyle']);
			$data['extstyle'] = array();
			foreach($extstyle as $dir) {
				if(file_exists($extstylefile = DISCUZ_ROOT.$data['tpldir'].'/style/'.$dir.'/style.css')) {
					if($data['defaultextstyle'] == $dir) {
						$data['defaultextstyle'] = $data['tpldir'].'/style/'.$dir;
					}
					$content = file_get_contents($extstylefile);
					if(preg_match('/\[name\](.+?)\[\/name\]/i', $content, $r1) && preg_match('/\[iconbgcolor](.+?)\[\/iconbgcolor]/i', $content, $r2)) {
						$data['extstyle'][] = array($data['tpldir'].'/style/'.$dir, $r1[1], $r2[1]);
					}
				}
			}
		}
		$data['verhash'] = random(3);
		$styledata[] = $data;
	}
	foreach($styledata as $data) {
		savecache('style_'.$data['styleid'], $data);
		if($defaultstyleid == $data['styleid']) {
			savecache('style_default', $data);
		}
		writetocsscache($data);
	}

}

function setcssbackground(&$data, $code) {
	$codes = explode(' ', $data[$code]);
	$css = $codevalue = '';
	for($i = 0; $i < count($codes); $i++) {
		if($i < 2) {
			if($codes[$i] != '') {
				if($codes[$i][0] == '#') {
					$css .= strtoupper($codes[$i]).' ';
					$codevalue = strtoupper($codes[$i]);
				} elseif(preg_match('/^(https?:)?\/\//i', $codes[$i])) {
					$css .= 'url("'.$codes[$i].'") ';
				} else {
					$css .= 'url("'.$data['styleimgdir'].'/'.$codes[$i].'") ';
				}
			}
		} else {
			$css .= $codes[$i].' ';
		}
	}
	$data[$code] = $codevalue;
	$css = trim($css);
	return $css ? 'background: '.$css : '';
}

function writetocsscache($data) {
	global $_G;
	$dir = DISCUZ_ROOT.'./template/default/common/';
	$dh = opendir($dir);
	$data['staticurl'] = STATICURL;
	while(($entry = readdir($dh)) !== false) {
		if(fileext($entry) == 'css') {
			$cssfile = DISCUZ_ROOT.'./'.$data['tpldir'].'/common/'.$entry;
			!file_exists($cssfile) && $cssfile = $dir.$entry;
			$cssdata = @implode('', file($cssfile));
			if(file_exists($cssfile = DISCUZ_ROOT.'./'.$data['tpldir'].'/common/extend_'.$entry)) {
				$cssdata .= @implode('', file($cssfile));
			}
			if(is_array($_G['setting']['plugins']['available']) && $_G['setting']['plugins']['available']) {
				foreach($_G['setting']['plugins']['available'] as $plugin) {
					if(file_exists($cssfile = DISCUZ_ROOT.'./source/plugin/'.$plugin.'/template/extend_'.$entry)) {
						$cssdata .= @implode('', file($cssfile));
					}
				}
			}

			writetocsscache_callback_1($data, 1);

			$cssdata = preg_replace_callback("/\{([A-Z0-9]+)\}/", 'writetocsscache_callback_1', $cssdata);
			$cssdata = preg_replace("/<\?.+?\?>\s*/", '', $cssdata);
			$cssdata = !preg_match('/^(https?:)?\/\//i', $data['styleimgdir']) ? preg_replace("/url\(([\"'])?".preg_quote($data['styleimgdir'], '/')."/i", "url(\\1$_G[siteurl]$data[styleimgdir]", $cssdata) : $cssdata;
			$cssdata = !preg_match('/^(https?:)?\/\//i', $data['imgdir']) ? preg_replace("/url\(([\"'])?".preg_quote($data['imgdir'], '/')."/i", "url(\\1$_G[siteurl]$data[imgdir]", $cssdata) : $cssdata;
			$cssdata = !preg_match('/^(https?:)?\/\//i', $data['staticurl']) ? preg_replace("/url\(([\"'])?".preg_quote($data['staticurl'], '/')."/i", "url(\\1$_G[siteurl]$data[staticurl]", $cssdata) : $cssdata;
			if($entry == 'module.css') {
				$cssdata = preg_replace('/\/\*\*\s*(.+?)\s*\*\*\//', '[\\1]', $cssdata);
			}
			$cssdata = preg_replace(array('/\s*([,;:\{\}])\s*/', '/[\t\n\r]/', '/\/\*.+?\*\//'), array('\\1', '',''), $cssdata);
			if(@$fp = fopen(DISCUZ_ROOT.'./data/cache/style_'.$data['styleid'].'_'.$entry, 'w')) {
				fwrite($fp, $cssdata);
				fclose($fp);
			} else {
				exit('Can not write to cache files, please check directory ./data/ and ./data/cache/ .');
			}
		}
	}
}

function writetocsscache_callback_1($matches, $action = 0) {
	static $data = null;

	if($action == 1) {
		$data = $matches;
	} else {
		return $data[strtolower($matches[1])];
	}
}

?>