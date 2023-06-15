<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_plugin.php 36284 2016-12-12 00:47:50Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/cloudaddons');

function plugininstall($pluginarray, $installtype = '', $available = 0) {
	if(!$pluginarray || !$pluginarray['plugin']['identifier']) {
		return false;
	}
	$plugin = C::t('common_plugin')->fetch_by_identifier($pluginarray['plugin']['identifier']);
	if($plugin) {
		return false;
	}

	$pluginarray['plugin']['modules'] = dunserialize($pluginarray['plugin']['modules']);
	$pluginarray['plugin']['modules']['extra']['installtype'] = $installtype;
	if(updatepluginlanguage($pluginarray)) {
		$pluginarray['plugin']['modules']['extra']['langexists'] = 1;
	}
	if(!empty($pluginarray['intro'])) {
		if(!empty($pluginarray['intro'])) {
			require_once libfile('function/discuzcode');
			$pluginarray['plugin']['modules']['extra']['intro'] = discuzcode(strip_tags($pluginarray['intro']), 1, 0);
		}
	}
	if(!empty($pluginarray['uninstallfile'])) {
		$pluginarray['plugin']['modules']['extra']['uninstallfile'] = $pluginarray['uninstallfile'];
	}
	if(!empty($pluginarray['checkfile'])) {
		$pluginarray['plugin']['modules']['extra']['checkfile'] = $pluginarray['checkfile'];
	}
	if(!empty($pluginarray['enablefile'])) {
		$pluginarray['plugin']['modules']['extra']['enablefile'] = $pluginarray['enablefile'];
	}
	if(!empty($pluginarray['disablefile'])) {
		$pluginarray['plugin']['modules']['extra']['disablefile'] = $pluginarray['disablefile'];
	}

	$pluginarray['plugin']['modules'] = serialize($pluginarray['plugin']['modules']);

	$data = array();
	foreach($pluginarray['plugin'] as $key => $val) {
		if($key == 'directory') {
			$val .= (!empty($val) && substr($val, -1) != '/') ? '/' : '';
		} elseif($key == 'available') {
			$val = $available;
		}
		$data[$key] = $val;
	}

	$pluginid = C::t('common_plugin')->insert($data, true);

	if(is_array($pluginarray['var'])) {
		foreach($pluginarray['var'] as $config) {
			$data = array('pluginid' => $pluginid);
			foreach($config as $key => $val) {
				$data[$key] = $val;
			}
			C::t('common_pluginvar')->insert($data);
		}
	}

	if(!empty($dir) && !empty($pluginarray['importfile'])) {
		require_once libfile('function/importdata');
		foreach($pluginarray['importfile'] as $importtype => $file) {
			if(in_array($importtype, array('smilies', 'styles'))) {
				$files = explode(',', $file);
				foreach($files as $file) {
					if(file_exists($file = DISCUZ_ROOT.'./source/plugin/'.$dir.'/'.$file)) {
						$importtxt = @implode('', file($file));
						$imporfun = 'import_'.$importtype;
						$imporfun();
					}
				}
			}
		}
	}

	cloudaddons_installlog($pluginarray['plugin']['identifier'].'.plugin');
	cron_create($pluginarray['plugin']['identifier']);
	updatecache(array('plugin', 'setting', 'styles'));
	cleartemplatecache();
	dsetcookie('addoncheck_plugin', '', -1);
	return $pluginid;
}

