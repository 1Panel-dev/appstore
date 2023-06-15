
<?php


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_connect_disktask extends discuz_table {

	public function __construct() {
		$this->_table = 'connect_disktask';
		$this->_pk = 'taskid';

		parent::__construct();
	}

	public function delete_by_status($status) {
		if (dintval($status)) {
			return DB::query('DELETE FROM %t WHERE status = %d', array($this->_table, $status));
		}
	}
}