<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: helper_seo.php 36278 2016-12-09 07:52:35Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class helper_seo {


	public static function get_seosetting($page, $data = array(), $defset = array()) {
		global $_G;
		$searchs = array('{bbname}');
		$replaces = array($_G['setting']['bbname']);

		$seotitle = $seodescription = $seokeywords = '';
		$titletext = $defset['seotitle'] ? $defset['seotitle'] : $_G['setting']['seotitle'][$page];
		$descriptiontext = $defset['seodescription'] ? $defset['seodescription'] : $_G['setting']['seodescription'][$page];
		$keywordstext = $defset['seokeywords'] ? $defset['seokeywords'] : $_G['setting']['seokeywords'][$page];
		preg_match_all("/\{([a-z0-9_-]+?)\}/", $titletext.$descriptiontext.$keywordstext, $pageparams);
		if($pageparams) {
			foreach($pageparams[1] as $var) {
				$searchs[] = '{'.$var.'}';
				if($var == 'page') {
					$data['page'] = $data['page'] > 1 ? lang('core', 'page', array('page' => $data['page'])) : '';
				}
				$replaces[] = $data[$var] ? strip_tags($data[$var]) : '';
			}
			if($titletext) {
				$seotitle = helper_seo::strreplace_strip_split($searchs, $replaces, $titletext);
			}
			if($descriptiontext) {
				$seodescription = helper_seo::strreplace_strip_split($searchs, $replaces, $descriptiontext);
			}
			if($keywordstext) {
				$seokeywords = helper_seo::strreplace_strip_split($searchs, $replaces, $keywordstext);
			}
		}
		return array($seotitle, $seodescription, $seokeywords);
	}


	public static function strreplace_strip_split($searchs, $replaces, $str) {
		$searchspace = array('(((\s)*\-(\s)*)+)', '(((\s)*\,(\s)*)+)', '(((\s)*\|(\s)*)+)', '(((\s)*\t(\s)*)+)', '(((\s)*_(\s)*)+)');
		$replacespace = array('$3-$3', '$3,$3', '$3|$3', '$3 $3', '$3_$3');
		return trim(preg_replace($searchspace, $replacespace, str_replace($searchs, $replaces, $str)), ' ,-|_');
	}

	public static function get_title_page($navtitle, $page){
		if($page > 1) {
			$navtitle .= ' - '.lang('core', 'page', array('page' => $page));
		}
		return $navtitle;
	}

	public static function get_related_link($extent) {
		global $_G;
		loadcache('relatedlink');
		$allextent = array('article' => 0, 'forum' => 1, 'group' => 2, 'blog' => 3);
		$links = array();
		if($_G['cache']['relatedlink'] && isset($allextent[$extent])) {
			foreach($_G['cache']['relatedlink'] as $link) {
				$link['extent'] = sprintf('%04b', $link['extent']);
				if($link['extent'][$allextent[$extent]] && $link['name'] && $link['url']) {
					$links[] = daddslashes($link);
				}
			}
		}
		rsort($links);
		return $links;
	}

	public static function parse_related_link($content, $extent) {
		global $_G;
		loadcache('relatedlink');
		$allextent = array('article' => 0, 'forum' => 1, 'group' => 2, 'blog' => 3);
		if($_G['cache']['relatedlink'] && isset($allextent[$extent])) {
			$searcharray = $replacearray = array();
			foreach($_G['cache']['relatedlink'] as $link) {
				$link['extent'] = sprintf('%04b', $link['extent']);
				if($link['extent'][$allextent[$extent]] && $link['name'] && $link['url']) {
					$searcharray[$link[name]] = '/('.preg_quote($link['name']).')/i';
					$replacearray[$link[name]] = "<a href=\"$link[url]\" target=\"_blank\" class=\"relatedlink\">$link[name]</a>";
				}
			}
			if($searcharray && $replacearray) {
				$_G['trunsform_tmp'] = array();
				$content = preg_replace_callback("/(<script\s+.*?>.*?<\/script>)|(<a\s+.*?>.*?<\/a>)|(<img\s+.*?[\/]?>)|(\[attach\](\d+)\[\/attach\])/is", array(__CLASS__, 'parse_related_link_callback_base64_transform_1234'), $content);
				$content = preg_replace($searcharray, $replacearray, $content, 1);
				$content = preg_replace_callback("/<relatedlink>(.*?)<\/relatedlink>/is", array(__CLASS__, 'parse_related_link_callback_base64_transform_1'), $content);
			}
		}
		return $content;
	}

	static public function parse_related_link_callback_base64_transform_1234($matches) {
		return self::base64_transform('encode', '<relatedlink>', $matches[1].$matches[2].$matches[3].$matches[4], '</relatedlink>');
	}

	static function parse_related_link_callback_base64_transform_1($matches) {
		return self::base64_transform('decode', '', $matches[1], '');
	}

	public static function base64_transform($type, $prefix, $string, $suffix) {
		global $_G;
		if($type == 'encode') {
			$_G['trunsform_tmp'][] = base64_encode(str_replace("\\\"", "\"", $string));
			return $prefix.(count($_G['trunsform_tmp']) - 1).$suffix;
		} elseif($type == 'decode') {
			return $prefix.base64_decode($_G['trunsform_tmp'][$string]).$suffix;
		}
	}
}

?>