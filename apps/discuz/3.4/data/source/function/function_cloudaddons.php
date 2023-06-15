<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_cloudaddons.php 36333 2016-12-30 02:29:39Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$addonsource = $_G['config']['addonsource'] ? $_G['config']['addonsource'] : ($_G['setting']['addon_source'] ? $_G['setting']['addon_source'] : array());
$addon = $addonsource ?
	$_G['config']['addon'][$addonsource] :
	array(
		'website_url' => 'https://addon.dismall.com',
		'download_url' => 'https://addon.dismall.com/index.php',
		'download_ip' => '',
		'check_url' => 'https://addon1.dismall.com/md5/',
		'check_ip' => '',
	);

define('CLOUDADDONS_WEBSITE_URL', $addon['website_url']);
define('CLOUDADDONS_DOWNLOAD_URL', $addon['download_url']);
define('CLOUDADDONS_DOWNLOAD_IP', $addon['download_ip']);
define('CLOUDADDONS_CHECK_URL', $addon['check_url']);
define('CLOUDADDONS_CHECK_IP', $addon['check_ip']);

function cloudaddons_md5($file) {
	return dfsockopen(CLOUDADDONS_CHECK_URL.$file, 0, '', '', false, CLOUDADDONS_CHECK_IP, 999);
}

function cloudaddons_getuniqueid() {
	global $_G;
	if(CLOUDADDONS_WEBSITE_URL == 'https://addon.dismall.com') {
		return $_G['setting']['siteuniqueid'] ? $_G['setting']['siteuniqueid'] : C::t('common_setting')->fetch('siteuniqueid');
	} else {
		if(!$_G['setting']['addon_uniqueid']) {
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
			$addonuniqueid = $chars[date('y')%60].$chars[date('n')].$chars[date('j')].$chars[date('G')].$chars[date('i')].$chars[date('s')].substr(md5($_G['clientip'].TIMESTAMP), 0, 4).random(6);
			C::t('common_setting')->update('addon_uniqueid', $addonuniqueid);
			require_once libfile('function/cache');
			updatecache('setting');
		}
		return $_G['setting']['addon_uniqueid'];
	}
}
function cloudaddons_url($extra) {
	global $_G;

	require_once DISCUZ_ROOT.'./source/discuz_version.php';
	$data = 'siteuniqueid='.rawurlencode(cloudaddons_getuniqueid()).'&siteurl='.rawurlencode($_G['siteurl']).'&sitever='.DISCUZ_VERSION.'/'.DISCUZ_RELEASE.'&sitecharset='.CHARSET.'&mysiteid='.$_G['setting']['my_siteid'].'&addonversion=1&dlip='.CLOUDADDONS_DOWNLOAD_IP.'&os='.PHP_OS .'&php='.PHP_VERSION.'&web='.$_SERVER['SERVER_SOFTWARE'].'&db='.helper_dbtool::dbversion().'&lang='.currentlang();
	$param = 'data='.rawurlencode(base64_encode($data));
	$param .= '&md5hash='.substr(md5($data.TIMESTAMP), 8, 8).'&timestamp='.TIMESTAMP;
	return CLOUDADDONS_DOWNLOAD_URL.'?'.$param.$extra;
}

function cloudaddons_check() {
	if(!function_exists('gzuncompress')) {
		cpmsg('cloudaddons_check_gzuncompress_error', '', 'error');
	}

	if(!function_exists('curl_init') || !function_exists('curl_exec')) {
		cpmsg('cloudaddons_check_curl_error', '', 'error');
	}

	foreach(array('download', 'addonmd5') as $path) {
		$tmpdir = DISCUZ_ROOT.'./data/'.$path.'/'.random(5);
		$tmpfile = $tmpdir.'/index.html';
		dmkdir($tmpdir, 0777);
		if(!is_dir($tmpdir) || !file_exists($tmpfile)) {
			cpmsg('cloudaddons_check_write_error', '', 'error');
		}
		@unlink($tmpfile);
		@rmdir($tmpdir);
		if(is_dir($tmpdir) || file_exists($tmpfile)) {
			cpmsg('cloudaddons_check_write_error', '', 'error');
		}
	}
}

function cloudaddons_open($extra, $post = '', $timeout = 15) {
	return dfsockopen(cloudaddons_url('&from=s').$extra, 0, $post, '', false, CLOUDADDONS_DOWNLOAD_IP, $timeout);
}

