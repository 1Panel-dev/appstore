<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portalcp_article.php 36278 2016-12-09 07:52:35Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$op = in_array($_GET['op'], array('edit', 'delete', 'related', 'batch', 'pushplus', 'verify', 'checkhtmlname')) ? $_GET['op'] : 'add';
$aid = intval($_GET['aid']);
$catid = intval($_GET['catid']);
list($seccodecheck, $secqaacheck) = seccheck('publish');

$article = $article_content = array();
if($aid) {
	$article = C::t('portal_article_title')->fetch($aid);
	if(!$article) {
		showmessage('article_not_exist', dreferer());
	}
}

loadcache('portalcategory');
$portalcategory = $_G['cache']['portalcategory'];
if($catid && empty($portalcategory[$catid])) {
	showmessage('portal_category_not_find', dreferer());
}
if(empty($article) && $catid && $portalcategory[$catid]['disallowpublish']) {
	showmessage('portal_category_disallowpublish', dreferer());
}

if(empty($catid) && $article) {
	$catid = $article['catid'];
}
$htmlstatus = !empty($_G['setting']['makehtml']['flag']) && $portalcategory[$catid]['fullfoldername'];

if(submitcheck("articlesubmit", 0, $seccodecheck, $secqaacheck)) {

	if($aid) {
		check_articleperm($article['catid'], $aid, $article);
	} else {
		check_articleperm($catid);
	}

	$_POST['title'] = getstr(trim($_POST['title']), 80);
	if(strlen($_POST['title']) < 1) {
		showmessage('title_not_too_little');
	}
	$_POST['title'] = censor($_POST['title'], NULL, FALSE, FALSE);

	$_POST['pagetitle'] = getstr(trim($_POST['pagetitle']), 60);
	$_POST['pagetitle'] = censor($_POST['pagetitle'], NULL, FALSE, FALSE);
	$htmlname = basename(trim($_POST['htmlname']));

	$highlight_style = $_GET['highlight_style'];
	$style = '';
	$style = implode('|',$highlight_style);
	if(empty($_POST['summary'])) $_POST['summary'] = preg_replace("/(\s|\<strong\>##########NextPage(\[title=.*?\])?##########\<\/strong\>)+/", ' ', $_POST['content']);
	$summary = portalcp_get_summary($_POST['summary']);
	$summary = censor($summary, NULL, FALSE, FALSE);

	$_GET['author'] = dhtmlspecialchars($_GET['author']);
	$_GET['url'] = str_replace('&amp;', '&', dhtmlspecialchars($_GET['url']));
	$_GET['from'] = dhtmlspecialchars($_GET['from']);
	$_GET['fromurl'] = str_replace('&amp;', '&', dhtmlspecialchars($_GET['fromurl']));
	$_GET['dateline'] = !empty($_GET['dateline']) ? strtotime($_GET['dateline']) : TIMESTAMP;

	if(!preg_match('/^https?:\/\//is', $_GET['url'])) {
		$_GET['url'] = '';
	}

	if(!preg_match('/^https?:\/\//is', $_GET['fromurl'])) {
		$_GET['fromurl'] = '';
	}

	if(censormod($_POST['title']) || $_G['group']['allowpostarticlemod']) {
		$article_status = 1;
	} else {
		$article_status = 0;
	}

	$setarr = array(
		'title' => $_POST['title'],
		'author' => $_GET['author'],
		'from' => $_GET['from'],
		'fromurl' => $_GET['fromurl'],
		'dateline' => intval($_GET['dateline']),
		'url' => $_GET['url'],
		'allowcomment' => !empty($_POST['forbidcomment']) ? '0' : '1',
		'summary' => $summary,
		'catid' => intval($_POST['catid']),
		'tag' => article_make_tag($_POST['tag']),
		'status' => $article_status,
		'highlight' => $style,
		'showinnernav' => empty($_POST['showinnernav']) ? '0' : '1',
	);

	if(empty($setarr['catid'])) {
		showmessage('article_choose_system_category');
	}

	if($_GET['conver']) {
		$converfiles = dunserialize($_GET['conver']);
		$setarr['pic'] = $converfiles['pic'];
		$setarr['thumb'] = intval($converfiles['thumb']);
		$setarr['remote'] = intval($converfiles['remote']);
	}

	$id = 0;
	$idtype = '';

	if(empty($article)) {
		$setarr['uid'] = $_G['uid'];
		$setarr['username'] = $_G['username'];
		$setarr['id'] = intval($_POST['id']);
		$setarr['htmlname'] = $htmlname;
		$table = '';
		if($setarr['id']) {
			if($_POST['idtype']=='blogid') {
				$table = 'home_blogfield';
				$setarr['idtype'] = 'blogid';
				$id = $setarr['id'];
				$idtype = $setarr['idtype'];
			} else {
				$table = 'forum_thread';
				$setarr['idtype'] = 'tid';

				require_once libfile('function/discuzcode');
				$id = C::t('forum_post')->fetch_threadpost_by_tid_invisible($setarr['id']);
				$id = $id['pid'];
				$idtype = 'pid';
			}
		}
		$aid = C::t('portal_article_title')->insert($setarr, 1);
		if($table) {
			if($_POST['idtype']=='blogid') {
				C::t('home_blogfield')->update($setarr['id'], array('pushedaid' => $aid));
			} elseif($setarr['idtype']=='tid') {
				$modarr = array(
					'tid' => $setarr['id'],
					'uid' => $_G['uid'],
					'username' => $_G['username'],
					'dateline' => TIMESTAMP,
					'action' => 'PTA',
					'status' => '1',
					'stamp' => '',
				);
				C::t('forum_threadmod')->insert($modarr);

				C::t('forum_thread')->update($setarr['id'], array('moderated' => 1, 'pushedaid' => $aid));
			}
		}
		C::t('common_member_status')->update($_G['uid'], array('lastpost' => TIMESTAMP), 'UNBUFFERED');
		C::t('portal_category')->increase($setarr['catid'], array('articles' => 1));
		C::t('portal_category')->update($setarr['catid'], array('lastpublish' => TIMESTAMP));
		C::t('portal_article_count')->insert(array('aid'=>$aid, 'catid'=>$setarr['catid'], 'viewnum'=>1));
	} else {
		if($htmlname && $article['htmlname'] !== $htmlname) {
			$setarr['htmlname'] = $htmlname;
			$oldarticlename = $article['htmldir'].$article['htmlname'];
			unlink($oldarticlename.'.'.$_G['setting']['makehtml']['extendname']);
			for($i = 1; $i < $article['contents']; $i++) {
				unlink($oldarticlename.$i.'.'.$_G['setting']['makehtml']['extendname']);
			}
		}
		C::t('portal_article_title')->update($aid, $setarr);
	}

	$content = getstr($_POST['content'], 0, 0, 0, 0, 1);
	$content = censor($content, NULL, FALSE, FALSE);
	if(censormod($content) || $_G['group']['allowpostarticlemod']) {
		$article_status = 1;
	} else {
		$article_status = 0;
	}

	$regexp = '/(\<strong\>##########NextPage(\[title=(.*?)\])?##########\<\/strong\>)+/is';
	preg_match_all($regexp, $content ,$arr);
	$pagetitle = !empty($arr[3]) ? $arr[3] : array();
	$pagetitle = array_map('trim', $pagetitle);
	array_unshift($pagetitle, $_POST['pagetitle']);
	$contents = preg_split($regexp, $content);
	$cpostcount = count($contents);

	$dbcontents = C::t('portal_article_content')->fetch_all($aid);

	$pagecount = $cdbcount = count($dbcontents);
	if($cdbcount > $cpostcount) {
		$cdelete = array();
		foreach(array_splice($dbcontents, $cpostcount) as $value) {
			$cdelete[$value['cid']] = $value['cid'];
		}
		if(!empty($cdelete)) {
			C::t('portal_article_content')->delete($cdelete);
		}
		$pagecount = $cpostcount;
	}

	foreach($dbcontents as $key => $value) {
		C::t('portal_article_content')->update($value['cid'], array('title' => $pagetitle[$key], 'content' => $contents[$key], 'pageorder' => $key+1));
		unset($pagetitle[$key], $contents[$key]);
	}

	if($cdbcount < $cpostcount) {
		foreach($contents as $key => $value) {
			C::t('portal_article_content')->insert(array('aid' => $aid, 'id' => $setarr['id'], 'idtype' => $setarr['idtype'], 'title' => $pagetitle[$key], 'content' => $contents[$key], 'pageorder' => $key+1, 'dateline' => TIMESTAMP));
		}
		$pagecount = $cpostcount;
	}

	$updatearticle = array('contents' => $pagecount);
	if($article_status == 1) {
		$updatearticle['status'] = 1;
		updatemoderate('aid', $aid);
		manage_addnotify('verifyarticle');
	}

	$updatearticle = array_merge($updatearticle, portalcp_article_pre_next($catid, $aid));
	C::t('portal_article_title')->update($aid, $updatearticle);

	$newaids = array();
	$_POST['attach_ids'] = explode(',', $_POST['attach_ids']);
	foreach ($_POST['attach_ids'] as $newaid) {
		$newaid = intval($newaid);
		if($newaid) $newaids[$newaid] = $newaid;
	}
	if($newaids) {
		C::t('portal_attachment')->update_to_used($newaids, $aid);
	}

	addrelatedarticle($aid, $_POST['raids']);

	if($_GET['from_idtype'] && $_GET['from_id']) {

		$id = intval($_GET['from_id']);
		$notify = array();
		switch ($_GET['from_idtype']) {
			case 'blogid':
				$blog = C::t('home_blog')->fetch($id);
				if(!empty($blog)) {
					$notify = array(
						'url' => "home.php?mod=space&uid=$blog[uid]&do=blog&id=$id",
						'subject' => $blog['subject']
					);
					$touid = $blog['uid'];
				}
				break;
			case 'tid':
				$thread = C::t('forum_thread')->fetch($id);
				if(!empty($thread)) {
					$notify = array(
						'url' => "forum.php?mod=viewthread&tid=$id",
						'subject' => $thread['subject']
					);
					$touid = $thread['authorid'];
				}
				break;
		}
		if(!empty($notify)) {
			$notify['newurl'] = 'portal.php?mod=view&aid='.$aid;
			notification_add($touid, 'pusearticle', 'puse_article', $notify, 1);
		}
	}

	if(trim($_GET['from']) != '') {
		$from_cookie = '';
		$from_cookie_array = array();
		$from_cookie = getcookie('from_cookie');
		$from_cookie_array = explode("\t", $from_cookie);
		$from_cookie_array[] = $_GET['from'];
		$from_cookie_array = array_unique($from_cookie_array);
		$from_cookie_array = array_filter($from_cookie_array);
		$from_cookie_num = count($from_cookie_array);
		$from_cookie_start = $from_cookie_num - 10;
		$from_cookie_start = $from_cookie_start > 0 ? $from_cookie_start : 0;
		$from_cookie_array = array_slice($from_cookie_array, $from_cookie_start, $from_cookie_num);
		$from_cookie = implode("\t", $from_cookie_array);
		dsetcookie('from_cookie', $from_cookie);
	}
	dsetcookie('clearUserdata', 'home');
	$op = 'add_success';
	$article_add_url = 'portal.php?mod=portalcp&ac=article&catid='.$catid;


	$article = C::t('portal_article_title')->fetch($aid);
	$viewarticleurl = $_POST['url'] ? "portal.php?mod=list&catid=$_POST[catid]" : fetch_article_url($article);

	include_once template("portal/portalcp_article");dexit();

} elseif(submitcheck('pushplussubmit')) {

	if($aid) {
		check_articleperm($article['catid'], $aid, $article);
	} else {
		showmessage('no_article_specified_for_pushplus', dreferer());
	}

	$tourl = !empty($_POST['toedit']) ? 'portal.php?mod=portalcp&ac=article&op=edit&aid='.$aid : dreferer();
	$pids = (array)$_POST['pushpluspids'];
	$posts = array();
	$tid = intval($_GET['tid']);
	if($tid && $pids) {
		foreach(C::t('forum_post')->fetch_all('tid:'.$tid, $pids) as $value) {
			if($value['tid'] != $tid) {
				continue;
			}
			$posts[$value['pid']] = $value;
		}
	}
	if(empty($posts)) {
		showmessage('no_posts_for_pushplus', dreferer());
	}

	$pageorder = C::t('portal_article_content')->fetch_max_pageorder_by_aid($aid);
	$pageorder = intval($pageorder + 1);
	$inserts = array();
	foreach($posts as $post) {
		$summary = portalcp_get_postmessage($post);
		$summary .= lang('portalcp', 'article_pushplus_info', array('author'=>$post['author'], 'url'=>'forum.php?mod=redirect&goto=findpost&ptid='.$post['tid'].'&pid='.$post['pid']));
		$inserts[] = array('aid'=>$aid, 'content'=>$summary, 'pageorder'=>$pageorder, 'dateline'=>$_G['timestamp'], 'id'=>$post[pid], 'idtype' =>'pid');
		$pageorder++;
	}
	C::t('portal_article_content')->insert_batch($inserts);
	$pluscount = C::t('portal_article_content')->count_by_aid($aid);
	C::t('portal_article_title')->update($aid, array('contents' => $pluscount, 'owncomment' => 1));
	$commentnum = C::t('portal_comment')->count_by_id_idtype($aid, 'aid');
	C::t('portal_article_count')->update($aid, array('commentnum'=>intval($commentnum)));
	showmessage('pushplus_do_success', $tourl, array(), array('header'=>1, 'refreshtime'=>0));

} elseif(submitcheck('verifysubmit')) {
	if($aid) {
		check_articleperm($article['catid'], $aid, $article, true);
	} else {
		showmessage('article_not_exist', dreferer());
	}
	if($_POST['status'] == '0') {
		C::t('portal_article_title')->update($aid, array('status'=>'0'));
		updatemoderate('aid', $aid, 2);
		$tourl = dreferer(fetch_article_url($article));
		showmessage('article_passed', $tourl);

	} elseif($_POST['status'] == '2') {
		C::t('portal_article_title')->update($aid, array('status'=>'2'));
		updatemoderate('aid', $aid, 1);
		$tourl = dreferer(fetch_article_url($article));
		showmessage('article_ignored', $tourl);

	} elseif($_POST['status'] == '-1') {
		include_once libfile('function/delete');
		deletearticle(array($aid), 0);
		updatemoderate('aid', $aid, 2);

		$tourl = dreferer('portal.php?mod=portalcp&catid='.$article['catid']);
		showmessage('article_deleted', $tourl);

	} else {
		showmessage('select_operation');
	}
}

