<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_portalcp.php 35943 2016-05-18 03:26:08Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function get_uploadcontent($attach, $type='portal', $dotype='') {

	$return = '';
	$dotype = $dotype ? 'checked' : '';
	if($attach['isimage']) {
		$pic = pic_get($attach['attachment'], $type, $attach['thumb'], $attach['remote'], 0);
		$small_pic = $attach['thumb'] ? getimgthumbname($pic) : '';
		$check = $attach['pic'] == $type.'/'.$attach['attachment'] ? 'checked' : $dotype;
		$aid = $check ? $attach['aid'] : '';

		$return .= '<a href="javascript:;" class="opattach"><span class="opattach_ctrl">';
		$return .= '<span onclick="insertImage(\''.$pic.'\');" class="cur1">'.lang('portalcp', 'insert_large_image').'</span>';
		$return .= '<span class="pipe">|</span>';
		if($small_pic) $return .= '<span onclick="insertImage(\''.$small_pic.'\', \''.$pic.'\');" class="cur1">'.lang('portalcp', 'small_image').'</span>';
		$return .= '</span><img src="'.($small_pic ? $small_pic : $pic).'" onclick="insertImage(\''.$pic.'\');" class="cur1"></a>';
		$return .= '<label for="setconver'.$attach['attachid'].'" class="cur1 xi2"><input type="radio" name="setconver" id="setconver'.$attach['attachid'].'" class="pr" value="1" onclick="setConver(\''.addslashes(serialize(array('pic'=>$type.'/'.$attach['attachment'], 'thumb'=>$attach['thumb'], 'remote'=>$attach['remote']))).'\') '.$check.'>'.lang('portalcp', 'set_to_conver').'</label>';
		$return .= '<span class="pipe">|</span>';
		if($type == 'portal') $return .= '<span class="cur1 xi2" onclick="deleteAttach(\''.$attach['attachid'].'\', \'portal.php?mod=attachment&id='.$attach['attachid'].'&aid='.$aid.'&op=delete&formhash='.FORMHASH.'\');">'.lang('portalcp', 'delete').'</span>';

	} else {
		$attach_url = $type == 'forum' ? 'forum.php?mod=attachment&aid='.aidencode($attach['attachid'], 1) : 'portal.php?mod=attachment&id='.$attach['attachid'];
		$return .= '<table id="attach_list_'.$attach['attachid'].'" width="100%" class="xi2">';
		$return .= '<td width="50" class="bbs"><a href="'.$attach_url.'" target="_blank">'.$attach['filename'].'</a></td>';
		$return .= '<td align="right" class="bbs">';
		$return .= '<a href="javascript:void(0);" onclick="insertFile(\''.$attach['filename'].'\', \''.$attach_url.'\');return false;">'.lang('portalcp', 'insert_file').'</a><br>';
		if($type == 'portal') $return .= '<a href="javascript:void(0);" onclick="deleteAttach(\''.$attach['attachid'].'\', \'portal.php?mod=attachment&id='.$attach['attachid'].'&op=delete&formhash='.FORMHASH.'\');return false;">'.lang('portalcp', 'delete').'</a>';
		$return .= '</td>';
		$return .= '</table>';
	}
	return $return;

}

function get_upload_content($attachs, $dotype='') {
	$html = '';
	$dotype = $dotype ? 'checked' : '';
	$i = 0;
	foreach($attachs as $key => $attach) {
		$type = $attach['from'] == 'forum' ? 'forum' : 'portal';
		$html .= '<td id="attach_list_'.$attach['attachid'].'">';
		if($attach['isimage']) {
			$pic = pic_get($attach['attachment'], $type, $attach['thumb'], $attach['remote'], 0);
			$small_pic = $attach['thumb'] ? getimgthumbname($pic) : '';
			$check = $attach['pic'] == $type.'/'.$attach['attachment'] ? 'checked' : $dotype;
			$aid = $check ? $attach['aid'] : '';

			$html .= '<a href="javascript:;" class="opattach">';
			$html .= '<span class="opattach_ctrl">';
			$html .= '<span onclick="insertImage(\''.$pic.'\');" class="cur1">'.lang('portalcp', 'insert_large_image').'</span><span class="pipe">|</span>';
			if($small_pic) $html .= '<span onclick="insertImage(\''.$small_pic.'\', \''.$pic.'\');" class="cur1">'.lang('portalcp', 'small_image').'</span>';
			$html .= '</span><img src="'.($small_pic ? $small_pic : $pic).'" onclick="insertImage(\''.$pic.'\');" class="cur1" /></a>';
			$html .= '<label for="setconver'.$attach['attachid'].'" class="cur1 xi2"><input type="radio" name="setconver" id="setconver'.$attach['attachid'].'" class="pr" value="1" onclick=setConver(\''.addslashes(serialize(array('pic'=>$type.'/'.$attach['attachment'], 'thumb'=>$attach['thumb'], 'remote'=>$attach['remote']))).'\') '.$check.'>'.lang('portalcp', 'set_to_conver').'</label>';
			if($type == 'portal') {
				$html .= '<span class="pipe">|</span><span class="cur1 xi2" onclick="deleteAttach(\''.$attach['attachid'].'\', \'portal.php?mod=attachment&id='.$attach['attachid'].'&aid='.$aid.'&op=delete&formhash='.FORMHASH.'\');">'.lang('portalcp', 'delete').'</span>';
			}
		} else {
			$html .= '<img src="static/image/editor/editor_file_thumb.png" class="cur1" onclick="insertFile(\''.$attach['filename'].'\', \'portal.php?mod=attachment&id='.$attach['attachid'].'\');" tip="'.$attach['filename'].'" onmouseover="showTip(this);" /><br/>';
			$html .= '<span onclick="deleteAttach(\''.$attach['attachid'].'\', \'portal.php?mod=attachment&id='.$attach['attachid'].'&op=delete&formhash='.FORMHASH.'\');" class="cur1 xi2">'.lang('portalcp', 'delete').'</span>';
		}
		$html .= '</td>';
		$i++;

		if($i % 4 == 0 && isset($attachs[$i])) {
			$html .= '</tr><tr>';
		}
	}
	if(!empty($html)) {
		if(($imgpad = $i % 4) > 0) {
			$html .= str_repeat('<td width="25%"></td>', 4 - $imgpad);
		}

		$html = '<table class="imgl"><tr>'.$html.'</tr></table>';
	}
	return $html;
}

