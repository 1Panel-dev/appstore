<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_quickquery.php 20397 2011-02-23 03:24:20Z congyushuai $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$simplequeries = array(
	array('comment' => cplang('quickquery_open_forum'), 'sql' => ''),
	array('comment' => cplang('quickquery_open_recycle'), 'sql' => 'UPDATE {tablepre}forum_forum SET recyclebin=\'1\' WHERE status<\'3\''),
	array('comment' => cplang('quickquery_open_discuzcode'), 'sql' => 'UPDATE {tablepre}forum_forum SET allowbbcode=\'1\' WHERE status<\'3\''),
	array('comment' => cplang('quickquery_open_imgcode'), 'sql' => 'UPDATE {tablepre}forum_forum SET allowimgcode=\'1\' WHERE status<\'3\''),
	array('comment' => cplang('quickquery_open_smilies'), 'sql' => 'UPDATE {tablepre}forum_forum SET allowsmilies=\'1\' WHERE status<\'3\''),
	array('comment' => cplang('quickquery_open_jam'), 'sql' => 'UPDATE {tablepre}forum_forum SET jammer=\'1\' WHERE status<\'3\''),
	array('comment' => cplang('quickquery_open_guest'), 'sql' => 'UPDATE {tablepre}forum_forum SET allowanonymous=\'1\' WHERE status<\'3\''),

	array('comment' => cplang('quickquery_close_forum'), 'sql' => ''),
	array('comment' => cplang('quickquery_close_recycle'), 'sql' => 'UPDATE {tablepre}forum_forum SET recyclebin=\'0\' WHERE status<\'3\''),
	array('comment' => cplang('quickquery_close_html'), 'sql' => 'UPDATE {tablepre}forum_forum SET allowhtml=\'0\' WHERE status<\'3\''),
	array('comment' => cplang('quickquery_close_discuzcode'), 'sql' => 'UPDATE {tablepre}forum_forum SET allowbbcode=\'0\' WHERE status<\'3\''),
	array('comment' => cplang('quickquery_close_imgcode'), 'sql' => 'UPDATE {tablepre}forum_forum SET allowimgcode=\'0\' WHERE status<\'3\''),
	array('comment' => cplang('quickquery_close_smilies'), 'sql' => 'UPDATE {tablepre}forum_forum SET allowsmilies=\'0\' WHERE status<\'3\''),
	array('comment' => cplang('quickquery_close_jam'), 'sql' => 'UPDATE {tablepre}forum_forum SET jammer=\'0\' WHERE status<\'3\''),
	array('comment' => cplang('quickquery_close_guest'), 'sql' => 'UPDATE {tablepre}forum_forum SET allowanonymous=\'0\' WHERE status<\'3\''),

	array('comment' => cplang('quickquery_user'), 'sql' => ''),
	array('comment' => cplang('quickquery_clear_userlog'), 'sql' => 'TRUNCATE {tablepre}common_credit_log'),
);

?>