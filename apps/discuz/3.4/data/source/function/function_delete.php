<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_delete.php 36334 2017-01-03 01:32:35Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/home');

function deletemember($uids, $delpost = true) {
	global $_G;
	if(!$uids) {
		return;
	}
	if($_G['setting']['plugins']['func'][HOOKTYPE]['deletemember']) {
		$_G['deleteposuids'] = & $uids;
		$hookparam = func_get_args();
		hookscript('deletemember', 'global', 'funcs', array('param' => $hookparam, 'step' => 'check'), 'deletemember');
	}
	if($delpost) {
		deleteattach($uids, 'uid');
		deletepost($uids, 'authorid');
	}

	$arruids = $uids;
	$uids = dimplode($uids);
	$numdeleted = count($arruids);
	foreach(array('common_member_field_forum', 'common_member_field_home', 'common_member_count',
		'common_member_profile', 'common_member_status',) as $table) {
		C::t($table)->delete($arruids, true, 1);
	}

	foreach(array( 'common_member_log', 'common_member_verify', 'common_member_validate', 'common_member_magic') as $table) {
		C::t($table)->delete($arruids, true);
	}

	C::t('forum_access')->delete_by_uid($arruids);
	C::t('common_member_verify_info')->delete_by_uid($arruids);
	C::t('common_member_action_log')->delete_by_uid($arruids);
	C::t('forum_moderator')->delete_by_uid($arruids);
	C::t('forum_post_location')->delete_by_uid($arruids);
	$doids = array();
	$query = C::t('home_doing')->fetch_all_by_uid_doid($arruids);
	foreach($query as $value) {
		$doids[$value['doid']] = $value['doid'];
	}

	C::t('home_docomment')->delete_by_doid_uid($doids, $arruids);
	C::t('common_domain')->delete_by_id_idtype($arruids, 'home');
	C::t('home_feed')->delete_by_uid($arruids);
	C::t('home_notification')->delete_by_uid($arruids);
	C::t('home_poke')->delete_by_uid_or_fromuid($uids);
	C::t('home_comment')->delete_by_uid($arruids);
	C::t('home_visitor')->delete_by_uid_or_vuid($uids);
	C::t('home_friend')->delete_by_uid_fuid($arruids);
	C::t('home_friend_request')->delete_by_uid_or_fuid($arruids);
	C::t('common_invite')->delete_by_uid_or_fuid($arruids);
	C::t('common_myinvite')->delete_by_touid_or_fromuid($uids);
	C::t('common_moderate')->delete($arruids, 'uid_cid');
	C::t('common_member_forum_buylog')->delete_by_uid($arruids);
	C::t('forum_threadhidelog')->delete_by_uid($arruids);
	C::t('common_member_crime')->delete_by_uid($arruids);
	C::t('home_follow')->delete_by_uid($arruids);
	C::t('home_follow')->delete_by_followuid($arruids);
	C::t('home_follow_feed')->delete_by_uid($arruids);

	foreach(C::t('forum_collectionfollow')->fetch_all_by_uid($arruids) as $follow) {
		C::t('forum_collection')->update_by_ctid($follow['ctid'], 0, -1);
	}

	foreach(C::t('forum_collectioncomment')->fetch_all_by_uid($arruids) as $comment) {
		C::t('forum_collection')->update_by_ctid($comment['ctid'], 0, 0, -1);
	}

	$query = C::t('home_pic')->fetch_all_by_uid($uids);
	foreach($query as $value) {
		$pics[] = $value;
	}
	deletepicfiles($pics);

	include_once libfile('function/home');
	$query = C::t('home_album')->fetch_all_by_uid($arruids);
	foreach($query as $value) {
		pic_delete($value['pic'], 'album', 0, ($value['picflag'] == 2 ? 1 : 0));
	}

	C::t('common_mailcron')->delete_by_touid($arruids);

	foreach(array('home_doing', 'home_share', 'home_album', 'common_credit_rule_log', 'common_credit_rule_log_field',
		'home_pic', 'home_blog', 'home_blogfield', 'home_class', 'home_clickuser',
		'home_show', 'forum_collectioncomment', 'forum_collectionfollow', 'forum_collectionteamworker') as $table) {
		C::t($table)->delete_by_uid($arruids);
	}
	C::t('common_member')->delete($arruids, 1, 1);

	manyoulog('user', $uids, 'delete');
	if($_G['setting']['plugins']['func'][HOOKTYPE]['deletemember']) {
		hookscript('deletemember', 'global', 'funcs', array('param' => $hookparam, 'step' => 'delete'), 'deletemember');
	}
	return $numdeleted;
}