function getallowcategory($uid){
	global $_G;
	$permission = array();
	if (empty($uid)) return $permission;
	if(getstatus($_G['member']['allowadmincp'], 2) || getstatus($_G['member']['allowadmincp'], 3)) {
		$uid = max(0,intval($uid));
		foreach(C::t('portal_category_permission')->fetch_all_by_uid($uid) as $catid=>$value) {
			if ($value['allowpublish'] || $value['allowmanage']) {
				$permission[$catid] = $value;
			}
		}
	}
	return $permission;
}

function getpermissioncategory($category, $permission = array()) {

	$cats = array();
	foreach ($permission as $k=>$v) {
		$cur = $category[$v];

		if ($cur['level'] != 0) {
			while ($cur['level']) {
				$cats[$cur['upid']]['permissionchildren'][$cur['catid']] = $cur['catid'];
				$cur = $category[$cur['upid']];
			}
		} elseif(empty($cats[$v])) {
			$cats[$v] = array();
		}
	}

	return $cats;
}

function getallowdiytemplate($uid){
	if (empty($uid)) return false;
	$permission = array();
	$uid = max(0,intval($uid));
	$permission = C::t('common_template_permission')->fetch_all_by_uid($uid);
	return $permission;
}

function getdiytpldir($targettplname) {
	global $_G;
	$tpldir = $pre = '';
	if (substr($targettplname, 0, 13) === ($pre = 'forum/discuz_')) {
	} elseif (substr($targettplname, 0, 19) === ($pre = 'forum/forumdisplay_')) {
	}
	if($pre) {
		$forum = C::t('forum_forum')->fetch(intval(str_replace($pre, '', $targettplname)));
		if(!empty($forum['styleid'])) {
			$_cname = 'style_'.$forum['styleid'];
			loadcache($_cname);
			$tpldir = empty($_G['cache'][$_cname]['tpldir']) ? '' : $_G['cache'][$_cname]['tpldir'];
		}
	}
	return $tpldir ? $tpldir : ($_G['cache']['style_default']['tpldir'] ? $_G['cache']['style_default']['tpldir'] : './template/default');
}

function save_diy_data($tpldirectory, $primaltplname, $targettplname, $data, $database = false, $optype = '') {
	global $_G;
	if (empty($data) || !is_array($data)) return false;
	checksecurity($data['spacecss']);
	if(empty($tpldirectory)) {
		$tpldirectory = getdiytpldir($targettplname);
	}
	$isextphp = false;
	$file = $tpldirectory.'/'.$primaltplname.'.htm';
	if (!file_exists($file)) {
		$file = $tpldirectory.'/'.$primaltplname.'.php';
		if (!file_exists($file)) {
			$file = './template/default/'.$primaltplname.'.htm';
		} else {
			$isextphp = true;
		}
	}
	if(!file_exists($file)) return false;
	$content = file_get_contents(DISCUZ_ROOT.$file);
	if($isextphp) {
		$content = substr($content, strpos($content, "\n"));
	}
	$content = preg_replace("/\<\!\-\-\[name\].+?\[\/name\]\-\-\>\s+/is", '', $content);
	$content = preg_replace("/\<script src\=\"misc\.php\?mod\=diyhelp\&action\=get.+?\>\<\/script\>/", '', $content);
	foreach ($data['layoutdata'] as $key => $value) {
		$key = trimdxtpllang($key);
		$html = '';
		$html .= '<div id="'.$key.'" class="area">';
		$html .= getframehtml($value);
		$html .= '</div>';
		$content = preg_replace("/(\<\!\-\-\[diy\=$key\]\-\-\>).+?(\<\!\-\-\[\/diy\]\-\-\>)/is", "\\1".$html."\\2", $content);
	}
	$data['spacecss'] = str_replace('.content', '.dxb_bc', $data['spacecss']);
	$data['spacecss'] = trimdxtpllang($data['spacecss']);
	$content = preg_replace("/(\<style id\=\"diy_style\" type\=\"text\/css\"\>).*?(\<\/style\>)/is", "\\1".$data['spacecss']."\\2", $content);
	if (!empty($data['style'])) {
		$content = preg_replace("/(\<link id\=\"style_css\" rel\=\"stylesheet\" type\=\"text\/css\" href\=\").+?(\"\>)/is", "\\1".$data['style']."\\2", $content);
	}

	$flag = $optype == 'savecache' ? true : false;
	if($flag) {
		$targettplname = $targettplname.'_diy_preview';
	} else {
		@unlink('./data/diy/'.$tpldirectory.'/'.$targettplname.'_diy_preview.htm');
	}

	$tplfile =DISCUZ_ROOT.'./data/diy/'.$tpldirectory.'/'.$targettplname.'.htm';
	$tplpath = dirname($tplfile);
	if (!is_dir($tplpath)) {
		dmkdir($tplpath);
	} else {
		if (file_exists($tplfile) && !$flag) copy($tplfile, $tplfile.'.bak');
	}
	$r = file_put_contents($tplfile, $content);
	if ($r && $database && !$flag) {
		$diytplname = getdiytplname($targettplname, $tpldirectory);
		C::t('common_diy_data')->insert(array(
			'targettplname' => $targettplname,
			'tpldirectory' => $tpldirectory,
			'primaltplname' => $primaltplname,
			'diycontent' => serialize($data),
			'name' => $diytplname,
			'uid' => $_G['uid'],
			'username' => $_G['username'],
			'dateline' => TIMESTAMP,
		), false, true);
	}
	return $r;
}


