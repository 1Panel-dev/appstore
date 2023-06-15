<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: forum_collection.php 28448 2012-03-01 03:27:53Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('function/collection');

$tpp = $_G['setting']['topicperpage']; //per page
$maxteamworkers = $_G['setting']['collectionteamworkernum'];

$action = trim($_GET['action']);
$ctid = $_GET['ctid'];
$page = $_GET['page'];
$tid = intval($_GET['tid']);
$op = trim($_GET['op']);
$page = $page ? $page : 1;

if(!is_array($ctid)) {
	$ctid = intval($ctid);
	$_G['collection'] = C::t('forum_collection')->fetch($ctid);
}

$allowaction = array('index', 'view', 'edit', 'follow', 'comment', 'mycollection', 'all');

if(!in_array($action, $allowaction)) {
	$action = 'index';
}

require_once libfile('collection/'.$action, 'include');


?>