function deletepost($ids, $idtype = 'pid', $credit = false, $posttableid = false, $recycle = false) {
	global $_G;
	$recycle = $recycle && $idtype == 'pid' ? true : false;
	if($_G['setting']['plugins']['func'][HOOKTYPE]['deletepost']) {
		$_G['deletepostids'] = & $ids;
		$hookparam = func_get_args();
		hookscript('deletepost', 'global', 'funcs', array('param' => $hookparam, 'step' => 'check'), 'deletepost');
	}
	if(!$ids || !in_array($idtype, array('authorid', 'tid', 'pid'))) {
		return 0;
	}

	loadcache('posttableids');
	$posttableids = !empty($_G['cache']['posttableids']) ? ($posttableid !== false && in_array($posttableid, $_G['cache']['posttableids']) ? array($posttableid) : $_G['cache']['posttableids']): array('0');

	$count = count($ids);
	$idsstr = dimplode($ids);

	if($credit) {
		$replycredit_list = $tuidarray = $ruidarray = $_G['deleteauthorids'] = array();
		foreach($posttableids as $id) {
			$postlist = array();
			if($idtype == 'pid') {
				$postlist = C::t('forum_post')->fetch_all($id, $ids, false);
			} elseif($idtype == 'tid') {
				$postlist = C::t('forum_post')->fetch_all_by_tid($id, $ids, false);
			} elseif($idtype == 'authorid') {
				$postlist = C::t('forum_post')->fetch_all_by_authorid($id, $ids, false);
			}
			foreach($postlist as $post) {
				if($post['invisible'] != -1 && $post['invisible'] != -5) {
					if($post['first']) {
						$tuidarray[$post['fid']][] = $post['authorid'];
					} else {
						$ruidarray[$post['fid']][] = $post['authorid'];
						if($post['authorid'] > 0 && $post['replycredit'] > 0) {
							$replycredit_list[$post['authorid']][$post['tid']] += $post['replycredit'];
						}
					}
					$tids[$post['tid']] = $post['tid'];
					$_G['deleteauthorids'][$post['authorid']] = $post['authorid'];
				}
			}
			unset($postlist);
		}

		if($tuidarray || $ruidarray) {
			require_once libfile('function/post');
		}
		if($tuidarray) {
			foreach($tuidarray as $fid => $tuids) {
				updatepostcredits('-', $tuids, 'post', $fid);
			}
		}
		if($ruidarray) {
			foreach($ruidarray as $fid => $ruids) {
				updatepostcredits('-', $ruids, 'reply', $fid);
			}
		}
	}

	foreach($posttableids as $id) {
		if($recycle) {
			C::t('forum_post')->update($id, $ids, array('invisible' => -5));
		} else {
			if($idtype == 'pid') {
				C::t('forum_post')->delete($id, $ids);
				C::t('forum_postcomment')->delete_by_pid($ids);
				C::t('forum_postcomment')->delete_by_rpid($ids);
			} elseif($idtype == 'tid') {
				C::t('forum_post')->delete_by_tid($id, $ids);
				C::t('forum_postcomment')->delete_by_tid($ids);
			} elseif($idtype == 'authorid') {
				C::t('forum_post')->delete_by_authorid($id, $ids);
				C::t('forum_postcomment')->delete_by_authorid($ids);
			}
			C::t('forum_trade')->delete_by_id_idtype($ids, ($idtype == 'authorid' ? 'sellerid' : $idtype));
			C::t('home_feed')->delete_by_id_idtype($ids, ($idtype == 'authorid' ? 'uid' : $idtype));
		}
	}
	if(!$recycle && $idtype != 'authorid') {
		if($idtype == 'pid') {
			C::t('forum_poststick')->delete_by_pid($ids);
		} elseif($idtype == 'tid') {
			C::t('forum_poststick')->delete_by_tid($ids);
		}

	}
	if($idtype == 'pid') {
		C::t('forum_postcomment')->delete_by_rpid($ids);
		C::t('common_moderate')->delete($ids, 'pid');
		C::t('forum_post_location')->delete($ids);
		C::t('forum_filter_post')->delete_by_pid($ids);
		C::t('forum_hotreply_number')->delete_by_pid($ids);
		C::t('forum_hotreply_member')->delete_by_pid($ids);
	} elseif($idtype == 'tid') {
		C::t('forum_post_location')->delete_by_tid($ids);
		C::t('forum_filter_post')->delete_by_tid($ids);
		C::t('forum_hotreply_number')->delete_by_tid($ids);
		C::t('forum_hotreply_member')->delete_by_tid($ids);
		C::t('forum_sofa')->delete($ids);
	} elseif($idtype == 'authorid') {
		C::t('forum_post_location')->delete_by_uid($ids);
	}
	if($replycredit_list) {
		foreach(C::t('forum_replycredit')->fetch_all($tids) as $rule) {
			$rule['extcreditstype'] = $rule['extcreditstype'] ? $rule['extcreditstype'] : $_G['setting']['creditstransextra'][10] ;
			$replycredity_rule[$rule['tid']] = $rule;
		}
		foreach($replycredit_list AS $uid => $tid_credit) {
			foreach($tid_credit AS $tid => $credit) {
				$uid_credit[$replycredity_rule[$tid]['extcreditstype']] -= $credit;
			}
			updatemembercount($uid, $uid_credit, true);
		}
	}
	if(!$recycle) {
		deleteattach($ids, $idtype);
	}
	if($tids) {
	    foreach($tids as $tid) {
	        updatethreadcount($tid, 1);
	    }
	}
	if($_G['setting']['plugins']['func'][HOOKTYPE]['deletepost']) {
		hookscript('deletepost', 'global', 'funcs', array('param' => $hookparam, 'step' => 'delete'), 'deletepost');
	}
	return $count;
}