function getdiytplnames($tpls) {
	$arr = $ret = array();
	foreach((array)$tpls as $targettplname) {
		$id = $pre = '';
		if (substr($targettplname, 0, 12) === ($pre = 'portal/list_')) {
		} elseif (substr($targettplname, 0, 12) === ($pre = 'portal/view_')) {
		} elseif (substr($targettplname, 0, 13) === ($pre = 'forum/discuz_')) {
		} elseif (substr($targettplname, 0, 17) === ($pre = 'forum/viewthread_')) {
		} elseif (substr($targettplname, 0, 19) === ($pre = 'forum/forumdisplay_')) {
		} elseif (substr($targettplname, 0, 28) === ($pre = 'portal/portal_topic_content_')) {
		}
		if($pre && ($id = dintval(str_replace($pre, '', $targettplname)))) {
			$arr[$pre][$id] = $id;
		}
	}
	foreach($arr as $pre => $ids) {
		if ($pre === 'portal/list_') {
			foreach(C::t('portal_category')->fetch_all($ids) as $id => $value) {
				$ret[$pre][$id] = $value['catname'];
			}
		} elseif ($pre === 'portal/view_') {
			$portal_view_name = lang('portalcp', 'portal_view_name');
			foreach(C::t('portal_category')->fetch_all($ids) as $id => $value) {
				$ret[$pre][$id] = $value['catname'].$portal_view_name;
			}
		} elseif ($pre === 'forum/forumdisplay_' || $pre === 'forum/discuz_') {
			foreach(C::t('forum_forum')->fetch_all($ids) as $id => $value) {
				$ret[$pre][$id] = $value['name'];
			}
		} elseif ($pre === 'forum/viewthread_') {
			$forum_viewthread_name = lang('portalcp', 'forum_viewthread_name');
			foreach(C::t('forum_forum')->fetch_all($ids) as $id => $value) {
				$ret[$pre][$id] = $value['name'].$forum_viewthread_name;
			}
		} elseif ($pre === 'portal/portal_topic_content_') {
			foreach(C::t('portal_topic')->fetch_all($ids) as $id => $value) {
				$ret[$pre][$id] = $value['title'];
			}
		}
	}
	return $ret;
}

function getdiytplname($targettplname, $tpldirectory) {
	$diydata = C::t('common_diy_data')->fetch($targettplname, $tpldirectory);
	$diytplname = $diydata ? $diydata['name'] : '';
	if(empty($diytplname) && ($data = getdiytplnames(array($targettplname)))) {
		$diytplname = array_shift(array_shift($data));
	}
	return $diytplname;
}
function getframehtml($data = array()) {
	global $_G;
	$html = $style = '';
	foreach ((array)$data as $id => $content) {
		$id = trimdxtpllang($id);
		$flag = $name = '';
		list($flag, $name) = explode('`', $id);
		if ($flag == 'frame') {
			$fattr = $content['attr'];
			$fattr['name'] = trimdxtpllang($fattr['name']);
			$fattr['className'] = trimdxtpllang($fattr['className']);
			$moveable = $fattr['moveable'] == 'true' ? ' move-span' : '';
			$html .= '<div id="'.$fattr['name'].'" class="'.$fattr['className'].'">';
			if (checkhastitle($fattr['titles'])) {
				$style = gettitlestyle($fattr['titles']);
				$cn = trimdxtpllang(implode(' ',$fattr['titles']['className']));
				$html .= '<div class="'.$cn.'"'.$style.'>'.gettitlehtml($fattr['titles'], 'frame').'</div>';
			}
			foreach ((array)$content as $colid => $coldata) {
				list($colflag, $colname) = explode('`', $colid);
				$colname = trimdxtpllang($colname);
				$cn = trimdxtpllang($coldata['attr']['className']);
				if ($colflag == 'column') {
					$html .= '<div id="'.$colname.'" class="'.$cn.'">';
					$html .= '<div id="'.$colname.'_temp" class="move-span temp"></div>';
					$html .= getframehtml($coldata);
					$html .= '</div>';
				}
			}
			$html .= '</div>';
		} elseif ($flag == 'tab') {
			$fattr = $content['attr'];
			$fattr['name'] = trimdxtpllang($fattr['name']);
			$fattr['className'] = trimdxtpllang($fattr['className']);
			$moveable = $fattr['moveable'] == 'true' ? ' move-span' : '';
			$html .= '<div id="'.$fattr['name'].'" class="'.$fattr['className'].'">';
			$switchtype = 'click';
			foreach ((array)$content as $colid => $coldata) {
				list($colflag, $colname) = explode('`', $colid);
				$colname = trimdxtpllang($colname);
				$cn = trimdxtpllang($coldata['attr']['className']);
				if ($colflag == 'column') {
					if (checkhastitle($fattr['titles'])) {
						$style = gettitlestyle($fattr['titles']);
						$title = gettitlehtml($fattr['titles'], 'tab');
					}
					$switchtype = is_array($fattr['titles']['switchType']) && !empty($fattr['titles']['switchType'][0]) ? $fattr['titles']['switchType'][0] : 'click';
					$switchtype = in_array(strtolower($switchtype), array('click', 'mouseover')) ? $switchtype : 'click';
					$html .= '<div id="'.$colname.'" class="'.$cn.'"'.$style.' switchtype="'.$switchtype.'">'.$title;
					$html .= getframehtml($coldata);
					$html .= '</div>';
				}
			}
			$html .= '<div id="'.$fattr['name'].'_content" class="tb-c"></div>';
			$html .= '<script type="text/javascript">initTab("'.$fattr['name'].'","'.$switchtype.'");</script>';
			$html .= '</div>';
		} elseif ($flag == 'block') {
			$battr = $content['attr'];
			$bid = intval(str_replace('portal_block_', '', $battr['name']));
			if (!empty($bid)) {
				$html .= "<!--{block/{$bid}}-->";
				$_G['curtplbid'][$bid] = $bid;
			}
		}
	}

	return $html;
}
function gettitlestyle($title) {
	$style = '';
	if (is_array($title['style']) && count($title['style'])) {
		foreach ($title['style'] as $k=>$v){
			$style .= trimdxtpllang($k).':'.trimdxtpllang($v).';';
		}
	}
	$style = $style ? ' style=\''.$style.'\'' : '';
	return $style;
}
function checkhastitle($title) {
	if (!is_array($title)) return false;
	foreach ($title as $k => $v) {
		if (strval($k) == 'className') continue;
		if (!empty($v['text'])) return true;
	}
	return false;
}

