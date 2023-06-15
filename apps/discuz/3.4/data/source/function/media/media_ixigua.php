<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$checkurl = array('ixigua.com/');

function media_ixigua($url, $width, $height) {
	if(preg_match("/^https?:\/\/(|m.|www.)ixigua.com\/(\d+)/i", $url, $matches)) {
		$iframe = 'https://www.ixigua.com/iframe/'.$matches[2].'?autoplay=0';
		$flv = $imgurl = '';
	}
	return array($flv, $iframe, $url, $imgurl);
}