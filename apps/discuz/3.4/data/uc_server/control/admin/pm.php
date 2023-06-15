<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: pm.php 1066 2011-03-07 09:20:31Z svn_project_zhangjie $
*/

!defined('IN_UC') && exit('Access Denied');

class control extends adminbase {

	function __construct() {
		$this->control();
	}

	function control() {
		parent::__construct();
		if(!$this->user['isfounder'] && !$this->user['allowadminpm']) {
			$this->message('no_permission_for_this_module');
		}
		$this->load('pm');
		$this->check_priv();
	}

	function onls() {
		$pmlist = array();
		if($this->submitcheck() || getgpc('searchpmsubmit', 'G')) {
			$srchtablename = intval(getgpc('srchtablename', 'R'));
			$srchauthor = trim(getgpc('srchauthor', 'R'));
			$srchstarttime = trim(getgpc('srchstarttime', 'R'));
			$srchendtime = trim(getgpc('srchendtime', 'R'));
			$srchmessage = trim(getgpc('srchmessage', 'R'));

			$wheresql = array();
			if(!$srchtablename) {
				$srchtablename = 0;
			}
			if($srchauthor) {
				$this->load('user');
				$uidarr = $_ENV['user']->name2id(explode(',', $srchauthor));
				$wheresql[] = "authorid IN (".$this->implode($uidarr).")";
			}
			if($srchstarttime) {
				$wheresql[] = "dateline>='".strtotime($srchstarttime)."'";
			}
			if($srchendtime) {
				$wheresql[] = "dateline<'".strtotime($srchendtime)."'";
			}
			if($srchmessage) {
				$wheresql[] = "message LIKE '%{$srchmessage}%'";
			}

			$count = 0;
			if(!empty($wheresql)) {
				$count = $this->db->result_first("SELECT COUNT(*) FROM ".UC_DBTABLEPRE."pm_messages_".(string)$srchtablename." WHERE ".implode(' AND ', $wheresql));
			}
			if($count) {
				$page = intval(getgpc('page', 'R'));
				$page = $page ? $page : 1;
				$start = ($page-1) * UC_PPP;
				$limit = UC_PPP;
				$query = $this->db->query("SELECT * FROM ".UC_DBTABLEPRE."pm_messages_".(string)$srchtablename." WHERE ".implode(' AND ', $wheresql)." LIMIT $start, $limit");
				while($message = $this->db->fetch_array($query)) {
					$message['dateline'] = $this->date($message['dateline']);
					$user[] = $message['authorid'];
					$pmlist[] = $message;
				}
				$this->load('user');
				$usernamearr = $_ENV['user']->id2name($user);
				foreach($pmlist as $key => $value) {
					$pmlist[$key]['author'] = $usernamearr[$pmlist[$key]['authorid']];
				}
				$multipage = $this->page($count, UC_PPP, $page, 'admin.php?m=pm&a=ls&srchtablename='.$srchtablename.'&srchauthor='.urlencode($srchauthor).'&srchstarttime='.urlencode($srchstarttime).'&srchendtime='.urlencode($srchendtime).'&srchmessage='.urlencode($srchmessage).'&searchpmsubmit=true');
			}
		}

		$pmnum = 0;
		for($i = 0; $i < 10; $i++) {
			$pmnum += $this->db->result_first("SELECT COUNT(*) FROM ".UC_DBTABLEPRE."pm_messages_".(string)$i);
		}
		$this->view->assign('pmnum', $pmnum);
		$this->view->assign('count', $count);
		$this->view->assign('pmlist', $pmlist);
		$this->view->assign('multipage', $multipage);
		$this->view->assign('srchtablename', $srchtablename);
		$this->view->assign('srchauthor', $srchauthor);
		$this->view->assign('srchstarttime', $srchstarttime);
		$this->view->assign('srchendtime', $srchendtime);
		$this->view->assign('srchmessage', $srchmessage);
		$this->view->display('admin_pm_search');
	}