function gettitlehtml($title, $type) {
	global $_G;
	if (!is_array($title)) return '';
	$html = $one = $style = $color =  '';
	foreach ($title as $k => $v) {
		if (in_array(strval($k),array('className','style'))) continue;
		if (empty($v['src']) && empty($v['text'])) continue;
		$v['className'] = trimdxtpllang($v['className']);
		$v['font-size'] = intval($v['font-size']);
		$v['margin'] = intval($v['margin']);
		$v['float'] = trimdxtpllang($v['float']);
		$v['color'] = trimdxtpllang($v['color']);
		$v['src'] = trimdxtpllang($v['src']);
		$v['href'] = trimdxtpllang($v['href']);
		$v['text'] = dhtmlspecialchars(str_replace(array('{', '$'), array('{ ', '$ '), $v['text']));
		$one = "<span class=\"{$v['className']}\"";
		$style = $color = "";
		$style .= empty($v['font-size']) ? '' : "font-size:{$v['font-size']}px;";
		$style .= empty($v['float']) ? '' : "float:{$v['float']};";
		$margin_ = empty($v['float']) ? 'left' : $v['float'];
		$style .= empty($v['margin']) ? '' : "margin-{$margin_}:{$v['margin']}px;";
		$color = empty($v['color']) ? '' : "color:{$v['color']};";
		$img = !empty($v['src']) ? '<img src="'.$v['src'].'" class="vm" alt="'.$v['text'].'"/>' : '';
		if (empty($v['href'])) {
			$style = empty($style)&&empty($color) ? '' : ' style="'.$style.$color.'"';
			$one .= $style.">$img{$v['text']}";
		} else {
			$style = empty($style) ? '' : ' style="'.$style.'"';
			$colorstyle = empty($color) ? '' : ' style="'.$color.'"';
			$one .= $style.'><a href="'.$v['href'].'" target="_blank"'.$colorstyle.'>'.$img.$v['text'].'</a>';
		}
		$one .= '</span>';

		$siteurl = str_replace(array('/','.'),array('\/','\.'),$_G['siteurl']);
		$one = preg_replace('/\"'.$siteurl.'(.*?)\"/','"$1"',$one);

		$html = $k === 'first' ? $one.$html : $html.$one;
	}
	return $html;
}

function gettheme($type) {
	$themes = array();
	$themedirs = dreaddir(DISCUZ_ROOT."/static/$type");
	foreach ($themedirs as $key => $dirname) {
		$now_dir = DISCUZ_ROOT."/static/$type/$dirname";
		if(file_exists($now_dir.'/style.css') && file_exists($now_dir.'/preview.jpg')) {
			$themes[] = array(
				'dir' => $type.'/'.$dirname,
				'name' => getcssname($type.'/'.$dirname)
			);
		}
	}
	return $themes;
}

function getcssname($dirname) {
	$css = @file_get_contents(DISCUZ_ROOT.'./static/'.$dirname.'/style.css');
	if($css) {
		preg_match("/\[name\](.+?)\[\/name\]/i", trim($css), $mathes);
		if(!empty($mathes[1])) $name = dhtmlspecialchars($mathes[1]);
	} else {
		$name = 'No name';
	}
	return $name;
}

function checksecurity($str) {

	$filter = array(
		'/\/\*[\n\r]*(.+?)[\n\r]*\*\//is',
		'/[^a-z0-9\\\]+/i',
		'/important/i',
	);
	if(preg_match("/[^a-z0-9:;'\(\)!\.#\-_\s\{\}\/\,\"\?\>\=\?\%]+/i", $str)) {
		showmessage('css_contains_elements_of_insecurity');
	}
	$str = preg_replace($filter, '', $str);
	if(preg_match("/(expression|import|javascript)/i", $str)) {
		showmessage('css_contains_elements_of_insecurity');
	}
	return true;
}

function block_export($bids) {
	$return = array('block'=>array(), 'style'=>array());
	if(empty($bids)) {
		return;
	}
	$styleids = array();
	foreach(C::t('common_block')->fetch_all($bids) as $value) {
		$value['param'] = dunserialize($value['param']);
		if(!empty($value['blockstyle'])) $value['blockstyle'] = dunserialize($value['blockstyle']);

		$return['block'][$value['bid']] = $value;
		if(!empty($value['styleid'])) $styleids[] = intval($value['styleid']);
	}
	if($styleids) {
		$styleids = array_unique($styleids);
		foreach(C::t('common_block_style')->fetch_all($styleids) as $value) {
			$value['template'] = dunserialize($value['template']);
			if(!empty($value['fields'])) $value['fields'] = dunserialize($value['fields']);
			$return['style'][$value['styleid']] = $value;
		}
	}
	return $return ;
}

