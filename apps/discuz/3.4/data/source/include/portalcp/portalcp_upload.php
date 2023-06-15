<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: portalcp_upload.php 30107 2012-05-11 02:10:58Z svn_project_zhangjie $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$operation = $_GET['op'] ? $_GET['op'] : '';

$upload = new discuz_upload();
$downremotefile = false;
$aid = intval(getgpc('aid'));
$catid = intval(getgpc('catid'));
if($aid) {
	$article = C::t('portal_article_title')->fetch($aid);
	if(!$article) {
		portal_upload_error(lang('portalcp', 'article_noexist'));
	}
	if(check_articleperm($catid, $aid, $article, false, true) !== true) {
		portal_upload_error(lang('portalcp', 'article_noallowed'));
	}
} else {
	if(($return = check_articleperm($catid, $aid, null, false, true)) !== true) {
		portal_upload_error(lang('portalcp', $return));
	}
}

if($operation == 'downremotefile') {
	$arrayimageurl = $temp = $imagereplace = array();
	$string = $_GET['content'];
	$downremotefile = true;
	preg_match_all("/\<img.+\bsrc\b\s*=('|\"|)(.*)('|\"|)([\s].*)?\>/ismU", $string, $temp, PREG_SET_ORDER);
	if(is_array($temp) && !empty($temp)) {
		foreach($temp as $tempvalue) {
			$tempvalue[2] = str_replace('\"', '', $tempvalue[2]);
			if(strlen($tempvalue[2])){
				$arrayimageurl[] = $tempvalue[2];
			}
		}
		$arrayimageurl = array_unique($arrayimageurl);
		if($arrayimageurl) {
			foreach($arrayimageurl as $tempvalue) {
				$imageurl = $tempvalue;
				$imagereplace['oldimageurl'][] = $imageurl;
				$attach['ext'] = $upload->fileext($imageurl);
				if(!$upload->is_image_ext($attach['ext'])) {
					continue;
				}
				$content = '';
				if(preg_match('/^(http(s?):\/\/|\.)/i', $imageurl)) {
					$content = dfsockopen($imageurl);
				} elseif(checkperm('allowdownlocalimg')) {
					if(preg_match('/^data\/(.*?)\.thumb\.jpg$/i', $imageurl)) {
						$content = file_get_contents(substr($imageurl, 0, strrpos($imageurl, '.')-6));
					} elseif(preg_match('/^data\/(.*?)\.(jpg|jpeg|gif|png)$/i', $imageurl)) {
						$content = file_get_contents($imageurl);
					}
				}
				if(empty($content)) continue;
				$temp = explode('/', $imageurl);

				$attach['name'] =  trim($temp[count($temp)-1]);
				$attach['thumb'] = '';

				$attach['isimage'] = $upload -> is_image_ext($attach['ext']);
				$attach['extension'] = $upload -> get_target_extension($attach['ext']);
				$attach['attachdir'] = $upload -> get_target_dir('portal');
				$attach['attachment'] = $attach['attachdir'] . $upload->get_target_filename('portal').'.'.$attach['extension'];
				$attach['target'] = getglobal('setting/attachdir').'./portal/'.$attach['attachment'];

				if(!@$fp = fopen($attach['target'], 'wb')) {
					continue;
				} else {
					flock($fp, 2);
					fwrite($fp, $content);
					fclose($fp);
				}
				if(!$upload->get_image_info($attach['target'])) {
					@unlink($attach['target']);
					continue;
				}
				$attach['size'] = filesize($attach['target']);
				$attachs[] = daddslashes($attach);
			}
		}
	}
} else {

	$upload->init($_FILES['attach'], 'portal');
	$attach = $upload->attach;

	if(!$upload->error()) {
		$upload->save();
	}
	if($upload->error()) {
		portal_upload_error($upload->error());
	}
	$attachs[] = $attach;
}

