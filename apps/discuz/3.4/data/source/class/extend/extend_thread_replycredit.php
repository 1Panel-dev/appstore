<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: extend_thread_replycredit.php 33418 2013-06-08 08:46:32Z andyzheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class extend_thread_replycredit extends extend_thread_base {

	public $replycredit_real;
	public function before_newthread($parameters) {

		if($this->group['allowreplycredit']) {
			$_GET['replycredit_extcredits'] = intval($_GET['replycredit_extcredits']);
			$_GET['replycredit_times'] = intval($_GET['replycredit_times']);
			$_GET['replycredit_membertimes'] = intval($_GET['replycredit_membertimes']) > 0 && intval($_GET['replycredit_membertimes']) <= 10 ? intval($_GET['replycredit_membertimes']) : 1;
			$_GET['replycredit_random'] = intval($_GET['replycredit_random']);

			$_GET['replycredit_random'] = $_GET['replycredit_random'] < 0 || $_GET['replycredit_random'] > 99 ? 0 : $_GET['replycredit_random'] ;
			$this->replycredit_real = 0;
			$this->param['replycredit'] = 0;
			if($_GET['replycredit_extcredits'] > 0 && $_GET['replycredit_times'] > 0) {
				$this->replycredit_real = ceil(($_GET['replycredit_extcredits'] * $_GET['replycredit_times']) + ($_GET['replycredit_extcredits'] * $_GET['replycredit_times'] *  $this->setting['creditstax']));
				if($this->replycredit_real > getuserprofile('extcredits'.$this->setting['creditstransextra']['10'])) {
					showmessage('replycredit_morethan_self');
				} else {
					$this->param['replycredit'] = ceil($_GET['replycredit_extcredits'] * $_GET['replycredit_times']);
				}
			}
		}
	}

	public function after_newthread() {

		if($this->group['allowreplycredit']) {
			if($this->param['replycredit'] > 0 && $this->replycredit_real > 0) {
				updatemembercount($this->member['uid'], array('extcredits'.$this->setting['creditstransextra']['10'] => -$this->replycredit_real), 1, 'RCT', $this->tid);
				$insertdata = array(
						'tid' => $this->tid,
						'extcredits' => $_GET['replycredit_extcredits'],
						'extcreditstype' => $this->setting['creditstransextra']['10'],
						'times' => $_GET['replycredit_times'],
						'membertimes' => $_GET['replycredit_membertimes'],
						'random' => $_GET['replycredit_random']
					);
				C::t('forum_replycredit')->insert($insertdata);
			}
		}

	}

	public function after_newreply() {
		if($this->thread['replycredit'] > 0 && !$this->param['modnewreplies'] && $this->thread['authorid'] != $this->member['uid'] && $this->member['uid']) {

			$replycredit_rule = C::t('forum_replycredit')->fetch($this->thread['tid']);
			if(!empty($replycredit_rule['times'])) {
				$have_replycredit = C::t('common_credit_log')->count_by_uid_operation_relatedid($this->member['uid'], 'RCA', $this->thread['tid']);
				if($replycredit_rule['membertimes'] - $have_replycredit > 0 && $this->thread['replycredit'] - $replycredit_rule['extcredits'] >= 0) {
					$creditstransextra = $this->setting['creditstransextra'];
					$replycredit_rule['extcreditstype'] = $replycredit_rule['extcreditstype'] ? $replycredit_rule['extcreditstype'] : $creditstransextra[10];
					if($replycredit_rule['random'] > 0) {
						$rand = rand(1, 100);
						$rand_replycredit = $rand <= $replycredit_rule['random'] ? true : false ;
					} else {
						$rand_replycredit = true;
					}
					if($rand_replycredit) {
						updatemembercount($this->member['uid'], array($replycredit_rule['extcreditstype'] => $replycredit_rule['extcredits']), 1, 'RCA', $this->thread['tid']);
						C::t('forum_post')->update('tid:'.$this->thread['tid'], $this->pid, array('replycredit' => $replycredit_rule['extcredits']));
						C::t('forum_thread')->update($this->thread['tid'], (array)DB::field('replycredit', $this->thread['replycredit'] - $replycredit_rule['extcredits']), false, false, 0, true);
					}
				}
			}
		}
	}

	public function before_editpost($parameters) {
		$isfirstpost = $this->post['first'] ? 1 : 0;
		$isorigauthor = $this->member['uid'] && $this->member['uid'] == $this->post['authorid'];
		if($isfirstpost) {
			if($isorigauthor && $this->group['allowreplycredit']) {
				$replycredit_rule = isset($parameters['replycredit_rule']) && $parameters['replycredit_rule'] ? $parameters['replycredit_rule'] : array();
				$_POST['replycredit_extcredits'] = intval($_POST['replycredit_extcredits']);
				$_POST['replycredit_times'] = intval($_POST['replycredit_times']);
				$_POST['replycredit_membertimes'] = intval($_POST['replycredit_membertimes']) > 0 && intval($_POST['replycredit_membertimes']) <= 10 ? intval($_POST['replycredit_membertimes']) : 1;
				$_POST['replycredit_random'] = intval($_POST['replycredit_random']) < 0 || intval($_POST['replycredit_random']) > 99 ? 0 : intval($_POST['replycredit_random']) ;
				if($_POST['replycredit_extcredits'] > 0 && $_POST['replycredit_times'] > 0) {
					$replycredit = $_POST['replycredit_extcredits'] * $_POST['replycredit_times'];
					$replycredit_diff =  $replycredit - $this->thread['replycredit'];
					if($replycredit_diff > 0) {
						$replycredit_diff = ceil($replycredit_diff + ($replycredit_diff * $this->setting['creditstax']));
						if(!$replycredit_rule) {
							if($this->setting['creditstransextra']['10']) {
								$replycredit_rule['extcreditstype'] = $this->setting['creditstransextra']['10'];
							}
						}

						if($replycredit_diff > getuserprofile('extcredits'.$replycredit_rule['extcreditstype'])) {
							showmessage('post_edit_thread_replaycredit_nocredit');
						}
					}
					if($replycredit_diff) {
						updatemembercount($this->thread['authorid'], array($replycredit_rule['extcreditstype'] => ($replycredit_diff > 0 ? -$replycredit_diff : abs($replycredit_diff))), 1, ($replycredit_diff > 0 ? 'RCT' : 'RCB'), $this->thread['tid']);
					}
				} elseif(($_POST['replycredit_extcredits'] == 0 || $_POST['replycredit_times'] == 0) && $this->thread['replycredit'] > 0) {
					$replycredit = 0;
					C::t('forum_replycredit')->delete($this->thread['tid']);
					updatemembercount($this->thread['authorid'], array($replycredit_rule['extcreditstype'] => $this->thread['replycredit']), 1, 'RCB', $this->thread['tid']);
					$this->param['threadupdatearr']['replycredit'] = 0;
				} else {
					$replycredit = $this->thread['replycredit'];
				}
				if($replycredit) {
					$this->param['threadupdatearr']['replycredit'] = $replycredit;
					$replydata = array(
							'tid' => $this->thread['tid'],
							'extcredits' => $_POST['replycredit_extcredits'],
							'extcreditstype' => $replycredit_rule['extcreditstype'],
							'times' => $_POST['replycredit_times'],
							'membertimes' => $_POST['replycredit_membertimes'],
							'random' => $_POST['replycredit_random']
						);
					C::t('forum_replycredit')->insert($replydata, false, true);
				}
			}
		}
	}

	public function before_deletepost() {
		global $replycredit_rule;
		$isfirstpost = $this->post['first'] ? 1 : 0;
		if($this->thread['replycredit'] && $isfirstpost && !$this->param['isanonymous']) {
			updatemembercount($this->post['authorid'], array($replycredit_rule['extcreditstype'] => $this->thread['replycredit']), true, 'RCB', $this->thread['tid']);
			C::t('forum_replycredit')->delete($this->thread['tid']);

			$this->param['handlereplycredit'] = true;
		}
	}
}

?>