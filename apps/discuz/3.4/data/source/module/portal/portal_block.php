<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portal_block.php 24984 2011-10-20 07:59:00Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$bid = max(0,intval($_GET['bid']));
if(empty($bid)) {
	showmessage('block_choose_bid', dreferer());
}
block_get($bid);
if(!isset($_G['block'][$bid])) {
	showmessage('block_noexist', dreferer());
}
$block = &$_G['block'][$bid];
$blockmoreurl = $block['param']['moreurl'] = isset($block['param']['moreurl']) ? $block['param']['moreurl'] :
				array('perpage' => 20, 'seotitle' => $block['name'], 'keywords' => '', 'description' => '');
$blocktype = $block['blockclass'];
if(!in_array($blocktype, array('forum_thread', 'portal_article', 'group_thread'), true)) {
	showmessage('block_nomore', dreferer());
}

$perpage = max(1, intval($blockmoreurl['perpage']));
$curpage = max(1, intval($_GET['page']));
$start = ($curpage-1) * $perpage;
$count = C::t('common_block_item_data')->count_by_bid($bid);
$list = $count ? C::t('common_block_item_data')->fetch_all_by_bid($bid, 1, $start, $perpage) : array();
$multipage = $count ? multi($count, $perpage, $curpage, 'portal.php?mod=block&bid='.$bid) : '';

$navtitle = $blockmoreurl['seotitle'];
$metakeywords = $blockmoreurl['seokeywords'];
$metadescription = $blockmoreurl['seodescription'];

$file = 'portal/block_more_'.$blocktype;
include template('diy:'.$file);

?>