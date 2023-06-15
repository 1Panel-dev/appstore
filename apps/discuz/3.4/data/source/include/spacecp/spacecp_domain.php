<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_domain.php 24601 2011-09-27 12:26:41Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$domainlength = checkperm('domainlength');

if($_G['setting']['allowspacedomain'] && !empty($_G['setting']['domain']['root']['home']) && $domainlength) {
	checklowerlimit('modifydomain');
} else {
	showmessage('no_privilege_spacedomain');
}

if(submitcheck('domainsubmit')) {

	$setarr = array();
	$_POST['domain'] = strtolower(trim($_POST['domain']));
	if($_POST['domain'] != $space['domain']) {

		if(empty($domainlength) || empty($_POST['domain'])) {
			$setarr['domain'] = '';
		} else {
			require_once libfile('function/domain');
			if(domaincheck($_POST['domain'], $_G['setting']['domain']['root']['home'], $domainlength)) {
				$setarr['domain'] = $_POST['domain'];
			}
		}
	}
	if($setarr) {
		updatecreditbyaction('modifydomain');
		C::t('common_member_field_home')->update($_G['uid'], $setarr);
		require_once libfile('function/delete');
		deletedomain($_G['uid'], 'home');
		if(!empty($setarr['domain'])) {
			C::t('common_domain')->insert(array('domain' => $setarr['domain'], 'domainroot' => $_G['setting']['domain']['root']['home'], 'id' => $_G['uid'], 'idtype' => 'home'));
		}
	}

	showmessage('domain_succeed', 'home.php?mod=spacecp&ac=domain');
}

loadcache('creditrule');
getuserprofile('extcredits1');
$rule = $_G['cache']['creditrule']['modifydomain'];

$credits = $consume = $common = '';
for($i = 1; $i <= 8; $i++) {
	if($_G['setting']['extcredits'][$i] && $rule['extcredits'.$i]) {
		$consume .= $common.$_G['setting']['extcredits'][$i]['title'].$rule['extcredits'.$i].$_G['setting']['extcredits'][$i]['unit'];
		$credits .= $common.$_G['setting']['extcredits'][$i]['title'].$_G['member']['extcredits'.$i].$_G['setting']['extcredits'][$i]['unit'];
		$common = ',';
	}
}
$defaultop = '';
$profilegroup = C::t('common_setting')->fetch('profilegroup', true);

$actives = array('profile' =>' class="a"');
$opactives = array('domain' =>' class="a"');

include_once template("home/spacecp_domain");

?>