<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: checknewpm.php 35183 2017-11-03 16:46:53Z leiyu $
 */

if(!defined('IN_MOBILE_API')) {
    exit('Access Denied');
}

$_GET['mod'] = 'spacecp';
$_GET['ac'] = 'pm';
$_GET['op'] = 'checknewpm';
include_once 'home.php';

class mobile_api {

    function common() {
    }

    function output() {
    }
}

?>