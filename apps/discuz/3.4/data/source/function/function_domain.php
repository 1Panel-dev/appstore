<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_domain.php 24601 2011-09-27 12:26:41Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function domaincheck($domain, $domainroot, $domainlength, $msgtype = 1) {

	if(strlen($domain) < $domainlength) {
		showmessage('domain_length_error', '', array('length' => $domainlength), array('return' => true));
	}
	if(strlen($domain) > 30) {
		$msgtype ? showmessage('two_domain_length_not_more_than_30_characters', '', array(), array('return' => true)) : cpmsg('two_domain_length_not_more_than_30_characters', '', 'error');
	}
	if(!preg_match("/^[a-z0-9]*$/", $domain)) {
		$msgtype ? showmessage('only_two_names_from_english_composition_and_figures', '', array(), array('return' => true)) : cpmsg('only_two_names_from_english_composition_and_figures', '', 'error');
	}

	if($msgtype && isholddomain($domain)) {
		showmessage('domain_be_retained', '', array(), array('return' => true));
	}

	if(existdomain($domain, $domainroot)) {
		$msgtype ? showmessage('two_domain_have_been_occupied', '', array(), array('return' => true)) : cpmsg('two_domain_have_been_occupied', '', 'error');
	}

	return true;
}

function isholddomain($domain) {
	global $_G;

	$domain = strtolower($domain);
	$holdmainarr = empty($_G['setting']['holddomain'])?array('www'):explode('|', $_G['setting']['holddomain']);
	$ishold = false;
	foreach ($holdmainarr as $value) {
		if(strpos($value, '*') === false) {
			if(strtolower($value) == $domain) {
				$ishold = true;
				break;
			}
		} else {
			$value = str_replace('*', '.*?', $value);
			if(@preg_match("/$value/i", $domain)) {
				$ishold = true;
				break;
			}
		}
	}
	return $ishold;
}

function existdomain($domain, $domainroot) {
	global $_G;

	$exist = false;
	if(C::t('common_domain')->count_by_domain_domainroot($domain, $domainroot)) {
		$exist = true;
	}
	return $exist;
}
?>