function pluginupgrade($pluginarray, $installtype) {
	if(!$pluginarray || !$pluginarray['plugin']['identifier']) {
		return false;
	}
	$plugin = C::t('common_plugin')->fetch_by_identifier($pluginarray['plugin']['identifier']);
	if(!$plugin) {
		return false;
	}
	if(is_array($pluginarray['var'])) {
		$pluginvars = $pluginvarsnew = array();
		foreach(C::t('common_pluginvar')->fetch_all_by_pluginid($plugin['pluginid']) as $pluginvar) {
			$pluginvars[] = $pluginvar['variable'];
		}
		foreach($pluginarray['var'] as $config) {
			if(!in_array($config['variable'], $pluginvars)) {
				$data = array('pluginid' => $plugin[pluginid]);
				foreach($config as $key => $val) {
					$data[$key] = $val;
				}
				C::t('common_pluginvar')->insert($data);
			} else {
				$data = array();
				foreach($config as $key => $val) {
					if($key != 'value') {
						$data[$key] = $val;
					}
				}
				if($data) {
					C::t('common_pluginvar')->update_by_variable($plugin['pluginid'], $config['variable'], $data);
				}
			}
			$pluginvarsnew[] = $config['variable'];
		}
		$pluginvardiff = array_diff($pluginvars, $pluginvarsnew);
		if($pluginvardiff) {
			C::t('common_pluginvar')->delete_by_variable($plugin['pluginid'], $pluginvardiff);
		}
	}

	$langexists = updatepluginlanguage($pluginarray);

	$pluginarray['plugin']['modules'] = dunserialize($pluginarray['plugin']['modules']);
	$plugin['modules'] = dunserialize($plugin['modules']);
	if(!empty($plugin['modules']['system'])) {
		$pluginarray['plugin']['modules']['system'] = $plugin['modules']['system'];
	}
	$plugin['modules']['extra']['installtype'] = $installtype;
	$pluginarray['plugin']['modules']['extra'] = $plugin['modules']['extra'];
	if(!empty($pluginarray['intro']) || $langexists) {
		if(!empty($pluginarray['intro'])) {
			require_once libfile('function/discuzcode');
			$pluginarray['plugin']['modules']['extra']['intro'] = discuzcode(strip_tags($pluginarray['intro']), 1, 0);
		}
		$langexists && $pluginarray['plugin']['modules']['extra']['langexists'] = 1;
	}
	if(!empty($pluginarray['uninstallfile'])) {
		$pluginarray['plugin']['modules']['extra']['uninstallfile'] = $pluginarray['uninstallfile'];
	}
	if(!empty($pluginarray['checkfile'])) {
		$pluginarray['plugin']['modules']['extra']['checkfile'] = $pluginarray['checkfile'];
	}
	if(!empty($pluginarray['enablefile'])) {
		$pluginarray['plugin']['modules']['extra']['enablefile'] = $pluginarray['enablefile'];
	}
	if(!empty($pluginarray['disablefile'])) {
		$pluginarray['plugin']['modules']['extra']['disablefile'] = $pluginarray['disablefile'];
	}
	$pluginarray['plugin']['modules'] = serialize($pluginarray['plugin']['modules']);

	$data = array();
	foreach($pluginarray['plugin'] as $key => $val) {
		if($key == 'directory') {
			$val .= (!empty($val) && substr($val, -1) != '/') ? '/' : '';
		} elseif($key == 'available') {
			continue;
		}
		$data[$key] = $val;
	}

	C::t('common_plugin')->update($plugin['pluginid'], $data);

	cloudaddons_installlog($pluginarray['plugin']['identifier'].'.plugin');
	cron_create($pluginarray['plugin']['identifier']);
	updatecache(array('plugin', 'setting', 'styles'));
	cleartemplatecache();
	dsetcookie('addoncheck_plugin', '', -1);
	return true;
}

function modulecmp($a, $b) {
	return $a['displayorder'] > $b['displayorder'] ? 1 : -1;
}

function updatepluginlanguage($pluginarray) {
	global $_G;
	if(!$pluginarray['language']) {
		return false;
	}
	foreach(array('script', 'template', 'install', 'system') as $type) {
		loadcache('pluginlanguage_'.$type, 1);
		if(empty($_G['cache']['pluginlanguage_'.$type])) {
			$_G['cache']['pluginlanguage_'.$type] = array();
		}
		if($type != 'system') {
			if(!empty($pluginarray['language'][$type.'lang'])) {
				$_G['cache']['pluginlanguage_'.$type][$pluginarray['plugin']['identifier']] = $pluginarray['language'][$type.'lang'];
			}
		} else {
			if(!empty($_G['config']['plugindeveloper']) && @include(DISCUZ_ROOT.'./data/plugindata/'.$pluginarray['plugin']['identifier'].'.lang.php')) {
				if(!empty($systemlang[$pluginarray['plugin']['identifier']])) {
					$pluginarray['language']['systemlang'] = $systemlang[$pluginarray['plugin']['identifier']];
				}
			}
			foreach($pluginarray['language']['systemlang'] as $file => $vars) {
				foreach($vars as $key => $var) {
					$_G['cache']['pluginlanguage_system'][$file][$key] = $var;
				}
			}
		}
		savecache('pluginlanguage_'.$type, $_G['cache']['pluginlanguage_'.$type]);
	}
	return true;
}