function block_import($data) {
	global $_G;
	if(!is_array($data['block'])) {
		return ;
	}
	$stylemapping = array();
	if($data['style']) {
		$hashes = $styles = array();
		foreach($data['style'] as $value) {
			$hashes[] = $value['hash'];
			$styles[$value['hash']] = $value['styleid'];
		}
		if(!empty($hashes)) {
			foreach(C::t('common_block_style')->fetch_all_by_hash($hashes) as $value) {
				$id = $styles[$value['hash']];
				$stylemapping[$id] = intval($value['styleid']);
				unset($styles[$value['hash']]);
			}
		}
		foreach($styles as $id) {
			$style = $data['style'][$id];
			$style['styleid'] = '';
			if(is_array($style['template'])) {
				$style['template'] = serialize($style['template']);
			}
			if(is_array($style['fields'])) {
				$style['fields'] = serialize($style['fields']);
			}
			$newid = C::t('common_block_style')->insert($style, true);
			$stylemapping[$id] = $newid;
		}
	}

	$blockmapping = array();
	foreach($data['block'] as $block) {
		$oid = $block['bid'];
		if(!empty($block['styleid'])) {
			$block['styleid'] = intval($stylemapping[$block['styleid']]);
		}
		$block['bid'] = '';
		$block['uid'] = $_G['uid'];
		$block['username'] = $_G['username'];
		$block['dateline'] = 0;
		$block['notinherited'] = 0;
		if(is_array($block['param'])) {
			$block['param'] = serialize($block['param']);
		}
		if(is_array($block['blockstyle'])) {
			$block['blockstyle'] = serialize($block['blockstyle']);
		}
		$newid = C::t('common_block')->insert($block, true);
		$blockmapping[$oid] = $newid;
	}
	include_once libfile('function/cache');
	updatecache('blockclass');
	return $blockmapping;
}

function getobjbyname($name, $data) {
	if (!$name || !$data) return false;

	foreach ((array)$data as $id => $content) {
		list($type, $curname) = explode('`', $id);
		if ($curname == $name) {
			return array('type'=>$type,'content'=>$content);
		} elseif ($type == 'frame' || $type == 'tab' || $type == 'column') {
			$r = getobjbyname($name, $content);
			if ($r) return $r;
		}
	}
	return false;
}

function getframeblock($data) {
	global $_G;

	if (!isset($_G['curtplbid'])) $_G['curtplbid'] = array();
	if (!isset($_G['curtplframe'])) $_G['curtplframe'] = array();

	foreach ((array)$data as $id => $content) {
		list($flag, $name) = explode('`', $id);
		if ($flag == 'frame' || $flag == 'tab') {
			foreach ((array)$content as $colid => $coldata) {
				list($colflag, $colname) = explode('`', $colid);
				if ($colflag == 'column') {
					getframeblock($coldata,$framename);
				}
			}
			$_G['curtplframe'][$name] = array('type'=>$flag,'name'=>$name);
		} elseif ($flag == 'block') {
			$battr = $content['attr'];
			$bid = intval(str_replace('portal_block_', '', $battr['name']));
			if (!empty($bid)) {
				$_G['curtplbid'][$bid] = $bid;
			}
		}
	}
}

function getcssdata($css) {
	global $_G;
	if (empty($css)) return '';
	$reglist = array();
	foreach ((array)$_G['curtplframe'] as $value) {
		$reglist[] = '#'.$value['name'].'.*?\{.*?\}';
	}
	foreach ((array)$_G['curtplbid'] as $value) {
		$reglist[] = '#portal_block_'.$value.'.*?\{.*?\}';
	}
	$reg = implode('|',$reglist);
	preg_match_all('/'.$reg.'/',$css,$csslist);
	return implode('', $csslist[0]);
}

function import_diy($file) {
	global $_G;

	$css = '';
	$html = array();
	$arr = array();

	$content = file_get_contents($file);
	require_once libfile('class/xml');
	if (empty($content)) return $arr;
	if(fileext($file) == 'php') {
		$content = preg_replace("/^\<\?php(.+?)\?\>\s+/i", '', $content);
	}
	$content = preg_replace("/\<\!\-\-\[name\](.+?)\[\/name\]\-\-\>\s+/i", '', $content);
	$diycontent = xml2array($content);

	if ($diycontent) {

		foreach ($diycontent['layoutdata'] as $key => $value) {
			if (!empty($value)) getframeblock($value);
		}
		$newframe = array();
		foreach ($_G['curtplframe'] as $value) {
			$newframe[] = $value['type'].random(6);
		}

		$mapping = array();
		if (!empty($diycontent['blockdata'])) {
			$mapping = block_import($diycontent['blockdata']);
			unset($diycontent['blockdata']);
		}

		$oldbids = $newbids = array();
		if (!empty($mapping)) {
			foreach($mapping as $obid=>$nbid) {
				$oldbids[] = '#portal_block_'.$obid.' ';
				$newbids[] = '#portal_block_'.$nbid.' ';
				$oldbids[] = '[portal_block_'.$obid.']';
				$newbids[] = '[portal_block_'.$nbid.']';
				$oldbids[] = '~portal_block_'.$obid.'"';
				$newbids[] = '~portal_block_'.$nbid.'"';
			}
		}

		require_once libfile('class/xml');
		$xml = array2xml($diycontent['layoutdata'],true);
		$xml = str_replace($oldbids, $newbids, $xml);
		$xml = str_replace((array)array_keys($_G['curtplframe']), $newframe, $xml);
		$diycontent['layoutdata'] = xml2array($xml);

		$css = str_replace($oldbids, $newbids, $diycontent['spacecss']);
		$css = str_replace((array)array_keys($_G['curtplframe']), $newframe, $css);
		foreach ($diycontent['layoutdata'] as $key => $value) {
			$html[$key] = getframehtml($value);
		}
	}
	if (!empty($html)) {
		$xml = array2xml($html, true);
		require_once libfile('function/block');
		block_get_batch(implode(',', $mapping));
		foreach ($mapping as $bid) {
			$blocktag[] = '<!--{block/'.$bid.'}-->';
			$blockcontent[] = block_fetch_content($bid);
		}
		$xml = str_replace($blocktag,$blockcontent,$xml);
		$html = xml2array($xml);
		$arr = array('html'=>$html,'css'=>$css,'mapping'=>$mapping);
	}
	return $arr;
}

