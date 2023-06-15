<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_domain.php 27860 2012-02-16 02:32:58Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_domain extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_domain';
		$this->_pk    = '';

		parent::__construct();
	}

	public function update_by_idtype($idtype, $data) {
		if($idtype && !empty($data) && is_array($data)) {
			return DB::update($this->_table, $data, DB::field('idtype', $idtype));
		}
		return 0;
	}

	public function fetch_all_by_idtype($idtype) {
		if(!empty($idtype)) {
			return DB::fetch_all('SELECT * FROM %t WHERE '.DB::field('idtype', $idtype), array($this->_table));
		}
		return array();
	}
	public function fetch_by_domain_domainroot($domain, $droot) {
		return DB::fetch_first('SELECT * FROM %t WHERE domain=%s AND domainroot=%s', array($this->_table, $domain, $droot));
	}

	public function delete_by_id_idtype($id, $idtype) {
		$parameter = array($this->_table, $id, $idtype);
		$wherearr = array();
		$wherearr[] = is_array($id) ? 'id IN(%n)' : 'id=%d';
		$wherearr[] = is_array($idtype) ? 'idtype IN(%n)' : 'idtype=%s';
		$wheresql = !empty($wherearr) && is_array($wherearr) ? ' WHERE '.implode(' AND ', $wherearr) : '';
		return DB::query("DELETE FROM %t $wheresql", $parameter);
	}

	public function count_by_domain_domainroot($domain, $droot) {
		return DB::result_first('SELECT COUNT(*) FROM %t WHERE domain=%s AND domainroot=%s', array($this->_table, $domain, $droot));
	}

}

?>