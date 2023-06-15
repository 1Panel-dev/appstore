<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_topiclist.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$lang = array
(
	'topiclist_topicids' => '指定专题',
	'topiclist_topicids_comment' => '填入指定专题的ID(topicid)，多个专题之间用逗号(,)分隔',
	'topiclist_uids' => '创建者UID',
	'topiclist_uids_comment' => '填入指定专题创建者的ID(uid)，多个用户之间用逗号(,)分隔',
	'topiclist_startrow' => '起始数据行数',
	'topiclist_startrow_comment' => '如需设定起始的数据行数，请输入具体数值，0 为从第一行开始，以此类推',
	'topiclist_titlelength' => '标题长度',
	'topiclist_titlelength_comment' => '指定专题标题最大长度',
	'topiclist_summarylength' => '介绍长度',
	'topiclist_summarylength_comment' => '指定专题介绍最大长度',
	'topiclist_picrequired' => '过滤无封面专题',
	'topiclist_picrequired_comment' => '是否过滤没有封面图片的专题',
	'topiclist_orderby' => '专题排序方式',
	'topiclist_orderby_comment' => '设置以哪一字段或方式对专题进行排序',
	'topiclist_orderby_dateline' => '按发布时间倒序',
	'topiclist_orderby_viewnum' => '按查看数倒序',
);

?>