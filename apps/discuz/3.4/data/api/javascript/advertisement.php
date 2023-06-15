<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: advertisement.php 25246 2011-11-02 03:34:53Z zhangguosheng $
 */

header('Expires: '.gmdate('D, d M Y H:i:s', time() + 60).' GMT');

if(!defined('IN_API')) {
	exit('document.write(\'Access Denied\')');
}

loadcore();

$adid = $_GET['adid'];
$data = adshow($adid);

dheader('Content-Type: application/javascript');

echo 'document.write(\''.preg_replace("/\r\n|\n|\r/", '\n', addcslashes($data, "'\\")).'\');';

?>