function deletethreadcover($tids) {
	global $_G;
	loadcache(array('threadtableids', 'posttableids'));
	$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : array(0);
	$deletecover = array();
	foreach($threadtableids as $tableid) {
		foreach(C::t('forum_thread')->fetch_all_by_tid($tids, 0, 0, $tableid) as $row) {
			if($row['cover']) {
				$deletecover[$row['tid']] = $row['cover'];
			}
		}
	}
	if($deletecover) {
		foreach($deletecover as $tid => $cover) {
			$filename = getthreadcover($tid, 0, 1);
			$remote = $cover < 0 ? 1 : 0;
			dunlink(array('attachment' => $filename, 'remote' => $remote, 'thumb' => 0));
		}
	}
}

function deletethread($tids, $membercount = false, $credit = false, $ponly = false) {
	global $_G;
	if($_G['setting']['plugins']['func'][HOOKTYPE]['deletethread']) {
		$_G['deletethreadtids'] = & $tids;
		$hookparam = func_get_args();
		hookscript('deletethread', 'global', 'funcs', array('param' => $hookparam, 'step' => 'check'), 'deletethread');
	}
	if(!$tids) {
		return 0;
	}

	$count = count($tids);
	$arrtids = $tids;
	$tids = dimplode($tids);

	loadcache(array('threadtableids', 'posttableids'));
	$threadtableids = !empty($_G['cache']['threadtableids']) ? $_G['cache']['threadtableids'] : array();
	$posttableids = !empty($_G['cache']['posttableids']) ? $_G['cache']['posttableids'] : array('0');
	if(!in_array(0, $threadtableids)) {
		$threadtableids = array_merge(array(0), $threadtableids);
	}

	C::t('common_moderate')->delete($arrtids, 'tid');
	C::t('forum_threadclosed')->delete($arrtids);
	C::t('forum_newthread')->delete_by_tids($arrtids);

	$cachefids = $atids = $fids = $postids = $threadtables = $_G['deleteauthorids'] = array();
	foreach($threadtableids as $tableid) {
		foreach(C::t('forum_thread')->fetch_all_by_tid($arrtids, 0, 0, $tableid) as $row) {
			$atids[] = $row['tid'];
			$row['posttableid'] = !empty($row['posttableid']) && in_array($row['posttableid'], $posttableids) ? $row['posttableid'] : '0';
			$postids[$row['posttableid']][$row['tid']] = $row['tid'];
			if($tableid) {
				$fids[$row['fid']][] = $tableid;
			}
			$cachefids[$row['fid']] = $row['fid'];
			$_G['deleteauthorids'][$row['authorid']] = $row['authorid'];
		}
		if(!$tableid && !$ponly) {
			$threadtables[] = $tableid;
		}
	}

	if($credit || $membercount) {
		$losslessdel = $_G['setting']['losslessdel'] > 0 ? TIMESTAMP - $_G['setting']['losslessdel'] * 86400 : 0;

		$postlist = $uidarray = $tuidarray = $ruidarray = array();
		foreach($postids as $posttableid => $posttabletids) {
			foreach(C::t('forum_post')->fetch_all_by_tid($posttableid, $posttabletids, false) as $post) {
				if($post['invisible'] != -1 && $post['invisible'] != -5) {
					$postlist[] = $post;
				}
			}
		}
		foreach(C::t('forum_replycredit')->fetch_all($arrtids) as $rule) {
			$rule['extcreditstype'] = $rule['extcreditstype'] ? $rule['extcreditstype'] : $_G['setting']['creditstransextra'][10] ;
			$replycredit_rule[$rule['tid']] = $rule;
		}

		foreach($postlist as $post) {
			if($post['dateline'] < $losslessdel) {
				if($membercount) {
					if($post['first']) {
						updatemembercount($post['authorid'], array('threads' => -1, 'post' => -1), false);
					} else {
						updatemembercount($post['authorid'], array('posts' => -1), false);
					}
				}
			} else {
				if($credit) {
					if($post['first']) {
						$tuidarray[$post['fid']][] = $post['authorid'];
					} else {
						$ruidarray[$post['fid']][] = $post['authorid'];
					}
				}
			}
			if($credit || $membercount) {
				if($post['authorid'] > 0 && $post['replycredit'] > 0) {
					if($replycredit_rule[$post['tid']]['extcreditstype']) {
						updatemembercount($post['authorid'], array($replycredit_rule[$post['tid']]['extcreditstype'] => (int)('-'.$post['replycredit'])));
					}
				}
			}
		}

		if($credit) {
			if($tuidarray || $ruidarray) {
				require_once libfile('function/post');
			}
			if($tuidarray) {
				foreach($tuidarray as $fid => $tuids) {
					updatepostcredits('-', $tuids, 'post', $fid);
				}
			}
			if($ruidarray) {
				foreach($ruidarray as $fid => $ruids) {
					updatepostcredits('-', $ruids, 'reply', $fid);
				}
			}
			$auidarray = $attachtables = array();
			foreach($atids as $tid) {
				$attachtables[getattachtableid($tid)][] = $tid;
			}
			foreach($attachtables as $attachtable => $attachtids) {
				foreach(C::t('forum_attachment_n')->fetch_all_by_id($attachtable, 'tid', $attachtids) as $attach) {
					if($attach['dateline'] > $losslessdel) {
						$auidarray[$attach['uid']] = !empty($auidarray[$attach['uid']]) ? $auidarray[$attach['uid']] + 1 : 1;
					}
				}
			}
			if($auidarray) {
				$postattachcredits = !empty($_G['forum']['postattachcredits']) ? $_G['forum']['postattachcredits'] : $_G['setting']['creditspolicy']['postattach'];
				updateattachcredits('-', $auidarray, $postattachcredits);
			}
		}
	}

	$relatecollection = C::t('forum_collectionthread')->fetch_all_by_tids($arrtids);
	if(count($relatecollection) > 0) {
		$collectionids = array();
		foreach($relatecollection as $collection) {
			$collectionids[] = $collection['ctid'];
		}
		$collectioninfo = C::t('forum_collection')->fetch_all($collectionids);
		foreach($relatecollection as $collection) {
			$decthread = C::t('forum_collectionthread')->delete_by_ctid_tid($collection['ctid'], $arrtids);
			$lastpost = null;
			if(in_array($collectioninfo[$collection['ctid']]['lastpost'], $arrtids) && ($collectioninfo[$collection['ctid']]['threadnum'] - $decthread) > 0) {
				$collection_thread = C::t('forum_collectionthread')->fetch_by_ctid_dateline($collection['ctid']);
				if($collection_thread) {
					$thread = C::t('forum_thread')->fetch($collection_thread['tid']);
					$lastpost = array(
						'lastpost' => $thread['tid'],
						'lastsubject' => $thread['subject'],
						'lastposttime' => $thread['dateline'],
						'lastposter' => $thread['authorid']
					);
				}
			}
			C::t('forum_collection')->update_by_ctid($collection['ctid'], -$decthread, 0, 0, 0, 0, 0, $lastpost);
		}
		C::t('forum_collectionrelated')->delete($arrtids);
	}
	if($cachefids) {
		C::t('forum_thread')->clear_cache($cachefids, 'forumdisplay_');
	}
	if($ponly) {
		if($_G['setting']['plugins']['func'][HOOKTYPE]['deletethread']) {
			hookscript('deletethread', 'global', 'funcs', array('param' => $hookparam, 'step' => 'delete'), 'deletethread');
		}
		C::t('forum_thread')->update($arrtids, array('displayorder'=>-1, 'digest'=>0, 'moderated'=>1));
		foreach($postids as $posttableid=>$oneposttids) {
			C::t('forum_post')->update_by_tid($posttableid, $oneposttids, array('invisible' => '-1'));
		}
		return $count;
	}

	C::t('forum_replycredit')->delete($arrtids);
	C::t('forum_post_location')->delete_by_tid($arrtids);
	C::t('common_credit_log')->delete_by_operation_relatedid(array('RCT', 'RCA', 'RCB'), $arrtids);
	C::t('forum_threadhidelog')->delete_by_tid($arrtids);
	deletethreadcover($arrtids);
	foreach($threadtables as $tableid) {
		C::t('forum_thread')->delete_by_tid($arrtids, false, $tableid);
	}

	if($atids) {
		foreach($postids as $posttableid=>$oneposttids) {
			deletepost($oneposttids, 'tid', false, $posttableid);
		}
		deleteattach($atids, 'tid');
	}

	if($fids) {
		loadcache('forums');
		foreach($fids as $fid => $tableids) {
			if(empty($_G['cache']['forums'][$fid]['archive'])) {
				continue;
			}
			foreach(C::t('forum_thread')->count_posts_by_fid($fid) as $row) {
				C::t('forum_forum_threadtable')->insert(array(
						'fid' => $fid,
						'threadtableid' => $tableid,
						'threads' => $row['threads'],
						'posts' => $row['posts']
				), false, true);
			}
		}
	}

	foreach(array('forum_forumrecommend', 'forum_polloption', 'forum_poll', 'forum_polloption_image', 'forum_activity', 'forum_activityapply', 'forum_debate',
		'forum_debatepost', 'forum_threadmod', 'forum_relatedthread',
		'forum_pollvoter', 'forum_threadimage', 'forum_threadpreview') as $table) {
		C::t($table)->delete_by_tid($arrtids);
	}
	C::t('forum_typeoptionvar')->delete_by_tid($arrtids);
	C::t('forum_poststick')->delete_by_tid($arrtids);
	C::t('forum_filter_post')->delete_by_tid($arrtids);
	C::t('forum_hotreply_member')->delete_by_tid($arrtids);
	C::t('forum_hotreply_number')->delete_by_tid($arrtids);
	C::t('home_feed')->delete_by_id_idtype($arrtids, 'tid');
	C::t('common_tagitem')->delete(0, $arrtids, 'tid');
	C::t('forum_threadrush')->delete($arrtids);
	if($_G['setting']['plugins']['func'][HOOKTYPE]['deletethread']) {
		hookscript('deletethread', 'global', 'funcs', array('param' => $hookparam, 'step' => 'delete'), 'deletethread');
	}
	return $count;
}

