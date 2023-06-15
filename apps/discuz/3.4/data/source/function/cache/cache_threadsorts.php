<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_threadsorts.php 26205 2011-12-05 10:09:32Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_threadsorts() {
	$sortlist = $templatedata = $stemplatedata = $ptemplatedata = $btemplatedata = $template = array();
	$query = C::t('forum_threadtype')->fetch_all_for_cache();
	foreach($query as $data) {
		$data['rules'] = dunserialize($data['rules']);
		$sortid = $data['sortid'];
		$optionid = $data['optionid'];
		$sortlist[$sortid][$optionid] = array(
		'title' => dhtmlspecialchars($data['title']),
		'type' => dhtmlspecialchars($data['type']),
		'unit' => dhtmlspecialchars($data['unit']),
		'identifier' => dhtmlspecialchars($data['identifier']),
		'description' => dhtmlspecialchars($data['description']),
		'permprompt' => $data['permprompt'],
		'required' => intval($data['required']),
		'unchangeable' => intval($data['unchangeable']),
		'search' => intval($data['search']),
		'subjectshow' => intval($data['subjectshow']),
		'expiration' => intval($data['expiration']),
		'protect' => dunserialize($data['protect']),
		);

		if(in_array($data['type'], array('select', 'checkbox', 'radio'))) {
			if($data['rules']['choices']) {
				$choices = array();
				foreach(explode("\n", $data['rules']['choices']) as $item) {
					list($index, $choice) = explode('=', $item);
					$choices[trim($index)] = trim($choice);
				}
				$sortlist[$sortid][$optionid]['choices'] = $choices;
			} else {
				$sortlist[$sortid][$optionid]['choices'] = array();
			}
			if($data['type'] == 'select') {
				$sortlist[$sortid][$optionid]['inputsize'] = $data['rules']['inputsize'] ? intval($data['rules']['inputsize']) : 108;
			}
		} elseif(in_array($data['type'], array('text', 'textarea', 'calendar'))) {
			$sortlist[$sortid][$optionid]['maxlength'] = intval($data['rules']['maxlength']);
			if($data['type'] == 'textarea') {
				$sortlist[$sortid][$optionid]['rowsize'] = $data['rules']['rowsize'] ? intval($data['rules']['rowsize']) : 5;
				$sortlist[$sortid][$optionid]['colsize'] = $data['rules']['colsize'] ? intval($data['rules']['colsize']) : 50;
			} else {
				$sortlist[$sortid][$optionid]['inputsize'] = $data['rules']['inputsize'] ? intval($data['rules']['inputsize']) : '';
			}
			if(in_array($data['type'], array('text', 'textarea'))) {
				$sortlist[$sortid][$optionid]['defaultvalue'] = $data['rules']['defaultvalue'];
			}
			if($data['type'] == 'text') {
				$sortlist[$sortid][$optionid]['profile'] = $data['rules']['profile'];
			}
		} elseif($data['type'] == 'image') {
			$sortlist[$sortid][$optionid]['maxwidth'] = intval($data['rules']['maxwidth']);
			$sortlist[$sortid][$optionid]['maxheight'] = intval($data['rules']['maxheight']);
			$sortlist[$sortid][$optionid]['inputsize'] = $data['rules']['inputsize'] ? intval($data['rules']['inputsize']) : '';
		} elseif(in_array($data['type'], array('number', 'range'))) {
			$sortlist[$sortid][$optionid]['inputsize'] = $data['rules']['inputsize'] ? intval($data['rules']['inputsize']) : '';
			$sortlist[$sortid][$optionid]['maxnum'] = intval($data['rules']['maxnum']);
			$sortlist[$sortid][$optionid]['minnum'] = intval($data['rules']['minnum']);
			if($data['rules']['searchtxt']) {
				$sortlist[$sortid][$optionid]['searchtxt'] = explode(',', $data['rules']['searchtxt']);
			}
			if($data['type'] == 'number') {
				$sortlist[$sortid][$optionid]['defaultvalue'] = $data['rules']['defaultvalue'];
			}
		}
	}
	$query = C::t('forum_threadtype')->range();
	foreach($query as $data) {
		$templatedata[$data['typeid']] = addcslashes($data['template'], '",\\');
		$stemplatedata[$data['typeid']] = addcslashes($data['stemplate'], '",\\');
		$ptemplatedata[$data['typeid']] = addcslashes($data['ptemplate'], '",\\');
		$btemplatedata[$data['typeid']] = addcslashes($data['btemplate'], '",\\');
	}

	$data['sortoption'] = $data['template'] = array();

	foreach($sortlist as $sortid => $option) {
		$template['viewthread'] =  $templatedata[$sortid];
		$template['subject'] = $stemplatedata[$sortid];
		$template['post'] = $ptemplatedata[$sortid];
		$template['block'] = $btemplatedata[$sortid];

		savecache('threadsort_option_'.$sortid, $option);
		savecache('threadsort_template_'.$sortid, $template);
	}

}

?>