function cloudaddons_pluginlogo_url($id) {
	return CLOUDADDONS_WEBSITE_URL.'?_'.$id;
}

function cloudaddons_installlog($addonid) {
	$array = cloudaddons_getmd5($addonid);
	if($array['RevisionID']) {
		cloudaddons_open('&mod=app&ac=installlog&rid='.$array['RevisionID']);
	}
}

function cloudaddons_downloadlog($addonid) {
	$array = cloudaddons_getmd5($addonid);
	if($array['RevisionID']) {
		cloudaddons_open('&mod=app&ac=downloadlog&rid='.$array['RevisionID']);
	}
}

function cloudaddons_faillog($rid, $type) {
	$rid = intval($rid);
	$type = intval($type);
	cloudaddons_open('&mod=app&ac=faillog&rid='.$rid.'&type='.$type.'&serverinfo='.urlencode($_SERVER['SERVER_SOFTWARE']));
}

function cloudaddons_removelog($rid) {
	global $_G;
	cloudaddons_open('&mod=app&ac=removelog&rid='.$rid);
}

function cloudaddons_validator($addonid) {
	$array = cloudaddons_getmd5($addonid);
	if(cloudaddons_open('&mod=app&ac=validator&ver=2&addonid='.$addonid.($array !== false ? '&rid='.$array['RevisionID'].'&sn='.$array['SN'].'&rd='.$array['RevisionDateline'] : '')) === '0') {
		cpmsg('cloudaddons_genuine_message', '', 'error', array('addonid' => $addonid));
	}
}

function cloudaddons_upgradecheck($addonids) {
	$post = array();
	foreach($addonids as $addonid) {
		$array = cloudaddons_getmd5($addonid);
		$post[] = 'rid['.$addonid.']='.$array['RevisionID'].'&sn['.$addonid.']='.$array['SN'].'&rd['.$addonid.']='.$array['RevisionDateline'];
	}
	return cloudaddons_open('&mod=app&ac=validator&ver=2', implode('&', $post), 15);
}

function cloudaddons_getmd5($md5file) {
	$array = array();
	if(preg_match('/^[a-z0-9_\.]+$/i', $md5file) && file_exists(DISCUZ_ROOT.'./data/addonmd5/'.$md5file.'.xml')) {
		require_once libfile('class/xml');
		$xml = implode('', @file(DISCUZ_ROOT.'./data/addonmd5/'.$md5file.'.xml'));
		$array = xml2array($xml);
	} else {
		return false;
	}
	return $array;
}

function cloudaddons_uninstall($md5file, $dir) {
	$array = cloudaddons_getmd5($md5file);
	if($array === false) {
		return;
	}
	if(!empty($array['RevisionID'])) {
		cloudaddons_removelog($array['RevisionID']);
	}
	@unlink(DISCUZ_ROOT.'./data/addonmd5/'.$md5file.'.xml');
	cloudaddons_cleardir($dir);
}

function cloudaddons_savemd5($md5file, $end, $md5) {
	global $_G;
	parse_str($end, $r);
	require_once libfile('class/xml');
	$xml = implode('', @file(DISCUZ_ROOT.'./data/addonmd5/'.$md5file.'.xml'));
	$array = xml2array($xml);
	$ridexists = false;
	$data = array();
	if($array['RevisionID']) {
		foreach(explode(',', $array['RevisionID']) as $i => $rid) {
			$sns = explode(',', $array['SN']);
			$datalines = explode(',', $array['RevisionDateline']);
			$data[$rid]['SN'] = $sns[$i];
			$data[$rid]['RevisionDateline'] = $datalines[$i];
		}
	}
	$data[$r['RevisionID']]['SN'] = $r['SN'];
	$data[$r['RevisionID']]['RevisionDateline'] = $r['RevisionDateline'];
	$array['Title'] = 'Discuz! Addon MD5';
	$array['ID'] = $r['ID'];
	$array['RevisionDateline'] = $array['SN'] = $array['RevisionID'] = array();
	foreach($data as $rid => $tmp) {
		$array['RevisionID'][] = $rid;
		$array['SN'][] = $tmp['SN'];
		$array['RevisionDateline'][] = $tmp['RevisionDateline'];
	}
	$array['RevisionID'] = implode(',', $array['RevisionID']);
	$array['SN'] = implode(',', $array['SN']);
	$array['RevisionDateline'] = implode(',', $array['RevisionDateline']);
	$array['Data'] = $array['Data'] ? array_merge($array['Data'], $md5) : $md5;
	if(!isset($_G['siteftp'])) {
		dmkdir(DISCUZ_ROOT.'./data/addonmd5/', 0777, false);
		$fp = fopen(DISCUZ_ROOT.'./data/addonmd5/'.$md5file.'.xml', 'w');
		fwrite($fp, array2xml($array));
		fclose($fp);
	} else {
		$localfile = DISCUZ_ROOT.'./data/'.random(5);
		$fp = fopen($localfile, 'w');
		fwrite($fp, array2xml($array));
		fclose($fp);
		dmkdir(DISCUZ_ROOT.'./data/addonmd5/', 0777, false);
		siteftp_upload($localfile, 'data/addonmd5/'.$md5file.'.xml');
		@unlink($localfile);
	}
}

