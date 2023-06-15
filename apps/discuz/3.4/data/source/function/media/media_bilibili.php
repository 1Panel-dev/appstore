<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$checkurl = array('bilibili.com/video/', 'bilibili.tv/video/', 'acg.tv', 'b23.tv');

function media_bilibili($url, $width, $height) {
	if(preg_match("/https?:\/\/(m.|www.|)bilibili.(com|tv)\/video\/(a|b)v([A-Za-z0-9]+)(\/?.*?&p=|\/?\?p=)?(\d+)?/i", $url, $matches)) {
		$vid = (is_numeric($matches[4]) ? 'aid='.$matches[4] : 'bvid='.$matches[4]) . (empty($matches[6]) ? '' : '&page='.intval($matches[6]));
		$flv = '';
		$iframe = 'https://player.bilibili.com/player.html?'.$vid;
		$imgurl = '';
	} else if(preg_match("/https?:\/\/(www.|)(acg|b23).tv\/(a|b)v([A-Za-z0-9]+)(\/?.*?&p=|\/?\?p=)?(\d+)?/i", $url, $matches)) {
		$vid = (is_numeric($matches[4]) ? 'aid='.$matches[4] : 'bvid='.$matches[4]) . (empty($matches[6]) ? '' : '&page='.intval($matches[6]));
		$flv = '';
		$iframe = 'https://player.bilibili.com/player.html?'.$vid;
		$imgurl = '';
	}
	return array($flv, $iframe, $url, $imgurl);
}