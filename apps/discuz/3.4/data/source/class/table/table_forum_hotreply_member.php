
<?php


if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_forum_hotreply_member extends discuz_table {

	public function __construct() {
		$this->_table = 'forum_hotreply_member';
		$this->_pk = '';

		parent::__construct();
	}

	public function fetch($pid, $uid) {
		return DB::fetch_first('SELECT * FROM %t WHERE pid=%d AND uid=%d', array($this->_table, $pid, $uid));
	}

	public function delete_by_tid($tid) {
		if(empty($tid)) {
			return false;
		}
		return DB::query('DELETE FROM %t WHERE tid IN (%n)', array($this->_table, $tid));
	}

	public function delete_by_pid($pids) {
		if(empty($pids)) {
			return false;
		}
		return DB::query('DELETE FROM %t WHERE '.DB::field('pid', $pids), array($this->_table));
	}
}
?>