<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: wechat.class.php 36284 2016-12-12 00:47:50Z nemohou $
 */

if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_wechat {

	function plugin_wechat() {
		include_once template('wechat:module');
	}

	function common() {
		global $_G;
		if(!$_G['wechat']['setting']) {
			$_G['wechat']['setting'] = unserialize($_G['setting']['mobilewechat']);
		}
		if($_G['uid']) {
			if($_G['wechat']['setting']['wechat_qrtype']) {
				$_G['wechatuser'] = C::t('#wechat#common_member_wechatmp')->fetch($_G['uid']);
				if($_G['wechatuser'] && !$_G['wechatuser']['status']) {
					$_G['wechatuser']['isregister'] = 1;
				}
			} else {
				$_G['wechatuser'] = C::t('#wechat#common_member_wechat')->fetch($_G['uid']);
			}
			if($_G['wechatuser'] && $wechatuser['status'] == 1) {
				C::t('#wechat#common_member_wechat')->update($_G['uid'], array('status' => 0));
				require_once libfile('function/member');
				clearcookies();
			}
		}

		if(!$_G['uid'] && !defined('IN_MOBILE') && $_G['wechat']['setting']['wsq_allow']) {
			$_G['setting']['pluginhooks']['global_login_text'] .= wechat_tpl_login_bar();
		}

		$_G['Plang'] = $_G['setting']['wechatviewpluginid'] ? lang('plugin/'.$_G['setting']['wechatviewpluginid']) : array();

		if(!$_G['Plang'] || !$_G['wechatuser']) {
			unset($_G['setting']['plugins']['spacecp']['wechat:spacecp']);
		}
	}

	function deletemember($param) {
		$uids = $param['param'][0];
		$step = $param['step'];
		if ($step == 'check' && $uids && is_array($uids)) {
			foreach($uids as $uid) {
				C::t('#wechat#common_member_wechat')->delete($uid);
			}
		}
	}

	function global_login_extra() {
		global $_G;
		if(!$_G['Plang'] || $_G['inshowmessage'] || !$_G['wechat']['setting']['wsq_allow']) {
			return;
		}
		return wechat_tpl_login_extra_bar();
	}

	function global_usernav_extra1() {
		global $_G;
		if(!$_G['Plang'] || $_G['wechatuser'] || !$_G['wechat']['setting']['wsq_allow'] || !$_G['uid']) {
			return;
		}
		return wechat_tpl_user_bar();
	}

	function global_footer() {
		global $_G;
		if($_G['wechat']['setting']['wechat_float_qrcode'] && $_G['wechat']['setting']['wsq_siteid'] && $_G['wechat']['setting']['wsq_allow']) {
			$modid = $_G['basescript'].'::'.CURMODULE;
			if($modid == 'forum::forumdisplay' && !empty($_GET['fid'])) {
				$idstr = '&fid='.dintval($_GET['fid']);
				return wechat_tpl_float_qrcode($idstr);
			} elseif($modid == 'forum::viewthread' && !empty($_GET['tid'])) {
				$idstr = '&tid='.dintval($_GET['tid']).'&qrsize=2';
				return wechat_tpl_float_qrcode($idstr);
			} elseif($modid == 'forum::index') {
				return wechat_tpl_float_qrcode();
			}
		}
	}

}

class mobileplugin_wechat {

	function common() {
		global $_G;
		if(!$_G['wechat']['setting']) {
			$_G['wechat']['setting'] = unserialize($_G['setting']['mobilewechat']);
		}
		dsetcookie('mobile', '', -1);
		if(!isset($_GET['pluginid'])) {
			$redirect = WeChat::redirect(1);
			if($redirect) {
				dheader('location: '.$redirect);
			}
		}
	}

}

class plugin_wechat_member extends plugin_wechat {

	function logging_method() {
		global $_G;
		if(!$_G['Plang'] || !$_G['wechat']['setting']['wsq_allow']) {
			return;
		}
		return wechat_tpl_login_bar();
	}

