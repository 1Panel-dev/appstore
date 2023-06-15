<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_credit.php 32967 2013-03-28 10:57:48Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class credit {

	var $checklowerlimit = true;
	var $coef = 1;
	var $extrasql = array();

	function credit() {}

	function &instance() {
		static $object;
		if(empty($object)) {
			$object = new credit();
		}
		return $object;
	}

	function execrule($action, $uid = 0, $needle = '', $coef = 1, $update = 1, $fid = 0) {
		global $_G;

		$this->coef = $coef;
		$uid = intval($uid ? $uid : $_G['uid']);
		$fid = $fid ? $fid : (isset($_G['fid']) && $_G['fid'] ? $_G['fid'] : 0);
		$rule = $this->getrule($action, $fid);
		$updatecredit = false;

		$enabled = false;
		if($rule) {
			for ($i = 1; $i<=8; $i++) {
				if(!empty($rule['extcredits'.$i])) {
					$enabled = true; break;
				}
			}
		}

		if($enabled) {
			$rulelog = array();
			$fids = $rule['fids'] ? explode(',', $rule['fids']) : array();
			$fid = in_array($fid, $fids) ? $fid : 0;
			$rulelog = $this->getrulelog($rule['rid'], $uid, $fid);
			if($rulelog && $rule['norepeat']) {
				$rulelog = array_merge($rulelog, $this->getchecklogbyclid($rulelog['clid'], $uid));
				$rulelog['norepeat'] = $rule['norepeat'];
			}
			if($rule['rewardnum'] && $rule['rewardnum'] < $coef) {
				$coef = $rule['rewardnum'];
			}
			if(empty($rulelog)) {
				$logarr = array(
					'uid' => $uid,
					'rid' => $rule['rid'],
					'fid' => $fid,
					'total' => $coef,
					'cyclenum' => $coef,
					'dateline' => $_G['timestamp']
				);

				if(in_array($rule['cycletype'], array(2,3))) {
					$logarr['starttime'] = $_G['timestamp'];
				}
				$logarr = $this->addlogarr($logarr, $rule, false);
				if($update) {
					$clid = C::t('common_credit_rule_log')->insert($logarr, 1);
					if($rule['norepeat']) {
						$rulelog['isnew'] = 1;
						$rulelog['clid'] = $clid;
						$rulelog['uid'] = $uid;
						$rulelog['norepeat'] = $rule['norepeat'];
						$this->updatecheating($rulelog, $needle, true);
					}
				}
				$updatecredit = true;
			} else {

				$newcycle = false;
				$logarr = array();
				switch($rule['cycletype']) {
					case 0:
						break;
					case 1:
					case 4:
						if($rule['cycletype'] == 1) {
							$today = strtotime(dgmdate($_G['timestamp'], 'Y-m-d'));
							if($rulelog['dateline'] < $today && $rule['rewardnum']) {
								$rulelog['cyclenum'] =  0;
								$newcycle = true;
							}
						}
						if(empty($rule['rewardnum']) || $rulelog['cyclenum'] < $rule['rewardnum']) {
							if($rule['norepeat']) {
								$repeat = $this->checkcheating($rulelog, $needle, $rule['norepeat']);
								if($repeat && !$newcycle) {
									return false;
								}
							}
							if($rule['rewardnum']) {
								$remain = $rule['rewardnum'] - $rulelog['cyclenum'];
								if($remain < $coef) {
									$coef = $remain;
								}
							}
							$cyclenunm = $newcycle ? $coef : "cyclenum+'$coef'";
							$logarr = array(
								'cyclenum' => "cyclenum=$cyclenunm",
								'total' => "total=total+'$coef'",
								'dateline' => "dateline='$_G[timestamp]'"
							);
							$updatecredit = true;
						}
						break;

					case 2:
					case 3:
						$nextcycle = 0;
						if($rulelog['starttime']) {
							if($rule['cycletype'] == 2) {
								$start = strtotime(dgmdate($rulelog['starttime'], 'Y-m-d H:00:00'));
								$nextcycle = $start+$rule['cycletime']*3600;
							} else {
								$nextcycle = $rulelog['starttime']+$rule['cycletime']*60;
							}
						}
						if($_G['timestamp'] <= $nextcycle && $rulelog['cyclenum'] < $rule['rewardnum']) {
							if($rule['norepeat']) {
								$repeat = $this->checkcheating($rulelog, $needle, $rule['norepeat']);
								if($repeat && !$newcycle) {
									return false;
								}
							}
							if($rule['rewardnum']) {
								$remain = $rule['rewardnum'] - $rulelog['cyclenum'];
								if($remain < $coef) {
									$coef = $remain;
								}
							}
							$logarr = array(
								'cyclenum' => "cyclenum=cyclenum+'$coef'",
								'total' => "total=total+'$coef'",
								'dateline' => "dateline='$_G[timestamp]'"
							);
							$updatecredit = true;
						} elseif($_G['timestamp'] >= $nextcycle) {
							$newcycle = true;
							$logarr = array(
								'cyclenum' => "cyclenum=$coef",
								'total' => "total=total+'$coef'",
								'dateline' => "dateline='$_G[timestamp]'",
								'starttime' => "starttime='$_G[timestamp]'",
							);
							$updatecredit = true;
						}
						break;
				}
				if($update) {
					if($rule['norepeat'] && $needle) {
						$this->updatecheating($rulelog, $needle, $newcycle);
					}
					if($logarr) {
						$logarr = $this->addlogarr($logarr, $rule, true);
						C::t('common_credit_rule_log')->increase($rulelog['clid'], $logarr);
					}
				}

			}

		}
		if($update && ($updatecredit || $this->extrasql)) {
			if(!$updatecredit) {
				for($i = 1; $i <= 8; $i++) {
					if(isset($_G['setting']['extcredits'][$i])) {
						$rule['extcredits'.$i] = 0;
					}
				}
			}
			$this->updatecreditbyrule($rule, $uid, $coef, $fid);
		}
		$rule['updatecredit'] = $updatecredit;

		return $rule;
	}

	function lowerlimit($rule, $uid = 0, $coef = 1, $fid = 0) {
		global $_G;

		$uid = $uid ? $uid : intval($_G['uid']);
		if($this->checklowerlimit && $uid && $_G['setting']['creditspolicy']['lowerlimit']) {
			$member = C::t('common_member_count')->fetch($uid);
			$fid = $fid ? $fid : (isset($_G['fid']) && $_G['fid'] ? $_G['fid'] : 0);
			$rule = is_array($rule) ? $rule : $this->getrule($rule, $fid);
			for($i = 1; $i <= 8; $i++) {
				if($_G['setting']['extcredits'][$i] && $rule['extcredits'.$i]) {
					$limit = (float)$_G['setting']['creditspolicy']['lowerlimit'][$i];
					$extcredit = (float)$rule['extcredits'.$i] * $coef;
					if($extcredit < 0 && ($member['extcredits'.$i] + $extcredit) < $limit) {
						return $i;
					}
				}
			}
		}
		return true;
	}

	function updatecreditbyrule($rule, $uids = 0, $coef = 1, $fid = 0) {
		global $_G;

		$this->coef = intval($coef);
		$fid = $fid ? $fid : (isset($_G['fid']) && $_G['fid'] ? $_G['fid'] : 0);
		$uids = $uids ? $uids : intval($_G['uid']);
		$rule = is_array($rule) ? $rule : $this->getrule($rule, $fid);
		$creditarr = array();
		$updatecredit = false;
		for($i = 1; $i <= 8; $i++) {
			if(isset($_G['setting']['extcredits'][$i])) {
				$creditarr['extcredits'.$i] = intval($rule['extcredits'.$i]) * $this->coef;
				if(defined('IN_MOBILE') && $creditarr['extcredits'.$i] > 0) {
					$creditarr['extcredits'.$i] += $_G['setting']['creditspolicymobile'];
				}
				$updatecredit = true;
			}
		}
		if($updatecredit || $this->extrasql) {
			$this->updatemembercount($creditarr, $uids, is_array($uids) ? false : true, $this->coef > 0 ? urldecode($rule['rulenameuni']) : '');
		}
	}

	function frequencycheck($uids) {
		global $_G;
		if(empty($_G['config']['security']['creditsafe']['second']) || empty($_G['config']['security']['creditsafe']['times'])) {
			return true;
		}
		foreach($uids as $uid) {
			$key = 'credit_fc'.$uid;
			$v = intval(memory('get', $key));
			memory('set', $key, ++$v, $_G['config']['security']['creditsafe']['second']);
			if($v > $_G['config']['security']['creditsafe']['times']) {
				system_error('credit frequency limit', true);
				return false;
			}
		}
		return true;
	}

	function updatemembercount($creditarr, $uids = 0, $checkgroup = true, $ruletxt = '') {
		global $_G;

		if(!$uids) $uids = intval($_G['uid']);
		$uids = is_array($uids) ? $uids : array($uids);
		$this->frequencycheck($uids);
		if($uids && ($creditarr || $this->extrasql)) {
			if($this->extrasql) $creditarr = array_merge($creditarr, $this->extrasql);
			$sql = array();
			$allowkey = array('extcredits1', 'extcredits2', 'extcredits3', 'extcredits4', 'extcredits5', 'extcredits6', 'extcredits7', 'extcredits8', 'friends', 'posts', 'threads', 'oltime', 'digestposts', 'doings', 'blogs', 'albums', 'sharings', 'attachsize', 'views', 'todayattachs', 'todayattachsize');
			$creditnotice = $_G['setting']['creditnotice'] && $_G['uid'] && $uids == array($_G['uid']);
			if($creditnotice) {
				if(!isset($_G['cookiecredits'])) {
					$_G['cookiecredits'] = !empty($_COOKIE['creditnotice']) ? explode('D', $_COOKIE['creditnotice']) : array_fill(0, 9, 0);
					for($i = 1; $i <= 8; $i++) {
						$_G['cookiecreditsbase'][$i] = getuserprofile('extcredits'.$i);
					}
				}
				if($ruletxt) {
					$_G['cookiecreditsrule'][$ruletxt] = $ruletxt;
				}
			}
			foreach($creditarr as $key => $value) {
				if(!empty($key) && $value && in_array($key, $allowkey)) {
					$sql[$key] = $value;
					if($creditnotice && substr($key, 0, 10) == 'extcredits') {
						$i = substr($key, 10);
						$_G['cookiecredits'][$i] += $value;
					}
				}
			}
			if($creditnotice) {
				dsetcookie('creditnotice', implode('D', $_G['cookiecredits']).'D'.$_G['uid']);
				dsetcookie('creditbase', '0D'.implode('D', $_G['cookiecreditsbase']));
				if(!empty($_G['cookiecreditsrule'])) {
					dsetcookie('creditrule', strip_tags(implode("\t", $_G['cookiecreditsrule'])));
				}
			}
			if($sql) {
				C::t('common_member_count')->increase($uids, $sql);
			}
			if($checkgroup && count($uids) == 1) $this->checkusergroup($uids[0]);
			$this->extrasql = array();
		}
	}

	function countcredit($uid, $update = true) {
		global $_G;

		$credits = 0;
		if($uid && !empty($_G['setting']['creditsformula'])) {
			$member = C::t('common_member_count')->fetch($uid);
			eval("\$credits = round(".$_G['setting']['creditsformula'].");");
			if($uid == $_G['uid']) {
				if($update && $_G['member']['credits'] != $credits) {
					C::t('common_member')->update_credits($uid, $credits);
					$_G['member']['credits'] = $credits;
				}
			} elseif($update) {
				C::t('common_member')->update_credits($uid, $credits);
			}
		}
		return $credits;
	}

	function checkusergroup($uid) {
		global $_G;

		$uid = intval($uid ? $uid : $_G['uid']);
		$groupid = 0;
		if(!$uid) return $groupid;
		if($uid != $_G['uid']) {
			$member = getuserbyuid($uid);
		} else {
			$member = $_G['member'];
		}
		if(empty($member)) return $groupid;

		$credits = $this->countcredit($uid, false);
		$updatearray = array();
		$groupid = $member['groupid'];
		$group = C::t('common_usergroup')->fetch($member['groupid']);
		if($member['credits'] != $credits) {
			$updatearray['credits'] = $credits;
			$member['credits'] = $credits;
		}
		$member['credits'] = $member['credits'] == '' ? 0 : $member['credits'] ;
		$sendnotify = false;
		if(empty($group) || $group['type'] == 'member' && !($member['credits'] >= $group['creditshigher'] && $member['credits'] < $group['creditslower'])) {
			$newgroup = C::t('common_usergroup')->fetch_by_credits($member['credits']);
			if(!empty($newgroup)) {
				if($member['groupid'] != $newgroup['groupid']) {
					$updatearray['groupid'] = $groupid = $newgroup['groupid'];
					if($uid == $_G['uid']) {
						$_G['member']['groupid'] = $newgroup['groupid'];
					}
					$sendnotify = true;
				}
			}
		}
		if($updatearray) {
			C::t('common_member')->update($uid, $updatearray);
		}
		if($sendnotify) {
			notification_add($uid, 'system', 'user_usergroup', array('usergroup' => '<a href="home.php?mod=spacecp&ac=credit&op=usergroup">'.$newgroup['grouptitle'].'</a>', 'from_id' => 0, 'from_idtype' => 'changeusergroup'), 1);
		}

		return $groupid;

	}

	function deletelogbyfid($rid, $fid) {

		$fid = intval($fid);
		if($rid && $fid) {
			$lids = C::t('common_credit_rule_log')->fetch_ids_by_rid_fid($rid, $fid);
			if($lids) {
				C::t('common_credit_rule_log')->delete($lids);
				C::t('common_credit_rule_log_field')->delete_clid($lids);
			}
		}
	}

	function updatecheating($rulelog, $needle, $newcycle) {
		if($needle) {
			$logarr = array();
			switch($rulelog['norepeat']) {
				case 0:
					break;
				case 1:
					$info = empty($rulelog['info'])||$newcycle ? $needle : $rulelog['info'].','.$needle;
					$logarr['info'] = addslashes($info);
					break;
				case 2:
					$user = empty($rulelog['user'])||$newcycle ? $needle : $rulelog['user'].','.$needle;
					$logarr['user'] = addslashes($user);
					break;
				case 3:
					$app = empty($rulelog['app'])||$newcycle ? $needle : $rulelog['app'].','.$needle;
					$logarr['app'] = addslashes($app);
				break;
			}
			if($rulelog['isnew']) {
				$logarr['clid'] = $rulelog['clid'];
				$logarr['uid'] = $rulelog['uid'];
				C::t('common_credit_rule_log_field')->insert($logarr);
			} elseif($logarr) {
				C::t('common_credit_rule_log_field')->update($rulelog['uid'], $rulelog['clid'],$logarr);
			}
		}
	}

	function addlogarr($logarr, $rule, $issql = 0) {
		global $_G;

		for($i = 1; $i <= 8; $i++) {
			if($_G['setting']['extcredits'][$i]) {
				$extcredit = intval($rule['extcredits'.$i]) * $this->coef;
				if($issql) {
					$logarr['extcredits'.$i] = 'extcredits'.$i."='$extcredit'";
				} else {
					$logarr['extcredits'.$i] = $extcredit;
				}
			}
		}
		return $logarr;
	}

	function getrule($action, $fid = 0) {
		global $_G;

		if(empty($action)) {
			return false;
		}
		$fid = $fid ? $fid : (isset($_G['fid']) && $_G['fid'] ? $_G['fid'] : 0);
		if($_G['forum'] && $_G['forum']['status'] == 3) {
			$group_creditspolicy = $_G['grouplevels'][$_G['forum']['level']]['creditspolicy'];
			if(is_array($group_creditspolicy) && empty($group_creditspolicy[$action])) {
				return false;
			} else {
				$fid = 0;
			}
		}
		loadcache('creditrule');
		$rule = false;
		if(is_array($_G['cache']['creditrule'][$action])) {
			$rule = $_G['cache']['creditrule'][$action];
			$rulenameuni = $rule['rulenameuni'];
			if($rule['fids'] && $fid) {
				$fid = intval($fid);
				$fids = explode(',', $rule['fids']);
				if(in_array($fid, $fids)) {
					$forumfield = C::t('forum_forumfield')->fetch($fid);
					$policy = dunserialize($forumfield['creditspolicy']);
					if(isset($policy[$action])) {
						$rule = $policy[$action];
						$rule['rulenameuni'] = $rulenameuni;
						$rule['fids'] = implode(',', $fids);
					}
				}
			}

			for($i = 1; $i <= 8; $i++) {
				if(empty($_G['setting']['extcredits'][$i])) {
					unset($rule['extcredits'.$i]);
					continue;
				}
				$rule['extcredits'.$i] = intval($rule['extcredits'.$i]);
			}
		}
		return $rule;
	}

	function getrulelog($rid, $uid = 0, $fid = 0) {
		global $_G;

		$log = array();
		$uid = $uid ? $uid : $_G['uid'];
		if($rid && $uid) {
			$log = C::t('common_credit_rule_log')->fetch_rule_log($rid, $uid, $fid);
		}
		return $log;
	}

	function checkcheating($rulelog, $needle, $checktype) {

		$repeat = false;
		switch($checktype) {
			case 0:
				break;
			case 1:
				$infoarr = explode(',', $rulelog['info']);
				if(!empty($rulelog['info']) && in_array($needle, $infoarr)) {
					$repeat = true;
				}
				break;
			case 2:
				$userarr = explode(',', $rulelog['user']);
				if(!empty($rulelog['user']) && in_array($needle, $userarr)) {
					$repeat = true;
				}
				break;
			case 3:
				$apparr = explode(',', $rulelog['app']);
				if(!empty($rulelog['app']) && in_array($needle, $apparr)) {
					$repeat = true;
				}
				break;
		}
		return $repeat;
	}

	function getchecklogbyclid($clid, $uid = 0) {
		global $_G;

		$uid = $uid ? $uid : $_G['uid'];
		return C::t('common_credit_rule_log_field')->fetch($uid, $clid);
	}
}

?>