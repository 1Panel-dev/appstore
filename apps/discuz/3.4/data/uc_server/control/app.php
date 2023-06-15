<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: app.php 1102 2011-05-30 09:40:42Z svn_project_zhangjie $
*/

!defined('IN_UC') && exit('Access Denied');

class appcontrol extends base {

	function __construct() {
		$this->appcontrol();
	}

	function appcontrol() {
		parent::__construct();
		$this->load('app');
		$this->load('user');
	}

	function onls() {
		$this->init_input();
		$applist = $_ENV['app']->get_apps('appid, type, name, url, tagtemplates, viewprourl, synlogin');
		$applist2 = array();
		foreach($applist as $key => $app) {
			$app['tagtemplates'] = $this->unserialize($app['tagtemplates']);
			$applist2[$app['appid']] = $app;
		}
		return $applist2;
	}

	function onadd() {
		$ucfounderpw = getgpc('ucfounderpw', 'P');
		$apptype = getgpc('apptype', 'P');
		$appname = getgpc('appname', 'P');
		$appurl = getgpc('appurl', 'P');
		$appip = getgpc('appip', 'P');
		$apifilename = trim(getgpc('apifilename', 'P'));
		$viewprourl = getgpc('viewprourl', 'P');
		$appcharset = getgpc('appcharset', 'P');
		$appdbcharset = getgpc('appdbcharset', 'P');
		$apptagtemplates = getgpc('apptagtemplates', 'P');
		$appallowips = getgpc('allowips', 'P');

		$apifilename = $apifilename ? $apifilename : 'uc.php';

		if(!$this->settings['addappbyurl'] || !$_ENV['user']->can_do_login('UCenterAdministrator', $this->onlineip)) {
			exit('-1');
		}

		if(md5(md5($ucfounderpw).UC_FOUNDERSALT) == UC_FOUNDERPW || (strlen($ucfounderpw) == 32 && $ucfounderpw == md5(UC_FOUNDERPW))) {
			@ob_start();
			$return  = '';

			$this->_writelog('login', 'succeed_by_url_add_app');

			$app = $this->db->fetch_first("SELECT * FROM ".UC_DBTABLEPRE."applications WHERE url='$appurl' AND type='$apptype'");

			if(empty($app)) {
				$authkey = $this->generate_key(64);
				$apptagtemplates = $this->serialize($apptagtemplates, 1);
				$this->db->query("INSERT INTO ".UC_DBTABLEPRE."applications SET
					name='$appname',
					url='$appurl',
					ip='$appip',
					apifilename='$apifilename',
					authkey='$authkey',
					viewprourl='$viewprourl',
					synlogin='1',
					charset='$appcharset',
					dbcharset='$appdbcharset',
					type='$apptype',
					recvnote='1',
					tagtemplates='$apptagtemplates',
					allowips='$appallowips'
					");
				$appid = $this->db->insert_id();

				$this->_writelog('app_add', "appid=$appid; appname=$appname; by=url_add");

				$_ENV['app']->alter_app_table($appid, 'ADD');
				$return = "$authkey|$appid|".UC_DBHOST.'|'.UC_DBNAME.'|'.UC_DBUSER.'|'.UC_DBPW.'|'.UC_DBCHARSET.'|'.UC_DBTABLEPRE.'|'.UC_CHARSET;
				$this->load('cache');
				$_ENV['cache']->updatedata('apps');

				$this->load('note');
				$notedata = $this->db->fetch_all("SELECT appid, type, name, url, ip, charset, synlogin, extra FROM ".UC_DBTABLEPRE."applications");
				$notedata = $this->_format_notedata($notedata);
				$notedata['UC_API'] = UC_API;
				$_ENV['note']->add('updateapps', '', $this->serialize($notedata, 1));
				$_ENV['note']->send();
			} else {
				$this->_writelog('app_queryinfo', "appid=$app[appid]; by=url_add");
				$return = "$app[authkey]|$app[appid]|".UC_DBHOST.'|'.UC_DBNAME.'|'.UC_DBUSER.'|'.UC_DBPW.'|'.UC_DBCHARSET.'|'.UC_DBTABLEPRE.'|'.UC_CHARSET;
			}
			@ob_end_clean();
			exit($return);
		} else {
			$pwlen = strlen($ucfounderpw);
			$this->_writelog('login', 'error_by_url_add_app: user=UCenterAdministrator; password='.($pwlen > 2 ? preg_replace("/^(.{".round($pwlen / 4)."})(.+?)(.{".round($pwlen / 6)."})$/s", "\\1***\\3", $ucfounderpw) : $ucfounderpw));

			$_ENV['user']->loginfailed('UCenterAdministrator', $this->onlineip);

			exit('-1');
		}
	}

	function onucinfo() {
		if(!$this->settings['addappbyurl']) {
			exit('-1');
		}

		$arrapptypes = $this->db->fetch_all("SELECT DISTINCT type FROM ".UC_DBTABLEPRE."applications");
		$apptypes = $tab = '';
		foreach($arrapptypes as $apptype) {
			$apptypes .= $tab.$apptype['type'];
			$tab = "\t";
		}
		exit("UC_STATUS_OK|".UC_SERVER_VERSION."|".UC_SERVER_RELEASE."|".UC_CHARSET."|".UC_DBCHARSET."|".$apptypes);
	}

	function _format_notedata($notedata) {
		$arr = array();
		foreach($notedata as $key => $note) {
			$arr[$note['appid']] = $note;
		}
		return $arr;
	}

	function _writelog($action, $extra = '') {
		$log = dhtmlspecialchars('UCenterAdministrator'."\t".$this->onlineip."\t".$this->time."\t$action\t$extra");
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

}

?>