function checkprimaltpl($template) {
	global $_G;
	$tpldirectory = '';
	if(strpos($template, ':') !== false) {
		list($tpldirectory, $template) = explode(':', $template);
	}
	if(!$template || preg_match("/(\.)(exe|jsp|asp|aspx|cgi|fcgi|pl)(\.|$)/i", $template)) {
		return 'diy_template_filename_invalid';
	}
	if(strpos($template, '..') !== false || strpos($template, "\0") !== false) {
		return 'diy_template_filename_invalid';
	}
	$tpldirectoryarr = explode('/', trim($tpldirectory, './'));
	if(strpos($tpldirectory, '..') !== false || strpos($tpldirectory, "\0") !== false || ($tpldirectoryarr[0] != 'template' && $tpldirectoryarr[0] != 'source')) {
		return 'diy_tpldirectory_invalid';
	}
	$primaltplname = (!$tpldirectory ? DISCUZ_ROOT.$_G['cache']['style_default']['tpldir'] : $tpldirectory).'/'.$template.'.htm';
	if (!file_exists($primaltplname)) {
		$primaltplname = DISCUZ_ROOT.'./template/default/'.$template.'.htm';
	}
	$pathinfos = pathinfo($primaltplname);
	if(strtolower($pathinfos['extension']) != 'htm') {
		return 'diy_template_extension_invalid';
	}
	if (!is_file($primaltplname)) {
		return 'diy_template_noexist';
	}
	return true;
}

function article_tagnames() {
	global $_G;
	if(!isset($_G['article_tagnames'])) {
		$_G['article_tagnames'] = array();
		for($i=1; $i<=8; $i++) {
			if(isset($_G['setting']['article_tags']) && isset($_G['setting']['article_tags'][$i])) {
				$_G['article_tagnames'][$i] = $_G['setting']['article_tags'][$i];
			} else {
				$_G['article_tagnames'][$i] = lang('portalcp', 'article_tag').$i;
			}
		}
	}
	return $_G['article_tagnames'];
}

function article_parse_tags($tag) {
	$tag = intval($tag);
	$article_tags = array();
	for($i=1; $i<=8; $i++) {
		$k = pow(2, $i-1);
		$article_tags[$i] = ($tag & $k) ? 1 : 0;
	}
	return $article_tags;
}

function article_make_tag($tags) {
	$tags = (array)$tags;
	$tag = 0;
	for($i=1; $i<=8; $i++) {
		if(!empty($tags[$i])) {
			$tag += pow(2, $i-1);
		}
	}
	return $tag;
}

function category_showselect($type, $name='catid', $shownull=true, $current='') {
	global $_G;
	if(! in_array($type, array('portal', 'blog', 'album'))) {
		return '';
	}
	loadcache($type.'category');
	$category = $_G['cache'][$type.'category'];

	$select = "<select id=\"$name\" name=\"$name\" class=\"ps vm\">";
	if($shownull) {
		$select .= '<option value="">'.lang('portalcp', 'select_category').'</option>';
	}
	foreach ($category as $value) {
		if($value['level'] == 0) {
			$selected = ($current && $current==$value['catid']) ? 'selected="selected"' : '';
			$select .= "<option value=\"$value[catid]\"$selected>$value[catname]</option>";
			if(!$value['children']) {
				continue;
			}
			foreach ($value['children'] as $catid) {
				$selected = ($current && $current==$catid) ? 'selected="selected"' : '';
				$select .= "<option value=\"{$category[$catid][catid]}\"$selected>-- {$category[$catid][catname]}</option>";
				if($category[$catid]['children']) {
					foreach ($category[$catid]['children'] as $catid2) {
						$selected = ($current && $current==$catid2) ? 'selected="selected"' : '';
						$select .= "<option value=\"{$category[$catid2][catid]}\"$selected>---- {$category[$catid2][catname]}</option>";
					}
				}
			}
		}
	}
	$select .= "</select>";
	return $select;
}

function category_get_childids($type, $catid, $depth=3) {
	global $_G;
	if(! in_array($type, array('portal', 'blog', 'album'))) {
		return array();
	}
	loadcache($type.'category');
	$category = $_G['cache'][$type.'category'];
	$catids = array();
	if(isset($category[$catid]) && !empty($category[$catid]['children']) && $depth) {
		$catids = $category[$catid]['children'];
		foreach($category[$catid]['children'] as $id) {
			$catids = array_merge($catids, category_get_childids($type, $id, $depth-1));
		}
	}
	return $catids;
}

function category_get_num($type, $catid) {
	global $_G;
	if(! in_array($type, array('portal', 'blog', 'album'))) {
		return array();
	}
	loadcache($type.'category');
	$category = $_G['cache'][$type.'category'];

	$numkey = $type == 'portal' ? 'articles' : 'num';
	if(! isset($_G[$type.'category_nums'])) {
		$_G[$type.'category_nums'] = array();
		$tables = array('portal'=>'portal_category', 'blog'=>'home_blog_category', 'album'=>'home_album_category');
		$query = C::t($tables[$type])->fetch_all_numkey($numkey);
		foreach ($query as $value) {
			$_G[$type.'category_nums'][$value['catid']] = intval($value[$numkey]);
		}
	}

	$nums = $_G[$type.'category_nums'];
	$num = intval($nums[$catid]);
	if($category[$catid]['children']) {
		foreach($category[$catid]['children'] as $id) {
			$num += category_get_num($type, $id);
		}
	}
	return $num;
}


