<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_search.php 25246 2011-11-02 03:34:53Z zhangguosheng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

lang('admincp_searchindex');

$searchindex = & $_G['lang']['admincp_searchindex'];

if(!$searchindex) {
	cpmsg('searchindex_not_found', '', 'error');
}

$keywords = trim($_GET['keywords']);
$kws = explode(' ', $keywords);
$kws = array_map('trim', $kws);
$keywords = implode(' ', $kws);

$result = $html = array();

if($_GET['searchsubmit'] && $keywords) {
	foreach($searchindex as $skey => $items) {
		foreach($kws as $kw) {
			foreach($items['text'] as $k => $text) {
				if(strpos(strtolower($text), strtolower($kw)) !== FALSE) {
					$result[$skey][] = $k;
				}
			}
		}
	}
	if($result) {
		$totalcount = 0;
		foreach($result as $skey => $tkeys) {
			$tmp = array();
			foreach($searchindex[$skey]['index'] as $title => $url) {
				if($title[0] != '_') {
					$tmp[] = '<a href="'.ADMINSCRIPT.'?'.$url.'&highlight='.rawurlencode($keywords).'"  target="_blank">'.$title.'</a>';
				}
			}
			$texts = array();
			$tkeys = array_unique($tkeys);
			foreach($tkeys as $tkey) {
				if(isset($lang[$searchindex[$skey]['text'][$tkey]])) {
					$texts[] = '<li><span s="1">'.strip_tags($lang[$searchindex[$skey]['text'][$tkey]]).'</span><span class="lightfont">('.$searchindex[$skey]['text'][$tkey].')</span></li>';
				} else {
					$texts[] = '<li><span s="1">'.$searchindex[$skey]['text'][$tkey].'</span></li>';
				}
			}
			$texts = array_unique($texts);
			$texts = implode('', $texts);
			$totalcount += $count = count($tkeys);
			$html[] = '<div class="news"><span class="right">'.cplang('search_result_item', array('number' => $count)).'</span><b>'.implode(' &raquo; ', $tmp).'</b></div><ul class="tipsblock">'.$texts.'</ul>';
		}
		if($totalcount) {
			showsubmenu('search_result', array(), '<span class="right">'.cplang('search_result_find', array('number' => $totalcount)).'</span>');
			echo implode('<br />', $html);
			hlkws($kws);
		} else {
			cpmsg('search_result_noexists', '', 'error');
		}
	} else {
		cpmsg('search_result_noexists', '', 'error');
	}
} else {
	cpmsg('search_keyword_noexists', '', 'error');
}

function hlkws($kws) {
echo <<<EOF
<script type="text/JavaScript">
_attachEvent(window, 'load', function () {
EOF;
foreach($kws as $kw) {
	echo 'parsetag(\''.$kw.'\');';
}
echo '}, document)</script>';
}

?>