function cloudaddons_comparetree($new, $old, $basedir, $md5file = '', $first = 0) {
	global $_G;
	if($first && file_exists(DISCUZ_ROOT.'./data/addonmd5/'.$md5file.'.xml')) {
		require_once libfile('class/xml');
		$xml = implode('', @file(DISCUZ_ROOT.'./data/addonmd5/'.$md5file.'.xml'));
		$array = xml2array($xml);
		$_G['treeop']['md5old'] = $array['Data'];
	}

	$dh = opendir($new);
	while(($file = readdir($dh)) !== false) {
		if($file != '.' && $file != '..') {
			$newfile = $new.'/'.$file;
			$oldfile = $old.'/'.$file;
			if(is_file($newfile)) {
				$oldfile = preg_replace('/\._addons_$/', '', $oldfile);
				$md5key = str_replace($basedir, '', preg_replace('/\._addons_$/', '', $newfile));
				$newmd5 = md5_file($newfile);
				$oldmd5 = file_exists($oldfile) ? md5_file($oldfile) : '';
				if(isset($_G['treeop']['md5old'][$md5key]) && $_G['treeop']['md5old'][$md5key] != $oldmd5 && $oldmd5) {
					$_G['treeop']['oldchange'][] = $md5key;
				}
				if($newmd5 != $oldmd5) {
					$_G['treeop']['copy'][] = $newfile;
				}
				$_G['treeop']['md5'][$md5key] = $newmd5;
			} else {
				cloudaddons_comparetree($newfile, $oldfile, $basedir);
			}
		}
	}
}

function cloudaddons_copytree($from, $to) {
	global $_G;
	$dh = opendir($from);
	while(($file = readdir($dh)) !== false) {
		if($file != '.' && $file != '..') {
			$readfile = $from.'/'.$file;
			$writefile = $to.'/'.$file;
			if(is_file($readfile)) {
				if(!in_array($readfile, $_G['treeop']['copy'])) {
					continue;
				}
				if(!isset($_G['siteftp'])) {
					$content = -1;
					if($fp = @fopen($readfile, 'r')) {
						$startTime = microtime();
						do {
							$canRead = flock($fp, LOCK_SH);
							if(!$canRead) {
								usleep(round(rand(0, 100) * 1000));
							}
						} while ((!$canRead) && ((microtime() - $startTime) < 1000));

						if(!$canRead) {
							cpmsg('cloudaddons_file_read_error', '', 'error');
						}
						$content = fread($fp, filesize($readfile));
						flock($fp, LOCK_UN);
						fclose($fp);
					}
					if($content < 0) {
						cpmsg('cloudaddons_file_read_error', '', 'error');
					}
					dmkdir(dirname($writefile), 0777, false);
					$writefile = preg_replace('/\._addons_$/', '', $writefile);
					if($fp = fopen($writefile, 'w')) {
						$startTime = microtime();
						do {
							$canWrite = flock($fp, LOCK_EX);
							if(!$canWrite) {
								usleep(round(rand(0, 100) * 1000));
							}
						} while ((!$canWrite) && ((microtime() - $startTime) < 1000));

						if(!$canWrite) {
							cpmsg('cloudaddons_file_write_error', '', 'error');
						}
						fwrite($fp, $content);
						flock($fp, LOCK_UN);
						fclose($fp);
					}
					if(!$canWrite) {
						cpmsg('cloudaddons_file_write_error', '', 'error');
					}
				} else {
					$writefile = preg_replace('/\._addons_$/', '', $writefile);
					siteftp_upload($readfile, preg_replace('/^'.preg_quote(DISCUZ_ROOT).'/', '', $writefile));
				}
				if(md5_file($readfile) != md5_file($writefile)) {
					cpmsg('cloudaddons_file_write_error', '', 'error');
				}
			} else {
				cloudaddons_copytree($readfile, $writefile);
			}

		}
	}
}

