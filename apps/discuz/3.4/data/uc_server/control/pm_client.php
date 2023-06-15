<?php

/*
  [UCenter] (C)2001-2099 Comsenz Inc.
  This is NOT a freeware, use is subject to license terms

  $Id: pm_client.php 1166 2014-11-03 01:49:32Z hypowang $
 */
!defined('IN_UC') && exit('Access Denied');

class pm_clientcontrol extends base {

	function __construct() {
		$this->pm_clientcontrol();
	}

	function pm_clientcontrol() {
		parent::__construct();
		if (!$this->settings['pmcenter']) {
			exit('PMCenter closed');
		}
		$this->load('user');
		$this->load('pm', NULL, UC_SERVER_RELEASE);
	}

	function _auth() {
		$input = getgpc('input');
		if (!$this->user['uid'] || isset($input)) {
			$this->init_input();
			header('P3P: CP="CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR"');
			if ($this->input['uid']) {
				$this->setcookie('uc_auth', @$this->authcode($this->input['uid'] . "||" . md5($_SERVER['HTTP_USER_AGENT']), 'ENCODE', UC_KEY), 1800);
				@$this->user['uid'] = $this->input['uid'];
			} else {
				$this->setcookie('uc_auth', '');
				$this->message('please_login', '', 1);
			}
		}
	}

	function onls() {
		$folder = getgpc('folder');
		$page = getgpc('page');
		$filter = getgpc('filter');
		$a = getgpc('a');
		$this->_auth();
		$uid = $this->user['uid'];
		$_ENV['pm']->lang = &$this->lang;
		$page = $page ? $page : 1;
		$filter = $filter ? (in_array($filter, array('newpm', 'privatepm')) ? $filter : '') : 'privatepm';
		$pmnum_private = $_ENV['pm']->getpmnum($uid, 0, 0);
		$unreadpmnum = $_ENV['pm']->getpmnum($uid, 0, 1);
		$this->view->assign('user', $this->user);
		$this->view->assign('pmnum_private', $pmnum_private);
		$this->view->assign('pmnum_chatpm', $pmnum_chatpm);
		$this->view->assign('unreadpmnum', $unreadpmnum);
		if ($folder == 'blackls') {
			$blackls = dhtmlspecialchars($_ENV['pm']->get_blackls($uid));
			$this->view->assign('folder', $folder);
			$this->view->assign('blackls', $blackls);
			$this->view->display('pm_blackls');
		} else {
			$start = ($page - 1) * 10;
			$pmlist = $_ENV['pm']->getpmlist($uid, $filter, $start, 10);
			if ($pmlist) {
				foreach ($pmlist as $key => $value) {
					$pmlist[$key]['filter'] = 'privatepm';
					$pmlist[$key]['lastdateline'] = $this->date($value['lastdateline']);
				}
			}

			$extra = 'extra=' . rawurlencode('page=' . $page);
			$multipage = $this->page($pmnum, 10, $page, 'index.php?m=pm_client&a=ls&folder=' . $folder . '&filter=' . $filter);
			$this->view->assign('extra', $extra);
			$this->view->assign('filter', $filter);
			$this->view->assign('pmlist', $pmlist);
			$this->view->assign('multipage', $multipage);
			$this->view->display('pm_ls');
		}
	}

	function onblackls() {
		$blackls = getgpc('blackls', 'P');
		$this->_auth();
		$uid = $this->user['uid'];
		if ($this->submitcheck()) {
			$_ENV['pm']->set_blackls($uid, $blackls);
		}
		$this->message('blackls_updated', 'index.php?m=pm_client&a=ls&folder=blackls', 1);
	}

