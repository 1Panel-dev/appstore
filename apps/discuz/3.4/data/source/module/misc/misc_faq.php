<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_faq.php 32267 2012-12-12 03:26:40Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$keyword = isset($_GET['keyword']) ? dhtmlspecialchars($_GET['keyword']) : '';

$faqparent = $faqsub = array();
foreach(C::t('forum_faq')->fetch_all_by_fpid() as $faq) {
	if(empty($faq['fpid'])) {
		$faqparent[$faq['id']] = $faq;
		if($_GET['id'] == $faq['id']) {
			$ctitle = $faq['title'];
		}
	} else {
		$faqsub[$faq['fpid']][] = $faq;
	}
}

if($_GET['action'] == 'faq') {

	$id = intval($_GET['id']);
	$faq = C::t('forum_faq')->fetch_all_by_fpid($id);
	if($faq) {
		$ffaq = $faq[$id];

		$navtitle = $ctitle;
		$navigation = "<em>&rsaquo;</em> $ctitle";
		$faqlist = array();
		$messageid = empty($_GET['messageid']) ? 0 : $_GET['messageid'];
		foreach(C::t('forum_faq')->fetch_all_by_fpid($id) as $faq) {
			if(!$messageid) {
				$messageid = $faq['id'];
			}
			$faqlist[] = $faq;
		}
	} else {
		showmessage('faq_content_empty', 'misc.php?mod=faq');
	}

} elseif($_GET['action'] == 'search') {

	$navtitle = lang('core', 'search');
	if(submitcheck('searchsubmit')) {
		if(($keyword = $_GET['keyword'])) {
			$sqlsrch = '';
			$searchtype = in_array($_GET['searchtype'], array('all', 'title', 'message')) ? $_GET['searchtype'] : 'all';
			$faqlist = array();
			foreach(C::t('forum_faq')->fetch_all_by_fpid('', $keyword) as $faq) {
				if(!empty($faq['fpid'])) {
					$faq['title'] = preg_replace("/(?<=[\s\"\]>()]|[\x7f-\xff]|^)(".preg_quote($keyword, '/').")(([.,:;-?!()\s\"<\[]|[\x7f-\xff]|$))/siU", "<u><b><font color=\"#FF0000\">\\1</font></b></u>\\2", $faq['title']);
					$faq['message'] = preg_replace("/(?<=[\s\"\]>()]|[\x7f-\xff]|^)(".preg_quote($keyword, '/').")(([.,:;-?!()\s\"<\[]|[\x7f-\xff]|$))/siU", "<u><b><font color=\"#FF0000\">\\1</font></b></u>\\2", $faq['message']);
					$faqlist[] = $faq;
				}
			}
		} else {
			showmessage('faq_keywords_empty', 'misc.php?mod=faq');
		}
		$keyword = dhtmlspecialchars($keyword);
	}

} elseif($_GET['action'] == 'plugin' && !empty($_GET['id'])) {

	$navtitle = $_G['setting']['plugins']['faq'][$_GET['id']]['name'];
	$navigation = '<em>&rsaquo;</em> '.$_G['setting']['plugins']['faq'][$_GET['id']]['name'];
	include pluginmodule($_GET['id'], 'faq');

} else {
	$navtitle = lang('core', 'faq');
}

include template('common/faq');

?>