<?php


!defined('IN_UC') && exit('Access Denied');

class control extends pluginbase {

	function control() {
		$this->pluginbase();
	}

	function onindex() {

		if($this->submitcheck()) {
			if(!getgpc('reconfkey', 'P')) {
				$this->message('replacemykey_no_confirm', 'BACK');
			}
			if($this->_replacemykey()) {
				$this->message('replacemykey_succeed', 'BACK');
			} else {
				$this->message('replacemykey_false', 'BACK');
			}
		}

		$this->view->display('plugin_replacemykey');
	}

	function _replacemykey() {
		$oldmykey = UC_MYKEY;
		$newmykey = $this->generate_key();
		$configfile = UC_ROOT.'./data/config.inc.php';
		if(!is_writable($configfile)) {
			return false;
		}
		$config = file_get_contents($configfile);
		$config = preg_replace("/define\('UC_MYKEY',\s*'.*?'\);/i", "define('UC_MYKEY', '$newmykey');", $config);
		if(file_put_contents($configfile, $config, LOCK_EX) === false) {
			return false;
		}
		$apps = $this->db->fetch_all("SELECT appid, authkey FROM ".UC_DBTABLEPRE."applications", 'appid');
		foreach($apps as $k => $v) {
			if($tmp = $this->authcode($v['authkey'], 'DECODE', $oldmykey)) {
				$appid = $v['appid'];
				$appkey = $this->authcode($tmp, 'ENCODE', $newmykey);
				$this->db->query("UPDATE ".UC_DBTABLEPRE."applications SET authkey='$appkey' WHERE appid='$appid'");
				if($this->db->errno()) {
					return false;
				}
			}
		}
		return true;
	}

}