function deleteattach($ids, $idtype = 'aid') {
	global $_G;
	if(!$ids || !in_array($idtype, array('authorid', 'uid', 'tid', 'pid'))) {
		return;
	}
	$idtype = $idtype == 'authorid' ? 'uid' : $idtype;

	$pics = $attachtables = array();

	if($idtype == 'tid') {
		$pollImags = C::t('forum_polloption_image')->fetch_all_by_tid($ids);
		foreach($pollImags as $image) {
			dunlink($image);
		}
	}
	foreach(C::t('forum_attachment')->fetch_all_by_id($idtype, $ids) as $attach) {
		$attachtables[$attach['tableid']][] = $attach['aid'];
	}

	foreach($attachtables as $attachtable => $aids) {
		if($attachtable == 127) {
			continue;
		}
		$attachs = C::t('forum_attachment_n')->fetch_all($attachtable, $aids);
		foreach($attachs as $attach) {
			if($attach['picid']) {
				$pics[] = $attach['picid'];
			}
			dunlink($attach);
		}
		C::t('forum_attachment_exif')->delete($aids);
		C::t('forum_attachment_n')->delete($attachtable, $aids);
	}
	C::t('forum_attachment')->delete_by_id($idtype, $ids);
	if($pics) {
		$albumids = array();
		C::t('home_pic')->delete($pics);
		$query = C::t('home_pic')->fetch_all($pics);
		foreach($query as $album) {
			if(!in_array($album['albumid'], $albumids)) {
				C::t('home_album')->update($album['albumid'], array('picnum' => C::t('home_pic')->check_albumpic($album['albumid'])));
				$albumids[] = $album['albumid'];
			}
		}
	}
}

