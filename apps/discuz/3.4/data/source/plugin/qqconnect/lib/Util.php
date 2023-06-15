<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: Util.php 31728 2012-09-25 09:03:42Z zhouxiaobo $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_Util {

	protected static $_instance;

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {

	}

	public function generateSiteSignUrl($params = array(), $isCamelCase = false, $isReturnArray = false) {
		global $_G;

		$ts = TIMESTAMP;
		$sId = $_G['setting']['my_siteid'];
		$sKey = $_G['setting']['my_sitekey'];
		$uid = $_G['uid'];

		if(!is_array($params)) {
			$params = array();
		}

		unset($params['sig'], $params['ts']);

		if ($isCamelCase) {
			$params['sId'] = $sId;
			$params['sSiteUid'] = $uid;
		} else {
			$params['s_id'] = $sId;
			$params['s_site_uid'] = $uid;
		}

		ksort($params);

		$str = $this->httpBuildQuery($params, '', '&');
		$sig = md5(sprintf('%s|%s|%s', $str, $sKey, $ts));

		$params['ts'] = $ts;
		$params['sig'] = $sig;

		if(!$isReturnArray) {
			$params = $this->httpBuildQuery($params, '', '&');
		}

		return $params;
	}

	public function redirect($url, $code = 302) {

		@ob_end_clean();
		@ob_start();

		$errorChars = array();
		for ($i = 0; $i <= 31; $i ++) {
			$errorChars[] = chr($i);
		}

		$url = trim(str_replace($errorChars, '', $url));

		if (strpos($url, '/') === 0) {
			$url = '/' . ltrim($url, '/');
		}

		@header('Location: ' . $url, true, $code);

		exit;
	}

	public function generateUniqueId() {
		$siteuniqueid = C::t('common_setting')->fetch('siteuniqueid');
		if(empty($siteuniqueid) || strlen($siteuniqueid) < 16) {
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
			$siteuniqueid = 'DX'.$chars[date('y')%60].$chars[date('n')].$chars[date('j')].$chars[date('G')].$chars[date('i')].$chars[date('s')].substr(md5($_G['clientip'].$_G['username'].TIMESTAMP), 0, 4).random(4);
			C::t('common_setting')->update('siteuniqueid', $siteuniqueid);
			require_once libfile('function/cache');
			updatecache('setting');
		}
	}

	public function httpBuildQuery($data, $numeric_prefix='', $arg_separator='', $prefix='') {

		$render = array();
		if (empty($arg_separator)) {
			$arg_separator = @ini_get('arg_separator.output');
			empty($arg_separator) && $arg_separator = '&';
		}
		foreach ((array) $data as $key => $val) {
			if (is_array($val) || is_object($val)) {
				$_key = empty($prefix) ? "{$key}[%s]" : sprintf($prefix, $key) . "[%s]";
				$_render = $this->httpBuildQuery($val, '', $arg_separator, $_key);
				if (!empty($_render)) {
					$render[] = $_render;
				}
			} else {
				if (is_numeric($key) && empty($prefix)) {
					$render[] = urlencode("{$numeric_prefix}{$key}") . "=" . urlencode($val);
				} else {
					if (!empty($prefix)) {
						$_key = sprintf($prefix, $key);
						$render[] = urlencode($_key) . "=" . urlencode($val);
					} else {
						$render[] = urlencode($key) . "=" . urlencode($val);
					}
				}
			}
		}
		$render = implode($arg_separator, $render);
		if (empty($render)) {
			$render = '';
		}

		return $render;
	}

	public function getApiVersion() {

		return '0.6';
	}

	public function hashHmac($algo, $data, $key, $raw_output = false) {
		if (function_exists('hash_hmac')) {
			return hash_hmac($algo, $data, $key, $raw_output);
		} else {
			$algo = strtolower($algo);
			$pack = 'H'.strlen(call_user_func($algo, 'test'));
			$size = 64;
			$opad = str_repeat(chr(0x5C), $size);
			$ipad = str_repeat(chr(0x36), $size);

			if(strlen($key) > $size) {
				$key = str_pad(pack($pack, call_user_func($algo, $key)), $size, chr(0x00));
			} else {
				$key = str_pad($key, $size, chr(0x00));
			}

			for ($i = 0; $i < strlen($key) - 1; $i++) {
				$opad[$i] = $opad[$i] ^ $key[$i];
				$ipad[$i] = $ipad[$i] ^ $key[$i];
			}

			$output = call_user_func($algo, $opad.pack($pack, call_user_func($algo, $ipad.$data)));

			return ($raw_output) ? pack($pack, $output) : $output;
		}
	}

	public function isMobile($status) {
		if (getstatus($status, 11) || getstatus($status, 12) || getstatus($status, 13)) {
			return true;
		}
		return false;
	}

	public function mobileHasSound() {
		if (getstatus($status, 13)) {
			return true;
		}
		return false;
	}

	public function mobileHasPhoto() {
		if (getstatus($status, 12) && getstatus($status, 11)) {
			return true;
		}
		return false;
	}

	public function mobileHasGPS() {
		if (getstatus($status, 12)) {
			return true;
		}
		return false;
	}

	public function isfounder($user) {
		global $_G;
		$founders = str_replace(' ', '', $_G['config']['admincp']['founder']);
		if(!$user['uid'] || $user['groupid'] != 1 || $user['adminid'] != 1) {
			return false;
		} elseif(empty($founders)) {
			return false;
		} elseif(strexists(",$founders,", ",$user[uid],")) {
			return true;
		} elseif(!is_numeric($user['username']) && strexists(",$founders,", ",$user[username],")) {
			return true;
		} else {
			return FALSE;
		}
	}

}