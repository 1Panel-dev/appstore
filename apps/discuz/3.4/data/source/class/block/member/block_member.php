<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_member.php 32370 2013-01-07 03:00:27Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class block_member extends discuz_block {
	var $setting = array();
	function block_member() {
		$this->setting = array(
			'uids' => array(
				'title' => 'memberlist_uids',
				'type' => 'text'
			),
			'groupid' => array(
				'title' => 'memberlist_groupid',
				'type' => 'mselect',
				'value' => array()
			),
			'special' => array(
				'title' => 'memberlist_special',
				'type' => 'mradio',
				'value' => array(
					array('', 'memberlist_special_nolimit'),
					array('0', 'memberlist_special_hot'),
					array('1', 'memberlist_special_default'),
				),
				'default' => ''
			),
			'gender' => array(
				'title' => 'memberlist_gender',
				'type' => 'mradio',
				'value' => array(
					array('1', 'memberlist_gender_male'),
					array('2', 'memberlist_gender_female'),
					array('', 'memberlist_gender_nolimit'),
				),
				'default' => ''
			),
			'birthcity' => array(
				'title' => 'memberlist_birthcity',
				'type' => 'district',
				'value' => array('xbirthprovince', 'xbirthcity', 'xbirthdist', 'xbirthcommunity'),
			),
			'residecity' => array(
				'title' => 'memberlist_residecity',
				'type' => 'district',
				'value' => array('xresideprovince', 'xresidecity', 'xresidedist', 'xresidecommunity')
			),
			'avatarstatus' => array(
				'title' => 'memberlist_avatarstatus',
				'type' => 'radio',
				'default' => ''
			),
			'emailstatus' => array(
				'title' => 'memberlist_emailstatus',
				'type' => 'mcheckbox',
				'value' => array(
					array(1, 'memberlist_yes'),
				),
				'default' => ''
			),
			'verifystatus' => array(
				'title' => 'memberlist_verifystatus',
				'type' => 'mcheckbox',
				'value' => array(),
				'default' => '',
			),
			'orderby' => array(
				'title' => 'memberlist_orderby',
				'type' => 'mradio',
				'value' => array(
					array('credits', 'memberlist_orderby_credits'),
					array('extcredits', 'memberlist_orderby_extcredits'),
					array('threads', 'memberlist_orderby_threads'),
					array('posts', 'memberlist_orderby_posts'),
					array('blogs', 'memberlist_orderby_blogs'),
					array('doings', 'memberlist_orderby_doings'),
					array('albums', 'memberlist_orderby_albums'),
					array('sharings', 'memberlist_orderby_sharings'),
					array('digestposts', 'memberlist_orderby_digestposts'),
					array('regdate', 'memberlist_orderby_regdate'),
					array('show', 'memberlist_orderby_show'),
					array('special', 'memberlist_orderby_special'),
					array('todayposts', 'memberlist_orderby_todayposts'),
				),
				'default' => 'credits'
			),
			'extcredit' => array(
				'title' => 'memberlist_orderby_extcreditselect',
				'type' => 'select',
				'value' => array()
			),
			'lastpost' => array(
				'title' => 'memberlist_lastpost',
				'type' => 'mradio',
				'value' => array(
					array('', 'memberlist_lastpost_nolimit'),
					array('3600', 'memberlist_lastpost_hour'),
					array('86400', 'memberlist_lastpost_day'),
					array('604800', 'memberlist_lastpost_week'),
					array('2592000', 'memberlist_lastpost_month'),
				),
				'default' => ''
			),
			'startrow' => array(
				'title' => 'memberlist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
		$verifys = getglobal('setting/verify');
		if(!empty($verifys)) {
			foreach($verifys as $key => $value) {
				if($value['title']) {
					$this->setting['verifystatus']['value'][] = array($key, $value['title']);
				}
			}
		}
		if(empty($this->setting['verifystatus']['value'])) {
			unset($this->setting['verifystatus']);
		}
	}

	function name() {
		return lang('blockclass', 'blockclass_member_script_member');
	}

	function blockclass() {
		return array('member', lang('blockclass', 'blockclass_member_member'));
	}

	function fields() {
		global $_G;
		$fields = array(
				'id' => array('name' => lang('blockclass', 'blockclass_field_id'), 'formtype' => 'text', 'datatype' => 'int'),
				'url' => array('name' => lang('blockclass', 'blockclass_member_field_url'), 'formtype' => 'text', 'datatype' => 'string'),
				'title' => array('name' => lang('blockclass', 'blockclass_member_field_title'), 'formtype' => 'title', 'datatype' => 'title'),
				'avatar' => array('name' => lang('blockclass', 'blockclass_member_field_avatar'), 'formtype' => 'text', 'datatype' => 'string'),
				'avatar_middle' => array('name' => lang('blockclass', 'blockclass_member_field_avatar_middle'), 'formtype' => 'text', 'datatype' => 'string'),
				'avatar_big' => array('name' => lang('blockclass', 'blockclass_member_field_avatar_big'), 'formtype' => 'text', 'datatype' => 'string'),
				'regdate' => array('name' => lang('blockclass', 'blockclass_member_field_regdate'), 'formtype' => 'date', 'datatype' => 'date'),
				'posts' => array('name' => lang('blockclass', 'blockclass_member_field_posts'), 'formtype' => 'text', 'datatype' => 'int'),
				'threads' => array('name' => lang('blockclass', 'blockclass_member_field_threads'), 'formtype' => 'text', 'datatype' => 'int'),
				'digestposts' => array('name' => lang('blockclass', 'blockclass_member_field_digestposts'), 'formtype' => 'text', 'datatype' => 'int'),
				'credits' => array('name' => lang('blockclass', 'blockclass_member_field_credits'), 'formtype' => 'text', 'datatype' => 'int'),
				'reason' => array('name' => lang('blockclass', 'blockclass_member_field_reason'), 'formtype' => 'text', 'datatype' => 'string'),
				'unitprice' => array('name' => lang('blockclass', 'blockclass_member_field_unitprice'), 'formtype' => 'text', 'datatype' => 'int'),
				'showcredit' => array('name' => lang('blockclass', 'blockclass_member_field_showcredit'), 'formtype' => 'text', 'datatype' => 'int'),
				'shownote' => array('name' => lang('blockclass', 'blockclass_member_field_shownote'), 'formtype' => 'text', 'datatype' => 'string'),
				);
		foreach($_G['setting']['extcredits'] as $key=>$value) {
			$fields['extcredits'.$key] = array('name'=>$value['title'], 'formtype'=>'text', 'datatype'=>'int');
		}
		loadcache('profilesetting');
		foreach($_G['cache']['profilesetting'] as $key=>$value) {
			if($value['available']) {
				$fields[$key] = array('name'=>$value['title'], 'formtype'=>'text', 'datatype'=>'string');
			}
		}
		return $fields;
	}

	function getsetting() {
		global $_G;
		$settings = $this->setting;

		if($settings['extcredit']) {
			foreach($_G['setting']['extcredits'] as $id => $credit) {
				$settings['extcredit']['value'][] = array($id, $credit['title']);
			}
		}
		if($settings['groupid']) {
			$settings['groupid']['value'][] = array(0, lang('portalcp', 'block_all_group'));
			foreach(C::t('common_usergroup')->fetch_all_by_type(array('member', 'special')) as $value) {
				$settings['groupid']['value'][] = array($value['groupid'], $value['grouptitle']);
			}
		}
		return $settings;
	}

	function getdata($style, $parameter) {
		global $_G;

		$parameter = $this->cookparameter($parameter);

		$uids		= !empty($parameter['uids']) ? explode(',',$parameter['uids']) : array();
		$groupid	= !empty($parameter['groupid']) && !in_array(0, $parameter['groupid']) ? $parameter['groupid'] : array();
		$startrow	= !empty($parameter['startrow']) ? intval($parameter['startrow']) : 0;
		$items		= !empty($parameter['items']) ? intval($parameter['items']) : 10;
		$orderby	= isset($parameter['orderby']) && in_array($parameter['orderby'],array('credits', 'extcredits', 'threads', 'posts', 'digestposts', 'regdate', 'show', 'blogs', 'albums', 'doings', 'sharings', 'special', 'todayposts')) ? $parameter['orderby'] : '';
		$special    = isset($parameter['special']) && strlen($parameter['special']) ? intval($parameter['special']) : null;
		$lastpost	= !empty($parameter['lastpost']) ? intval($parameter['lastpost']) : '';
		$avatarstatus = !empty($parameter['avatarstatus']) ? 1 : 0;
		$emailstatus = !empty($parameter['emailstatus']) ? 1 : 0;
		$verifystatus = !empty($parameter['verifystatus']) ? $parameter['verifystatus'] : array();
		$profiles = array();
		$profiles['gender']		= !empty($parameter['gender']) ? intval($parameter['gender']) : 0;
		$profiles['resideprovince']	= !empty($parameter['xresideprovince']) ? $parameter['xresideprovince'] : '';
		$profiles['residecity']	= !empty($parameter['xresidecity']) ? $parameter['xresidecity'] : '';
		$profiles['residedist']	= !empty($parameter['xresidedist']) ? $parameter['xresidedist'] : '';
		$profiles['residecommunity']	= !empty($parameter['xresidecommunity']) ? $parameter['xresidecommunity'] : '';
		$profiles['birthprovince']	= !empty($parameter['xbirthprovince']) ? $parameter['xbirthprovince'] : '';
		$profiles['birthcity']	= !empty($parameter['xbirthcity']) ? $parameter['xbirthcity'] : '';

		$bannedids = !empty($parameter['bannedids']) ? explode(',', $parameter['bannedids']) : array();

		$list = $todayuids = $todayposts = array();
		$tables = $wheres = array();
		$sqlorderby = '';
		$olditems = $items;
		$tables[] = DB::table('common_member').' m';
		if($groupid) {
			$wheres[] = 'm.groupid IN ('.dimplode($groupid).')';
		}
		if($bannedids) {
			$wheres[] = 'm.uid NOT IN ('.dimplode($bannedids).')';
		}
		if($avatarstatus) {
			$wheres[] = "m.avatarstatus='1'";
		}
		if($emailstatus) {
			$wheres[] = "m.emailstatus='1'";
		}
		if(!empty($verifystatus)) {
			$flag = false;
			foreach($verifystatus as $value) {
				if(isset($_G['setting']['verify'][$value])) {
					$flag = true;
					$wheres[] = "cmv.verify$value='1'";
				}
			}
			if($flag) {
				$tables[] = DB::table('common_member_verify').' cmv';
				$wheres[] = 'cmv.uid=m.uid';
			}
		}
		$tables[] = DB::table('common_member_count').' mc';
		$wheres[] = 'mc.uid=m.uid';
		foreach($profiles as $key=>$value) {
			if($value) {
				$tables[] = DB::table('common_member_profile').' mp';
				$wheres[] = 'mp.uid=m.uid';
				$wheres[] = "mp.$key='$value'";
			}
		}

		$reason = $show = '';
		if($special !== null) {
			$special = in_array($special, array(-1, 0, 1)) ? $special : -1;
			$tables[] = DB::table('home_specialuser').' su';
			if($special != -1) {
				$wheres[] = "su.status='$special'";
			}
			$wheres[] = 'su.uid=m.uid';
			$reason = ', su.reason';
		}
		if($lastpost && $orderby != 'todayposts') {
			$time = TIMESTAMP - $lastpost;
			$tables[] = DB::table('common_member_status')." ms";
			$wheres[] = "ms.uid=m.uid";
			$wheres[] = "ms.lastpost>'$time'";
		}
		switch($orderby) {
			case 'credits':
			case 'regdate':
				$sqlorderby = " ORDER BY m.$orderby DESC";
				break;
			case 'extcredits':
				$extcredits = 'extcredits'.(in_array($parameter['extcredit'], range(1, 8)) ? $parameter['extcredit'] : '1');
				$sqlorderby = " ORDER BY mc.$extcredits DESC";
				break;
			case 'threads':
			case 'posts':
			case 'blogs':
			case 'albums':
			case 'doings':
			case 'sharings':
			case 'digestposts':
				$sqlorderby = " ORDER BY mc.$orderby DESC";
				break;
			case 'show':
				$show = ', s.unitprice, s.credit as showcredit, s.note as shownote';
				$tables[] = DB::table('home_show')." s";
				$wheres[] = 's.uid=m.uid';
				$sqlorderby = ' ORDER BY s.unitprice DESC, s.credit DESC';
				break;
			case 'special':
				$sqlorderby = $special !== null ? ' ORDER BY su.displayorder, dateline DESC' : '';
				break;
			case 'todayposts':
				$todaytime = strtotime(dgmdate(TIMESTAMP, 'Ymd'));
				$inuids = $uids ? ' AND uid IN ('.dimplode($uids).')' : '';
				$items = $items * 5;
				$query = DB::query('SELECT uid, count(*) as sum FROM '.DB::table('common_member_action_log')."
						WHERE dateline>=$todaytime AND action='".getuseraction('pid')."'$inuids GROUP BY uid ORDER BY sum DESC LIMIT $items");
				while($value = DB::fetch($query)) {
					$todayposts[$value['uid']] = $value['sum'];
					$todayuids[] = $value['uid'];
				}
				if(empty($todayuids)) {
					$todayuids = array(0);
				}
				$uids = $todayuids;
				break;
		}

		if($uids) {
			$wheres[] = 'm.uid IN ('.dimplode($uids).')';
		}
		$wheres[] = '(m.groupid < 4 OR m.groupid > 8)';

		$tables = array_unique($tables);
		$wheres = array_unique($wheres);
		$tablesql = implode(',',$tables);
		$wheresql = implode(' AND ',$wheres);
		$query = DB::query("SELECT m.*, mc.*$reason$show FROM $tablesql WHERE $wheresql $sqlorderby LIMIT $startrow,$items");
		$resultuids = array();
		while($data = DB::fetch($query)){
			$resultuids[] = intval($data['uid']);
			$list[] = array(
				'id' => $data['uid'],
				'idtype' => 'uid',
				'title' => $data['username'],
				'url' => 'home.php?mod=space&uid='.$data['uid'],
				'pic' => '',
				'picflag' => 0,
				'summary' => '',
				'fields' => array(
					'avatar' => avatar($data['uid'], 'small', true, false, false, $_G['setting']['ucenterurl']),
					'avatar_middle' => avatar($data['uid'], 'middle', true, false, false, $_G['setting']['ucenterurl']),
					'avatar_big' => avatar($data['uid'], 'big', true, false, false, $_G['setting']['ucenterurl']),
					'credits' => $data['credits'],
					'extcredits1' => $data['extcredits1'],
					'extcredits2' => $data['extcredits2'],
					'extcredits3' => $data['extcredits3'],
					'extcredits4' => $data['extcredits4'],
					'extcredits5' => $data['extcredits5'],
					'extcredits6' => $data['extcredits6'],
					'extcredits7' => $data['extcredits7'],
					'extcredits8' => $data['extcredits8'],
					'regdate' => $data['regdate'],
					'posts' => empty($todayposts[$data['uid']]) ? $data['posts'] : $todayposts[$data['uid']],
					'threads' => $data['threads'],
					'digestposts' => $data['digestposts'],
					'reason' => isset($data['reason']) ? $data['reason'] : '',
					'unitprice' => isset($data['unitprice']) ? $data['unitprice'] : '',
					'showcredit' => isset($data['showcredit']) ? $data['showcredit'] : '',
					'shownote' => isset($data['shownote']) ? $data['shownote'] : '',
				)
			);
		}
		if($resultuids) {
			include_once libfile('function/profile');
			$profiles = array();
			$query = DB::query('SELECT * FROM '.DB::table('common_member_profile')." WHERE uid IN (".dimplode($resultuids).")");
			while($data = DB::fetch($query)) {
				$profile = array();
				foreach($data as $fieldid=>$fieldvalue) {
					$fieldvalue = profile_show($fieldid, $data, true);
					if(false !== $fieldvalue) {
						$profile[$fieldid] = $fieldvalue;
					}
				}
				$profiles[$data['uid']] = $profile;
			}
			for($i=0,$L=count($list); $i<$L; $i++) {
				$uid = $list[$i]['id'];
				if($profiles[$uid]) {
					$list[$i]['fields'] = array_merge($list[$i]['fields'], $profiles[$uid]);
				}
			}

			if(!empty($todayuids)) {
				$datalist = array();
				foreach($todayuids as $uid) {
					foreach($list as $user) {
						if($user['id'] == $uid) {
							$datalist[] = $user;
							break;
						}
					}
					if(count($datalist) >= $olditems) {
						break;
					}
				}
				$list = $datalist;
			}
		}
		return array('html' => '', 'data' => $list);
	}
}

?>