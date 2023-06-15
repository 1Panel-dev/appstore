<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: extend_thread_activity.php 35202 2015-02-04 08:07:39Z hypowang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class extend_thread_activity extends extend_thread_base {

	public $activity;
	public $activitytime;

	public function before_newthread($parameters) {

		$this->activitytime = intval($_GET['activitytime']);
		if(empty($_GET['starttimefrom'][$this->activitytime])) {
			showmessage('activity_fromtime_please');
		} elseif(@strtotime($_GET['starttimefrom'][$this->activitytime]) === -1 || @strtotime($_GET['starttimefrom'][$this->activitytime]) === FALSE) {
			showmessage('activity_fromtime_error');
		} elseif($this->activitytime && ((@strtotime($_GET['starttimefrom']) > @strtotime($_GET['starttimeto']) || !$_GET['starttimeto']))) {
			showmessage('activity_fromtime_error');
		} elseif(!trim($_GET['activityclass'])) {
			showmessage('activity_sort_please');
		} elseif(!trim($_GET['activityplace'])) {
			showmessage('activity_address_please');
		} elseif(trim($_GET['activityexpiration']) && (@strtotime($_GET['activityexpiration']) === -1 || @strtotime($_GET['activityexpiration']) === FALSE)) {
			showmessage('activity_totime_error');
		}

		$this->activity = array();
		$this->activity['class'] = censor(dhtmlspecialchars(trim($_GET['activityclass'])));
		$this->activity['starttimefrom'] = @strtotime($_GET['starttimefrom'][$this->activitytime]);
		$this->activity['starttimeto'] = $this->activitytime ? @strtotime($_GET['starttimeto']) : 0;
		$this->activity['place'] = censor(dhtmlspecialchars(trim($_GET['activityplace'])));
		$this->activity['cost'] = intval($_GET['cost']);
		$this->activity['gender'] = intval($_GET['gender']);
		$this->activity['number'] = intval($_GET['activitynumber']);

		if($_GET['activityexpiration']) {
			$this->activity['expiration'] = @strtotime($_GET['activityexpiration']);
		} else {
			$this->activity['expiration'] = 0;
		}
		if(trim($_GET['activitycity'])) {
			$this->param['subject'] = $parameters['subject'].'['.dhtmlspecialchars(trim($_GET['activitycity'])).']';
		}
		$extfield = $_GET['extfield'];
		$extfield = explode("\n", $_GET['extfield']);
		foreach($extfield as $key => $value) {
			$extfield[$key] = dhtmlspecialchars(strip_tags(censor(trim($value))));
			if($extfield[$key] === '' || is_numeric($extfield[$key])) {
				unset($extfield[$key]);
			}
		}
		$extfield = array_unique($extfield);
		if(count($extfield) > $this->setting['activityextnum']) {
			showmessage('post_activity_extfield_toomany', '', array('maxextfield' => $this->setting['activityextnum']));
		}
		$this->activity['ufield'] = array('userfield' => $_GET['userfield'], 'extfield' => $extfield);
		$this->activity['ufield'] = serialize($this->activity['ufield']);
		if(intval($_GET['activitycredit']) > 0) {
			$this->activity['credit'] = intval($_GET['activitycredit']);
		}
		$this->param['extramessage'] = "\t".$_GET['activityplace']."\t".$_GET['activitycity']."\t".$_GET['activityclass'];
	}

	public function after_newthread() {
		if($this->group['allowpostactivity']) {
			$data = array('tid' => $this->tid, 'uid' => $this->member['uid'], 'cost' => $this->activity['cost'], 'starttimefrom' => $this->activity['starttimefrom'], 'starttimeto' => $this->activity['starttimeto'], 'place' => $this->activity['place'], 'class' => $this->activity['class'], 'gender' => $this->activity['gender'], 'number' => $this->activity['number'], 'expiration' => $this->activity['expiration'], 'aid' => $_GET['activityaid'], 'ufield' => $this->activity['ufield'], 'credit' => $this->activity['credit']);
			C::t('forum_activity')->insert($data);
		}
	}

	public function before_feed() {
		$message = !$this->param['price'] && !$this->param['readperm'] ? $this->param['message'] : '';
		$this->feed['icon'] = 'activity';
		$this->feed['title_template'] = 'feed_thread_activity_title';
		$this->feed['body_template'] = 'feed_thread_activity_message';
		$this->feed['body_data'] = array(
			'subject' => "<a href=\"forum.php?mod=viewthread&tid={$this->tid}\">{$this->param['subject']}</a>",
			'starttimefrom' => $_GET['starttimefrom'][$this->activitytime],
			'activityplace'=> $this->activity['place'],
			'message' => messagecutstr($message, 150),
		);
		if($_GET['activityaid']) {
			$this->feed['images'] = array(getforumimg($_GET['activityaid']));
			$this->feed['image_links'] = array("forum.php?mod=viewthread&do=tradeinfo&tid={$this->tid}&pid={$this->pid}");
		}
	}

	public function before_editpost($parameters) {
		$isfirstpost = $this->post['first'] ? 1 : 0;
		if($isfirstpost) {
			if($this->thread['special'] == 4 && $this->group['allowpostactivity']) {
				$activitytime = intval($_GET['activitytime']);
				if(empty($_GET['starttimefrom'][$activitytime])) {
					showmessage('activity_fromtime_please');
				} elseif(strtotime($_GET['starttimefrom'][$activitytime]) === -1 || @strtotime($_GET['starttimefrom'][$activitytime]) === FALSE) {
					showmessage('activity_fromtime_error');
				} elseif($activitytime && ((@strtotime($_GET['starttimefrom']) > @strtotime($_GET['starttimeto']) || !$_GET['starttimeto']))) {
					showmessage('activity_fromtime_error');
				} elseif(!trim($_GET['activityclass'])) {
					showmessage('activity_sort_please');
				} elseif(!trim($_GET['activityplace'])) {
					showmessage('activity_address_please');
				} elseif(trim($_GET['activityexpiration']) && (@strtotime($_GET['activityexpiration']) === -1 || @strtotime($_GET['activityexpiration']) === FALSE)) {
					showmessage('activity_totime_error');
				}

				$activity = array();
				$activity['class'] = censor(dhtmlspecialchars(trim($_GET['activityclass'])));
				$activity['starttimefrom'] = @strtotime($_GET['starttimefrom'][$activitytime]);
				$activity['starttimeto'] = $activitytime ? @strtotime($_GET['starttimeto']) : 0;
				$activity['place'] = censor(dhtmlspecialchars(trim($_GET['activityplace'])));
				$activity['cost'] = intval($_GET['cost']);
				$activity['gender'] = intval($_GET['gender']);
				$activity['number'] = intval($_GET['activitynumber']);
				if($_GET['activityexpiration']) {
					$activity['expiration'] = @strtotime($_GET['activityexpiration']);
				} else {
					$activity['expiration'] = 0;
				}
				$extfield = $_GET['extfield'];
				$extfield = explode("\n", $_GET['extfield']);
				foreach($extfield as $key => $value) {
					$extfield[$key] = dhtmlspecialchars(strip_tags(censor(trim($value))));
					if($extfield[$key] === '' || is_numeric($extfield[$key])) {
						unset($extfield[$key]);
					}
				}
				$extfield = array_unique($extfield);
				if(count($extfield) > $this->setting['activityextnum']) {
					showmessage('post_activity_extfield_toomany', '', array('maxextfield' => $this->setting['activityextnum']));
				}
				$activity['ufield'] = array('userfield' => $_GET['userfield'], 'extfield' => $extfield);
				$activity['ufield'] = serialize($activity['ufield']);
				if(intval($_GET['activitycredit']) > 0) {
					$activity['credit'] = intval($_GET['activitycredit']);
				}
				$data = array('cost' => $activity['cost'], 'starttimefrom' => $activity['starttimefrom'], 'starttimeto' => $activity['starttimeto'], 'place' => $activity['place'], 'class' => $activity['class'], 'gender' => $activity['gender'], 'number' => $activity['number'], 'expiration' => $activity['expiration'], 'ufield' => $activity['ufield'], 'credit' => $activity['credit']);
				C::t('forum_activity')->update($this->thread['tid'], $data);

			}
		}


		if($parameters['special'] == 4 && $isfirstpost && $this->group['allowpostactivity']) {
			$activity = C::t('forum_activity')->fetch($this->thread['tid']);
			$activityaid = $activity['aid'];
			if($activityaid && $activityaid != $_GET['activityaid']) {
				$attach = C::t('forum_attachment_n')->fetch('tid:'.$this->thread['tid'], $activityaid);
				C::t('forum_attachment')->delete($activityaid);
				C::t('forum_attachment_n')->delete('tid:'.$this->thread['tid'], $activityaid);
				dunlink($attach);
			}
			if($_GET['activityaid']) {
				$threadimageaid = $_GET['activityaid'];
				convertunusedattach($_GET['activityaid'], $this->thread['tid'], $this->post['pid']);
				C::t('forum_activity')->update($this->thread['tid'], array('aid' => $_GET['activityaid']));
			}
		}
	}

}

?>