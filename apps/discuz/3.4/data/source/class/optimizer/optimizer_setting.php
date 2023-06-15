<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: optimizer_setting.php 33488 2013-06-24 01:48:20Z jeffjzhang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class optimizer_setting {

	private $setting;

	public function __construct() {
		include_once DISCUZ_ROOT.'./source/language/lang_optimizer.php';
		$this->setting = array(
			'cacheindexlife' => array(
				'initvalue' => '0',
				'optimizedvalue' => '900',
				'title' => $lang['optimizer_setting_cache_index'],
				'description' => $lang['optimizer_setting_cache_index_desc'],
				'optimizerdesc' => $lang['optimizer_setting_cache_optimize_desc'],
			),
			'cachethreadlife' => array(
				'initvalue' => '0',
				'optimizedvalue' => '900',
				'title' => $lang['optimizer_setting_cache_post'],
				'description' => $lang['optimizer_setting_cache_post_desc'],
				'optimizerdesc'=> $lang['optimizer_setting_cache_post_optimize_desc'],
			),
			'optimizeviews' => array(
				'initvalue' => '0',
				'optimizedvalue' => '1',
				'title' => $lang['optimizer_setting_optimizeviews'],
				'description' => $lang['optimizer_setting_optimizeviews_desc'],
				'optimizerdesc' => $lang['optimizer_setting_optimizeviews_optimize_desc'],
			),
			'delayviewcount' => array(
				'initvalue' => '0',
				'optimizedvalue' => '1',
				'title' => $lang['optimizer_setting_delayviewcount'],
				'description' => $lang['optimizer_setting_delayviewcount_desc'],
				'optimizerdesc' => $lang['optimizer_setting_delayviewcount_optimize_desc'],
			),
			'preventrefresh' => array(
				'initvalue' => '0',
				'optimizedvalue' => '1',
				'title' => $lang['optimizer_setting_preventrefresh'],
				'description' => $lang['optimizer_setting_preventrefresh_desc'],
				'optimizerdesc' => $lang['optimizer_setting_preventrefresh_optimize_desc'],
			),
			'nocacheheaders' => array(
				'initvalue' => '1',
				'optimizedvalue' => '0',
				'title' => $lang['optimizer_setting_nocacheheaders'],
				'description' => $lang['optimizer_setting_nocacheheaders_desc'],
				'optimizerdesc' => $lang['optimizer_setting_nocacheheaders_optimize_desc'],
			),
			'jspath' => array(
				'initvalue' => 'static/js/',
				'optimizedvalue' => 'data/cache/',
				'title' => $lang['optimizer_setting_jspath'],
				'description' => $lang['optimizer_setting_jspath_desc'],
				'optimizerdesc' => $lang['optimizer_setting_jspath_optimize_desc'],
			),
			'lazyload' => array(
				'initvalue' => '0',
				'optimizedvalue' => '1',
				'title' => $lang['optimizer_setting_lazyload'],
				'description' => $lang['optimizer_setting_lazyload_desc'],
				'optimizerdesc' => $lang['optimizer_setting_lazyload_optimize_desc'],
			),
			'sessionclose' => array(
				'initvalue' => '0',
				'optimizedvalue' => '1',
				'title' => $lang['optimizer_setting_sessionclose'],
				'description' => $lang['optimizer_setting_sessionclose_desc'],
				'optimizerdesc' => $lang['optimizer_setting_sessionclose_optimize_desc'],
			),
			'rewriteguest' => array(
				'initvalue' => 0,
				'optimizedvalue' => '1',
				'title' => $lang['optimizer_setting_rewriteguest'],
				'description' => $lang['optimizer_setting_rewriteguest_desc'],
				'optimizerdesc' => $lang['optimizer_setting_rewriteguest_optimize_desc'],
			),
		);
	}

	public function check() {
		$count = 0;
		$options = $this->get_option();
		foreach($options as $option) {
			if($option[4] == '1') {
				$count++;
			}
		}
		if($count) {
			$return = array('status' => 1, 'type' => 'view', 'lang' => lang('optimizer', 'optimizer_setting_need_optimizer', array('count' => $count)));
		} else {
			$return = array('status' => 0, 'type' => 'view', 'lang' => lang('optimizer', 'optimizer_setting_no_need'));
		}
		return $return;
	}

	public function optimizer() {
		$adminfile = defined(ADMINSCRIPT) ? ADMINSCRIPT : 'admin.php';
		dheader('Location: '.$_G['siteurl'].$adminfile.'?action=optimizer&operation=setting_optimizer&type=optimizer_setting&anchor=performance');
	}

	public function option_optimizer($options) {
		$update = array();
		foreach($options as $option) {
			if(isset($this->setting[$option])) {
				$update[$option] = $this->setting[$option]['optimizedvalue'];
			}
		}
		if($update) {
			C::t('common_setting')->update_batch($update);
			updatecache('setting');
		}
		return true;
	}

	public function get_option() {
		$return = array();
		$settings = C::t('common_setting')->fetch_all(array_keys($this->setting));
		foreach($this->setting as $k => $setting) {
			if($settings[$k] == $setting['initvalue']) {
				$return[] = array($k, $setting['title'], $setting['description'], $setting['optimizerdesc'], '1');
			} else {
				$return[] = array($k, $setting['title'], $setting['description'], $setting['optimizerdesc'], '0');
			}
		}
		return $return;
	}

}

?>