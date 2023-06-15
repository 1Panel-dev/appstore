<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_ranklist_forum.php 25889 2011-11-24 09:52:20Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$forumsrank = '';
$view = 'threads';
$navname = $_G['setting']['navs'][8]['navname'];
switch($_GET['view']) {
	case 'posts':
		$gettype = 'post';
		break;
	case 'thismonth':
		$gettype = 'post_30';
		break;
	case 'today':
		$gettype = 'post_24';
		break;
	case 'threads':
		$gettype = 'thread';
		break;
	default: $_GET['view'] = 'threads';
}
$view = $_GET['view'];
$forumsrank = getranklistdata($type, $view);
$lastupdate = $_G['lastupdate'];
$nextupdate = $_G['nextupdate'];

$navtitle = lang('ranklist/navtitle', 'ranklist_title_forum_'.$gettype).' - '.$navname;
$metakeywords = lang('ranklist/navtitle', 'ranklist_title_forum_'.$gettype);
$metadescription = lang('ranklist/navtitle', 'ranklist_title_forum_'.$gettype);

include template('diy:ranklist/forum');

?>