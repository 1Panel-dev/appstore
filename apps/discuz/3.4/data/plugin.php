<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: plugin.php 27335 2012-01-16 10:15:37Z monkey $
 */

define('APPTYPEID', 127);
define('CURSCRIPT', 'plugin');
define('NOT_IN_MOBILE_API', 1);

require './source/class/class_core.php';

$discuz = C::app();

$cachelist = array('plugin', 'diytemplatename');

$discuz->cachelist = $cachelist;
$discuz->init();

if(!empty($_GET['id'])) {
	list($identifier, $module) = explode(':', $_GET['id']);
	$module = $module !== NULL ? $module : $identifier;
} else {
	showmessage('plugin_nonexistence');
}
$mnid = 'plugin_'.$identifier.'_'.$module;
$pluginmodule = isset($_G['setting']['pluginlinks'][$identifier][$module]) ? $_G['setting']['pluginlinks'][$identifier][$module] : (isset($_G['setting']['plugins']['script'][$identifier][$module]) ? $_G['setting']['plugins']['script'][$identifier][$module] : array('adminid' => 0, 'directory' => preg_match("/^[a-z]+[a-z0-9_]*$/i", $identifier) ? $identifier.'/' : ''));

if(!preg_match('/^[\w\_]+$/', $identifier)) {
	showmessage('plugin_nonexistence');
}

if(empty($identifier) || !preg_match("/^[a-z0-9_\-]+$/i", $module) || !in_array($identifier, $_G['setting']['plugins']['available'])) {
	showmessage('plugin_nonexistence');
} elseif($pluginmodule['adminid'] && ($_G['adminid'] < 1 || ($_G['adminid'] > 0 && $pluginmodule['adminid'] < $_G['adminid']))) {
	showmessage('plugin_nopermission');
} elseif(@!file_exists(DISCUZ_ROOT.($modfile = './source/plugin/'.$pluginmodule['directory'].$module.'.inc.php'))) {
	showmessage('plugin_module_nonexistence', '', array('mod' => $modfile));
}

define('CURMODULE', $identifier);
runhooks();

include DISCUZ_ROOT.$modfile;

?>