	function ondelete() {
		$srchtablename = intval(getgpc('srchtablename', 'R'));
		$srchauthor = trim(getgpc('srchauthor', 'R'));
		$srchstarttime = trim(getgpc('srchstarttime', 'R'));
		$srchendtime = trim(getgpc('srchendtime', 'R'));
		$srchmessage = trim(getgpc('srchmessage', 'R'));
		if($this->submitcheck()) {
			$pmids = getgpc('deletepmid');
			if(empty($pmids)) {
				$this->message('pm_delete_noselect', 'admin.php?m=pm&a=ls&srchtablename='.$srchtablename.'&srchauthor='.urlencode($srchauthor).'&srchstarttime='.urlencode($srchstarttime).'&srchendtime='.urlencode($srchendtime).'&srchmessage='.urlencode($srchmessage).'&searchpmsubmit=true');
			}
			foreach($pmids as $pmid) {
				$query = $this->db->query("SELECT * FROM ".UC_DBTABLEPRE."pm_indexes i LEFT JOIN ".UC_DBTABLEPRE."pm_lists l ON i.plid=l.plid WHERE i.pmid='$pmid'");
				if($index = $this->db->fetch_array($query)) {
					$this->db->query("DELETE FROM ".UC_DBTABLEPRE.$_ENV['pm']->getposttablename($index['plid'])." WHERE pmid='$pmid'");
					if($index['pmtype'] == 1) {
						$authorcount = $this->db->result_first("SELECT COUNT(*) FROM ".UC_DBTABLEPRE.$_ENV['pm']->getposttablename($index['plid'])." WHERE plid='".$index['plid']."' AND delstatus IN (0, 2)");
						$othercount = $this->db->result_first("SELECT COUNT(*) FROM ".UC_DBTABLEPRE.$_ENV['pm']->getposttablename($index['plid'])." WHERE plid='".$index['plid']."' AND delstatus IN (0, 1)");
						$users = explode('_', $index['min_max']);
						if($users[0] == $index['authorid']) {
							$other = $users[1];
						} else {
							$other = $users[0];
						}
						if($authorcount + $othercount == 0) {
							$this->db->query("DELETE FROM ".UC_DBTABLEPRE."pm_members WHERE plid='".$index['plid']."'");
							$this->db->query("DELETE FROM ".UC_DBTABLEPRE."pm_lists WHERE plid='".$index['plid']."'");
							$this->db->query("DELETE FROM ".UC_DBTABLEPRE."pm_indexes WHERE plid='".$index['plid']."'");
						} else {
							if($authorcount){
								$this->db->query("UPDATE ".UC_DBTABLEPRE."pm_members SET pmnum='$authorcount' WHERE plid='".$index['plid']."' AND uid='".$index['authorid']."'");
							} else {
								$this->db->query("DELETE FROM ".UC_DBTABLEPRE."pm_members WHERE plid='".$index['plid']."' AND uid='".$index['authorid']."'");
							}
							if($othercount) {
								$this->db->query("UPDATE ".UC_DBTABLEPRE."pm_members SET pmnum='$othercount' WHERE plid='".$index['plid']."' AND uid='".$other."'");
							} else {
								$this->db->query("DELETE FROM ".UC_DBTABLEPRE."pm_members WHERE plid='".$index['plid']."' AND uid='".$other."'");
							}
						}
					} elseif($index['pmtype'] == 2) {
						$count = $this->db->result_first("SELECT COUNT(*) FROM ".UC_DBTABLEPRE.$_ENV['pm']->getposttablename($index['plid'])." WHERE plid='".$index['plid']."'");
						if(!$count) {
							$this->db->query("DELETE FROM ".UC_DBTABLEPRE."pm_members WHERE plid='".$index['plid']."'");
							$this->db->query("DELETE FROM ".UC_DBTABLEPRE."pm_lists WHERE plid='".$index['plid']."'");
							$this->db->query("DELETE FROM ".UC_DBTABLEPRE."pm_indexes WHERE plid='".$index['plid']."'");
						} else {
							$this->db->query("UPDATE ".UC_DBTABLEPRE."pm_members SET pmnum='$count' WHERE plid='".$index['plid']."'");
						}
					}
				}
			}
			$this->message('pm_clear_succeed', 'admin.php?m=pm&a=ls&srchtablename='.$srchtablename.'&srchauthor='.urlencode($srchauthor).'&srchstarttime='.urlencode($srchstarttime).'&srchendtime='.urlencode($srchendtime).'&srchmessage='.urlencode($srchmessage).'&searchpmsubmit=true');
		}
	}

