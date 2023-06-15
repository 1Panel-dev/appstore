<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portalcp_diy.php 33949 2013-09-05 02:16:25Z laoguozhang $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$op = in_array($_GET['op'], array('style', 'diy', 'image', 'export', 'import', 'blockclass')) ? $_GET['op'] : '';


if (submitcheck('uploadsubmit')) {
	$topicid = intval($_POST['topicid']);
	if($topicid) {
		$topic = C::t('portal_topic')->fetch($topicid);
		if(empty($topic)) {
			topic_upload_error('diy_topic_noexist');
		}
	}
	topic_checkperm($topic);

	$upload = new discuz_upload();

	$upload->init($_FILES['attach'], 'portal');
	$attach = $upload->attach;

	if(!$upload->error()) {
		$upload->save();
	}
	if($upload->error()) {
		topic_upload_error($attach, $upload->error());
	} else {
		if($attach['isimage']) {
			require_once libfile('class/image');
			$image = new image();
			$attach['thumb'] = $image->Thumb($attach['target'], '', '80', '50');
		}

		if(ftpperm($attach['ext'], $attach['size'])) {
			if(ftpcmd('upload', 'portal/'.$attach['attachment'])) {
				if($attach['thumb']) {
					ftpcmd('upload', 'portal/'.getimgthumbname($attach['attachment']));
				}
				$attach['remote'] = 1;
			} else {
				if(getglobal('setting/ftp/mirror')) {
					@unlink($attach['target']);
					@unlink(getimgthumbname($attach['target']));
					topic_upload_error($attach, 'diy_remote_upload_failed');
				}
			}
		}

		$setarr = array(
			'uid' => $_G['uid'],
			'username' => $_G['username'],
			'filename' => $attach['name'],
			'filepath' => $attach['attachment'],
			'size' => $attach['size'],
			'thumb' => $attach['thumb'],
			'remote' => $attach['remote'],
			'dateline' => $_G['timestamp'],
			'topicid' => $topicid
		);
		$setarr['picid'] = C::t('portal_topic_pic')->insert($setarr, true);

		topic_upload_show($topicid);
	}

} elseif (submitcheck('diysubmit')) {

	require_once libfile('function/portalcp');

	$tpldirectory = getstr($_POST['tpldirectory'], 80);
	$template = getstr($_POST['template'], 50);
	if(dsign($tpldirectory.$template) !== $_POST['diysign']) {
		showmessage('diy_sign_invalid');
	}
	$tpldirectory = ($tpldirectory) ? $tpldirectory : $_G['cache']['style_default']['tpldir'];
	$savemod = getstr($_POST['savemod'], 1);
	$recover = getstr($_POST['recover'], 1);
	$optype = getstr($_POST['optype'],10);

	tpl_checkperm($template);

	list($template, $clonefile) = explode(':', $template);
	list($mod,$file) = explode('/', $template);
	$targettplname = $template;

	if ($savemod == '1' && !empty($clonefile)) {
		$targettplname = $template.'_'.$clonefile;
	}

	$istopic = $iscategory = $isarticle = false;
	if($template == 'portal/portal_topic_content') {
		$template = gettopictplname($clonefile);
		$istopic = true;
	} elseif ($template == 'portal/list') {
		$template = getportalcategorytplname($clonefile);
		$iscategory = true;
	} elseif ($template == 'portal/view') {
		$template = getportalarticletplname($clonefile, $template);
		$isarticle = true;
	}

	if(($istopic || $iscategory || $isarticle) && strpos($template, ':') !== false) {
		list($tpldirectory, $template) = explode(':', $template);
	}

	$checktpl = checkprimaltpl($tpldirectory.':'.$template);
	if($checktpl !== true) {
		showmessage($checktpl);
	}

	if($optype == 'canceldiy') {
		@unlink(DISCUZ_ROOT.'./data/diy/'.$tpldirectory.'/'.$targettplname.'_diy_preview.htm');
		if($targettplname == $template) @unlink(DISCUZ_ROOT.'./data/diy/'.$tpldirectory.'/'.$targettplname.'_'.$clonefile.'_diy_preview.htm');
		showmessage('do_success');
	}

	if ($recover == '1') {
		$file = './data/diy/'.$tpldirectory.'/'.$targettplname.'.htm';
		if (is_file($file.'.bak')) {
			copy ($file.'.bak', $file);
		} else {
			showmessage('diy_backup_noexist');
		}
	} else {
		$templatedata = array();
		checksecurity($_POST['spacecss']);
		$templatedata['spacecss'] = preg_replace("/(\<|\>)/is", '', $_POST['spacecss']);
		$style = empty($_POST['style'])?'':preg_replace("/[^0-9a-z]/i", '', $_POST['style']);
		if($style) {
			$cssfile = DISCUZ_ROOT.'./static/topic/'.$style.'/style.css';
			if(!file_exists($cssfile)) {
				showmessage('theme_does_not_exist');
			} else {
				$templatedata['style'] = "static/topic/$style/style.css";
			}
		}

		$layoutdata = getstr($_POST['layoutdata'],0,0,0,0,1);
		require_once libfile('class/xml');
		$templatedata['layoutdata'] = xml2array($layoutdata);
		if (empty($templatedata['layoutdata'])) showmessage('diy_data_format_invalid');

		$r = save_diy_data($tpldirectory, $template, $targettplname, $templatedata, true, $optype);

		include_once libfile('function/cache');
		updatecache('diytemplatename');

		if ($r && $optype != 'savecache') {
			if (!$iscategory && !$istopic && empty($savemod) && !empty($clonefile)) {
				$delfile = DISCUZ_ROOT.'./data/diy/'.$tpldirectory.'/'.$template.'_'.$clonefile.'.htm';
				if (file_exists($delfile)) {
					unlink($delfile);
					@unlink($delfile.'.bak');
					C::t('common_template_block')->delete_by_targettplname("{$template}_{$clonefile}", $tpldirectory);
					C::t('common_diy_data')->delete("{$template}_{$clonefile}", $tpldirectory);
					include_once libfile('function/cache');
					updatecache('diytemplatename');
				}
			}
		}
	}

	$tourl = empty($_POST['gobackurl']) || strpos($_POST['gobackurl'],'op=add') != false || strpos($_POST['gobackurl'],'&diy=yes') != false ?
			str_replace('&diy=yes','',$_SERVER['HTTP_REFERER']) : $_POST['gobackurl'];

	$tourl = preg_replace('/[\?|&]preview=yes/', '', $tourl);

	showmessage('do_success', $tourl,array('rejs'=>$_POST['rejs']));
}
if($op == 'blockclass') {

	loadcache('blockclass');
} elseif($op == 'style') {

	if(!$_G['group']['allowmanagetopic'] && !$_G['group']['allowdiy'] && !$_G['group']['allowaddtopic']) {
		showmessage('group_nopermission', NULL, array('grouptitle' => $_G['group']['grouptitle']), array('login' => 1));
	}

	$themes = gettheme('topic');

} elseif ($op == 'diy' || $op == 'image') {

	$topicid = intval($_GET['topicid']);
	$topic = C::t('portal_topic')->fetch($topicid);
	topic_checkperm($topic);

	$perpage = 6;
	$page = max(1, intval($_GET['page']));
	$start=  ($page-1) * $perpage;

	$list = array();
	if ($topicid) {
		$count = C::t('portal_topic_pic')->count_by_topicid($topicid);
		if (!empty($count)) {
			foreach(C::t('portal_topic_pic')->fetch_all_by_topicid($topicid, $start, $perpage) as $value) {
				$value['pic'] = pic_get($value['filepath'], 'portal', $value['thumb'], $value['remote']);
				$list[] = $value;
			}
		}
		$multi= multi($count, $perpage, $page, "portal.php?mod=portalcp&ac=diy&op=image&topicid=$topicid");
	}


} elseif ($op == 'delete') {

	$topicid = intval($_GET['topicid']);
	$topic = C::t('portal_topic')->fetch($topicid);
	topic_checkperm($topic);

	$picid = intval($_GET['picid']);

} elseif ($op == 'export') {
	if (submitcheck('exportsubmit')) {
		$tpl = $_POST['tpl'];
		$tpldirectory = $_POST['tpldirectory'];
		$frame = $_POST['frame'];
		$type = $_POST['type'];
		if (!empty($tpl)) {
			tpl_checkperm($tpl);

			list($tpl,$id) = explode(':', $tpl);
			$tplname = $id ? $tpl.'_'.$id : $tpl;
			$diydata = C::t('common_diy_data')->fetch($tplname, $tpldirectory);
			if(empty($diydata) && $id) $diydata = C::t('common_diy_data')->fetch($tpl, $tpldirectory);
			if ($diydata) {

				$filename = $diydata['targettplname'];

				$diycontent = dunserialize($diydata['diycontent']);

				if (empty($diycontent)) showmessage('diy_no_export_data');
				if ($frame) {
					$area = '';
					$filename = $frame;
					$framedata = array();
					foreach ($diycontent['layoutdata'] as $key => $value) {
						$framedata = getobjbyname($frame, $value);
						if ($framedata) {
							$area = $key;
							getframeblock(array($framedata['type'].'`'.$frame => $framedata['content']));
							break;
						}
					}
				} else {
					foreach ($diycontent['layoutdata'] as $key => $value) {
						if (!empty($value)) getframeblock($value);
					}
				}

				$diycontent['blockdata'] = block_export($_G['curtplbid']);

				if ($frame) {
					$diycontent['spacecss'] = getcssdata($diycontent['spacecss']);
					$diycontent['layoutdata'] = array();
					$area = empty($area) ? 'diy1' : $area;
					$diycontent['layoutdata'][$area][$framedata['type'].'`'.$frame] = $framedata['content'] ? $framedata['content'] : array();
				}

				dheader('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
				dheader('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
				dheader('Cache-Control: no-cache, must-revalidate');
				dheader('Pragma: no-cache');
				dheader('Content-Encoding: none');

				if ($type == 'txt') {
					$str = serialize($diycontent);
					dheader('Content-Length: '.strlen($str));
					dheader('Content-Disposition: attachment; filename='.$filename.'.txt');
					dheader('Content-Type: text/plain');
				} else {
					require_once libfile('class/xml');
					$str = array2xml($diycontent, true);
					dheader('Content-Length: '.strlen($str));
					dheader('Content-Disposition: attachment; filename='.$filename.'.xml');
					dheader('Content-Type: text/xml');
				}
				echo $str;
				exit();
			} else {
				showmessage('diy_export_no_data','/');
			}
		} else {
			showmessage('diy_export_tpl_invalid','/');
		}
	}
	showmessage('diy_operation_invalid','/');
} elseif ($op == 'import') {

	$tpl = $_POST['tpl'] ? $_POST['tpl'] : $_GET['tpl'];
	tpl_checkperm($tpl);
	$tpldir = './template/default/portal/diyxml/';
	if ($_G['style']['tpldir'] && preg_match("#^\./template/\w+$#i", $_G['style']['tpldir']) && is_dir(DISCUZ_ROOT.$_G['style']['tpldir'].'/portal/diyxml/')) {
		$tpldir = $_G['style']['tpldir'].'/portal/diyxml/';
	}

	if (submitcheck('importsubmit')) {
		$isinner = false;
		$filename = '';
		if($_POST['importfilename']) {
			if (!preg_match("/^\w+$/i", $_POST['importfilename'])) {
				showmessage('do_success','portal.php',array('status'=>0));
			}
			$filename = DISCUZ_ROOT.$tpldir.$_POST['importfilename'].'.xml';
			if(!file_exists($filename)) {
				$filename = DISCUZ_ROOT.$tpldir.$_POST['importfilename'].'.php';
			}
			$isinner = true;
		} else {
			$upload = new discuz_upload();

			$upload->init($_FILES['importfile'], 'temp');
			$attach = $upload->attach;

			if(!$upload->error()) {
				$upload->save();
			}
			if($upload->error()) {
				showmessage($upload->error(),'portal.php',array('status'=>$upload->error()));
			} else {
				$filename = $attach['target'];
			}
		}
		if($filename && file_exists($filename)) {
			$arr = import_diy($filename);
			if(!$isinner) {
				@unlink($filename);
			}
			if (!empty($arr)) {
				$search = array('/\<script/i', '/\<\/script\>/i', "/\r/", "/\n/", '/(\[script [^>]*?)(src=)(.*?\[\/script\])/');
				$replace = array('[script', '[/script]', '', '', '$1[src=]$3');
				$arr['css'] = str_replace(array("\r","\n"),array(''),$arr['css']);

				$jsarr = array('status'=>1,'css'=>$arr['css'],'bids'=>implode(',',$arr['mapping']));

				foreach ($arr['html'] as $key => $value) {
					$value = preg_replace($search,$replace,$value);
					$jsarr['html'][$key] = $value;
				}

				showmessage('do_success','portal.php',$jsarr);
			} else {
				showmessage('do_success','portal.php',array('status'=>0));
			}
		} else {
			showmessage('do_success','portal.php',array('status'=>0));
		}
	}
	$xmlarr = array();
	if ($_GET['type'] == 1) {
		$xmlfilepath = DISCUZ_ROOT.$tpldir;
		if(($dh = @opendir($xmlfilepath))) {
			while(($file = @readdir($dh)) !== false) {
				if(in_array(fileext($file), array('xml', 'php'))) {
					$xmlarr[substr($file, 0, -4)] = getdiyxmlname($file, $xmlfilepath);
				}
			}
			closedir($dh);
		}
		arsort($xmlarr);
	}
} else {
	showmessage('undefined_action');
}

include_once template("portal/portalcp_diy");

function topic_upload_error($attach, $msg='') {
	echo '<script>';
	echo 'parent.document.getElementById(\'uploadmsg\').innerHTML = \''.$attach['name'].' '.lang('home/template', 'upload_error').$msg.'\';';
	echo '</script>';
	exit();
}

function topic_upload_show($topicid) {

	echo '<script>';
	echo 'parent.ajaxget("portal.php?mod=portalcp&ac=diy&op=image&topicid='.$topicid.'&", "diyimages");';
	echo 'parent.document.uploadpic.attach.value = \'\';';
	echo 'Util.toggleEle(\'upload\')';
	echo '</script>';
	exit();
}

function tpl_checkperm($tpl) {
	global $_G;
	list($file,$id) = explode(':', $tpl);
	if ($file == 'portal/portal_topic_content') {
		$topicid = max(0,intval($id));
		$topic = C::t('portal_topic')->fetch($topicid);
		topic_checkperm($topic);
	} elseif($file == 'portal/list'){
		$catid = max(0,intval($id));
		$category = $_G['cache']['portalcategory'][$catid];
		category_checkperm($category);
	} else {
		if(!$_G['group']['allowdiy']) {
			showmessage('diy_nopermission');
		}
	}
}

function category_checkperm($category) {
	global $_G;
	if(empty($category)) {
		showmessage('topic_not_exist');
	}

	if($_G['group']['allowdiy']) return true;

	if(!$_G['group']['allowdiy'] && (!$_G['group']['allowaddtopic'] || $_G['uid'] != $topic['uid'])) {
		showmessage('topic_edit_nopermission');
	}

}

function topic_checkperm($topic) {
	global $_G;
	if(empty($topic)) {
		showmessage('topic_not_exist');
	}
	if(!$_G['group']['allowmanagetopic'] && (!$_G['group']['allowaddtopic'] || $_G['uid'] != $topic['uid'])) {
		showmessage('topic_edit_nopermission');
	}
}

function gettopictplname($topicid) {
	$topicid = max(0,intval($topicid));
	$topic = C::t('portal_topic')->fetch($topicid);
	return !empty($topic) && !empty($topic['primaltplname']) ? $topic['primaltplname'] : getglobal('cache/style_default/tpldir').':portal/portal_topic_content';
}

function getportalcategorytplname($catid) {
	global $_G;
	$catid = max(0,intval($catid));
	$category = $_G['cache']['portalcategory'][$catid];
	return !empty($category) && !empty($category['primaltplname']) ? $category['primaltplname'] : getglobal('cache/style_default/tpldir').':portal/list';
}

function getportalarticletplname($catid, $primaltplname = ''){
	if(($catid = intval($catid))) {
		if(($category = C::t('portal_category')->fetch($catid))) {
			$primaltplname = $category['articleprimaltplname'];
		}
		if(empty($primaltplname)) {
			$primaltplname = getglobal('cache/style_default/tpldir').':portal/view';
			C::t('portal_category')->update($catid, array('articleprimaltplname' => $primaltplname));
		}
	}
	return $primaltplname;
}

function getdiyxmlname($filename, $path) {
	$content = @file_get_contents($path.$filename);
	$name = $filename;
	if($content) {
		preg_match("/\<\!\-\-\[name\](.+?)\[\/name\]\-\-\>/i", trim($content), $mathes);
		if(!empty($mathes[1])) {
			preg_match("/^\{lang (.+?)\}$/", $mathes[1], $langs);
			if(!empty($langs[1])) {
				$name = lang('portalcp', $langs[1]);
			} else {
				$name = dhtmlspecialchars($mathes[1]);
			}
		}
	}
	return $name;
}
?>