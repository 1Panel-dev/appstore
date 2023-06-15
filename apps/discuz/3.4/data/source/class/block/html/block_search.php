<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: block_search.php 23608 2011-07-27 08:10:07Z cnteacher $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('commonblock_html', 'class/block/html');

class block_search extends commonblock_html {

	function block_search() {}

	function name() {
		return lang('blockclass', 'blockclass_html_script_search');
	}

	function getsetting() {
		global $_G;
		$settings = array();
		return $settings;
	}

	function getdata($style, $parameter) {
		global $_G;
		$lang = lang('template');
		$slist = array();
		$checked = ' class="curtype"';
		if($_G['setting']['search']) {
			if($_G['setting']['search']['portal']['status']) {
				$slist['portal'] = '<li><a href="javascript:;" rel="article"'.$checked.'>'.$lang['article'].'</a></li>';
				$checked = '';
			}
			if($_G['setting']['search']['forum']['status']) {
				$slist['forum'] = '<li><a href="javascript:;" rel="forum"'.$checked.'>'.$lang['thread'].'</a></li>';
				$checked = '';
			}
			if ($_G['setting']['search']['blog']['status']) {
				$slist['blog'] = '<li><a href="javascript:;" rel="blog"'.$checked.'>'.$lang['blog'].'</a></li>';
				$checked = '';
			}
			if ($_G['setting']['search']['album']['status']) {
				$slist['album'] = '<li><a href="javascript:;" rel="album"'.$checked.'>'.$lang['album'].'</a></li>';
				$checked = '';
			}
			if ($_G['setting']['groupstatus'] && $_G['setting']['search']['group']['status']) {
				$slist['group'] = '<li><a href="javascript:;" rel="group"'.$checked.'>'.$_G['setting']['navs'][3]['navname'].'</a></li>';
				$checked = '';
			}
			$slist['user'] = '<li><a href="javascript:;" rel="user"'.$checked.'>'.$lang['users'].'</a></li>';
		}
		if($slist) {
			$slist = implode('', $slist);
			$hotsearch = '';
			if ($_G['setting']['srchhotkeywords']) {
				$hotsearch = '<strong class="xw1 xi1">'.$lang['hot_search'].': </strong>';
				foreach($_G['setting']['srchhotkeywords'] as $val) {
					$val = trim($val);
					if($val) {
						$hotsearch .= '<a href="search.php?mod=forum&srchtxt='.rawurlencode($val).'&formhash={FORMHASH}&searchsubmit=true" target="_blank" class="xi2">'.$val.'</a>';
					}
				}
			}
			$html = <<<EOT
				<div id="scbar" class="cl" style="border-top: 1px solid #CCC;">
					<form id="scbar_form" class="z" method="post" autocomplete="off" onsubmit="searchFocus($('srchtxt'))" action="search.php?searchsubmit=yes" target="_blank">
						<input type="hidden" name="mod" id="scbar_mod" value="search" />
						<input type="hidden" name="formhash" value="{FORMHASH}" />
						<input type="hidden" name="srchtype" value="title" />
						<table cellspacing="0" cellpadding="0">
							<tr>
								<td class="scbar_icon_td"></td>
								<td class="scbar_txt_td"><input type="text" name="srchtxt" id="scbar_txt" class="xg1" value="{$lang['enter_content']}" autocomplete="off" onfocus="if(this.value=='{$lang['enter_content']}'){this.value='';this.className=''}" onblur="if(this.value==''){this.value='{$lang['enter_content']}';this.className='xg1'}" /></td>
								<td class="scbar_type_td"><a href="javascript:;" id="scbar_type" class="showmenu xg1" onclick="showMenu({'ctrlid':this.id,'pos':'34'})">{$lang['search']}</a></td>
								<td class="scbar_btn_td"><button type="submit" id="scbar_btn" name="searchsubmit" class="pn pnc" value="true"><strong class="xi2">{$lang['search']}</strong></button></td>
								<td class="scbar_hot_td">
									<div id="scbar_hot">
										$hotsearch
									</div>
								</td>
							</tr>
						</table>
					</form>
				</div>
				<ul id="scbar_type_menu" class="p_pop" style="display: none;">$slist</ul>
				<script type="text/javascript">initSearchmenu('scbar');</script>
EOT;
		}
		return array('html' => $html, 'data' => null);
	}
}

?>