function cloudaddons_deltree($dir) {
	if($directory = @dir($dir)) {
		while($entry = $directory->read()) {
			if($entry == '.' || $entry == '..') {
				continue;
			}
			$filename = $dir.'/'.$entry;
			if(is_file($filename)) {
				@unlink($filename);
			} else {
				cloudaddons_deltree($filename);
			}
		}
		$directory->close();
		@rmdir($dir);
	}
}

function cloudaddons_cleardir($dir) {
	if(is_dir($dir)) {
		cloudaddons_deltree($dir);
	}
}

function cloudaddons_dirwriteable($basedir, $dir, $sourcedir) {
	$checkdirs = array($dir);
	cloudaddons_getsubdirs($sourcedir, $dir, $checkdirs);
	$return = array();
	foreach($checkdirs as $k => $dir) {
		$writeable = false;
		$checkdir = $basedir.'/'.$dir;
		if(!is_dir($checkdir)) {
			@mkdir($checkdir, 0777);
		}
		if(is_dir($checkdir)) {
			$fp = fopen($checkdir.'/test.txt', 'w');
			if($fp) {
				fclose($fp);
				unlink($checkdir.'/test.txt');
				$writeable = true;
			} else {
				$writeable = false;
			}
		}
		if(!$writeable && $dir) {
			$return[] = $dir;
		}
	}
	return $return;
}

function cloudaddons_getsubdirs($dir, $root, &$return) {
	static $prefix = false;
	if($prefix === false) {
		$prefix = strlen($dir) + 1;
	}
	$dh = opendir($dir);
	while(($file = readdir($dh)) !== false) {
		if($file != '.' && $file != '..') {
			$readfile = $dir.'/'.$file;
			if(is_dir($readfile)) {
				$return[] = $root.'/'.substr($readfile, $prefix);
				cloudaddons_getsubdirs($readfile, $root, $return);
			}
		}
	}
}

function cloudaddons_http_build_query($formdata, $numeric_prefix = null, $key = null) {
	$res = array();
	foreach((array) $formdata as $k => $v) {
		$tmp_key = urlencode(is_int($k) ? $numeric_prefix . $k : $k);
		if ($key) {
			$tmp_key = $key.'['.$tmp_key.']';
		}
		if (is_array($v) || is_object($v)) {
			$res[] = cloudaddons_http_build_query($v, null, $tmp_key);
		} else {
			$res[] = $tmp_key.'='.urlencode($v);
		}
	}
	return implode('&', $res);
}

function cloudaddons_clear($type, $id) {
	global $_G;
	if(isset($_G['config']['plugindeveloper']) && $_G['config']['plugindeveloper'] > 0) {
		return;
	}
	$dirs = array('plugin' => array('plugin', './source/plugin/'), 'template' => array('style', './template/'));
	if($dirs[$type] && cloudaddons_getmd5($id.'.'.$type)) {
		$entrydir = DISCUZ_ROOT.$dirs[$type][1].$id;
		$d = dir($entrydir);
		$filedeleted = false;
		while($f = $d->read()) {
			if(preg_match('/^discuz\_'.$dirs[$type][0].'\_'.$id.'(\_\w+)?\.xml$/', $f)) {
				if($type == 'plugin' && !$filedeleted) {
					$pluginarray = getimportdata('Discuz! Plugin');
					if($pluginarray['installfile']) {
						@unlink($entrydir.'/'.$pluginarray['installfile']);
					}
					if($pluginarray['upgradefile']) {
						@unlink($entrydir.'/'.$pluginarray['upgradefile']);
					}
					$filedeleted = true;
				}
				@unlink($entrydir.'/'.$f);
			}
		}
	}
}

function versioncompatible($versions) {
	global $_G;
	list($currentversion) = explode(' ', trim(strip_tags($_G['setting']['version'])));
	$versions = strip_tags($versions);
	foreach(explode(',', $versions) as $version) {
		list($version) = explode(' ', trim($version));
		if($version && ($currentversion === $version || $version === 'X3' || $version === 'X3.1' || $version === 'X3.2')) {
			return true;
		}
	}
	return false;
}

?>