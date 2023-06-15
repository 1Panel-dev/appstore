<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$checkurl = array('v.youku.com/v_show/');

function media_youku($url, $width, $height) {
	$ctx = stream_context_create(array('http' => array('timeout' => 10)));
	if(preg_match("/^https?:\/\/v.youku.com\/v_show\/id_([^\/]+)(.html|)/i", $url, $matches)) {
		$params = explode('.', $matches[1]);
		$flv = 'https://player.youku.com/player.php/sid/'.$params[0].'/v.swf';
		$iframe = 'https://player.youku.com/embed/'.$params[0];
		if(!$width && !$height) {
			$api = 'http://v.youku.com/player/getPlayList/VideoIDS/'.$params[0];
			$str = stripslashes(dfsockopen($api));
			if(!empty($str) && preg_match("/\"logo\":\"(.+?)\"/i", $str, $image)) {
				$url = substr($image[1], 0, strrpos($image[1], '/')+1);
				$filename = substr($image[1], strrpos($image[1], '/')+2);
				$imgurl = $url.'0'.$filename;
			}
		}
	}
	return array($flv, $iframe, $url, $imgurl);
}