	function onsend() {
		$a = getgpc('a');
		$do = getgpc('do');
		$this->_auth();
		$uid = $this->user['uid'];

		$this->load('friend');
		$totalnum = $_ENV['friend']->get_totalnum_by_uid($this->user['uid'], 3);
		$friends = $totalnum ? $_ENV['friend']->get_list($this->user['uid'], 1, $totalnum, $totalnum, 3) : array();
		if (!$this->submitcheck()) {
			$extra = 'extra=' . rawurlencode($_GET['extra']);
			$type = !empty($_GET['type']) ? $_GET['type'] : '';
			$pmid = @is_numeric($_GET['pmid']) ? $_GET['pmid'] : 0;
			$daterange = $_GET['daterange'] ? intval($_GET['daterange']) : 1;
			$touid = intval($_GET['touid']);
			$plid = intval($_GET['plid']);
			$folder = getgpc('folder');

			$pmnum_private = $_ENV['pm']->getpmnum($uid, 0, 0);
			$unreadpmnum = $_ENV['pm']->getpmnum($uid, 0, 1);

			$touser = '';
			if ($pmid) {
				$tmp = $_ENV['pm']->getpmbypmid($uid, $pmid);
				$tmp = $tmp[0];
			} else {
				$tmp = array();
			}

			if (!empty($pmid)) {
				if ($do == 'forward') {
					$user = $_ENV['user']->get_user_by_uid($tmp['msgtoid']);
					$tmp['message'] = $this->lang['pm_from'] . ': ' . $tmp['author'] . "\n" .
							$this->lang['pm_to'] . ': ' . dhtmlspecialchars($user['username']) . "\n" .
							$this->lang['pm_date'] . ': ' . $this->date($tmp['dateline']) . "\n\n" .
							'[quote]' . trim(preg_replace("/(\[quote])(.*)(\[\/quote])/siU", '', $tmp['message'])) . '[/quote]' . "\n";
				}
			} else {
				!empty($_GET['msgto']) && $touser = dhtmlspecialchars($_GET['msgto']);
				!empty($_GET['subject']) && $tmp['subject'] = $_GET['subject'];
				!empty($_GET['message']) && $tmp['message'] = $_GET['message'];
			}

			if ($this->settings['sendpmseccode']) {
				$authkey = md5(UC_KEY . $_SERVER['HTTP_USER_AGENT'] . $this->onlineip);
				$rand = rand(100000, 999999);
				$seccodeinit = rawurlencode($this->authcode($rand, 'ENCODE', $authkey, 720));
				$this->view->assign('seccodeinit', $seccodeinit);
			}

			$this->view->assign('sendpmseccode', $this->settings['sendpmseccode']);
			$this->view->assign('touser', $touser);
			$this->view->assign('user', $this->user);
			$this->view->assign('pmnum_private', $pmnum_private);
			$this->view->assign('pmnum_chatpm', $pmnum_chatpm);
			$this->view->assign('unreadpmnum', $unreadpmnum);
			$this->view->assign('friends', $friends);
			$this->view->assign('extra', $extra);
			$this->view->assign('pmid', $pmid);
			$this->view->assign('daterange', $daterange);
			$this->view->assign('touid', $touid);
			$this->view->assign('plid', $plid);
			$this->view->assign('a', $a);
			$this->view->assign('do', $do);
			$this->view->assign('folder', $folder);
			$tmp['message'] = dhtmlspecialchars($tmp['message']);
			$this->view->assign('message', $tmp['message']);
			$this->view->assign('type', $type);
			$this->view->display('pm_send');
		} else {

			if ($this->settings['sendpmseccode']) {
				$authkey = md5(UC_KEY . $_SERVER['HTTP_USER_AGENT'] . $this->onlineip);
				$seccodehidden = urldecode(getgpc('seccodehidden', 'P'));
				$seccode = strtoupper(getgpc('seccode', 'P'));
				$seccodehidden = $this->authcode($seccodehidden, 'DECODE', $authkey);
				require UC_ROOT . './lib/seccode.class.php';
				if (!seccode::seccode_check($seccodehidden, $seccode)) {
					$this->message('pm_send_seccode_error', 'BACK', 1);
				}
			}

			$user = $_ENV['user']->get_user_by_uid($this->user['uid']);
			$this->user['username'] = daddslashes($user['username'], 1);
			$touid = intval(getgpc('touid'));
			$daterange = intval(getgpc('daterange'));
			$type = intval(getgpc('type'));
			$replypmid = @is_numeric($_GET['replypmid']) ? $_GET['replypmid'] : 0;

			$msgto = array();
			if ($replypmid) {
				$plid = $_ENV['pm']->getplidbypmid($replypmid);
				$msgto = $_ENV['pm']->getuidbyplid($plid);
				unset($msgto[$uid]);
			} else {
				if (!empty($_POST['msgto'])) {
					$msgto = explode(',', $_POST['msgto']);
					$msgto = $_ENV['user']->name2id($msgto);
				}
			}
			if (isset($_POST['friend'])) {
				$frienduids = array();
				foreach ($friends as $friend) {
					$frienduids[] = $friend['friendid'];
				}
				foreach ($_POST['friend'] as $friendid) {
					if (in_array($friendid, $frienduids)) {
						$msgto[] = $friendid;
					}
				}
			}
			if (!$msgto) {
				$this->message('receiver_no_exists', 'BACK', 1);
			}

			$msgto = array_unique($msgto);
			$countmsgto = count($msgto);

			if ($this->settings['pmsendregdays']) {
				if ($user['regdate'] > $this->time - $this->settings['pmsendregdays'] * 86400) {
					$this->message('pm_send_regdays_error', 'BACK', 1, array('$pmsendregdays' => $this->settings['pmsendregdays']));
				}
			}
			if ($this->settings['chatpmmemberlimit']) {
				if ($type == 1 && ($countmsgto > ($this->settings['chatpmmemberlimit'] - 1))) {
					$this->message('pm_send_chatpmmemberlimit_error', 'BACK', 1, array('$chatpmmemberlimit' => $this->settings['chatpmmemberlimit']));
				}
			}
			if ($this->settings['pmfloodctrl']) {
				if (!$_ENV['pm']->ispminterval($this->user['uid'], $this->settings['pmfloodctrl'])) {
					$this->message('pm_send_pmfloodctrl_error', 'BACK', 1, array('$pmfloodctrl' => $this->settings['pmfloodctrl']));
				}
			}
			if ($this->settings['privatepmthreadlimit']) {
				if (!$_ENV['pm']->isprivatepmthreadlimit($this->user['uid'], $this->settings['privatepmthreadlimit'])) {
					$this->message('pm_send_privatepmthreadlimit_error', 'BACK', 1, array('$privatepmthreadlimit' => $this->settings['privatepmthreadlimit']));
				}
			}
			if ($this->settings['chatpmthreadlimit']) {
				if (!$_ENV['pm']->ischatpmthreadlimit($this->user['uid'], $this->settings['chatpmthreadlimit'])) {
					$this->message('pm_send_chatpmthreadlimit_error', 'BACK', 1, array('$chatpmthreadlimit' => $this->settings['chatpmthreadlimit']));
				}
			}

			if ($replypmid) {
				$lastpmid = $_ENV['pm']->replypm($plid, $this->user['uid'], $this->user['username'], $_POST['message']);
			} else {
				$lastpmid = $_ENV['pm']->sendpm($this->user['uid'], $this->user['username'], $msgto, '', $_POST['message'], $type);
			}
			if ($lastpmid > 0) {
				if ($replypmid) {
					if ($touid) {
						$this->message('pm_send_succeed', "index.php?m=pm_client&a=view&touid=$touid&daterange=$daterange&filter=privatepm", 1);
					} else {
						$this->message('pm_send_succeed', "index.php?m=pm_client&a=view&plid=$plid&daterange=$daterange&filter=chatpm", 1);
					}
				} else {
					if (!$type) {
						$this->message('pm_send_succeed', 'index.php?m=pm_client&a=ls&filter=privatepm', 1);
					} else {
						$this->message('pm_send_succeed', 'index.php?m=pm_client&a=ls&filter=chatpm', 1);
					}
				}
			} else {
				$this->message('pm_send_ignore', 'BACK', 1);
			}
		}
	}

