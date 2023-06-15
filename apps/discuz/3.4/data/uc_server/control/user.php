<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: user.php 1177 2014-11-03 05:46:57Z hypowang $
*/

!defined('IN_UC') && exit('Access Denied');

define('UC_USER_CHECK_USERNAME_FAILED', -1);
define('UC_USER_USERNAME_BADWORD', -2);
define('UC_USER_USERNAME_EXISTS', -3);
define('UC_USER_EMAIL_FORMAT_ILLEGAL', -4);
define('UC_USER_EMAIL_ACCESS_ILLEGAL', -5);
define('UC_USER_EMAIL_EXISTS', -6);

class usercontrol extends base {


	function __construct() {
		$this->usercontrol();
	}

	function usercontrol() {
		parent::__construct();
		$this->load('user');
	}

	function onsynlogin() {
		$this->init_input();
		$uid = $this->input('uid');
		if($this->app['synlogin']) {
			if($this->user = $_ENV['user']->get_user_by_uid($uid)) {
				$synstr = '';
				foreach($this->cache['apps'] as $appid => $app) {
					if($app['synlogin']) {
						if($app['appid'] != $this->app['appid']) {
							$synstr .= '<script type="text/javascript" src="'.$app['url'].'/api/'.$app['apifilename'].'?time='.$this->time.'&code='.urlencode($this->authcode('action=synlogin&username='.$this->user['username'].'&uid='.$this->user['uid'].'&password='.$this->user['password']."&time=".$this->time, 'ENCODE', $app['authkey'])).'" reload="1"></script>';
						}
						if(is_array($app['extra']['extraurl'])) foreach($app['extra']['extraurl'] as $extraurl) {
							$synstr .= '<script type="text/javascript" src="'.$extraurl.'/api/'.$app['apifilename'].'?time='.$this->time.'&code='.urlencode($this->authcode('action=synlogin&username='.$this->user['username'].'&uid='.$this->user['uid'].'&password='.$this->user['password']."&time=".$this->time, 'ENCODE', $app['authkey'])).'" reload="1"></script>';
						}
					}
				}
				return $synstr;
			}
		}
		return '';
	}

	function onsynlogout() {
		$this->init_input();
		if($this->app['synlogin']) {
			$synstr = '';
			foreach($this->cache['apps'] as $appid => $app) {
				if($app['synlogin']) {
					if($app['appid'] != $this->app['appid']) {
						$synstr .= '<script type="text/javascript" src="'.$app['url'].'/api/'.$app['apifilename'].'?time='.$this->time.'&code='.urlencode($this->authcode('action=synlogout&time='.$this->time, 'ENCODE', $app['authkey'])).'" reload="1"></script>';
					}
					if(is_array($app['extra']['extraurl'])) foreach($app['extra']['extraurl'] as $extraurl) {
						$synstr .= '<script type="text/javascript" src="'.$extraurl.'/api/'.$app['apifilename'].'?time='.$this->time.'&code='.urlencode($this->authcode('action=synlogout&time='.$this->time, 'ENCODE', $app['authkey'])).'" reload="1"></script>';
					}
				}
			}
			return $synstr;
		}
		return '';
	}

	function onregister() {
		$this->init_input();
		$username = $this->input('username');
		$password =  $this->input('password');
		$email = $this->input('email');
		$questionid = $this->input('questionid');
		$answer = $this->input('answer');
		$regip = $this->input('regip');

		if(($status = $this->_check_username($username)) < 0) {
			return $status;
		}
		if(($status = $this->_check_email($email)) < 0) {
			return $status;
		}

		$uid = $_ENV['user']->add_user($username, $password, $email, 0, $questionid, $answer, $regip);
		return $uid;
	}

	function onedit() {
		$this->init_input();
		$username = $this->input('username');
		$oldpw = $this->input('oldpw');
		$newpw = $this->input('newpw');
		$email = $this->input('email');
		$ignoreoldpw = $this->input('ignoreoldpw');
		$questionid = $this->input('questionid');
		$answer = $this->input('answer');

		if(!$ignoreoldpw && $email && ($status = $this->_check_email($email, $username)) < 0) {
			return $status;
		}
		$status = $_ENV['user']->edit_user($username, $oldpw, $newpw, $email, $ignoreoldpw, $questionid, $answer);

		if($newpw && $status > 0) {
			$this->load('note');
			$_ENV['note']->add('updatepw', 'username='.urlencode($username).'&password=');
			$_ENV['note']->send();
		}
		return $status;
	}