function deletecomments($cids) {
	global $_G;

	$blognums = $newcids = $dels = $counts = array();
	$allowmanage = checkperm('managecomment');

	$query = C::t('home_comment')->fetch_all($cids);
	$deltypes = array();
	foreach($query as $value) {
		if($allowmanage || $value['authorid'] == $_G['uid'] || $value['uid'] == $_G['uid']) {
			$dels[] = $value;
			$newcids[] = $value['cid'];
			$deltypes[] = $value['idtype'].'_cid';
			if($value['authorid'] != $_G['uid'] && $value['uid'] != $_G['uid']) {
				$counts[$value['authorid']]['coef'] -= 1;
			}
			if($value['idtype'] == 'blogid') {
				$blognums[$value['id']]++;
			}
		}
	}

	if(empty($dels)) return array();

	C::t('home_comment')->delete($newcids);
	for($i = 0; $i < count($newcids); $i++) {
		C::t('common_moderate')->delete($newcids[$i], $deltypes[$i]);
	}

	if($counts) {
		foreach ($counts as $uid => $setarr) {
			batchupdatecredit('comment', $uid, array(), $setarr['coef']);
		}
	}
	if($blognums) {
		$nums = renum($blognums);
		foreach ($nums[0] as $num) {
			C::t('home_blog')->increase($nums[1][$num], 0, array('replynum' => -$num));
		}
	}
	return $dels;
}

function deleteblogs($blogids, $force = false) {
	global $_G;

	$blogs = $newblogids = $counts = array();
	$allowmanage = checkperm('manageblog');

	$query = C::t('home_blog')->fetch_all($blogids);
	foreach($query as $value) {
		if($allowmanage || $value['uid'] == $_G['uid']) {
			$blogs[] = $value;
			$newblogids[] = $value['blogid'];

			if($value['status'] == 0) {
				if($value['uid'] != $_G['uid']) {
					$counts[$value['uid']]['coef'] -= 1;
				}
				$counts[$value['uid']]['blogs'] -= 1;
			}
		}
	}
	if(empty($blogs)) return array();

	C::t('common_moderate')->delete($newblogids, 'blogid');
	C::t('common_moderate')->delete($newblogids, 'blogid_cid');

	if(getglobal('setting/blogrecyclebin') && !$force) {
		C::t('home_blog')->update($newblogids, array('status' => -1));
		return $blogs;
	}
	C::t('home_blog')->delete($newblogids);
	C::t('home_blogfield')->delete($newblogids);
	C::t('home_comment')->delete('', $newblogids, 'blogid');
	C::t('home_feed')->delete_by_id_idtype($newblogids, 'blogid');
	C::t('home_clickuser')->delete_by_id_idtype($newblogids, 'blogid');

	if($counts) {
		foreach ($counts as $uid => $setarr) {
			batchupdatecredit('publishblog', $uid, array('blogs' => $setarr['blogs']), $setarr['coef']);
		}
	}

	C::t('common_tagitem')->delete(0, $newblogids, 'blogid');

	return $blogs;
}

function deletefeeds($feedids) {
	global $_G;

	$allowmanage = checkperm('managefeed');

	$feeds = $newfeedids = array();
	$query = C::t('home_feed')->fetch_all($feedids);
	foreach($query as $value) {
		if($allowmanage || $value['uid'] == $_G['uid']) {
			$newfeedids[] = $value['feedid'];
			$feeds[] = $value;
		}
	}

	if(empty($newfeedids)) return array();

	C::t('home_feed')->delete($newfeedids);

	return $feeds;
}

