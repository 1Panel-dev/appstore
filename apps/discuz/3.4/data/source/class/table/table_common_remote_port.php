<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: table_common_remote_port.php 33618 2013-07-17 06:16:46Z andyzheng $
 */
if(!defined('IN_DISCUZ')){
	exit('Access Denied');
}
class table_common_remote_port extends discuz_table {
	public function __construct() {
		$this->_table = 'common_remote_port';
		$this->_pk = '';
		parent::__construct();
		$porttable = getglobal('setting/porttable');
		if(empty($porttable)) {
			$this->inittable();
		}

	}

	public function fetch_by_id_idtype($id, $idtype) {
		return DB::fetch_first('SELECT * FROM %t WHERE id=%d AND idtype=%s', array($this->_table, $id, $idtype));
	}

	public function fetch_all_by_id_idtype($id, $idtype) {
		$id = dintval($id, is_array($id) ? true : false);
		$parameter = array($this->_table, $id, $idtype);
		$wherearr = array();
		$wherearr[] = is_array($id) ? 'id IN(%n)' : 'id=%d';
		$wherearr[] = 'idtype=%s';
		$wheresql = ' WHERE '.implode(' AND ', $wherearr);
		return DB::fetch_all("SELECT * FROM %t $wheresql ", $parameter);
	}

	private function inittable() {

		if(!DB::fetch_first("SHOW TABLES LIKE '".DB::table('common_remote_port')."'")) {

			$orig_tablepre = 'pre_';
			$tablepre = getglobal('config/db/1/tablepre');
			$dbcharset = getglobal('config/db/1/dbcharset');
			$porttable = "CREATE TABLE pre_common_remote_port (id mediumint(8) unsigned NOT NULL default '0' COMMENT '',idtype char(15) NOT NULL default '' COMMENT '',useip char(15) NOT NULL default '' COMMENT '',port smallint(6) unsigned NOT NULL default '0' COMMENT '',PRIMARY KEY (id, idtype)) ENGINE=MyISAM COMMENT=''";
			$porttable = str_replace("\r", "\n", str_replace(' '.$orig_tablepre, ' '.$tablepre, $porttable));
			$porttable = str_replace("\r", "\n", str_replace(' `'.$orig_tablepre, ' `'.$tablepre, $porttable));

			$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $porttable));
			$type = in_array($type, array('MYISAM', 'HEAP', 'MEMORY')) ? $type : 'MYISAM';
			$dbver = DB::$db->version();
			$porttable = preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $porttable).($dbver > '4.1' ? " ENGINE=$type DEFAULT CHARSET=".$dbcharset : " TYPE=$type");
			DB::query($porttable);
			C::t('common_setting')->update('porttable', 1);
			require_once libfile('function/cache');
			updatecache('setting');
			return true;
		}
	}
}