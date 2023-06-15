<?php

/**
 *		[Discuz! X] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: connect.class.php 34497 2014-05-09 09:05:09Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class plugin_qqconnect_base {

	public $retryInterval = 60;
	public $retryMax = 5;
	public $retryAvaiableTime = 1800;

	function init() {
		global $_G;
		if(!defined('IN_MOBILE')) {
			include_once template('qqconnect:module');
		}
		if(!$_G['setting']['connect']['allow'] || $_G['setting']['bbclosed']) {
			return;
		}
		$this->allow = true;
	}

	function common_base() {
		global $_G;

		if(!isset($_G['connect'])) {
			$_G['connect']['qzone_public_share_url'] = 'https://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey';
			$_G['connect']['referer'] = !$_G['inajax'] && CURSCRIPT != 'member' ? $_G['basefilename'].($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : '') : dreferer();

			$_G['connect']['login_url'] = $_G['siteurl'].'connect.php?mod=login&op=init&referer='.urlencode($_G['connect']['referer'] ? $_G['connect']['referer'] : 'index.php');
			$_G['connect']['callback_url'] = $_G['siteurl'].'connect.php?mod=login&op=callback';
			$_G['connect']['discuz_new_feed_url'] = $_G['siteurl'].'connect.php?mod=feed&op=new&formhash=' . formhash();
			$_G['connect']['discuz_new_post_feed_url'] = $_G['siteurl'].'connect.php?mod=feed&op=new&action=post&formhash=' . formhash();
			$_G['connect']['discuz_new_share_url'] = $_G['siteurl'].'home.php?mod=spacecp&ac=plugin&id=qqconnect:spacecp&pluginop=new';
			$_G['connect']['discuz_sync_tthread_url'] = $_G['siteurl'].'home.php?mod=spacecp&ac=plugin&id=qqconnect:spacecp&pluginop=sync_tthread&formhash=' . formhash();
			$_G['connect']['discuz_change_qq_url'] = $_G['siteurl'].'connect.php?mod=login&op=change';
			$_G['connect']['auth_fields'] = array(
				'is_user_info' => 1,
				'is_feed' => 0,
			);

			if($_G['uid']) {
				dsetcookie('connect_is_bind', $_G['member']['conisbind'], 31536000);
				if(!$_G['member']['conisbind'] && $_G['cookie']['connect_login']) {
					$_G['cookie']['connect_login'] = 0;
					dsetcookie('connect_login');
				}
			}

			if (!$_G['uid'] && $_G['connectguest']) {
				if ($_G['cookie']['connect_qq_nick']) {
					$_G['member']['username'] = $_G['cookie']['connect_qq_nick'];
				} else {
					$connectGuest = C::t('#qqconnect#common_connect_guest')->fetch($conopenid);
					if ($connectGuest['conqqnick']) {
						$_G['member']['username'] = $connectGuest['conqqnick'];
					}
				}
			}

			if($this->allow && !$_G['uid'] && !defined('IN_MOBILE')) {
				$_G['setting']['pluginhooks']['global_login_text'] = tpl_login_bar();
			}
		}
	}

}

class plugin_qqconnect extends plugin_qqconnect_base {

	var $allow = false;

	function plugin_qqconnect() {
		$this->init();
	}

	function common() {
		$this->common_base();
	}

	function discuzcode($param) {
		global $_G;
		if($param['caller'] == 'discuzcode') {
			$_G['discuzcodemessage'] = preg_replace('/\[wb=(.+?)\](.+?)\[\/wb\]/', '', $_G['discuzcodemessage']);
		}
		if($param['caller'] == 'messagecutstr') {
			$_G['discuzcodemessage'] = preg_replace('/\[tthread=(.+?)\](.*?)\[\/tthread\]/', '', $_G['discuzcodemessage']);
		}
	}

	function global_login_extra() {
        global $_G;
		if(!$this->allow) {
			return;
		}
		return tpl_global_login_extra();
	}

	function global_usernav_extra1() {
		global $_G;
		if(!$this->allow) {
			return;
		}
		if (!$_G['uid'] && !$_G['connectguest']) {
			return;
		}
		if(!$_G['member']['conisbind']) {
			return tpl_global_usernav_extra1();
		}
	}

	function _allowconnectfeed() {
		if(!$this->allow) {
			return;
		}
		global $_G;
		return $_G['uid'] && $_G['setting']['connect']['allow'] && $_G['setting']['connect']['feed']['allow'] && ($_G['forum']['status'] == 3 && $_G['setting']['connect']['feed']['group'] || $_G['forum']['status'] != 3 && (!$_G['setting']['connect']['feed']['fids'] || in_array($_G['fid'], $_G['setting']['connect']['feed']['fids'])));
	}

	function _allowconnectt() {
		if(!$this->allow) {
			return;
		}
		global $_G;
		return $_G['uid'] && $_G['setting']['connect']['allow'] && $_G['setting']['connect']['t']['allow'] && ($_G['forum']['status'] == 3 && $_G['setting']['connect']['t']['group'] || $_G['forum']['status'] != 3 && (!$_G['setting']['connect']['t']['fids'] || in_array($_G['fid'], $_G['setting']['connect']['t']['fids'])));
	}

	function _viewthread_share_method_output() {
		global $_G;
		$_G['connect']['qq_share_url'] = $_G['siteurl'] . 'home.php?mod=spacecp&ac=plugin&id=qqconnect:spacecp&pluginop=share&sh_type=4&thread_id=' . $_G['tid'];
		$_G['connect']['first_post'] = $postlist[$_G['forum_firstpid']];
		if ($_G['connect']['first_post']['anonymous']) {
			$_G['connect']['first_post']['authorid'] = 0;
			$_G['connect']['first_post']['author'] = '';
		}
		if ($_G['group']['allowgetimage'] && $_G['thread']['price'] == 0) {
			if (trim($_G['forum']['viewperm'])) {
				$allowViewPermGroupIds = explode("\t", trim($_G['forum']['viewperm']));
			}
			if (trim($_G['forum']['getattachperm'])) {
				$allowViewAttachGroupIds = explode("\t", trim($_G['forum']['getattachperm']));
			}
			$bigWidth = '400';
			$bigHeight = '400';
			$share_images = array();
			foreach ($_G['connect']['first_post']['attachments'] as $attachment) {
				if ($attachment['isimage'] == 0 || $attachment['price'] > 0
					|| $attachment['readperm'] > $_G['group']['readaccess']
					|| ($allowViewPermGroupIds && !in_array($_G['groupid'], $allowViewPermGroupIds))
					|| ($allowViewAttachGroupIds && !in_array($_G['groupid'], $allowViewAttachGroupIds))) {
						continue;
					}
				$bigImageURL = $_G['siteurl'] . getforumimg($attachment['aid'], 1, $bigWidth, $bigHeight, 'fixnone');
				$share_images[] = urlencode($bigImageURL);
			}
			$_G['connect']['share_images'] = implode('|', $share_images);
		}
		return tpl_viewthread_share_method($jsurl);
	}

}

class plugin_qqconnect_member extends plugin_qqconnect {

	function connect_member() {
		global $_G, $seccodecheck, $secqaacheck, $connect_guest;

		if($this->allow) {
			if($_G['uid'] && $_G['member']['conisbind']) {
				dheader('location: '.$_G['siteurl'].'index.php');
			}
			$connect_guest = array();
			if($_G['connectguest'] && (submitcheck('regsubmit', 0, $seccodecheck, $secqaacheck) || submitcheck('loginsubmit', 1, $seccodestatus))) {
				if(!$_GET['auth_hash']) {
					$_GET['auth_hash'] = $_G['cookie']['con_auth_hash'];
				}
				$conopenid = authcode($_GET['auth_hash']);
				$connect_guest = C::t('#qqconnect#common_connect_guest')->fetch($conopenid);
				if(!$connect_guest) {
					dsetcookie('con_auth_hash');
					showmessage('qqconnect:connect_login_first');
				}
			}
		}
	}

	function logging_member() {
		global $_G;
		if($this->allow && $_G['connectguest'] && $_GET['action'] == 'login') {
			if ($_G['inajax']) {
				showmessage('qqconnect:connectguest_message_complete_or_bind');
			} else {
				dheader('location: '.$_G['siteurl'].'member.php?mod=connect&ac=bind');
			}
		}
	}

	function register_member() {
		global $_G;
		if($this->allow && $_G['connectguest']) {
			if ($_G['inajax']) {
				showmessage('qqconnect:connectguest_message_complete_or_bind');
			} else {
				dheader('location: '.$_G['siteurl'].'member.php?mod=connect');
			}
		}
	}

	function logging_method() {
		if(!$this->allow) {
			return;
		}
		return tpl_login_bar();
	}

	function register_logging_method() {
		if(!$this->allow) {
			return;
		}
		return tpl_login_bar();
	}

	function connect_input_output() {
		if(!$this->allow) {
			return;
		}
		global $_G;
		$_G['setting']['pluginhooks']['register_input'] = tpl_register_input();
	}

	function connect_bottom_output() {
		if(!$this->allow) {
			return;
		}
		global $_G;
		$_G['setting']['pluginhooks']['register_bottom'] = tpl_register_bottom();
	}

}

class plugin_qqconnect_forum extends plugin_qqconnect {

	function viewthread_share_method_output() {
		return $this->_viewthread_share_method_output();
	}

}

class plugin_qqconnect_group extends plugin_qqconnect {

	function viewthread_share_method_output() {
		return $this->_viewthread_share_method_output();
	}

}


class mobileplugin_qqconnect extends plugin_qqconnect_base {

	var $allow = false;

	function mobileplugin_qqconnect() {
		global $_G;
		if(!$_G['setting']['connect']['allow'] || $_G['setting']['bbclosed']) {
			return;
		}
		$this->allow = true;
	}

	function common() {
		$this->common_base();
	}

}

class mobileplugin_qqconnect_member extends mobileplugin_qqconnect {

	function connect_member() {
		global $_G, $seccodecheck, $secqaacheck, $connect_guest;

		if($this->allow) {
			if($_G['uid'] && $_G['member']['conisbind']) {
				dheader('location: '.$_G['siteurl'].'index.php');
			}
			$connect_guest = array();
			if($_G['connectguest'] && (submitcheck('regsubmit', 0, $seccodecheck, $secqaacheck) || submitcheck('loginsubmit', 1, $seccodestatus))) {
				if(!$_GET['auth_hash']) {
					$_GET['auth_hash'] = $_G['cookie']['con_auth_hash'];
				}
				$conopenid = authcode($_GET['auth_hash']);
				$connect_guest = C::t('#qqconnect#common_connect_guest')->fetch($conopenid);
				if(!$connect_guest) {
					dsetcookie('con_auth_hash');
					showmessage('qqconnect:connect_login_first');
				}
			}
		}
	}
}