function deleteshares($sids) {
	global $_G;

	$allowmanage = checkperm('manageshare');

	$shares = $newsids = $counts = array();
	foreach(C::t('home_share')->fetch_all($sids) as $value) {
		if($allowmanage || $value['uid'] == $_G['uid']) {
			$shares[] = $value;
			$newsids[] = $value['sid'];

			if($value['uid'] != $_G['uid']) {
				$counts[$value['uid']]['coef'] -= 1;
			}
			$counts[$value['uid']]['sharings'] -= 1;
		}
	}
	if(empty($shares)) return array();

	C::t('home_share')->delete($newsids);
	C::t('home_comment')->delete('', $newsids, 'sid');
	C::t('home_feed')->delete_by_id_idtype($newsids, 'sid');
	C::t('common_moderate')->delete($newsids, 'sid');
	C::t('common_moderate')->delete($newsids, 'sid_cid');

	if($counts) {
		foreach ($counts as $uid => $setarr) {
			batchupdatecredit('createshare', $uid, array('sharings' => $setarr['sharings']), $setarr['coef']);
		}
	}

	return $shares;
}

function deletedoings($ids) {
	global $_G;

	$allowmanage = checkperm('managedoing');

	$doings = $newdoids = $counts = array();
	$query = C::t('home_doing')->fetch_all($ids);
	foreach($query as $value) {
		if($allowmanage || $value['uid'] == $_G['uid']) {
			$doings[] = $value;
			$newdoids[] = $value['doid'];

			if($value['uid'] != $_G['uid']) {
				$counts[$value['uid']]['coef'] -= 1;
			}
			$counts[$value['uid']]['doings'] -= 1;
		}
	}

	if(empty($doings)) return array();

	C::t('home_doing')->delete($newdoids);
	C::t('home_docomment')->delete_by_doid_uid($newdoids);
	C::t('home_feed')->delete_by_id_idtype($newdoids, 'doid');
	C::t('common_moderate')->delete($newdoids, 'doid');

	if($counts) {
		foreach ($counts as $uid => $setarr) {
			if ($uid) {
				batchupdatecredit('doing', $uid, array('doings' => $setarr['doings']), $setarr['coef']);
				$lastdoing = C::t('home_doing')->fetch_all_by_uid_doid($uid, '', 'dateline', 0, 1, true, true);
				$setarr = array('recentnote'=>$lastdoing[0]['message'], 'spacenote'=>$lastdoing[0]['message']);
				C::t('common_member_field_home')->update($uid, $setarr);
			}
		}
	}

	return $doings;
}

function deletespace($uid) {
	global $_G;

	$allowmanage = checkperm('managedelspace');

	if($allowmanage) {
		C::t('common_member')->update($uid, array('status' => 1));
		manyoulog('user', $uid, 'delete');
		return true;
	} else {
		return false;
	}
}

function deletepics($picids) {
	global $_G;

	$albumids = $sizes = $pics = $newids = array();
	$allowmanage = checkperm('managealbum');

	$haveforumpic = false;
	$query = C::t('home_pic')->fetch_all($picids);
	foreach($query as $value) {
		if($allowmanage || $value['uid'] == $_G['uid']) {
			$pics[] = $value;
			$newids[] = $value['picid'];
			$sizes[$value['uid']] = $sizes[$value['uid']] + $value['size'];
			$albumids[$value['albumid']] = $value['albumid'];
			if(!$haveforumpic && $value['remote'] > 1) {
				$haveforumpic = true;
			}
		}
	}
	if(empty($pics)) return array();

	C::t('home_pic')->delete($newids);
	if($haveforumpic) {
		for($i = 0;$i < 10;$i++) {
			C::t('forum_attachment_n')->reset_picid($i, $newids);
		}
	}

	C::t('home_comment')->delete('', $newids, 'picid');
	C::t('home_feed')->delete_by_id_idtype($newids, 'picid');
	C::t('home_clickuser')->delete_by_id_idtype($newids, 'picid');
	C::t('common_moderate')->delete($newids, 'picid');
	C::t('common_moderate')->delete($newids, 'picid_cid');

	if($sizes) {
		foreach ($sizes as $uid => $setarr) {
			$attachsize = intval($sizes[$uid]);
			updatemembercount($uid, array('attachsize' => -$attachsize), false);
		}
	}

	require_once libfile('function/spacecp');
	foreach ($albumids as $albumid) {
		if($albumid) {
			album_update_pic($albumid);
		}
	}

	deletepicfiles($pics);

	return $pics;
}

function deletepicfiles($pics) {
	global $_G;
	$remotes = array();
	include_once libfile('function/home');
	foreach ($pics as $pic) {
		pic_delete($pic['filepath'], 'album', $pic['thumb'], $pic['remote']);
	}
}