	function ondelete() {
		$this->_auth();
		$uid = $this->user['uid'];
		$deletetouids = getgpc('deleteuid');
		$deleteplids = getgpc('deleteplid');
		$quitplids = getgpc('deletequitplid');
		$filter = getgpc('filter');
		if ($deletetouids && $deleteplids && $quitplids) {
			$this->message('pm_delete_invalid', 'index.php?m=pm_client&a=ls&filter=' . $filter . '&' . $_GET['extra'], 1);
		}
		$flag = true;
		if ($deletetouids) {
			$return = $_ENV['pm']->deletepmbyplids($uid, $deletetouids, 1);
			if ($return <= 0) {
				$flag = false;
			}
		}
		if ($deleteplids) {
			$return = $_ENV['pm']->deletepmbyplids($uid, $deleteplids);
			if ($return <= 0) {
				$flag = false;
			}
		}
		if ($quitplids) {
			$return = $_ENV['pm']->quitchatpm($uid, $quitplids);
			if ($return <= 0) {
				$flag = false;
			}
		}
		if ($flag) {
			$this->message('pm_delete_succeed', 'index.php?m=pm_client&a=ls&filter=' . $filter . '&' . $_GET['extra'], 1);
		} else {
			$this->message('pm_delete_invalid', 'index.php?m=pm_client&a=ls&filter=' . $filter . '&' . $_GET['extra'], 1);
		}
	}

