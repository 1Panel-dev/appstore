<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_threadlist.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$lang = array
(
	'threadlist_name' => '论坛/群组 帖子列表帖位广告',
	'threadlist_desc' => '展现方式: 帖位广告显示于帖子列表页第一页的帖子位置，可以模拟出一个具有广告意义的帖子地址，吸引访问者的注意力。',
	'threadlist_fids' => '投放版块',
	'threadlist_fids_comment' => '设置广告投放的论坛版块，当广告投放范围中包含“论坛”时有效',
	'threadlist_groups' => '投放群组分类',
	'threadlist_groups_comment' => '设置广告投放的群组分类，当广告投放范围中包含“群组”时有效',
	'threadlist_pos' => '投放位置',
	'threadlist_pos_comment' => '设置在主题列表的第几个主题位置显示此广告，如不指定则将随机位置显示',
	'threadlist_mode' => '显示模式',
	'threadlist_mode_comment' => '自由模式，占用主题列表的全部列宽显示本广告<br />主题模式，把广告模拟成一个主题，点击广告后跳转到指定的主题',
	'threadlist_mode_0' => '自由模式',
	'threadlist_mode_1' => '主题模式',
	'threadlist_tid' => '主题模式指定主题 tid',
	'threadlist_threadurl' => '主题模式自定义主题 URL',
	'threadlist_threadurl_comment' => '留空表示使用指定主题的 URL',
);

?>