<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$threadlist = '';
$orderby = in_array($_GET['orderby'], array('thisweek', 'thismonth', 'today', 'all')) ? $_GET['orderby'] : '';
$navname = $_G['setting']['navs'][8]['navname'];
switch($_GET['view']) {
	case 'replies':
		$gettype = 'reply';
		break;
	case 'views':
		$gettype = 'view';
		break;
	case 'sharetimes':
		$gettype = 'share';
		break;
	case 'favtimes':
		$gettype = 'favorite';
		break;
	case 'heats':
		$gettype = 'heat';
		break;
	default: $_GET['view'] = 'replies';
}
$view = $_GET['view'];

$threadlist = getranklistdata($type, $view, $orderby);
$lastupdate = $_G['lastupdate'];
$nextupdate = $_G['nextupdate'];

$navtitle = lang('ranklist/navtitle', 'ranklist_title_thread_'.$gettype).' - '.$navname;
$metakeywords = lang('ranklist/navtitle', 'ranklist_title_thread_'.$gettype);
$metadescription = lang('ranklist/navtitle', 'ranklist_title_thread_'.$gettype);

include template('diy:ranklist/thread');

?>