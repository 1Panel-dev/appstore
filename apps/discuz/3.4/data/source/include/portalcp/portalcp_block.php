<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portalcp_block.php 32281 2012-12-18 04:48:04Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include_once libfile('function/block');
$oparr = array('block', 'data', 'style', 'itemdata', 'setting', 'remove', 'item', 'additem', 'blockclass',
				'getblock', 'thumbsetting', 'push', 'recommend', 'verifydata', 'managedata',
				'saveblockclassname', 'saveblocktitle', 'convert', 'favorite', 'banids', 'delrecommend', 'moreurl');
$op = in_array($_GET['op'], $oparr, true) ? $_GET['op'] : 'block';
$_GET['from'] = $_GET['from'] == 'cp' ? 'cp' : null;
$allowmanage = $allowdata = 0;

$block = array();
$bid = !empty($_GET['bid']) ? intval($_GET['bid']) : 0;
if($bid) {
	if(!($block = C::t('common_block')->fetch($bid))) {
		showmessage('block_not_exist');
	}

	$blockstyle = array();
	if(!empty($block['styleid'])) {
		$blockstyle = block_getstyle($block['styleid']);
	} else {
		$blockstyle = dunserialize($block['blockstyle']);
	}
	$block['moreurl'] = $blockstyle['moreurl'] && in_array($block['blockclass'], array('forum_thread', 'portal_article', 'group_thread'), true) ? 1 : 0;

	$_G['block'][$bid] = $block;
	$blockperm = getblockperm($bid);
	if($blockperm['allowmanage']) {
		$allowmanage = 1;
		$allowdata = 1;
	}
	if ($blockperm['allowrecommend'] && !$blockperm['needverify']) {
		$allowdata = 1;
	}
}

if(empty($block['bid'])) {
	$bid = 0;
}

$_GET['classname'] = !empty($_GET['classname']) ? $_GET['classname'] : ($block ? $block['blockclass'] : 'html_html');
$theclass = block_getclass($_GET['classname'], true);
$theclass['script'] = isset($theclass['script']) ? $theclass['script'] : array();
if(!empty($_GET['styleid']) && isset($theclass['style'][$_GET['styleid']])) {
	$thestyle = $theclass['style'][$_GET['styleid']];
} elseif(isset($theclass['style'][$block['styleid']])) {
	$_GET['styleid'] = intval($block['styleid']);
	$thestyle = $theclass['style'][$_GET['styleid']];
} else {
	$_GET['styleid'] = 0;
	$thestyle = (array)dunserialize($block['blockstyle']);
}
$_GET['script'] = !empty($_GET['script']) && isset($theclass['script'][$_GET['script']])
		? $_GET['script']
		: (!empty($block['script']) ? $block['script'] : key($theclass['script']));

$blocktype = (!empty($_GET['blocktype']) || !empty($block['blocktype'])) ? 1 : 0;
$nocachetime = in_array($_GET['script'], array('blank', 'line', 'banner', 'vedio', 'google')) ? true : false;
$is_htmlblock = ($_GET['classname'] == 'html_html') ? 1 : 0;
$showhtmltip = false;
if($op == 'data' && $is_htmlblock) {
	$op = 'block';
	$showhtmltip = true;
}
$block['blockclass'] = empty($block['blockclass']) ? $_GET['classname'] : $block['blockclass'];
$is_recommendable = block_isrecommendable($block);

