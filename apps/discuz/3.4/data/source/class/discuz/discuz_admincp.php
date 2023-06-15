<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: discuz_admincp.php 31471 2012-08-31 07:33:26Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class discuz_admincp
{
	var $core = null;
	var $script = null;

	var $userlogin = false;
	var $adminsession = array();
	var $adminuser = array();
	var $perms = null;

	var $panel = 1;

	var $isfounder = false;

	var $cpsetting = array();

	var $cpaccess = 0;

	var $sessionlife = 1800;
	var $sessionlimit = 0;

	function &instance() {
		static $object;
		if(empty($object)) {
			$object = new discuz_admincp();
		}
		return $object;
	}

	function __construct() {
		;
	}

	function init() {

		if(empty($this->core) || !is_object($this->core)) {
			exit('No Discuz core found');
		}

		$this->cpsetting = $this->core->config['admincp'];
		$this->adminuser = & $this->core->var['member'];

		$this->isfounder = $this->checkfounder($this->adminuser);

		$this->sessionlimit = TIMESTAMP - $this->sessionlife;

		$this->check_cpaccess();

		$this->writecplog();
	}

	function writecplog() {
		global $_G;
		$extralog = implodearray(array('GET' => $_GET, 'POST' => $_POST), array('formhash', 'submit', 'addsubmit', 'admin_password', 'sid', 'action'));
		writelog('cplog', implode("\t", clearlogstring(array($_G['timestamp'], $_G['username'], $_G['adminid'], $_G['clientip'], getgpc('action'), $extralog))));
	}

	function check_cpaccess() {

		global $_G;
		$session = array();

		if(!$this->adminuser['uid']) {
			$this->cpaccess = getglobal('config/admincp/mustlogin') ? -5 : 0;
		} else {

			if(!$this->isfounder) {
				$session = C::t('common_admincp_member')->fetch($this->adminuser['uid']);
				if($session) {
					$session = array_merge($session, C::t('common_admincp_session')->fetch($this->adminuser['uid'], $this->panel));
				}
			} else {
				$session = C::t('common_admincp_session')->fetch($this->adminuser['uid'], $this->panel);
			}

			if(empty($session)) {
				$this->cpaccess = $this->isfounder ? 1 : -2;

			} elseif($_G['setting']['adminipaccess'] && !ipaccess($_G['clientip'], $_G['setting']['adminipaccess'])) {
				$this->do_user_login();

			} elseif ($session && empty($session['uid'])) {
				$this->cpaccess = 1;

			} elseif ($session['dateline'] < $this->sessionlimit) {
				$this->cpaccess = 1;

			} elseif ($this->cpsetting['checkip'] && ($session['ip'] != substr($this->core->var['clientip'], 0, strlen($session['ip'])))) {
				$this->cpaccess = 1;

			} elseif ($session['errorcount'] >= 0 && $session['errorcount'] <= 3) {
				$this->cpaccess = 2;

			} elseif ($session['errorcount'] == -1) {
				$this->cpaccess = 3;

			} else {
				$this->cpaccess = -1;
			}
		}

		if($this->cpaccess == 2 || $this->cpaccess == 3) {
			if(!empty($session['customperm'])) {
				$session['customperm'] = dunserialize($session['customperm']);
			}
		}

		$this->adminsession = $session;

		if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['admin_password'])) {
			if($this->cpaccess == 2) {
				$this->check_admin_login();
			} elseif($this->cpaccess == 0) {
				$this->check_user_login();
			}
		}

		if($this->cpaccess == 1) {
			C::t('common_admincp_session')->delete($this->adminuser['uid'], $this->panel, $this->sessionlife);
			C::t('common_admincp_session')->insert(array(
				'uid' => $this->adminuser['uid'],
				'adminid' => $this->adminuser['adminid'],
				'panel' => $this->panel,
				'ip' => $this->core->var['clientip'],
				'dateline' => TIMESTAMP,
				'errorcount' => 0,
			));
		} elseif ($this->cpaccess == 3) {
			$this->load_admin_perms();
			C::t('common_admincp_session')->update($this->adminuser['uid'], $this->panel, array('dateline' => TIMESTAMP, 'ip' => $this->core->var['clientip'], 'errorcount' => -1));
		}

		if($this->cpaccess != 3) {
			$this->do_user_login();
		}

	}

	function check_admin_login() {
		global $_G;
		if((empty($_POST['admin_questionid']) || empty($_POST['admin_answer'])) && ($_G['config']['admincp']['forcesecques'] || $_G['group']['forcesecques'])) {
			$this->do_user_login();
		}
		loaducenter();
		$ucresult = uc_user_login($this->adminuser['uid'], $_POST['admin_password'], 1, 1, $_POST['admin_questionid'], $_POST['admin_answer'], $this->core->var['clientip']);
		if($ucresult[0] > 0) {
			C::t('common_admincp_session')->update($this->adminuser['uid'], $this->panel, array('dateline' => TIMESTAMP, 'ip' => $this->core->var['clientip'], 'errorcount' => -1));
			dheader('Location: '.ADMINSCRIPT.'?'.cpurl('url', array('sid')));
		} else {
			$errorcount = $this->adminsession['errorcount'] + 1;
			C::t('common_admincp_session')->update($this->adminuser['uid'], $this->panel, array('dateline' => TIMESTAMP, 'ip' => $this->core->var['clientip'], 'errorcount' => $errorcount));
		}
	}

	function check_user_login() {
		global $_G;
		$admin_username = isset($_POST['admin_username']) ? trim($_POST['admin_username']) : '';
		if($admin_username != '') {

			require_once libfile('function/member');
			if(logincheck($_POST['admin_username'])) {
				if((empty($_POST['admin_questionid']) || empty($_POST['admin_answer'])) && ($_G['config']['admincp']['forcesecques'] || $_G['group']['forcesecques'])) {
					$this->do_user_login();
				}
				$result = userlogin($_POST['admin_username'], $_POST['admin_password'], $_POST['admin_questionid'], $_POST['admin_answer'], 'username', $this->core->var['clientip']);
				if($result['status'] == 1) {
					$cpgroupid = C::t('common_admincp_member')->fetch($result['member']['uid']);
					$cpgroupid = $cpgroupid['uid'];
					if($cpgroupid || $this->checkfounder($result['member'])) {
						C::t('common_admincp_session')->insert(array(
							'uid' =>$result['member']['uid'],
							'adminid' =>$result['member']['adminid'],
							'panel' =>$this->panel,
							'dateline' => TIMESTAMP,
							'ip' => $this->core->var['clientip'],
							'errorcount' => -1), false, true);

						setloginstatus($result['member'], 0);
						dheader('Location: '.ADMINSCRIPT.'?'.cpurl('url', array('sid')));
					} else {
						$this->cpaccess = -2;
					}
				} else {
					loginfailed($_POST['admin_username']);
				}
			} else {
				$this->cpaccess = -4;
			}
		}
	}

	function allow($action, $operation, $do) {

		if($this->perms === null) {
			$this->load_admin_perms();
		}

		if(isset($this->perms['all'])) {
			return $this->perms['all'];
		}

		if(!empty($_POST) && !array_key_exists('_allowpost', $this->perms) && $action.'_'.$operation != 'misc_custommenu') {
			return false;
		}
		$this->perms['misc_custommenu'] = 1;

		$key = $action;
		if(isset($this->perms[$key])) {
			return $this->perms[$key];
		}
		$key = $action.'_'.$operation;
		if(isset($this->perms[$key])) {
			return $this->perms[$key];
		}
		$key = $action.'_'.$operation.'_'.$do;
		if(isset($this->perms[$key])) {
			return $this->perms[$key];
		}
		return false;
	}

	function load_admin_perms() {

		$this->perms = array();
		if(!$this->isfounder) {
			if($this->adminsession['cpgroupid']) {
				foreach(C::t('common_admincp_perm')->fetch_all_by_cpgroupid($this->adminsession['cpgroupid']) as $perm) {
					if(empty($this->adminsession['customperm'])) {
						$this->perms[$perm['perm']] = true;
					} elseif(!in_array($perm['perm'], (array)$this->adminsession['customperm'])) {
						$this->perms[$perm['perm']] = true;
					}
				}
			} else {
				$this->perms['all'] = true;
			}
		} else {
			$this->perms['all'] = true;
		}
	}

	function checkfounder($user) {

		$founders = str_replace(' ', '', $this->cpsetting['founder']);
		if(!$user['uid'] || $user['groupid'] != 1 || $user['adminid'] != 1) {
			return false;
		} elseif(empty($founders)) {
			return true;
		} elseif(strexists(",$founders,", ",$user[uid],")) {
			return true;
		} elseif(!is_numeric($user['username']) && strexists(",$founders,", ",$user[username],")) {
			return true;
		} else {
			return FALSE;
		}
	}

	function do_user_login() {
		require $this->admincpfile('login');
	}

	function do_admin_logout() {
		C::t('common_admincp_session')->delete($this->adminuser['uid'], $this->panel, $this->sessionlife);
	}

	function admincpfile($action) {
		return DISCUZ_ROOT.'./source/admincp/admincp_'.$action.'.php';
	}

	function show_admincp_main() {
		$this->do_request('main');
	}

	function show_no_access() {
		cpheader();
		cpmsg('action_noaccess', '', 'error');
		cpfooter();
	}

	function do_request($action) {

		global $_G;

		$lang = lang('admincp');
		$title = 'cplog_'.getgpc('action').(getgpc('operation') ? '_'.getgpc('operation') : '');
		$operation = getgpc('operation');
		$do = getgpc('do');
		$sid = $_G['sid'];
		$isfounder = $this->isfounder;
		if($action == 'main' || $this->allow($action, $operation, $do)) {
			require DISCUZ_ROOT.'./source/admincp/admincp_'.$action.'.php';
		} else {
			cpheader();
			cpmsg('action_noaccess', '', 'error');
		}
	}
}