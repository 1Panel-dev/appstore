<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: index.php 34524 2014-05-15 04:42:23Z nemohou $
 */

if(version_compare(PHP_VERSION, '8.0.0', '>=')) {
	exit('This version of Discuz! is not compatible with >= PHP 8.0, Please install or update to higher version.');
}

if(!empty($_SERVER['QUERY_STRING']) && is_numeric($_SERVER['QUERY_STRING'])) {
	$_ENV['curapp'] = 'home';
	$_GET = array('mod'=>'space', 'uid'=>$_SERVER['QUERY_STRING']);
} else {

	$url = '';
	$domain = $_ENV = array();
	$jump = false;
	@include_once './data/sysdata/cache_domain.php';
	$_ENV['domain'] = $domain;
	if(empty($_ENV['domain'])) {
		$_ENV['curapp'] = 'forum';
	} else {
		$_ENV['defaultapp'] = array('portal.php' => 'portal', 'forum.php' => 'forum', 'group.php' => 'group', 'home.php' => 'home');
		$_ENV['hostarr'] = explode('.', $_SERVER['HTTP_HOST']);
		$_ENV['domainroot'] = substr($_SERVER['HTTP_HOST'], strpos($_SERVER['HTTP_HOST'], '.')+1);
		if(!empty($_ENV['domain']['app']) && is_array($_ENV['domain']['app']) && in_array($_SERVER['HTTP_HOST'], $_ENV['domain']['app'])) {
			$_ENV['curapp'] = array_search($_SERVER['HTTP_HOST'], $_ENV['domain']['app']);
			if($_ENV['curapp'] == 'mobile') {
				$_ENV['curapp'] = 'forum';
				if(!isset($_GET['mobile'])) {
					@$_GET['mobile'] = '2';
				}
			}
			if($_ENV['curapp'] == 'default' || !isset($_ENV['defaultapp'][$_ENV['curapp'].'.php'])) {
				$_ENV['curapp'] = '';
			}
		} elseif(!empty($_ENV['domain']['root']) && is_array($_ENV['domain']['root']) && in_array($_ENV['domainroot'], $_ENV['domain']['root'])) {

			$_G['setting']['holddomain'] = $_ENV['domain']['holddomain'] ? $_ENV['domain']['holddomain'] : array('www');
			$list = $_ENV['domain']['list'];
			if(isset($list[$_SERVER['HTTP_HOST']])) {
				$domain = $list[$_SERVER['HTTP_HOST']];
				switch($domain['idtype']) {
					case 'subarea':
						$_ENV['curapp'] = 'forum';
						$_GET['gid'] = intval($domain['id']);
						break;
					case 'forum':
						$_ENV['curapp'] = 'forum';
						$_GET['mod'] = 'forumdisplay';
						$_GET['fid'] = intval($domain['id']);
						break;
					case 'topic':
						$_ENV['curapp'] = 'portal';
						$_GET['mod'] = 'topic';
						$_GET['topicid'] = intval($domain['id']);
						break;
					case 'channel':
						$_ENV['curapp'] = 'portal';
						$_GET['mod'] = 'list';
						$_GET['catid'] = intval($domain['id']);
						break;
					case 'plugin':
						$_ENV['curapp'] = 'plugin';
						$_GET['id'] = $domain['id'];
						$_GET['fromapp'] = 'index';
						break;
				}
			} elseif(count($_ENV['hostarr']) > 2 && $_ENV['hostarr'][0] != 'www' && !checkholddomain($_ENV['hostarr'][0])) {
				$_ENV['prefixdomain'] = addslashes($_ENV['hostarr'][0]);
				$_ENV['domainroot'] = addslashes($_ENV['domainroot']);
				require_once './source/class/class_core.php';
				C::app()->init_setting = true;
				C::app()->init_user = false;
				C::app()->init_session = false;
				C::app()->init_cron = false;
				C::app()->init_misc = false;
				C::app()->init();
				$jump = true;
				$domain = C::t('common_domain')->fetch_by_domain_domainroot($_ENV['prefixdomain'], $_ENV['domainroot']);
				$apphost = $_ENV['domain']['app'][$domain['idtype']] ? $_ENV['domain']['app'][$domain['idtype']] : $_ENV['domain']['app']['default'];
				$apphost = $apphost ? $_G['scheme'].'://'.$apphost.'/' : '';
				switch($domain['idtype']) {
					case 'home':
						if($_G['setting']['rewritestatus'] && in_array('home_space', $_G['setting']['rewritestatus'])) {
							$url = rewriteoutput('home_space', 1, $apphost, $domain['id']);
						} else {
							$url = $apphost.'home.php?mod=space&uid='.$domain['id'];
						}
						break;
					case 'group':
						if($_G['setting']['rewritestatus'] && in_array('group_group', $_G['setting']['rewritestatus'])) {
							$url = rewriteoutput('group_group', 1, $apphost, $domain['id']);
						} else {
							$url = $apphost.'forum.php?mod=group&fid='.$domain['id'].'&page=1';
						}
						break;
				}
			}
		} else {
			$jump = true;
		}
		if(empty($url) && empty($_ENV['curapp'])) {
			if(!empty($_ENV['domain']['defaultindex']) && !$jump) {
				if($_ENV['defaultapp'][$_ENV['domain']['defaultindex']]) {
					$_ENV['curapp'] = $_ENV['defaultapp'][$_ENV['domain']['defaultindex']];
				} else {
					$url = $_ENV['domain']['defaultindex'];
				}
			} else {
				if($jump) {
					$url = empty($_ENV['domain']['app']['default']) ? (!empty($_ENV['domain']['defaultindex']) ? $_ENV['domain']['defaultindex'] : 'forum.php') : (is_https() ? 'https' : 'http').'://'.$_ENV['domain']['app']['default'];
				} else {
					$_ENV['curapp'] = 'forum';
				}
			}
		}
	}
}
if(!empty($url)) {
	$delimiter = strrpos($url, '?') ? '&' : '?';
	if(isset($_GET['fromuid']) && $_GET['fromuid']) {
		$url .= sprintf('%sfromuid=%d', $delimiter, $_GET['fromuid']);
	} elseif(isset($_GET['fromuser']) && $_GET['fromuser']) {
		$url .= sprintf('%sfromuser=%s', $delimiter, rawurlencode($_GET['fromuser']));
	}
	$parse = parse_url($url);
	if(!isset($parse['host']) && file_exists($parse['path']) && preg_match("/^[\w-]+\.php$/i", $parse['path'])) {
		if(!empty($parse['query'])) {
			parse_str($parse['query'], $_GET);
		}
		require './'.$parse['path'];
	} else {
		header("location: $url");
	}
} else {
	if(preg_match("/^[\w-]+$/i", $_ENV['curapp'])) {
		require './'.$_ENV['curapp'].'.php';
	} else {
		header('location: ./'.$_ENV['curapp'].'.php');
	}
}

function checkholddomain($domain) {
	global $_G;

	$domain = strtolower($domain);
	if(preg_match("/^[^a-z]/i", $domain)) return true;
	$holdmainarr = empty($_G['setting']['holddomain']) ? array('www') : explode('|', $_G['setting']['holddomain']);
	$ishold = false;
	foreach ($holdmainarr as $value) {
		if(strpos($value, '*') === false) {
			if(strtolower($value) == $domain) {
				$ishold = true;
				break;
			}
		} else {
			$value = str_replace('*', '.*?', $value);
			if(@preg_match("/$value/i", $domain)) {
				$ishold = true;
				break;
			}
		}
	}
	return $ishold;
}

function is_https() {
	if(isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') {
		return true;
	}
	if(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') {
		return true;
	}
	if(isset($_SERVER['HTTP_X_CLIENT_SCHEME']) && strtolower($_SERVER['HTTP_X_CLIENT_SCHEME']) == 'https') {
		return true;
	}
	if(isset($_SERVER['HTTP_FROM_HTTPS']) && strtolower($_SERVER['HTTP_FROM_HTTPS']) != 'off') {
		return true;
	}
	if(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
		return true;
	}
	return false;
}

?>