if($op == 'block') {
	if($bid && !$allowmanage) {
		showmessage('block_edit_nopermission');
	}
	if(!$bid) {
		list($tpl, $id) = explode(':', $_GET['tpl']);
		if(trim($tpl)=='portal/portal_topic_content') {
			if(!$_G['group']['allowaddtopic'] && !$_G['group']['allowmanagetopic']) {
				showmessage('block_topic_nopermission');
			}
		} elseif(!$_G['group']['allowdiy']) {
			showmessage('block_add_nopermission');
		}
	}

	if(submitcheck('blocksubmit')) {
		$_POST['cachetime'] = intval($_POST['cachetime']) * 60;
		$_POST['styleid'] = intval($_POST['styleid']);
		$_POST['shownum'] = intval($_POST['shownum']);
		$_POST['picwidth'] = $_POST['picwidth'] ? intval($_POST['picwidth']) : 0;
		$_POST['picheight'] = $_POST['picheight'] ? intval($_POST['picheight']) : 0;
		$_POST['script'] = isset($theclass['script'][$_POST['script']]) ? $_POST['script'] : key($theclass['script']);
		$_POST['a_target'] = in_array($_POST['a_target'], array('blank', 'top', 'self')) ? $_POST['a_target'] : 'blank';
		$_POST['dateformat'] = in_array($_POST['dateformat'], array('Y-m-d', 'm-d', 'H:i', 'Y-m-d H:i')) ? $_POST['dateformat'] : 'Y-m-d';
		$_POST['isblank'] = intval($_POST['isblank']);
		$_POST['cachetimerangestart'] = intval($_POST['cachetimerangestart']);
		$_POST['cachetimerangeend'] = intval($_POST['cachetimerangeend']);
		$summary = getstr($_POST['summary'], '', 0, 0, 0, 1);
		if($summary) {
			$tag = block_ckeck_summary($summary);
			if($tag != $summary) {
				$msg = lang('portalcp', 'block_diy_summary_html_tag').$tag.lang('portalcp', 'block_diy_summary_not_closed');
				showmessage($msg);
			}
		}

		$_POST['shownum'] = $_POST['shownum'] > 0 ? $_POST['shownum'] : 10;
		$_POST['parameter']['items'] = $_POST['shownum'];
		$cachetimerange = $_POST['cachetimerangestart'].','.$_POST['cachetimerangeend'];
		if(empty($_G['setting']['blockcachetimerange'])) {
			$cachetimerange = $cachetimerange == '0,23' ? '' : $cachetimerange;
		} else {
			$cachetimerange = $cachetimerange == $_G['setting']['blockcachetimerange'] ? '' : $cachetimerange;
		}
		include_once libfile('function/home');
		$setarr = array(
			'name' => getstr($_POST['name'], 255),
			'summary' => $summary,
			'styleid' => $_POST['styleid'],
			'script' => $_POST['script'],
			'cachetime' => intval($_POST['cachetime']),
			'cachetimerange' => $cachetimerange,
			'punctualupdate' => !empty($_POST['punctualupdate']) ? '1' : '0',
			'shownum' => $_POST['shownum'],
			'picwidth' => $_POST['picwidth'] && $_POST['picwidth'] > 8 && $_POST['picwidth'] < 1960 ? $_POST['picwidth'] : 0,
			'picheight' => $_POST['picheight'] && $_POST['picheight'] > 8 && $_POST['picheight'] < 1960 ? $_POST['picheight'] : 0,
			'target' => $_POST['a_target'],
			'dateuformat' => !empty($_POST['dateuformat']) ? '1' : '0',
			'dateformat' => $_POST['dateformat'],
			'hidedisplay' => $_POST['hidedisplay'] ? '1' : '0',
			'dateline' => TIMESTAMP,
			'isblank' => $_POST['isblank']
		);

		$picdata = array();
		if(!empty($_FILES)) {
			foreach($_FILES as $varname => $file) {
				if($file['tmp_name']) {
					$result = pic_upload($file, 'portal');
					$pic = 'portal/'.$result['pic'];
					$picdata[] = array('bid' => $bid, 'pic' => $pic, 'picflag' =>$result['remote'] , 'type' => '1');
					$pic = $result['remote'] ? $_G['setting']['ftp']['attachurl'].$pic : $_G['setting']['attachurl'].$pic;
					$_POST['parameter'][$varname] = $pic;
				}
			}
		}

		if(($block['blockclass'] == 'html_html' || $_GET['classname'] == 'html_html') && $_POST['script'] == 'blank' && isset($_POST['parameter']['content'])) {
			$_POST['parameter']['content'] = addslashes($_POST['parameter']['content']);
		}

		$parameter = $_POST['parameter'];
		if(isset($block['param'])) {
			$blockobj = block_script($block['blockclass'], $block['script']);
			if($blockobj) {
				$_block_setting = $blockobj->getsetting();
				foreach($block['param'] as $_key => $_val) {
					if(!isset($parameter[$_key]) && (!isset($_block_setting[$_key]) || (isset($_block_setting[$_key]) && $_block_setting[$_key]['type'] !== 'mcheckbox'))) {
						$parameter[$_key] = $_val;
					}
				}
			} else {
				$parameter = $parameter + $block['param'];
			}
		}
		$setarr['param'] = serialize($parameter);

		if($bid) {
			C::t('common_block')->update($bid, $setarr);
		} else {
			$setarr['blockclass'] = $_GET['classname'];
			$setarr['uid'] = $_G['uid'];
			$setarr['username'] = $_G['username'];
			$setarr['notinherited'] = 0;
			if($blocktype == 1) {
				$setarr['blocktype'] = '1';
			}
			$bid = C::t('common_block')->insert($setarr, true);
		}

		if(!empty($picdata)) {
			C::t('common_block_pic')->insert_by_bid($bid, $picdata);
		}

		$_G['block'][$bid] = C::t('common_block')->fetch($bid);
		block_updatecache($bid, true);
		showmessage('do_success', 'portal.php?mod=portalcp&ac=block&op=block&bid='.$bid, array('bid'=>$bid, 'eleid'=> $_GET['eleid']));
	}

	loadcache('blockconvert');
	$block['script'] = isset($block['script']) ? $block['script'] : $_GET['script'];

	if($block['blockclass'] == 'html_html' && $block['script'] == 'blank'){
		$block['param']['content'] = stripslashes($block['param']['content']);
	}

	$settings = block_setting($_GET['classname'], $block['script'], $block['param']);
	$scriptarr = array($block['script'] => ' selected');
	$stylearr = array($_GET['styleid'] => ' selected');

	$block = block_checkdefault($block);
	$cachetimearr = array($block['cachetime'] =>' selected="selected"');
	$block['cachetime_min'] = intval($block['cachetime'] / 60);
	$targetarr[$block['target']] = ' selected';
	$block['cachetimerange'] = empty($block['cachetimerange']) ? (isset($_G['setting']['blockcachetimerange']) ? $_G['setting']['blockcachetimerange'] : '') : $block['cachetimerange'];
	$block['cachetimerange'] = empty($block['cachetimerange']) ? array('0', '23') : explode(',', $block['cachetimerange']);
	$cachetimerange = range(0, 23);
	$dateformats = block_getdateformats($block['dateformat']);

	$block['summary'] = dhtmlspecialchars($block['summary']);
	$blockclassname = '';
	$blockclass = $block['blockclass'] ? $block['blockclass'] : $_GET['classname'];
	$arr = explode('_', $blockclass);
	if(count($arr) == 2) {
		$blockclassname = $_G['cache']['blockclass'][$arr[0]]['subs'][$blockclass]['name'];
	}
	$blockclassname = empty($blockclassname) ? $blockclass : $blockclassname;

} elseif($op == 'banids') {
	if(!$bid || (!$allowmanage && !$allowdata)) {
		showmessage('block_edit_nopermission');
	}

	if(isset($_GET['bannedids']) && $block['param']['bannedids'] != $_GET['bannedids']) {
		$arr = explode(',', $_GET['bannedids']);
		$arr = array_map('intval', $arr);
		$arr = array_filter($arr);
		$_GET['bannedids'] = implode(',', $arr);
		$block['param']['bannedids'] = $_GET['bannedids'];
		C::t('common_block')->update($bid, array('param'=>serialize($block['param'])));
		$_G['block'][$bid] = $block;
		block_updatecache($bid, true);
	}

	showmessage('do_success', 'portal.php?mod=portalcp&ac=block&op=data&bid='.$bid, array('bid'=>$bid, 'eleid'=> $_GET['eleid']));

} elseif($op == 'data') {
	if(!$bid || (!$allowmanage && !$allowdata)) {
		showmessage('block_edit_nopermission');
	}

	if(submitcheck('updatesubmit')) {
		if($_POST['displayorder']) {
			asort($_POST['displayorder']);
			$orders = $ids = array();
			$order = 1;
			foreach($_POST['displayorder'] as $k=>$v) {
				$k = intval($k);
				$ids[] = $k;
				$orders[$k] = $order;
				$order++;
			}
			$items = array();
			foreach(C::t('common_block_item')->fetch_all($ids) as $value) {
				if($value['bid'] == $bid) {
					$items[$value['itemid']] = $value;
				}
			}
			foreach($items as $key=>$value) {
				$itemtype = !empty($_POST['locked'][$key]) ? '1' : '0';
				if($orders[$key] != $value['displayorder'] || $itemtype != $value['itemtype']) {
					C::t('common_block_item')->update($key, array('displayorder'=>$orders[$key], 'itemtype'=>$itemtype));
				}
			}
		}
		showmessage('do_success', 'portal.php?mod=portalcp&ac=block&op=data&bid='.$bid, array('bid'=>$bid, 'eleid'=> $_GET['eleid']));
	}

	$itemlist = array();
	if($bid) {
		$preorders = array();
		foreach(C::t('common_block_item')->fetch_all_by_bid($bid, true) as $value) {
			if($value['itemtype']==1 && $value['enddate'] && $value['enddate'] <= TIMESTAMP) {
				continue;
			}
			$value['ispreorder'] = false;
			if($value['itemtype']==1) {
				if($value['startdate'] > TIMESTAMP) {
					$value['ispreorder'] = true;
				} else {
					$preorders[$value['displayorder']] = $value['itemid'];
				}
			}
			$value['itemtypename'] = lang('portalcp', 'itemtypename'.$value['itemtype']);
			$itemlist[$value['itemid']] = $value;
		}
		if($preorders) {
			foreach($itemlist as $key=>$value) {
				if(isset($preorders[$value['displayorder']]) && $value['itemid'] != $preorders[$value['displayorder']]) {
					unset($itemlist[$key]);
				}
			}
		}
	}

	$block['param']['bannedids'] = !empty($block['param']['bannedids']) ? $block['param']['bannedids'] : '';

} elseif($op == 'style') {
	if(!$bid || !$allowmanage) {
		showmessage('block_edit_nopermission');
	}

	if(submitcheck('stylesubmit')) {
		$_POST['name'] = trim($_POST['name']);
		$arr = array(
			'name' => $_POST['name'],
			'blockclass' => $_GET['classname'],
		);
		$_POST['template'] = $_POST['template'];

		include_once libfile('function/block');
		block_parse_template($_POST['template'], $arr);
		if(!empty($_POST['name'])) {
			$styleid = C::t('common_block_style')->insert($arr, true);
		}
		$arr['fields'] = dunserialize($arr['fields']);
		$arr['template'] = dunserialize($arr['template']);
		$arr = serialize($arr);
		C::t('common_block')->update($bid, array('blockstyle'=>$arr, 'styleid'=>'0'));

		showmessage('do_success', 'portal.php?mod=portalcp&ac=block&op=style&bid='.$bid, array('bid'=>$bid, 'eleid'=> $_GET['eleid']));
	}

	$template = block_build_template($blockstyle['template']);

	$samplecode = '';
	if($block['hidedisplay']) {
		$samplecode = '<ul>\n'
			.'<!--{loop $_G[block_1] $key $value}-->\n'
			.'<li><a href="$value[url]">$value[title]</a></li>\n'
			.'<!--{/loop}-->\n'
			.'</ul>';
		$samplecode = dhtmlspecialchars($samplecode);
		$samplecode = str_replace('\n', '<br />', $samplecode);
	}

} elseif($op == 'itemdata') {

	if(!$bid ||  (!$allowmanage && !$allowdata)) {
		showmessage('block_edit_nopermission');
	}
	if(!$is_recommendable) {
		showmessage('block_no_recommend_library');
	}

	$theurl = 'portal.php?mod=portalcp&ac=block&op=itemdata';
	$perpage = 20;
	$page = max(1,intval($_GET['page']));
	$start = ($page-1)*$perpage;
	if($start<0) $start = 0;

	if(submitcheck('deletesubmit')) {
		if(!empty($_POST['ids'])) {
			C::t('common_block_item_data')->delete_by_dataid_bid($_POST['ids'], $bid);
		}

		$displayorder = array_map('intval', $_POST['displayorder']);
		foreach($displayorder  as $dataid => $displayorder) {
			if($displayorder !== intval($_POST['olddisplayorder'][$dataid])) {
				C::t('common_block_item_data')->update($dataid, array('displayorder' => $displayorder));
			}
		}
		showmessage('do_success', "portal.php?mod=portalcp&ac=block&op=itemdata&bid=$bid&page=$page");
	}

	$count = C::t('common_block_item_data')->count_by_bid($bid);
	$multi = '';
	$datalist = array();
	if($count) {
		$datalist = C::t('common_block_item_data')->fetch_all_by_bid($bid, 1, $start, $perpage);
		$multi = multi($count, $perpage, $page, "portal.php?mod=portalcp&ac=block&bid=$bid&op=itemdata");
	}

} elseif($op == 'setting') {

	if(($bid && !$allowmanage)) {
		showmessage('block_edit_nopermission');
	}

	$settings = array();
	if($theclass['script'][$_GET['script']]) {
		$settings = block_setting($_GET['classname'], $_GET['script'], $block['param']);
	}

	$block['script'] = isset($block['script']) ? $block['script'] : $_GET['script'];
	$scriptarr = array($block['script'] => ' selected');
	$stylearr = array($_GET['styleid'] => ' selected');

	$block = block_checkdefault($block);
	$cachetimearr = array($block['cachetime'] =>' selected="selected"');
	$block['cachetime_min'] = intval($block['cachetime'] / 60);
	$targetarr[$block['target']] = ' selected';

} elseif($op == 'thumbsetting') {

	if(($bid && !$allowmanage)) {
		showmessage('block_edit_nopermission');
	}

	$block = block_checkdefault($block);
	$cachetimearr = array($block['cachetime'] =>' selected="selected"');
	$block['cachetime_min'] = intval($block['cachetime'] / 60);
	$targetarr[$block['target']] = ' selected';

	$dateformats = block_getdateformats($block['dateformat']);

} elseif($op == 'remove') {

	if(!$bid || (!$allowmanage && !$allowdata)) {
		showmessage('block_edit_nopermission');
	}

	if($_GET['itemid']) {
		$_GET['itemid'] = intval($_GET['itemid']);
		if(($item = C::t('common_block_item')->fetch($_GET['itemid'])) && $item['bid'] == $bid) {
			C::t('common_block_item')->delete($_GET['itemid']);
			if($item['itemtype'] != '1') {
				block_ban_item($block, $item);
			}
			block_updatecache($bid, true);
		}
	}
	showmessage('do_success', "portal.php?mod=portalcp&ac=block&op=data&bid=$bid", array('bid'=>$bid));

} elseif( in_array($op, array('item', 'additem', 'push', 'recommend', 'verifydata', 'managedata'))) {

	if(!$bid) {
		showmessage('block_edit_nopermission');
	}

	$itemid = $_GET['itemid'] ? intval($_GET['itemid']) : 0;
	$dataid = $_GET['dataid'] ? intval($_GET['dataid']) : 0;
	$_GET['id'] = intval($_GET['id']);
	$_GET['idtype'] = preg_replace('/[^\w]/', '', $_GET['idtype']);

	$item = $perm = array();
	if($op == 'item' || $op == 'additem') {
		if(!$allowmanage && !$allowdata) {
			showmessage('block_edit_nopermission');
		}
		if($itemid && ($item = C::t('common_block_item')->fetch($itemid))) {
			$item['fields'] = dunserialize($item['fields']);
		}
	} elseif($op == 'push') {

		$item = get_push_item($block, $thestyle, $_GET['id'], $_GET['idtype']);
		if($itemid) {
			$item['itemid'] = $itemid;
		}
	} elseif($op == 'recommend') {
		$perm = getblockperm($bid);
		if(!$perm['allowmanage'] && !$perm['allowrecommend']) {
			showmessage('block_no_right_recommend');
		}

		$isrepeatrecommend = false;
		$idtype = $_GET['idtype'] == 'gtid' ? 'tid' : $_GET['idtype'];
		if(($item =  C::t('common_block_item_data')->fetch_by_bid_id_idtype($bid, $_GET['id'], $idtype))) {
			$item['fields'] = dunserialize($item['fields']);
			$isrepeatrecommend = true;

			if(!$perm['allowmanage'] && $item['uid'] != $_G['uid']) {
				showmessage('data_in_mod_library', null, null, array('striptags' => false));
			}

		} else {
			if(in_array($_GET['idtype'],array('tid', 'gtid', 'aid', 'picid', 'blogid'))) {
				$_GET['idtype'] = $_GET['idtype'] == 'gtid' ? 'tids' : $_GET['idtype'].'s';
			}
			$item = get_push_item($block, $thestyle, $_GET['id'], $_GET['idtype'], $block['blockclass'], $block['script']);
			if(empty($item)) showmessage('block_data_type_invalid', null, null, array('msgtype'=>3));
		}
	} elseif($op=='verifydata' || $op=='managedata') {
		if(!$allowmanage && !$allowdata) {
			showmessage('no_right_manage_data');
		}
		if($dataid) {
			$item = C::t('common_block_item_data')->fetch($dataid);
			$item['fields'] = dunserialize($item['fields']);
		}
	}

	if(!$item && $op != 'additem') {
		showmessage('block_edit_nopermission');
	}

	$item['oldpic'] = $item['pic'];
	if($item['picflag'] == '1') {
		$item['pic'] = $item['pic'] ? $_G['setting']['attachurl'].$item['pic'] : '';
	} elseif($item['picflag'] == '2') {
		$item['pic'] = $item['pic'] ? $_G['setting']['ftp']['attachurl'].$item['pic'] : '';
	}

	$item['startdate'] = $item['startdate'] ? dgmdate($item['startdate']) : dgmdate(TIMESTAMP);
	$item['enddate'] = $item['enddate'] ? dgmdate($item['enddate']) : '';
	$orders = range(1, $block['shownum']);
	$orderarr[$item['displayorder']] = ' selected="selected"';
	$item['showstyle'] = !empty($item['showstyle']) ? (array)(dunserialize($item['showstyle'])) : (!empty($item['fields']['showstyle']) ? $item['fields']['showstyle'] : array());
	$showstylearr = array();
	foreach(array('title_b', 'title_i', 'title_u', 'title_c', 'summary_b', 'summary_i', 'summary_u', 'title_c') as $value) {
		if(!empty($item['showstyle'][$value])) {
			$showstylearr[$value] = 'class="a"';
		}
	}

	$itemfields = $blockitem = $item;
	unset($itemfields['fields']);
	$item['fields'] = (array)$item['fields'];
	foreach($item['fields'] as $key=>$value) {
		if($theclass['fields'][$key]) {
			switch($theclass['fields'][$key]['datatype']) {
				case 'date':
					$itemfields[$key] = dgmdate($value, 'Y-m-d H:i:s');
					break;
				case 'int':
					$itemfields[$key] = intval($value);
					break;
				case 'string':
					$itemfields[$key] = dhtmlspecialchars($value);
					break;
				default:
					$itemfields[$key] = $value;
			}
		}
	}

	$showfields = array();
	if(empty($thestyle['fields'])) {
		$template = block_build_template($thestyle['template']);
		$thestyle['fields'] = block_parse_fields($template);
		C::t('common_block_style')->update(intval($thestyle['styleid']), array('fields'=>serialize($thestyle['fields'])));
	}
	foreach($thestyle['fields'] as $fieldname) {
		$showfields[$fieldname] = "1";
	}

	if(submitcheck('itemsubmit') || submitcheck('recommendsubmit') || submitcheck('verifydatasubmit') || submitcheck('managedatasubmit')) {
		$item['bid'] = $block['bid'];
		$item['displayorder'] = intval($_POST['displayorder']);
		$item['startdate'] = !empty($_POST['startdate']) ? strtotime($_POST['startdate']) : 0;
		$item['enddate'] = !empty($_POST['enddate']) ? strtotime($_POST['enddate']) : 0;
		$item['itemtype'] = !empty($_POST['locked']) ? '1' : '2';
		$item['title'] = dhtmlspecialchars($_POST['title']);
		$item['url'] = $_POST['url'];
		$block['param']['summarylength'] = empty($block['param']['summarylength']) ? 80 : $block['param']['summarylength'];
		$block['param']['titlelength'] = empty($block['param']['titlelength']) ? 40 : $block['param']['titlelength'];
		$item['summary'] = cutstr($_POST['summary'], $block['param']['summarylength'], '');
		if($_FILES['pic']['tmp_name']) {
			$result = pic_upload($_FILES['pic'], 'portal');
			$item['pic'] = 'portal/'.$result['pic'];
			$item['picflag'] = $result['remote'] ? '2' : '1';
			$item['makethumb'] = 0;
			$item['thumbpath'] = '';
			$thumbdata = array('bid' => $block['bid'], 'itemid' => $item['itemid'], 'pic' => $item['pic'], 'picflag' => $result['remote'], 'type' => '1');
			C::t('common_block_pic')->insert($thumbdata);
		} elseif($_POST['pic']) {
			$pic = dhtmlspecialchars($_POST['pic']);
			$urls = parse_url($pic);
			if(!empty($urls['scheme']) && !empty($urls['host'])) {
				$item['picflag'] = '0';
				$item['thumbpath'] = '';
			} else {
				$item['picflag'] = intval($_POST['picflag']);
			}
			if($item['pic'] != $pic) {
				$item['pic'] = $pic;
				$item['makethumb'] = 0;
				$item['thumbpath'] = block_thumbpath($block, $item);
			}
		}
		unset($item['oldpic']);
		$item['showstyle'] = $_POST['showstyle']['title_b'] || $_POST['showstyle']['title_i'] || $_POST['showstyle']['title_u'] || $_POST['showstyle']['title_c'] ? $_POST['showstyle'] : array();
		$item['showstyle'] = empty($item['showstyle']) ? '' : serialize($item['showstyle']);

		foreach($theclass['fields'] as $key=>$value) {
			if(!isset($item[$key]) && isset($_POST[$key])) {
				if($value['datatype'] == 'int') {
					$_POST[$key] = intval($_POST[$key]);
				} elseif($value['datatype'] == 'date') {
					$_POST[$key] = strtotime($_POST[$key]);
				} else {
					$_POST[$key] = $_POST[$key];
				}
				$item['fields'][$key] = $_POST[$key];
			}
		}
		if(isset($item['fields']['fulltitle'])) {
			$item['fields']['fulltitle'] = $item['title'];
		}
		$item['fields']	= serialize($item['fields']);

		$item['title'] = cutstr($item['title'], $block['param']['titlelength'], '');

		if($_POST['icflag']) {
			$item['makethumb'] = 1;
			$item['thumbpath'] = block_thumbpath($block, $item);
		}
		if(submitcheck('itemsubmit')) {

			if($op == 'additem' && !$item['id']) {
				$item['id'] = ($pushid = intval($_POST['push_id'])) ? $pushid : mt_rand(1,9999);
				$item['idtype'] = 'rand';
			}
			if($item['startdate'] > $_G['timestamp']) {
				C::t('common_block_item')->insert($item, false, true);
			} elseif(empty($item['enddate']) || $item['enddate'] > $_G['timestamp']) {
				C::t('common_block_item')->delete_by_bid_displayorder($bid, $item['displayorder']);
				C::t('common_block_item')->insert($item, false, true);
			} else {
				C::t('common_block_item')->delete_by_itemid_bid($item['itemid'], $bid);
			}
			block_updatecache($bid, true);
			showmessage('do_success', 'portal.php?mod=portalcp&ac=block&op=data&bid='.$block['bid'], array('bid'=>$bid));

		} elseif(submitcheck('recommendsubmit')) {
			include_once libfile('function/home');
			$thumbpath = $item['thumbpath'];
			unset($item['itemid']);
			unset($item['thumbpath']);
			$item['itemtype'] = '0';
			$item['uid'] = $_G['uid'];
			$item['username'] = $_G['username'];
			$item['dateline'] = TIMESTAMP;
			$item['isverified'] = empty($_POST['needverify']) && ($perm['allowmanage'] || empty($perm['needverify'])) ? '1' : '0';
			$item['verifiedtime'] = TIMESTAMP;

			C::t('common_block_item_data')->insert($item, false, true);
			if($_GET['showrecommendtip'] && (in_array($_GET['idtype'], array('tids', 'tid', 'gtid')))) {
				$modarr = array(
					'tid' => $item['id'],
					'uid' => $item['uid'],
					'username' => $item['username'],
					'dateline' => TIMESTAMP,
					'action' => 'REB',
					'status' => '1',
					'stamp' => '',
					'reason' => getstr($_GET['recommendto'], 20),
				);
				C::t('forum_threadmod')->insert($modarr);
				$data = array('moderated' => 1);
				loadcache('stamptypeid');
				if(array_key_exists(4, $_G['cache']['stamptypeid'])) {
					$data['stamp'] = $_G['cache']['stamptypeid']['4'];
				}
				C::t('forum_thread')->update($item['id'], $data);
			}
			if($_POST['icflag'] && !(C::t('common_block_pic')->count_by_bid_pic($block['bid'], $thumbpath))) {
				$picflag = 0; //common_block_pic表中的picflag标识（0本地，1远程）
				if($_G['setting']['ftp']['on'] && !empty($_G['setting']['ftp']['host'])) {
					$ftp = & discuz_ftp::instance();
					$ftp->connect();
					if(($ftp->connectid && $ftp->ftp_size($thumbpath) > 0) || (ftpperm(fileext($thumbpath), filesize($_G['setting']['attachdir'].'./'.$thumbpath)) && $ftp->upload($_G['setting']['attachurl'].'/'.$thumbpath, $thumbpath))) {
						$picflag = 1; //common_block_pic表中的picflag标识（0本地，1远程）
						@unlink($_G['setting']['attachdir'].'./'.$thumbpath);
					}
				}

				$thumbdata = array('bid' => $block['bid'], 'itemid' => 0, 'pic' => $thumbpath, 'picflag' => $picflag, 'type' => '0');
				C::t('common_block_pic')->insert($thumbdata);
			}
			if(!empty($_POST['updateblock'])) {
				block_updatecache($bid, true);
			}

			if(($_G['group']['reasonpm'] == 2 || $_G['group']['reasonpm'] == 3) || !empty($_GET['sendreasonpm'])) {
				$sendreasonpm = 1;
			} else {
				$sendreasonpm = 0;
			}
			if($sendreasonpm) {
				require_once libfile('function/misc');
				if((in_array($_GET['idtype'], array('tids', 'tid', 'gtid')))) {
					$sendreasonpmcontent = C::t('forum_thread')->fetch($item['id']);
					sendreasonpm($sendreasonpmcontent, 'recommend_note_post', array(
						'tid' => $item['id'],
						'subject' => $sendreasonpmcontent['subject'],
						'from_id' => 0,
						'from_idtype' => 'recommend'
					));
				}
			}

			$showrecommendrate = '';
			if($_G['group']['raterange'] && (in_array($_GET['idtype'], array('tids', 'tid', 'gtid')))) {
				$showrecommendrate = 1;
			}
			if($showrecommendrate) {
				showmessage('do_success', dreferer('portal.php'), array(), array('showdialog' => true, 'closetime' => 0.01, 'extrajs' =>
					'<script type="text/javascript" reload="1">
					showWindow("rate", "forum.php?mod=misc&action=rate&tid='.$item[id].'&pid='.$_GET[recommend_thread_pid].'&showratetip=1", "get", -1);
					</script>'));
			} elseif($_GET['showrecommendtip']) {
				showmessage('do_success', dreferer('portal.php'), array(), array('showdialog' => true, 'closetime' => true, 'extrajs' =>
					'<script type="text/javascript" reload="1">
					window.location.reload();
					</script>'));
			} else {
				showmessage('do_success', dreferer('portal.php'), array(), array('showdialog' => true, 'closetime' => true));
			}
		} elseif(submitcheck('verifydatasubmit')) {
			unset($item['thumbpath']);
			$item['isverified'] = '1';
			$item['verifiedtime'] = TIMESTAMP;
			C::t('common_block_item_data')->update($dataid, $item);
			if(!empty($_POST['updateblock'])) {
				block_updatecache($bid, true);
			}
			showmessage('do_success', dreferer('portal.php?mod=portalcp&ac=portalblock&op=verifieddata&searchkey=%23'.$bid));
		} elseif(submitcheck('managedatasubmit')) {
			unset($item['thumbpath']);
			$item['stickgrade'] = intval($_POST['stickgrade']);
			C::t('common_block_item_data')->update($dataid, $item);
			showmessage('do_success', dreferer('portal.php?mod=portalcp&ac=block&op=itemdata&bid='.$bid));
		}
	}
	if(in_array($block['blockclass'], array('forum_thread', 'portal_article', 'group_thread', 'space_blog'), true)) {
		$picdatas = array();
		$prefix = ($item['picflag'] == 2 ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']);
		$itemfields['pics'] = array();
		$first = true;

		if(empty($_GET['idtype'])) {
			$_GET['idtype'] = $itemfields['idtype'].'s';
			$_GET['id'] = $itemfields['id'];
		}

		if(in_array($_GET['idtype'], array('tids', 'tid'))) {
			$prefix .= 'forum/';
			$firstpost = C::t('forum_post')->fetch_threadpost_by_tid_invisible($_GET['id']);
			foreach(C::t('forum_attachment_n')->fetch_all_by_pid_width('pid:'.$firstpost['pid'], $firstpost['pid'], $block['picwidth']) as $pic) {
				if($first) {
					$first = false;
					$itemfields['pics'][0] = '';
					if(strpos($itemfields['oldpic'], 'nophoto.gif') !== false) {
						$itemfields['oldpic'] = 'forum/'.$pic['attachment'];
					}
				}
				$thumb = $prefix.($pic['thumb'] ? getimgthumbname($pic['attachment']) : $pic['attachment']);
				if('forum/'.$pic['attachment'] == $itemfields['oldpic']) {
					$itemfields['pics'][0] = array('big' => $prefix.$pic['attachment'], 'thumb' => $thumb, 'attachment' => 'forum/'.$pic['attachment'], 'first' => 1);
				} else {
					$itemfields['pics'][] = array('big' => $prefix.$pic['attachment'], 'thumb' => $thumb, 'attachment' => 'forum/'.$pic['attachment'], 'first' => 0);
				}
			}
			if(empty($itemfields['pics'][0])) {
				unset($itemfields['pics'][0]);
			}
		} elseif($_GET['idtype'] == 'aids') {
			$prefix .= 'portal/';
			foreach(C::t('portal_attachment')->fetch_all_by_aid($_GET['id']) as $pic) {
				if($first) {
					$first = false;
					$itemfields['pics'][0] = '';
					if(strpos($itemfields['oldpic'], 'nophoto.gif') !== false) {
						$itemfields['oldpic'] = 'portal/'.$pic['attachment'];
					}
				}
				$thumb = $prefix.($pic['thumb'] ? getimgthumbname($pic['attachment']) : $pic['attachment']);
				if('portal/'.$pic['attachment'] == $itemfields['oldpic']) {
					$itemfields['pics'][0] = array('big' => $prefix.$pic['attachment'], 'thumb' => $thumb, 'attachment' => 'portal/'.$pic['attachment'], 'first' => 1);
				} else {
					$itemfields['pics'][] = array('big' => $prefix.$pic['attachment'], 'thumb' => $thumb, 'attachment' => 'portal/'.$pic['attachment'], 'first' => 0);
				}
			}
			if(empty($itemfields['pics'][0])) {
				unset($itemfields['pics'][0]);
			}
		} elseif($_GET['idtype'] == 'blogids') {
			$itemfields['pics'][] = array('big' => $itemfields['pic'], 'thumb' => 1, 'attachment' => $itemfields['oldpic']);
		}

	}

} elseif ($op == 'getblock') {

	if(!$bid || (!$allowmanage && !$allowdata)) {
		showmessage('block_edit_nopermission');
	}

	block_get_batch($bid);
	if(!empty($_GET['forceupdate'])) block_updatecache($bid, !empty($_GET['forceupdate']));
	if(strexists($block['summary'], '<script')) {
		$block['summary'] = lang('portalcp', 'block_diy_nopreview');
		$_G['block'][$bid] = $block;
		$_G['block'][$bid]['cachetime'] = 0;
		$_G['block'][$bid]['nocache'] = true;
	}
	$html = block_fetch_content($bid, $block['blocktype']);

} elseif ($op == 'saveblockclassname') {

	if(!$bid || !$allowmanage) {
		showmessage('block_edit_nopermission');
	}

	if (submitcheck('saveclassnamesubmit')) {
		$setarr = array('classname'=>getstr($_POST['classname'], 100, 0, 0, 0, -1));
		C::t('common_block')->update($bid, $setarr);
	}
	C::t('common_block')->clear_cache($bid);

	showmessage('do_success');
} elseif ($op == 'saveblocktitle') {

	if(!$bid || !$allowmanage) {
		showmessage('block_edit_nopermission');
	}

	if (submitcheck('savetitlesubmit')) {
		$_POST['title'] = preg_replace('/\<script|\<iframe|\<\/iframe\>/is', '', $_POST['title']);
		$title = $_POST['title'];
		$title = preg_replace('/url\([\'"](.*?)[\'"]\)/','url($1)',$title);

		$_G['siteurl'] = str_replace(array('/','.'),array('\/','\.'),$_G['siteurl']);
		$title = preg_replace('/\"'.$_G['siteurl'].'(.*?)\"/','"$1"',$title);

		$setarr = array('title'=>$title);
		C::t('common_block')->update($bid, $setarr);
	}

	C::t('common_block')->clear_cache($bid);

	showmessage('do_success');
} elseif ($op == 'convert') {

	if(!$bid || !$allowmanage) {
		showmessage('block_edit_nopermission');
	}
	block_convert($bid, $_GET['toblockclass']);
} elseif ($op == 'favorite') {
	$perm = getblockperm($bid);
	if(!$perm['allowmanage'] && !$perm['allowrecommend']) {
		showmessage('block_no_right_recommend');
	}
	$favoriteop = '';
	if(!block_check_favorite($_G['uid'], $bid)) {
		$setarr = array(
			'uid' => $_G['uid'],
			'bid' => $bid,
		);
		block_add_favorite($setarr);
		$favoriteop = 'add';
	} else {
		block_delete_favorite($_G['uid'], $bid);
		$favoriteop = 'del';
	}
} elseif($op == 'delrecommend') {
	$perm = getblockperm($bid);
	if(!$perm['allowmanage'] && !$perm['allowrecommend']) {
		showmessage('block_no_right_recommend');
	}
	if(($_GET['dataid'] = dintval($_GET['dataid']))) {
		C::t('common_block_item_data')->delete($_GET['dataid']);
		block_updatecache($bid, true);
	}
	showmessage('do_success');
} elseif($op == 'moreurl') {
	if(!$bid || !$allowmanage) {
		showmessage('block_edit_nopermission');
	}

	if(submitcheck('moreurlsubmit')) {
		$arr = array(
			'perpage' => max(1, intval($_POST['perpage'])),
			'seotitle' => $_POST['seotitle'],
			'seokeywords' => $_POST['seokeywords'],
			'seodescription' => $_POST['seodescription'],
		);
		$block['param']['moreurl'] = $arr;
		C::t('common_block')->update($bid, array('param' => serialize($block['param'])));

		showmessage('do_success', 'portal.php?mod=portalcp&ac=block&op=moreurl&bid='.$bid, array('bid'=>$bid));
	}
	$block['param']['moreurl'] = !empty($block['param']['moreurl']) ? $block['param']['moreurl'] :
								array('perpage' => 20, 'seotitle' => $block['name'], 'keywords' => '', 'description' => '');
}

include_once template("portal/portalcp_block");

function block_checkdefault($block) {
	if(empty($block['shownum'])) {
		$block['shownum'] = 10;
	}
	if(!isset($block['cachetime'])) {
		$block['cachetime'] = '3600';
	}
	if(empty($block['picwidth'])) {
		$block['picwidth'] = "200";
	}
	if(empty($block['picheight'])) {
		$block['picheight'] = "200";
	}
	if(empty($block['target'])) {
		$block['target'] = "blank";
	}
	return $block;
}

function block_getdateformats($format='') {
	$formats = array('Y-m-d', 'm-d', 'H:i', 'Y-m-d H:i');
	$return = array();
	foreach($formats as $value) {
		$return[] = array(
			'format' => $value,
			'selected' => $format==$value ? ' selected="selected"' : '',
			'time' => dgmdate(TIMESTAMP, $value)
		);
	}
	return $return;
}

function block_ban_item($block, $item) {
	global $_G;
	$parameters = !empty($block['param']) ? $block['param'] : array();
	$bannedids = !empty($parameters['bannedids']) ? explode(',', $parameters['bannedids']) : array();
	$bannedids[] = intval($item['id']);
	$bannedids = array_unique($bannedids);
	$parameters['bannedids'] = implode(',', $bannedids);
	$_G['block'][$block['bid']]['param'] = $parameters;
	$parameters = serialize($parameters);
	C::t('common_block')->update($block['bid'], array('param'=>$parameters));
}

function get_push_item($block, $blockstyle, $id, $idtype, $blockclass = '', $script = '') {
	$item = array();
	$obj = null;
	if(empty($blockclass) || empty($script)) {
		if($idtype == 'tids') {
			$obj = block_script('forum', 'thread');
		} elseif($idtype == 'gtids') {
			$obj = block_script('group', 'groupthread');
		} elseif($idtype == 'aids') {
			$obj = block_script('portal', 'article');
		} elseif($idtype == 'picids') {
			$obj = block_script('space', 'pic');
		} elseif($idtype == 'blogids') {
			$obj = block_script('space', 'blog');
		}
	} else {
		list($blockclass) = explode('_', $blockclass);
		$obj = block_script($blockclass, $script);
	}
	if($obj && is_object($obj)) {
		$paramter = array($idtype => intval($id));
		if(isset($block['param']['picrequired'])) {
			$paramter['picrequired'] = $block['param']['picrequired'];
		}
		$return = $obj->getData($blockstyle, $paramter);
		if($return['data']) {
			$item = array_shift($return['data']);
		}
	}
	return $item;
}

function block_convert($bid, $toblockclass) {
	global $_G;
	$bid = intval($bid);
	if(empty($bid) || empty($toblockclass)) return false;
	if(($block = C::t('common_block')->fetch($bid))) {
		loadcache('blockconvert');
		$fromblockclass = $block['blockclass'];
		list($bigclass) = explode('_', $fromblockclass);
		$convertrule = null;
		if(!empty($_G['cache']['blockconvert']) && !empty($_G['cache']['blockconvert'][$bigclass][$fromblockclass][$toblockclass])) {
			$convertrule = $_G['cache']['blockconvert'][$bigclass][$fromblockclass][$toblockclass];
		}
		if(!empty($convertrule)) {
			$blockstyle = array();
			if($block['styleid']) {
				if(($blockstyle = C::t('common_block_style')->fetch(intval($block['styleid'])))) {
					unset($blockstyle['styleid']);
					$blockstyle['fields'] = dunserialize($blockstyle['fields']);
					$blockstyle['template'] = dunserialize($blockstyle['template']);
				}
			} elseif($block['blockstyle']) {
				$blockstyle = dunserialize($block['blockstyle']);
			}

			if($blockstyle) {
				$blockstyle['name'] = '';
				$blockstyle['blockclass'] = $toblockclass;
				foreach($blockstyle['fields'] as $key => $value) {
					$blockstyle['fields'][$key] = str_replace($convertrule['searchkeys'], $convertrule['replacekeys'], $value);
				}

				$fun = create_function('&$v','$v = "{".$v."}";');
				array_walk($convertrule['searchkeys'], $fun);
				array_walk($convertrule['replacekeys'], $fun);

				foreach($blockstyle['template'] as $key => $value) {
					$blockstyle['template'][$key] = str_replace($convertrule['searchkeys'], $convertrule['replacekeys'], $value);
				}
				unset($block['bid']);
				$block['styleid'] = '0';
				$block['script'] = $convertrule['script'];
				$block['blockclass'] = $toblockclass;
				$block['blockstyle'] = serialize($blockstyle);
				$block['param'] = serialize($block['param']);
				C::t('common_block')->update($bid, $block);
			}
		}

	}
}

function block_check_favorite($uid, $bid){
	$uid = intval($uid);
	$bid = intval($bid);
	if($uid && $bid) {
		return C::t('common_block_favorite')->count_by_uid_bid($uid, $bid);
	} else {
		return false;
	}
}

function block_add_favorite($setarr){
	$arr = array(
		'uid' => intval($setarr['uid']),
		'bid' => intval($setarr['bid']),
		'dateline' => TIMESTAMP
	);
	return C::t('common_block_favorite')->insert($arr, true);
}

function block_delete_favorite($uid, $bid){
	$uid = intval($uid);
	$bid = intval($bid);
	if($uid && $bid) {
		return C::t('common_block_favorite')->delete_by_uid_bid($uid, $bid);
	} else {
		return false;
	}

}

function block_ckeck_summary($summary){
	if($summary) {
		$tags = array('div', 'table', 'tbody', 'tr', 'td', 'th');
		foreach($tags as $tag) {
			preg_match_all('/(<'.$tag.')|(<\/'.$tag.'>)/i', $summary, $all);
			if(!empty($all[1]) && !empty($all[2])) {
				$all[1] = array_filter($all[1]);
				$all[2] = array_filter($all[2]);
				if(count($all[1]) !== count($all[2])) {
					return $tag;
				}
			}
		}
	}
	return $summary;
}
?>