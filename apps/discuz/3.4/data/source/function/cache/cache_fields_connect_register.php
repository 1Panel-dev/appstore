<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_fields_connect_register.php 24935 2011-10-17 07:41:48Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_fields_connect_register() {
	global $_G;
	$data = array();
	$fields = array();
	if($_G['setting']['connect']['register_gender']) {
		$fields[] = 'gender';
	}
	if($_G['setting']['connect']['register_birthday']) {
		$fields[] = 'birthyear';
		$fields[] = 'birthmonth';
		$fields[] = 'birthday';
	}
	if($fields) {

		foreach(C::t('common_member_profile_setting')->fetch_all($fields) as $field) {
			$choices = array();
			if($field['selective']) {
				foreach(explode("\n", $field['choices']) as $item) {
					list($index, $choice) = explode('=', $item);
					$choices[trim($index)] = trim($choice);
				}
				$field['choices'] = $choices;
			} else {
				unset($field['choices']);
			}
			$field['showinregister'] = 1;
			$field['available'] = 1;
			$data['field_'.$field['fieldid']] = $field;
		}
	}

	savecache('fields_connect_register', $data);
}

?>