	function register_top_output() {
		global $_G;
		if(strexists($_GET['referer'], 'wechat:login') && $_G['wechat']['setting']['wsq_allow']) {
			return wechat_tpl_register();
		}
	}

	function register_logging_method() {
		global $_G;
		if(!$_G['Plang'] || !$_G['wechat']['setting']['wsq_allow']) {
			return;
		}
		return wechat_tpl_login_bar();
	}

}

class mobileplugin_wechat_forum extends mobileplugin_wechat {

	function post_showactivity() {
		if(!showActivity::init()) {
			return false;
		}
		showActivity::post();
	}

	function viewthread_showactivity() {
		showActivity::init();
	}

	function misc_showactivity() {
		showActivity::init();
	}

}

class plugin_wechat_forum extends plugin_wechat {

	function viewthread_showactivity() {
		showActivity::init();
	}

	function viewthread_postheader_output() {
		if(!showActivity::init()) {
			return array();
		}
		if($GLOBALS['activity']['starttimeto']) {
			global $_G;
			$starttimeto = strtotime($GLOBALS['activity']['starttimeto']);
			if($starttimeto < TIMESTAMP && $_G['forum_thread']['displayorder'] > 0) {
				C::t('forum_thread')->update($_G['tid'], array('displayorder' => 0));
			}
		}
		return showActivity::returnvoters(1);
	}

	function viewthread_posttop_output() {
		if(!showActivity::init()) {
			return array();
		}
		return showActivity::returnvoters(2);
	}

	function misc_showactivity() {
		if(!showActivity::init()) {
			return false;
		}
		showActivity::misc();
	}

	function post_showactivity() {
		if(!showActivity::init()) {
			return false;
		}
		showActivity::post();
	}

	function viewthread_share_method_output() {
		global $_G;
		if($_G['wechat']['setting']['wsq_allow']) {
			return wechat_tpl_share(showActivity::init());
		}
	}

	function viewthread_postaction() {
		global $_G;
		if($_G['wechat']['setting']['wsq_allow'] && $_G['adminid'] == 1 && empty($_GET['viewpid'])) {
			return array(wechat_tpl_resourcepush());
		}
	}

}

class WeChat {

	static $QRCODE_EXPIRE = 1800;

	static public function getqrcode() {
		global $_G;
		if(!$_G['wechat']['setting']) {
			$_G['wechat']['setting'] = unserialize($_G['setting']['mobilewechat']);
		}
		require_once DISCUZ_ROOT . './source/plugin/wechat/wechat.lib.class.php';
		$wechat_client = new WeChatClient($_G['wechat']['setting']['wechat_appId'], $_G['wechat']['setting']['wechat_appsecret']);

		$ticket = '';
		if(!$_G['cookie']['wechat_ticket'] || $_G['wechat']['setting']['wechat_mtype'] == 1) {
			$code = 0;
			$i = 0;
			do {
				$code = rand(100000, 999999);
				$codeexists = C::t('#wechat#mobile_wechat_authcode')->fetch_by_code($code);
				$i++;
			} while($codeexists && $i < 10);

			if($_G['wechat']['setting']['wechat_mtype'] == 2) {
				$option = array(
					'scene_id' => $code,
					'expire' => self::$QRCODE_EXPIRE,
					'ticketOnly' => '1'
				);
				$ticket = $wechat_client->getQrcodeTicket($option);
				if(!$ticket) {
					showmessage('wechat:wechat_message_codefull');
				}
				dsetcookie('wechat_ticket', authcode($ticket."\t".$code, 'ENCODE'), self::$QRCODE_EXPIRE);
			}
		} else {
			list($ticket, $code) = explode("\t", authcode($_G['cookie']['wechat_ticket'], 'DECODE'));
		}

		$isqrapi = $ticket ? $ticket : '';
		if($codeexists) {
			showmessage('wechat:wechat_message_codefull');
		}

		$qrcodeurl = !$isqrapi ? $_G['setting']['attachurl'].'common/'.$_G['wechat']['setting']['wechat_qrcode'] : $_G['siteurl'].'plugin.php?id=wechat:qrcode&rand='.random(5);

		$codeenc = urlencode(base64_encode(authcode($code, 'ENCODE', $_G['config']['security']['authkey'])));
		C::t('#wechat#mobile_wechat_authcode')->insert(array('sid' => $_G['cookie']['saltkey'], 'uid' => $_G['uid'], 'code' => $code, 'createtime' => TIMESTAMP), 0, 1);
		if(!discuz_process::islocked('clear_wechat_authcode')) {
			C::t('#wechat#mobile_wechat_authcode')->delete_history();
			discuz_process::unlock('clear_wechat_authcode');
		}
		return array($isqrapi, $qrcodeurl, $codeenc, $code);
	}

