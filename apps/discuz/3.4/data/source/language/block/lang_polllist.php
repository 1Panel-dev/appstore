<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_polllist.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$lang = array
(
	'polllist_name' => '投票列表',
	'polllist_desc' => '投票列表调用',
	'polllist_uids' => '用户UID',
	'polllist_uids_comment' => '填入指定用户的ID(uid)，多个用户之间用逗号(,)分隔',
	'polllist_startrow' => '起始数据行数',
	'polllist_startrow_comment' => '如需设定起始的数据行数，请输入具体数值，0 为从第一行开始，以此类推',
	'polllist_titlelength' => '标题长度',
	'polllist_summarylength' => '简介长度',
	'polllist_orderby' => '投票排序方式',
	'polllist_orderby_comment' => '设置以哪一字段或方式对投票进行排序',
	'polllist_orderby_dateline' => '按发布时间倒序',
	'polllist_orderby_hot' => '按热度倒序',
	'polllist_orderby_lastvote' => '按最后投票时间倒序',
	'polllist_orderby_viewnum' => '按查看数倒序',
	'polllist_orderby_replynum' => '按回复数倒序',
	'polllist_orderby_votenum' => '按投票数倒序',
	'polllist_credit' => '悬赏投票',
	'polllist_credit_nolimit' => '不限定',
	'polllist_credit_yes' => '只取悬赏投票',
	'polllist_expirefilter' => '过期投票',
	'polllist_expirefilter_off' => '不屏蔽',
	'polllist_expirefilter_on' => '屏蔽',

);

?>