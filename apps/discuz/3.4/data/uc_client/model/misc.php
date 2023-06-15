<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: misc.php 1127 2011-12-14 04:24:58Z svn_project_zhangjie $
*/

!defined('IN_UC') && exit('Access Denied');

define('UC_ARRAY_SEP_1', 'UC_ARRAY_SEP_1');
define('UC_ARRAY_SEP_2', 'UC_ARRAY_SEP_2');

class miscmodel {

	var $db;
	var $base;

	function __construct(&$base) {
		$this->miscmodel($base);
	}

	function miscmodel(&$base) {
		$this->base = $base;
		$this->db = $base->db;
	}

	function get_apps($col = '*', $where = '') {
		$arr = $this->db->fetch_all("SELECT $col FROM ".UC_DBTABLEPRE."applications".($where ? ' WHERE '.$where : ''));
		return $arr;
	}

	function delete_apps($appids) {
	}

	function update_app($appid, $name, $url, $authkey, $charset, $dbcharset) {
	}

	function alter_app_table($appid, $operation = 'ADD') {
	}

	function get_host_by_url($url) {
	}

	function check_url($url) {
	}

	function check_ip($url) {
	}

	function test_api($url, $ip = '') {
	}

	function dfopen2($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE, $encodetype  = 'URLENCODE', $allowcurl = TRUE) {
		$__times__ = isset($_GET['__times__']) ? intval($_GET['__times__']) + 1 : 1;
		if($__times__ > 2) {
			return '';
		}
		$url .= (strpos($url, '?') === FALSE ? '?' : '&')."__times__=$__times__";
		return $this->dfopen($url, $limit, $post, $cookie, $bysocket, $ip, $timeout, $block, $encodetype, $allowcurl);
	}

	function dfopen($url, $limit = 0, $post = '', $cookie = '', $bysocket = FALSE, $ip = '', $timeout = 15, $block = TRUE, $encodetype  = 'URLENCODE', $allowcurl = TRUE) {
		$return = '';
		$matches = parse_url($url);
		$scheme = strtolower($matches['scheme']);
		$host = $matches['host'];
		$path = !empty($matches['path']) ? $matches['path'].(!empty($matches['query']) ? '?'.$matches['query'] : '') : '/';
		$port = !empty($matches['port']) ? $matches['port'] : ($scheme == 'https' ? 443 : 80);

		if(function_exists('curl_init') && function_exists('curl_exec') && $allowcurl) {
			$ch = curl_init();
			$ip && curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host: ".$host));
			curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
			if(!empty($ip) && filter_var($ip, FILTER_VALIDATE_IP) && !filter_var($host, FILTER_VALIDATE_IP) && version_compare(PHP_VERSION, '5.5.0', 'ge')) {
				curl_setopt($ch, CURLOPT_RESOLVE, array("$host:$port:$ip"));
				curl_setopt($ch, CURLOPT_URL, $scheme.'://'.$host.':'.$port.$path);
			} else {
				curl_setopt($ch, CURLOPT_URL, $scheme.'://'.($ip ? $ip : $host).':'.$port.$path);
			}
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			if($post) {
				curl_setopt($ch, CURLOPT_POST, 1);
				if($encodetype == 'URLENCODE') {
					curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
				} else {
					parse_str($post, $postarray);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $postarray);
				}
			}
			if($cookie) {
				curl_setopt($ch, CURLOPT_COOKIE, $cookie);
			}
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$data = curl_exec($ch);
			$status = curl_getinfo($ch);
			$errno = curl_errno($ch);
			curl_close($ch);
			if($errno || $status['http_code'] != 200) {
				return;
			} else {
				return !$limit ? $data : substr($data, 0, $limit);
			}
		}

		if($post) {
			$out = "POST $path HTTP/1.0\r\n";
			$header = "Accept: */*\r\n";
			$header .= "Accept-Language: zh-cn\r\n";
			if($allowcurl) {
				$encodetype = 'URLENCODE';
			}
			$boundary = $encodetype == 'URLENCODE' ? '' : '; boundary='.trim(substr(trim($post), 2, strpos(trim($post), "\n") - 2));
			$header .= $encodetype == 'URLENCODE' ? "Content-Type: application/x-www-form-urlencoded\r\n" : "Content-Type: multipart/form-data$boundary\r\n";
			$header .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
			$header .= "Host: $host:$port\r\n";
			$header .= 'Content-Length: '.strlen($post)."\r\n";
			$header .= "Connection: Close\r\n";
			$header .= "Cache-Control: no-cache\r\n";
			$header .= "Cookie: $cookie\r\n\r\n";
			$out .= $header.$post;
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
		$context = array();
		if($scheme == 'https') {
			$context['ssl'] = array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'peer_name' => $host
			);
			if(version_compare(PHP_VERSION, '5.6.0', '<')) {
				$context['ssl']['SNI_enabled'] = true;
				$context['ssl']['SNI_server_name'] = $host;
			}
		}
		if(ini_get('allow_url_fopen')) {
			$context['http'] = array(
				'method' => $post ? 'POST' : 'GET',
				'header' => $header,
				'timeout' => $timeout
			);
			if($post) {
				$context['http']['content'] = $post;
			}
			$context = stream_context_create($context);
			$fp = @fopen($scheme.'://'.($ip ? $ip : $host).':'.$port.$path, 'b', false, $context);
			$fpflag = 1;
		} elseif(function_exists('stream_socket_client')) {
			$context = stream_context_create($context);
			$fp = @stream_socket_client(($scheme == 'https' ? 'ssl://' : '').($ip ? $ip : $host).':'.$port, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
		} else {
			$fp = @fsocketopen(($scheme == 'https' ? 'ssl://' : '').($scheme == 'https' ? $host : ($ip ? $ip : $host)), $port, $errno, $errstr, $timeout);
		}

		if(!$fp) {
			return '';
		} else {
			stream_set_blocking($fp, $block);
			stream_set_timeout($fp, $timeout);
			if(!$fpflag) {
				@fwrite($fp, $out);
			}
			$status = stream_get_meta_data($fp);
			if(!$status['timed_out']) {
				while (!feof($fp) && !$fpflag) {
					if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n")) {
						break;
					}
				}

				$stop = false;
				while(!feof($fp) && !$stop) {
					$data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
					$return .= $data;
					if($limit) {
						$limit -= strlen($data);
						$stop = $limit <= 0;
					}
				}
			}
			@fclose($fp);
			return $return;
		}
	}

	function array2string($arr) {
		$s = $sep = '';
		if($arr && is_array($arr)) {
			foreach($arr as $k => $v) {
				$s .= $sep.addslashes($k).UC_ARRAY_SEP_1.$v;
				$sep = UC_ARRAY_SEP_2;
			}
		}
		return $s;
	}

	function string2array($s) {
		$arr = explode(UC_ARRAY_SEP_2, $s);
		$arr2 = array();
		foreach($arr as $k => $v) {
			list($key, $val) = explode(UC_ARRAY_SEP_1, $v);
			$arr2[$key] = $val;
		}
		return $arr2;
	}
}

?>