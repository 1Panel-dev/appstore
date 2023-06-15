<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_blockxml.php 25246 2011-11-02 03:34:53Z zhangguosheng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();
$operation = in_array($operation, array('add', 'edit', 'update', 'delete')) ? $operation : 'list';
$signtypearr = array(array('',cplang('blockxml_signtype_no')), array('MD5',cplang('blockxml_signtype_md5')));
shownav('portal', 'blockxml');

if($operation == 'add') {

	if(submitcheck('addsubmit')) {
		require_once libfile('function/importdata');
		import_block($_GET['xmlurl'], $_GET['clientid'], $_GET['key'], $_GET['signtype'], $_GET['ignoreversion']);
		require_once libfile('function/block');
		blockclass_cache();
		cpmsg('blockxml_xmlurl_add_succeed', 'action=blockxml', 'succeed');
	} else {
		showsubmenu('blockxml',  array(
			array('list', 'blockxml', 0),
			array('add', 'blockxml&operation=add', 1)
		));

		showtips('blockxml_tips');
		showformheader('blockxml&operation=add');
		showtableheader('blockxml_add');
		showsetting('blockxml_xmlurl', 'xmlurl', '', 'text');
		showsetting('blockxml_clientid', 'clientid', $blockxml['clientid'], 'text');
		showsetting('blockxml_signtype', array('signtype', $signtypearr), $blockxml['signtype'], 'select');
		showsetting('blockxml_xmlkey', 'key', $blockxml['key'], 'text');
		echo '<tr><td colspan="2"><input class="checkbox" type="checkbox" name="ignoreversion" id="ignoreversion" value="1" /><label for="ignoreversion"> '.cplang('blockxml_import_ignore_version').'</label></td></tr>';
		showsubmit('addsubmit');
		showtablefooter();
		showformfooter();
	}

} elseif($operation == 'edit' && !empty($_GET['id'])) {

	$id = intval($_GET['id']);
	if(!($blockxml = C::t('common_block_xml')->fetch($id))) {
		cpmsg('blockxml_xmlurl_notfound', '', 'error');
	}
	if(submitcheck('editsubmit')) {
		require_once libfile('function/importdata');
		import_block($_GET['xmlurl'], $_GET['clientid'], $_GET['key'], $_GET['signtype'], 1, $id);

		require_once libfile('function/block');
		blockclass_cache();
		cpmsg('blockxml_xmlurl_update_succeed', 'action=blockxml', 'succeed');
	} else {
		showsubmenu('blockxml',  array(
			array('list', 'blockxml', 0),
			array('add', 'blockxml&operation=add', 1)
		));

		showformheader('blockxml&operation=edit&id='.$id);
		showtableheader(cplang('blockxml_edit').' - '.$blockxml['name']);
		showsetting('blockxml_xmlurl', 'xmlurl', $blockxml['url'], 'text');
		showsetting('blockxml_clientid', 'clientid', $blockxml['clientid'], 'text');
		showsetting('blockxml_signtype', array('signtype', $signtypearr), $blockxml['signtype'], 'select');
		showsetting('blockxml_xmlkey', 'key', $blockxml['key'], 'text');
		showtablerow('', '', '<input class="checkbox" type="checkbox" name="ignoreversion" id="ignoreversion" value="1" /><label for="ignoreversion"> '.cplang('blockxml_import_ignore_version').'</label>');
		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();
	}

} elseif($operation == 'update' && !empty($_GET['id'])) {

	$id = intval($_GET['id']);
	if(!($blockxml = C::t('common_block_xml')->fetch($id))) {
		cpmsg('blockxml_xmlurl_notfound', '', 'error');
	}
	require_once libfile('function/importdata');
	import_block($blockxml['url'], $blockxml['clientid'], $blockxml['key'], $blockxml['signtype'], 1, $id);

	require_once libfile('function/block');
	blockclass_cache();

	cpmsg('blockxml_xmlurl_update_succeed', 'action=blockxml', 'succeed');

} elseif($operation == 'delete' && !empty($_GET['id'])) {

	$id = intval($_GET['id']);
	if(!empty($_GET['confirm'])) {
		C::t('common_block_xml')->delete($id);

		require_once libfile('function/block');
		blockclass_cache();
		cpmsg('blockxml_xmlurl_delete_succeed', 'action=blockxml', 'succeed');
	} else {
		cpmsg('blockxml_xmlurl_delete_confirm', 'action=blockxml&operation=delete&id='.$id.'&confirm=yes', 'form');
	}

} else {

	showsubmenu('blockxml',  array(
		array('list', 'blockxml', 1),
		array('add', 'blockxml&operation=add', 0)
	));

	showtableheader('blockxml_list');
	showsubtitle(array('blockxml_name', 'blockxml_xmlurl', 'operation'));
	foreach(C::t('common_block_xml')->range() as $row) {
		showtablerow('', array('class=""', 'class=""', 'class="td28"'), array(
			$row['name'],
			$row['url'],
			"<a href=\"".ADMINSCRIPT."?action=blockxml&operation=update&id=$row[id]\">".cplang('blockxml_update')."</a>&nbsp;&nbsp;".
			"<a href=\"".ADMINSCRIPT."?action=blockxml&operation=edit&id=$row[id]\">".cplang('edit')."</a>&nbsp;&nbsp;".
			"<a href=\"".ADMINSCRIPT."?action=blockxml&operation=delete&id=$row[id]\">".cplang('delete')."</a>&nbsp;&nbsp;"
		));
	}
	showtablefooter();
	showformfooter();

}

?>