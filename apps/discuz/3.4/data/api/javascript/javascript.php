<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: javascript.php 25246 2011-11-02 03:34:53Z zhangguosheng $
 */

header('Expires: '.gmdate('D, d M Y H:i:s', time() + 60).' GMT');

if(!defined('IN_API')) {
	exit('document.write(\'Access Denied\')');
}

loadcore();

include_once libfile('function/block');

loadcache('blockclass');
$bid = intval($_GET['bid']);
block_get_batch($bid);
$data = block_fetch_content($bid, true);

$search = "/(href|src)\=(\"|')(?![fhtps]+\:)(.*?)\\2/i";
$replace = "\\1=\\2$_G[siteurl]\\3\\2";
$data = preg_replace($search, $replace, $data);

dheader('Content-Type: application/javascript');

echo 'document.write(\''.preg_replace("/\r\n|\n|\r/", '\n', addcslashes($data, "'\\")).'\');';

?>