function runquery($sql) {
	global $_G;
	$tablepre = $_G['config']['db'][1]['tablepre'];
	$dbcharset = $_G['config']['db'][1]['dbcharset'];

	$sql = str_replace(array(' cdb_', ' `cdb_', ' pre_', ' `pre_'), array(' {tablepre}', ' `{tablepre}', ' {tablepre}', ' `{tablepre}'), $sql);
	$sql = str_replace("\r", "\n", str_replace(array(' {tablepre}', ' `{tablepre}'), array(' '.$tablepre, ' `'.$tablepre), $sql));

	$ret = array();
	$num = 0;
	foreach(explode(";\n", trim($sql)) as $query) {
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= $query[0] == '#' || $query[0].$query[1] == '--' ? '' : $query;
		}
		$num++;
	}
	unset($sql);

	foreach($ret as $query) {
		$query = trim($query);
		if($query) {

			if(substr($query, 0, 12) == 'CREATE TABLE') {
				$name = preg_replace("/CREATE TABLE ([a-z0-9_]+) .*/is", "\\1", $query);
				DB::query(createtable($query, $dbcharset));

			} else {
				DB::query($query);
			}

		}
	}
}

function createtable($sql, $dbcharset) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
	(DB::$db->version() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=$dbcharset" : " TYPE=$type");
}

