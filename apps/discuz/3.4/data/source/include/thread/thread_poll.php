<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: thread_poll.php 36284 2016-12-12 00:47:50Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$polloptions = array();
$votersuid = '';
if($count = C::t('forum_polloption')->fetch_count_by_tid($_G['tid'])) {

	$options = C::t('forum_poll')->fetch($_G['tid']);
	if($options['isimage']) {
		$pollimages = C::t('forum_polloption_image')->fetch_all_by_tid($_G['tid']);
		require_once libfile('function/home');
	}
	$isimagepoll = $options['isimage'] ? true : false;
	$multiple = $options['multiple'];
	$visible = $options['visible'];
	$maxchoices = $options['maxchoices'];
	$expiration = $options['expiration'];
	$overt = $options['overt'];
	$voterscount = $options['voters'];

	$query = C::t('forum_polloption')->fetch_all_by_tid($_G['tid'], 1);
	$colors = array('E92725', 'F27B21', 'F2A61F', '5AAF4A', '42C4F5', '0099CC', '3365AE', '2A3591', '592D8E', 'DB3191');
	$voterids = $polloptionpreview = '';
	$ci = 0;
	$opts = 1;
	foreach($query as $options) {
		$viewvoteruid[] = $options['voterids'];
		$voterids .= "\t".$options['voterids'];
		$option = preg_replace("/\[url=(https?){1}:\/\/([^\[\"']+?)\](.+?)\[\/url\]/i", "<a href=\"\\1://\\2\" target=\"_blank\">\\3</a>", $options['polloption']);
		$attach = array();
		if($isimagepoll && $pollimages[$options['polloptionid']]) {
			$attach = $pollimages[$options['polloptionid']];
			$attach['small'] = pic_get($attach['attachment'], 'forum', $attach['thumb'], $attach['remote']);
			$attach['big'] = pic_get($attach['attachment'], 'forum', 0, $attach['remote']);
		}
		$polloptions[$opts++] = array
		(
			'polloptionid'	=> $options['polloptionid'],
			'polloption'	=> $option,
			'votes'		=> $options['votes'],
			'width'		=> $options['votes'] > 0 ? (@round($options['votes'] * 100 / $count['total'])).'%' : '8px',
			'percent'	=> $count['total'] ? sprintf("%01.2f", $options['votes'] * 100 / $count['total']) : 0,
			'color'		=> $colors[$ci],
			'imginfo'	=> $attach
		);
		if($ci < 2) {
			$polloptionpreview .= $option."\t";
		}
		$ci++;
		if($ci == count($colors)) {
			$ci = 0;
		}
	}

	$voterids = explode("\t", $voterids);
	$voters = array_unique($voterids);
	array_shift($voters);

	if(!$expiration) {
		$expirations = TIMESTAMP + 86400;
	} else {
		$expirations = $expiration;
		if($expirations > TIMESTAMP) {
			$_G['forum_thread']['remaintime'] = remaintime($expirations - TIMESTAMP);
		}
	}

	$allwvoteusergroup = $_G['group']['allowvote'];
	$allowvotepolled = !in_array(($_G['uid'] ? $_G['uid'] : $_G['clientip']), $voters);
	$allowvotethread = ($_G['forum_thread']['isgroup'] || !$_G['forum_thread']['closed'] && !checkautoclose($_G['forum_thread']) || $_G['group']['alloweditpoll']) && TIMESTAMP < $expirations && $expirations > 0;

	$_G['group']['allowvote'] = $allwvoteusergroup && $allowvotepolled && $allowvotethread;

	$optiontype = $multiple ? 'checkbox' : 'radio';
	$visiblepoll = $visible || $_G['forum']['ismoderator'] || ($_G['uid'] && $_G['uid'] == $_G['forum_thread']['authorid']) || ($expirations >= TIMESTAMP && in_array(($_G['uid'] ? $_G['uid'] : $_G['clientip']), $voters)) || $expirations < TIMESTAMP ? 0 : 1;

}

?>