<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portalcp_portalblock.php 31958 2012-10-26 05:11:05Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/block');
$op = in_array($_GET['op'], array('recommend', 'getblocklist', 'verifydata', 'verifieddata')) ? $_GET['op'] : 'getblocklist';
$initemdata = $op === 'verifydata' || $op === 'verifieddata' ? true : false;
$_GET['idtype'] = dhtmlspecialchars($_GET['idtype']);
$_GET['id'] = intval($_GET['id']);

$allowdiy = checkperm('allowdiy');
if(!$allowdiy && !$admincp4 && !$admincp5 && !$admincp6) {
	showmessage('portal_nopermission', dreferer());
}
loadcache('diytemplatename');
$pagebids = $tpls = $blocks = $tplpermissions = $wherearr = $blockfavorite = $topblocks = $blockdata = array();

if(submitcheck('getblocklistsubmit') || submitcheck('verifieddatasubmit') || submitcheck('verifydatasubmit')) {

	if($allowdiy) {
		$tpls = array_keys($_G['cache']['diytemplatename']);
	} else {
		$permissions = getallowdiytemplate($_G['uid']);
		foreach($permissions as $value) {
			if($value['allowmanage'] || ($value['allowrecommend'] && empty($value['needverify']))) {
				$tpls[] = $value['targettplname'];
			}
		}
	}
	if(!$allowdiy) {
		foreach(C::t('common_block_permission')->fetch_all_by_uid($_G['uid']) as $bid => $value) {
			if($value['allowmanage'] == 1 || ($value['allowrecommend'] == 1 && $value['needverify'] == 0)) {
				$bids[$value['bid']] = intval($value['bid']);
			}
		}
	}

	if(!$allowdiy && empty($bids)) {
		showmessage('portal_nopermission', dreferer());
	}

	if(submitcheck('getblocklistsubmit')) {

		$updatebids = $_GET['bids'];
		$updatebids = array_map('intval', $updatebids);
		$updatebids = array_filter($updatebids);
		$updatebids = !$allowdiy ? array_intersect($bids, $updatebids) : $updatebids;
		if($updatebids) {
			C::t('common_block')->update_dateline_to_expired($updatebids, TIMESTAMP);
		}
		showmessage('portalcp_block_push_the_update_line', dreferer());

	} else if (submitcheck('verifydatasubmit')) {

		if(!in_array($_POST['optype'], array('pass', 'delete'))) {
			showmessage('select_a_option', dreferer());
		}
		$ids = $updatebids = array();
		if($_POST['ids']) {
			foreach(C::t('common_block_item_data')->fetch_all($_POST['ids']) as $value) {
				if($allowdiy || in_array($value['bid'], $bids)) {
					$ids[$value['dataid']] = intval($value['dataid']);
					$updatebids[$value['bid']] = $value['bid'];
				}
			}
		}
		if(empty($ids)) {
			showmessage('select_a_moderate_data', dreferer());
		}

		if($_POST['optype']=='pass') {
			C::t('common_block_item_data')->update($ids, array('isverified' => '1', 'verifiedtime' => $_G['timestamp']));
			if($updatebids) {
				C::t('common_block')->update_dateline_to_expired($updatebids, TIMESTAMP);
			}
		} elseif($_POST['optype']=='delete') {
			C::t('common_block_item_data')->delete($ids);
		}
		showmessage('operation_done', dreferer());

	} else if (submitcheck('verifieddatasubmit')) {

		$ids = array();
		if(!empty($_POST['ids'])) {
			foreach(C::t('common_block_item_data')->fetch_all($_POST['ids']) as $value) {
				if($allowdiy || in_array($value['bid'], $bids)) {
					$ids[$value['dataid']] = intval($value['dataid']);
				}
			}
		}
		if($ids) {
			C::t('common_block_item_data')->delete($ids);
		}

		$displayorder = array_map('intval', $_POST['displayorder']);
		foreach($displayorder  as $dataid => $displayorder) {
			if($displayorder !== intval($_POST['olddisplayorder'][$dataid])) {
				C::t('common_block_item_data')->update($dataid, array('displayorder' => $displayorder));
			}
		}
		showmessage('do_success', dreferer());
	}
} else {

	$perpage = $op == 'recommend' ? 16 : 30;
	$page = max(1,intval($_GET['page']));
	$start = ($page-1)*$perpage;
	if($start<0) $start = 0;
	$theurl = 'portal.php?mod=portalcp&ac=portalblock&op='.$op.'&idtype='.$_GET['idtype'].'&id='.$_GET['id'];
	$showfavorite = $page == 1 ? true : false;

	$multi = $fields = $leftjoin = '';
	$blockfavorite = block_get_favorite($_G['uid']);
	if($allowdiy) {
		$tpls = $_G['cache']['diytemplatename'];
	} else {
		$tplpermissions = getallowdiytemplate($_G['uid']);
		foreach($tplpermissions as $value) {
			if($value['allowmanage'] || ($value['allowrecommend'] && empty($value['needverify'])) || ($op=='recommend' && $value['allowrecommend'])) {
				$tpls[$value['targettplname']] = isset($_G['cache']['diytemplatename'][$value['targettplname']]) ? $_G['cache']['diytemplatename'][$value['targettplname']] : $value['targettplname'];
			}
		}
		$fields = ',bp.allowmanage,bp.allowrecommend,bp.needverify';
		$leftjoin = ' LEFT JOIN '.DB::table('common_block_permission').' bp ON b.bid=bp.bid';
		$wherearr[] = "bp.uid='$_G[uid]'";
		$wherearr[] = "(bp.allowmanage='1' OR (bp.allowrecommend='1'".($op == 'recommend' ? '' : "AND bp.needverify='0'")."))";
	}

	$hasinblocks = array();
	if($op == 'recommend' && in_array($_GET['idtype'], array('tid', 'gtid', 'blogid', 'picid', 'aid'), true) && ($_GET['id'] = dintval($_GET['id']))) {
		$hasinblocks = C::t('common_block')->fetch_all_recommended_block($_GET['id'], $_GET['idtype'], $wherearr, $leftjoin, $fields);
	}

	if($_GET['searchkey']) {
		$_GET['searchkey'] = trim($_GET['searchkey']);
		$showfavorite = false;
		if (preg_match('/^[#]?(\d+)$/', $_GET['searchkey'],$match)) {
			$bid = intval($match[1]);
			$wherearr[] = " (b.bid='$bid' OR b.name='$bid')";
		} else {
			$wherearr[] = " b.name LIKE '%".stripsearchkey($_GET['searchkey'])."%'";
			$perpage = 10000;
		}
		$_GET['searchkey'] = dhtmlspecialchars($_GET['searchkey']);
		$theurl .= '&searchkey='.$_GET['searchkey'];
	}
	if($_GET['targettplname']) {
		$showfavorite = false;
		$targettplname = trim($_GET['targettplname']);
		$pagebids = array_keys(C::t('common_template_block')->fetch_all_by_targettplname($targettplname));
		if(!empty($pagebids)) {
			$wherearr[] = "b.bid IN (".dimplode($pagebids).")";
			$perpage = 10000;
		} else {
			$wherearr[] = "b.bid='0'";
		}
		$_GET['targettplname'] = dhtmlspecialchars($_GET['targettplname']);
		$theurl .= '&targettplname='.$_GET['targettplname'];
	}

	if($op == 'recommend') {

		$rewhere = array();
		switch ($_GET['idtype']) {
			case 'tid' :
				$rewhere[] = "(blockclass='forum_thread' OR blockclass='forum_activity' OR blockclass='forum_trade')";
				break;
			case 'gtid' :
				$rewhere[] = "(blockclass='group_thread' OR blockclass='group_activity' OR blockclass='group_trade')";
				break;
			case 'blogid' :
				$rewhere[] = "blockclass ='space_blog'";
				break;
			case 'picid' :
				$rewhere[] = "blockclass ='space_pic'";
				break;
			case 'aid' :
				$rewhere[] = "blockclass ='portal_article'";
				break;
		}
		$wherearr = array_merge($rewhere, $wherearr);
		$where = $wherearr ? ' WHERE '.implode(' AND ', $wherearr) : '';

		if(($count = C::t('common_block')->count_by_where($where, $leftjoin))) {
			foreach(C::t('common_block')->fetch_all_by_where($where, $start, $perpage, $leftjoin, $fields) as $value) {
				$value = formatblockvalue($value);
				if(!$value['favorite'] || !$showfavorite) {
					$blocks[$value['bid']] = $value;
				}
			}
			if(!empty($blockfavorite) && $showfavorite) {
				$blocks = $blockfavorite + $blocks;
			}
			$theurl = $_G['inajax'] ? $theurl.'&getdata=yes' : $theurl;
			if($_G['inajax']) $_GET['ajaxtarget'] = 'itemeditarea';
			$multi = multi($count, $perpage, $page, $theurl);
		}
	} else {
		$where = empty($wherearr) ? '' : ' WHERE '.implode(' AND ', $wherearr);
		if(($count = C::t('common_block')->count_by_where($where, $leftjoin))) {
			foreach(C::t('common_block')->fetch_all_by_where($where, $initemdata ? 0 : $start, $initemdata ? 0 : $perpage, $leftjoin, $fields) as $value) {
				$value = formatblockvalue($value);
				if(!$value['favorite'] || !$showfavorite) {
					$blocks[$value['bid']] = $value;
				}
			}
			if(!empty($blockfavorite) && $showfavorite) {
				$blocks = $blockfavorite + $blocks;
			}
			$multi = $initemdata ? '' : multi($count, $perpage, $page, $theurl);
		}
	}

	if($blocks) {
		$losttpls = $alldata = array();
		$bids = array_keys($blocks);
		if($bids) {
			foreach(C::t('common_template_block')->fetch_all_by_bid($bids) as $value) {
				$alldata[] = $value;
				if(!isset($_G['cache']['diytemplatename'][$value['targettplname']])) {
					$losttpls[$value['targettplname']] = $value['targettplname'];
				}
			}

			if($losttpls) {
				$lostnames = getdiytplnames($losttpls);
				foreach($lostnames as $pre => $datas) {
					foreach($datas as $id => $name) {
						$_G['cache']['diytemplatename'][$pre.$id] = $tpls[$pre.$id] = $name;
					}
				}
			}

			foreach($alldata as $value) {
				$diyurl = block_getdiyurl($value['targettplname']);
				$diyurl = $diyurl['url'];
				$tplname = isset($_G['cache']['diytemplatename'][$value['targettplname']]) ? $_G['cache']['diytemplatename'][$value['targettplname']] : $value['targettplname'];
				if(!isset($tpls[$value['targettplname']])) {
					$tpls[$value['targettplname']] = $tplname;
				}
				$blocks[$value['bid']]['page'][$value['targettplname']] = $diyurl ? '<a href="'.$diyurl.'" target="_blank">'.$tplname.'</a>' : $tplname;
			}
		}
		if($initemdata) {
			$isverified = $op === 'verifieddata' ? 1 : 0;
			$count = C::t('common_block_item_data')->count_by_bid($bids, $isverified);
			$blockdata = $count ? C::t('common_block_item_data')->fetch_all_by_bid($bids, $isverified, $start, $perpage) : array();
			$multi = multi($count, $perpage, $page, $theurl);
		}
	}
}
include_once template("portal/portalcp_portalblock");

