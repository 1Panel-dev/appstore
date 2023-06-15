<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: thread_album.php 28709 2012-11-08 08:53:48Z liulanbo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
require_once libfile('function/attachment');
$imglist = $albumpayaids = $attachmentlist = $freeattachids = array();
if($_G['forum_threadpay'] && $_G['thread']['freemessage']) {
	preg_match_all('/id="aimg_(\d+)"/i', $_G['thread']['freemessage'], $matches);
	if(!empty($matches[1]) && is_array($matches[1])) {
		$freeattachids = $matches[1];
	}
}
foreach(C::t('forum_attachment_n')->fetch_all_by_id('tid:'.$_G['tid'], 'tid', $_G['tid'], 'aid') as $attach) {
	if($_G['forum_threadpay'] && !in_array($attach['aid'], $freeattachids)) {
		continue;
	}
	if($attach['uid'] != $_G['forum_thread']['authorid'] && IN_MOBILE != 2) {
		continue;
	}
	if($attach['isimage'] && !$_G['setting']['attachimgpost']) {
		$attach['isimage'] = 0;
	}
	$attach['attachimg'] = $attach['isimage'] && (!$attach['readperm'] || $_G['group']['readaccess'] >= $attach['readperm']) ? 1 : 0;
	if(!$attach['attachimg']) {
		continue;
	}
	if($attach['price'] && $_G['uid'] != $attach['uid']) {
		$albumpayaids[$attach['aid']] = $attach['aid'];
		$attach['payed'] = 0;
	} else {
		$attach['payed'] = 1;
	}
	$attachmentlist[$attach['aid']] = $attach;
}

if($albumpayaids) {
	foreach(C::t('common_credit_log')->fetch_all_by_uid_operation_relatedid($_G['uid'], 'BAC', array_keys($albumpayaids)) as $creditlog) {
		$attachmentlist[$creditlog['relatedid']]['payed'] = 1;
	}
}

foreach($attachmentlist as $attach) {
	if($attach['payed'] == 0) {
		continue;
	}
	$attach['url'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'forum/';
	$attach['dbdateline'] = $attach['dateline'];
	$attach['dateline'] = dgmdate($attach['dateline'], 'u');
	$imglist['aid'][] = $attach['aid'];
	$imglist['url'][] = $attach['url'].$attach['attachment'];
	$apids[] = $attach['pid'];
}

if(empty($imglist)) {
	showmessage('author_not_uploadpic');
}

foreach($postlist as $key=>$subpost) {
	if($subpost['first'] == 1 || in_array($subpost['pid'], $apids)) {
		unset($postlist[$key]);
	}
}
?>