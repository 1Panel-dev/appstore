<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_portalpermission.php 29236 2012-03-30 05:34:47Z chenmengshu $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$ops = array('article', 'template', 'block');
$operation = in_array($operation, $ops, true) ? $operation : 'article';
$opdata = array();
foreach($ops as $op) {
	$opdata[] = array('portalpermission_'.$op, 'portalpermission&operation='.$op, $op == $operation);
}

$line = '&minus;';
$right = '&radic;';
$adminscript = $mpurl = ADMINSCRIPT.'?action=portalpermission&operation='.$operation;

$permissions = $members = $uids = array();

shownav('portal', 'portalpermission');
showsubmenu('portalpermission', $opdata);

$_GET['ordersc'] = in_array($_GET['ordersc'], array('desc', 'asc'), true) ? $_GET['ordersc'] : 'desc';
$_GET['uid'] = dintval($_GET['uid']);
if(($_GET['uid'] = $_GET['uid'] ? $_GET['uid'] : '')) {
	$mpurl .= '&uid='.$_GET['uid'];
} elseif($_GET['username']) {
	$uids = array_keys(C::t('common_member')->fetch_all_by_like_username($_GET['username']));
	$uids = $uids ? $uids : array(0);
	$mpurl .= '&username='.dhtmlspecialchars($_GET['username']);
}
if($_GET['inherited']) {
	$inherited = ' checked';
	$mpurl .= '&inherited=1';
}
$ordersc = array($_GET['ordersc']=>' selected');
$perpage = in_array($_GET['perpage'], array(10,20,50,100)) ?  $_GET['perpage'] : 20;
$start = ($page-1)*$perpage;
$perpages = array($perpage => ' selected');
$searchlang = array();
$keys = array('search', 'resultsort', 'orderdesc', 'orderasc', 'perpage_10', 'perpage_20', 'perpage_50', 'perpage_100', 'likesupport',
			'uid', 'username', 'portalpermission_no_inherited');
foreach ($keys as $key) {
	$searchlang[$key] = cplang($key);
}
echo <<<SEARCH
<form method="get" autocomplete="off" action="$adminscript" id="tb_search">
	<div style="margin-top:8px;">
		<table cellspacing="3" cellpadding="3">
			<tr>
				<th>$searchlang[uid]</th><td><input type="text" class="txt" name="uid" value="$_GET[uid]"></td>
				<th>$searchlang[username]*</th><td><input type="text" class="txt" name="username" value="$_GET[username]"> *$searchlang[likesupport]</td>
			</tr>
			<tr>
				<th>$searchlang[resultsort]</th>
				<td>
					<select name="ordersc">
					<option value="desc"$ordersc[desc]>$searchlang[orderdesc]</option>
					<option value="asc"$ordersc[asc]>$searchlang[orderasc]</option>
					</select>
					<select name="perpage">
					<option value="10"$perpages[10]>$searchlang[perpage_10]</option>
					<option value="20"$perpages[20]>$searchlang[perpage_20]</option>
					<option value="50"$perpages[50]>$searchlang[perpage_50]</option>
					<option value="100"$perpages[100]>$searchlang[perpage_100]</option>
					</select>
				</td>
				<th><label for="inherited">$searchlang[portalpermission_no_inherited]</label></th>
				<td>
					<input type="checkbox" value=1 name="inherited" id="inherited" $inherited/>
					<input type="hidden" name="action" value="portalpermission">
					<input type="hidden" name="operation" value="$operation">
					<input type="submit" name="searchsubmit" value="$searchlang[search]" class="btn">
				</td>
			</tr>
		</table>
	</div>
</form>
SEARCH;

showformheader('portalpermission&operation='.$operation);
showtableheader('portalpermission');

