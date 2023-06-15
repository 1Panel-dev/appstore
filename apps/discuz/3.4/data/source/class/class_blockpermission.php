<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_blockpermission.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class block_permission {

	function block_permission() {}

	function &instance() {
		static $object;
		if(empty($object)) {
			$object = new block_permission();
		}
		return $object;
	}

	function add_users_perm($bid, $users) {
		if(($uids = C::t('common_block_permission')->insert_by_bid($bid, $users))) {
			$this->_update_member_allowadmincp($uids);
		}

	}

	function _update_member_allowadmincp($uids) {
		if(!empty($uids)) {
			$userperms = C::t('common_block_permission')->fetch_permission_by_uid($uids);
			foreach(C::t('common_member')->fetch_all($uids, false, 0) as $uid => $v) {
				$v['allowadmincp'] = setstatus(4, empty($userperms[$uid]['allowmanage']) ? 0 : 1, $v['allowadmincp']);
				if($userperms[$uid]['allowrecommend'] > 0 ) {
					if($userperms[$uid]['allowrecommend'] == $userperms[$uid]['needverify']) {
						$v['allowadmincp'] = setstatus(5, 1, $v['allowadmincp']);
						$v['allowadmincp'] = setstatus(6, 0, $v['allowadmincp']);
					} else {
						$v['allowadmincp'] = setstatus(5, 0, $v['allowadmincp']);
						$v['allowadmincp'] = setstatus(6, 1, $v['allowadmincp']);
					}
				} else {
					$v['allowadmincp'] = setstatus(5, 0, $v['allowadmincp']);
					$v['allowadmincp'] = setstatus(6, 0, $v['allowadmincp']);
				}
				C::t('common_member')->update($uid, array('allowadmincp'=>$v['allowadmincp']));
			}
		}
	}

	function delete_users_perm($bid, $users) {
		$bid = intval($bid);
		if($bid && $users) {
			C::t('common_block_permission')->delete_by_bid_uid_inheritedtplname($bid, $users, '');
			C::t('common_block_favorite')->delete_by_uid_bid($users, $bid);
			$this->_update_member_allowadmincp($users);
		}
	}

	function delete_inherited_perm_by_bid($bids, $inheritedtplname = '', $uid = 0) {
		if(!is_array($bids)) $bids = array($bids);
		if($bids) {
			$uid = intval($uid);
			C::t('common_block_permission')->delete_by_bid_uid_inheritedtplname($bids, $users, empty($inheritedtplname) ? true : $inheritedtplname);
			if($uid) {
				C::t('common_block_favorite')->delete_by_uid_bid($uid, $bids);
				$this->_update_member_allowadmincp(array($uid));
			}
		}
	}

	function remake_inherited_perm($bid) {
		$bid = intval($bid);
		if($bid) {
			if(($targettplname = C::t('common_template_block')->fetch_targettplname_by_bid($bid))) {
				$tplpermsission = & template_permission::instance();
				$userperm = $tplpermsission->get_users_perm_by_template($targettplname);
				$this->add_users_blocks($userperm, $bid, $targettplname);
			}
		}
	}

	function get_perms_by_bid($bid, $uid = 0) {
		$perms = array();
		$bid = intval($bid);
		$uid = intval($uid);
		if($bid) {
			$perms = C::t('common_block_permission')->fetch_all_by_bid($bid, $uid);
		}
		return $perms;
	}


	function add_users_blocks($users, $bids, $tplname = '') {
		if(($uids = C::t('common_block_permission')->insert_batch($users, $bids, $tplname))) {
			$this->_update_member_allowadmincp($uids);
		}
	}

	function delete_perm_by_inheritedtpl($tplname, $uids) {
		if(!empty($uids) && !is_array($uids)) $uids = array($uids);
		if($tplname) {
			C::t('common_block_permission')->delete_by_bid_uid_inheritedtplname(FALSE, $uids, $tplname);
			if($uids) {
				$this->_update_member_allowadmincp($uids);
			}
		}
	}

	function delete_perm_by_template($templates) {
		if($templates) {
			C::t('common_block_permission')->delete_by_bid_uid_inheritedtplname(FALSE, FALSE, $templates);
		}
	}
	function get_bids_by_template($tplname) {
		return $tplname ? C::t('common_template_block')->fetch_all_bid_by_targettplname_notinherited($tplname, 0) : array();
	}
}

class template_permission {
	function template_permission() {}

	function &instance() {
		static $object;
		if(empty($object)) {
			$object = new template_permission();
		}
		return $object;
	}

	function add_users($tplname, $users) {
		$templates = $this->_get_templates_subs($tplname);
		$this->_add_users_templates($users, $templates);

		$blockpermission = & block_permission::instance();
		$bids = $blockpermission->get_bids_by_template($templates);
		$blockpermission->add_users_blocks($users, $bids, $tplname);
	}

