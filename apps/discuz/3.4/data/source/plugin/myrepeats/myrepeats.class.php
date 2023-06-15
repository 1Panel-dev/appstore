<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: myrepeats.class.php 29558 2012-04-18 10:17:22Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_myrepeats {

	var $value = array();

	function plugin_myrepeats() {
		global $_G;
		if(!$_G['uid']) {
			return;
		}

		$myrepeatsusergroups = (array)dunserialize($_G['cache']['plugin']['myrepeats']['usergroups']);
		if(in_array('', $myrepeatsusergroups)) {
			$myrepeatsusergroups = array();
		}
		$userlist = array();
		if(!in_array($_G['groupid'], $myrepeatsusergroups)) {
			if(!isset($_G['cookie']['myrepeat_rr'])) {
				$users = count(C::t('#myrepeats#myrepeats')->fetch_all_by_username($_G['username']));
				dsetcookie('myrepeat_rr', 'R'.$users, 86400);
			} else {
				$users = substr($_G['cookie']['myrepeat_rr'], 1);
			}
			if(!$users) {
				return '';
			}
		}

		$this->value['global_usernav_extra1'] = '<script>'.
			'function showmyrepeats() {if(!$(\'myrepeats_menu\')) {'.
			'menu=document.createElement(\'div\');menu.id=\'myrepeats_menu\';menu.style.display=\'none\';menu.className=\'p_pop\';'.
			'$(\'append_parent\').appendChild(menu);'.
			'ajaxget(\'plugin.php?id=myrepeats:switch&list=yes\',\'myrepeats_menu\',\'ajaxwaitid\');}'.
			'showMenu({\'ctrlid\':\'myrepeats\',\'duration\':2});}'.
			'</script>'.
			'<span class="pipe">|</span><a id="myrepeats" href="home.php?mod=spacecp&ac=plugin&id=myrepeats:memcp" class="showmenu cur1" onmouseover="delayShow(this, showmyrepeats)">'.lang('plugin/myrepeats', 'switch').'</a>'."\n";
	}

	function global_usernav_extra1() {
		return $this->value['global_usernav_extra1'];
	}

}

?>