function updatetopic($topic = ''){
	global $_G;

	$topicid = empty($topic) ? '' : $topic['topicid'];
	include_once libfile('function/home');
	$_POST['title'] = getstr(trim($_POST['title']), 255);
	$_POST['name'] = getstr(trim($_POST['name']), 255);
	$_POST['domain'] = getstr(trim($_POST['domain']), 255);
	if(empty($_POST['title'])) {
		return 'topic_title_cannot_be_empty';
	}
	if(empty($_POST['name'])) {
		$_POST['name'] = $_POST['title'];
	}
	if(!preg_match('/^[\w\_\.]+$/i', $_POST['name'])) {
		return 'topic_created_failed';
	}
	if(!$topicid || $_POST['name'] != $topic['name']) {
		if(($value = C::t('portal_topic')->fetch_by_name($_POST['name']))) {
			return 'topic_name_duplicated';
		}
	}
	if($topicid && !empty($topic['domain'])) {
		require_once libfile('function/delete');
		deletedomain($topicid, 'topic');
	}
	if(!empty($_POST['domain'])) {
		require_once libfile('function/domain');
		domaincheck($_POST['domain'], $_G['setting']['domain']['root']['topic'], 1);
	}

	$setarr = array(
		'title' => $_POST['title'],
		'name' => $_POST['name'],
		'domain' => $_POST['domain'],
		'summary' => getstr($_POST['summary']),
		'keyword' => getstr($_POST['keyword']),
		'useheader' => $_POST['useheader'] ? '1' : '0',
		'usefooter' => $_POST['usefooter'] ? '1' : '0',
		'allowcomment' => $_POST['allowcomment'] ? 1 : 0,
		'closed' => $_POST['closed'] ? 0 : 1,
	);

	if($_POST['deletecover'] && $topic['cover']) {
		if($topic['picflag'] != '0') pic_delete(str_replace('portal/', '', $topic['cover']), 'portal', 0, $topic['picflag'] == '2' ? '1' : '0');
		$setarr['cover'] = '';
	} else {
		if($_FILES['cover']['tmp_name']) {
			if($topic['cover'] && $topic['picflag'] != '0') pic_delete(str_replace('portal/', '', $topic['cover']), 'portal', 0, $topic['picflag'] == '2' ? '1' : '0');
			$pic = pic_upload($_FILES['cover'], 'portal');
			if($pic) {
				$setarr['cover'] = 'portal/'.$pic['pic'];
				$setarr['picflag'] = $pic['remote'] ? '2' : '1';
			}
		} else {
			if(!empty($_POST['cover']) && $_POST['cover'] != $topic['cover']) {
				if($topic['cover'] && $topic['picflag'] != '0') pic_delete(str_replace('portal/', '', $topic['cover']), 'portal', 0, $topic['picflag'] == '2' ? '1' : '0');
				$setarr['cover'] = $_POST['cover'];
				$setarr['picflag'] = '0';
			}
		}
	}


	$primaltplname = '';
	if(empty($topicid) || empty($topic['primaltplname']) || ($topic['primaltplname'] && $topic['primaltplname'] != $_POST['primaltplname'])) {
		$primaltplname = $_POST['primaltplname'];
		if(!isset($_POST['signs'][dsign($primaltplname)])) {
			return 'diy_sign_invalid';
		}
		$checktpl = checkprimaltpl($primaltplname);
		if($checktpl !== true) {
			return $checktpl;
		}
		$setarr['primaltplname'] = $primaltplname;
	}

	if($topicid) {
		C::t('portal_topic')->update($topicid, $setarr);
		C::t('common_diy_data')->update('portal/portal_topic_content_'.$topicid, getdiydirectory($topic['primaltplname']), array('name'=>$setarr['title']));
	} else {
		$setarr['uid'] = $_G['uid'];
		$setarr['username'] = $_G['username'];
		$setarr['dateline'] = $_G['timestamp'];
		$setarr['closed'] = '1';
		$topicid = addtopic($setarr);
		if(!$topicid) {
			return 'topic_created_failed';
		}

	}

	if(!empty($_POST['domain'])) {
		C::t('common_domain')->insert(array('domain' => $_POST['domain'], 'domainroot' => $_G['setting']['domain']['root']['topic'], 'id' => $topicid, 'idtype' => 'topic'));
	}

	$tpldirectory = '';
	if($primaltplname && $topic['primaltplname'] != $primaltplname) {
		$targettplname = 'portal/portal_topic_content_'.$topicid;
		if(strpos($primaltplname, ':') !== false) {
			list($tpldirectory, $primaltplname) = explode(':', $primaltplname);
		}
		C::t('common_diy_data')->update($targettplname, getdiydirectory($topic['primaltplname']), array('primaltplname'=>$primaltplname, 'tpldirectory'=>$tpldirectory));
		updatediytemplate($targettplname);
	}

	if($primaltplname && empty($topic['primaltplname'])) {
		$tpldirectory = ($tpldirectory ? $tpldirectory : $_G['cache']['style_default']['tpldir']);
		$content = file_get_contents(DISCUZ_ROOT.$tpldirectory.'/'.$primaltplname.'.htm');
		$tplfile = DISCUZ_ROOT.'./data/diy/'.$tpldirectory.'/portal/portal_topic_content_'.$topicid.'.htm';
		$tplpath = dirname($tplfile);
		if (!is_dir($tplpath)) {
			dmkdir($tplpath);
		}
		file_put_contents($tplfile, $content);
	}

	include_once libfile('function/cache');
	updatecache(array('diytemplatename', 'setting'));

	return $topicid;
}

function addtopic($topic) {
	global $_G;
	$topicid = '';
	if($topic && is_array($topic)) {
		$topicid = C::t('portal_topic')->insert($topic, true);
		if(!empty($topicid)) {
			$diydata = array(
				'targettplname' => 'portal/portal_topic_content_'.$topicid,
				'name' => $topic['title'],
				'uid' => $_G['uid'],
				'username' => $_G['username'],
				'dateline' => TIMESTAMP,
			);
			C::t('common_diy_data')->insert($diydata);
		}
	}
	return $topicid;
}

