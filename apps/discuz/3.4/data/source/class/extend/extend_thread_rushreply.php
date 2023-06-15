<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: extend_thread_rushreply.php 34216 2013-11-14 02:32:06Z hypowang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class extend_thread_rushreply extends extend_thread_base {

	public function before_newthread($parameters) {

		if($this->group['allowpostrushreply']) {
			$_GET['rushreplyfrom'] = strtotime($_GET['rushreplyfrom']);
			$_GET['rushreplyto'] = strtotime($_GET['rushreplyto']);
			$_GET['rewardfloor'] = preg_replace('#[^0-9|*|,]#', '', $_GET['rewardfloor']);
			$_GET['stopfloor'] = intval($_GET['stopfloor']);
			$_GET['replylimit'] = intval($_GET['replylimit']);
			$_GET['creditlimit'] = $_GET['creditlimit'] == '' ? '-996' : intval($_GET['creditlimit']);
			if($_GET['rushreplyfrom'] > $_GET['rushreplyto'] && !empty($_GET['rushreplyto'])) {
				showmessage('post_rushreply_timewrong');
			}
			if(($_GET['rushreplyfrom'] > TIMESTAMP) || (!empty($_GET['rushreplyto']) && $_GET['rushreplyto'] < TIMESTAMP) || ($_GET['stopfloor'] == 1) ) {
				$this->param['closed'] = true;
			}
			if(!empty($_GET['rewardfloor']) && !empty($_GET['stopfloor'])) {
				$floors = explode(',', $_GET['rewardfloor']);
				if(!empty($floors) && is_array($floors)) {
					foreach($floors AS $key => $floor) {
						if(strpos($floor, '*') === false) {
							if(intval($floor) == 0) {
								unset($floors[$key]);
							} elseif($floor > $_GET['stopfloor']) {
								unset($floors[$key]);
							}
						}
					}
					$_GET['rewardfloor'] = implode(',', $floors);
				}
			}
			$parameters['tstatus'] = setstatus(3, 1, $parameters['tstatus']);
			$parameters['tstatus'] = setstatus(1, 1, $parameters['tstatus']);
			$this->param['tstatus'] = $parameters['tstatus'];
		}

	}


	public function after_newthread() {
		if($this->group['allowpostrushreply']) {
			$rushdata = array('tid' => $this->tid, 'stopfloor' => $_GET['stopfloor'], 'starttimefrom' => $_GET['rushreplyfrom'], 'starttimeto' => $_GET['rushreplyto'], 'rewardfloor' => $_GET['rewardfloor'], 'creditlimit' => $_GET['creditlimit'], 'replylimit' => $_GET['replylimit']);
			C::t('forum_threadrush')->insert($rushdata);
		}
	}
	public function before_newreply() {
		global $_G, $rushinfo;
		if(getstatus($this->thread['status'], 3) && $rushinfo['replylimit'] > 0) {
			$replycount = C::t('forum_post')->count_by_tid_invisible_authorid($this->thread['tid'], $_G['uid']);
			if($replycount >= $rushinfo['replylimit']) {
				showmessage('noreply_replynum_error');
			}
		}
	}
	public function after_newreply() {
		global $rushinfo;
		if(getstatus($this->thread['status'], 3) && $this->param['maxposition']) {
			$rushstopfloor = $rushinfo['stopfloor'];
			if($rushstopfloor > 0 && $this->thread['closed'] == 0 && $this->param['maxposition'] >= $rushstopfloor) {
				$this->param['updatethreaddata'] = array_merge((array)$this->param['updatethreaddata'], array('closed' => 1));
			}
		}
	}

	public function before_editpost($parameters) {
		global $_G, $rushreply;
		$isfirstpost = $this->post['first'] ? 1 : 0;
		if($isfirstpost) {
			if($rushreply) {
				$_GET['rushreplyfrom'] = strtotime($_GET['rushreplyfrom']);
				$_GET['rushreplyto'] = strtotime($_GET['rushreplyto']);
				$_GET['rewardfloor'] = preg_replace('#[^0-9|*|,]#', '', $_GET['rewardfloor']);
				$_GET['stopfloor'] = intval($_GET['stopfloor']);
				$_GET['replylimit'] = intval($_GET['replylimit']);
				$_GET['creditlimit'] = $_GET['creditlimit'] == '' ? '-996' : intval($_GET['creditlimit']);
				if($_GET['rushreplyfrom'] > $_GET['rushreplyto'] && !empty($_GET['rushreplyto'])) {
					showmessage('post_rushreply_timewrong');
				}
				$maxposition = C::t('forum_post')->fetch_maxposition_by_tid($this->thread['posttableid'], $this->thread['tid']);
				if($this->thread['closed'] == 1 && ((!$_GET['rushreplyfrom'] && !$_GET['rushreplyto']) || ($_GET['rushreplyfrom'] < $_G['timestamp'] && $_GET['rushreplyto'] > $_G['timestamp']) || (!$_GET['rushreplyfrom'] && $_GET['rushreplyto'] > $_G['timestamp']) || ($_GET['stopfloor'] && $_GET['stopfloor'] > $maxposition) )) {
					$this->param['threadupdatearr']['closed'] = 0;
				} elseif($this->thread['closed'] == 0 && (($_GET['rushreplyfrom'] && $_GET['rushreplyfrom'] > $_G['timestamp']) || ($_GET['rushreplyto'] && $_GET['rushreplyto'] && $_GET['rushreplyto'] < $_G['timestamp']) || ($_GET['stopfloor'] && $_GET['stopfloor'] <= $maxposition) )) {
					$this->param['threadupdatearr']['closed'] = 1;
				}
				if(!empty($_GET['rewardfloor']) && !empty($_GET['stopfloor'])) {
					$floors = explode(',', $_GET['rewardfloor']);
					if(!empty($floors)) {
						foreach($floors AS $key => $floor) {
							if(strpos($floor, '*') === false) {
								if(intval($floor) == 0) {
									unset($floors[$key]);
								} elseif($floor > $_GET['stopfloor']) {
									unset($floors[$key]);
								}
							}
						}
					}
					$_GET['rewardfloor'] = implode(',', $floors);
				}
				$rushdata = array('stopfloor' => $_GET['stopfloor'], 'starttimefrom' => $_GET['rushreplyfrom'], 'starttimeto' => $_GET['rushreplyto'], 'rewardfloor' => $_GET['rewardfloor'], 'creditlimit' => $_GET['creditlimit'], 'replylimit' => $_GET['replylimit']);
				C::t('forum_threadrush')->update($this->thread['tid'], $rushdata);
			}
		}
	}

	public function before_deletepost() {
		global $rushreply;
		if($rushreply) {
			showmessage('post_edit_delete_rushreply_nopermission', NULL);
		}
	}
}

?>