function formatblockvalue($value) {
	global $blockfavorite;
	$value['name'] = empty($value['name']) ? '<strong>#'.$value['bid'].'</strong>' : $value['name'];
	$theclass = block_getclass($value['blockclass']);
	$value['blockclassname'] = $theclass['name'];
	$value['datasrc'] = $theclass['script'][$value['script']];
	$value['isrecommendable'] = block_isrecommendable($value);
	$value['perm'] = formatblockpermissoin($value);
	$value['favorite'] = isset($blockfavorite[$value['bid']]) ? true : false;
	return $value;
}
function formatblockpermissoin($block) {
	static $allowdiy = null;
	$allowdiy = isset($allowdiy) ? $allowdiy : checkperm('allowdiy');;
	$perm = array('allowproperty' => 0, 'allowdata'=> 0);
	$bid = !empty($block) ? $block['bid'] : 0;
	if(!empty($bid)) {
		if($allowdiy) {
			$perm = array('allowproperty' => 1, 'allowdata'=> 1);
		} else {
			if($block['allowmanage']) {
				$perm = array('allowproperty' => 1, 'allowdata'=> 1);
			}
			if ($block['allowrecommend'] && !$block['needverify']) {
				$perm['allowdata'] = 1;
			}
		}
	}
	return $perm;
}

function block_get_favorite($uid){
	static $allowdiy = null;
	$allowdiy = isset($allowdiy) ? $allowdiy : checkperm('allowdiy');
	$blockfavorite = $permission = array();
	$uid = intval($uid);
	if($uid) {
		foreach(C::t('common_block_favorite')->fetch_all_by_uid($uid) as $value) {
			$blockfavorite[$value['bid']] = $value['bid'];
		}
	}
	if(!empty($blockfavorite)) {
		$blocks = C::t('common_block')->fetch_all($blockfavorite);
		if(!$allowdiy) {
			$permission = C::t('common_block_permission')->fetch_all_by_uid($uid);
		}
		foreach($blocks as $bid => $value) {
			if(!$allowdiy && $permission[$bid]) {
				$value = array_merge($value, $permission[$bid]);
			}
			$value = formatblockvalue($value);
			$value['favorite'] = true;
			$blockfavorite[$value['bid']] = $value;
		}
		$blockfavorite = array_filter($blockfavorite, 'is_array');
	}
	return $blockfavorite;
}

?>