	static public function redirect($type = '') {
		global $_G;
		$hook = unserialize($_G['setting']['wechatredirect']);
		if (!$hook || !in_array($hook['plugin'], $_G['setting']['plugins']['available'])) {
			return;
		}
		if(!preg_match("/^[\w\_]+$/i", $hook['plugin']) || !preg_match('/^[\w\_\.]+\.php$/i', $hook['include'])) {
			return;
		}
		include_once DISCUZ_ROOT . 'source/plugin/' . $hook['plugin'] . '/' . $hook['include'];
		if (!class_exists($hook['class'], false)) {
			return;
		}
		$class = new $hook['class'];
		if (!method_exists($class, $hook['method'])) {
			return;
		}
		$return = call_user_func(array($class, $hook['method']), $type);
		if($return) {
			return $return;
		}
	}

	static public function register($username, $return = 0, $groupid = 0) {
		global $_G;
		if(!$username) {
			return;
		}
		if(!$_G['wechat']['setting']) {
			$_G['wechat']['setting'] = unserialize($_G['setting']['mobilewechat']);
		}

		loaducenter();
		$groupid = !$groupid ? ($_G['wechat']['setting']['wechat_newusergroupid'] ? $_G['wechat']['setting']['wechat_newusergroupid'] : $_G['setting']['newusergroupid']) : $groupid;

		$password = md5(random(10));
		$email = 'wechat_'.strtolower(random(10)).'@null.null';

		$usernamelen = dstrlen($username);
		if($usernamelen < 3) {
			$username = $username.'_'.random(5);
		}
		if($usernamelen > 15) {
			if(!$return) {
				showmessage('profile_username_toolong');
			} else {
				return;
			}
		}

		$censorexp = '/^('.str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($_G['setting']['censoruser'] = trim($_G['setting']['censoruser'])), '/')).')$/i';

		if($_G['setting']['censoruser'] && @preg_match($censorexp, $username)) {
			if(!$return) {
				showmessage('profile_username_protect');
			} else {
				return;
			}
		}

		if(!$_G['wechat']['setting']['wechat_disableregrule']) {
			loadcache('ipctrl');
			if($_G['cache']['ipctrl']['ipregctrl']) {
				foreach(explode("\n", $_G['cache']['ipctrl']['ipregctrl']) as $ctrlip) {
					if(preg_match("/^(".preg_quote(($ctrlip = trim($ctrlip)), '/').")/", $_G['clientip'])) {
						$ctrlip = $ctrlip.'%';
						$_G['setting']['regctrl'] = $_G['setting']['ipregctrltime'];
						break;
					} else {
						$ctrlip = $_G['clientip'];
					}
				}
			} else {
				$ctrlip = $_G['clientip'];
			}

			if($_G['setting']['regctrl']) {
				if(C::t('common_regip')->count_by_ip_dateline($ctrlip, $_G['timestamp']-$_G['setting']['regctrl']*3600)) {
					if(!$return) {
						showmessage('register_ctrl', NULL, array('regctrl' => $_G['setting']['regctrl']));
					} else {
						return;
					}
				}
			}

			$setregip = null;
			if($_G['setting']['regfloodctrl']) {
				$regip = C::t('common_regip')->fetch_by_ip_dateline($_G['clientip'], $_G['timestamp']-86400);
				if($regip) {
					if($regip['count'] >= $_G['setting']['regfloodctrl']) {
						if(!$return) {
							showmessage('register_flood_ctrl', NULL, array('regfloodctrl' => $_G['setting']['regfloodctrl']));
						} else {
							return;
						}
					} else {
						$setregip = 1;
					}
				} else {
					$setregip = 2;
				}
			}

			if($setregip !== null) {
				if($setregip == 1) {
					C::t('common_regip')->update_count_by_ip($_G['clientip']);
				} else {
					C::t('common_regip')->insert(array('ip' => $_G['clientip'], 'count' => 1, 'dateline' => $_G['timestamp']));
				}
			}
		}

		$uid = uc_user_register(addslashes($username), $password, $email, '', '', $_G['clientip']);
		if($uid <= 0) {
			if(!$return) {
				if($uid == -1) {
					showmessage('profile_username_illegal');
				} elseif($uid == -2) {
					showmessage('profile_username_protect');
				} elseif($uid == -3) {
					showmessage('profile_username_duplicate');
				} elseif($uid == -4) {
					showmessage('profile_email_illegal');
				} elseif($uid == -5) {
					showmessage('profile_email_domain_illegal');
				} elseif($uid == -6) {
					showmessage('profile_email_duplicate');
				} else {
					showmessage('undefined_action');
				}
			} else {
				return;
			}
		}

		$init_arr = array('credits' => explode(',', $_G['setting']['initcredits']));
		C::t('common_member')->insert($uid, $username, $password, $email, $_G['clientip'], $groupid, $init_arr);

		if($_G['setting']['regctrl'] || $_G['setting']['regfloodctrl']) {
			C::t('common_regip')->delete_by_dateline($_G['timestamp']-($_G['setting']['regctrl'] > 72 ? $_G['setting']['regctrl'] : 72)*3600);
			if($_G['setting']['regctrl']) {
				C::t('common_regip')->insert(array('ip' => $_G['clientip'], 'count' => -1, 'dateline' => $_G['timestamp']));
			}
		}

		if($_G['setting']['regverify'] == 2) {
			C::t('common_member_validate')->insert(array(
				'uid' => $uid,
				'submitdate' => $_G['timestamp'],
				'moddate' => 0,
				'admin' => '',
				'submittimes' => 1,
				'status' => 0,
				'message' => '',
				'remark' => '',
			), false, true);
			manage_addnotify('verifyuser');
		}

		setloginstatus(array(
			'uid' => $uid,
			'username' => $username,
			'password' => $password,
			'groupid' => $groupid,
		), 0);

		include_once libfile('function/stat');
		updatestat('register');

		return $uid;
	}

	static public function syncAvatar($uid, $avatar) {

		if(!$uid || !$avatar) {
			return false;
		}

		if(!$content = dfsockopen($avatar)) {
			return false;
		}

		$tmpFile = DISCUZ_ROOT.'./data/avatar/'.TIMESTAMP.random(6);
		file_put_contents($tmpFile, $content);

		if(!is_file($tmpFile)) {
			return false;
		}

		$result = uploadUcAvatar::upload($uid, $tmpFile);
		unlink($tmpFile);

		if($result) C::t('common_member')->update($uid, array('avatarstatus'=>'1'));

		return $result;
	}



	static public function getnewname($openid) {
		global $_G;
		if(!$_G['wechat']['setting']) {
			$_G['wechat']['setting'] = unserialize($_G['setting']['mobilewechat']);
		}
		$wechat_client = new WeChatClient($_G['wechat']['setting']['wechat_appId'], $_G['wechat']['setting']['wechat_appsecret']);
		$userinfo = $wechat_client->getUserInfoById($openid);
		if($userinfo) {
			$defaultusername = substr(WeChatEmoji::clear($userinfo['nickname']), 0, 15);
			loaducenter();
			$user = uc_get_user($defaultusername);
			if(!empty($user)) {
				$defaultusername = cutstr($defaultusername, 7, '').'_'.random(5);
			}
		} else {
			$defaultusername = 'wx_'.random(5);
		}
		return $defaultusername;
	}

}

