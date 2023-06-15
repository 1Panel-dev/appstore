<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_bloglist.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$lang = array
(
	'bloglist_blogids' => '指定日志',
	'bloglist_blogids_comment' => '填入指定日志的ID(blogid)，多个日志之间用逗号(,)分隔',
	'bloglist_uids' => '作者UID',
	'bloglist_uids_comment' => '填入指定用户的ID(uid)，多个用户之间用逗号(,)分隔',
	'bloglist_catid' => '指定分类',
	'bloglist_catid_comment' => '选择日志所属的系统日志分类，可多选',
	'bloglist_startrow' => '起始数据行数',
	'bloglist_startrow_comment' => '如需设定起始的数据行数，请输入具体数值，0 为从第一行开始，以此类推',
	'bloglist_titlelength' => '标题长度',
	'bloglist_summarylength' => '简介长度',
	'bloglist_picrequired' => '过滤无封面日志',
	'bloglist_picrequired_comment' => '是否过滤没有封面图片的日志',
	'bloglist_hours' => '时间范围',
	'bloglist_hours_nolimit' => '不限制',
	'bloglist_hours_hour' => '1小时内',
	'bloglist_hours_day' => '24小时内',
	'bloglist_hours_week' => '7天内',
	'bloglist_hours_month' => '1月内',
	'bloglist_hours_year' => '1年内',
	'bloglist_orderby' => '日志排序方式',
	'bloglist_orderby_comment' => '设置以哪一字段或方式对日志进行排序',
	'bloglist_orderby_dateline' => '按发布时间倒序',
	'bloglist_orderby_viewnum' => '按查看数倒序',
	'bloglist_orderby_replynum' => '按回复数倒序',
	'bloglist_orderby_hot' => '按热度倒序'
);

?>