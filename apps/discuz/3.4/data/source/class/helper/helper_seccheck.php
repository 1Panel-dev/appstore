<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_seccode.php 33661 2013-07-29 08:18:34Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class helper_seccheck {

	private function _check($type) {
		global $_G;
		$secappend = '';
		if(!defined('IN_MOBILE')) {
			if(isset($_GET['idhash']) && $_GET['idhash']) {
				$secappend = $_GET['idhash'];
			} elseif($type == 'code') {
				if(isset($_GET['seccodehash']) && $_GET['seccodehash']) {
					$secappend = $_GET['seccodehash'];
				}
			} elseif($type == 'qaa') {
				if(isset($_GET['secqaahash']) && $_GET['secqaahash']) {
					$secappend = $_GET['secqaahash'];
				}
			}
		}
		if(!isset($_G['cookie']['sec'.$type.$secappend])) {
			return false;
		}
		list($ssid, $sign) = explode('.', $_G['cookie']['sec'.$type.$secappend]);
		if($sign != substr(md5($ssid.$_G['uid'].$_G['authkey']), 8, 18)) {
			return false;
		}
		$seccheck = C::t('common_seccheck')->fetch($ssid);
		if(!$seccheck) {
			return false;
		}
		if(TIMESTAMP - $seccheck['dateline'] > 600 || $seccheck['verified'] > 4) {
			C::t('common_seccheck')->delete_expiration($ssid);
			return false;
		}
		return $seccheck;
	}

	function _create($type, $code = '') {
		global $_G;
		$secappend = '';
		if(!defined('IN_MOBILE')) {
			if(isset($_GET['idhash']) && $_GET['idhash']) {
				$secappend = $_GET['idhash'];
			} elseif($type == 'code') {
				if(isset($_GET['seccodehash']) && $_GET['seccodehash']) {
					$secappend = $_GET['seccodehash'];
				}
			} elseif($type == 'qaa') {
				if(isset($_GET['secqaahash']) && $_GET['secqaahash']) {
					$secappend = $_GET['secqaahash'];
				}
			}
		}
		$ssid = C::t('common_seccheck')->insert(array(
		    'dateline' => TIMESTAMP,
		    'code' => $code,
		    'succeed' => 0,
		    'verified' => 0,
		), true);
		dsetcookie('sec'.$type.$secappend, $ssid.'.'.substr(md5($ssid.$_G['uid'].$_G['authkey']), 8, 18));
	}

	public static function make_seccode($seccode = ''){
		global $_G;
		if(!$seccode) {
			$seccode = random(6, 1);
			$seccodeunits = '';
			if($_G['setting']['seccodedata']['type'] == 1) {
				$lang = lang('seccode');
				$len = strtoupper(CHARSET) == 'GBK' ? 2 : 3;
				$code = array(substr($seccode, 0, 3), substr($seccode, 3, 3));
				$seccode = '';
				for($i = 0; $i < 2; $i++) {
					$seccode .= substr($lang['chn'], $code[$i] * $len, $len);
				}
			} elseif($_G['setting']['seccodedata']['type'] == 3) {
				$s = sprintf('%04s', base_convert($seccode, 10, 20));
				$seccodeunits = 'CEFHKLMNOPQRSTUVWXYZ';
			} else {
				$s = sprintf('%04s', base_convert($seccode, 10, 24));
				$seccodeunits = 'BCEFGHJKMPQRTVWXY2346789';
			}
			if($seccodeunits) {
				$seccode = '';
				for($i = 0; $i < 4; $i++) {
					$unit = ord($s[$i]);
					$seccode .= ($unit >= 0x30 && $unit <= 0x39) ? $seccodeunits[$unit - 0x30] : $seccodeunits[$unit - 0x57];
				}
			}
		}
		self::_create('code', $seccode);
		return $seccode;
	}

	public static function make_secqaa() {
		global $_G;
		loadcache('secqaa');
		$secqaakey = max(1, random(1, 1));
		if($_G['cache']['secqaa'][$secqaakey]['type']) {
			$etype = explode(':', $_G['cache']['secqaa'][$secqaakey]['question']);
			if(count($etype) > 1) {
				if(!preg_match('/^\w+$/', $etype[0]) || !preg_match('/^\w+$/', $etype[1])) {
					return;
				}
				$qaafile = DISCUZ_ROOT.'./source/plugin/'.$etype[0].'/secqaa/secqaa_'.$etype[1].'.php';
				$class = $etype[1];
			} else {
				if(!preg_match('/^\w+$/', $_G['cache']['secqaa'][$secqaakey]['question'])) {
					return;
				}
				$qaafile = libfile('secqaa/'.$_G['cache']['secqaa'][$secqaakey]['question'], 'class');
				$class = $_G['cache']['secqaa'][$secqaakey]['question'];
			}
			if(file_exists($qaafile)) {
				@include_once $qaafile;
				$class = 'secqaa_'.$class;
				if(class_exists($class)) {
					$qaa = new $class();
					if(method_exists($qaa, 'make')) {
						$_G['cache']['secqaa'][$secqaakey]['answer'] = md5($qaa->make($_G['cache']['secqaa'][$secqaakey]['question']));
					}
				}
			}
		}
		self::_create('qaa', substr($_G['cache']['secqaa'][$secqaakey]['answer'], 0, 6));
		return $_G['cache']['secqaa'][$secqaakey]['question'];
	}

	public static function check_seccode($value, $idhash, $fromjs = 0, $modid = '') {
		global $_G;
		if(!$_G['setting']['seccodestatus']) {
			return true;
		}
		$seccheck = self::_check('code');
		if(!$seccheck) {
			return false;
		}
		$ssid = $seccheck['ssid'];
		if(!is_numeric($_G['setting']['seccodedata']['type'])) {
			$etype = explode(':', $_G['setting']['seccodedata']['type']);
			if(count($etype) > 1) {
				if(!preg_match('/^\w+$/', $etype[0]) || !preg_match('/^\w+$/', $etype[1])) {
					return false;
				}
				$codefile = DISCUZ_ROOT.'./source/plugin/'.$etype[0].'/seccode/seccode_'.$etype[1].'.php';
				$class = $etype[1];
			} else {
				if(!preg_match('/^\w+$/', $_G['setting']['seccodedata']['type'])) {
					return false;
				}
				$codefile = libfile('seccode/'.$_G['setting']['seccodedata']['type'], 'class');
				$class = $_G['setting']['seccodedata']['type'];
			}
			if(file_exists($codefile)) {
				@include_once $codefile;
				$class = 'seccode_'.$class;
				if(class_exists($class)) {
					$code = new $class();
					if(method_exists($code, 'check')) {
						$return = $code->check($value, $idhash, $seccheck, $fromjs, $modid);
					}
				}
			} else {
				$return = false;
			}
		} else {
			$return = $seccheck['code'] == strtoupper($value);
		}
		if($return) {
			C::t('common_seccheck')->update_succeed($ssid);
		} else {
			C::t('common_seccheck')->update_verified($ssid);
		}
		return $return;
	}

	public static function check_secqaa($value, $idhash) {
		global $_G;
		if(!$_G['setting']['secqaa']) {
			return true;
		}
		$seccheck = self::_check('qaa');
		if(!$seccheck) {
			return false;
		}
		$ssid = $seccheck['ssid'];
		$return = $seccheck['code'] == substr(md5($value), 0, 6);
		if($return) {
			C::t('common_seccheck')->update_succeed($ssid);
		} else {
			C::t('common_seccheck')->update_verified($ssid);
		}
		return $return;
	}

	public static function rule_register() {
		global $_G;
		$seccheckrule = & $_G['setting']['seccodedata']['rule']['register'];
		if($seccheckrule['allow'] == 1) {
			$seccode = true;
		} elseif($seccheckrule['allow'] == 2) {
			if($seccheckrule['numlimit'] > 0) {
				loadcache('seccodedata', true);
				if($_G['cache']['seccodedata']['register']['show']) {
					$seccode = true;
				} else {
					$regnumber = C::t('common_member')->count_by_regdate(TIMESTAMP - $seccheckrule['timelimit']);
					if($regnumber >= $seccheckrule['numlimit']) {
						$seccode = true;
						$_G['cache']['seccodedata']['register']['show'] = 1;
						savecache('seccodedata', $_G['cache']['seccodedata']);
					}
				}
			}
		} else {
			$seccode = false;
		}
		return array(
			$seccode,
			$_G['setting']['secqaa']['status'] & 1
		);
	}

	public static function rule_login() {
		global $_G;
                $seccheckrule = & $_G['setting']['seccodedata']['rule']['login'];
		if($seccheckrule['allow'] == 1) {
			$seccode = true;
		} elseif($seccheckrule['allow'] == 2) {
			$seccode = false;
		} else {
			$seccode = false;
		}
		return array($seccode);
	}

	public static function rule_post($action) {
		global $_G;
		$seccheckrule = & $_G['setting']['seccodedata']['rule']['post'];
		if($seccheckrule['allow'] == 1) {
			$seccode = !$_G['setting']['seccodedata']['minposts'] || getuserprofile('posts') < $_G['setting']['seccodedata']['minposts'];
		} elseif($seccheckrule['allow'] == 2) {
			if(C::t('common_member_secwhite')->check($_G['uid'])) {
				 $seccode = false;
			} else {
				$seccode = getuserprofile('posts') < $_G['setting']['seccodedata']['minposts'];
				if(!$seccode && $seccheckrule['numlimit']) {
					$count = C::t('forum_post')->count_by_search('pid:0', null, null, null, null, $_G['uid'], null, TIMESTAMP - $seccheckrule['timelimit']);
					$seccode = $seccheckrule['numlimit'] <= $count;
				}
				if($action == 'newthread' && !$seccode && !empty($_POST) && $seccheckrule['nplimit']) {
					if(!$_G['cookie']['st_t']) {
						$seccode = true;
					} else {
						list($uid, $t, $hash) = explode('|', $_G['cookie']['st_t']);
						list($t, $m) = explode(',', $t);
						if(md5($uid.'|'.$t.$_G['config']['security']['authkey']) == $hash && !$m) {
							if(TIMESTAMP - $t <= $seccheckrule['nplimit']) {
								$seccode = true;
							} else {
								$seccode = false;
							}
						} else {
							$seccode = true;
						}
					}
				}
				if($action == 'reply' && !$seccode && !empty($_POST) && $seccheckrule['vplimit']) {
					if(!$_G['cookie']['st_p']) {
						$seccode = true;
					} else {
						list($uid, $t, $hash) = explode('|', $_G['cookie']['st_p']);
						list($t, $m) = explode(',', $t);
						if(md5($uid.'|'.$t.$_G['config']['security']['authkey']) == $hash && !$m) {
							if(TIMESTAMP - $t <= $seccheckrule['vplimit']) {
								$seccode = true;
							} else {
								$seccode = false;
							}
						} else {
							$seccode = true;
						}
					}
				}
			}
		} else {
			$seccode = false;
		}
		return array(
			$seccode,
			$_G['setting']['secqaa']['status'] & 2 && (!$_G['setting']['secqaa']['minposts'] || getuserprofile('posts') < $_G['setting']['secqaa']['minposts'])
		);
	}

	public static function rule_publish($rule) {
		global $_G;
		$seccheckrule = & $_G['setting']['seccodedata']['rule']['post'];
		return array(
			$seccheckrule['allow'] && (!$_G['setting']['seccodedata']['minposts'] || getuserprofile('posts') < $_G['setting']['seccodedata']['minposts']),
			$_G['setting']['secqaa']['status'] & 2 && (!$_G['setting']['secqaa']['minposts'] || getuserprofile('posts') < $_G['setting']['secqaa']['minposts'])
		);
	}

	public static function rule_password($rule) {
		global $_G;
		$seccheckrule = & $_G['setting']['seccodedata']['rule']['password'];
		return array(
			$seccheckrule['allow'] && (!$_G['setting']['seccodedata']['minposts'] || getuserprofile('posts') < $_G['setting']['seccodedata']['minposts']),
			$_G['setting']['secqaa']['status'] & 4 && (!$_G['setting']['secqaa']['minposts'] || getuserprofile('posts') < $_G['setting']['secqaa']['minposts'])
		);
	}

	public static function rule_card() {
		global $_G;
		$seccheckrule = & $_G['setting']['seccodedata']['rule']['card'];
		return array($seccheckrule['allow']);
	}

	public static function seccheck($rule, $param = array()) {
		global $_G;
		if($_G['uid'] && !checkperm('seccode')) {
			return array();
		}
		if(method_exists('helper_seccheck', 'rule_'.$rule)) {
			$return = call_user_func(array('helper_seccheck', 'rule_'.$rule), $param);
			return $return;
		} else {
			return array();
		}
	}

}

?>