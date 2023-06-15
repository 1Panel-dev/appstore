<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: qrcode.inc.php 34711 2014-07-11 06:16:56Z nemohou $
 */
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$dir = DISCUZ_ROOT.'./data/cache/qrcode/';

$_G['wechat']['setting'] = unserialize($_G['setting']['mobilewechat']);

if($_GET['access']) {
	dheader('Expires: '.gmdate('D, d M Y H:i:s', TIMESTAMP + 86400).' GMT');
	require_once DISCUZ_ROOT . './source/plugin/wechat/wsq.class.php';
	$url = wsq::$WSQ_DOMAIN.'siteid='.$_G['wechat']['setting']['wsq_siteid'].'&c=index&a=';
	if($_GET['threadqr']) {
		$tid = dintval($_GET['threadqr']);
		include_once template('wechat:wechat_threadqr');
	} elseif($_GET['tid']) {
		$qrsize = !empty($_GET['qrsize']) ? $_GET['qrsize'] : 4;
		if(empty($_GET['pid'])) {
			$tid = dintval($_GET['tid']);
			$dtid = sprintf("%09d", $tid);
			$dir1 = substr($dtid, 0, 3);
			$dir2 = substr($dtid, 3, 2);
			$dir3 = substr($dtid, 5, 2);
			$dir = $dir.$dir1.'/'.$dir2.'/'.$dir3.'/';
			$file = $dir.'/qr_t'.$tid.'.jpg';
			if(!file_exists($file) || !filesize($file)) {
				if(!C::t('forum_thread')->fetch($tid)) {
					exit;
				}
				dmkdir($dir);
				require_once DISCUZ_ROOT.'source/plugin/mobile/qrcode.class.php';
				QRcode::png($url.'viewthread&tid='.$_GET['tid'].'&source=pcscan', $file, QR_ECLEVEL_Q, $qrsize);
			}
			dheader('Content-Disposition: inline; filename=qrcode_t'.$tid.'.jpg');
			dheader('Content-Type: image/pjpeg');
			@readfile($file);
		} else {
			require_once DISCUZ_ROOT.'source/plugin/mobile/qrcode.class.php';
			QRcode::png($url.'showactivity&tid='.$_GET['tid'].'&viewpid='.$_GET['pid'].'&source=pcscan', false, QR_ECLEVEL_Q, $qrsize);
		}
	} elseif($_GET['fid']) {
		$qrsize = !empty($_GET['qrsize']) ? $_GET['qrsize'] : 2;
		$fid = dintval($_GET['fid']);
		$file = $dir.'qr_'.$fid.'.jpg';
		if(!file_exists($file) || !filesize($file)) {
			if(!C::t('forum_forum')->fetch($fid)) {
				exit;
			}
			dmkdir($dir);
			require_once DISCUZ_ROOT.'source/plugin/mobile/qrcode.class.php';
			QRcode::png($url.'index&fid='.$_GET['fid'].'&source=pcscan', $file, QR_ECLEVEL_Q, $qrsize);
		}
		dheader('Content-Disposition: inline; filename=qrcode_'.$fid.'.jpg');
		dheader('Content-Type: image/pjpeg');
		@readfile($file);
	} else {
		$qrsize = !empty($_GET['qrsize']) ? $_GET['qrsize'] : 2;
		$file = $dir.'qr_index.jpg';
		if(!file_exists($file) || !filesize($file)) {
			dmkdir($dir);
			require_once DISCUZ_ROOT.'source/plugin/mobile/qrcode.class.php';
			QRcode::png($url.'index&source=pcscan', $file, QR_ECLEVEL_Q, $qrsize);
		}
		dheader('Content-Disposition: inline; filename=qrcode_index.jpg');
		dheader('Content-Type: image/pjpeg');
		@readfile($file);
	}
	exit;
}

require_once DISCUZ_ROOT . './source/plugin/wechat/wechat.lib.class.php';
$wechat_client = new WeChatClient($_G['wechat']['setting']['wechat_appId'], $_G['wechat']['setting']['wechat_appsecret']);
list($ticket, $code) = explode("\t", authcode($_G['cookie']['wechat_ticket'], 'DECODE'));

if($ticket) {
	$file = $dir.md5($ticket).'_'.$code.'.jpg';
	if(!file_exists($file) || !filesize($file)) {
		dmkdir($dir);
		$qrcode = dfsockopen($wechat_client->getQrcodeImgUrlByTicket($ticket));
		$fp = @fopen($file, 'wb');
		@fwrite($fp, $qrcode);
		@fclose($fp);
	}
	dheader('Content-Disposition: inline; filename=qrcode.jpg');
	dheader('Content-Type: image/pjpeg');
	@readfile($file);
}