class uploadUcAvatar {

	public static function upload($uid, $localFile) {

		global $_G;
		if(!$uid || !$localFile) {
			return false;
		}

		list($width, $height, $type, $attr) = getimagesize($localFile);
		if(!$width) {
			return false;
		}

		if($width < 10 || $height < 10 || $type == 4) {
			return false;
		}

		$imageType = array(1 => '.gif', 2 => '.jpg', 3 => '.png');
		$fileType = $imgType[$type];
		if(!$fileType) {
			$fileType = '.jpg';
		}
		$avatarPath = $_G['setting']['attachdir'];
		$tmpAvatar = $avatarPath.'./temp/upload'.$uid.$fileType;
		file_exists($tmpAvatar) && @unlink($tmpAvatar);
		file_put_contents($tmpAvatar, file_get_contents($localFile));

		if(!is_file($tmpAvatar)) {
			return false;
		}

		$tmpAvatarBig = './temp/upload'.$uid.'big'.$fileType;
		$tmpAvatarMiddle = './temp/upload'.$uid.'middle'.$fileType;
		$tmpAvatarSmall = './temp/upload'.$uid.'small'.$fileType;

		$image = new image;
		if($image->Thumb($tmpAvatar, $tmpAvatarBig, 200, 250, 1) <= 0) {
			return false;
		}
		if($image->Thumb($tmpAvatar, $tmpAvatarMiddle, 120, 120, 1) <= 0) {
			return false;
		}
		if($image->Thumb($tmpAvatar, $tmpAvatarSmall, 48, 48, 2) <= 0) {
			return false;
		}

		$tmpAvatarBig = $avatarPath.$tmpAvatarBig;
		$tmpAvatarMiddle = $avatarPath.$tmpAvatarMiddle;
		$tmpAvatarSmall = $avatarPath.$tmpAvatarSmall;

		$avatar1 = self::byte2hex(file_get_contents($tmpAvatarBig));
		$avatar2 = self::byte2hex(file_get_contents($tmpAvatarMiddle));
		$avatar3 = self::byte2hex(file_get_contents($tmpAvatarSmall));

		$extra = '&avatar1='.$avatar1.'&avatar2='.$avatar2.'&avatar3='.$avatar3;
		$result = self::uc_api_post_ex('user', 'rectavatar', array('uid' => $uid), $extra);

		@unlink($tmpAvatar);
		@unlink($tmpAvatarBig);
		@unlink($tmpAvatarMiddle);
		@unlink($tmpAvatarSmall);

		return true;
	}

