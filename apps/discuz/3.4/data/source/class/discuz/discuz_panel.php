<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: discuz_panel.php 26205 2011-12-05 10:09:32Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

define('ADMINCP_PANEL', 1);
define('MODCP_PANEL', 2);
define('PORTALCP_PANEL', 3);


class discuz_panel {

	private $table;
	var $ttl = 3600;
	var $lockttl = 900;

	var $uid;
	var $adminid;
	var $groupid;
	var $panel;
	var $ip;

	var $storage = array();
	var $session = array();
	var $islogin = false;

	public function __construct($panel) {
		global $_G;
		$this->uid = (int)$_G['uid'];
		$this->adminid = (int)$_G['adminid'];
		$this->groupid = (int)$_G['groupid'];
		$this->panel = (int)$panel;
		$this->ip = $_G['clientip'];

		$this->table = C::t('common_admincp_session');

		$this->_cpaccess();
	}

	function _session_load() {

		$this->session = $this->table->fetch($this->uid, $this->panel);

		if(empty($this->session) || (time() - $this->session['dateline'] > $this->ttl)) {
			$this->session = array();
		} elseif($this->session['errorcount'] >=5 && (time() - $this->session['dateline'] > $this->lockttl)) {
			$this->session = array();
		} elseif(!empty($this->session['storage'])) {
			$this->storage = dunserialize(base64_decode($this->session['storage']));
			$this->session['storage'] = '';
		}
		return $this->session;
	}

	function _session_destroy($uid = 0) {
		$uid = empty($uid) ? $this->uid : $uid;
		$this->table->delete($uid, $this->panel, $this->ttl);
	}

	function _loadstorage() {
		$ret = $this->table->fetch($this->uid, $this->panel);
		$storage = $ret['storage'];
		if(!empty($storage)) {
			$this->storage = dunserialize(base64_decode($storage));
		} else {
			$this->storage = array();
		}
	}

	function geturl() {
		$url = getglobal('basefilename').'?';
		if(!empty($_GET)) {
			foreach ($_GET as $key => $value) {
				$url .= urlencode($key).'='.urlencode($value).'&';
			}
		}
		return $url;
	}

	function isfounder($user = '') {
		global $_G;
		$user = empty($user) ? array('uid' => $_G['uid'], 'adminid' => $_G['adminid'], 'username' => $_G['member']['username']) : $user;
		$founders = str_replace(' ', '', $GLOBALS['forumfounders']);
		if($user['adminid'] <> 1) {
			return FALSE;
		} elseif(empty($founders)) {
			return TRUE;
		} elseif(strexists(",$founders,", ",$user[uid],")) {
			return TRUE;
		} elseif(!is_numeric($user['username']) && strexists(",$founders,", ",$user[username],")) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function set($varname, $value, $updatedb = false) {
		$this->storage[$varname] = $value;
		$updatedb && $this->update();
	}

	function get($varname, $fromdb = false) {
		$return = null;
		$fromdb && $this->_loadstorage();
		if(isset($this->storage[$varname])) {
			$return = $this->storage[$varname];
		}
		return $return;
	}

	function clear($updatedb = false) {
		$this->storage = array();
		$updatedb && $this->update();
	}

	function _sesssion_creat() {
		$this->_session_destroy();
		$this->set('url_forward',  $this->geturl());
		$this->session = array(
			'uid' => $this->uid,
			'adminid' => $this->adminid,
			'panel' => $this->panel,
			'ip' => $this->ip,
			'errorcount' => 0,
		);
		$this->update(true);
	}

	function update($isnew = false) {
		$data = array();
		$this->session['dateline'] = time();
		$this->session['storage'] = !empty($this->storage) ? base64_encode((serialize($this->storage))) : '';
		if($isnew) {
			$this->table->insert($this->session, false, true);
		} else {
			$this->table->update($this->uid, $this->panel, $this->session);
		}
	}

	function _cpaccess() {

		if(empty($this->uid)) {
			$this->_user_login();
		} elseif($this->panel == MODCP_PANEL && $this->adminid <= 0) {
			$this->showmessage('admin_cpanel_noaccess');
		}

		$this->_session_load();
		if(empty($this->session)) {
			$this->_sesssion_creat();
		} elseif($this->session['errorcount'] > 5) {
			$this->_panel_locked();
		} elseif($this->session['errorcount'] == -1) {
			$this->islogin = true;
			$this->update();
		} else {
			$this->islogin = false;
		}
	}

	function dologin($username, $password, $isuid = false) {
		loaducenter();
		if(!$isuid) {
			$username = addslashes($username);
		}
		$ucresult = uc_user_login($username, $password, $isuid ? 1 : 0);
		if($ucresult[0] > 0) {
			$this->loginsucced();
		} else {
			$this->session['errorcount'] ++;
		}
		$this->update();
		return $this->islogin;
	}

	function dologout() {
		$this->_session_destroy();
	}

	function loginsucced() {
		$this->session['errorcount'] = '-1';
		$this->islogin = true;
		$this->update();
		dheader('Location: '.$this->get('url_forward'));
	}

	function showmessage($message, $url_forward = '', $values =  array(), $ext = array()) {
		showmessage($message, $url_forward, $values, $ext);
		dexit();
	}

	function _panel_locked() {
		$unlocktime = dgmdate($this->session['dateline'] + $this->lockttl + 30);
		$this->showmessage('admin_cpanel_locked', '', array('unlocktime' => $unlocktime));
	}

	function _user_login() {
		$this->showmessage('to_login', 'member.php?mod=logging&action=login', array(), array('showmsg' => true, 'login' => 1));
	}

}
?>