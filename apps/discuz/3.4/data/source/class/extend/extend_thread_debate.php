<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: extend_thread_debate.php 30673 2012-06-11 07:51:54Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class extend_thread_debate extends extend_thread_base {

	public $affirmpoint;
	public $negapoint;
	public $endtime;
	public $stand;

	public function before_newthread($parameters) {
		if(empty($_GET['affirmpoint']) || empty($_GET['negapoint'])) {
			showmessage('debate_position_nofound');
		} elseif(!empty($_GET['endtime']) && (!($this->endtime = @strtotime($_GET['endtime'])) || $this->endtime < TIMESTAMP)) {
			showmessage('debate_endtime_invalid');
		} elseif(!empty($_GET['umpire'])) {
			if(!C::t('common_member')->fetch_uid_by_username($_GET['umpire'])) {
				$_GET['umpire'] = dhtmlspecialchars($_GET['umpire']);
				showmessage('debate_umpire_invalid', '', array('umpire' => $_GET['umpire']));
			}
		}
		$this->affirmpoint = censor(dhtmlspecialchars($_GET['affirmpoint']));
		$this->negapoint = censor(dhtmlspecialchars($_GET['negapoint']));
		$this->stand = intval($_GET['stand']);
		$this->param['extramessage'] = "\t".$_GET['affirmpoint']."\t".$_GET['negapoint'];
	}

	public function after_newthread() {
		if($this->group['allowpostdebate']) {

		C::t('forum_debate')->insert(array(
			'tid' => $this->tid,
			'uid' => $this->member['uid'],
			'starttime' => $this->param['publishdate'],
			'endtime' => $this->endtime,
			'affirmdebaters' => 0,
			'negadebaters' => 0,
			'affirmvotes' => 0,
			'negavotes' => 0,
			'umpire' => $_GET['umpire'],
			'winner' => '',
			'bestdebater' => '',
			'affirmpoint' => $this->affirmpoint,
			'negapoint' => $this->negapoint,
			'umpirepoint' => ''
		));

	}
	}

	public function before_feed() {

		$message = !$this->param['price'] && !$this->param['readperm'] ? $this->param['message'] : '';
		$this->feed['icon'] = 'debate';
		$this->feed['title_template'] = 'feed_thread_debate_title';
		$this->feed['body_template'] = 'feed_thread_debate_message';
		$this->feed['body_data'] = array(
			'subject' => "<a href=\"forum.php?mod=viewthread&tid={$this->tid}\">{$this->param['subject']}</a>",
			'message' => messagecutstr($message, 150),
			'affirmpoint'=> messagecutstr($this->affirmpoint, 150),
			'negapoint'=> messagecutstr($this->negapoint, 150)
		);
	}

	public function after_newreply() {
		global $firststand, $stand;
		if($this->param['special'] == 5) {
			if(!$firststand) {
				C::t('forum_debate')->update_debaters($this->thread['tid'], $stand);
			} else {
				$stand = $firststand;
			}
			C::t('forum_debate')->update_replies($this->thread['tid'], $stand);
			C::t('forum_debatepost')->insert(array(
			    'tid' => $this->thread['tid'],
			    'pid' => $this->pid,
			    'uid' => $this->member['uid'],
			    'dateline' => getglobal('timestamp'),
			    'stand' => $stand,
			    'voters' => 0,
			    'voterids' => '',
			));
		}
	}

	public function before_replyfeed() {
		global $stand;
		if($this->forum['allowfeed'] && !$this->param['isanonymous']) {
			if($this->param['special'] == 5 && $this->thread['authorid'] != $this->member['uid']) {
				$this->feed['icon'] = 'debate';
				if($stand == 1) {
					$this->feed['title_template'] = 'feed_thread_debatevote_title_1';
				} elseif($stand == 2) {
					$this->feed['title_template'] = 'feed_thread_debatevote_title_2';
				} else {
					$this->feed['title_template'] = 'feed_thread_debatevote_title_3';
				}
				$this->feed['title_data'] = array(
					'subject' => "<a href=\"forum.php?mod=viewthread&tid=".$this->thread['tid']."\">".$this->thread['subject']."</a>",
					'author' => "<a href=\"home.php?mod=space&uid=".$this->thread['authorid']."\">".$this->thread['author']."</a>"
				);
			}
		}
	}

	public function before_editpost($parameters) {
		$isfirstpost = $this->post['first'] ? 1 : 0;
		if($isfirstpost) {
			if($this->thread['special'] == 5 && $this->group['allowpostdebate']) {

				if(empty($_GET['affirmpoint']) || empty($_GET['negapoint'])) {
					showmessage('debate_position_nofound');
				} elseif(!empty($_GET['endtime']) && (!($endtime = @strtotime($_GET['endtime'])) || $endtime < TIMESTAMP)) {
					showmessage('debate_endtime_invalid');
				} elseif(!empty($_GET['umpire'])) {
					if(!C::t('common_member')->fetch_uid_by_username($_GET['umpire'])) {
						$_GET['umpire'] = dhtmlspecialchars($_GET['umpire']);
						showmessage('debate_umpire_invalid');
					}
				}
				$affirmpoint = censor(dhtmlspecialchars($_GET['affirmpoint']));
				$negapoint = censor(dhtmlspecialchars($_GET['negapoint']));
				C::t('forum_debate')->update($this->thread['tid'], array('affirmpoint' => $affirmpoint, 'negapoint' => $negapoint, 'endtime' => $endtime, 'umpire' => $_GET['umpire']));

			}
		}
	}
}

?>