	function onview() {
		$touid = intval(getgpc('touid'));
		$plid = intval(getgpc('plid'));
		$scroll = getgpc('scroll');
		$daterange = getgpc('daterange');
		$filter = getgpc('filter');
		$extra = 'extra=' . rawurlencode(getgpc('extra'));
		$a = getgpc('a');
		$this->_auth();
		$uid = $this->user['uid'];
		$pmnum_private = $_ENV['pm']->getpmnum($uid, 0, 0);
		$unreadpmnum = $_ENV['pm']->getpmnum($uid, 0, 1);

		$daterange = empty($daterange) ? 1 : $daterange;
		$today = $this->time - ($this->time + $this->settings['timeoffset']) % 86400;
		if ($daterange == 1) {
			$starttime = $today;
		} elseif ($daterange == 2) {
			$starttime = $today - 86400;
		} elseif ($daterange == 3) {
			$starttime = $today - 172800;
		} elseif ($daterange == 4) {
			$starttime = $today - 604800;
		} elseif ($daterange == 5) {
			$starttime = 0;
		}
		$endtime = $this->time;
		if ($touid) {
			$touser = $_ENV['user']->get_user_by_uid($touid);
			$plid = $_ENV['pm']->getplidbytouid($uid, $touid);
			$pms = $_ENV['pm']->getpmbyplid($uid, $plid, $starttime, $endtime, 0, 0, 0);
		} elseif ($plid) {
			$pms = $_ENV['pm']->getpmbyplid($uid, $plid, $starttime, $endtime, 0, 0, 1);
		}
		if ($pms) {
			$founderuid = $pms[0]['founderuid'];
			$replypmid = $pms[0]['pmid'];
			$subject = $pms[0]['subject'];
		}

		require_once UC_ROOT . 'lib/uccode.class.php';
		$this->uccode = new uccode();
		foreach ($pms as $key => $pm) {
			$pms[$key]['message'] = $this->uccode->complie($pms[$key]['message']);
			$pms[$key]['dateline'] = $this->date($pms[$key]['dateline']);
		}

		if ($this->settings['sendpmseccode']) {
			$authkey = md5(UC_KEY . $_SERVER['HTTP_USER_AGENT'] . $this->onlineip);
			$rand = rand(100000, 999999);
			$seccodeinit = rawurlencode($this->authcode($rand, 'ENCODE', $authkey, 720));
			$this->view->assign('seccodeinit', $seccodeinit);
		}

		$this->view->assign('sendpmseccode', $this->settings['sendpmseccode']);
		$this->view->assign('scroll', $scroll);
		$this->view->assign('user', $this->user);
		$this->view->assign('touser', $touser);
		$this->view->assign('subject', $subject);
		$this->view->assign('pmnum_private', $pmnum_private);
		$this->view->assign('pmnum_chatpm', $pmnum_chatpm);
		$this->view->assign('unreadpmnum', $unreadpmnum);
		$this->view->assign('daterange', $daterange);
		$this->view->assign('replypmid', $replypmid);
		$this->view->assign('touid', $touid);
		$this->view->assign('plid', $plid);
		$this->view->assign('extra', $extra);
		$this->view->assign('founderuid', $founderuid);
		$this->view->assign('filter', $filter);
		$this->view->assign('pms', $pms);
		$this->view->display('pm_view');
	}

	function onmember() {
		$plid = intval(getgpc('plid'));
		$scroll = getgpc('scroll');
		$daterange = getgpc('daterange');
		$filter = getgpc('filter');
		$extra = 'extra=' . rawurlencode(getgpc('extra'));
		$a = getgpc('a');
		$do = getgpc('do');
		$this->_auth();
		$uid = $this->user['uid'];
		$pmnum_private = $_ENV['pm']->getpmnum($uid, 0, 0);
		$unreadpmnum = $_ENV['pm']->getpmnum($uid, 0, 1);

		if ($do == 'kickmember') {
			$memberuid = intval(getgpc('memberuid'));
			if ($memberuid) {
				$_ENV['pm']->kickchatpm($plid, $uid, $memberuid);
			}
			$this->message('pm_kickmember_succeed', "index.php?m=pm_client&a=member&plid=$plid&filter=" . $filter . '&' . $_GET['extra'], 1);
		}
		if ($this->submitcheck() && $do == 'appendmember') {
			$appendmember = $_ENV['user']->get_user_by_username(getgpc('appendmember'));
			if ($appendmember) {
				$return = $_ENV['pm']->appendchatpm($plid, $uid, $appendmember['uid']);
				if ($return > 0) {
					$this->message('pm_appendmember_succeed', "index.php?m=pm_client&a=member&plid=$plid&filter=" . $filter . '&' . $_GET['extra'], 1);
				}
			}
			$this->message('pm_appendmember_invalid', "index.php?m=pm_client&a=member&plid=$plid&filter=" . $filter . '&' . $_GET['extra'], 1);
		}

		$members = $_ENV['pm']->chatpmmemberlist($uid, $plid);
		$authorid = $members['author'];
		$members = $members['member'];
		$members = $_ENV['user']->id2name($members);

		$this->view->assign('scroll', $scroll);
		$this->view->assign('user', $this->user);
		$this->view->assign('pmnum_private', $pmnum_private);
		$this->view->assign('pmnum_chatpm', $pmnum_chatpm);
		$this->view->assign('unreadpmnum', $unreadpmnum);
		$this->view->assign('replypmid', $replypmid);
		$this->view->assign('subject', $subject);
		$this->view->assign('daterange', $daterange);
		$this->view->assign('plid', $plid);
		$this->view->assign('extra', $extra);
		$this->view->assign('filter', $filter);
		$this->view->assign('authorid', $authorid);
		$this->view->assign('members', $members);
		$this->view->display('pm_member');
	}

}

?>