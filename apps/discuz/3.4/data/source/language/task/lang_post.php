<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_post.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$lang = array
(
	'post_name' => '论坛帖子类任务',
	'post_desc' => '通过发帖回帖完成任务，活跃论坛的氛围',
	'post_complete_var_act' => '动作',
	'post_complete_var_act_newthread' => '发新主题',
	'post_complete_var_act_newreply' => '发新回复',
	'post_complete_var_act_newpost' => '发新主题/回复',
	'post_complate_var_forumid' => '版块限制',
	'post_complate_var_forumid_comment' => '设置会员只能在某个版块完成任务',
	'post_complate_var_threadid' => '回复指定主题',
	'post_complate_var_threadid_comment' => '设置会员只有回复该主题才能完成任务，请填写主题的 TID',
	'post_complate_var_author' => '回复指定作者',
	'post_complate_var_author_comment' => '设置会员只有回复该作者发表的主题才能完成任务，请填写作者的用户名',
	'post_complete_var_num' => '执行动作次数下限',
	'post_complete_var_num_comment' => '会员需要执行相应动作的最少次数',
	'post_complete_var_time' => '时间限制(小时)',
	'post_complete_var_time_comment' => '设置会员从申请任务到完成任务的时间限制，会员在此时间内未能完成任务则不能领取奖励并标记任务失败，0 或留空为不限制',

	'task_complete_forumid' => '在版块 {value} ',
	'task_complete_act_newthread' => '发新主题 {num} 次',
	'task_complete_act_newpost' => '发新主题/回复 {num} 次',
	'task_complete_act_newreply_thread' => '在“{value}”回复主题 {num} 次',
	'task_complete_act_newreply_author' => '回复作者“{value}”的主题 {num} 次',
);

?>