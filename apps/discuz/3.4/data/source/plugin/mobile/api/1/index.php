<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: index.php 34314 2014-02-20 01:04:24Z nemohou $
 */

define('IN_OPEN', 1);
define('SUB_DIR', 'api/open/');
chdir('../../');

require_once 'source/class/helper/helper_open.php';
class open_api_base extends helper_open_base {}

$_GET['mobile'] = 'no';

if(empty($_GET['module']) || !preg_match('/^[\w\.]+$/', $_GET['module'])) {
	helper_open::result(array('error' => 'param_error'));
}

$apifile = 'api/open/'.$_GET['module'].'.php';

if(file_exists($apifile)) {
	require_once $apifile;
} else {
	helper_open::result(array('error' => 'module_not_exists'));
}

?>