function updatetable($sql) {
	global $_G;

	$config = array(
		'dbcharset' => $_G['config']['db']['1']['dbcharset'],
		'charset' => $_G['config']['output']['charset'],
		'tablepre' => $_G['config']['db']['1']['tablepre']
	);

	preg_match_all("/CREATE\s+TABLE.+?pre\_(.+?)\s*\((.+?)\)\s*(ENGINE|TYPE)\s*=\s*(\w+)/is", $sql, $matches);
	$newtables = empty($matches[1])?array():$matches[1];
	$newsqls = empty($matches[0])?array():$matches[0];
	if(empty($newtables) || empty($newsqls)) {
		return array(1);
	}

	foreach($newtables as $i => $newtable) {
		$newcols = updatetable_getcolumn($newsqls[$i]);

		if(!$query = DB::query("SHOW CREATE TABLE ".DB::table($newtable), 'SILENT')) {
			preg_match("/(CREATE TABLE .+?)\s*(ENGINE|TYPE)\s*=\s*(\w+)/is", $newsqls[$i], $maths);

			$maths[3] = strtoupper($maths[3]);
			if($maths[3] == 'MEMORY' || $maths[3] == 'HEAP') {
				$type = helper_dbtool::dbversion() > '4.1' ? " ENGINE=MEMORY".(empty($config['dbcharset'])?'':" DEFAULT CHARSET=$config[dbcharset]" ): " TYPE=HEAP";
			} else {
				$type = helper_dbtool::dbversion() > '4.1' ? " ENGINE=MYISAM".(empty($config['dbcharset'])?'':" DEFAULT CHARSET=$config[dbcharset]" ): " TYPE=MYISAM";
			}
			$usql = $maths[1].$type;

			$usql = str_replace("CREATE TABLE IF NOT EXISTS pre_", 'CREATE TABLE IF NOT EXISTS '.$config['tablepre'], $usql);
			$usql = str_replace("CREATE TABLE pre_", 'CREATE TABLE '.$config['tablepre'], $usql);

			if(!DB::query($usql, 'SILENT')) {
				return array(-1, $newtable);
			}
		} else {
			$value = DB::fetch($query);
			$oldcols = updatetable_getcolumn($value['Create Table']);

			$updates = array();
			$allfileds =array_keys($newcols);
			foreach ($newcols as $key => $value) {
				if($key == 'PRIMARY') {
					if($value != $oldcols[$key]) {
						if(!empty($oldcols[$key])) {
							$usql = "RENAME TABLE ".DB::table($newtable)." TO ".DB::table($newtable.'_bak');
							if(!DB::query($usql, 'SILENT')) {
								return array(-1, $newtable);
							}
						}
						$updates[] = "ADD PRIMARY KEY $value";
					}
				} elseif ($key == 'KEY') {
					foreach ($value as $subkey => $subvalue) {
						if(!empty($oldcols['KEY'][$subkey])) {
							if($subvalue != $oldcols['KEY'][$subkey]) {
								$updates[] = "DROP INDEX `$subkey`";
								$updates[] = "ADD INDEX `$subkey` $subvalue";
							}
						} else {
							$updates[] = "ADD INDEX `$subkey` $subvalue";
						}
					}
				} elseif ($key == 'UNIQUE') {
					foreach ($value as $subkey => $subvalue) {
						if(!empty($oldcols['UNIQUE'][$subkey])) {
							if($subvalue != $oldcols['UNIQUE'][$subkey]) {
								$updates[] = "DROP INDEX `$subkey`";
								$updates[] = "ADD UNIQUE INDEX `$subkey` $subvalue";
							}
						} else {
							$usql = "ALTER TABLE  ".DB::table($newtable)." DROP INDEX `$subkey`";
							DB::query($usql, 'SILENT');
							$updates[] = "ADD UNIQUE INDEX `$subkey` $subvalue";
						}
					}
				} else {
					if(!empty($oldcols[$key])) {
						if(strtolower($value) != strtolower($oldcols[$key])) {
							$updates[] = "CHANGE `$key` `$key` $value";
						}
					} else {
						$i = array_search($key, $allfileds);
						$fieldposition = $i > 0 ? 'AFTER `'.$allfileds[$i-1].'`' : 'FIRST';
						$updates[] = "ADD `$key` $value $fieldposition";
					}
				}
			}

			if(!empty($updates)) {
				$usql = "ALTER TABLE ".DB::table($newtable)." ".implode(', ', $updates);
				if(!DB::query($usql, 'SILENT')) {
					return array(-1, $newtable);
				}
			}
		}
	}
	return array(1);
}

function updatetable_getcolumn($creatsql) {

	$creatsql = preg_replace("/ COMMENT '.*?'/i", '', $creatsql);
	preg_match("/\((.+)\)\s*(ENGINE|TYPE)\s*\=/is", $creatsql, $matchs);

	$cols = explode("\n", $matchs[1]);
	$newcols = array();
	foreach ($cols as $value) {
		$value = trim($value);
		if(empty($value)) continue;
		$value = updatetable_remakesql($value);
		if(substr($value, -1) == ',') $value = substr($value, 0, -1);

		$vs = explode(' ', $value);
		$cname = $vs[0];

		if($cname == 'KEY' || $cname == 'INDEX' || $cname == 'UNIQUE') {

			$name_length = strlen($cname);
			if($cname == 'UNIQUE') $name_length = $name_length + 4;

			$subvalue = trim(substr($value, $name_length));
			$subvs = explode(' ', $subvalue);
			$subcname = $subvs[0];
			$newcols[$cname][$subcname] = trim(substr($value, ($name_length+2+strlen($subcname))));

		}  elseif($cname == 'PRIMARY') {

			$newcols[$cname] = trim(substr($value, 11));

		}  else {

			$newcols[$cname] = trim(substr($value, strlen($cname)));
		}
	}
	return $newcols;
}

function updatetable_remakesql($value) {
	$value = trim(preg_replace("/\s+/", ' ', $value));
	$value = str_replace(array('`',', ', ' ,', '( ' ,' )', 'mediumtext'), array('', ',', ',','(',')','text'), $value);
	return $value;
}

