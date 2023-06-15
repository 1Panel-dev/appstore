<?php

/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: forum.func.php 14122 2008-08-20 06:06:33Z cnteacher $
*/

if(!defined('IN_COMSENZ')) {
	exit('Access Denied');
}

define('SOFT_NAME', 'UCenter');

if(defined('UC_SERVER_VERSION')) {
	define('SOFT_VERSION', UC_SERVER_VERSION);
	define('SOFT_RELEASE', UC_SERVER_RELEASE);
} else {
	define('SOFT_VERSION', '0.0.0');
	define('SOFT_RELEASE', '19700101');
}

define('INSTALL_LANG', 'SC_UTF8');

define('CONFIG', ROOT_PATH.'./data/config.inc.php');

$sqlfile = ROOT_PATH.'./install/uc.sql';

$lockfile = ROOT_PATH.'./data/install.lock';

define('CHARSET', 'utf-8');
define('DBCHARSET', 'utf8');

define('ORIG_TABLEPRE', 'uc_');

define('METHOD_UNDEFINED', 255);
define('ENV_CHECK_RIGHT', 0);
define('ERROR_CONFIG_VARS', 1);
define('SHORT_OPEN_TAG_INVALID', 2);
define('INSTALL_LOCKED', 3);
define('DATABASE_NONEXISTENCE', 4);
define('PHP_VERSION_TOO_LOW', 5);
define('MYSQL_VERSION_TOO_LOW', 6);
define('UC_URL_INVALID', 7);
define('UC_DNS_ERROR', 8);
define('UC_URL_UNREACHABLE', 9);
define('UC_VERSION_INCORRECT', 10);
define('UC_DBCHARSET_INCORRECT', 11);
define('UC_API_ADD_APP_ERROR', 12);
define('UC_ADMIN_INVALID', 13);
define('UC_DATA_INVALID', 14);
define('DBNAME_INVALID', 15);
define('DATABASE_ERRNO_2003', 16);
define('DATABASE_ERRNO_1044', 17);
define('DATABASE_ERRNO_1045', 18);
define('DATABASE_CONNECT_ERROR', 19);
define('TABLEPRE_INVALID', 20);
define('CONFIG_UNWRITEABLE', 21);
define('ADMIN_USERNAME_INVALID', 22);
define('ADMIN_EMAIL_INVALID', 25);
define('ADMIN_EXIST_PASSWORD_ERROR', 26);
define('ADMININFO_INVALID', 27);
define('LOCKFILE_NO_EXISTS', 28);
define('TABLEPRE_EXISTS', 29);
define('ERROR_UNKNOW_TYPE', 30);
define('ENV_CHECK_ERROR', 31);
define('UNDEFINE_FUNC', 32);
define('MISSING_PARAMETER', 33);
define('LOCK_FILE_NOT_TOUCH', 34);

$func_items = array('mysql_connect', 'gethostbyname', 'file_get_contents', 'xml_parser_create');

$env_items = array
(
	'os' => array('c' => 'PHP_OS', 'r' => 'notset', 'b' => 'unix'),
	'php' => array('c' => 'PHP_VERSION', 'r' => '4.0', 'b' => '5.0'),
	'attachmentupload' => array('r' => 'notset', 'b' => '2M'),
	'gdversion' => array('r' => '1.0', 'b' => '2.0'),
	'diskspace' => array('r' => '10M', 'b' => 'notset'),
);

$dirfile_items = array
(
	'config' => array('type' => 'file', 'path' => './data/config.inc.php'),
	'data' => array('type' => 'dir', 'path' => './data'),
	'cache' => array('type' => 'dir', 'path' => './data/cache'),
	'view' => array('type' => 'dir', 'path' => './data/view'),
	'avatar' => array('type' => 'dir', 'path' => './data/avatar'),
	'logs' => array('type' => 'dir', 'path' => './data/logs'),
	'backup' => array('type' => 'dir', 'path' => './data/backup'),
	'tmp' => array('type' => 'dir', 'path' => './data/tmp')
);

$form_db_init_items = array
(
	'dbinfo' => array
	(
		'dbhost' => array('type' => 'text', 'required' => 1, 'reg' => '/^.*$/', 'value' => array('type' => 'string', 'var' => 'localhost')),
		'dbname' => array('type' => 'text', 'required' => 1, 'reg' => '/^.*$/', 'value' => array('type' => 'string', 'var' => 'ucenter')),
		'dbuser' => array('type' => 'text', 'required' => 0, 'reg' => '/^.*$/', 'value' => array('type' => 'string', 'var' => 'root')),
		'dbpw' => array('type' => 'text', 'required' => 0, 'reg' => '/^.*$/', 'value' => array('type' => 'string', 'var' => '')),
		'tablepre' => array('type' => 'text', 'required' => 0, 'reg' => '/^.*$/', 'value' => array('type' => 'string', 'var' => 'uc_')),
	),
	'admininfo' => array
	(
		'ucfounderpw' => array('type' => 'password', 'required' => 1, 'reg' => '/^.*$/'),
		'ucfounderpw2' => array('type' => 'password', 'required' => 1, 'reg' => '/^.*$/'),
	)
);