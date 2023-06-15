<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_articlelist.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$lang = array
(
	'articlelist_aids' => '指定文章',
	'articlelist_aids_comment' => '填入指定文章的ID(aid)，多篇文章之间用逗号(,)分隔',
	'articlelist_uids' => '作者UID',
	'articlelist_uids_comment' => '填入指定用户的ID(uid)，多个用户之间用逗号(,)分隔',
	'articlelist_startrow' => '起始数据行数',
	'articlelist_startrow_comment' => '如需设定起始的数据行数，请输入具体数值，0 为从第一行开始，以此类推',
	'articlelist_tag' => '聚合标签',
	'articlelist_tag_comment' => '指定要聚合的标签',
	'articlelist_titlelength' => '标题长度',
	'articlelist_titlelength_comment' => '设置标题最大长度',
	'articlelist_summarylength' => '简介长度',
	'articlelist_summarylength_comment' => '设置简介最大长度',
	'articlelist_starttime' => '发布时间-起始',
	'articlelist_starttime_comment' => '文章的发布时间在指定时间之后',
	'articlelist_endtime' => '发布时间-结束',
	'articlelist_endtime_comment' => '文章的发布时间在指定时间之前',
	'articlelist_catid' => '文章栏目',
	'articlelist_catid_comment' => '选择文章所属栏目',
	'articlelist_picrequired' => '过滤无封面文章',
	'articlelist_picrequired_comment' => '是否过滤未设置封面图片的文章',
	'articlelist_orderby' => '文章排序方式',
	'articlelist_orderby_comment' => '设置以哪一字段或方式对文章进行排序',
	'articlelist_orderby_dateline' => '按发布时间倒序',
	'articlelist_orderby_viewnum' => '按查看数倒序',
	'articlelist_orderby_commentnum' => '按评论数倒序',
	'articlelist_orderby_click' => '按表态 {clickname} 数倒序',
	'articlelist_publishdateline' => '文章发布时间',
	'articlelist_publishdateline_nolimit' => '不限制',
	'articlelist_publishdateline_hour' => '1小时内',
	'articlelist_publishdateline_day' => '24小时内',
	'articlelist_publishdateline_week' => '7天内',
	'articlelist_publishdateline_month' => '1个月内',
	'articlelist_keyword' => '标题关键字',
	'articlelist_keyword_comment' => '设置标题包含的关键字。注意: 留空为不进行任何过滤； 关键字中可使用通配符 *； 匹配多个关键字全部，可用空格或 AND 连接。如 win32 AND unix； 匹配多个关键字其中部分，可用 | 或 OR 连接。如 win32 OR unix',
);

?>