	public static function byte2hex($string) {
		$buffer = '';
		$value = unpack('H*', $string);
		$value = str_split($value[1], 2);
		$b = '';
		foreach($value as $k => $v) {
			$b .= strtoupper($v);
		}

		return $b;
	}

	public static function uc_api_post_ex($module, $action, $arg = array(), $extra = '') {
		$s = $sep = '';
		foreach($arg as $k => $v) {
			$k = urlencode($k);
			if(is_array($v)) {
				$s2 = $sep2 = '';
				foreach($v as $k2 => $v2) {
					$k2 = urlencode($k2);
					$s2 .= "$sep2{$k}[$k2]=".urlencode(uc_stripslashes($v2));
					$sep2 = '&';
				}
				$s .= $sep.$s2;
			} else {
				$s .= "$sep$k=".urlencode(uc_stripslashes($v));
			}
			$sep = '&';
		}
		$postdata = uc_api_requestdata($module, $action, $s, $extra);
		return uc_fopen2(UC_API.'/index.php', 500000, $postdata, '', TRUE, UC_IP, 20);
	}
}

class showActivity {

	public static $init = false;

	public static function init() {
		global $_G;
		if(!$_G['wechat']['setting']['wsq_allow'] || !in_array($_G['tid'], (array)$_G['wechat']['setting']['showactivity']['tids'])) {
			return false;
		}
		if(!self::$init) {
			$_G['setting']['allowpostcomment'] = array(0 => 1, 1 => 2);
			$_G['setting']['commentnumber'] = 10;
			$_G['setting']['commentpostself'] = 0;
			$_G['setting']['commentfirstpost'] = 0;
			$_G['setting']['fastpost'] = 0;
			$_G['setting']['showimages'] = 1;
			$_G['setting']['imagelistthumb'] = 1;
			$_G['setting']['activitypp'] = 0;
			$_G['setting']['disallowfloat'] .= '|reply';
			$_G['setting']['guesttipsinthread']['flag'] = 0;
			$_G['setting']['nofilteredpost'] = 0;
			$_G['group']['allowgetimage'] = 1;
			if($_G['basescript'].'::'.CURMODULE == 'forum::post' && $_GET['action'] == 'edit') {
				$_G['group']['allowpostactivity'] = true;
				$_G['forum']['allowpostspecial'] = 255;
			}
			$_GET['ordertype'] = empty($_GET['ordertype']) ? 1 : $_GET['ordertype'];
			self::$init = true;
		}
		return true;
	}

