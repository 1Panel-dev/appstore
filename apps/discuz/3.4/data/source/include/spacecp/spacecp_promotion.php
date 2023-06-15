<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_promotion.php 25889 2011-11-24 09:52:20Z monkey $
 */
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!($_G['setting']['creditspolicy']['promotion_visit'] || $_G['setting']['creditspolicy']['promotion_register'])) {
	showmessage('action_closed', NULL);
}

loadcache('creditrule');

$visit = $_G['cache']['creditrule']['promotion_visit'];
$register = $_G['cache']['creditrule']['promotion_register'];
$copystr = lang('spacecp', 'invite_you_to_visit', array('user' => $_G['username'], 'bbname' => daddslashes($_G['setting']['bbname'])));

$visitstr = $regstr = '';
foreach($_G['setting']['extcredits'] as $key => $credit) {
	$creditkey = 'extcredits'.$key;
	if($visit[$creditkey]) {
		$visitstr .= $credit['title'].($visit[$creditkey] > 0 ? '+'.$visit[$creditkey] : $visit[$creditkey])."&nbsp;";
	}
	if($register[$creditkey]) {
		$regstr .= $credit['title'].($register[$creditkey] > 0 ? '+'.$register[$creditkey] : $register[$creditkey])."&nbsp;";
	}
}

include_once template("home/spacecp_promotion");
?>