<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: admin.php 1167 2014-11-03 03:06:21Z hypowang $
*/

!defined('IN_UC') && exit('Access Denied');

class adminbase extends base {

	var $cookie_status = 0;

	function __construct() {
		$this->adminbase();
	}

	function adminbase() {
		parent::__construct();
		$this->cookie_status = isset($_COOKIE['sid']) ? 1 : 0;
		$sid = $this->cookie_status ? getgpc('sid', 'C') : rawurlencode(getgpc('sid', 'R'));
		$this->sid = $this->view->sid = $this->sid_decode($sid) ? $sid : '';
		$this->view->assign('sid', $this->view->sid);
		$this->view->assign('iframe', getgpc('iframe'));
		$a = getgpc('a');
		if(!(getgpc('m') =='user' && ($a == 'login' || $a == 'logout'))) {
			$this->check_priv();
		}
	}

	function check_priv() {
		$username = $this->sid_decode($this->view->sid);
		if(empty($username)) {
			header('Location: '.UC_API.'/admin.php?m=user&a=login&iframe='.getgpc('iframe', 'G').($this->cookie_status ? '' : '&sid='.$this->view->sid));
			exit;
		} else {
			$this->user['isfounder'] = $username == 'UCenterAdministrator' ? 1 : 0;
			if(!$this->user['isfounder']) {
				$admin = $this->db->fetch_first("SELECT a.*, m.* FROM ".UC_DBTABLEPRE."admins a LEFT JOIN ".UC_DBTABLEPRE."members m USING(uid) WHERE a.username='$username'");
				if(empty($admin)) {
					header('Location: '.UC_API.'/admin.php?m=user&a=login&iframe='.getgpc('iframe', 'G').($this->cookie_status ? '' : '&sid='.$this->view->sid));
					exit;
				} else {
					$this->user = $admin;
					$this->user['username'] = $username;
					$this->user['admin'] = 1;
					$this->view->sid = $this->sid_encode($username);
					$this->setcookie('sid', $this->view->sid, 86400);
				}
			} else {
				$this->user['username'] = 'UCenterAdministrator';
				$this->user['admin'] = 1;
				$this->view->sid = $this->sid_encode($this->user['username']);
				$this->setcookie('sid', $this->view->sid, 86400);
			}
			$this->view->assign('user', $this->user);
		}
	}

	function is_founder($username) {
		return $this->user['isfounder'];
	}

	function writelog($action, $extra = '') {
		$log = dhtmlspecialchars($this->user['username']."\t".$this->onlineip."\t".$this->time."\t$action\t$extra");
		$logfile = UC_ROOT.'./data/logs/'.gmdate('Ym', $this->time).'.php';
		if(@filesize($logfile) > 2048000) {
			PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
			$hash = '';
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
			for($i = 0; $i < 4; $i++) {
				$hash .= $chars[mt_rand(0, 61)];
			}
			@rename($logfile, UC_ROOT.'./data/logs/'.gmdate('Ym', $this->time).'_'.$hash.'.php');
		}
		if($fp = @fopen($logfile, 'a')) {
			@flock($fp, 2);
			@fwrite($fp, "<?PHP exit;?>\t".str_replace(array('<?', '?>', '<?php'), '', $log)."\n");
			@fclose($fp);
		}
	}

	function fetch_plugins() {
		$plugindir = UC_ROOT.'./plugin';
		$d = opendir($plugindir);
		while($f = readdir($d)) {
			if($f != '.' && $f != '..' && is_dir($plugindir.'/'.$f)) {
				$pluginxml = $plugindir.$f.'/plugin.xml';
				$plugins[] = xml_unserialize($pluginxml);
			}
		}
	}

	function _call($a, $arg) {
		if(method_exists($this, $a) && $a[0] != '_') {
			$this->$a();
		} else {
			exit('Method does not exists');
		}
	}

	function sid_encode($username) {
		$ip = $this->onlineip;
		$agent = $_SERVER['HTTP_USER_AGENT'];
		$authkey = md5($ip.$agent.UC_KEY);
		$check = substr(md5($ip.$agent), 0, 8);
		return rawurlencode($this->authcode("$username\t$check", 'ENCODE', $authkey, 1800));
	}

	function sid_decode($sid) {
		$ip = $this->onlineip;
		$agent = $_SERVER['HTTP_USER_AGENT'];
		$authkey = md5($ip.$agent.UC_KEY);
		$s = $this->authcode(rawurldecode($sid), 'DECODE', $authkey, 1800);
		if(empty($s)) {
			return FALSE;
		}
		@list($username, $check) = explode("\t", $s);
		if($check == substr(md5($ip.$agent), 0, 8)) {
			return $username;
		} else {
			return FALSE;
		}
	}

}

?>