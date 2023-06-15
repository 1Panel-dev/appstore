<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: viewthread.php 34314 2014-02-20 01:04:24Z nemohou $
 */

if(!defined('IN_MOBILE_API')) {
	exit('Access Denied');
}

define('IS_WEBVIEW', true);

$_GET['mod'] = 'viewthread';
include_once 'forum.php';

class mobile_api {

	function common() {
		global $_G;
		$_G['setting']['avatarmethod'] = 0;
	}

	function output() {
		extract($GLOBALS);
		$_G['forum_thread']['replies'] = $_G['forum_thread']['replies'] >= 0 ? $_G['forum_thread']['replies'] : 0;
		if($_G['page'] > @ceil(($_G['forum_thread']['replies'] + 1) / $_G['ppp'])) {
			$content = '';
		} elseif($postlist) {
			if(!function_exists('mobileoem_template')) {
				include_once DISCUZ_ROOT.'./source/plugin/mobileoem/discuzcode.func.php';
			}
			$variable = array();
			foreach($GLOBALS['aimgs'] as $pid => $aids) {
				foreach($aids as $aid) {
					$_url = parse_url($postlist[$pid]['attachments'][$aid]['url']);
					$variable['imagelist'][$aid] = (!$postlist[$pid]['attachments'][$aid]['remote'] && !$_url['scheme'] ? $_G['siteurl'] : '').$postlist[$pid]['attachments'][$aid]['url'].$postlist[$pid]['attachments'][$aid]['attachment'];
					if(strexists($postlist[$pid]['message'], '[attach]'.$aid.'[/attach]')) {
						$postlist[$pid]['message'] = str_replace('[attach]'.$aid.'[/attach]', mobileoem_parseimg($postlist[$pid]['attachments'][$aid]['width'], 0, $variable['imagelist'][$aid]), $postlist[$pid]['message']);
					} else {
						$postlist[$pid]['message'] .= '<br /><br />'.mobileoem_parseimg($postlist[$pid]['attachments'][$aid]['width'], 0, $variable['imagelist'][$aid]);
					}
				}
			}
			foreach($postlist as $pid => $post) {
				if($post['attachlist']) {
					foreach($post['attachlist'] as $aid) {
						$aidencode = packaids($postlist[$pid]['attachments'][$aid]);
						$_code = parseurl('/forum.php?mod=attachment&aid='.$aidencode, $postlist[$pid]['attachments'][$aid]['filename'], 0);
						if(strexists($postlist[$pid]['message'], '[attach]'.$aid.'[/attach]')) {
							$postlist[$pid]['message'] = str_replace('[attach]'.$aid.'[/attach]', $_code, $postlist[$pid]['message']);
						} else {
							$postlist[$pid]['message'] .= '<br /><br />'.$_code;
						}
					}
				}
				$postlist[$pid]['message'] = preg_replace("/\[attach\]\d+\[\/attach\]/i", '', $postlist[$pid]['message']);
			}
			$get = $_GET;
			unset($get['page'], $get['debug']);
			$nexturl = http_build_query($get);
			include mobileoem_template('forum/viewthread');
			if(!empty($_GET['debug'])) {
				exit;
			}
			$content = ob_get_contents();
			ob_end_clean();
		}
		$variable['forumname'] = $forum['name'];
		$variable['datatype'] = $_G['page'] == 1 ? 0 : 1;
		$variable['webview_page'] = $content;
		$variable['ppp'] = $_G['ppp'];
		$variable['posts'] = count($postlist);
		$variable['page'] = $_G['page'];
		if($_G['forum_discuzcode']['passwordauthor']) {
			$variable['passwordpid'] = array_keys($_G['forum_discuzcode']['passwordauthor']);
		}

		mobile_core::result(mobile_core::variable($variable));
	}

}

?>