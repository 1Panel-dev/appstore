<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_filesock.php 36279 2016-12-09 07:54:31Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function _isLocalip($ip) {
	$iplong = ip2long($ip);
	return ($iplong >= 167772160 && $iplong <= 184549375) ||
		($iplong >= 2886729728 && $iplong <= 2887778303) ||
		($iplong >= 1681915904 && $iplong <= 1686110207) ||
		($iplong >= 3232235520 && $iplong <= 3232301055) ||
		($iplong >= 150994944 && $iplong <= 167772159);
}

function _parse_url($url) {
	global $_G;
	$tmp = parse_url($url);
	if(!$tmp || empty($tmp['host'])) return false;
	if(isset($tmp['user']) || isset($tmp['pass'])) return false;
	if(strpbrk($tmp['host'], ':#?[]' ) !== false) return false;
	if(!in_array(strtolower($tmp['scheme']), array('http', 'https'))) {
		return false;
	}
	$config = $_G['config']['security']['fsockopensafe'];

	$ip = gethostbyname($tmp['host']);
	if($ip == $tmp['host']) {
		return false;
	}
	if(filter_var($tmp['host'], FILTER_VALIDATE_IP) && _isLocalip($tmp['host'])) {
		return false;
	}

	if(!empty($config['port']) && isset($tmp['port'])) {
		if(isset($_SERVER['SERVER_PORT']) && !in_array($_SERVER['SERVER_PORT'], $config['port'])) {
			$config['port'][] = $_SERVER['SERVER_PORT'];
		}
		if(!in_array($tmp['port'], $config['port'])) {
			return false;
		}
	}

	if(!isset($tmp['port'])) {
		$tmp['port'] = strtolower($tmp['scheme']) == 'https' ? 443 : 80;
	}

	if($ip) {
		if(!_isLocalip($ip)) {
			$tmp['ip'] = $ip;
			return $tmp;
		}
	} else {
		return $tmp;
	}
}

