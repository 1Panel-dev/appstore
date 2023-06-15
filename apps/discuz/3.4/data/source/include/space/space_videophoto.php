<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: space_videophoto.php 22572 2011-05-12 09:35:18Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if(empty($_G['setting']['verify'][7]['available'])) {
	showmessage('no_open_videophoto');
}

require_once libfile('function/spacecp');
ckvideophoto($space);

$videophoto = getvideophoto($space['videophoto']);

include_once template("home/space_videophoto");

?>