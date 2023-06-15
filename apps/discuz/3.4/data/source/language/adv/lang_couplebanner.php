<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_couplebanner.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$lang = array
(
	'couplebanner_name' => '全局 对联广告',
	'couplebanner_desc' => '展现方式: 对联广告以长方形图片的形式显示于页面顶部两侧，形似一幅对联，通常使用宽小高大的长方形图片或 Flash 的形式。对联广告一般只在使用像素约定主表格宽度的情况下使用，如使用超过 90% 以上的百分比约定主表格宽度时，可能会影响访问者的正常浏览。当访问者浏览器宽度小于 800 像素时，自动不显示此类广告。当前页面有多个对联广告时，系统会随机选取其中之一显示。<br />价值分析: 对联广告由于只展现于高分辨率(1024x768 或更高)屏幕的两侧，只占用页面的空白区域，因此不会招致访问者反感，能够良好的突出推广内容。但由于对分辨率和主表格宽度的特殊要求，使得广告的受众比例无法达到 100%。',
	'couplebanner_index' => '首页',
	'couplebanner_fids' => '投放版块',
	'couplebanner_fids_comment' => '设置广告投放的论坛版块',
	'couplebanner_groups' => '投放群组分类',
	'couplebanner_groups_comment' => '设置广告投放的群组分类，当广告投放范围中包含“群组”时有效',
	'couplebanner_position' => '对联位置',
	'couplebanner_position_comment' => '设置广告对联的位置',
	'couplebanner_position_left' => '左侧',
	'couplebanner_position_right' => '右侧',
	'couplebanner_coupleadid' => '上/下联广告',
	'couplebanner_coupleadid_comment' => '设置和当前广告配对的上/下联广告',
	'couplebanner_category' => '投放门户频道',
	'couplebanner_category_comment' => '设置广告投放的频道分类，当广告投放范围中包含“门户”时有效',
	'couplebanner_disableclose' => '关闭广告的链接',
	'couplebanner_disableclose_comment' => '如果广告代码中已内置关闭操作，可以关闭系统预置的关闭链接',
	'couplebanner_show' => '显示',
	'couplebanner_hidden' => '隐藏',
);

?>