if($op == 'delete') {

	if(!$aid) {
		showmessage('article_edit_nopermission');
	}
	check_articleperm($article['catid'], $aid, $article);

	if(submitcheck('deletesubmit')) {
		include_once libfile('function/delete');
		$article = deletearticle(array(intval($_POST['aid'])), intval($_POST['optype']));
		showmessage('article_delete_success', "portal.php?mod=list&catid={$article[0][catid]}");
	}

} elseif($op == 'related') {

	$raid = intval($_GET['raid']);
	$ra = array();
	if($raid) {
		$ra = C::t('portal_article_title')->fetch($raid);
	}

} elseif($op == 'batch') {

	check_articleperm($catid);

	$aids = $_POST['aids'];
	$optype = $_POST['optype'];
	if(empty($optype) || $optype == 'push') showmessage('article_action_invalid');
	$aids = array_map('intval', $aids);
	$aids = array_filter($aids);
	if(empty($aids)) showmessage('article_not_choose');

	if (submitcheck('batchsubmit')) {
		if ($optype == 'trash' || $optype == 'delete') {
				require_once libfile('function/delete');
				$istrash = $optype == 'trash' ? 1 : 0;
				$article = deletearticle($aids, $istrash);
				showmessage('article_delete_success', dreferer("portal.php?mod=portalcp&ac=category&catid={$article[0][catid]}"));
		} elseif($optype == 'move') {
			if($catid) {
				$categoryUpdate = array();
				foreach(C::t('portal_article_title')->fetch_all($aids) as $s_article) {
					$categoryUpdate[$s_article['catid']] = $categoryUpdate[$s_article['catid']] ? --$categoryUpdate[$s_article['catid']] : -1;
					$categoryUpdate[$catid] = $categoryUpdate[$catid] ? ++$categoryUpdate[$catid] : 1;
				}
				foreach($categoryUpdate as $scatid=>$scatnum) {
					if($scatnum) {
						C::t('portal_category')->increase($scatid, array('articles' => $scatnum));
					}
				}
				C::t('portal_article_title')->update($aids, array('catid'=>$catid));
				showmessage('article_move_success', dreferer("portal.php?mod=portalcp&ac=category&catid=$catid"));
			} else {
				showmessage('article_move_select_cat', dreferer());
			}
		}

	}

} elseif($op == 'verify') {
	if($aid) {
		check_articleperm($article['catid'], $aid, $article);
	} else {
		showmessage('article_not_exist', dreferer());
	}

} elseif($op == 'pushplus') {
	if($aid) {
		check_articleperm($article['catid'], $aid, $article);
	} else {
		showmessage('no_article_specified_for_pushplus', dreferer());
	}

	$pids = (array)$_POST['topiclist'];
	$tid = intval($_GET['tid']);
	$pushedids = array();
	$pushcount = $pushedcount = 0;
	if(!empty($pids)) {
		foreach(C::t('portal_article_content')->fetch_all($aid) as $value) {
			$pushedids[] = intval($value['id']);
			$pushedcount++;
		}
		$pids = array_diff($pids, $pushedids);
	}
	$pushcount = count($pids);

	if(empty($pids)) {
		showmessage($pushedids ? 'all_posts_pushed_already' : 'no_posts_for_pushplus');
	}

} else if($op == 'checkhtmlname') {
	$htmlname = basename(trim($_GET['htmlname']));
	if($htmlstatus) {
		$_time = !empty($article) ? $article['dateline'] : TIMESTAMP;
		if(file_exists(helper_makehtml::fetch_dir($portalcategory[$catid]['fullfoldername'], $_time).$htmlname.'.'.$_G['setting']['makehtml']['extendname'])) {
			showmessage('html_existed');
		} else {
			showmessage('html_have_no_exists');
		}
	} else {
		showmessage('make_html_closed');
	}

} else {

	if(empty($_G['cache']['portalcategory'])) {
		showmessage('portal_has_not_category');
	}

	if(!checkperm('allowmanagearticle') && !checkperm('allowpostarticle')) {
		$allowcategorycache = array();
		if($allowcategory = getallowcategory($_G['uid'])) {
			foreach($allowcategory as $catid => $category) {
				$allowcategorycache[$catid] = $_G['cache']['portalcategory'][$catid];
			}
		}
		foreach($allowcategorycache as &$_value) {
			if($_value['upid'] && !isset($allowcategorycache[$_value['upid']])) {
				$_value['level'] = 0;
			}
		}
		$_G['cache']['portalcategory'] = $allowcategorycache;
	}

	if(empty($_G['cache']['portalcategory'])) {
		showmessage('portal_article_add_nopermission');
	}

	$category = $_G['cache']['portalcategory'];
	$cate = $category[$catid];
	$categoryselect = category_showselect('portal', 'catid', true, !empty($article['catid']) ? $article['catid'] : $catid);

	if($aid) {
		$catid = intval($article['catid']);
	}

	if($aid && $article['highlight']) {
		$stylecheck = '';
		$stylecheck = explode('|', $article['highlight']);
	}

	$from_cookie_str = '';
	$from_cookie = array();
	$from_cookie_str = stripcslashes(getcookie('from_cookie'));
	$from_cookie = explode("\t", $from_cookie_str);
	$from_cookie = array_filter($from_cookie);

	if($article) {

		foreach(C::t('portal_article_content')->fetch_all($aid) as $key => $value) {
			$nextpage = '';
			if($key > 0) {
				$pagetitle = $value['title'] ? '[title='.$value['title'].']' : '';
				$nextpage = "\r\n".'<strong>##########NextPage'.$pagetitle.'##########</strong>';
			} else {
				$article_content['title'] = $value['title'];
			}
			$article_content['content'] .= $nextpage.$value['content'];
		}

		$article_content['content'] = dhtmlspecialchars($article_content['content']);

		$article['attach_image'] = $article['attach_file'] = '';
		foreach(C::t('portal_attachment')->fetch_all_by_aid($aid) as $value) {
			if($value['isimage']) {
				if($article['pic']) {
					$value['pic'] = $article['pic'];
				}
			} else {
			}
			$attachs[] = $value;
		}
		if($article['idtype'] == 'tid') {
			foreach(C::t('forum_attachment_n')->fetch_all_by_id('tid:'.$article['id'], 'tid', $article['id']) as $value) {
				if($value['isimage']) {
					if($article['pic']) {
						$value['pic'] = $article['pic'];
					}
					$value['attachid'] = $value['aid'];
				} else {
				}
				$value['from'] = 'forum';
				$attachs[] = $value;
			}
		}

		if($article['pic']) {
			$article['conver'] = addslashes(serialize(array('pic'=>$article['pic'], 'thumb'=>$article['thumb'], 'remote'=>$article['remote'])));
		}

		$article['related'] = array();
		if(($relateds = C::t('portal_article_related')->fetch_all_by_aid($aid))) {
			foreach(C::t('portal_article_title')->fetch_all(array_keys($relateds)) as $raid=>$value) {
				$article['related'][$raid] = $value['title'];
			}
		}
	}

	$_GET['from_id'] = empty ($_GET['from_id'])?0:intval($_GET['from_id']);
	if($_GET['from_idtype'] != 'blogid') $_GET['from_idtype'] = 'tid';

	$idtypes = array($_GET['from_idtype'] => ' selected');
	if($_GET['from_idtype'] && $_GET['from_id']) {

		$havepush = C::t('portal_article_title')->fetch_count_for_idtype($_GET['from_id'], $_GET['from_idtype']);
		if($havepush) {
			if($_GET['from_idtype'] == 'blogid') {
				showmessage('article_push_blogid_invalid_repeat', '', array(), array('return'=>true));
			} else {
				showmessage('article_push_tid_invalid_repeat', '', array(), array('return'=>true));
			}
		}

		switch ($_GET['from_idtype']) {
		case 'blogid':
			$blog = array_merge(
				C::t('home_blog')->fetch($_GET['from_id']),
				C::t('home_blogfield')->fetch($_GET['from_id'])
			);
			if($blog) {
				if($blog['friend']) {
					showmessage('article_push_invalid_private');
				}
				$article['title'] = getstr($blog['subject'], 0);
				$article['summary'] = portalcp_get_summary($blog['message']);
				$article['fromurl'] = 'home.php?mod=space&uid='.$blog[uid].'&do=blog&id='.$blog[blogid];
				$article['author'] = $blog['username'];
				$article_content['content'] = dhtmlspecialchars($blog['message']);
			}
			break;
		default:
			$posttable = getposttablebytid($_GET['from_id']);
			$thread = C::t('forum_thread')->fetch($_GET['from_id']);
			$thread = array_merge($thread, C::t('forum_post')->fetch_threadpost_by_tid_invisible($_GET['from_id']));
			if($thread) {
				$article['title'] = $thread['subject'];
				$thread['message'] = portalcp_get_postmessage($thread, $_GET['getauthorall']);
				$article['summary'] = portalcp_get_summary($thread['message']);
				$article['fromurl'] = 'forum.php?mod=viewthread&tid='.$thread['tid'];
				$article['author'] = $thread['author'];
				$article_content['content'] = dhtmlspecialchars($thread['message']);

				$article['attach_image'] = $article['attach_file'] = '';
				foreach(C::t('forum_attachment_n')->fetch_all_by_id('tid:'.$thread['tid'], 'pid', $thread['pid'], 'aid DESC') as $attach) {
					$attachcode = '[attach]'.$attach['aid'].'[/attach]';
					if(!strexists($article_content['content'], $attachcode)) {
						$article_content['content'] .= '<br /><br />'.$attachcode;
					}
					if($attach['isimage']) {
						if($article['pic']) {
							$attach['pic'] = $article['pic'];
						}
					} else {
					}
					$attach['from'] = 'forum';
					$attachs[] = $attach;
				}
			}
			break;
		}
	}

	if(!empty($article['dateline'])) {
		$article['dateline'] = dgmdate($article['dateline']);
	}
	if(!empty($attachs)) {
		$article['attachs'] = get_upload_content($attachs);
	}
	$article_tags = article_parse_tags($article['tag']);
	$tag_names = article_tagnames();
}
require_once libfile('function/upload');
$swfconfig = getuploadconfig($_G['uid'], 0, false);
require_once libfile('function/spacecp');
$albums = getalbums($_G['uid']);
include_once template("portal/portalcp_article");

