<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_friendlink.php 24531 2011-09-23 05:45:11Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('commonblock_html', 'class/block/html');

class block_friendlink extends commonblock_html {

	function block_friendlink() {}

	function name() {
		return lang('blockclass', 'blockclass_html_script_friendlink');
	}

	function getsetting() {
		global $_G;
		$settings = array(
			'content' => array(
				'title' => 'friendlink_content',
				'type' => 'mradio',
				'value' => array(
					array('both', 'friendlink_content_both'),
					array('logo', 'friendlink_content_logo'),
					array('text', 'friendlink_content_text')
				),
				'default' => 'both'
			),
			'type' => array(
				'title' => 'friendlink_type',
				'type' => 'mcheckbox',
				'value' => array(
					array('1', 'friendlink_type_group1'),
					array('2', 'friendlink_type_group2'),
					array('3', 'friendlink_type_group3'),
					array('4', 'friendlink_type_group4'),
				),
				'default' => array('1','2','3','4')
			)
		);
		return $settings;
	}

	function getdata($style, $parameter) {
		$type = !empty($parameter['type']) && is_array($parameter) ? $parameter['type'] : array();
		$b = '0000';
		for($i=1;$i<=4;$i++) {
			if(in_array($i, $type)) {
				$b[$i-1] = '1';
			}
		}
		$type = intval($b, '2');
		$query = C::t('common_friendlink')->fetch_all_by_displayorder($type);
		$group1 = $group2 = $group3 = array();
		foreach ($query as $value) {
			if($parameter['content']=='logo') {
				$group2[] = $value;
			} elseif($parameter['content']=='text') {
				$group3[] = $value;
			} else {
				if($value['description']) {
					$group1[] = $value;
				} elseif($value['logo']) {
					$group2[] = $value;
				} else {
					$group3[] = $value;
				}
			}
		}
		$return = '<div class="bn lk">';
		if($group1) {
			$return .= '<ul class="m cl">';
			foreach($group1 as $value) {
				$return .= '<li class="cl">'
					. '<div class="forumlogo"><a target="_blank" href="'.$value['url'].'"><img border="0" alt="'.$value['name'].'" src="'.$value['logo'].'"></a></div>'
					. '<div class="forumcontent"><h5><a target="_blank" href="'.$value['url'].'">'.$value['name'].'</a></h5><p>'.$value['description'].'</p></div>'
					. '</li>';
			}
			$return .= '</ul>';
		}
		if($group2) {
			$return .= '<div class="cl mbm">';
			foreach($group2 as $value) {
				$return .= '<a target="_blank" href="'.$value['url'].'"><img border="0" alt="'.$value['name'].'" src="'.$value['logo'].'"></a>';
			}
			$return .= '</div>';
		}
		if($group3) {
			$return .= '<ul class="x cl">';
			foreach($group3 as $value) {
				$return .= '<li><a target="_blank" href="'.$value['url'].'">'.$value['name'].'</a></li>';
			}
			$return .= '</ul>';
		}
		$return .= '</div>';
		return array('html' => $return, 'data' => null);
	}
}

?>