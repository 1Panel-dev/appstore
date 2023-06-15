<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$checkurl = array('wasu.cn');

function media_wasu($url, $width, $height) {
	if(preg_match("/https?:\/\/(www.|)wasu.cn\/(wap\/|)Play\/show\/id\/(\d+)/i", $url, $matches)) {
		$vid = $matches[3];
		$flv = '';
		$iframe = 'https://www.wasu.cn/Play/iframe/id/'.$vid;
		$imgurl = '';
	}
	return array($flv, $iframe, $url, $imgurl);
}