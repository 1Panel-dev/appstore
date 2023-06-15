<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_member_archive.php 29613 2012-04-23 04:19:05Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_member_archive extends table_common_member
{
	public function __construct() {

		parent::__construct();
		$this->_table = 'common_member_archive';
		$this->_pk    = 'uid';
	}

	public function fetch($id){
		$data = array();
		if(isset($this->membersplit) && ($id = dintval($id)) && ($data = DB::fetch_first('SELECT * FROM '.DB::table($this->_table).' WHERE '.DB::field($this->_pk, $id)))) {
			$data['_inarchive'] = true;
		}
		return $data;
	}

	public function fetch_by_username($username) {
		$user = array();
		if(isset($this->membersplit) && $username && ($user = DB::fetch_first('SELECT * FROM %t WHERE username=%s', array($this->_table, $username)))) {
			$user['_inarchive'] = true;
		}
		return $user;
	}

	public function fetch_uid_by_username($username) {
		$uid = 0;
		if(isset($this->membersplit) && $username) {
			$uid = DB::result_first('SELECT uid FROM %t WHERE username=%s', array($this->_table, $username));
		}
		return $uid;
	}

	public function count() {
		return isset($this->membersplit) ? DB::result_first('SELECT COUNT(*) FROM %t', array($this->_table)) : 0;
	}

	public function fetch_by_email($email) {
		$user = array();
		if(isset($this->membersplit) && $email && ($user = DB::fetch_first('SELECT * FROM %t WHERE email=%s', array($this->_table, $email)))) {
			$user['_inarchive'] = true;
		}
		return $user;
	}


	public function count_by_email($email) {
		$count = 0;
		if(isset($this->membersplit) && $email) {
			$count = DB::result_first('SELECT COUNT(*) FROM %t WHERE email=%s', array($this->_table, $email));
		}
		return $count;
	}

	public function fetch_all($ids) {
		$data = array();
		if(isset($this->membersplit) && ($ids = dintval($ids, true))) {
			$query = DB::query('SELECT * FROM '.DB::table($this->_table).' WHERE '.DB::field($this->_pk, $ids));
			while($value = DB::fetch($query)) {
				$value['_inarchive'] = true;
				$data[$value[$this->_pk]] = $value;
			}
		}
		return $data;
	}

	public function move_to_master($uid){
		if(isset($this->membersplit) && ($uid = intval($uid)) && ($member = $this->fetch($uid))) {
			unset($member['_inarchive']);
			DB::insert('common_member',$member);
			C::t('common_member_count')->insert(C::t('common_member_count_archive')->fetch($uid));
			C::t('common_member_status')->insert(C::t('common_member_status_archive')->fetch($uid));
			C::t('common_member_profile')->insert(C::t('common_member_profile_archive')->fetch($uid));
			C::t('common_member_field_home')->insert(C::t('common_member_field_home_archive')->fetch($uid));
			C::t('common_member_field_forum')->insert(C::t('common_member_field_forum_archive')->fetch($uid));
			$this->delete($uid);
			C::t('common_member_count_archive')->delete($uid);
			C::t('common_member_status_archive')->delete($uid);
			C::t('common_member_profile_archive')->delete($uid);
			C::t('common_member_field_home_archive')->delete($uid);
			C::t('common_member_field_forum_archive')->delete($uid);
		}
	}

	public function delete($val, $unbuffered = false) {
		return isset($this->membersplit) && ($val = dintval($val, true)) && DB::delete($this->_table, DB::field($this->_pk, $val), null, $unbuffered);
	}

	public function check_table() {
		if(DB::fetch_first("SHOW TABLES LIKE '".DB::table('common_member_archive')."'")){
			return false;
		} else {
			$mastertables = array('common_member', 'common_member_count', 'common_member_status', 'common_member_profile', 'common_member_field_home', 'common_member_field_forum');
			foreach($mastertables as $tablename) {
				$createtable = DB::fetch_first('SHOW CREATE TABLE '.DB::table($tablename));
				DB::query(str_replace(DB::table($tablename), DB::table("{$tablename}_archive"), $createtable['Create Table']));
			}
			return true;
		}
	}

	public function rebuild_table($step) {
		$mastertables = array('common_member', 'common_member_count', 'common_member_status', 'common_member_profile', 'common_member_field_home', 'common_member_field_forum');

		if(!isset($mastertables[$step])) {
			return false;
		}
		$updates = array();
		$mastertable = DB::table($mastertables[$step]);
		$archivetable = DB::table($mastertables[$step].'_archive');

		$mastercols = DB::fetch_all('SHOW COLUMNS FROM '.$mastertable, null, 'Field');
		$archivecols = DB::fetch_all('SHOW COLUMNS FROM '.$archivetable, null, 'Field');
		foreach(array_diff(array_keys($archivecols), array_keys($mastercols)) as $field) {
			$updates[] = "DROP `$field`";
		}

		$createtable = DB::fetch_first('SHOW CREATE TABLE '.$mastertable);
		$mastercols = $this->getcolumn($createtable['Create Table']);

		$archivecreatetable = DB::fetch_first('SHOW CREATE TABLE '.$archivetable);
		$oldcols = $this->getcolumn($archivecreatetable['Create Table']);

		$indexmastercols =array_keys($mastercols);
		foreach ($mastercols as $key => $value) {
			if($key == 'PRIMARY') {
				if($value != $oldcols[$key]) {
					if(!empty($oldcols[$key])) {
						$usql = "RENAME TABLE ".$archivetable." TO ".$archivetable.'_bak';
						if(!DB::query($usql, 'SILENT')) {
							return $mastertable;
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
						$usql = "ALTER TABLE  ".$archivetable." DROP INDEX `$subkey`";
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
					$i = array_search($key, $indexmastercols);
					$fieldposition = $i > 0 ? 'AFTER '.$indexmastercols[$i-1] : 'FIRST';
					$updates[] = "ADD `$key` $value $fieldposition";
				}
			}
		}

		$ret = true;
		if(!empty($updates)) {
			if(!DB::query("ALTER TABLE ".$archivetable." ".implode(', ', $updates), 'SILENT')) {
				$ret = $mastertable;
			} else {
			}
		}
		return $ret;
	}

	private function getcolumn($creatsql) {

		$creatsql = preg_replace("/ COMMENT '.*?'/i", '', $creatsql);
		$matchs = array();
		preg_match("/\((.+)\)\s*(ENGINE|TYPE)\s*\=/is", $creatsql, $matchs);

		$cols = explode("\n", $matchs[1]);
		$newcols = array();
		foreach ($cols as $value) {
			$value = trim($value);
			if(empty($value)) continue;
			$value = $this->remakesql($value);
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

	private function remakesql($value) {
		$value = trim(preg_replace("/\s+/", ' ', $value));
		$value = str_replace(array('`',', ', ' ,', '( ' ,' )', 'mediumtext'), array('', ',', ',','(',')','text'), $value);
		return $value;
	}
}

?>