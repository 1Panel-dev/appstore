<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: moderate_share.php 27434 2012-01-31 08:57:34Z chenmengshu $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

if(!submitcheck('modsubmit') && !$_GET['fast']) {

	shownav('topic', $lang['moderate_shares']);
	showsubmenu('nav_moderate_posts', $submenu);

	$select[$_GET['tpp']] = $_GET['tpp'] ? "selected='selected'" : '';
	$tpp_options = "<option value='20' $select[20]>20</option><option value='50' $select[50]>50</option><option value='100' $select[100]>100</option>";
	$tpp = !empty($_GET['tpp']) ? $_GET['tpp'] : '20';
	$start_limit = ($page - 1) * $ppp;
	$dateline = $_GET['dateline'] ? $_GET['dateline'] : '604800';
	$dateline_options = '';
	foreach(array('all', '604800', '2592000', '7776000') as $v) {
		$selected = '';
		if($dateline == $v) {
			$selected = "selected='selected'";
		}
		$dateline_options .= "<option value=\"$v\" $selected>".cplang("dateline_$v");
	}
	$share_status = 1;
	if($_GET['filter'] == 'ignore') {
		$share_status = 2;
	}
	showformheader("moderate&operation=shares");
	showtableheader('search');

	showtablerow('', array('width="60"', 'width="160"', 'width="60"'),
		array(
			cplang('username'), "<input size=\"15\" name=\"username\" type=\"text\" value=\"$_GET[username]\" />",
			cplang('moderate_content_keyword'), "<input size=\"15\" name=\"keyword\" type=\"text\" value=\"$_GET[keyword]\" />",
		)
	);
	showtablerow('', array('width="60"', 'width="160"', 'width="60"'),
                array(
                        "$lang[perpage]",
                        "<select name=\"tpp\">$tpp_options</select><label><input name=\"showcensor\" type=\"checkbox\" class=\"checkbox\" value=\"yes\" ".($showcensor ? ' checked="checked"' : '')."/> $lang[moderate_showcensor]</label>",
                        "$lang[moderate_bound]",
                        "<select name=\"filter\">$filteroptions</select>
                        <select name=\"dateline\">$dateline_options</select>
                        <input class=\"btn\" type=\"submit\" value=\"$lang[search]\" />"
                )
        );

	showtablefooter();

	$pagetmp = $page;
	$sqlwhere = '';
	if(!empty($_GET['username'])) {
		$sqlwhere .= " AND s.username='{$_GET['username']}'";
	}
	if(!empty($dateline) && $dateline != 'all') {
		$sqlwhere .= " AND s.dateline>'".(TIMESTAMP - $dateline)."'";
	}
	if(!empty($_GET['keyword'])) {
		$keyword = str_replace(array('%', '_'), array('\%', '\_'), $_GET['keyword']);
		$sqlwhere .= " AND s.body_general LIKE '%$keyword%'";
	}
	$modcount = C::t('common_moderate')->count_by_search_for_share($moderatestatus, $_GET['username'], (($dateline &&  $dateline != 'all') ? (TIMESTAMP - $dateline) : null), $_GET['keyword']);
	do {
		$start_limit = ($pagetmp - 1) * $tpp;
		$sharearr = C::t('common_moderate')->fetch_all_by_search_for_share($moderatestatus, $_GET['username'], (($dateline &&  $dateline != 'all') ? (TIMESTAMP - $dateline) : null), $_GET['keyword'], $start_limit, $tpp);
		$pagetmp = $pagetmp - 1;
	} while($pagetmp > 0 && empty($sharearr));
	$page = $pagetmp + 1;
	$multipage = multi($modcount, $tpp, $page, ADMINSCRIPT."?action=moderate&operation=shares&filter=$filter&dateline={$_GET['dateline']}&username={$_GET['username']}&keyword={$_GET['keyword']}&tpp=$tpp&showcensor=$showcensor");

	echo '<p class="margintop marginbot"><a href="javascript:;" onclick="expandall();">'.cplang('moderate_all_expand').'</a> <a href="javascript:;" onclick="foldall();">'.cplang('moderate_all_fold').'</a></p>';

	showtableheader();
	$censor = & discuz_censor::instance();
	$censor->highlight = '#FF0000';
	require_once libfile('function/misc');
	foreach($sharearr as $share) {
		$short_desc = cutstr($share['body_general'], 30);
		$share['dateline'] = dgmdate($share['dateline']);
		if($showcensor) {
			$censor->check($short_desc);
			$censor->check($share['body_general']);
		}
		$share_censor_words = $censor->words_found;
		if(count($share_censor_words) > 3) {
			$share_censor_words = array_slice($share_censor_words, 0, 3);
		}
		$share['censorwords'] = implode(', ', $share_censor_words);
		$share['modkey'] = modauthkey($share['itemid']);

		if(count($share_censor_words)) {
			$share_censor_text = "<span style=\"color: red;\">({$share['censorwords']})</span>";
		} else {
			$share_censor_text = '';
		}

		$shareurl = '';
		switch($share['type']) {
			case 'thread':
				$shareurl = "forum.php?mod=viewthread&tid=$share[itemid]&modthreadkey=$share[modkey]";
				$sharetitle = lang('admincp', 'share_type_thread');
				break;
			case 'pic':
				$shareurl = "home.php?mod=space&uid=$share[fromuid]&do=album&picid=$share[itemid]&modpickey=$share[modkey]";
				$sharetitle = lang('admincp', 'share_type_pic');
				break;
			case 'space':
				$shareurl = "home.php?mod=space&uid=$share[itemid]";
				$sharetitle = lang('admincp', 'share_type_space');
				break;
			case 'blog':
				$shareurl = "home.php?mod=space&uid=$share[fromuid]&do=blog&id=$share[itemid]&modblogkey=$share[modkey]";
				$sharetitle = lang('admincp', 'share_type_blog');
				break;
			case 'album':
				$shareurl = "home.php?mod=space&uid=$share[fromuid]&do=album&id=$share[itemid]&modalbumkey=$share[modkey]";
				$sharetitle = lang('admincp', 'share_type_album');
				break;
			case 'article':
				$shareurl = "portal.php?mod=view&aid=$share[itemid]&modarticlekey=$share[modkey]";
				$sharetitle = lang('admincp', 'share_type_article');
				break;
		}
		showtagheader('tbody', '', true, 'hover');
		showtablerow("id=\"mod_$share[sid]_row1\"", array("id=\"mod_$share[sid]_row1_op\" rowspan=\"3\" class=\"rowform threadopt\" style=\"width:80px;\"", '', 'width="120"', 'width="120"', 'width="55"', 'width="55"'), array(
			"<ul class=\"nofloat\"><li><input class=\"radio\" type=\"radio\" name=\"moderate[$share[sid]]\" id=\"mod_$share[sid]_1\" value=\"validate\" onclick=\"mod_setbg($share[sid], 'validate');\"><label for=\"mod_$share[sid]_1\">$lang[validate]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$share[sid]]\" id=\"mod_$share[sid]_2\" value=\"delete\" onclick=\"mod_setbg($share[sid], 'delete');\"><label for=\"mod_$share[sid]_2\">$lang[delete]</label></li><li><input class=\"radio\" type=\"radio\" name=\"moderate[$share[sid]]\" id=\"mod_$doing[doid]_3\" value=\"ignore\" onclick=\"mod_setbg($share[sid], 'ignore');\"><label for=\"mod_$share[sid]_3\">$lang[ignore]</label></li></ul>",
			"<h3><a href=\"javascript:;\" onclick=\"display_toggle({$share[sid]});\">$short_desc $share_censor_text</a></h3>",
			$sharetitle,
			"<p><a target=\"_blank\" href=\"".ADMINSCRIPT."?action=members&operation=search&uid=$share[uid]&submit=yes\">$share[username]</a></p> <p>$share[dateline]</p>",
			"<a target=\"_blank\" href=\"$shareurl\">$lang[view]</a>",
		));

		showtablerow("id=\"mod_$share[sid]_row2\"", 'colspan="4" style="padding: 10px; line-height: 180%;"', '<div style="overflow: auto; overflow-x: hidden; max-height:120px; height:auto !important; height:100px; word-break: break-all;">'.$share['body_general'].'</div>');

		showtablerow("id=\"mod_$share[sid]_row3\"", 'class="threadopt threadtitle" colspan="4"', "<a href=\"?action=moderate&operation=shares&fast=1&sid=$share[sid]&moderate[$share[sid]]=validate&page=$page&frame=no\" target=\"fasthandle\">$lang[validate]</a> | <a href=\"?action=moderate&operation=shares&fast=1&sid=$share[sid]&moderate[$share[sid]]=delete&page=$page&frame=no\" target=\"fasthandle\">$lang[delete]</a> | <a href=\"?action=moderate&operation=shares&fast=1&sid=$share[sid]&moderate[$share[sid]]=ignore&page=$page&frame=no\" target=\"fasthandle\">$lang[ignore]</a>");
		showtagfooter('tbody');
	}

	showsubmit('modsubmit', 'submit', '', '<a href="#all" onclick="mod_setbg_all(\'validate\')">'.cplang('moderate_all_validate').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'delete\')">'.cplang('moderate_all_delete').'</a> &nbsp;<a href="#all" onclick="mod_setbg_all(\'ignore\')">'.cplang('moderate_all_ignore').'</a> &nbsp;<a href="#all" onclick="mod_cancel_all();">'.cplang('moderate_all_cancel').'</a>', $multipage, false);
	showtablefooter();
	showformfooter();

} else {

	$moderation = array('validate' => array(), 'delete' => array(), 'ignore' => array());
	$validates = $deletes = $ignores = 0;
	if(is_array($moderate)) {
		foreach($moderate as $sid => $act) {
			$moderation[$act][] = $sid;
		}
	}

	if(!empty($moderation['validate'])) {
		require_once libfile('function/feed');
		$validates = C::t('home_share')->update($moderation['validate'], array('status' => 0));
		foreach(C::t('home_share')->fetch_all($moderation['validate']) as $share) {
			switch($share['type']) {
				case 'thread':
					$feed_hash_data = 'tid' . $share['itemid'];
					$share['title_template'] = lang('spacecp', 'share_thread');
					break;
				case 'space':
					$feed_hash_data = 'uid' . $share['itemid'];
					$share['title_template'] = lang('spacecp', 'share_space');
					break;
				case 'blog':
					$feed_hash_data = 'blogid' . $share['itemid'];
					$share['title_template'] = lang('spacecp', 'share_blog');
					break;
				case 'album':
					$feed_hash_data = 'albumid' . $share['itemid'];
					$share['title_template'] =  lang('spacecp', 'share_album');
					break;
				case 'pic':
					$feed_hash_data = 'picid' . $share['itemid'];
					$share['title_template'] = lang('spacecp', 'share_image');
					break;
				case 'article':
					$feed_hash_data = 'articleid' . $share['itemid'];
					$share['title_template'] = lang('spacecp', 'share_article');
					break;
				case 'link':
					$feed_hash_data = '';
					break;
			}
			feed_add('share',
				'{actor} '.$share['title_template'],
				array('hash_data' => $feed_hash_data),
				$share['body_template'],
				dunserialize($share['body_data']),
				$share['body_general'],
				array($share['image']),
				array($share['image_link']),
				'',
				'',
				'',
				0,
				0,
				'',
				$share['uid'],
				$share['username']
			);
		}
		updatemoderate('sid', $moderation['validate'], 2);
	}

	if(!empty($moderation['delete'])) {
		require libfile('function/delete');
		$shares = deleteshares($moderation['delete']);
		$deletes = count($shares);
		updatemoderate('sid', $moderation['delete'], 2);
	}

	if($ignore_sids = dimplode($moderation['ignore'])) {
		$ignores = C::t('home_share')->update($moderation['ignore'], array('status' => 2));
		updatemoderate('sid', $moderation['ignore'], 1);
	}

	if($_GET['fast']) {
		echo callback_js($_GET['sid']);
		exit;
	} else {
		cpmsg('moderate_shares_succeed', "action=moderate&operation=shares&page=$page&filter=$filter&dateline={$_GET['dateline']}&username={$_GET['username']}&keyword={$_GET['keyword']}&tpp={$_GET['tpp']}&showcensor=$showcensor", 'succeed', array('validates' => $validates, 'ignores' => $ignores, 'deletes' => $deletes));
	}

}

?>