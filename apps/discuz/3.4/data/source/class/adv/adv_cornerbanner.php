<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: adv_cornerbanner.php 26692 2011-12-20 05:27:38Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class adv_cornerbanner {

	var $version = '1.1';
	var $name = 'cornerbanner_name';
	var $description = 'cornerbanner_desc';
	var $copyright = '<a href="https://www.discuz.vip/" target="_blank">Discuz!</a>';
	var $targets = array('portal', 'home', 'member', 'forum', 'group', 'plugin', 'custom');
	var $imagesizes = array('300x250', '290x200', '250x180');

	function getsetting() {
		global $_G;
		$settings = array(
			'fids' => array(
				'title' => 'cornerbanner_fids',
				'type' => 'mselect',
				'value' => array(),
			),
			'groups' => array(
				'title' => 'cornerbanner_groups',
				'type' => 'mselect',
				'value' => array(),
			),
			'category' => array(
				'title' => 'cornerbanner_category',
				'type' => 'mselect',
				'value' => array(),
			),
		        'disableclose' => array(
			    'title' => 'cornerbanner_disableclose',
			    'type' => 'mradio',
			    'value' => array(
			            array(0, 'cornerbanner_show'),
				    array(1, 'cornerbanner_hidden'),
			    ),
			    'default' => 0,
			),
			'animator' => array(
				'title' => 'cornerbanner_animator',
				'type' => 'radio',
				'default' => 0,
			),
		);
		loadcache(array('forums', 'grouptype'));
		$settings['fids']['value'][] = $settings['groups']['value'][] = array(0, '&nbsp;');
		$settings['fids']['value'][] = $settings['groups']['value'][] = array(-1, 'cornerbanner_index');
		if(empty($_G['cache']['forums'])) $_G['cache']['forums'] = array();
		foreach($_G['cache']['forums'] as $fid => $forum) {
			$settings['fids']['value'][] = array($fid, ($forum['type'] == 'forum' ? str_repeat('&nbsp;', 4) : ($forum['type'] == 'sub' ? str_repeat('&nbsp;', 8) : '')).$forum['name']);
		}
		foreach($_G['cache']['grouptype']['first'] as $gid => $group) {
			$settings['groups']['value'][] = array($gid, str_repeat('&nbsp;', 4).$group['name']);
			if($group['secondlist']) {
				foreach($group['secondlist'] as $sgid) {
					$settings['groups']['value'][] = array($sgid, str_repeat('&nbsp;', 8).$_G['cache']['grouptype']['second'][$sgid]['name']);
				}
			}
		}
		loadcache('portalcategory');
		$this->categoryvalue[] = array(-1, 'cornerbanner_index');
		$this->getcategory(0);
		$settings['category']['value'] = $this->categoryvalue;
		return $settings;
	}

	function getcategory($upid) {
		global $_G;
		foreach($_G['cache']['portalcategory'] as $category) {
			if($category['upid'] == $upid) {
				$this->categoryvalue[] = array($category['catid'], str_repeat('&nbsp;', $category['level'] * 4).$category['catname']);
				$this->getcategory($category['catid']);
			}
		}
	}

	function setsetting(&$advnew, &$parameters) {
		global $_G;
		if(is_array($advnew['targets'])) {
			$advnew['targets'] = implode("\t", $advnew['targets']);
		}
		if(is_array($parameters['extra']['fids']) && in_array(0, $parameters['extra']['fids'])) {
			$parameters['extra']['fids'] = array();
		}
		if(is_array($parameters['extra']['groups']) && in_array(0, $parameters['extra']['groups'])) {
			$parameters['extra']['groups'] = array();
		}
		if(is_array($parameters['extra']['category']) && in_array(0, $parameters['extra']['category'])) {
			$parameters['extra']['category'] = array();
		}
		$parameters['extra']['animator'] = $parameters['animator'];
	}

	function evalcode($adv) {
		return array(
			'check' => '
			if($_G[\'basescript\'] == \'forum\' && $parameter[\'fids\'] && !(in_array($_G[\'fid\'], $parameter[\'fids\']) || CURMODULE == \'index\' && in_array(-1, $parameter[\'fids\']) || defined(\'IN_ARCHIVER\') && in_array(-2, $parameter[\'fids\']))
			|| $_G[\'basescript\'] == \'group\' && $parameter[\'groups\'] && !(in_array($_G[\'grouptypeid\'], $parameter[\'groups\']) || CURMODULE == \'index\' && in_array(-1, $parameter[\'groups\']))
			|| $_G[\'basescript\'] == \'portal\' && $parameter[\'category\'] && !(!empty($_G[\'catid\']) && in_array($_G[\'catid\'], $parameter[\'category\']) || empty($_G[\'catid\']) && in_array(-1, $parameter[\'category\']))
			) {
				$checked = false;
			}',
			'create' => '
				if(empty($parameter[\'disableclose\'])) {
					$adid = $adids[array_rand($adids)];
					$aniscript = $parameter[\'animator\'] ? \'<script type="text/javascript">_attachEvent(window, \\\'load\\\', function () {var ad_corner_obj = $(\\\'ad_corner_close\\\').parentNode,ad_corner_height = ad_corner_obj.clientHeight,ad_corner_hi=0,ad_corner_si=setInterval(function () { ad_corner_obj.style.visibility=\\\'visible\\\';ad_corner_obj.style.overflow=\\\'hidden\\\';ad_corner_obj.style.height=ad_corner_hi+\\\'px\\\';ad_corner_hi+=10;if(ad_corner_height<ad_corner_hi) {ad_corner_obj.style.overflow=\\\'visible\\\';clearInterval(ad_corner_si);}}, 1);}, document);</script>\' : \'\';
					$adcode = empty($_G[\'cookie\'][\'adclose_\'.$adid]) ? \'<p class="close" id="ad_corner_close" onclick="setcookie(\\\'adclose_\'.$adid.\'\\\', 1, 86400);this.parentNode.style.display=\\\'none\\\'"><a href="javascript:;"><img src="\'.STATICURL.\'image/common/ad_close.gif" /></a></p>\'.$codes[$adid].$aniscript : \'\';
					$extra = \'style="\'.($parameters[$adid][\'height\'] ? \'line-height:\'.$parameters[$adid][\'height\'].\'px;height:\'.$parameters[$adid][\'height\'].\'px\' : \'\').($parameter[\'animator\'] ? \';visibility:hidden\': \'\').\'"\';
				} else {
					$adcode = $codes[$adids[array_rand($adids)]];
				}
			',
		);
	}

}

?>