function deletealbums($albumids) {
	global $_G;

	$sizes = $dels = $newids = $counts = array();
	$allowmanage = checkperm('managealbum');

	$albums = C::t('home_album')->fetch_all($albumids);
	foreach($albums as $value) {
		if($value['albumid']) {
			if($allowmanage || $value['uid'] == $_G['uid']) {
				$dels[] = $value;
				$newids[] = $value['albumid'];
				if(!empty($value['pic'])) {
					include_once libfile('function/home');
					pic_delete($value['pic'], 'album', 0, ($value['picflag'] == 2 ? 1 : 0));
				}
			}
			$counts[$value['uid']]['albums'] -= 1;
		}
	}

	if(empty($dels)) return array();

	$pics = $picids = array();
	$query = C::t('home_pic')->fetch_all_by_albumid($newids);
	foreach($query as $value) {
		$pics[] = $value;
		$picids[] = $value['picid'];
		$sizes[$value['uid']] = $sizes[$value['uid']] + $value['size'];
	}

	if($picids) {
		deletepics($picids);
	}
	C::t('home_album')->delete($newids);
	C::t('home_feed')->delete_by_id_idtype($newids, 'albumid');
	if($picids) {
		C::t('home_clickuser')->delete_by_id_idtype($picids, 'picid');
	}

	if($sizes) {
		foreach ($sizes as $uid => $value) {
			$attachsize = intval($sizes[$uid]);
			$albumnum = $counts[$uid]['albums'] ? $counts[$uid]['albums'] : 0;
			updatemembercount($uid, array('albums' => $albumnum, 'attachsize' => -$attachsize), false);
		}
	}
	return $dels;
}

function deletetrasharticle($aids) {
	global $_G;

	require_once libfile('function/home');
	$articles = $trashid = $pushs = $dels = array();
	foreach(C::t('portal_article_trash')->fetch_all($aids) as $value) {
		$dels[$value['aid']] = $value['aid'];
		$article = dunserialize($value['content']);
		$articles[$article['aid']] = $article;
		if(!empty($article['idtype'])) $pushs[$article['idtype']][] = $article['id'];
		if($article['pic']) {
			pic_delete($article['pic'], 'portal', $article['thumb'], $article['remote']);
		}
		if($article['htmlmade'] && $article['htmldir'] && $article['htmlname']) {
			deletehtml(DISCUZ_ROOT.'/'.$article['htmldir'].$article['htmlname'], $article['contents']);
		}
	}

	if($dels) {
		C::t('portal_article_trash')->delete($dels, 'UNBUFFERED');
		deletearticlepush($pushs);
		deletearticlerelated($dels);
	}

	return $articles;
}

function deletearticle($aids, $istrash = true) {
	global $_G;

	if(empty($aids)) return false;
	$trasharr = $article = $bids = $dels = $attachment = $attachaid = $catids = $pushs = array();
	$query = C::t('portal_article_title')->fetch_all($aids);
	foreach($query as $value) {
		$catids[] = intval($value['catid']);
		$dels[$value['aid']] = $value['aid'];
		$article[] = $value;
		if(!empty($value['idtype'])) $pushs[$value['idtype']][] = $value['id'];
	}
	if($dels) {
		foreach($article as $key => $value) {
			if($istrash) {
				$trasharr[] = array('aid' => $value['aid'], 'content'=>serialize($value));
			} elseif($value['pic']) {
				pic_delete($value['pic'], 'portal', $value['thumb'], $value['remote']);
				$attachaid[] = $value['aid'];
				if($value['madehtml'] && $value['htmldir'] && $value['htmlname']) {
					deletehtml(DISCUZ_ROOT.'/'.$value['htmldir'].$value['htmlname'], $value['contents']);
				}
			}
		}
		if($istrash && $trasharr) {
			C::t('portal_article_trash')->insert_batch($trasharr);
		} else {
			deletearticlepush($pushs);
			deletearticlerelated($dels);
		}

		C::t('portal_article_title')->delete($dels);
		C::t('common_moderate')->delete($dels, 'aid');

		$catids = array_unique($catids);
		if($catids) {
			foreach($catids as $catid) {
				$cnt = C::t('portal_article_title')->fetch_count_for_cat($catid);
				C::t('portal_category')->update($catid, array('articles'=>dintval($cnt)));
			}
		}
	}
	return $article;
}

function deletearticlepush($pushs) {
	if(!empty($pushs) && is_array($pushs)) {
		foreach($pushs as $idtype=> $fromids) {
			switch ($idtype) {
				case 'blogid':
					if(!empty($fromids)) C::t('home_blogfield')->update($fromids, array('pushedaid'=>'0'));
					break;
				case 'tid':
					if(!empty($fromids)) C::t('forum_thread')->update($fromids, array('pushedaid'=>'0'));
					break;
			}
		}
	}
}

function deletearticlerelated($dels) {

	C::t('portal_article_count')->delete($dels);
	C::t('portal_article_content')->delete_by_aid($dels);

	if($attachment = C::t('portal_attachment')->fetch_all_by_aid($dels)) {
		require_once libfile('function/home');
		foreach ($attachment as $value) {
			pic_delete($value['attachment'], 'portal', $value['thumb'], $value['remote']);
		}
		C::t('portal_attachment')->delete(array_keys($attachment));
	}

	C::t('portal_comment')->delete_by_id_idtype($dels, 'aid');
	C::t('common_moderate')->delete($dels, 'aid_cid');

	C::t('portal_article_related')->delete_by_aid_raid($dels);

}

