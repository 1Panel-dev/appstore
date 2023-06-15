<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portalcp_index.php 25889 2011-11-24 09:52:20Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(!$_G['setting']['portalstatus']) {
	dheader('location:portal.php?mod=portalcp&ac=portalblock');
}
$op = $_GET['op'] = 'list';
$allowpostarticle = checkperm('allowmanagearticle') || checkperm('allowpostarticle') || $admincp2 || $admincp3;
if($op == 'list') {
	if(checkperm('allowdiy')) {
	} elseif(!checkperm('allowmanagearticle') && checkperm('allowpostarticle') && !$admincp2 || (!$admincp2 && $admincp3)) {
		dheader('location:portal.php?mod=portalcp&ac=category');
	} elseif($_G['member']['allowadmincp'] == 8 || $_G['member']['allowadmincp'] == 32) {
		dheader('location:portal.php?mod=portalcp&ac=portalblock');
	}
}

require_once libfile('function/portalcp');

$category = $_G['cache']['portalcategory'];
$permissioncategory = $permission = array();

if (checkperm('allowmanagearticle')) {
	$permissioncategory = $category;
} elseif ($admincp2) {
	$permission = getallowcategory($_G['uid']);
	if(!empty($permission)) {
		$permissioncategory = getpermissioncategory($category,array_keys($permission));
	}
}

if($op == 'list') {

	$categorytree = '';
	foreach($permissioncategory as $key => $value) {
		if ($category[$key]['level'] == 0) {
			$categorytree .= showcategoryrow($key, 0);
		}
	}

}

include_once template("portal/portalcp_index");


function showcategoryrow($key, $level = 0, $last = '') {
	global $category, $permissioncategory, $permission;

	$value = $category[$key];
	$return = '';

	$op = $addarticle = $artilcemanage = '';
	$value['articles'] = category_get_num('portal', $key);
	if (checkperm('allowmanagearticle') || checkperm('allowmanage') || $permission[$key]['allowmanage']) {
		$addarticle .= '<a href="portal.php?mod=portalcp&ac=category&catid='.$key.'" class="y">'.lang('portalcp', 'article_manage').'</a>';
	}
	if ((checkperm('allowmanagearticle') || checkperm('allowpostarticle') || $permission[$key]['allowmanage'] || $permission[$key]['allowpublish']) && empty($value['disallowpublish'])) {
		$artilcemanage .= '<a href="portal.php?mod=portalcp&ac=article&catid='.$value['catid'].'" target="_blank" class="y">'.lang('portalcp', 'article_publish').'</a>';
	}
	if($addarticle && $artilcemanage) {
		$op = $addarticle.'<span class="pipe y">|</span>'.$artilcemanage;
	} else {
		$op = $addarticle ? $addarticle : $artilcemanage;
	}
	if($level == 2) {
		$class = $last ? 'lastchildcat' : 'childcat';
		$return = '<tr class="hover"><td><div class="'.$class.'"><a href="portal.php?mod=portalcp&ac=category&catid='.$key.'">'.$value['catname'].'</a>'.
		'</div></td><td>'.$value['articles'].'</td><td>'.$op.'</td></tr>';
	} elseif($level == 1) {
		$return = '<tr class="hover"><td><div class="cat"><a href="portal.php?mod=portalcp&ac=category&catid='.$key.'">'.$value['catname'].'</a>'.
		'</td><td>'.$value['articles'].'</td><td>'.$op.'</td></tr>';
		$children = checkperm('allowmanagearticle') ? $category[$key]['children'] : $permissioncategory[$key]['permissionchildren'];
		$i = 1;
		$l = count($children);
		foreach($children as $v){
			$return .= showcategoryrow($v, 2 ,$i++ == $l);
		}
	} else {
		$return = '<tr class="hover"><td><div class="parentcat"><a href="portal.php?mod=portalcp&ac=category&catid='.$key.'">'.$value['catname'].'</a>'.
		'</div></td><td>'.$value['articles'].'</td><td>'.$op.'</td></tr>';
		$children = checkperm('allowmanagearticle') ? $category[$key]['children'] : $permissioncategory[$key]['permissionchildren'];
		foreach($children as $v){
			$return .= showcategoryrow($v, 1);
		}
	}
	return $return;
}

function showcategoryrowpush($key, $level = 0, $last = '') {
	global $_G, $category, $permissioncategory, $permission;

	$value = $category[$key];
	$return = '';

	$op = '';
	if (checkperm('allowmanagearticle') || checkperm('allowpostarticle') || $permission[$key]['allowpublish'] || $permission[$key]['allowmanage']) {
		if(empty($value['disallowpublish'])){
			$value['pushurl'] = '<a href="portal.php?mod=portalcp&ac=article&catid='.$key.'&from_idtype='.$_GET['idtype'].'&from_id='.$_GET['id'].'" target="_blank" onclick="hideWindow(\''.$_GET[handlekey].'\')">'.$value['catname'].'</a>';
		} else {
			$value['pushurl'] = $value['catname'];
		}
	}

	if($level == 2) {
		$class = $last ? 'lastchildcat' : 'childcat';
		$return = '<tr class="hover"><td>&nbsp;</td><td><div class="'.$class.'">'.$value['pushurl'].'</div></td></tr>';
	} elseif($level == 1) {
		$return = '<tr class="hover"><td>&nbsp;</td><td><div class="cat">'.$value['pushurl'].'</div></td></tr>';
		$children = checkperm('allowmanagearticle') ? $category[$key]['children'] : $permissioncategory[$key]['permissionchildren'];
		$i = 1;
		$l = count($children);
		foreach($children as $v) {
			$return .= showcategoryrowpush($v, 2, $i++ == $l);
		}
	} else {
		$children = checkperm('allowmanagearticle') ? $category[$key]['children'] : $permissioncategory[$key]['permissionchildren'];
		$childrennum = count($children);
		$toggle = $childrennum > 25 ? ' style="display:none"' : '';
		$return = '<tbody><tr class="hover"><td onclick="toggle_group(\'group_'.$value['catid'].'\')"><a id="a_group_'.$value['catid'].'" href="javascript:;">'.($toggle ? '[+]' : '[-]').'</a></td>'
		.'<td><div class="parentcat">'.$value['pushurl'].'</div></td></tr></tbody>
		<tbody id="group_'.$value['catid'].'"'.$toggle.'>';
		foreach($children as $v) {
			$return .= showcategoryrowpush($v, 1, '');
		}
		$return .= '</tdoby>';
	}
	return $return;
}

?>