	function delete_users($tplname, $uids) {
		$uids = !is_array($uids) ? array($uids) : $uids;
		$uids = array_map('intval', $uids);
		$uids = array_filter($uids);
		if($uids) {
			C::t('common_template_permission')->delete_by_targettplname_uid_inheritedtplname($tplname, $uids, '');
		}
		$this->delete_perm_by_inheritedtpl($tplname, $uids);
	}

	function add_blocks($tplname, $bids){
		$users = $this->get_users_perm_by_template($tplname);
		if($users) {
			$blockpermission = & block_permission::instance();
			$blockpermission->add_users_blocks($users, $bids, $tplname);
		}
	}

	function get_users_perm_by_template($tplname){
		$perm = array();
		if($tplname) {
			$perm = C::t('common_template_permission')->fetch_all_by_targettplname($tplname);
		}
		return $perm;
	}

	function _add_users_templates($users, $templates, $uptplname = '') {
		C::t('common_template_permission')->insert_batch($users, $templates, $uptplname);
	}

	function delete_allperm_by_tplname($tplname){
		if($tplname) {
			$tplname = is_array($tplname) ? $tplname : array($tplname);
			$blockpermission = & block_permission::instance();
			$blockpermission->delete_perm_by_template($tplname);
			$tplnames = dimplode($tplname);
			C::t('common_template_permission')->delete_by_targettplname_uid_inheritedtplname($tplnames);
			C::t('common_template_permission')->delete_by_targettplname_uid_inheritedtplname(false, false, $tplnames);
		}
	}
	function delete_inherited_perm_by_tplname($templates, $inheritedtplname = '', $uid = 0) {
		if($templates && !is_array($templates)) {
			$templates = $this->_get_templates_subs($templates);
		}
		if($templates) {
			$uid = intval($uid);
			C::t('common_template_permission')->delete_by_targettplname_uid_inheritedtplname($templates, $uid, $inheritedtplname ? $inheritedtplname : true);

			$blockpermission = & block_permission::instance();
			$blocks = $blockpermission->get_bids_by_template($templates);
			$blockpermission->delete_inherited_perm_by_bid($blocks, $inheritedtplname, $uid);
		}
	}

	function delete_perm_by_inheritedtpl($tplname, $uids = array()) {
		if($uids && !is_array($uids)) $uids = array($uids);
		if($tplname) {
			C::t('common_template_permission')->delete_by_targettplname_uid_inheritedtplname(false, $uids, $tplname);
			$blockpermission = & block_permission::instance();
			$blockpermission->delete_perm_by_inheritedtpl($tplname, $uids);
		}
	}

	function remake_inherited_perm($tplname, $parenttplname) {
		if($tplname && $parenttplname) {
			$users = $this->get_users_perm_by_template($parenttplname);
			$templates = $this->_get_templates_subs($tplname);
			$this->_add_users_templates($users, $templates, $parenttplname);

			$blockpermission = & block_permission::instance();
			$bids = $blockpermission->get_bids_by_template($templates);
			$blockpermission->add_users_blocks($users, $bids, $parenttplname);
		}
	}

	function _get_templates_subs($tplname){
		global $_G;
		$tplpre = 'portal/list_';
		$cattpls = array($tplname);
		if(substr($tplname, 0, 12) == $tplpre){
			loadcache('portalcategory');
			$portalcategory = $_G['cache']['portalcategory'];
			$catid = intval(str_replace($tplpre, '', $tplname));
			if(isset($portalcategory[$catid]) && !empty($portalcategory[$catid]['children'])) {
				$children = array();
				foreach($portalcategory[$catid]['children'] as $cid) {
					if(!$portalcategory[$cid]['notinheritedblock']) {
						$cattpls[] = $tplpre.$cid;
						if(!empty($portalcategory[$cid]['children'])) {
							$children = array_merge($children, $portalcategory[$cid]['children']);
						}
					}
				}
				if(!empty($children)) {
					foreach($children as $cid) {
						if(!$portalcategory[$cid]['notinheritedblock']) {
							$cattpls[] = $tplpre.$cid;
						}
					}
				}
			}
		}
		return $cattpls;
	}

	function _get_templates_ups($tplname){
		global $_G;
		$tplpre = 'portal/list_';
		$cattpls = array($tplname);
		if(substr($tplname, 0, 12) == $tplpre){
			loadcache('portalcategory');
			$portalcategory = $_G['cache']['portalcategory'];
			$catid = intval(str_replace($tplpre, '', $tplname));
			if(isset($portalcategory[$catid]) && !$portalcategory[$catid]['notinheritedblock']) {
				$upid = $portalcategory[$catid]['upid'];
				while(!empty($upid)) {
					$cattpls[] = $tplpre.$upid;
					$upid = !$portalcategory[$upid]['notinheritedblock'] ? $portalcategory[$upid]['upid'] : 0;
				}
			}
		}
		return $cattpls;
	}

}

?>