function portalcp_get_summary($message) {
	$message = preg_replace(array("/\[attach\].*?\[\/attach\]/", "/\&[a-z]+\;/i", "/\<script.*?\<\/script\>/"), '', $message);
	$message = preg_replace("/\[.*?\]/", '', $message);
	$message = getstr(strip_tags($message), 200);
	return $message;
}

function portalcp_get_postmessage($post, $getauthorall = '') {
	global $_G;
	$forum = C::t('forum_forum')->fetch($post['fid']);
	require_once libfile('function/discuzcode');
	$language = lang('forum/misc');
	if($forum['type'] == 'sub' && $forum['status'] == 3) {
		loadcache('grouplevels');
		$grouplevel = $_G['grouplevels'][$forum['level']];
		$group_postpolicy = $grouplevel['postpolicy'];
		if(is_array($group_postpolicy)) {
			$forum = array_merge($forum, $group_postpolicy);
		}
	}
	$post['message'] = preg_replace($language['post_edit_regexp'], '', $post['message']);

	$_message = '';
	if($getauthorall) {
		foreach(C::t('forum_post')->fetch_all_by_tid('tid:'.$post['tid'], $post['tid'], true, '', 0, 0, null, null, $post['authorid']) as $value){
			if(!$value['first']) {
				$value['message'] = preg_replace("/\s?\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s?/is", '', $value['message']);
				$value['message'] = discuzcode($value['message'], $value['smileyoff'], $value['bbcodeoff'], $value['htmlon'] & 1, $forum['allowsmilies'], $forum['allowbbcode'], ($forum['allowimgcode'] && $_G['setting']['showimages'] ? 1 : 0), $forum['allowhtml'], 0, 0, $value['authorid'], $forum['allowmediacode'], $value['pid']);
				portalcp_parse_postattch($value);
				$_message .= '<br /><br />'.$value['message'];
			}
		}
	}

	$msglower = strtolower($post['message']);
	if(strpos($msglower, '[/media]') !== FALSE) {
		$post['message'] = preg_replace_callback("/\[media=([\w%,]+)\]\s*([^\[\<\r\n]+?)\s*\[\/media\]/is", 'portalcp_get_postmessage_callback_parsearticlemedia_12', $post['message']);
	}
	if(strpos($msglower, '[/audio]') !== FALSE) {
		$post['message'] = preg_replace_callback("/\[audio(=1)*\]\s*([^\[\<\r\n]+?)\s*\[\/audio\]/is", 'portalcp_get_postmessage_callback_parsearticlemedia_2', $post['message']);
	}
	if(strpos($msglower, '[/flash]') !== FALSE) {
		$post['message'] = preg_replace_callback("/\[flash(=(\d+),(\d+))?\]\s*([^\[\<\r\n]+?)\s*\[\/flash\]/is", 'portalcp_get_postmessage_callback_parsearticlemedia_4', $post['message']);
	}

	$post['message'] = discuzcode($post['message'], $post['smileyoff'], $post['bbcodeoff'], $post['htmlon'] & 1, $forum['allowsmilies'], $forum['allowbbcode'], ($forum['allowimgcode'] && $_G['setting']['showimages'] ? 1 : 0), $forum['allowhtml'], 0, 0, $post['authorid'], $forum['allowmediacode'], $post['pid']);
	portalcp_parse_postattch($post);

	if(strpos($post['message'], '[/flash1]') !== FALSE) {
		$post['message'] = str_replace('[/flash1]', '[/flash]', $post['message']);
	}
	return $post['message'].$_message;
}