	function onlogin() {
		$this->init_input();
		$isuid = $this->input('isuid');
		$username = $this->input('username');
		$password = $this->input('password');
		$checkques = $this->input('checkques');
		$questionid = $this->input('questionid');
		$answer = $this->input('answer');
		$ip = $this->input('ip');

		$check_times = $this->settings['login_failedtime'] > 0 ? $this->settings['login_failedtime'] : ($this->settings['login_failedtime'] < 0 ? 0 : 5);

		if($ip && $check_times && !$loginperm = $_ENV['user']->can_do_login($username, $ip)) {
			$status = -4;
			return array($status, '', $password, '', 0);
		}

		if($isuid == 1) {
			$user = $_ENV['user']->get_user_by_uid($username);
		} elseif($isuid == 2) {
			$user = $_ENV['user']->get_user_by_email($username);
		} else {
			$user = $_ENV['user']->get_user_by_username($username);
		}

		$passwordmd5 = preg_match('/^\w{32}$/', $password) ? $password : md5($password);
		if(empty($user)) {
			$status = -1;
		} elseif($user['password'] != md5($passwordmd5.$user['salt'])) {
			$status = -2;
		} elseif($checkques && $user['secques'] != $_ENV['user']->quescrypt($questionid, $answer)) {
			$status = -3;
		} else {
			$status = $user['uid'];
		}
		if($ip && $check_times && $status <= 0) {
			$_ENV['user']->loginfailed($username, $ip);
		}
		$merge = $status != -1 && !$isuid && $_ENV['user']->check_mergeuser($username) ? 1 : 0;
		return array($status, $user['username'], $password, $user['email'], $merge);
	}

	function onlogincheck() {
		$this->init_input();
		$username = $this->input('username');
		$ip = $this->input('ip');
		return $_ENV['user']->can_do_login($username, $ip);
	}

	function oncheck_email() {
		$this->init_input();
		$email = $this->input('email');
		return $this->_check_email($email);
	}

	function oncheck_username() {
		$this->init_input();
		$username = $this->input('username');
		if(($status = $this->_check_username($username)) < 0) {
			return $status;
		} else {
			return 1;
		}
	}

	function onget_user() {
		$this->init_input();
		$username = $this->input('username');
		if(!$this->input('isuid')) {
			$status = $_ENV['user']->get_user_by_username($username);
		} else {
			$status = $_ENV['user']->get_user_by_uid($username);
		}
		if($status) {
			return array($status['uid'],$status['username'],$status['email']);
		} else {
			return 0;
		}
	}


	function ongetprotected() {
		$this->init_input();
		$protectedmembers = $this->db->fetch_all("SELECT uid,username FROM ".UC_DBTABLEPRE."protectedmembers GROUP BY username");
		return $protectedmembers;
	}

	function ondelete() {
		$this->init_input();
		$uid = $this->input('uid');
		return $_ENV['user']->delete_user($uid);
	}

	function ondeleteavatar() {
		$this->init_input();
		$uid = $this->input('uid');
		$_ENV['user']->delete_useravatar($uid);
	}

	function onaddprotected() {
		$this->init_input();
		$username = $this->input('username');
		$admin = $this->input('admin');
		$appid = $this->app['appid'];
		$usernames = (array)$username;
		foreach($usernames as $username) {
			$user = $_ENV['user']->get_user_by_username($username);
			$uid = $user['uid'];
			$this->db->query("REPLACE INTO ".UC_DBTABLEPRE."protectedmembers SET uid='$uid', username='$username', appid='$appid', dateline='{$this->time}', admin='$admin'", 'SILENT');
		}
		return $this->db->errno() ? -1 : 1;
	}

	function ondeleteprotected() {
		$this->init_input();
		$username = $this->input('username');
		$appid = $this->app['appid'];
		$usernames = (array)$username;
		foreach($usernames as $username) {
			$this->db->query("DELETE FROM ".UC_DBTABLEPRE."protectedmembers WHERE username='$username' AND appid='$appid'");
		}
		return $this->db->errno() ? -1 : 1;
	}

