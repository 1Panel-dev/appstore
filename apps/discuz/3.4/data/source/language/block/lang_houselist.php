<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_houselist.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$lang = array
(
	'categorylist_fids' => '所在版块',
	'categorylist_fids_comment' => '设置允许参与新帖调用的版块，可以按住 CTRL 多选，全选或全不选均为不做限制',
	'categorylist_startrow' => '起始数据行数',
	'categorylist_startrow_comment' => '如需设定起始的数据行数，请输入具体数值，0 为从第一行开始，以此类推',
	'categorylist_showitems' => '显示数据条数',
	'categorylist_showitems_comment' => '设置一次显示的主题条目数，请设置为大于 0 的整数',
	'categorylist_titlelength' => '标题最大字节数',
	'categorylist_titlelength_comment' => '设置当标题长度超过本设定时，是否将标题自动缩减到本设定中的字节数，0 为不自动缩减',
	'categorylist_fnamelength' => '标题最大字节数包含版块名称',
	'categorylist_fnamelength_comment' => '设置标题长度是否将所在版块名称的长度一同计算在内',
	'categorylist_summarylength' => '主题简短内容文字数',
	'categorylist_summarylength_comment' => '设置主题简短内容的文字数，0 为使用默认值 255',
	'categorylist_tids' => '指定主题',
	'categorylist_tids_comment' => '设置要指定显示的主题 tid ，多个 tid 请用半角逗号“,”隔开。注意: 留空为不进行任何过滤',
	'categorylist_keyword' => '标题关键字',
	'categorylist_keyword_comment' => '设置标题包含的关键字。注意: 留空为不进行任何过滤； 关键字中可使用通配符 *； 匹配多个关键字全部，可用空格或 AND 连接。如 win32 AND unix； 匹配多个关键字其中部分，可用 | 或 OR 连接。如 win32 OR unix',
	'categorylist_sortids' => '分类信息',
	'categorylist_sortids_comment' => '设置特定分类的主题。注意: 全选或全不选均为不进行任何过滤',
	'categorylist_styleids' => '分类模板',
	'categorylist_styleids_comment' => '在后台对应的分类信息里面设置模板',
	'categorylist_styleids_style1' => '样式1',
	'categorylist_styleids_style2' => '样式2',
	'categorylist_styleids_style3' => '样式3',
	'categorylist_styleids_style4' => '样式4',
	'categorylist_styleids_style5' => '样式5',
	'categorylist_typeids_all' => '全部的主题分类',
	'categorylist_categoryids' => '分类信息',
	'categorylist_categoryids_comment' => '设置特定分类信息的主题。注意: 全选或全不选均为不进行任何过滤',
	'categorylist_categoryids_all' => '全部的分类信息',
	'categorylist_digest' => '精华主题过滤',
	'categorylist_digest_comment' => '设置特定的主题范围。注意: 全选或全不选均为不进行任何过滤',
	'categorylist_digest_0' => '普通主题',
	'categorylist_digest_1' => '精华 I',
	'categorylist_digest_2' => '精华 II',
	'categorylist_digest_3' => '精华 III',
	'categorylist_stick' => '置顶主题过滤',
	'categorylist_stick_comment' => '设置特定的主题范围。注意: 全选或全不选均为不进行任何过滤',
	'categorylist_stick_0' => '普通主题',
	'categorylist_stick_1' => '置顶 I',
	'categorylist_stick_2' => '置顶 II',
	'categorylist_stick_3' => '置顶 III',
	'categorylist_special' => '特殊主题过滤',
	'categorylist_special_comment' => '设置特定的主题范围。注意: 全选或全不选均为不进行任何过滤',
	'categorylist_special_1' => '投票主题',
	'categorylist_special_2' => '商品主题',
	'categorylist_special_3' => '悬赏主题',
	'categorylist_special_4' => '活动主题',
	'categorylist_special_5' => '辩论主题',
	'categorylist_special_0' => '普通主题',
	'categorylist_special_reward' => '悬赏主题过滤',
	'categorylist_special_reward_comment' => '设置特定类型的悬赏主题',
	'categorylist_special_reward_0' => '全部',
	'categorylist_special_reward_1' => '已解决',
	'categorylist_special_reward_2' => '未解决',
	'categorylist_recommend' => '推荐主题过滤',
	'categorylist_recommend_comment' => '设置是否只显示推荐的主题',
	'categorylist_orderby' => '主题排序方式',
	'categorylist_orderby_comment' => '设置以哪一字段或方式对主题进行排序',
	'categorylist_orderby_lastpost' => '按最后回复时间倒序排序',
	'categorylist_orderby_dateline' => '按发布时间倒序排序',
	'categorylist_orderby_replies' => '按回复数倒序排序',
	'categorylist_orderby_views' => '按浏览次数倒序排序',
	'categorylist_orderby_heats' => '按热度倒序排序',
	'categorylist_orderby_recommends' => '按主题评价倒序排序',
	'categorylist_orderby_hourviews' => '按指定时间内浏览次数倒序排序',
	'categorylist_orderby_todayviews' => '按当天浏览次数倒序排序',
	'categorylist_orderby_weekviews' => '按本周浏览次数倒序排序',
	'categorylist_orderby_monthviews' => '按当月浏览次数倒序排序',
	'categorylist_orderby_hours' => '指定时间(小时)',
	'categorylist_orderby_hours_comment' => '指定时间内浏览次数倒序排序的时间值',
);

?>