	function onclear() {
		$delnum = 0;
		if($this->submitcheck() || getgpc('clearpmsubmit', 'G')) {
			$usernames = trim(getgpc('usernames', 'R'));
			$pertask = intval(getgpc('pertask', 'R'));
			$current = intval(getgpc('current', 'R'));
			$pertask = $pertask ? $pertask : 100;
			$current = $current > 0 ? $current : 0;
			$next = $current + $pertask;
			$nexturl = "admin.php?m=pm&a=clear&usernames=$usernames&current=$next&pertask=$pertask&clearpmsubmit=1";

			if($usernames) {
				$uids = 0;
				$processed = 0;
				$usernames = "'".implode("', '", explode(',', $usernames))."'";
				$query = $this->db->query("SELECT uid FROM ".UC_DBTABLEPRE."members WHERE username IN ($usernames)");
				while($res = $this->db->fetch_array($query)) {
					$uids .= ','.$res['uid'];
				}
				if($uids) {
					$query = $this->db->query("SELECT m.plid, m.uid, t.pmtype, t.authorid FROM ".UC_DBTABLEPRE."pm_members m LEFT JOIN ".UC_DBTABLEPRE."pm_lists t ON m.plid=t.plid WHERE m.uid IN ($uids) LIMIT $pertask");
					while($member = $this->db->fetch_array($query)) {
						$processed = 1;
						if($member['pmtype'] == 1) {
							$this->db->query("DELETE FROM ".UC_DBTABLEPRE.$_ENV['pm']->getposttablename($member['plid'])." WHERE plid='".$member['plid']."'");
							$this->db->query("DELETE FROM ".UC_DBTABLEPRE."pm_lists WHERE plid='".$member['plid']."'");
							$this->db->query("DELETE FROM ".UC_DBTABLEPRE."pm_members WHERE plid='".$member['plid']."'");
							$adjust = $this->db->affected_rows();
							$this->db->query("DELETE FROM ".UC_DBTABLEPRE."pm_indexes WHERE plid='".$member['plid']."'");
						} elseif($member['pmtype'] == 2) {
							if($member['authorid'] == $member['uid']) {
								$this->db->query("DELETE FROM ".UC_DBTABLEPRE.$_ENV['pm']->getposttablename($member['plid'])." WHERE plid='".$member['plid']."'");
								$this->db->query("DELETE FROM ".UC_DBTABLEPRE."pm_lists WHERE plid='".$member['plid']."'");
								$this->db->query("DELETE FROM ".UC_DBTABLEPRE."pm_members WHERE plid='".$member['plid']."'");
								$adjust = $this->db->affected_rows();
								$this->db->query("DELETE FROM ".UC_DBTABLEPRE."pm_indexes WHERE plid='".$member['plid']."'");
							} else {
								$this->db->query("DELETE FROM ".UC_DBTABLEPRE.$_ENV['pm']->getposttablename($member['plid'])." WHERE plid='".$member['plid']."' AND authorid IN (".$uids.")");
								$affectpmnum = $this->db->affected_rows();
								$this->db->query("DELETE FROM ".UC_DBTABLEPRE."pm_members WHERE plid='".$member['plid']."' AND uid IN (".$uids.")");
								$affectmembers = $this->db->affected_rows();
								$adjust = $affectmembers;
								$this->db->query("UPDATE ".UC_DBTABLEPRE."pm_members SET pmnum=pmnum-'$affectpmnum' WHERE plid='".$member['plid']."'");
								$this->db->query("UPDATE ".UC_DBTABLEPRE."pm_lists SET members=members-'$affectmembers' WHERE plid='".$member['plid']."'");
							}
						}
					}
				}
				if($processed) {
					$this->message('pm_clear_processing', $nexturl, 0, array('current' => $current, 'next' => $next));
				} else {
					$this->message('pm_clear_succeed', 'admin.php?m=pm&a=clear');
				}
			}
		}

		$pmnum = 0;
		for($i = 0; $i < 10; $i++) {
			$pmnum += $this->db->result_first("SELECT COUNT(*) FROM ".UC_DBTABLEPRE."pm_messages_".(string)$i);
		}
		$this->view->assign('pmnum', $pmnum);
		$this->view->assign('delnum', $delnum);
		$this->view->assign('status', $status);
		$this->view->display('admin_pm_clear');
	}

}

?>