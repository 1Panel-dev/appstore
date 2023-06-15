<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: optimizer_seo.php 31344 2012-08-15 04:01:32Z zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class optimizer_seo {

	public function __construct() {

	}

	public function check() {
		$seotitle = C::t('common_setting')->fetch('seotitle', true);
		$seokeywords = C::t('common_setting')->fetch('seokeywords', true);
		$seodescription = C::t('common_setting')->fetch('seodescription', true);
		$rewritestatus = C::t('common_setting')->fetch('rewritestatus', true);
		if(!$seotitle || !$seokeywords || !$seodescription || !$rewritestatus) {
			$return = array('status' => 1, 'type' =>'header', 'lang' => lang('optimizer', 'optimizer_seo_advice'));
		} else {
			$return = array('status' => 0, 'type' =>'none', 'lang' => lang('optimizer', 'optimizer_seo_no_need'));
		}
		return $return;
	}

	public function optimizer() {
		$adminfile = defined(ADMINSCRIPT) ? ADMINSCRIPT : 'admin.php';
		dheader('Location: '.$_G['siteurl'].$adminfile.'?action=setting&operation=seo');
	}
}

?>