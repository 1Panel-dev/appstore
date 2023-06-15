<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_moderate.php 32501 2013-01-29 09:51:00Z chenmengshu $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
		exit('Access Denied');
}

cpheader();

$ignore = $_GET['ignore'];
$filter = $_GET['filter'];
$modfid = $_GET['modfid'];
$modsubmit = $_GET['modsubmit'];
$moderate = $_GET['moderate'];
$pm = $_GET['pm'];
$showcensor = !empty($_GET['showcensor']) ? 1 : 0;

$_G['setting']['memberperpage'] = 100;
if(empty($operation)) {
	$operation = 'threads';
}
if($operation == 'members') {

	require_once libfile('moderate/member', 'admincp');
	exit;

} else {

	require_once libfile('function/forumlist');
	require_once libfile('function/post');

	$modfid = !empty($modfid) ? intval($modfid) : 0;

	$recyclebins = $forumlist = array();

	$query = C::t('forum_forum')->fetch_all_valid_forum();
	foreach($query as $forum) {
		$recyclebins[$forum['fid']] = $forum['recyclebin'];
		$forumlist[$forum['fid']] = $forum['name'];
	}

	if($modfid && $modfid != '-1') {
		$fidadd = array('fids' => $modfid, 'and' => ' AND ', 't' => 't.', 'p' => 'p.');
	} else {
		$fidadd = array();
	}

	if(isset($filter) && $filter == 'ignore') {
		$displayorder = -3;
		$moderatestatus = 1;
		$filteroptions = '<option value="normal">'.$lang['moderate_none'].'</option><option value="ignore" selected>'.$lang['moderate_ignore'].'</option>';
	} else {
		$displayorder = -2;
		$moderatestatus = 0;
		$filter = 'normal';
		$filteroptions = '<option value="normal" selected>'.$lang['moderate_none'].'</option><option value="ignore">'.$lang['moderate_ignore'].'</option>';
	}

	$forumoptions = '<option value="all"'.(empty($modfid) ? ' selected' : '').'>'.$lang['moderate_all_fields'].'</option>';
	if($operation != 'replies') {
		$forumoptions .= '<option value="-1" '.($modfid == '-1' ? 'selected' : '').'>'.$lang['moderate_all_groups'].'</option>'."\n";
	}
	foreach($forumlist as $fid => $forumname) {
		$selected = $modfid == $fid ? ' selected' : '';
		$forumoptions .= '<option value="'.$fid.'" '.$selected.'>'.$forumname.'</option>'."\n";
	}

	require_once libfile('function/misc');
	$modreasonoptions = '<option value="">'.$lang['none'].'</option><option value="">--------</option>'.modreasonselect(1);

	echo <<<EOT
<script type="text/JavaScript">
	var cookiepre = "{$_G[config][cookie][cookiepre]}";
	function mod_setbg(tid, value) {
		$('mod_' + tid + '_row1').className = 'mod_' + value;
		$('mod_' + tid + '_row2').className = 'mod_' + value;
		$('mod_' + tid + '_row3').className = 'mod_' + value;
		$("chk_apply_all").checked = false;
		$("chk_apply_all").disabled = true;
	}
	function mod_setbg_all(value) {
		checkAll('option', $('cpform'), value);
		var trs = $('cpform').getElementsByTagName('TR');
		for(var i in trs) {
			if(trs[i].id && trs[i].id.substr(0, 4) == 'mod_') {
				trs[i].className = 'mod_' + value;
			}
		}
		$("chk_apply_all").disabled = false;
		$("chk_apply_all").value = value;
	}
	function attachimg() {}
	function expandall() {
		var tds = $('cpform').getElementsByTagName('TD');
		for(var i in tds) {
			if(tds[i].id && tds[i].id.match(/^mod_(\d+)_row1_op$/) != null) {
				tds[i].rowSpan = "3";
			}
		}
		var trs = $('cpform').getElementsByTagName('TR');
		for(var i in trs) {
			if(trs[i].id && trs[i].id.match(/^mod_(\d+)_row1$/) != null) {
				tds = trs[i].getElementsByTagName('TD');
				for(var j in tds) {
					if(tds[j].className == "threadtitle threadopt") {
						tds[j].className = "";
					}
				}
			}
			if(trs[i].id && trs[i].id.match(/^mod_(\d+)_row(2|3)$/) != null) {
				trs[i].style.display = "";
			}
		}
		setcookie("foldall", 0, 3600);
	}

	function foldall() {
		var tds = $('cpform').getElementsByTagName('TD');
		for(var i in tds) {
			if(tds[i].id && tds[i].id.match(/^mod_(\d+)_row1_op$/) != null) {
				tds[i].rowSpan = "1";
			}
		}
		var trs = $('cpform').getElementsByTagName('TR');
		for(var i in trs) {
			if(trs[i].id && trs[i].id.match(/^mod_(\d+)_row1$/) != null) {
				tds = trs[i].getElementsByTagName('TD');
				for(var j in tds) {
					if(tds[j].className == "") {
						tds[j].className = "threadtitle threadopt";
					}
				}
			}
			if(trs[i].id && trs[i].id.match(/^mod_(\d+)_row(2|3)$/) != null) {
				trs[i].style.display = "none";
			}
		}
		setcookie("foldall", 1, 3600);
	}

	function display_toggle(tid) {
		var tr1 = $('mod_' + tid + '_row1');
		var tr1_op = $('mod_' + tid + '_row1_op');
		var tr2 = $('mod_' + tid + '_row2');
		var tr3 = $('mod_' + tid + '_row3');
		var tds = tr1.getElementsByTagName('TD');
		if(tr1_op.rowSpan == "1") {
			for(var i in tds) {
				if(tds[i].className == "threadtitle threadopt") {
					tds[i].className = "";
				}
			}
			tr1_op.rowSpan = "3";
			tr2.style.display = "";
			tr3.style.display = "";
		} else {
			for(var i in tds) {
				if(tds[i].className == "") {
					tds[i].className = "threadtitle threadopt";
				}
			}
			tr1_op.rowSpan = "1";
			tr2.style.display = "none";
			tr3.style.display = "none";
		}
	}

	function mod_cancel_all() {
		var form = $('cpform');
		var checkall = 'chkall';
		for(var i = 0; i < form.elements.length; i++) {
			var e = form.elements[i];
			if(e.type == 'radio') {
				e.checked = '';
			}
		}
		var trs = $('cpform').getElementsByTagName('TR');
		for(var i in trs) {
			if(trs[i].id && trs[i].id.match(/^mod_(\d+)_row(1|2|3)$/)) {
				trs[i].className = "mod_cancel";
			}
		}
		$("chk_apply_all").checked = false;
		$("chk_apply_all").disabled = true;
	}

	function remove_element(_element) {
		var _parentElement = _element.parentNode;
		if(_parentElement){
			_parentElement.removeChild(_element);
		}
	}

	function mod_remove_row(id) {
		var id1 = "mod_" + id + "_row1";
		var id2 = "mod_" + id + "_row2";
		var id3 = "mod_" + id + "_row3";
		var node1 = parent.document.getElementById(id1);
		var node2 = parent.document.getElementById(id2);
		var node3 = parent.document.getElementById(id3);
		remove_element(node1);
		remove_element(node2);
		remove_element(node3);
	}

	window.onload = function() {
		if(getcookie("foldall")) {
			foldall();
		}
	};
</script>
EOT;

}

