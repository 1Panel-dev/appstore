<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$checkurl = array('my.tv.sohu.com/u/', 'v.blog.sohu.com/u/');

function media_sohu($url, $width, $height) {
	if(preg_match("/^http:\/\/my.tv.sohu.com\/u\/[^\/]+\/(\d+)/i", $url, $matches)) {
		$flv = 'http://v.blog.sohu.com/fo/v4/'.$matches[1];
		if(!$width && !$height) {
			$api = 'http://v.blog.sohu.com/videinfo.jhtml?m=view&id='.$matches[1].'&outType=3';
			$str = dfsockopen($api);
			if(!empty($str) && preg_match("/\"cutCoverURL\":\"(.+?)\"/i", $str, $image)) {
				$imgurl = str_replace(array('\u003a', '\u002e'), array(':', '.'), $image[1]);
			}
		}
	} elseif(preg_match("/^http:\/\/v.blog.sohu.com\/u\/[^\/]+\/(\d+)/i", $url, $matches)) {
		$flv = 'http://v.blog.sohu.com/fo/v4/'.$matches[1];
		if(!$width && !$height) {
			$api = 'http://v.blog.sohu.com/videinfo.jhtml?m=view&id='.$matches[1].'&outType=3';
			$str = dfsockopen($api);
			if(!empty($str) && preg_match("/\"cutCoverURL\":\"(.+?)\"/i", $str, $image)) {
				$imgurl = str_replace(array('\u003a', '\u002e'), array(':', '.'), $image[1]);
			}
		}
	}
	return array($flv, $iframe, $url, $imgurl);
}