function _dfsockopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE, $encodetype  = 'URLENCODE', $allowcurl = TRUE, $position = 0, $files = array()) {
	$return = '';
	$matches = _parse_url($url);
	if(!$matches) {
		return '';
	}
	$ip = isset($matches['ip']) ? $matches['ip'] : $ip;
	$scheme = $matches['scheme'];
	$host = $matches['host'];
	if($ip && _isLocalip($ip)) {
		return '';
	}
	$path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
	$port = !empty($matches['port']) ? $matches['port'] : (strtolower($scheme) == 'https' ? 443 : 80);
	$boundary = $encodetype == 'URLENCODE' ? '' : random(40);

	if($post) {
		if(!is_array($post)) {
			parse_str($post, $post);
		}
		_format_postkey($post, $postnew);
		$post = $postnew;
	}
	if(function_exists('curl_init') && function_exists('curl_exec') && $allowcurl) {
		$ch = curl_init();
		$httpheader = array();
		if($ip) {
			$httpheader[] = "Host: ".$host;
		}
		$httpheader[] = "User-Agent: ".$_SERVER['HTTP_USER_AGENT'];
		if($httpheader) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
		}
		if(!empty($ip) && filter_var($ip, FILTER_VALIDATE_IP) && !filter_var($host, FILTER_VALIDATE_IP) && version_compare(PHP_VERSION, '5.5.0', 'ge')) {
			curl_setopt($ch, CURLOPT_RESOLVE, array("$host:$port:$ip"));
			curl_setopt($ch, CURLOPT_URL, $scheme.'://'.$host.':'.$port.$path);
		} else {
			curl_setopt($ch, CURLOPT_URL, $scheme.'://'.($ip ? $ip : $host).':'.$port.$path);
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		if($post) {
			curl_setopt($ch, CURLOPT_POST, 1);
			if($encodetype == 'URLENCODE') {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			} else {
				foreach($post as $k => $v) {
					if(isset($files[$k])) {
						$post[$k] = '@'.$files[$k];
					}
				}
				foreach($files as $k => $file) {
					if(!isset($post[$k]) && file_exists($file)) {
						$post[$k] = '@'.$file;
					}
				}
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			}
		}
		if($cookie) {
			curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		}
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		$data = curl_exec($ch);
		$status = curl_getinfo($ch);
		$errno = curl_errno($ch);
		curl_close($ch);
		if($errno || $status['http_code'] != 200) {
			return;
		} else {
			$GLOBALS['filesockheader'] = substr($data, 0, $status['header_size']);
			$data = substr($data, $status['header_size']);
			return !$limit ? $data : substr($data, 0, $limit);
		}
	}

	if($post) {
		if($encodetype == 'URLENCODE') {
			$data = http_build_query($post);
		} else {
			$data = '';
			foreach($post as $k => $v) {
				$data .= "--$boundary\r\n";
				$data .= 'Content-Disposition: form-data; name="'.$k.'"'.(isset($files[$k]) ? '; filename="'.basename($files[$k]).'"; Content-Type: application/octet-stream' : '')."\r\n\r\n";
				$data .= $v."\r\n";
			}
			foreach($files as $k => $file) {
				if(!isset($post[$k]) && file_exists($file)) {
					if($fp = @fopen($file, 'r')) {
						$v = fread($fp, filesize($file));
						fclose($fp);
						$data .= "--$boundary\r\n";
						$data .= 'Content-Disposition: form-data; name="'.$k.'"; filename="'.basename($file).'"; Content-Type: application/octet-stream'."\r\n\r\n";
						$data .= $v."\r\n";
					}
				}
			}
			$data .= "--$boundary\r\n";
		}
		$out = "POST $path HTTP/1.0\r\n";
		$header = "Accept: */*\r\n";
		$header .= "Accept-Language: zh-cn\r\n";
		$header .= $encodetype == 'URLENCODE' ? "Content-Type: application/x-www-form-urlencoded\r\n" : "Content-Type: multipart/form-data; boundary=$boundary\r\n";
		$header .= 'Content-Length: '.strlen($data)."\r\n";
		$header .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$header .= "Host: $host:$port\r\n";
		$header .= "Connection: Close\r\n";
		$header .= "Cache-Control: no-cache\r\n";
		$header .= "Cookie: $cookie\r\n\r\n";
		$out .= $header;
		$out .= $data;
	} else {
		$out = "GET $path HTTP/1.0\r\n";
		$header = "Accept: */*\r\n";
		$header .= "Accept-Language: zh-cn\r\n";
		$header .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
		$header .= "Host: $host:$port\r\n";
		$header .= "Connection: Close\r\n";
		$header .= "Cookie: $cookie\r\n\r\n";
		$out .= $header;
	}

	$fpflag = 0;
	if(!$fp = @fsocketopen(($scheme == 'https' ? 'ssl://' : '').($scheme == 'https' ? $host : ($ip ? $ip : $host)), $port, $errno, $errstr, $timeout)) {
		$context = array(
			'http' => array(
				'method' => $post ? 'POST' : 'GET',
				'header' => $header,
				'content' => $post,
				'timeout' => $timeout,
			),
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
			),
		);
		$context = stream_context_create($context);
		$fp = @fopen($scheme.'://'.($scheme == 'https' ? $host : ($ip ? $ip : $host)).':'.$port.$path, 'b', false, $context);
		$fpflag = 1;
	}

	if(!$fp) {
		return '';
	} else {
		stream_set_blocking($fp, $block);
		stream_set_timeout($fp, $timeout);
		@fwrite($fp, $out);
		$status = stream_get_meta_data($fp);
		if(!$status['timed_out']) {
			while (!feof($fp) && !$fpflag) {
				$header = @fgets($fp);
				$headers .= $header;
				if($header && ($header == "\r\n" ||  $header == "\n")) {
					break;
				}
			}
			$GLOBALS['filesockheader'] = $headers;

			if($position) {
				for($i=0; $i<$position; $i++) {
					$char = fgetc($fp);
					if($char == "\n" && $oldchar != "\r") {
						$i++;
					}
					$oldchar = $char;
				}
			}

			if($limit) {
				$return = stream_get_contents($fp, $limit);
			} else {
				$return = stream_get_contents($fp);
			}
		}
		@fclose($fp);
		return $return;
	}
}

function _format_postkey($post, &$result, $key = '') {
	foreach($post as $k => $v) {
		$_k = $key ? $key.'['.$k.']' : $k;
		if(is_array($v)) {
			_format_postkey($v, $result, $_k);
		} else {
			$result[$_k] = $v;
		}
	}
}

?>