if($operation == 'article') {
	showsubtitle(array('username', 'portalcategory', 'portalcategory_perm_publish', 'portalcategory_perm_manage', 'block_perm_inherited'));
	showtagheader('tbody', '', true);
	loadcache('portalcategory');
	$wherearr = array();
	if(($where = $_GET['uid'] ? 'uid='.$_GET['uid'] : ($uids ? 'uid IN('.dimplode($uids).')' : ''))) {
		$wherearr[] = $where;
	}
	if($inherited) {
		$wherearr[] = 'inheritedcatid = \'\'';
	}
	$wheresql = $wherearr ? ' WHERE '.implode(' AND ', $wherearr) : '';
	$uids = $_GET['uid'] ? array($_GET['uid']) : $uids;
	$count = C::t('portal_category_permission')->count_by_uids($uids, !$inherited);
	if($count) {
		$permissions = C::t('portal_category_permission')->fetch_all_by_uid($uids, !$inherited, $_GET['ordersc'], $start, $perpage);
		foreach($permissions as $value) {
			$uids[$value['uid']] = $value['uid'];
		}
		if(empty($members)) $members = C::t('common_member')->fetch_all($uids);
		$multipage = multi($count, $perpage, $page, $mpurl.'&perpage='.$perpage);
		foreach($permissions as $value){
			showtablerow('', '', array(
				$members[$value['uid']]['username'],
				'<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=perm&catid='.$value['catid'].'">'.$_G['cache']['portalcategory'][$value['catid']]['catname'].'</a>',
				$value['allowpublish'] ? $right : $line,
				$value['allowmanage'] ? $right : $line,
				$value['inheritedcatid'] ? '<a href="'.ADMINSCRIPT.'?action=portalcategory&operation=perm&catid='.$value['inheritedcatid'].'">'.$_G['cache']['portalcategory'][$value['inheritedcatid']]['catname'].'</a>' : $line,
			));
		}
		echo '<tr><td colspan="6">'.$multipage.'</td></tr>';
	}
	showtagfooter('tbody');

} elseif ($operation == 'template') {

	showsubtitle(array('username', 'diytemplate_name', 'block_perm_manage', 'block_perm_recommend', 'block_perm_needverify', 'block_perm_inherited'));
	showtagheader('tbody', '', true);
	loadcache('diytemplatename');
	$uids = $_GET['uid'] ? array($_GET['uid']) : $uids;
	$count = C::t('common_template_permission')->count_by_uids($uids, !$inherited);
	if($count) {
		$permissions = C::t('common_template_permission')->fetch_all_by_uid($uids, !$inherited, $_GET['ordersc'], $start, $perpage);
		foreach($permissions as $value) {
			$uids[$value['uid']] = $value['uid'];
		}
		if(empty($members)) $members = C::t('common_member')->fetch_all($uids);
		$multipage = multi($count, $perpage, $page, $mpurl.'&perpage='.$perpage);
		foreach($permissions as $value){
			$targettplname = $_G['cache']['diytemplatename'][$value['targettplname']];
			showtablerow('', '', array(
				$members[$value['uid']]['username'],
				'<a href="'.ADMINSCRIPT.'?action=diytemplate&operation=perm&targettplname='.$value['targettplname'].'">'.$targettplname.'</a>',
				$value['allowmanage'] ? $right : $line,
				$value['allowrecommend'] ? $right : $line,
				$value['needverify'] ? $right : $line,
				$value['inheritedtplname'] ? '<a href="'.ADMINSCRIPT.'?action=diytemplate&operation=perm&targettplname='.$value['inheritedtplname'].'">'.$_G['cache']['diytemplatename'][$value['inheritedtplname']].'</a>' : $line,
			));
		}
		echo '<tr><td colspan="6">'.$multipage.'</td></tr>';
	}
	showtagfooter('tbody');
} elseif ($operation == 'block') {

	showsubtitle(array('username', 'block_name', 'block_perm_manage', 'block_perm_recommend', 'block_perm_needverify', 'block_perm_inherited'));
	showtagheader('tbody', '', true);
	loadcache('diytemplatename');
	$uids = $_GET['uid'] ? array($_GET['uid']) : $uids;
	if(($count = C::t('common_block_permission')->count_by_uids($uids, !$inherited))) {
		$blocks = $bids = array();
		$permissions = C::t('common_block_permission')->fetch_all_by_uid($uids, !$inherited, $_GET['ordersc'], $start, $perpage);
		foreach($permissions as $value) {
			$uids[$value['uid']] = $value['uid'];
			$bids[$value['bid']] = $value['bid'];
		}
		if($bids) $blocks = C::t('common_block')->fetch_all($bids);
		if(empty($members)) $members = C::t('common_member')->fetch_all($uids);
		$multipage = multi($count, $perpage, $page, $mpurl.'&perpage='.$perpage);
		foreach($permissions as $value){
			$blockname = $blocks[$value['bid']]['name'] ? $blocks[$value['bid']]['name'] : $value['bid'];
			showtablerow('', '', array(
				$members[$value['uid']]['username'],
				'<a href="'.ADMINSCRIPT.'?action=block&operation=perm&bid='.$value['bid'].'">'.$blockname.'</a>',
				$value['allowmanage'] ? $right : $line,
				$value['allowrecommend'] ? $right : $line,
				$value['needverify'] ? $right : $line,
				$value['inheritedtplname'] ? '<a href="'.ADMINSCRIPT.'?action=diytemplate&operation=perm&targettplname='.$value['inheritedtplname'].'">'.$_G['cache']['diytemplatename'][$value['inheritedtplname']].'</a>' : $line,
			));
		}
		echo '<tr><td colspan="6">'.$multipage.'</td></tr>';
	}
	showtagfooter('tbody');
}

showtablefooter();
showformfooter();
?>