function cron_create($pluginid, $filename = null, $name = null, $weekday = null, $day = null, $hour = null, $minute = null) {
	if(!ispluginkey($pluginid)) {
		return false;
	}
	$dir = DISCUZ_ROOT.'./source/plugin/'.$pluginid.'/cron';
	if(!file_exists($dir)) {
		return false;
	}
	$crondir = dir($dir);
	while($filename = $crondir->read()) {
		if(!in_array($filename, array('.', '..')) && preg_match("/^cron\_[\w\.]+$/", $filename)) {
			$content = file_get_contents($dir.'/'.$filename);
			preg_match("/cronname\:(.+?)\n/", $content, $r);$name = lang('plugin/'.$pluginid, trim($r[1]));
			preg_match("/week\:(.+?)\n/", $content, $r);$weekday = trim($r[1]) ? intval($r[1]) : -1;
			preg_match("/day\:(.+?)\n/", $content, $r);$day = trim($r[1]) ? intval($r[1]) : -1;
			preg_match("/hour\:(.+?)\n/", $content, $r);$hour = trim($r[1]) ? intval($r[1]) : -1;
			preg_match("/minute\:(.+?)\n/", $content, $r);$minute = trim($r[1]) ? trim($r[1]) : 0;
			$minutenew = explode(',', $minute);
			foreach($minutenew as $key => $val) {
				$minutenew[$key] = $val = intval($val);
				if($val < 0 || $var > 59) {
					unset($minutenew[$key]);
				}
			}
			$minutenew = array_slice(array_unique($minutenew), 0, 12);
			$minutenew = implode("\t", $minutenew);
			$filename = $pluginid.':'.$filename;
			$cronid = C::t('common_cron')->get_cronid_by_filename($filename);
			if(!$cronid) {
				C::t('common_cron')->insert(array(
					'available' => 1,
					'type' => 'plugin',
					'name' => $name,
					'filename' => $filename,
					'weekday' => $weekday,
					'day' => $day,
					'hour' => $hour,
					'minute' => $minutenew,
				), true);
			} else {
				C::t('common_cron')->update($cronid, array(
					'name' => $name,
					'weekday' => $weekday,
					'day' => $day,
					'hour' => $hour,
					'minute' => $minutenew,
				));
			}
		}
	}
	return true;
}

function cron_delete($pluginid) {
	if(!ispluginkey($pluginid)) {
		return false;
	}
	$dir = DISCUZ_ROOT.'./source/plugin/'.$pluginid.'/cron';
	if(!file_exists($dir)) {
		return false;
	}
	$crondir = dir($dir);
	$count = 0;
	while($filename = $crondir->read()) {
		if(!in_array($filename, array('.', '..')) && preg_match("/^cron\_[\w\.]+$/", $filename)) {
			$filename = $pluginid.':'.$filename;
			$cronid = C::t('common_cron')->get_cronid_by_filename($filename);
			C::t('common_cron')->delete($cronid);
			$count++;
		}
	}
	return $count;
}

function domain_create($pluginid, $domain, $domainroot) {
	$plugin = C::t('common_plugin')->fetch_by_identifier($pluginid);
	if(!$plugin || !$plugin['available']) {
		return;
	}
	C::t('common_domain')->delete_by_id_idtype($plugin['pluginid'], 'plugin');
	$data = array(
		'id' => $plugin['pluginid'],
		'idtype' => 'plugin',
		'domain' => $domain,
		'domainroot' => $domainroot,
	);
	C::t('common_domain')->insert($data);
	require_once libfile('function/cache');
	updatecache('setting');
}

function domain_delete($pluginid) {
	$plugin = C::t('common_plugin')->fetch_by_identifier($pluginid);
	if(!$plugin || !$plugin['available']) {
		return;
	}
	C::t('common_domain')->delete_by_id_idtype($plugin['pluginid'], 'plugin');
	require_once libfile('function/cache');
	updatecache('setting');
}

?>