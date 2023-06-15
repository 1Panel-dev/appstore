<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$checkurl = array('tudou.com/programs/view/');

function media_tudou($url, $width, $height) {
	if(preg_match("/^http:\/\/(www.)?tudou.com\/programs\/view\/([^\/]+)/i", $url, $matches)) {
		$flv = 'http://www.tudou.com/v/'.$matches[2];
		$iframe = 'http://www.tudou.com/programs/view/html5embed.action?code='.$matches[2];
		if(!$width && !$height) {
			$str = dfsockopen($api);
			if(!empty($str) && preg_match("/<span class=\"s_pic\">(.+?)<\/span>/i", $str, $image)) {
				$imgurl = trim($image[1]);
			}
		}
	}
	return array($flv, $iframe, $url, $imgurl);
}