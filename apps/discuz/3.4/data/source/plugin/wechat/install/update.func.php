<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: update.func.php 34824 2014-08-12 02:27:09Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include_once libfile('function/plugin');

if(!function_exists('updatetable')) {

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

}

?>