function deleteportaltopic($dels) {
	if(empty($dels)) return false;
	$targettplname = array();
	foreach ((array)$dels as $key => $value) {
		$targettplname[] = 'portal/portal_topic_content_'.$value;
	}
	C::t('common_diy_data')->delete($targettplname, null);

	require_once libfile('class/blockpermission');
	$tplpermission = & template_permission::instance();
	$templates = array();
	$tplpermission->delete_allperm_by_tplname($targettplname);

	deletedomain($dels, 'topic');
	C::t('common_template_block')->delete_by_targettplname($targettplname);

	require_once libfile('function/home');

	$picids = array();
	foreach(C::t('portal_topic')->fetch_all($dels) as $value) {
		if($value['picflag'] != '0') pic_delete(str_replace('portal/', '', $value['cover']), 'portal', 0, $value['picflag'] == '2' ? '1' : '0');
	}

	$picids = array();
	foreach(C::t('portal_topic_pic')->fetch_all($dels) as $value) {
		$picids[] = $value['picid'];
		pic_delete($value['filepath'], 'portal', $value['thumb'], $value['remote']);
	}
	if (!empty($picids)) {
		C::t('portal_topic_pic')->delete($picids, true);
	}


	C::t('portal_topic')->delete($dels);
	C::t('portal_comment')->delete_by_id_idtype($dels, 'topicid');
	C::t('common_moderate')->delete($dels, 'topicid_cid');

	include_once libfile('function/block');
	block_clear();

	include_once libfile('function/cache');
	updatecache('diytemplatename');
}

function deletedomain($ids, $idtype) {
	if($ids && $idtype) {
		C::t('common_domain')->delete_by_id_idtype($ids, $idtype);
	}
}

function deletecollection($ctid) {
	$tids = array();
	$threadlist = C::t('forum_collectionthread')->fetch_all_by_ctid($ctid);
	$tids = array_keys($threadlist);

	deleterelatedtid($tids, $ctid);

	$collectionteamworker = C::t('forum_collectionteamworker')->fetch_all_by_ctid($ctid);
	foreach ($collectionteamworker as $worker) {
		notification_add($worker['uid'], "system", 'collection_removed', array('ctid'=>$collectiondata['ctid'], 'collectionname'=>$collectiondata['name']), 1);
	}

	C::t('forum_collectionthread')->delete_by_ctid($ctid);
	C::t('forum_collectionfollow')->delete_by_ctid($ctid);
	C::t('forum_collectioncomment')->delete_by_ctid($ctid);
	C::t('forum_collectionteamworker')->delete_by_ctid($ctid);
	C::t('forum_collectioninvite')->delete_by_ctid($ctid);
	C::t('forum_collection')->delete($ctid, true);
}

function deleterelatedtid($tids, $ctid) {
	$loadreleated = C::t('forum_collectionrelated')->fetch_all($tids, true);
	foreach($loadreleated as $loadexist) {
		if($loadexist['tid']) {
			$collectionlist = explode("\t", $loadexist['collection']);
			if(count($collectionlist)>0) {
				foreach ($collectionlist as $collectionkey=>$collectionvalue) {
					if ($collectionvalue == $ctid) {
						unset($collectionlist[$collectionkey]);
						break;
					}
				}
			}
			$newcollection = implode("\t", $collectionlist);
			if (trim($newcollection) == '') {
				C::t('forum_collectionrelated')->delete($loadexist['tid']);
				C::t('forum_thread')->update_status_by_tid($loadexist['tid'], '1111111011111111', '&');
			} else {
				C::t('forum_collectionrelated')->update_collection_by_ctid_tid($newcollection, $loadexist['tid'], true);
			}
		}
	}
}

function deletehtml($htmlname, $count = 1) {
	global $_G;
	@unlink($htmlname.'.'.$_G['setting']['makehtml']['extendname']);
	if($count > 1) {
		for($i = 2; $i <= $count; $i++) {
			@unlink($htmlname.$i.'.'.$_G['setting']['makehtml']['extendname']);
		}
	}
}

function deletememberpost($uids) {
	global $_G;
	require_once libfile('function/post');
	loadcache('posttableids');

	foreach($uids as $uid) {
		$tidsdelete = array();
		$posttables = empty($_G['cache']['posttableids']) ? array(0) : $_G['cache']['posttableids'];
		foreach($posttables as $posttableid) {
			$pidsthread = $pidsdelete = array();
			$postlist = C::t('forum_post')->fetch_all_by_authorid($posttableid, $uid, false);
			if($postlist) {
				foreach($postlist as $post) {
					if($post['first']) {
						$tidsdelete[] = $post['tid'];
					}
					$pidsdelete[] = $post['pid'];
					$pidsthread[$post['pid']] = $post['tid'];
				}
			}
			deletepost($pidsdelete, 'pid', true, $posttableid, true);
		}
		unset($postlist);
		if($tidsdelete) {
			deletethread($tidsdelete, true, true, true);
		}
	}
}

?>