function getblockperm($bid) {
	global $_G;
	$perm = array('allowmanage'=>'0','allowrecommend'=>'0','needverify'=>'1');
	$bid = max(0, intval($bid));
	if(!$bid) return $perm;
	$allperm = array('allowmanage'=>'1','allowrecommend'=>'1','needverify'=>'0');
	if(checkperm('allowdiy')) {
		return $allperm;
	} elseif (!getstatus($_G['member']['allowadmincp'], 4) && !getstatus($_G['member']['allowadmincp'], 5) && !getstatus($_G['member']['allowadmincp'], 6) && !checkperm('allowmanagetopic') && !checkperm('allowaddtopic')) {
		return $perm;
	}
	require_once libfile('class/blockpermission');
	$blockpermsission = & block_permission::instance();
	$perm = $blockpermsission->get_perms_by_bid($bid, $_G['uid']);
	$perm = $perm ? current($perm) : '';
	if(empty($perm)) {
		if(($block = C::t('common_block')->fetch($bid))) {
			$block = array_merge($block, C::t('common_template_block')->fetch_by_bid($bid));
		}
		if(empty($block['targettplname']) && empty($block['blocktype'])) {
			if(($_G['group']['allowmanagetopic'] || ($_G['group']['allowaddtopic'] && $block['uid'] == $_G['uid']))) {
				$perm = $allperm;
			}
		} elseif(substr($block['targettplname'], 0, 28) == 'portal/portal_topic_content_') {
			if(!empty($_G['group']['allowmanagetopic'])) {
				$perm = $allperm;
			} elseif($_G['group']['allowaddtopic']) {
				$id = str_replace('portal/portal_topic_content_', '', $block['targettplname']);
				$topic = C::t('portal_topic')->fetch(intval($id));
				if($topic['uid'] == $_G['uid']) {
					$perm = $allperm;
				}
			}
		}
	}
	return $perm;
}

function check_articleperm($catid, $aid = 0, $article = array(), $isverify = false, $return = false) {
	global $_G;

	if(empty($catid)) {
		if(!$return) {
			showmessage('article_category_empty');
		} else {
			return 'article_category_empty';
		}
	}

	if($_G['group']['allowmanagearticle'] || (empty($aid) && $_G['group']['allowpostarticle'])) {
		return true;
	}

	$permission = getallowcategory($_G['uid']);
	if(isset($permission[$catid])) {
		if($permission[$catid]['allowmanage'] || (empty($aid) && $permission[$catid]['allowpublish'])) {
			return true;
		}
	}
	if(!$isverify && $aid && !empty($article['uid']) && $article['uid'] == $_G['uid'] && ($article['status'] == 1 && $_G['group']['allowpostarticlemod'] || empty($_G['group']['allowpostarticlemod']))) {
		return true;
	}

	if(!$return) {
		showmessage('article_edit_nopermission');
	} else {
		return 'article_edit_nopermission';
	}
}

function addportalarticlecomment($id, $message, $idtype = 'aid') {
	global $_G;

	$id = intval($id);
	if(empty($id)) {
		return 'comment_comment_noexist';
	}
	$message = getstr($message, $_G['group']['allowcommentarticle'], 0, 0, 1, 0);
	if(strlen($message) < 2) return 'content_is_too_short';

	$idtype = in_array($idtype, array('aid' ,'topicid')) ? $idtype : 'aid';
	$tablename = $idtype == 'aid' ? 'portal_article_title' : 'portal_topic';
	$data = C::t($tablename)->fetch($id);
	if(empty($data)) {
		return 'comment_comment_noexist';
	}
	if($data['allowcomment'] != 1) {
		return 'comment_comment_notallowed';
	}

	$message = censor($message, NULL, FALSE, FALSE);
	if(censormod($message)) {
		$comment_status = 1;
	} else {
		$comment_status = 0;
	}

	$setarr = array(
		'uid' => $_G['uid'],
		'username' => $_G['username'],
		'id' => $id,
		'idtype' => $idtype,
		'postip' => $_G['clientip'],
		'port' => $_G['remoteport'],
		'dateline' => $_G['timestamp'],
		'status' => $comment_status,
		'message' => $message
	);

	$pcid = C::t('portal_comment')->insert($setarr, true);

	if($comment_status == 1) {
		updatemoderate($idtype.'_cid', $pcid);
		$notifykey = $idtype == 'aid' ? 'verifyacommont' : 'verifytopiccommont';
		manage_addnotify($notifykey);
	}
	$tablename = $idtype == 'aid' ? 'portal_article_count' : 'portal_topic';
	C::t($tablename)->increase($id, array('commentnum' => 1));
	C::t('common_member_status')->update($_G['uid'], array('lastpost' => $_G['timestamp']), 'UNBUFFERED');

	if($data['uid'] != $_G['uid']) {
		updatecreditbyaction('portalcomment', 0, array(), $idtype.$id);
	}
	return 'do_success';
}

function trimdxtpllang($s){
	return str_replace(array('{', '$', '<', '>'), array('{ ', '$ ', '', ''), $s);
}

function addrelatedarticle($aid, $raids) {
	C::t('portal_article_related')->delete_by_aid_raid($aid, $aid);
	if($raids) {
		$relatedarr = array();
		$relatedarr = array_map('intval', $raids);
		$relatedarr = array_unique($relatedarr);
		$relatedarr = array_filter($relatedarr);
		if($relatedarr) {
			$list = C::t('portal_article_title')->fetch_all($relatedarr);
			C::t('portal_article_related')->insert_batch($aid, $list);
		}
	}
	return true;
}


function getprimaltplname($filename) {
	global $_G, $lang;
	$tpldirectory = '';
	if(strpos($filename, ':') !== false) {
		list($tpldirectory, $filename) = explode(':', $filename);
	}
	if(empty($tpldirectory)) {
		$tpldirectory = ($_G['cache']['style_default']['tpldir'] ? $_G['cache']['style_default']['tpldir'] : './template/default');
	}
	$content = @file_get_contents(DISCUZ_ROOT.$tpldirectory.'/'.$filename);
	$name = $tpldirectory.'/'.$filename;
	if($content) {
		preg_match("/\<\!\-\-\[name\](.+?)\[\/name\]\-\-\>/i", trim($content), $mathes);
		if(!empty($mathes[1])) {
			preg_match("/^\{lang (.+?)\}$/", $mathes[1], $langs);
			if(!empty($langs[1])) {
				$name = !$lang[$langs[1]] ? $langs[1] : $lang[$langs[1]];
			} else {
				$name = dhtmlspecialchars($mathes[1]);
			}
		}
	}
	return $name;
}

function getdiydirectory($value) {
	$directory = '';
	if($value && strpos($value, ':') !== false) {
		list($directory) = explode(':', $value);
	}
	return $directory;
}
?>