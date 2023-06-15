<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_cloudaddons.php 36311 2016-12-19 01:47:34Z nemohou $
 */
if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

require_once libfile('function/cloudaddons');

cpheader();

if(!$admincp->isfounder) {
	cpmsg('noaccess_isfounder', '', 'error');
}

if(!$operation || in_array($operation, array('plugins', 'templates'))) {

	cloudaddons_check();
	shownav('cloudaddons');
	$extra = '';
	if(empty($_GET['frame'])) {
		parse_str($_SERVER['QUERY_STRING'], $query);
		$query['frame'] = 'no';
		$query_sting_tmp = http_build_query($query);
		$url = ADMINSCRIPT.'?'.$query_sting_tmp;
		echo '<script type="text/javascript">top.location.href=\''.$url.'\';</script>';
	} else {
		if(!empty($operation)) {
			$extra .= '&view='.rawurlencode($operation);
		}elseif(!empty($_GET['id'])) {
			$extra .= '&mod=app&ac=item&id='.rawurlencode($_GET['id']);
		}
		if(!empty($_GET['from'])) {
			$extra .= '&from='.rawurlencode($_GET['from']);
		}
		if(!empty($_GET['extra'])) {
			$extra .= '&'.addslashes($_GET['extra']);
		}
		$url = cloudaddons_url($extra);
		echo '<script type="text/javascript">location.href=\''.$url.'\';</script>';
	}

} elseif($operation == 'download') {
	$step = intval($_GET['step']);
	$addoni = intval($_GET['i']);
	if(!$_GET['md5hash'] || md5($_GET['addonids'].md5(cloudaddons_getuniqueid().$_GET['timestamp'])) != $_GET['md5hash']) {
		cpmsg('cloudaddons_validator_error', '', 'error');
	}
	$addonids = explode(',', $_GET['addonids']);
	list($_GET['key'], $_GET['type'], $_GET['rid']) = explode('.', isset($addonids[$addoni]) ? $addonids[$addoni] : $addonids[0]);
	if($step == 0) {
		cpmsg('cloudaddons_downloading', "action=cloudaddons&operation=download&addonids=$_GET[addonids]&i=$addoni&step=1&md5hash=".$_GET['md5hash'].'&timestamp='.$_GET['timestamp'], 'loading', array('addonid' => $_GET['key'].'.'.$_GET['type']), '<div>0%</div>', FALSE);
	} elseif($step == 1) {
		$packnum = isset($_GET['num']) ? $_GET['num'] : 0;
		$tmpdir = DISCUZ_ROOT.'./data/download/'.$_GET['rid'];
		$end = '';
		$md5tmp = DISCUZ_ROOT.'./data/download/'.$_GET['rid'].'.md5';
		if($packnum) {
			list($md5total, $md5s) = unserialize(implode('', @file($md5tmp)));
			dmkdir($tmpdir, 0777, false);
		} else {
			dir_clear($tmpdir);
			@unlink($md5tmp);
			dmkdir($tmpdir, 0777, false);
			$md5total = '';
			$md5s = array();
		}
		$data = cloudaddons_open('&mod=app&ac=download&rid='.$_GET['rid'].'&packnum='.$packnum.'&downloadts='.$_GET['timestamp'], '', 999);
		if(empty($data)){
			cpmsg('cloudaddons_downloading', "action=cloudaddons&operation=download&addonids=$_GET[addonids]&i=$addoni&step=1&md5hash=".$_GET['md5hash'].'&timestamp='.$_GET['timestamp'].'&num='.$packnum, 'loading', array('addonid' => $_GET['key'].'.'.$_GET['type']), '<div></div>', FALSE);
			exit;
		}
		$_GET['importtxt'] = $data;
		$array = getimportdata('Discuz! File Pack');
		if(!$array['Status']) {
			list($_cur, $_max) = explode('/', $array['part']);
			$percent = intval($_cur/$_max * 100);
			if($array['type'] != $_GET['type'] || $array['key'] != $_GET['key'] || !$array['files']) {
				dir_clear($tmpdir);
				@unlink($md5tmp);
				cloudaddons_faillog($_GET['rid'], 100);
				cpmsg('cloudaddons_download_error', '', 'error', array('ErrorCode' => 100));
			}
			foreach($array['files'] as $file => $data) {
				$filename = $tmpdir.'/'.$file.'._addons_';
				$dirname = dirname($filename);
				dmkdir($dirname, 0777, false);
				$fp = fopen($filename, !$data['Part'] ? 'w' : 'a');
				if(!$fp) {
					dir_clear($tmpdir);
					@unlink($md5tmp);
					cloudaddons_faillog($_GET['rid'], 101);
					cpmsg('cloudaddons_download_write_error', '', 'error');
				}
				fwrite($fp, gzuncompress(base64_decode($data['Data'])));
				fclose($fp);
				if($data['MD5']) {
					$md5total .= $data['MD5'];
					$md5s[$filename] = $data['MD5'];
				}
			}
			$fp = fopen($md5tmp, 'w');
			fwrite($fp, serialize(array($md5total, $md5s)));
			fclose($fp);
		} elseif($array['Status'] == 'Error') {
			dir_clear($tmpdir);
			@unlink($md5tmp);
			cloudaddons_faillog($_GET['rid'], $array['ErrorCode']);
			cpmsg('cloudaddons_install_error', '', 'error', array('ErrorCode' => $array['ErrorCode']));
		} else {
			foreach($md5s as $file => $md5) {
				if($md5 != md5_file($file)) {
					dir_clear($tmpdir);
					@unlink($md5tmp);
					cloudaddons_faillog($_GET['rid'], 102);
					cpmsg('cloudaddons_download_error', '', 'error', array('ErrorCode' => 102));
				}
			}
			@unlink($md5tmp);
			$end = rawurlencode(cloudaddons_http_build_query($array));
		}
		if(!$end) {
			$packnum++;
			cpmsg('cloudaddons_downloading', "action=cloudaddons&operation=download&addonids=$_GET[addonids]&i=$addoni&step=1&md5hash=".$_GET['md5hash'].'&timestamp='.$_GET['timestamp'].'&num='.$packnum, 'loading', array('addonid' => $_GET['key'].'.'.$_GET['type']), '<div>'.$percent.'%</div>', FALSE);
		} else {
			if($md5total !== '' && md5($md5total) !== cloudaddons_md5($_GET['key'].'_'.$_GET['rid'].(!empty($array['MD5Key']) ? '_'.$array['MD5Key'] : ''))) {
				dir_clear($tmpdir);
				@unlink($md5tmp);
				cloudaddons_faillog($_GET['rid'], 105);
				cpmsg('cloudaddons_download_error', '', 'error', array('ErrorCode' => 105));
			}
			cpmsg('cloudaddons_installing', "action=cloudaddons&operation=download&addonids=$_GET[addonids]&i=$addoni&end=$end&step=2&md5hash=".$_GET['md5hash'].'&timestamp='.$_GET['timestamp'], 'loading', array('addonid' => $_GET['key'].'.'.$_GET['type']), FALSE);
		}
	} elseif($step == 2) {
		$tmpdir = DISCUZ_ROOT.'./data/download/'.$_GET['rid'];
		if(!file_exists($tmpdir)) {
			dir_clear($tmpdir);
			cloudaddons_faillog($_GET['rid'], 103);
			cpmsg('cloudaddons_download_error', '', 'error', array('ErrorCode' => 103));
		}
		$typedir = array(
		    'plugin' => 'source/plugin',
		    'template' => 'template',
		    'pack' => '.',
		);
		if(!$typedir[$_GET['type']]) {
			dir_clear($tmpdir);
			cloudaddons_faillog($_GET['rid'], 104);
			cpmsg('cloudaddons_download_error', '', 'error', array('ErrorCode' => 104));
		}
		if($_GET['type'] != 'pack') {
			$descdir = DISCUZ_ROOT.$typedir[$_GET['type']].'/';
			$subdir = $_GET['key'];
		} else {
			$descdir = DISCUZ_ROOT;
			$subdir = '';
		}
		$unwriteabledirs = cloudaddons_dirwriteable($descdir, $subdir, $tmpdir);
		if($unwriteabledirs) {
			if(!submitcheck('settingsubmit')) {
				showtips(cplang('cloudaddons_unwriteabledirs', array('basedir' => $typedir[$_GET['type']] != '.' ? $typedir[$_GET['type']] : '/', 'unwriteabledirs' => implode(', ', $unwriteabledirs))));
				siteftp_form("cloudaddons&operation=download&addonids=$_GET[addonids]&i=$addoni&end=".rawurlencode($_GET['end'])."&step=2&md5hash=".$_GET['md5hash'].'&timestamp='.$_GET['timestamp']);
				exit;
			} else {
				siteftp_check($_GET['siteftp'], $typedir[$_GET['type']]);
			}
		}
		$descdir .= $subdir;
		cloudaddons_comparetree($tmpdir, $descdir, $tmpdir, $_GET['key'].'.'.$_GET['type'], 1);
		if(!empty($_G['treeop']['oldchange']) && empty($_GET['confirmed'])) {
			cpmsg('cloudaddons_install_files_changed', '', 'form', array('files' => implode('<br />', $_G['treeop']['oldchange'])));
		}
		cloudaddons_copytree($tmpdir, $descdir);
		cloudaddons_savemd5($_GET['key'].'.'.$_GET['type'], $_GET['end'], $_G['treeop']['md5']);
		cloudaddons_deltree($tmpdir);
		if(count($addonids) - 1 > $addoni) {
			$addoni++;
			cpmsg('cloudaddons_downloading', "action=cloudaddons&operation=download&addonids=$_GET[addonids]&i=$addoni&step=1&md5hash=".$_GET['md5hash'].'&timestamp='.$_GET['timestamp'], 'loading', array('addonid' => $_GET['key'].'.'.$_GET['type']), FALSE);
		}

		$extra = currentlang();
		$extra = $extra ? '_'.$extra : '';
		$batch = array();
		foreach ($addonids as $addonid) {
			list($key, $type, $rid) = explode('.', $addonid);
			cloudaddons_downloadlog($key.'.'.$type);
			if(empty($batch[$key.'.'.$type][1])) {
				if($type == 'plugin') {
					$entrytitle = $importtxt = '';
					$plugindir = DISCUZ_ROOT.'./source/plugin/'.$key;
					if(file_exists($plugindir.'/discuz_plugin_'.$key.$extra.'.xml')) {
						$importtxt = @implode('', file($plugindir.'/discuz_plugin_'.$key.$extra.'.xml'));
					} elseif(file_exists($plugindir.'/discuz_plugin_'.$key.'.xml')) {
						$importtxt = @implode('', file($plugindir.'/discuz_plugin_'.$key.'.xml'));
					}
					if(!empty($importtxt)) {
						$pluginarray = getimportdata('Discuz! Plugin', 0, 1);
						if(!empty($pluginarray['plugin']['name'])) {
							$entrytitle = dhtmlspecialchars($pluginarray['plugin']['name'].' '.$pluginarray['plugin']['version']);
						}
					}
					$plugin = C::t('common_plugin')->fetch_by_identifier($key);
					if(!$plugin['pluginid']) {
						$batch[$key.'.'.$type] = array(
							ADMINSCRIPT.'?action=plugins&operation=import&dir='.$key,
							$lang['plugins_config_install'].' '.$entrytitle,
						);
					} else {
						if ($pluginarray['plugin']['version'] != $plugin['version']) {
							$batch[$key.'.'.$type] = array(
								ADMINSCRIPT.'?action=plugins&operation=upgrade&pluginid='.$plugin['pluginid'],
								$lang['plugins_config_upgrade'].' '.$entrytitle,
							);
						}
					}
				} elseif($type == 'template') {
					$pluginarray = array();
					$entrytitle = $importtxt = '';
					$templatedir = DISCUZ_ROOT.'./template/'.$key;
					$searchdir = dir($templatedir);
					while($searchentry = $searchdir->read()) {
						if(substr($searchentry, 0, 13) == 'discuz_style_' && fileext($searchentry) == 'xml') {
							$importtxt = @implode('', file($templatedir.'/'.$searchentry));
							if(!empty($importtxt)) {
								break;
							}
						}
					}
					if(!empty($importtxt)) {
						$stylearray = getimportdata('Discuz! Style');
						if(!empty($stylearray['tplname'])) {
							$entrytitle = dhtmlspecialchars($stylearray['tplname']);
						}
					}
					$batch[$key.'.'.$type] = array(
						ADMINSCRIPT.'?action=styles&operation=import&dir='.$key,
						$entrytitle,
					);
				} else {
					cloudaddons_validator($key.'.pack');
					cloudaddons_installlog($key.'.pack');
					if(file_exists(DISCUZ_ROOT.'./data/addonpack/'.$key.'.php')) {
						$batch[$key.'.'.$type] = array(
							$_G['siteurl'].'data/addonpack/'.$key.'.php',
							$key.'.'.$type,
						);
					}
				}
			}
		}

		if(count($batch) > 1) {
			$message = '';
			foreach ($batch as $k => $v) {
				$message .= '<p class="margintop"><a href="'.$v[0].'&frames=yes" onclick="return removelink(this);" target="_blank">'.($v[1] ? $v[1] : $k).'</a></p>';
			}
			echo '<div class="infobox"><h4 class="infotitle2">'.cplang('cloudaddons_batch_succeed').'<br /><br /><div id="addonlist">'.$message.'</div></h4></div>
			<script type="text/javascript">
			function removelink(obj){
				if(document.getElementById(\'addonlist\').children.length > 1){
					obj.parentNode.remove();
				}else{
					obj.target = \'_top\';
				}
				return true;
			}
			</script>
			';
			exit;
		} elseif(count($batch) == 1) {
			$v = reset($batch);
			dheader('location: '.$v[0]);
		} else {
			list($_GET['key'], $_GET['type'], $_GET['rid']) = explode('.', $addonids[0]);
			if($_GET['type'] == 'plugin') {
				dheader('location: '.ADMINSCRIPT.'?action=plugins&operation=upgrade&pluginid='.$plugin['pluginid']);
			} else {
				cpmsg('cloudaddons_pack_installed', '', 'succeed');
			}
		}
	}
}

function dir_clear($dir) {
	if($directory = @dir($dir)) {
		while($entry = $directory->read()) {
			if($entry == '.' || $entry == '..') {
				continue;
			}
			$filename = $dir.'/'.$entry;
			if(is_file($filename)) {
				@unlink($filename);
			} else {
				dir_clear($filename);
			}
		}
		$directory->close();
		@rmdir($dir);
	}
}

?>