	function onmerge() {
		$this->init_input();
		$oldusername = $this->input('oldusername');
		$newusername = $this->input('newusername');
		$uid = $this->input('uid');
		$password = $this->input('password');
		$email = $this->input('email');
		if(($status = $this->_check_username($newusername)) < 0) {
			return $status;
		}
		$uid = $_ENV['user']->add_user($newusername, $password, $email, $uid);
		$this->db->query("DELETE FROM ".UC_DBTABLEPRE."mergemembers WHERE appid='".$this->app['appid']."' AND username='$oldusername'");
		return $uid;
	}

	function onmerge_remove() {
		$this->init_input();
		$username = $this->input('username');
		$this->db->query("DELETE FROM ".UC_DBTABLEPRE."mergemembers WHERE appid='".$this->app['appid']."' AND username='$username'");
		return NULL;
	}

	function _check_username($username) {
		$username = addslashes(trim(stripslashes($username)));
		if(!$_ENV['user']->check_username($username)) {
			return UC_USER_CHECK_USERNAME_FAILED;
		} elseif(!$_ENV['user']->check_usernamecensor($username)) {
			return UC_USER_USERNAME_BADWORD;
		} elseif($_ENV['user']->check_usernameexists($username)) {
			return UC_USER_USERNAME_EXISTS;
		}
		return 1;
	}

	function _check_email($email, $username = '') {
		if(!$_ENV['user']->check_emailformat($email)) {
			return UC_USER_EMAIL_FORMAT_ILLEGAL;
		} elseif(!$_ENV['user']->check_emailaccess($email)) {
			return UC_USER_EMAIL_ACCESS_ILLEGAL;
		} elseif(!$this->settings['doublee'] && $_ENV['user']->check_emailexists($email, $username)) {
			return UC_USER_EMAIL_EXISTS;
		} else {
			return 1;
		}
	}

	function ongetcredit($arr) {
		$this->init_input();
		$appid = $this->input('appid');
		$uid = $this->input('uid');
		$credit = $this->input('credit');
		$this->load('note');
		$this->load('misc');
		$app = $this->cache['apps'][$appid];
		$apifilename = isset($app['apifilename']) && $app['apifilename'] ? $app['apifilename'] : 'uc.php';
		if($app['extra']['apppath'] && $this->detectescape($app['extra']['apppath'].'./api/', $apifilename) && substr(strrchr($apifilename, '.'), 1, 10) == 'php' && @include $app['extra']['apppath'].'./api/'.$apifilename) {
			$uc_note = new uc_note();
			return $uc_note->getcredit(array('uid' => $uid, 'credit' => $credit), '');
		} else {
			$url = $_ENV['note']->get_url_code('getcredit', "uid=$uid&credit=$credit", $appid);
			return $_ENV['misc']->dfopen($url, 0, '', '', 1, $app['ip'], UC_NOTE_TIMEOUT);
		}
	}


	function onuploadavatar() {
		@header("Expires: 0");
		@header("Cache-Control: private, post-check=0, pre-check=0, max-age=0", FALSE);
		@header("Pragma: no-cache");
		$this->init_input(getgpc('agent', 'G'));

		$uid = $this->input('uid');
		if(empty($uid)) {
			return -1;
		}
		if(empty($_FILES['Filedata'])) {
			return -3;
		}

		list($width, $height, $type, $attr) = getimagesize($_FILES['Filedata']['tmp_name']);
		if(!in_array($type, array(1,2,3,6))) {
			@unlink($_FILES['Filedata']['tmp_name']);
			return -4;
		}
		$imgtype = array(1 => '.gif', 2 => '.jpg', 3 => '.png');
		$filetype = $imgtype[$type];
		if(!$filetype) $filetype = '.jpg';
		$tmpavatar = UC_DATADIR.'./tmp/upload'.$uid.$filetype;
		file_exists($tmpavatar) && @unlink($tmpavatar);
		if(@copy($_FILES['Filedata']['tmp_name'], $tmpavatar) || @move_uploaded_file($_FILES['Filedata']['tmp_name'], $tmpavatar)) {
			@unlink($_FILES['Filedata']['tmp_name']);
			list($width, $height, $type, $attr) = getimagesize($tmpavatar);
			if($width < 10 || $height < 10 || $type == 4) {
				@unlink($tmpavatar);
				return -2;
			}
		} else {
			@unlink($_FILES['Filedata']['tmp_name']);
			return -4;
		}
		$avatarurl = UC_DATAURL.'/tmp/upload'.$uid.$filetype;
		return $avatarurl;
	}

