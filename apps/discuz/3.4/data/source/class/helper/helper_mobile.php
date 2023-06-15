<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: helper_mobile.php 36342 2017-01-09 01:15:30Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class helper_mobile {


	public static function mobileoutput() {
		global $_G;
		if(!defined('TPL_DEFAULT')) {
			$content = ob_get_contents();
			ob_end_clean();
			$content = preg_replace_callback("/href=\"(\w+\.php)(.*?)\"/", array(__CLASS__, 'mobileoutput_callback_mobilereplace_12'), $content);

			ob_start();
			$content = '<?xml version="1.0" encoding="utf-8"?>'.$content;
			if('utf-8' != CHARSET) {
				$content = diconv($content, CHARSET, 'utf-8');
			}
			if(IN_MOBILE === '3') {
				header("Content-type: text/vnd.wap.wml; charset=utf-8");
			} else {
				@header('Content-Type: text/html; charset=utf-8');
			}
			echo $content;
			exit();

		} elseif (defined('TPL_DEFAULT') && !$_G['cookie']['dismobilemessage'] && $_G['mobile']) {
			ob_end_clean();
			ob_start();
			$_G['forcemobilemessage'] = true;
			parse_str($_SERVER['QUERY_STRING'], $query);
			$query['forcemobile'] = '1';
			$query_sting_tmp = http_build_query($query);
			$_G['setting']['mobile']['pageurl'] = $_G['siteurl'].basename($_G['PHP_SELF']).'?'.$query_sting_tmp;
			unset($query_sting_tmp);
			showmessage('not_in_mobile');
			exit;
		}
	}

	public static function mobileoutput_callback_mobilereplace_12($matches) {
		return self::mobilereplace($matches[1], $matches[2]);
	}

	public static function mobilereplace($file, $replace) {
		if(strpos($replace, 'mobile=') === false) {
			if(strpos($replace, '?') === false) {
				$replace = 'href="'.$file.$replace.'?mobile='.IN_MOBILE.'"';
			} else {
				$replace = 'href="'.$file.$replace.'&amp;mobile='.IN_MOBILE.'"';
			}
			return $replace;
		} else {
			return 'href="'.$file.$replace.'"';
		}
	}
}

?>