	function misc() {
		global $_G;
		if(!$_POST || $_GET['action'] != 'activityapplies' && $_GET['action'] != 'activityapplylist') {
			return;
		}
		if(submitcheck('activitysubmit')) {
			showmessage('wechat:show_please_reply');
		} elseif(submitcheck('activitycancel')) {
			showmessage('wechat:show_no_cancel');
		} elseif(submitcheck('applylistsubmit') && $_GET['operation'] == 'replenish') {
			showmessage('wechat:show_disabled');
		}
	}

	function post() {
		global $_G;
		if($_GET['action'] != 'reply') {
			return;
		}
		if(submitcheck('replysubmit')) {
			$activity = C::t('forum_activity')->fetch($_G['tid']);
			if($activity['starttimefrom'] > TIMESTAMP) {
				showmessage('wechat:show_no_begin', NULL, array());
			}
			if($activity['expiration'] && $activity['expiration'] < TIMESTAMP) {
				showmessage('activity_stop', NULL, array(), array('login' => 1));
			}
			if(empty($_GET['attachnew'])) {
				showmessage('wechat:show_please_upload');
			}
			$data = array('tid' => $_G['tid'], 'username' => $_G['username'], 'uid' => $_G['uid'], 'message' => '', 'verified' => 1, 'dateline' => $_G['timestamp']);
			C::t('forum_activityapply')->insert($data);
			$applynumber = C::t('forum_activityapply')->fetch_count_for_thread($_G['tid']);
			C::t('forum_activity')->update($_G['tid'], array('applynumber' => $applynumber));
		}
	}

	function returnvoters($type) {
		global $_G;
		$return = array();
		if($type == 1) {
			$posts = DB::fetch_all("SELECT * FROM %t WHERE tid=%d", array('forum_debatepost', $_G['tid']), 'pid');
			foreach($GLOBALS['postlist'] as $post) {
				$posts[$post['pid']]['voters'] = intval($posts[$post['pid']]['voters']);
				$return[] = !$post['first'] ? wechatshowactivity_tpl_voters($posts[$post['pid']]) : '';
			}
		} else {
			foreach($GLOBALS['postlist'] as $post) {
				$return[] = !$post['first'] ? wechatshowactivity_tpl_share($post) : '';
			}
		}
		return $return;
	}

}