	function onrectavatar() {
		@header("Expires: 0");
		@header("Cache-Control: private, post-check=0, pre-check=0, max-age=0", FALSE);
		@header("Pragma: no-cache");
		if(getgpc('base64', 'G')){
			header("Content-type: text/html; charset=utf-8");
		}else{
			header("Content-type: application/xml; charset=utf-8");
		}
		$this->init_input(getgpc('agent'));
		$uid = $this->input('uid');
		if(empty($uid)) {
			return '<root><message type="error" value="-1" /></root>';
		}
		$home = $this->get_home($uid);
		if(!is_dir(UC_DATADIR.'./avatar/'.$home)) {
			$this->set_home($uid, UC_DATADIR.'./avatar/');
		}
		$avatartype = getgpc('avatartype', 'G') == 'real' ? 'real' : 'virtual';
		$bigavatarfile = UC_DATADIR.'./avatar/'.$this->get_avatar($uid, 'big', $avatartype);
		$middleavatarfile = UC_DATADIR.'./avatar/'.$this->get_avatar($uid, 'middle', $avatartype);
		$smallavatarfile = UC_DATADIR.'./avatar/'.$this->get_avatar($uid, 'small', $avatartype);
		$bigavatar = $this->flashdata_decode(getgpc('avatar1', 'P'));
		$middleavatar = $this->flashdata_decode(getgpc('avatar2', 'P'));
		$smallavatar = $this->flashdata_decode(getgpc('avatar3', 'P'));
		if(!$bigavatar || !$middleavatar || !$smallavatar) {
			return '<root><message type="error" value="-2" /></root>';
		}

		$success = 1;
		$fp = @fopen($bigavatarfile, 'wb');
		@fwrite($fp, $bigavatar);
		@fclose($fp);

		$fp = @fopen($middleavatarfile, 'wb');
		@fwrite($fp, $middleavatar);
		@fclose($fp);

		$fp = @fopen($smallavatarfile, 'wb');
		@fwrite($fp, $smallavatar);
		@fclose($fp);

		$biginfo = @getimagesize($bigavatarfile);
		$middleinfo = @getimagesize($middleavatarfile);
		$smallinfo = @getimagesize($smallavatarfile);
		if(!$biginfo || !$middleinfo || !$smallinfo || $biginfo[2] == 4 || $middleinfo[2] == 4 || $smallinfo[2] == 4
			|| $biginfo[0] > 200 || $biginfo[1] > 250 || $middleinfo[0] > 120 || $middleinfo[1] > 120 || $smallinfo[0] > 48 || $smallinfo[1] > 48) {
			file_exists($bigavatarfile) && unlink($bigavatarfile);
			file_exists($middleavatarfile) && unlink($middleavatarfile);
			file_exists($smallavatarfile) && unlink($smallavatarfile);
			$success = 0;
		}

		$filetype = '.jpg';
		@unlink(UC_DATADIR.'./tmp/upload'.$uid.$filetype);

		if(getgpc('base64', 'G')){
			if($success) {
				return "<script>window.parent.postMessage('success','*');</script>";
			} else {
				return "<script>window.parent.postMessage('failure','*');</script>";
			}
		}else{
			if($success) {
				return '<?xml version="1.0" ?><root><face success="1"/></root>';
			} else {
				return '<?xml version="1.0" ?><root><face success="0"/></root>';
			}
		}
	}


	function flashdata_decode($s) {
		$r = '';
		if(getgpc('base64', 'G')){
			$r = base64_decode($s);
		}else{
			$l = strlen($s);
			for($i=0; $i<$l; $i=$i+2) {
				$k1 = ord($s[$i]) - 48;
				$k1 -= $k1 > 9 ? 7 : 0;
				$k2 = ord($s[$i+1]) - 48;
				$k2 -= $k2 > 9 ? 7 : 0;
				$r .= chr($k1 << 4 | $k2);
			}
		}
		return $r;
	}

}

?>