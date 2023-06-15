<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: model_forum_post.php 34819 2014-08-11 06:46:20Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class model_forum_post extends discuz_model {
	public $forum;

	public $thread;

	public $post;







	public $pid = 0;

	public $feed = array();

	public function __construct($tid = null, $pid = null) {
		parent::__construct();
		require_once libfile('function/post');
		require_once libfile('function/forumlist');
		if($tid) {
			include_once libfile('function/forum');
			loadforum(null, $tid);
			if($pid) {
				$this->post = get_post_by_tid_pid($tid, $pid);
			}
		}
		$this->forum = &$this->app->var['forum'];
		$this->thread = &$this->app->var['thread'];
		$this->group = &$this->app->var['group'];
	}

	protected function _init_parameters($parameters) {
		$varname = array(
			'member', 'group', 'forum', 'thread', 'extramessage', 'special',//'nauthorid' 'modnewreplies' 'tid'
			'message','clientip', 'invisible', 'isanonymous', 'usesig',
			'htmlon', 'bbcodeoff', 'smileyoff', 'parseurloff', 'pstatus',
			'noticetrimstr', 'noticeauthor', 'from', 'sechash', 'geoloc',
			'timestamp',

			'subject', 'special', 'sortid', 'typeid', 'isanonymous', 'cronpublish', 'cronpublishdate', 'save',
			'readperm', 'price', 'ordertype', 'hiddenreplies', 'allownoticeauthor', 'audit', 'tags', 'bbcodeoff', 'imgcontent', 'imgcontentwidth',
			'smileyoff', 'parseurloff', 'usesig', 'htmlon', 'extramessage',

		);
		foreach($varname as $name) {
			if(!isset($this->param[$name]) && isset($parameters[$name])) {
				$this->param[$name] = $parameters[$name];
			}
		}

	}

	public function newreply($parameters) {

		$this->_init_parameters($parameters);

		if($this->thread['closed'] && !$this->forum['ismoderator'] && !$this->thread['isgroup']) {
			return $this->showmessage('post_thread_closed');
		} elseif(!$this->thread['isgroup'] && $post_autoclose = checkautoclose($this->thread)) {
			return $this->showmessage($post_autoclose, '', array('autoclose' => $this->forum['autoclose']));
		} if(trim($this->param['subject']) == '' && trim($this->param['message']) == '' && $this->thread['special'] != 2) {
			return $this->showmessage('post_sm_isnull');
		} elseif($post_invalid = checkpost($this->param['subject'], $this->param['message'], $this->param['special'] == 2 && $this->group['allowposttrade'])) {
			return $this->showmessage($post_invalid, '', array('minpostsize' => $this->setting['minpostsize'], 'maxpostsize' => $this->setting['maxpostsize']));
		} elseif(checkflood()) {
			return $this->showmessage('post_flood_ctrl', '', array('floodctrl' => $this->setting['floodctrl']));
		} elseif(checkmaxperhour('pid')) {
			return $this->showmessage('post_flood_ctrl_posts_per_hour', '', array('posts_per_hour' => $this->group['maxpostsperhour']));
		}


		$heatthreadset = update_threadpartake($this->thread['tid'], true);


		$bbcodeoff = checkbbcodes($this->param['message'], !empty($this->param['bbcodeoff']));
		$smileyoff = checksmilies($this->param['message'], !empty($this->param['smileyoff']));
		$parseurloff = !empty($this->param['parseurloff']);
		$htmlon = $this->group['allowhtml'] && !empty($this->param['htmlon']) ? 1 : 0;
		$usesig = !empty($this->param['usesig']) && $this->group['maxsigsize'] ? 1 : 0;

		$this->param['isanonymous'] = $this->group['allowanonymous'] && !empty($this->param['isanonymous'])? 1 : 0;
		$author = empty($this->param['isanonymous']) ? $this->member['username'] : '';

		list(, $this->param['modnewreplies']) = threadmodstatus($this->param['subject']."\t".$this->param['message'].$this->param['extramessage']);

		if($this->thread['displayorder'] == -4) {
			$this->param['modnewreplies'] = 0;
		}
		$pinvisible = $this->param['modnewreplies'] ? -2 : ($this->thread['displayorder'] == -4 ? -3 : 0);
		$this->param['message'] = preg_replace('/\[attachimg\](\d+)\[\/attachimg\]/is', '[attach]\1[/attach]', $this->param['message']);


		if(!empty($this->param['noticetrimstr'])) {
			$this->param['message'] = $this->param['noticetrimstr']."\n\n".$this->param['message'];
			$bbcodeoff = false;
		}

		$status = (defined('IN_MOBILE') ? 8 : 0);

		if($this->param['modstatus']) {
			foreach($this->param['modstatus'] as $modbit => $modvalue) {
				$status = setstatus($modbit, $modvalue, $status);
			}
		}

		$this->pid = insertpost(array(
			'fid' => $this->forum['fid'],
			'tid' => $this->thread['tid'],
			'first' => '0',
			'author' => $this->member['username'],
			'authorid' => $this->member['uid'],
			'subject' => $this->param['subject'],
			'dateline' => $this->param['timestamp'] ? $this->param['timestamp'] : getglobal('timestamp'),
			'message' => $this->param['message'],
			'useip' => $this->param['clientip'] ? $this->param['clientip'] : getglobal('clientip'),
			'port' => $this->param['remoteport'] ? $this->param['remoteport'] : getglobal('remoteport'),
			'invisible' => $pinvisible,
			'anonymous' => $this->param['isanonymous'],
			'usesig' => $usesig,
			'htmlon' => $htmlon,
			'bbcodeoff' => $bbcodeoff,
			'smileyoff' => $smileyoff,
			'parseurloff' => $parseurloff,
			'attachment' => '0',
			'status' => $status,
		));


		$this->param['updatethreaddata'] = $heatthreadset ? $heatthreadset : array();
		$this->param['maxposition'] = C::t('forum_post')->fetch_maxposition_by_tid($this->thread['posttableid'], $this->thread['tid']);
		$this->param['updatethreaddata'][] = DB::field('maxposition', $this->param['maxposition']);


		useractionlog($this->member['uid'], 'pid');

		if($this->param['geoloc'] && IN_MOBILE == 2) {
			list($mapx, $mapy, $location) = explode('|', $this->param['geoloc']);
			if($mapx && $mapy && $location) {
				C::t('forum_post_location')->insert(array(
					'pid' => $this->pid,
					'tid' => $this->thread['tid'],
					'uid' => $this->member['uid'],
					'mapx' => $mapx,
					'mapy' => $mapy,
					'location' => $location,
				));
			}
		}

		$nauthorid = 0;
		if(!empty($this->param['noticeauthor']) && !$this->param['isanonymous'] && !$this->param['modnewreplies']) {
			list($ac, $nauthorid) = explode('|', authcode($this->param['noticeauthor'], 'DECODE'));
			if($nauthorid != $this->member['uid']) {
				if($ac == 'q') {
					notification_add($nauthorid, 'post', 'reppost_noticeauthor', array(
						'tid' => $this->thread['tid'],
						'subject' => $this->thread['subject'],
						'fid' => $this->forum['fid'],
						'pid' => $this->pid,
						'from_id' => $this->pid,
						'from_idtype' => 'quote',
					));
				} elseif($ac == 'r') {
					notification_add($nauthorid, 'post', 'reppost_noticeauthor', array(
						'tid' => $this->thread['tid'],
						'subject' => $this->thread['subject'],
						'fid' => $this->forum['fid'],
						'pid' => $this->pid,
						'from_id' => $this->thread['tid'],
						'from_idtype' => 'post',
					));
				}
			}

		}

		if($this->thread['authorid'] != $this->member['uid'] && getstatus($this->thread['status'], 6) && empty($this->param['noticeauthor']) && !$this->param['isanonymous'] && !$this->param['modnewreplies']) {
			$thapost = C::t('forum_post')->fetch_threadpost_by_tid_invisible($this->thread['tid'], 0);
			notification_add($thapost['authorid'], 'post', 'reppost_noticeauthor', array(
				'tid' => $this->thread['tid'],
				'subject' => $this->thread['subject'],
				'fid' => $this->forum['fid'],
				'pid' => $this->pid,
				'from_id' => $this->thread['tid'],
				'from_idtype' => 'post',
			));
		}


		$this->forum['threadcaches'] && deletethreadcaches($this->thread['tid']);

		include_once libfile('function/stat');
		updatestat($this->thread['isgroup'] ? 'grouppost' : 'post');


		$this->param['showmsgparam']['fid'] = $this->forum['fid'];
		$this->param['showmsgparam']['tid'] = $this->thread['tid'];
		$this->param['showmsgparam']['pid'] = $this->pid;
		$this->param['showmsgparam']['from'] = $this->param['from'];
		$this->param['showmsgparam']['sechash'] = !empty($this->param['sechash']) ? $this->param['sechash'] : '';


		dsetcookie('clearUserdata', 'forum');

		if($this->param['modnewreplies']) {
			updatemoderate('pid', $this->pid);
			unset($this->param['showmsgparam']['pid']);
			if($this->param['updatethreaddata']) {
				C::t('forum_thread')->update($this->thread['tid'], $this->param['updatethreaddata'], false, false, 0, true);
			}
			C::t('forum_forum')->update_forum_counter($this->forum['fid'], 0, 0, 1, 1);


			manage_addnotify('verifypost');


			return 'post_reply_mod_succeed';

		} else {

			$fieldarr = array(
				'lastposter' => array($author),
				'replies' => 1
			);
			if($this->thread['lastpost'] < getglobal('timestamp')) {
				$fieldarr['lastpost'] = array(getglobal('timestamp'));
			}
			$row = C::t('forum_threadaddviews')->fetch($this->thread['tid']);
			if(!empty($row)) {
				C::t('forum_threadaddviews')->update($this->thread['tid'], array('addviews' => 0));
				$fieldarr['views'] = $row['addviews'];
			}
			$this->param['updatethreaddata'] = array_merge($this->param['updatethreaddata'], C::t('forum_thread')->increase($this->thread['tid'], $fieldarr, false, 0, true));
			if($this->thread['displayorder'] != -4) {
				updatepostcredits('+', $this->member['uid'], 'reply', $this->forum['fid']);
				if($this->forum['status'] == 3) {
					if($this->forum['closed'] > 1) {
						C::t('forum_thread')->increase($this->forum['closed'], $fieldarr, true);
					}
					C::t('forum_groupuser')->update_counter_for_user($this->member['uid'], $this->forum['fid'], 0, 1);
					C::t('forum_forumfield')->update($this->forum['fid'], array('lastupdate' => TIMESTAMP));
					require_once libfile('function/grouplog');
					updategroupcreditlog($this->forum['fid'], $this->member['uid']);
				}

				$lastpost = $this->thread['tid']."\t".$this->thread['subject']."\t".getglobal('timestamp')."\t".$author;
				C::t('forum_forum')->update($this->forum['fid'], array('lastpost' => $lastpost));
				C::t('forum_forum')->update_forum_counter($this->forum['fid'], 0, 1, 1);
				if($this->forum['type'] == 'sub') {
					C::t('forum_forum')->update($this->forum['fup'], array('lastpost' => $lastpost));
				}
			}


			$this->param['page'] = getstatus($this->thread['status'], 4) ? 1 : @ceil(($this->thread['special'] ? $this->thread['replies'] + 1 : $this->thread['replies'] + 2) / getglobal('ppp'));

			if($this->param['updatethreaddata']) {
				C::t('forum_thread')->update($this->thread['tid'], $this->param['updatethreaddata'], false, false, 0, true);
			}




			return 'post_reply_succeed';
		}
	}

	public function replyfeed() {
		if(!$this->feed) {
			if($this->forum['allowfeed'] && !$this->param['isanonymous']) {
				if($this->thread['authorid'] != $this->member['uid']) {
					$post_url = "forum.php?mod=redirect&goto=findpost&pid=".$this->pid."&ptid=".$this->thread['tid'];

					$this->feed['icon'] = 'post';
					$this->feed['title_template'] = !empty($this->thread['author']) ? 'feed_reply_title' : 'feed_reply_title_anonymous';
					$this->feed['title_data'] = array(
						'subject' => "<a href=\"$post_url\">".$this->thread['subject']."</a>",
						'author' => "<a href=\"home.php?mod=space&uid=".$this->thread['authorid']."\">".$this->thread['author']."</a>"
					);
					$forum_attachexist = getglobal('forum_attachexist');
					if(!empty($forum_attachexist)) {
						$imgattach = C::t('forum_attachment_n')->fetch_max_image('tid:'.$this->thread['tid'], 'pid', $this->pid);
						$firstaid = $imgattach['aid'];
						unset($imgattach);
						if($firstaid) {
							$this->feed['images'] = array(getforumimg($firstaid));
							$this->feed['image_links'] = array($post_url);
						}
					}
				}
			}
		}

		$this->feed['title_data']['hash_data'] = "tid".$this->thread['tid'];
		$this->feed['id'] = $this->pid;
		$this->feed['idtype'] = 'pid';
		if($this->feed['icon']) {
			postfeed($this->feed);
		}
	}

	public function thread($name = null, $val = null) {
		if(isset($val)) {
			return $this->setvar($this->thread, $name, $val);
		} else {
			return $this->getvar($this->thread, $name);
		}
	}

	public function forum($name = null, $val = null) {
		if(isset($val)) {
			return $this->setvar($this->forum, $name, $val);
		} else {
			return $this->getvar($this->forum, $name);
		}
	}

	public function editpost($parameters) {

		$this->_init_parameters($parameters);
		$isfirstpost = $this->post['first'] ? 1 : 0;
		$isorigauthor = $this->member['uid'] && $this->member['uid'] == $this->post['authorid'];
		$this->param['audit'] = $this->post['invisible'] == -2 || $this->thread['displayorder'] == -2 ? $this->param['audit'] : 0;

		list($this->param['modnewthreads'], $this->param['modnewreplies']) = threadmodstatus($this->param['subject']."\t".$this->param['message'].$this->param['extramessage']);

		if($post_invalid = checkpost($this->param['subject'], $this->param['message'], $isfirstpost && ($this->param['special'] || $this->param['sortid']))) {
			showmessage($post_invalid, '', array('minpostsize' => $this->setting['minpostsize'], 'maxpostsize' => $this->setting['maxpostsize']));
		}
		if(!$isorigauthor && !$this->group['allowanonymous']) {
			if($this->post['anonymous'] && !$this->param['isanonymous']) {
				$this->param['isanonymous'] = 0;
				$this->param['threadupdatearr']['author'] = $this->post['author'];
				$anonymousadd = 0;
			} else {
				$this->param['isanonymous'] = $this->post['anonymous'];
				$anonymousadd = '';
			}
		} else {
			$this->param['threadupdatearr']['author'] = $this->param['isanonymous'] ? '' : $this->post['author'];
			$anonymousadd = $this->param['isanonymous'];
		}

		if($isfirstpost) {
			if(trim($this->param['subject']) == '' && $this->thread['special'] != 2) {
				showmessage('post_sm_isnull');
			}

			if(!$this->param['sortid'] && !$this->thread['special'] && trim($this->param['message']) == '') {
				showmessage('post_sm_isnull');
			}


			$publishdate = null;
			if ($this->group['allowsetpublishdate'] && $this->thread['displayorder'] == -4) {
				$cron_publish_ids = dunserialize($this->cache('cronpublish'));
				if (!$this->param['cronpublish'] && in_array($this->thread['tid'], $cron_publish_ids) || $this->param['modnewthreads']) {
					$this->param['threadupdatearr']['dateline'] = $publishdate = TIMESTAMP;
					unset($cron_publish_ids[$this->thread['tid']]);
					$cron_publish_ids = serialize($cron_publish_ids);
					savecache('cronpublish', $cron_publish_ids);
				} elseif ($this->param['cronpublish'] && $this->param['cronpublishdate']) {
					$this->param['threadupdatearr']['dateline'] = $publishdate = strtotime($this->param['cronpublishdate']);
					$this->param['save'] = 1;
					if (!in_array($this->thread['tid'], $cron_publish_ids)) {
						$cron_publish_ids[$this->thread['tid']] = $this->thread['tid'];
						$cron_publish_ids = serialize($cron_publish_ids);
						savecache('cronpublish', $cron_publish_ids);
					}
				}
			}



			$this->param['readperm'] = $this->group['allowsetreadperm'] ? intval($this->param['readperm']) : ($isorigauthor ? $this->thread['readperm'] : 'ignore');
			if($this->thread['special'] != 3) {
				$this->param['price'] = intval($this->param['price']);
				$this->param['price'] = $this->thread['price'] < 0 && !$this->thread['special']
					?($isorigauthor || !$this->param['price'] ? -1 : $this->param['price'])
					:($this->group['maxprice'] ? ($this->param['price'] <= $this->group['maxprice'] ? ($this->param['price'] > 0 ? $this->param['price'] : 0) : $this->group['maxprice']) : ($isorigauthor ? $this->param['price'] : $this->thread['price']));

				if($this->param['price'] > 0 && floor($this->param['price'] * (1 - $this->setting['creditstax'])) == 0) {
					return $this->showmessage('post_net_price_iszero');
				}
			}

			$this->thread['status'] = setstatus(4, $this->param['ordertype'], $this->thread['status']);
			$this->thread['status'] = setstatus(15, $this->param['imgcontent'], $this->thread['status']);
			if($this->param['imgcontent']) {
				stringtopic($this->param['message'], $this->post['tid'], true, $this->param['imgcontentwidth']);
			}

			$this->thread['status'] = setstatus(2, $this->param['hiddenreplies'], $this->thread['status']);

			$this->thread['status'] = setstatus(6, $this->param['allownoticeauthor'] ? 1 : 0, $this->thread['status']);

			$displayorder = (empty($this->param['save']) || $this->thread['displayorder'] != -4 ) ? ($this->thread['displayorder'] == -4 ? -4 : $this->thread['displayorder']) : -4;
			$this->param['typeid'] = isset($this->param['typeid']) && isset($this->forum['threadtypes']['types'][$this->param['typeid']]) && (!$this->forum['threadtypes']['moderators'][$this->param['typeid']] || $this->forum['ismoderator']) ? $this->param['typeid'] : 0;
			$this->param['sortid'] = $this->param['special'] || !$this->forum['threadsorts']['types'][$this->param['sortid']] ? 0 : $this->param['sortid'];


			$this->param['threadupdatearr']['typeid'] = $this->param['typeid'];
			$this->param['threadupdatearr']['sortid'] = $this->param['sortid'];
			$this->param['threadupdatearr']['subject'] = $this->param['subject'];
			if($this->param['readperm'] !== 'ignore') {
				$this->param['threadupdatearr']['readperm'] = $this->param['readperm'];
			}
			$this->param['threadupdatearr']['price'] = $this->param['price'];
			$this->param['threadupdatearr']['status'] = $this->thread['status'];
			if(getglobal('forum_auditstatuson') && $this->param['audit'] == 1) {
				$this->param['threadupdatearr']['displayorder'] = 0;
				$this->param['threadupdatearr']['moderated'] = 1;
			} else {
				$this->param['threadupdatearr']['displayorder'] = $displayorder;
			}
			C::t('forum_thread')->update($this->thread['tid'], $this->param['threadupdatearr'], true);

			if($this->thread['tid'] > 1) {
				if($this->thread['closed'] > 1) {
					C::t('forum_thread')->update($this->thread['closed'], array('subject' => $this->param['subject']), true);
				} elseif(empty($this->thread['isgroup'])) {
					$threadclosed = C::t('forum_threadclosed')->fetch($this->thread['tid']);
					if($threadclosed['redirect']) {
						C::t('forum_thread')->update($threadclosed['redirect'], array('subject' => $this->param['subject']), true);
					}
				}
			}
			$class_tag = new tag();
			$tagstr = $class_tag->update_field($this->param['tags'], $this->thread['tid'], 'tid', $this->thread);

		} else {
			if($this->param['subject'] == '' && $this->param['message'] == '' && $this->thread['special'] != 2) {
				showmessage('post_sm_isnull');
			}
		}


		$this->param['htmlon'] = $this->group['allowhtml'] && !empty($this->param['htmlon']) ? 1 : 0;

		if($this->setting['editedby'] && (TIMESTAMP - $this->post['dateline']) > 60 && $this->member['adminid'] != 1) {
			$editor = $this->param['isanonymous'] && $isorigauthor ? lang('forum/misc', 'anonymous') : $this->member['username'];
			$edittime = dgmdate(TIMESTAMP);
			$this->param['message'] = lang('forum/misc', $this->param['htmlon'] ? 'post_edithtml' : (!$this->forum['allowbbcode'] || $this->param['bbcodeoff'] ? 'post_editnobbcode' : 'post_edit'), array('editor' => $editor, 'edittime' => $edittime)) . $this->param['message'];
		}


		$this->param['bbcodeoff'] = checkbbcodes($this->param['message'], !empty($this->param['bbcodeoff']));
		$this->param['smileyoff'] = checksmilies($this->param['message'], !empty($this->param['smileyoff']));
		$tagoff = $isfirstpost ? !empty($tagoff) : 0;


		if(getglobal('forum_auditstatuson') && $this->param['audit'] == 1) {
			if(getstatus($this->post['status'], 3) == 0) {
				C::t('forum_post')->update($this->thread['posttableid'], $this->post['pid'], array('status' => 4), false, false, null, -2, null, 0);
				updatepostcredits('+', $this->post['authorid'], ($isfirstpost ? 'post' : 'reply'), $this->forum['fid']);
			}
			if(!$isfirstpost) {
				C::t('forum_thread')->increase($this->thread['tid'], array('replies' => 1));
			}
			updatemodworks('MOD', 1);
			updatemodlog($this->thread['tid'], 'MOD');
		}

		$displayorder = $pinvisible = 0;
		if($isfirstpost) {
			$displayorder = $this->param['modnewthreads'] ? -2 : $this->thread['displayorder'];
			$pinvisible = $this->param['modnewthreads'] ? -2 : (empty($this->param['save']) ? 0 : -3);
		} else {
			$pinvisible = $this->param['modnewreplies'] ? -2 : ($this->thread['displayorder'] == -4 ? -3 : 0);
		}

		$this->param['message'] = preg_replace('/\[attachimg\](\d+)\[\/attachimg\]/is', '[attach]\1[/attach]', $this->param['message']);
		$this->param['parseurloff'] = !empty($this->param['parseurloff']);
		$setarr = array(
			'message' => $this->param['message'],
			'usesig' => $this->param['usesig'],
			'htmlon' => $this->param['htmlon'],
			'bbcodeoff' => $this->param['bbcodeoff'],
			'parseurloff' => $this->param['parseurloff'],
			'smileyoff' => $this->param['smileyoff'],
			'subject' => $this->param['subject'],
			'tags' => $tagstr,
			'port'=>getglobal('remoteport')
		);

		$setarr['status'] = $this->post['status'];
		if($this->param['modstatus']) {
			foreach($this->param['modstatus'] as $modbit => $modvalue) {
				$setarr['status'] = setstatus($modbit, $modvalue, $setarr['status']);
			}
		}

		if($anonymousadd !== '') {
			$setarr['anonymous'] = $anonymousadd;
		}
		if($publishdate) {
			$setarr['dateline'] = $publishdate;
		}
		if(getglobal('forum_auditstatuson') && $this->param['audit'] == 1) {
			$setarr['invisible'] = 0;
		} else {
			$setarr['invisible'] = $pinvisible;
		}
		C::t('forum_post')->update('tid:'.$this->thread['tid'], $this->post['pid'], $setarr);



		$this->forum['lastpost'] = explode("\t", $this->forum['lastpost']);

		if($this->post['dateline'] == $this->forum['lastpost'][2] && ($this->post['author'] == $this->forum['lastpost'][3] || ($this->forum['lastpost'][3] == '' && $this->post['anonymous']))) {
			$lastpost = $this->thread['tid']."\t".($isfirstpost ? $this->param['subject'] : $this->thread['subject'])."\t".$this->post['dateline']."\t".($this->param['isanonymous'] ? '' : $this->post['author']);
			C::t('forum_forum')->update($this->forum['fid'], array('lastpost' => $lastpost));

		}

		if(!getglobal('forum_auditstatuson') || $this->param['audit'] != 1) {
			if($isfirstpost && $this->param['modnewthreads']) {
				C::t('forum_thread')->update($this->thread['tid'], array('displayorder' => -2));
				manage_addnotify('verifythread');
			} elseif(!$isfirstpost && $this->param['modnewreplies']) {
				C::t('forum_thread')->increase($this->thread['tid'], array('replies' => -1));
				manage_addnotify('verifypost');
			}
			if($this->param['modnewreplies'] || $this->param['modnewthreads']) {
				C::t('forum_forum')->update($this->forum['fid'], array('modworks' => '1'));
			}
		}


		if($this->thread['lastpost'] == $this->post['dateline'] && ((!$this->post['anonymous'] && $this->thread['lastposter'] == $this->post['author']) || ($this->post['anonymous'] && $this->thread['lastposter'] == '')) && $this->post['anonymous'] != $this->param['isanonymous']) {
			C::t('forum_thread')->update($this->thread['tid'], array('lastposter' => $this->param['isanonymous'] ? '' : $this->post['author']), true);
		}

		if(!$isorigauthor) {
			updatemodworks('EDT', 1);
			require_once libfile('function/misc');
			modlog($this->thread, 'EDT');
		}

		if($isfirstpost && $this->thread['displayorder'] == -4 && empty($this->param['save'])) {
			threadpubsave($this->thread['tid']);
		}
	}

	public function deletepost($parameters) {

		$this->_init_parameters($parameters);
		if(!$this->setting['editperdel']) {
			return $this->showmessage('post_edit_thread_ban_del', NULL);
		}

		$isfirstpost = $this->post['first'] ? 1 : 0;

		if($isfirstpost && $this->thread['replies'] > 0) {
			return $this->showmessage(($this->thread['special'] == 3 ? 'post_edit_reward_already_reply' : 'post_edit_thread_already_reply'), NULL);
		}


		if($this->thread['displayorder'] >= 0) {
			updatepostcredits('-', $this->post['authorid'], ($isfirstpost ? 'post' : 'reply'), $this->forum['fid']);
		}


		if(!$this->param['handlereplycredit']) {
			if(!$isfirstpost && !$this->param['isanonymous']) {
				$postreplycredit = C::t('forum_post')->fetch('tid:'.$this->thread['tid'], $this->post['pid']);
				$postreplycredit = $postreplycredit['replycredit'];
				if($postreplycredit) {
					C::t('forum_post')->update('tid:'.$this->thread['tid'], $this->post['pid'], array('replycredit' => 0));
					updatemembercount($this->post['authorid'], array($replycredit_rule['extcreditstype'] => '-'.$postreplycredit));
				}
			}
		}


		C::t('forum_post')->delete('tid:'.$this->thread['tid'], $this->post['pid']);


		$forumcounter = array();
		if($isfirstpost) {
			$forumcounter['threads'] = $forumcounter['posts'] = -1;
			$tablearray = array('forum_relatedthread', 'forum_debate', 'forum_debatepost', 'forum_polloption', 'forum_poll');
			foreach ($tablearray as $table) {
				C::t($table)->delete_by_tid($this->thread['tid']);
			}
			C::t('forum_thread')->delete_by_tid($this->thread['tid']);
			C::t('common_moderate')->delete($this->thread['tid'], 'tid');
			C::t('forum_threadmod')->delete_by_tid($this->thread['tid']);
			if($this->setting['globalstick'] && in_array($this->thread['displayorder'], array(2, 3))) {
				require_once libfile('function/cache');
				updatecache('globalstick');
			}
		} else {
			$forumcounter['posts'] = -1;
			$lastpost = C::t('forum_post')->fetch_visiblepost_by_tid('tid:'.$this->thread['tid'], $this->thread['tid'], 0, 1);
			$lastpost['author'] = !$lastpost['anonymous'] ? addslashes($lastpost['author']) : '';

			$this->param['updatefieldarr']['replies'] = -1;
			$this->param['updatefieldarr']['lastposter'] = array($lastpost['author']);
			$this->param['updatefieldarr']['lastpost'] = array($lastpost['dateline']);

			C::t('forum_thread')->increase($this->thread['tid'], $this->param['updatefieldarr']);
		}

		$this->forum['lastpost'] = explode("\t", $this->forum['lastpost']);
		if($this->post['dateline'] == $this->forum['lastpost'][2] && ($this->post['author'] == $this->forum['lastpost'][3] || ($this->forum['lastpost'][3] == '' && $this->post['anonymous']))) {
			$lastthread = C::t('forum_thread')->fetch_by_fid_displayorder($this->forum['fid']);
			C::t('forum_forum')->update($this->forum['fid'], array('lastpost' => "$lastthread[tid]\t$lastthread[subject]\t$lastthread[lastpost]\t$lastthread[lastposter]"));
		}
		C::t('forum_forum')->update_forum_counter($this->forum['fid'], $forumcounter['threads'], $forumcounter['posts']);

	}

}
?>