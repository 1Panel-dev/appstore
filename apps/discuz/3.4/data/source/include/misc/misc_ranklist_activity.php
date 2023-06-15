<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$activitylist = '';
$orderby = in_array($_GET['orderby'], array('thisweek', 'thismonth', 'today', 'all')) ? $_GET['orderby'] : '';
$navname = $_G['setting']['navs'][8]['navname'];
switch($_GET['view']) {
	case 'heats':
		$gettype = 'heat';
		break;
	case 'sharetimes':
		$gettype = 'share';
		break;
	case 'favtimes':
		$gettype = 'favorite';
		break;
	default: $_GET['view'] = 'heats';
}
$view = $_GET['view'];

$dateline = !empty($before) ? TIMESTAMP - $before : 0;

$activitylist = getranklistdata($type, $view, $orderby);
$lastupdate = $_G['lastupdate'];
$nextupdate = $_G['nextupdate'];

$navtitle = lang('ranklist/navtitle', 'ranklist_title_activity_'.$gettype).' - '.$navname;
$metakeywords = lang('ranklist/navtitle', 'ranklist_title_activity_'.$gettype);
$metadescription = lang('ranklist/navtitle', 'ranklist_title_activity_'.$gettype);

include template('diy:ranklist/activity');

?>