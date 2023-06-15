<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_footerbanner.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$lang = array
(
	'footerbanner_name' => '全局 页尾通栏广告',
	'footerbanner_desc' => '展现方式: 页尾通栏广告显示于页面下方，通常使用 960x60 或其他尺寸图片、Flash 的形式。当前页面有多个页尾通栏广告时，系统会随机选取其中之一显示。<br />价值分析: 与页面头部和中部相比，页面尾部的展现机率相对较低，通常不会引起访问者的反感，同时又基本能够覆盖所有对广告内容感兴趣的受众，因此适合中性而温和的推广。',
	'footerbanner_index' => '首页',
	'footerbanner_fids' => '投放版块',
	'footerbanner_fids_comment' => '设置广告投放的论坛版块，当广告投放范围中包含“论坛”时有效',
	'footerbanner_groups' => '投放群组分类',
	'footerbanner_groups_comment' => '设置广告投放的群组分类，当广告投放范围中包含“群组”时有效',
	'footerbanner_position' => '投放位置',
	'footerbanner_position_comment' => '分为上中下 3 个位置，当上面的广告到期或被删除，下面的广告会自动上移',
	'footerbanner_position_up' => '上',
	'footerbanner_position_middle' => '中',
	'footerbanner_position_down' => '下',
	'footerbanner_category' => '投放门户频道',
	'footerbanner_category_comment' => '设置广告投放的频道分类，当广告投放范围中包含“门户”时有效',
);

?>