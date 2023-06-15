<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
*      This is NOT a freeware, use is subject to license terms
*
*      $Id: uploadavatar.php 34944 2014-09-05 08:06:41Z nemohou $
*/

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

$_GET['mod'] = 'spacecp';
$_GET['ac'] = 'avatar';
include_once 'home.php';

class mobile_api {

	public static $tmpavatar;
	public static $tmpavatarbig;
	public static $tmpavatarmiddle;
	public static $tmpavatarsmall;

	public static function common() {
		global $_G;
		if(empty($_G['uid'])) {
			self::error('api_uploadavatar_unavailable_user');
		}
		if(empty($_FILES['Filedata'])) {
			self::error('api_uploadavatar_unavailable_pic');
		}

		list($width, $height, $type, $attr) = getimagesize($_FILES['Filedata']['tmp_name']);
		$imgtype = array(1 => '.gif', 2 => '.jpg', 3 => '.png');
		$filetype = $imgtype[$type];
		if (!$filetype) {
			$filetype = '.jpg';
		}
		$avatarpath = $_G['setting']['attachdir'];
		$tmpavatar = $avatarpath.'./temp/upload'.$_G['uid'].$filetype;
		file_exists($tmpavatar) && @unlink($tmpavatar);
		if(@copy($_FILES['Filedata']['tmp_name'], $tmpavatar) || @move_uploaded_file($_FILES['Filedata']['tmp_name'], $tmpavatar)) {
			@unlink($_FILES['Filedata']['tmp_name']);
			list($width, $height, $type, $attr) = getimagesize($tmpavatar);
			if($width < 10 || $height < 10 || $type == 4) {
				@unlink($tmpavatar);
				self::error('api_uploadavatar_unusable_image');
			}
		} else {
			@unlink($_FILES['Filedata']['tmp_name']);
			self::error('api_uploadavatar_service_unwritable');
		}
		$tmpavatarbig = './temp/upload'.$_G['uid'].'big'.$filetype;
		$tmpavatarmiddle = './temp/upload'.$_G['uid'].'middle'.$filetype;
		$tmpavatarsmall = './temp/upload'.$_G['uid'].'small'.$filetype;
		$image = new image;
		if($image->Thumb($tmpavatar, $tmpavatarbig, 200, 250, 1) <= 0) {
			self::error('api_uploadavatar_unusable_image');
		}
		if($image->Thumb($tmpavatar, $tmpavatarmiddle, 120, 120, 1) <= 0) {
			self::error('api_uploadavatar_unusable_image');
		}
		if($image->Thumb($tmpavatar, $tmpavatarsmall, 48, 48, 2) <= 0) {
			self::error('api_uploadavatar_unusable_image');
		}

		self::$tmpavatar = $tmpavatar;
		self::$tmpavatarbig = $avatarpath.$tmpavatarbig;
		self::$tmpavatarmiddle = $avatarpath.$tmpavatarmiddle;
		self::$tmpavatarsmall = $avatarpath.$tmpavatarsmall;
	}

	public static function output() {
		global $_G;
		if(!empty($_G['uid'])) {
			if (self::$tmpavatarbig && self::$tmpavatarmiddle && self::$tmpavatarsmall) {
				$avatar1 = self::byte2hex(file_get_contents(self::$tmpavatarbig));
				$avatar2 = self::byte2hex(file_get_contents(self::$tmpavatarmiddle));
				$avatar3 = self::byte2hex(file_get_contents(self::$tmpavatarsmall));

				$extra = '&avatar1='.$avatar1.'&avatar2='.$avatar2.'&avatar3='.$avatar3;
				$result = self::uc_api_post_ex('user', 'rectavatar', array('uid' => $_G['uid']), $extra);

				@unlink(self::$tmpavatar);
				@unlink(self::$tmpavatarbig);
				@unlink(self::$tmpavatarmiddle);
				@unlink(self::$tmpavatarsmall);

				if($result == '<?xml version="1.0" ?><root><face success="1"/></root>') {
					$variable = array(
						'uploadavatar' => 'api_uploadavatar_success',
					);
					C::t('common_member')->update($_G['uid'], array('avatarstatus'=>'1'));
					mobile_core::result(mobile_core::variable($variable));
				} else {
					self::error('api_uploadavatar_uc_error');
				}
			}
		} else {
			self::error('api_uploadavatar_unavailable_user');
		}
	}

	public static function byte2hex($string) {
		$buffer = '';
		$value = unpack('H*', $string);
		$value = str_split($value[1], 2);
		$b = '';
		foreach($value as $k => $v) {
			$b .= strtoupper($v);
		}

		return $b;
	}

	public static function uc_api_post_ex($module, $action, $arg = array(), $extra = '') {
		$s = $sep = '';
		foreach($arg as $k => $v) {
			$k = urlencode($k);
			if(is_array($v)) {
				$s2 = $sep2 = '';
				foreach($v as $k2 => $v2) {
					$k2 = urlencode($k2);
					$s2 .= "$sep2{$k}[$k2]=".urlencode(uc_stripslashes($v2));
					$sep2 = '&';
				}
				$s .= $sep.$s2;
			} else {
				$s .= "$sep$k=".urlencode(uc_stripslashes($v));
			}
			$sep = '&';
		}
		$postdata = uc_api_requestdata($module, $action, $s, $extra);
		return uc_fopen2(UC_API.'/index.php', 500000, $postdata, '', TRUE, UC_IP, 20);
	}

	public static function error($errstr) {
		$variable = array(
			'uploadavatar' => $errstr,
		);
		mobile_core::result(mobile_core::variable($variable));
	}

}

?>