if($attachs) {

	foreach($attachs as $attach) {
		if($attach['isimage'] && empty($_G['setting']['portalarticleimgthumbclosed'])) {
			require_once libfile('class/image');
			$image = new image();
			$thumbimgwidth = $_G['setting']['portalarticleimgthumbwidth'] ? $_G['setting']['portalarticleimgthumbwidth'] : 300;
			$thumbimgheight = $_G['setting']['portalarticleimgthumbheight'] ? $_G['setting']['portalarticleimgthumbheight'] : 300;
			$attach['thumb'] = $image->Thumb($attach['target'], '', $thumbimgwidth, $thumbimgheight, 2);
			$image->Watermark($attach['target'], '', 'portal');
		}

		if(ftpperm($attach['ext'], $attach['size'])) {
			if(ftpcmd('upload', 'portal/'.$attach['attachment']) && (!$attach['thumb'] || ftpcmd('upload', 'portal/'.getimgthumbname($attach['attachment'])))) {
				@unlink($_G['setting']['attachdir'].'/portal/'.$attach['attachment']);
				@unlink($_G['setting']['attachdir'].'/portal/'.getimgthumbname($attach['attachment']));
				$attach['remote'] = 1;
			} else {
				if(getglobal('setting/ftp/mirror')) {
					@unlink($attach['target']);
					@unlink(getimgthumbname($attach['target']));
					portal_upload_error(lang('portalcp', 'upload_remote_failed'));
				}
			}
		}

		$setarr = array(
			'uid' => $_G['uid'],
			'filename' => $attach['name'],
			'attachment' => $attach['attachment'],
			'filesize' => $attach['size'],
			'isimage' => $attach['isimage'],
			'thumb' => $attach['thumb'],
			'remote' => $attach['remote'],
			'filetype' => $attach['extension'],
			'dateline' => $_G['timestamp'],
			'aid' => $aid
		);
		$setarr['attachid'] = C::t('portal_attachment')->insert($setarr, true);
		if($downremotefile) {
			$attach['url'] = ($attach['remote'] ? $_G['setting']['ftp']['attachurl'] : $_G['setting']['attachurl']).'portal/';
			$imagereplace['newimageurl'][] = $attach['url'].$attach['attachment'];
		}
		portal_upload_show($setarr);
	}
	if($downremotefile && $imagereplace) {
		$string = preg_replace(array("/\<(script|style|iframe)[^\>]*?\>.*?\<\/(\\1)\>/si", "/\<!*(--|doctype|html|head|meta|link|body)[^\>]*?\>/si"), '', $string);
		$string = str_replace($imagereplace['oldimageurl'], $imagereplace['newimageurl'], $string);
		$string = str_replace("\n", '<br>', $string);
		$string = str_replace(array("\r", "\n", "\r\n"), '', addcslashes($string, '/"\\\''));
		print <<<EOF
		<script type="text/javascript">
			var f = parent.window.frames["uchome-ifrHtmlEditor"].window.frames["HtmlEditor"];
			f.document.body.innerHTML = '$string';
		</script>
EOF;
	}
	exit();
}


function portal_upload_error($msg) {
	echo '<script>';
	echo 'if(parent.$(\'localfile_'.$_GET['attach_target_id'].'\') != null)parent.$(\'localfile_'.$_GET['attach_target_id'].'\').innerHTML = \''.lang('portalcp', 'upload_error').$msg.'\';else alert(\''.$msg.'\')';
	echo '</script>';
	exit();
}

function portal_upload_show($attach) {
	global $_G;

	$imagehtml = $filehtml = $coverstr ='';

	if($attach['isimage']) {
		$imagehtml = get_uploadcontent($attach, 'portal', 'upload');
		$coverstr = addslashes(serialize(array('pic'=>'portal/'.$attach['attachment'], 'thumb'=>$attach['thumb'], 'remote'=>$attach['remote'])));
	} else {
		$filehtml = get_uploadcontent($attach, 'portal', 'upload');
	}

	echo '<script type="text/javascript" src="'.$_G[setting][jspath].'handlers.js?'.$_G['style']['verhash'].'"></script>';
	echo '<script>';
	if($imagehtml) echo 'var tdObj = getInsertTdId(parent.$(\'imgattachlist\'), \'attach_list_'.$attach['attachid'].'\');tdObj.innerHTML = \''.addslashes($imagehtml).'\';';
	if($filehtml) echo 'parent.$(\'attach_file_body\').innerHTML = \''.addslashes($filehtml).'\'+parent.$(\'attach_file_body\').innerHTML;';
	echo 'if(parent.$(\'localfile_'.$_GET['attach_target_id'].'\') != null)parent.$(\'localfile_'.$_GET['attach_target_id'].'\').style.display = \'none\';';
	echo 'parent.$(\'attach_ids\').value += \','.$attach['attachid'].'\';';
	if($coverstr) echo 'if(parent.$(\'conver\').value == \'\')parent.$(\'conver\').value = \''.$coverstr.'\';';
	echo '</script>';

}

?>