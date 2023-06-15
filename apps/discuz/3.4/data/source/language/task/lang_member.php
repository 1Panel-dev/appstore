<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_member.php 29183 2012-03-28 06:39:26Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$lang = array
(
	'member_name' => '会员类任务',
	'member_desc' => '收藏主题、使用道具、添加漫游应用，此类任务用来鼓励和引导会员使用论坛的某个功能，活跃论坛的氛围',
	'member_complete_var_act' => '动作',
	'member_complete_var_act_favorite' => '收藏主题',
	'member_complete_var_act_magic' => '使用道具',
	'member_complete_var_act_userapp' => '添加漫游应用',
	'member_complete_var_num' => '执行动作次数下限',
	'member_complete_var_num_comment' => '会员需要执行相应动作的最少次数',
	'member_complete_var_time' => '时间限制(小时)',
	'member_complete_var_time_comment' => '设置会员从申请任务到完成任务的时间限制，会员在此时间内未能完成任务则不能领取奖励并标记任务失败，0 或留空为不限制',

	'task_complete_time_start' => '从申请任务开始计时，',
	'task_complete_time_limit' => '{value} 小时内，',
	'task_complete_act_favorite' => '收藏 {value} 个主题',
	'task_complete_act_magic' => '使用 {value} 次道具。<br />您可以在帖子页面、日志页面、图片页面等使用道具',
	'task_complete_act_userapp' => '添加 {value} 个漫游应用',
);

?>