<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_forumlinks.php 28612 2012-03-06 08:10:47Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_forumlinks() {
	global $_G;

	$data = array();
	$query = C::t('common_friendlink')->fetch_all_by_displayorder();

	if($_G['setting']['forumlinkstatus']) {
		$tightlink_content = $tightlink_text = $tightlink_logo = $comma = '';
		foreach ($query as $flink) {
			if($flink['description']) {
				if($flink['logo']) {
					$tightlink_content .= '<li class="lk_logo mbm bbda cl"><img src="'.$flink['logo'].'" border="0" alt="'.strip_tags($flink['name']).'" /><div class="lk_content z"><h5><a href="'.$flink['url'].'" target="_blank">'.$flink['name'].'</a></h5><p>'.$flink['description'].'</p></div></li>';
				} else {
					$tightlink_content .= '<li class="mbm bbda"><div class="lk_content"><h5><a href="'.$flink['url'].'" target="_blank">'.$flink['name'].'</a></h5><p>'.$flink['description'].'</p></div></li>';
				}
			} else {
				if($flink['logo']) {
					$tightlink_logo .= '<a href="'.$flink['url'].'" target="_blank"><img src="'.$flink['logo'].'" border="0" alt="'.strip_tags($flink['name']).'" /></a> ';
				} else {
					$tightlink_text .= '<li><a href="'.$flink['url'].'" target="_blank" title="'.strip_tags($flink['name']).'">'.$flink['name'].'</a></li>';
				}
			}
		}
		$data = array($tightlink_content, $tightlink_logo, $tightlink_text);
	}

	savecache('forumlinks', $data);
}

?>