$submenu = array(
	array(array('menu' => 'moderate_m_forum', 'submenu' => array(
		'threads' => array('nav_moderate_threads', 'moderate&operation=threads', $operation == 'threads'),
		'replies' => array('nav_moderate_replies', 'moderate&operation=replies', $operation == 'replies'),
	)), in_array($operation, array('threads', 'replies'))),
	array(array('menu' => 'moderate_m_home', 'submenu' => array(
		'blogs' => array('nav_moderate_blogs', 'moderate&operation=blogs', $operation == 'blogs'),
		'pictures' => array('nav_moderate_pictures', 'moderate&operation=pictures', $operation == 'pictures'),
		'doings' => array('nav_moderate_doings', 'moderate&operation=doings', $operation == 'doings'),
		'shares' => array('nav_moderate_shares', 'moderate&operation=shares', $operation == 'shares'),
		'comments' => array('nav_moderate_comments', 'moderate&operation=comments', $operation == 'comments'),
	)), in_array($operation, array('blogs', 'pictures', 'doings', 'shares', 'comments'))),
	array(array('menu' => 'moderate_m_portal', 'submenu' => array(
		'articles' => array('nav_moderate_articles', 'moderate&operation=articles', $operation == 'articles'),
		'articlecomments' => array('nav_moderate_articlecomments', 'moderate&operation=articlecomments', $operation == 'articlecomments'),
		'topiccomments' => array('nav_moderate_topiccomments', 'moderate&operation=topiccomments', $operation == 'topiccomments'),
	)), in_array($operation, array('articles', 'articlecomments', 'topiccomments')))
);

if($operation == 'threads') {

	require_once libfile('moderate/thread', 'admincp');

} elseif($operation == 'replies') {

	require_once libfile('moderate/reply', 'admincp');

} elseif($operation == 'blogs') {

	require_once libfile('moderate/blog', 'admincp');

} elseif($operation == 'pictures') {

	require_once libfile('moderate/picture', 'admincp');

} elseif($operation == 'doings') {

	require_once libfile('moderate/doing', 'admincp');

} elseif($operation == 'shares') {

	require_once libfile('moderate/share', 'admincp');

} elseif($operation == 'comments') {

	require_once libfile('moderate/comment', 'admincp');

} elseif($operation == 'articles') {

	require_once libfile('moderate/article', 'admincp');

} elseif($operation == 'articlecomments' || $operation == 'topiccomments') {

	require_once libfile('moderate/portalcomment', 'admincp');

}

echo '<iframe name="fasthandle" style="display: none;"></iframe>';

function callback_js($id) {
	$js = <<<EOT
<script type="text/javascript">
	mod_remove_row('$id');
</script>
EOT;
	return $js;
}

function moderateswipe($type, $ids) {
	if($type == 'pid') {
		$exist_ids = array_keys(C::t('forum_post')->fetch_all(0, $ids));
	} elseif($type == 'tid') {
		$exist_ids = array_keys(C::t('forum_thread')->fetch_all($ids));
	}
	$remove_ids = array_diff($ids, $exist_ids);
	if($remove_ids) {
		return C::t('common_moderate')->delete($remove_ids, $type);
	} else {
		return 0;
	}
}

?>