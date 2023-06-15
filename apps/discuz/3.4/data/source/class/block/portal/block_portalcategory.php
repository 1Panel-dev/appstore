<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_portalcategory.php 29236 2012-03-30 05:34:47Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class block_portalcategory extends discuz_block {
	var $setting = array();
	function block_portalcategory() {
		$this->setting = array(
			'thecatid' => array(
				'title' => 'portalcategory_thecatid',
				'type' => 'text',
			),
			'catid' => array(
				'title' => 'portalcategory_catid',
				'type' => 'mselect',
				'value' => array(
				),
			),
			'orderby' => array(
				'title' => 'portalcategory_orderby',
				'type' => 'mradio',
				'value' => array(
					array('displayorder', 'portalcategory_orderby_displayorder'),
					array('articles', 'portalcategory_orderby_articles')
				),
				'default' => 'displayorder'
			)
		);
	}

	function name() {
		return lang('blockclass', 'blockclass_category_script_portalcategory');
	}

	function blockclass() {
		return array('category', lang('blockclass', 'blockclass_portal_category'));
	}

	function fields() {
		return array(
				'id' => array('name' => lang('blockclass', 'blockclass_field_id'), 'formtype' => 'text', 'datatype' => 'int'),
				'url' => array('name' => lang('blockclass', 'blockclass_category_field_url'), 'formtype' => 'text', 'datatype' => 'string'),
				'title' => array('name' => lang('blockclass', 'blockclass_category_field_title'), 'formtype' => 'title', 'datatype' => 'title'),
				'articles' => array('name' => lang('blockclass', 'blockclass_category_field_articles'), 'formtype' => 'text', 'datatype' => 'int'),
			);
	}

	function fieldsconvert() {
		return array(
				'forum_forum' => array(
					'name' => lang('blockclass', 'blockclass_forum_forum'),
					'script' => 'forum',
					'searchkeys' => array('articles'),
					'replacekeys' => array('threads'),
				),
				'group_group' => array(
					'name' => lang('blockclass', 'blockclass_group_group'),
					'script' => 'group',
					'searchkeys' => array('articles'),
					'replacekeys' => array('threads'),
				),
			);
	}

	function getsetting() {
		global $_G;
		$settings = $this->setting;

		if($settings['catid']) {
			$settings['catid']['value'][] = array(0, lang('portalcp', 'block_first_category'));
			loadcache('portalcategory');
			foreach($_G['cache']['portalcategory'] as $value) {
				if($value['level'] == 0) {
					$settings['catid']['value'][] = array($value['catid'], $value['catname']);
					if($value['children']) {
						foreach($value['children'] as $catid2) {
							$value2 = $_G['cache']['portalcategory'][$catid2];
							$settings['catid']['value'][] = array($value2['catid'], '-- '.$value2['catname']);
							if($value2['children']) {
								foreach($value2['children'] as $catid3) {
									$value3 = $_G['cache']['portalcategory'][$catid3];
									$settings['catid']['value'][] = array($value3['catid'], '---- '.$value3['catname']);
								}
							}
						}
					}
				}
			}
		}
		return $settings;
	}

	function getdata($style, $parameter) {
		global $_G;

		$parameter = $this->cookparameter($parameter);
		loadcache('portalcategory');
		$thecatid = !empty($parameter['thecatid']) ? explode(',',$parameter['thecatid']) : array();
		if(!empty($parameter['catid'])) {
			$catid = $parameter['catid'];
		} else {
			$catid = array_keys($_G['cache']['portalcategory']);
			$catid[] = '0';
		}
		$orderby = $parameter['orderby'] == 'articles' ? ' ORDER BY articles DESC' : ' ORDER BY displayorder';

		$wheres = array();
		if($thecatid) {
			$wheres[] = "catid IN (".dimplode($thecatid).")";
		}
		if($catid) {
			$wheres[] = "upid IN (".dimplode($catid).")";
		}
		$wheresql = $wheres ? implode(' AND ', $wheres) : '1';

		$list = array();
		$query = DB::query('SELECT * FROM '.DB::table('portal_category')." WHERE $wheresql $orderby");
		while($data = DB::fetch($query)) {
			$list[] = array(
				'id' => $data['catid'],
				'idtype' => 'catid',
				'title' => dhtmlspecialchars($data['catname']),
				'url' => $_G['cache']['portalcategory'][$data['catid']]['caturl'],
				'pic' => '',
				'picflag' => '0',
				'summary' => '',
				'fields' => array(
					'dateline'=>$data['dateline'],
					'articles' => $data['articles']
				)
			);
		}
		return array('html' => '', 'data' => $list);
	}
}

?>