<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: note.php 1155 2013-06-20 08:36:25Z andyzheng $
*/

!defined('IN_UC') && exit('Access Denied');

class control extends adminbase {

	var $apps = array();
	var $operations = array();

	function __construct() {
		$this->control();
	}

	function control() {
		parent::__construct();
		$this->check_priv();
		if(!$this->user['isfounder'] && !$this->user['allowadminnote']) {
			$this->message('no_permission_for_this_module');
		}
		$this->load('note');
		$this->apps = $this->cache['apps'];

		$this->operations = array(
			'test'=>array('', 'action=test'),
			'deleteuser'=>array('', 'action=deleteuser'),
			'renameuser'=>array('', 'action=renameuser'),
			'deletefriend'=>array('', 'action=deletefriend'),
			'gettag'=>array('', 'action=gettag', 'tag', 'updatedata'),
			'getcreditsettings'=>array('', 'action=getcreditsettings'),
			'updatecreditsettings'=>array('', 'action=updatecreditsettings'),
			'updateclient'=>array('', 'action=updateclient'),
			'updatepw'=>array('', 'action=updatepw'),
			'updatebadwords'=>array('', 'action=updatebadwords'),
			'updatehosts'=>array('', 'action=updatehosts'),
			'updateapps'=>array('', 'action=updateapps'),
			'updatecredit'=>array('', 'action=updatecredit'),
		);
		$this->check_priv();
	}

	function onls() {
		$page = getgpc('page');
		$delete = getgpc('delete', 'P');
		$status = 0;
		if(!empty($delete)) {
			$_ENV['note']->delete_note($delete);
			$status = 2;
			$this->writelog('note_delete', "delete=".implode(',', $delete));
		}
		foreach($this->cache['apps'] as $key => $app) {
			if(empty($app['recvnote'])) {
				unset($this->apps[$key]);
			}
		}
		$num = $_ENV['note']->get_total_num(1);
		$notelist = $_ENV['note']->get_list($page, UC_PPP, $num, 1);
		$multipage = $this->page($num, UC_PPP, $page, 'admin.php?m=note&a=ls');

		$this->view->assign('status', $status);
		$this->view->assign('applist', $this->apps);
		$this->_format_notlist($notelist);
		$this->view->assign('notelist', $notelist);
		$this->view->assign('multipage', $multipage);

		$this->view->display('admin_note');
	}

	function onsend() {
		$noteid = intval(getgpc('noteid'));
		$appid = intval(getgpc('appid'));
		$result = $_ENV['note']->sendone($appid, $noteid);
		if($result) {
			$this->writelog('note_send', "appid=$appid&noteid=$noteid");
			$this->message('note_succeed', $_SERVER['HTTP_REFERER']);
		} else {
			$this->writelog('note_send', 'failed');
			$this->message('note_false', $_SERVER['HTTP_REFERER']);
		}

	}

	function _note_status($status, $appid, $noteid, $args, $operation) {
		if($status > 0) {
			return '<font color="green">'.$this->lang['note_succeed'].'</font>';
		} elseif($status == 0) {
			$url = 'admin.php?m=note&a=send&appid='.$appid.'&noteid='.$noteid;
			return '<a href="'.$url.'" class="red">'.$this->lang['note_na'].'</a>';
		} elseif($status < 0) {
			$url = 'admin.php?m=note&a=send&appid='.$appid.'&noteid='.$noteid;
			return '<a href="'.$url.'"><font color="red">'.$this->lang['note_false'].(-$status).$this->lang['note_times'].'</font></a>';
		}
	}

	function _format_notlist(&$notelist) {
		if(is_array($notelist)) {
			foreach($notelist AS $key => $note) {
				$notelist[$key]['operation'] = $this->lang['note_'.$note['operation']];//$this->operations[$note['operation']][0];
				foreach($this->apps AS $appid => $app) {
					$notelist[$key]['status'][$appid] = $this->_note_status($note['app'.$appid], $appid, $note['noteid'], $note['args'], $note['operation']);
				}
			}
		}
	}

}

?>