function portalcp_get_postmessage_callback_parsearticlemedia_12($matches) {
	return parsearticlemedia($matches[1], $matches[2]);
}

function portalcp_get_postmessage_callback_parsearticlemedia_2($matches) {
	return parsearticlemedia('mid,0,0', $matches[2]);
}

function portalcp_get_postmessage_callback_parsearticlemedia_4($matches) {
	return parsearticlemedia('swf,0,0', $matches[4]);
}

function portalcp_parse_postattch(&$post) {
	static $allpostattchs = null;
	if($allpostattchs === null) {
		foreach(C::t('forum_attachment_n')->fetch_all_by_id('tid:'.$post['tid'], 'tid', $post['tid']) as $attch) {
			$allpostattchs[$attch['pid']][$attch['aid']] = $attch['aid'];
		}
	}
	$attachs = $allpostattchs[$post['pid']];
	if(preg_match_all("/\[attach\](\d+)\[\/attach\]/i", $post['message'], $matchaids)) {
		$attachs = array_diff($allpostattchs[$post['pid']], $matchaids[1]);
	}
	if($attachs) {
		$add = '';
		foreach($attachs as $attachid) {
			$add .= '<br/>'.'[attach]'.$attachid.'[/attach]';
		}
		$post['message'] .= $add;
	}
}
function parsearticlemedia($params, $url) {
	global $_G;

	$params = explode(',', $params);

	$url = addslashes($url);
	if($flv = parseflv($url, 0, 0)) {
		if(!empty($flv) && preg_match("/\.flv$/i", $flv['flv'])) {
			$flv['flv'] = $_G['style']['imgdir'].'/flvplayer.swf?&autostart=true&file='.urlencode($flv['flv']);
		}
		$url = $flv['flv'];
		$params[0] = 'swf';
	}
	if(in_array(count($params), array(3, 4))) {
		$type = $params[0];
		$url = str_replace(array('<', '>'), '', str_replace('\\"', '\"', $url));
		switch($type) {
			case 'mp3':
			case 'wma':
			case 'ra':
			case 'ram':
			case 'wav':
			case 'mid':
				return '[flash=mp3]'.$url.'[/flash1]';
			case 'rm':
			case 'rmvb':
			case 'rtsp':
				return '[flash=real]'.$url.'[/flash1]';
			case 'swf':
				return '[flash]'.$url.'[/flash1]';
			case 'asf':
			case 'asx':
			case 'wmv':
			case 'mms':
			case 'avi':
			case 'mpg':
			case 'mpeg':
			case 'mov':
				return '[flash=media]'.$url.'[/flash1]';
			default:
				return '<a href="'.$url.'" target="_blank">'.$url.'</a>';
		}
	}
	return;
}

function portalcp_article_pre_next($catid, $aid) {
	$data = array(
		'preaid' => C::t('portal_article_title')->fetch_preaid_by_catid_aid($catid, $aid),
		'nextaid' => C::t('portal_article_title')->fetch_nextaid_by_catid_aid($catid, $aid),
	);
	if($data['preaid']) {
		C::t('portal_article_title')->update($data['preaid'], array(
			'preaid' => C::t('portal_article_title')->fetch_preaid_by_catid_aid($catid, $data['preaid']),
			'nextaid' => C::t('portal_article_title')->fetch_nextaid_by_catid_aid($catid, $data['preaid']),
			)
		);
	}
	return $data;
}

?>