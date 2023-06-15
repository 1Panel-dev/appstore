<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: optimizer_forumstatus.php 33594 2013-07-12 07:38:33Z jeffjzhang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class optimizer_forumstatus {

	public function __construct() {

	}

	public function check() {
		$forumlist = C::t('forum_forum')->fetch_all_by_status(0, 0);
		if(empty($forumlist)) {
			$return = array('status' => 0, 'type' =>'none', 'lang' => lang('optimizer', 'optimizer_forumstatus_no_need'));
		} else {
			$fids = array();
			foreach($forumlist as $forum) {
				$fids[] = $forum['fid'];
			}
			$forumfieldlist = C::t('forum_forumfield')->fetch_all_by_fid($fids);
			$forumdesc = '';
			$adminfile = defined(ADMINSCRIPT) ? ADMINSCRIPT : 'admin.php';
			$k = 1;
			foreach($forumlist as $forum) {
				if(!$forumfieldlist[$forum['fid']]['password'] || !$forumfieldlist[$forum['fid']]['formulaperm']) {
					$forumdesc .= '<p class="forumstatuslist" '.($k > 2 ? 'style="display:none;"' : '').'><a href="'.$adminfile.'?action=forums&operation=edit&fid='.$forum['fid'].'" target="_blank">'.$forum['name'].'</a></p>';
				}
				if($k == 3) {
					$forumdesc .= '<p id="forumstatusmore"><a href="javascript:;" onclick="showlistmore(\\\'forumstatusmore\\\',\\\'forumstatuslist\\\');">'.lang('admincp', 'more').'</a></p>';
				}
				$k++;
			}
			$extraurl = '';
			if(count($forumlist) == 1) {
				$extraurl = '&optimizefid='.$forum['fid'];
				$forumdesc = '';
			}
			if(!$forumdesc) {
				$return = array('status' => 0, 'type' =>'none', 'lang' => lang('optimizer', 'optimizer_forumstatus_no_need'));
			} else {
				$return = array('status' => 1, 'type' =>'view', 'lang' => lang('optimizer', 'optimizer_forumstatus_need', array('forumdesc' => $forumdesc)), 'extraurl' => $extraurl);
			}
		}
		return $return;
	}

	public function optimizer() {
		$adminfile = defined(ADMINSCRIPT) ? ADMINSCRIPT : 'admin.php';
		if($_GET['optimizefid']) {
			$url = '?action=forums&operation=edit&fid='.dintval($_GET['optimizefid']);
		} else {
			$url = '?action=forums';
		}
		dheader('Location: '.$_G['siteurl'].$adminfile.$url);
	}
}

?>