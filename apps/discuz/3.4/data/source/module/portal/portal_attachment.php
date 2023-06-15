<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portal_attachment.php 25246 2011-11-02 03:34:53Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$operation = $_GET['op'] ? $_GET['op'] : '';

$id = empty($_GET['id']) ? 0 : intval($_GET['id']);
$aid = empty($_GET['aid']) ? '' : intval($_GET['aid']);
$attach = C::t('portal_attachment')->fetch($id);
if(empty($attach)) {
	showmessage('portal_attachment_noexist');
}

if($operation == 'delete') {
	if(!$_G['group']['allowmanagearticle'] && ($_G['uid'] != $attach['uid'] || $aid != $attach['aid'])) {
		showmessage('portal_attachment_nopermission_delete');
	}
	if(!isset($_GET['formhash']) || formhash() != $_GET['formhash']) {
		showmessage('portal_attachment_nopermission_delete');
	}
	if($aid) {
		C::t('portal_article_title')->update($aid, array('pic' => ''));
	}
	C::t('portal_attachment')->delete($id);
	pic_delete($attach['attachment'], 'portal', $attach['thumb'], $attach['remote']);
	showmessage('portal_image_noexist');

} elseif($operation == 'getattach') {

	require_once libfile('function/attachment');
	if($attach['isimage']) {
		require_once libfile('function/home');
		$smallimg = pic_get($attach['attachment'], 'portal', $attach['thumb'], $attach['remote']);
		$bigimg = pic_get($attach['attachment'], 'portal', 0, $attach['remote']);
		$coverstr = addslashes(serialize(array('pic'=>'portal/'.$attach['attachment'], 'thumb'=>$attach['thumb'], 'remote'=>$attach['remote'])));
	}
	$attach['filetype'] = attachtype($attach['filetype']."\t".$attach['filetype']);
	$attach['filesize'] = sizecount($attach['filesize']);
	include template('portal/portal_attachment');

	exit;

} else {
	$filename = $_G['setting']['attachdir'].'/portal/'.$attach['attachment'];
	if(!$attach['remote'] && !is_readable($filename)) {
		showmessage('attachment_nonexistence');
	}

	$readmod = 2;//read local file's function: 1=fread 2=readfile 3=fpassthru 4=fpassthru+multiple
	$range = 0;
	if($readmod == 4 && !empty($_SERVER['HTTP_RANGE'])) {
		list($range) = explode('-',(str_replace('bytes=', '', $_SERVER['HTTP_RANGE'])));
	}

	if($attach['remote'] && !$_G['setting']['ftp']['hideurl'] && $attach['isimage']) {
		dheader('location:'.$_G['setting']['ftp']['attachurl'].'portal/'.$attach['attachment']);
	}

	$filesize = $attach['filesize'];
	$filenameencode = strtolower(CHARSET) == 'utf-8' ? rawurlencode($attach['filename']) : rawurlencode(diconv($attach['filename'], CHARSET, 'UTF-8'));

	$rfc6266blacklist = strexists($_SERVER['HTTP_USER_AGENT'], 'UCBrowser') || strexists($_SERVER['HTTP_USER_AGENT'], 'Quark') || strexists($_SERVER['HTTP_USER_AGENT'], 'SogouM') || strexists($_SERVER['HTTP_USER_AGENT'], 'baidu');

	dheader('Date: '.gmdate('D, d M Y H:i:s', $attach['dateline']).' GMT');
	dheader('Last-Modified: '.gmdate('D, d M Y H:i:s', $attach['dateline']).' GMT');
	dheader('Content-Encoding: none');
	dheader('Content-Disposition: attachment; filename="'.$filenameencode.'"'.(($attach['filename'] == $filenameencode || $rfc6266blacklist) ? '' : '; filename*=utf-8\'\''.$filenameencode));
	dheader('Content-Type: '.$attach['filetype']);
	dheader('Content-Length: '.$filesize);

	if($readmod == 4) {
		dheader('Accept-Ranges: bytes');
		if(!empty($_SERVER['HTTP_RANGE'])) {
			$rangesize = ($filesize - $range) > 0 ?  ($filesize - $range) : 0;
			dheader('Content-Length: '.$rangesize);
			dheader('HTTP/1.1 206 Partial Content');
			dheader('Content-Range: bytes='.$range.'-'.($filesize-1).'/'.($filesize));
		}
	}

	$attach['remote'] ? getremotefile($attach['attachment']) : getlocalfile($filename, $readmod, $range);
}
function getremotefile($file) {
	global $_G;
	@set_time_limit(0);
	if(!@readfile($_G['setting']['ftp']['attachurl'].'portal/'.$file)) {

		$ftp = new discuz_ftp();
		if(!($_G['setting']['ftp']['connid'] = $ftp->connect())) {
			return FALSE;
		}
		$tmpfile = @tempnam($_G['setting']['attachdir'], '');
		if($ftp->ftp_get($_G['setting']['ftp']['connid'], $tmpfile, $file, FTP_BINARY)) {
			@readfile($tmpfile);
			@unlink($tmpfile);
		} else {
			@unlink($tmpfile);
			return FALSE;
		}
	}
	return TRUE;
}

function getlocalfile($filename, $readmod = 2, $range = 0) {
	if($readmod == 1 || $readmod == 3 || $readmod == 4) {
		if($fp = @fopen($filename, 'rb')) {
			@fseek($fp, $range);
			if(function_exists('fpassthru') && ($readmod == 3 || $readmod == 4)) {
				@fpassthru($fp);
			} else {
				echo @fread($fp, filesize($filename));
			}
		}
		@fclose($fp);
	} else {
		@readfile($filename);
	}
	@flush();
	@ob_flush();
}

?>