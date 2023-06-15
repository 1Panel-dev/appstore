<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$checkurl = array('v.ku6.com/show/', 'v.ku6.com/special/show_');

function media_ku6($url, $width, $height) {
	if(preg_match("/^http:\/\/v.ku6.com\/show\/([^\/]+).html/i", $url, $matches)) {
		$flv = 'http://player.ku6.com/refer/'.$matches[1].'/v.swf';
		if(!$width && !$height) {
			$api = 'http://vo.ku6.com/fetchVideo4Player/1/'.$matches[1].'.html';
			$str = dfsockopen($api);
			if(!empty($str) && preg_match("/\"picpath\":\"(.+?)\"/i", $str, $image)) {
				$imgurl = str_replace(array('\u003a', '\u002e'), array(':', '.'), $image[1]);
			}
		}
	} elseif(preg_match("/^http:\/\/v.ku6.com\/special\/show_\d+\/([^\/]+).html/i", $url, $matches)) {
		$flv = 'http://player.ku6.com/refer/'.$matches[1].'/v.swf';
		if(!$width && !$height) {
			$api = 'http://vo.ku6.com/fetchVideo4Player/1/'.$matches[1].'.html';
			$str = dfsockopen($api);
			if(!empty($str) && preg_match("/\"picpath\":\"(.+?)\"/i", $str, $image)) {
				$imgurl = str_replace(array('\u003a', '\u002e'), array(':', '.'), $image[1]);
			}
		}
	}
	return array($flv, $iframe, $url, $imgurl);
}