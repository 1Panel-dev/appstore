<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: spacecp_search.php 28292 2012-02-27 07:23:14Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$myfields = array('uid','gender','birthyear','birthmonth','birthday','birthprovince','birthcity','resideprovince','residecity', 'residedist', 'residecommunity');

loadcache('profilesetting');
$fields = array();
foreach ($_G['cache']['profilesetting'] as $key => $value) {
	if($value['title'] && $value['available'] && $value['allowsearch'] && !in_array($key, $myfields)) {
		$fields[$value['fieldid']] = $value;
	}
}

$nowy = dgmdate($_G['timestamp'], 'Y');
$_GET = daddslashes($_GET);
if(!empty($_GET['searchsubmit']) || !empty($_GET['searchmode'])) {
	$_GET['searchsubmit'] = $_GET['searchmode'] = 1;
	$wherearr = $fromarr = $uidjoin = array();
	$fsql = '';

	$fromarr['member'] = DB::table('common_member').' s';

	if($searchkey = stripsearchkey($_GET['searchkey'])) {
		$wherearr[] = 's.'.DB::field('username', $searchkey);
		$searchkey = dhtmlspecialchars($searchkey);
	} else {
		foreach (array('uid','username','videophotostatus','avatarstatus') as $value) {
			if($_GET[$value]) {
				if($value == 'username' && empty($_GET['precision'])) {
					$_GET[$value] = stripsearchkey($_GET[$value]);
					$wherearr[] = 's.'.DB::field($value, '%'.$_GET[$value].'%', 'like');
				} else {
					$wherearr[] = 's.'.DB::field($value, $_GET[$value]);
				}
			}
		}
	}

	$startage = $endage = 0;
	if($_GET['endage']) {
		$startage = $nowy - intval($_GET['endage']);
	}
	if($_GET['startage']) {
		$endage = $nowy - intval($_GET['startage']);
	}

	if($startage && $endage && $endage > $startage) {
		$wherearr[] = 'sf.'.DB::field('birthyear', $startage, '>=').' AND sf.'.DB::field('birthyear', $endage, '<=');
	} else if($startage && empty($endage)) {
		$wherearr[] = 'sf.'.DB::field('birthyear', $startage, '>=');
	} else if(empty($startage) && $endage) {
		$wherearr[] = 'sf.'.DB::field('birthyear', $endage, '<=');
	}

	$havefield = 0;
	foreach ($myfields as $fkey) {
		$_GET[$fkey] = trim($_GET[$fkey]);
		if($_GET[$fkey]) {
			$havefield = 1;
			$wherearr[] = 'sf.'.DB::field($fkey, $_GET[$fkey]);
		}
	}

	foreach ($fields as $fkey => $fvalue) {
		$_GET['field_'.$fkey] = empty($_GET['field_'.$fkey])?'':stripsearchkey($_GET['field_'.$fkey]);
		if($_GET['field_'.$fkey]) {
			$havefield = 1;
			$wherearr[] = 'sf.'.DB::field($fkey, '%'.$_GET['field_'.$fkey].'%', 'like');
		}
	}

	if($havefield || $startage || $endage) {
		$fromarr['profile'] = DB::table('common_member_profile').' sf';
		$wherearr['profile'] = "sf.uid=s.uid";
	}

	$list = $ols = array();
	if($wherearr) {

		$space['friends'] = array();
		$query = C::t('home_friend')->fetch_all_by_uid($_G['uid'], 0, 0);
		foreach($query as $value) {
			$space['friends'][$value['fuid']] = $value['fuid'];
		}

		foreach(C::t('common_member')->fetch_all_for_spacecp_search($wherearr, $fromarr, 0, 100) as $value) {
			$value['isfriend'] = ($value['uid']==$space['uid'] || $space['friends'][$value['uid']])?1:0;
			$list[$value['uid']] = $value;
		}
		$follows = C::t('home_follow')->fetch_all_by_uid_followuid($_G['uid'], array_keys($list));
		foreach($list as $uid => $value) {
			$list[$uid]['follow'] = isset($follows[$uid]) ? 1 : 0;
		}
		if (!empty($list)) {
			foreach(C::app()->session->fetch_all_by_uid(array_keys($list)) as $value) {
				if(!$value['invisible']) {
					$ols[$value['uid']] = 1;
				}
			}
		}
	}


} else {

	$yearhtml = '';

	for ($i=0; $i<50; $i++) {
		$they = $nowy - $i;
		$yearhtml .= "<option value=\"$they\">$they</option>";
	}

	$sexarr = array($space['sex']=>' checked=\"checked\"');

	$birthyeayhtml = '';
	for ($i=0; $i<100; $i++) {
		$they = $nowy - $i;
		if(empty($_GET['all'])) $selectstr = $they == $space['birthyear']?' selected=\"selected\"':'';
		$birthyeayhtml .= "<option value=\"$they\"$selectstr>$they</option>";
	}
	$birthmonthhtml = '';
	for ($i=1; $i<13; $i++) {
		if(empty($_GET['all'])) $selectstr = $i == $space['birthmonth']?' selected=\"selected\"':'';
		$birthmonthhtml .= "<option value=\"$i\"$selectstr>$i</option>";
	}
	$birthdayhtml = '';
	for ($i=1; $i<29; $i++) {
		if(empty($_GET['all'])) $selectstr = $i == $space['birthday']?' selected=\"selected\"':'';
		$birthdayhtml .= "<option value=\"$i\"$selectstr>$i</option>";
	}
	$bloodhtml = '';
	foreach (array('A','B','O','AB') as $value) {
		if(empty($_GET['all'])) $selectstr = $value == $space['blood']?' selected=\"selected\"':'';
		$bloodhtml .= "<option value=\"$value\"$selectstr>$value</option>";
	}
	$marryarr = array($space['marry'] => ' selected');

	include_once libfile('function/profile');
	$birthcityhtml = showdistrict(array(0,0), array('birthprovince', 'birthcity'), 'birthcitybox', null, 'birth');
	$residecityhtml = showdistrict(array(0,0, 0, 0), array('resideprovince', 'residecity', 'residedist', 'residecommunity'), 'residecitybox', null, 'reside');

	foreach ($fields as $fkey => $fvalue) {
		if(empty($fvalue['choices'])) {
			$fvalue['html'] = '<input type="text" name="field_'.$fkey.'" class="px" value="" />';
		} else {
			$fvalue['html'] = "<select name=\"field_$fkey\"><option value=\"\">---</option>";
			$optionarr = explode("\n", $fvalue['choices']);
			foreach ($optionarr as $ov) {
				$ov = trim($ov);
				if($ov) {
					$fvalue['html'] .= "<option value=\"$ov\">$ov</option>";
				}
			}
			$fvalue['html'] .= "</select>";
		}
		$fields[$fkey] = $fvalue;
	}
}

$navtitle = lang('core', 'title_search_friend');

$actives = array($op=>' class="a"', 'search'=>' class="a"');
include template('home/spacecp_search');

?>