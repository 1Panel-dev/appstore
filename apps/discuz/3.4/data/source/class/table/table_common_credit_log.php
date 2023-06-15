<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_credit_log.php 31381 2012-08-21 07:56:35Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_credit_log extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_credit_log';
		$this->_pk    = 'logid';

		parent::__construct();
	}
	public function fetch_by_operation_relatedid($operation, $relatedid) {
		$relatedid = dintval($relatedid, true);
		$parameter = array($this->_table, $operation, $relatedid);
		$wherearr = array();
		$wherearr[] = is_array($operation) && $operation ? 'operation IN(%n)' : 'operation=%s';
		$wherearr[] = is_array($relatedid) && $relatedid ? 'relatedid IN(%n)' : 'relatedid=%d';
		return DB::fetch_all('SELECT * FROM %t WHERE '.implode(' AND ', $wherearr), $parameter);
	}
	public function fetch_all_by_operation($operation, $start = 0, $limit = 0) {
		return DB::fetch_all('SELECT * FROM %t WHERE operation=%s ORDER BY dateline DESC '.DB::limit($start, $limit), array($this->_table, $operation));
	}
	public function fetch_all_by_uid_operation_relatedid($uid, $operation, $relatedid) {
		$parameter = array($this->_table);
		$wherearr = array();
		if($uid) {
			$uid = dintval($uid, true);
			$wherearr[] = is_array($uid) && $uid ? 'uid IN(%n)' : 'uid=%d';
			$parameter[] = $uid;
		}
		$relatedid = dintval($relatedid, true);
		$wherearr[] = is_array($operation) && $operation ? 'operation IN(%n)' : 'operation=%s';
		$wherearr[] = is_array($relatedid) && $relatedid ? 'relatedid IN(%n)' : 'relatedid=%d';
		$parameter[] = $operation;
		$parameter[] = $relatedid;
		return DB::fetch_all('SELECT * FROM %t WHERE '.implode(' AND ', $wherearr).' ORDER BY dateline', $parameter);
	}
	public function fetch_all_by_uid($uid, $start = 0, $limit = 0) {
		$array = DB::fetch_all('SELECT * FROM %t WHERE uid=%d ORDER BY dateline DESC '.DB::limit($start, $limit), array($this->_table, $uid), 'logid');
		if(!$array) {
			return array();
		}
		$fieldids = array();
		foreach($array as $key => $value) {
			if(!in_array($value['operation'], lang('spacecp', 'logs_credit_update_INDEX'))) {
				$fieldids[] = $key;
			}
		}
		if($fieldids) {
			$arrayfield = DB::fetch_all('SELECT * FROM %t WHERE logid IN(%n)', array('common_credit_log_field', $fieldids), 'logid');
			foreach($arrayfield as $key => $value) {
				$array[$key] += $value;
			}
		}
		return $array;
	}
	public function fetch_all_by_search($uid, $optype, $begintime = 0, $endtime = 0, $exttype = 0, $income = 0, $extcredits = array(), $start = 0, $limit = 0, $relatedid = 0) {
		$condition = $this->make_query_condition($uid, $optype, $begintime, $endtime, $exttype, $income, $extcredits, $relatedid);
		$array = DB::fetch_all('SELECT * FROM %t '.$condition[0].' ORDER BY dateline DESC '.DB::limit($start, $limit), $condition[1], 'logid');
		if(!$array) {
			return array();
		}
		$fieldids = array();
		foreach($array as $key => $value) {
			if(!in_array($value['operation'], lang('spacecp', 'logs_credit_update_INDEX'))) {
				$fieldids[] = $key;
			}
		}
		if($fieldids) {
			$arrayfield = DB::fetch_all('SELECT * FROM %t WHERE logid IN(%n)', array('common_credit_log_field', $fieldids), 'logid');
			foreach($arrayfield as $key => $value) {
				$array[$key] += $value;
			}
		}
		return $array;
	}
	public function delete_by_operation_relatedid($operation, $relatedid) {
		$relatedid = dintval($relatedid, true);
		if($operation && $relatedid) {
			return DB::delete($this->_table, DB::field('operation', $operation).' AND '.DB::field('relatedid', $relatedid));
		}
		return 0;
	}

	public function delete_by_uid_operation_relatedid($uid, $operation, $relatedid) {
		$relatedid = dintval($relatedid, true);
		$uid = dintval($uid, true);
		if($relatedid && $uid && $operation) {
			return DB::delete($this->_table, DB::field('uid', $uid).' AND '.DB::field('operation', $operation).' AND '.DB::field('relatedid', $relatedid));
		}
		return 0;
	}
	public function update_by_uid_operation_relatedid($uid, $operation, $relatedid, $data) {
		$relatedid = dintval($relatedid, true);
		$uid = dintval($uid, true);
		if(!empty($data) && is_array($data) && $relatedid && $uid && $operation) {
			return DB::update($this->_table, $data, DB::field('uid', $uid).' AND '.DB::field('operation', $operation).' AND '.DB::field('relatedid', $relatedid));
		}
		return 0;
	}
	public function count_by_uid_operation_relatedid($uid, $operation, $relatedid) {
		$relatedid = dintval($relatedid, true);
		$uid = dintval($uid, true);
		if($relatedid && $uid && $operation) {
			$wherearr = array();
			$wherearr[] = is_array($uid) && $uid ? 'uid IN(%n)' : 'uid=%d';
			$wherearr[] = is_array($operation) && $operation ? 'operation IN(%n)' : 'operation=%s';
			$wherearr[] = is_array($relatedid) && $relatedid ? 'relatedid IN(%n)' : 'relatedid=%d';
			return DB::result_first('SELECT COUNT(*) FROM %t WHERE '.implode(' AND ', $wherearr), array($this->_table, $uid, $operation, $relatedid));
		}
		return 0;
	}
	public function count_by_uid($uid) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE uid=%d', array($this->_table, $uid));
	}
	public function count_by_operation($operation) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE operation=%s', array($this->_table, $operation));
	}
	public function count_stc_by_relatedid($relatedid, $creditid, $operation = 'STC') {
		$creditid = intval($creditid);
		if($creditid) {
			return DB::fetch_first("SELECT COUNT(*) AS payers, SUM(extcredits%d) AS income FROM %t WHERE relatedid=%d AND operation=%s", array($creditid, $this->_table, $relatedid, $operation));
		}
		return 0;
	}
	public function count_credit_by_uid_operation_relatedid($uid, $operation, $relatedid, $creditid) {
		$creditid = intval($creditid);
		if($creditid) {
			$relatedid = dintval($relatedid, true);
			$uid = dintval($uid, true);
			$wherearr = array();
			$wherearr[] = is_array($uid) && $uid ? 'uid IN(%n)' : 'uid=%d';
			$wherearr[] = is_array($operation) && $operation ? 'operation IN(%n)' : 'operation=%s';
			$wherearr[] = is_array($relatedid) && $relatedid ? 'relatedid IN(%n)' : 'relatedid=%d';
			return DB::result_first('SELECT SUM(extcredits%d) AS credit FROM %t WHERE '.implode(' AND ', $wherearr), array($creditid, $this->_table, $uid, $operation, $relatedid));
		}
		return 0;
	}
	public function count_by_search($uid, $optype, $begintime = 0, $endtime = 0, $exttype = 0, $income = 0, $extcredits = array(), $relatedid = 0) {
		$condition = $this->make_query_condition($uid, $optype, $begintime, $endtime, $exttype, $income, $extcredits, $relatedid);
		return DB::result_first('SELECT COUNT(*) FROM %t '.$condition[0], $condition[1]);
	}
	private function make_query_condition($uid, $optype, $begintime = 0, $endtime = 0, $exttype = 0, $income = 0, $extcredits = array(), $relatedid = 0) {
		$wherearr = array();
		$parameter = array($this->_table);
		if($uid) {
			$uid = dintval($uid, true);
			$wherearr[] = is_array($uid) && $uid ? 'uid IN(%n)' : 'uid=%d';
			$parameter[] = $uid;
		}
		if($optype) {
			$wherearr[] = is_array($optype) && $optype ? 'operation IN(%n)' : 'operation=%s';
			$parameter[] = $optype != -1 ? $optype : '';
		}
		if($relatedid) {
			$relatedid = dintval($relatedid, true);
			$wherearr[] = is_array($relatedid) && $relatedid ? 'relatedid IN(%n)' : 'relatedid=%d';
			$parameter[] = $relatedid;
		}
		if($begintime) {
			$wherearr[] = 'dateline>%d';
			$parameter[] = $begintime;
		}
		if($endtime) {
			$wherearr[] = 'dateline<%d';
			$parameter[] = $endtime;
		}
		if($exttype && $extcredits[$exttype]) {
			$wherearr[] = "extcredits{$exttype}!=0";
		}
		if($income && $extcredits) {
			$incomestr = $income < 0 ? '<' : '>';
			$incomearr = array();
			foreach(array_keys($extcredits) as $id) {
				$incomearr[] = 'extcredits'.$id.$incomestr.'0';
			}
			if($incomearr) {
				$wherearr[] = '('.implode(' OR ', $incomearr).')';
			}
		}
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return array($wheresql, $parameter);
	}
}

?>