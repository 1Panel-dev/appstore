<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: misc_userstatus.php 32720 2013-03-04 10:21:58Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$output = array();
$output['uid'] = $_G['uid'];
$tablename = '';
if($_GET['type'] === 'topic' && $typeid = intval($_GET['typeid'])) {
	$tablename = 'portal_topic';
	C::t('portal_topic')->increase($typeid, array('viewnum' => 1));
} elseif($_GET['type'] === 'article' && $typeid = intval($_GET['typeid'])) {
	C::t('portal_article_count')->increase($typeid, array('viewnum' => 1));
	$tablename = 'portal_article_count';
}
if($tablename) {
	$dynamicdata = C::t($tablename)->fetch($typeid);
	$output['viewnum'] = $dynamicdata['viewnum'];
	$output['commentnum'] = $dynamicdata['commentnum'];
}
if($output['uid']) {
	$_G['style']['tplfile'] = 'misc/userstatus';
	if(check_diy_perm($topic)){
		require template('common/header_diynav');
		echo $diynav;
	}
	$output['diynav'] = str_replace(array("\r", "\n"), '' ,ob_get_contents());
	ob_end_clean();
	$_G['gzipcompress'] ? ob_start('ob_gzhandler') : ob_start();

	require template('common/header_userstatus');
	$output['userstatus'] = str_replace(array("\r", "\n"), '' ,ob_get_contents());
	ob_end_clean();
	$_G['gzipcompress'] ? ob_start('ob_gzhandler') : ob_start();

	require template('common/header_qmenu');
	$output['qmenu'] = str_replace(array("\r", "\n"), '' ,ob_get_contents());
	ob_end_clean();
	$_G['gzipcompress'] ? ob_start('ob_gzhandler') : ob_start();
}

header("Content-Type: application/json");
echo helper_json::encode($output);

?>