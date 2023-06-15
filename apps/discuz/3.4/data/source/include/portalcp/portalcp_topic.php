<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portalcp_topic.php 27484 2012-02-02 05:08:02Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$allowmanage = $allowadd = 0;
if($_G['group']['allowaddtopic'] || $_G['group']['allowmanagetopic']) {
	$allowadd = 1;
}

$op = in_array($_GET['op'], array('edit')) ? $_GET['op'] : 'add';

$topicid = $_GET['topicid'] ? intval($_GET['topicid']) : 0;
$topic = '';
if($topicid) {
	$topic = C::t('portal_topic')->fetch($topicid);
	if(empty($topic)) {
		showmessage('topic_not_exist');
	}
	if($_G['group']['allowmanagetopic'] || ($_G['group']['allowaddtopic'] && $topic['uid'] == $_G['uid'])) {
		$allowmanage = 1;
	}
	$coverpath = $topic['picflag'] == '0' ? $topic['cover'] : '';

	if($topic['cover']) {
		if($topic['picflag'] == '1') {
			$topic['cover'] = $_G['setting']['attachurl'].$topic['cover'];
		} elseif ($topic['picflag'] == '2') {
			$topic['cover'] = $_G['setting']['ftp']['attachurl'].$topic['cover'];
		}
	}
}

if(($topicid && !$allowmanage) || (!$topicid && !$allowadd)) {
	showmessage('topic_edit_nopermission', dreferer());
}

$tpls = array();

foreach($alltemplate = C::t('common_template')->range() as $template) {
	if(($dir = dir(DISCUZ_ROOT.$template['directory'].'/portal/'))) {
		while(false !== ($file = $dir->read())) {
			$file = strtolower($file);
			if (fileext($file) == 'htm' && substr($file, 0, 13) == 'portal_topic_') {
				$tpls[$template['directory'].':portal/'.str_replace('.htm','',$file)] = getprimaltplname($template['directory'].':portal/'.$file);
			}
		}
	}
}

if (empty($tpls)) showmessage('topic_has_on_template', dreferer());

if(submitcheck('editsubmit')) {
	include_once libfile('function/portalcp');
	if(is_numeric($topicid = updatetopic($topic))){
		showmessage('do_success', 'portal.php?mod=topic&topicid='.$topicid);
	} else {
		showmessage($topicid, dreferer());
	}
}

include_once template("portal/portalcp_topic");


?>