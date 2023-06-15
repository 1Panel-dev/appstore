<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_membersearch.php 33687 2013-08-02 01:46:22Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class membersearch {

	function membersearch(){}

	function getfield($fieldid='') {
		static $fields = array(
			'uid'=>'member', 'username'=>'member', 'groupid'=>'member', 'medalid'=>'medal','tagid'=>'tag','idtype'=>'tag',
			'email'=>'member', 'credits'=>'member', 'regdate'=>'member',
			'status'=>'member', 'freeze'=>'member', 'emailstatus'=>'member', 'avatarstatus'=>'member','videophotostatus'=>'member',
			'conisbind'=>'member','uin' => 'black','sid'=>'session',
			'extcredits1'=>'count', 'extcredits2'=>'count', 'extcredits3'=>'count', 'extcredits4'=>'count',
			'extcredits5'=>'count',	'extcredits6'=>'count', 'extcredits7'=>'count', 'extcredits8'=>'count',
			'posts'=>'count','friends'=>'count','oltime'=>'count',
			'fid' => 'groupuser', 'level' => 'groupuser',
			'verify1' => 'verify', 'verify2' => 'verify', 'verify3' => 'verify', 'verify4' => 'verify', 'verify5' => 'verify', 'verify6' => 'verify', 'verify7' => 'verify',
			'regip'=>'status', 'lastip'=>'status', 'lastvisit'=>'status', 'lastpost' => 'status', 'realname'=>'profile',
			'birthyear'=>'profile', 'birthmonth'=>'profile', 'birthday'=>'profile', 'gender'=>'profile',
			'constellation'=>'profile', 'zodiac'=>'profile', 'telephone'=>'profile', 'mobile'=>'profile',
			'idcardtype'=>'profile', 'idcard'=>'profile', 'address'=>'profile', 'zipcode'=>'profile', 'nationality'=>'profile',
			'birthprovince'=>'profile', 'birthcity'=>'profile', 'resideprovince'=>'profile',
			'residecity'=>'profile', 'residedist'=>'profile', 'residecommunity'=>'profile',
			'residesuite'=>'profile', 'graduateschool'=>'profile', 'education'=>'profile',
			'occupation'=>'profile', 'company'=>'profile', 'position'=>'profile', 'revenue'=>'profile',
			'affectivestatus'=>'profile', 'lookingfor'=>'profile', 'bloodtype'=>'profile',
			'height'=>'profile', 'weight'=>'profile', 'alipay'=>'profile', 'icq'=>'profile',
			'qq'=>'profile', 'yahoo'=>'profile', 'msn'=>'profile', 'taobao'=>'profile', 'site'=>'profile',
			'bio'=>'profile', 'interest'=>'profile', 'field1'=>'profile', 'field2'=>'profile',
			'field3'=>'profile', 'field4'=>'profile', 'field5'=>'profile', 'field6'=>'profile',
			'field7'=>'profile', 'field8'=>'profile', 'token' => 'token');
		return $fieldid ? $fields[$fieldid] : $fields;
	}

	function gettype($fieldid) {
		static $types = array(
			'uid'=>'int', 'groupid'=>'int', 'medalid'=>'int', 'tagid'=>'int', 'credits'=>'int',
			'status'=>'int', 'freeze'=>'int', 'emailstatus'=>'int', 'avatarstatus'=>'int','videophotostatus'=>'int',
			'extcredits1'=>'int', 'extcredits2'=>'int', 'extcredits3'=>'int', 'extcredits4'=>'int',
			'extcredits5'=>'int', 'extcredits6'=>'int', 'extcredits7'=>'int', 'extcredits8'=>'int',
			'posts'=>'int', 'friends'=>'int', 'birthyear'=>'int', 'birthmonth'=>'int', 'birthday'=>'int', 'gender'=>'int',
			'uin'=>'int', 'sid'=>'noempty', 'token' => 'noempty'
			);
		return $types[$fieldid] ? $types[$fieldid] : 'string';
	}

	function search($condition, $maxsearch=100, $start=0){
		$list = array();
		$sql = membersearch::makesql($condition);
		if($maxsearch) {
			$sql .= " LIMIT $start, $maxsearch";
		}
		if(isset($condition['token_noempty'])) {
			try {
				$query = DB::query($sql);
				while($value = DB::fetch($query)) {
					$list[] = intval($value['uid']);
				}
			} catch (Exception $e) {}
		} else {
			$query = DB::query($sql);
			while($value = DB::fetch($query)) {
				$list[] = intval($value['uid']);
			}
		}
		return $list;
	}

	function getcount($condition) {
		$count = 0;
		if(isset($condition['token_noempty'])) {
			try {
				$count = DB::result_first(membersearch::makesql($condition, true));
			} catch (Exception $e) {}
		} else {
			$count = DB::result_first(membersearch::makesql($condition, true));
		}
		return intval($count);
	}

	function filtercondition($condition) {
		$tablename = isset($condition['tablename']) ? $condition['tablename'] : '';
		unset($condition['tablename']);
		$fields = membersearch::getfield();
		foreach($condition as $key => $value) {
			$rkey = str_replace(array('_low', '_high', '_noempty', '_after', '_before'), '', $key);
			if(!(isset($fields[$rkey]) || in_array($key, array('verify', 'fid', 'tagid')))) {
				unset($condition[$key]);
			}
		}
		$condition['tablename'] = $tablename;
		return $condition;
	}

	function makesql($condition, $onlyCount=false) {

		$tables = $wheres = array();
		$isarchive = $condition['tablename'] === 'archive' ? true : false;
		if($condition['verify']) {
			foreach($condition['verify'] as $key => $value) {
				$condition[$value] = 1;
			}
			unset($condition['verify']);
		}
		if($condition['fid']) {
			$condition['level'] = '1,2,3,4';
		}
		if($condition['tagid']) {
			$condition['idtype'] = 'uid';
		}

		$fields = membersearch::getfield();
		foreach ($fields as $key=>$value) {
			$return = array();
			if(isset($condition[$key])) {
				$return = membersearch::makeset($key, $condition[$key], membersearch::gettype($key));
			} elseif(isset($condition[$key.'_low']) || isset($condition[$key.'_high'])) {
				$return = membersearch::makerange($key, $condition[$key.'_low'], $condition[$key.'_high'], membersearch::gettype($key));
			} elseif(isset($condition[$key.'_noempty'])) {
				$return = membersearch::makeset($key, $condition[$key.'_noempty'], membersearch::gettype($key));
			} elseif(isset($condition[$key.'_after']) || isset($condition[$key.'_before'])) {
				$condition[$key.'_after'] = dmktime($condition[$key.'_after']);
				$condition[$key.'_before'] = dmktime($condition[$key.'_before']);
				$return = membersearch::makerange($key, $condition[$key.'_after'], $condition[$key.'_before'], membersearch::gettype($key));
			}
			if($return) {
				$tables[$return['table']] = true;
				$wheres[] = $return['where'];
			}
		}
		if($tables && $wheres) {
			$parts = array();
			$table1 = $asuid = '';
			$uidfield = 'uid';
			foreach ($tables as $key => $value) {
				$value = membersearch::gettable($key, $isarchive);
				$parts[] = "$value as `$key`";
				if(! $table1) {
					$table1 = $key;
					if($table1 == 'tag') {
						$uidfield = 'itemid';
						$asuid = ' as uid';
					}
				} else {
					if($key == 'tag') {
						$keyuid = 'itemid';
					} else {
						$keyuid = 'uid';
					}
					$wheres[] = $table1.'.'.$uidfield.' = '.$key.'.'.$keyuid;
				}
			}

			$selectsql = $onlyCount ? 'SELECT COUNT(DISTINCT '.$table1.'.'.$uidfield.') as cnt ' : 'SELECT DISTINCT '.$table1.'.'.$uidfield.$asuid;
			return $selectsql.' FROM '.implode(', ', $parts).' WHERE '.implode(' AND ', $wheres);
		} else {
			$selectsql = $onlyCount ? 'SELECT COUNT(uid) as cnt ' : 'SELECT uid';
			return $selectsql.' FROM '.DB::table('common_member'.($isarchive ? '_archive' : ''))." WHERE 1";
		}
	}

	function makeset($field, $condition, $type='string') {
		$return = $values = array();

		$return['table'] = membersearch::getfield($field);
		if(! $return['table']){
			return array();
		}
		$field = $return['table'].'.'.$field;

		$islikesearch = $noempty = false;
		if(!is_array($condition)) {
			$condition = explode(',', $condition);
		}
		foreach ($condition as $value) {
			$value = trim($value);
			if($type == 'int') {
				$value = intval($value);
			} elseif($type == 'noempty') {
				$noempty = true;
			} elseif(!$islikesearch && strexists($value, '*')) {
				$islikesearch = true;
			}
			if($type != 'int') $value = addslashes($value);
			if($value !== null) {
				$values[] = $value;
			}
		}

		if(!$values) {
			return array();
		}

		if($islikesearch) {
			$likes = array();
			foreach ($values as $value) {
				if(strexists($value, '*')) {
					$value = str_replace('*', '%', $value);
					$likes[] = "$field LIKE '$value'";
				} else {
					$likes[] = "$field = '$value'";
				}
			}
			$return['where'] = '('.implode(' OR ', $likes).')';
		} elseif($noempty) {
			$return['where'] = "$field != ''";
		} elseif(count($values) > 1) {
			$return['where'] = "$field IN ('".implode("','", $values)."')";
		} else {
			$return['where'] = "$field = '$values[0]'";
		}
		return $return;
	}

	function makerange($field, $range_low=null, $range_high=null, $type='string') {
		$return = array();

		$return['table'] = membersearch::getfield($field);
		if(!$return['table']){
			return array();
		}
		$field = $return['table'].'.'.$field;

		if($type == 'int') {
			$range_low = intval($range_low);
			$range_high = intval($range_high);
		}  else {
			$range_low = addslashes(trim($range_low));
			$range_high = addslashes(trim($range_high));
		}

		$wheres = array();
		if($range_low !== null) {
			$wheres[] = "$field >= '$range_low'";
		}
		if($range_high !== null && $range_high > $range_low) {
			$wheres[] = "$field <= '$range_high'";
		}
		if($wheres) {
			$return['where'] = implode(' AND ', $wheres);
			return $return;
		} else {
			return array();
		}
	}


	function gettable($alias, $isarchive = false) {
		static $mapping = array('member'=>'common_member', 'status'=>'common_member_status', 'profile'=>'common_member_profile', 'count'=>'common_member_count', 'session'=>'common_session', 'groupuser' => 'forum_groupuser', 'verify' => 'common_member_verify', 'black'=>'common_uin_black', 'medal'=>'common_member_medal', 'tag'=>'common_tagitem', 'token' => 'common_devicetoken');
		return DB::table($isarchive && in_array($alias, array('member', 'status', 'profile', 'count')) ? $mapping[$alias].'_archive' : $mapping[$alias]);
	}

}


?>