<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_threadsort.php 36284 2016-12-12 00:47:50Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function gettypetemplate($option, $optionvalue, $optionid) {
	global $_G;

	if(in_array($option['type'], array('number', 'text', 'email', 'calendar', 'image', 'url', 'range', 'upload', 'range'))) {
		if($option['type'] == 'calendar') {
			$showoption[$option['identifier']]['value'] = '<script type="text/javascript" src="'.$_G['setting']['jspath'].'calendar.js?'.VERHASH.'"></script><input type="text" name="typeoption['.$option['identifier'].']" tabindex="1" id="typeoption_'.$option['identifier'].'" style="width:'.$option['inputsize'].'px;" onchange="checkoption(\''.$option['identifier'].'\', \''.$option['required'].'\', \''.$option['type'].'\')" value="'.$optionvalue['value'].'" onclick="showcalendar(event, this, false)" '.$optionvalue['unchangeable'].' class="px"/>';
		} elseif($option['type'] == 'image') {
			$showoption[$option['identifier']]['value'] = '<button type="button" class="pn" onclick="uploadWindow(function (aid, url){updatesortattach(aid, url, \''.$_G['setting']['attachurl'].'forum\', \''.$option['identifier'].'\')})"><span>'.($optionvalue['value'] ? lang('forum/misc', 'sort_update') : lang('forum/misc', 'sort_upload')).'</span></button>
				<input type="hidden" name="typeoption['.$option['identifier'].'][aid]" id="sortaid_'.$option['identifier'].'" value="'.$optionvalue['value']['aid'].'" tabindex="1" />'.
				($optionvalue['value']['aid'] ? '<input type="hidden" name="oldsortaid['.$option['identifier'].']" value="'.$optionvalue['value']['aid'].'" tabindex="1" />' : '').
				'<input type="hidden" name="typeoption['.$option['identifier'].'][url]" id="sortattachurl_'.$option['identifier'].'" '.($optionvalue['value']['url'] ? 'value="'.$optionvalue['value']['url'].'"' : '').'tabindex="1" />
				<div id="sortattach_image_'.$option['identifier'].'" class="ptn">';

			if($optionvalue['value']['url']) {
				$showoption[$option['identifier']]['value'] .= '<a href="'.$optionvalue['value']['url'].'" target="_blank"><img class="spimg" src="'.$optionvalue['value']['url'].'" alt="" /></a>';
			}

			$showoption[$option['identifier']]['value'] .= '</div>';

		} else {
			$showoption[$option['identifier']]['value'] = '<input type="text" name="typeoption['.$option['identifier'].']" id="typeoption_'.$option['identifier'].'" class="px" tabindex="1" style="width:'.$option['inputsize'].'px;" onBlur="checkoption(\''.$option['identifier'].'\', \''.$option['required'].'\', \''.$option['type'].'\', \''.intval($option['maxnum']).'\', \''.intval($option['minnum']).'\', \''.intval($option['maxlength']).'\')" value="'.($optionvalue['value'] ? $optionvalue['value'] : $option['defaultvalue']).'" '.$optionvalue['unchangeable'].' />';
		}
	} elseif(in_array($option['type'], array('radio', 'checkbox', 'select'))) {
		if($option['type'] == 'select') {
			if(empty($optionvalue['value'])) {
				$showoption[$option['identifier']]['value'] = '<span id="select_'.$option['identifier'].'"><select tabindex="1" onchange="changeselectthreadsort(this.value, \''.$optionid.'\');checkoption(\''.$option['identifier'].'\', \''.$option['required'].'\', \''.$option['type'].'\')" '.$optionvalue['unchangeable'].' class="ps">';
				$showoption[$option['identifier']]['value'] .= '<option value="0">'.lang('forum/template', 'please_select').'</option>';
				foreach($option['choices'] as $id => $value) {
					if(!$value['foptionid']) {
						$showoption[$option['identifier']]['value'] .= '<option value="'.$id.'">'.$value['content'].' '.(($value['level'] == 1) ? '' : '>').'</option>';
					}
				}
				$showoption[$option['identifier']]['value'] .= '</select></span>';
			} else {
				foreach($optionvalue['value'] as $selectedkey => $selectedvalue) {
					$showoption[$option['identifier']]['value'] = '<span id="select_'.$option['identifier'].'"><script type="text/javascript">changeselectthreadsort(\''.$selectedkey.'\', '.$optionid.');</script></span>';
				}
			}
		} elseif($option['type'] == 'radio') {
			foreach($option['choices'] as $id => $value) {
				$showoption[$option['identifier']]['value'] .= '<span class="fb"><input type="radio" name="typeoption['.$option['identifier'].']" id="typeoption_'.$option['identifier'].'" class="pr" value="'.$id.'" tabindex="1" onclick="checkoption(\''.$option['identifier'].'\', \''.$option['required'].'\', \''.$option['type'].'\')" '.$optionvalue['value'][$id].' '.$optionvalue['unchangeable'].' />'.$value.'</span>';
			}
		} elseif($option['type'] == 'checkbox') {
			foreach($option['choices'] as $id => $value) {
				$showoption[$option['identifier']]['value'] .= '<span class="fb"><input type="checkbox" name="typeoption['.$option['identifier'].'][]" id="typeoption_'.$option['identifier'].'" class="pc" tabindex="1" value="'.$id.'" onclick="checkoption(\''.$option['identifier'].'\', \''.$option['required'].'\', \''.$option['type'].'\')" '.$optionvalue['value'][$id][$id].' '.$optionvalue['unchangeable'].' />'.$value.'</span>';
			}
		}
	} elseif(in_array($option['type'], array('textarea'))) {
		$showoption[$option['identifier']]['value'] = '<span><textarea name="typeoption['.$option['identifier'].']" id="typeoption_'.$option['identifier'].'" class="pt" tabindex="1" rows="'.$option['rowsize'].'" cols="'.$option['colsize'].'" onBlur="checkoption(\''.$option['identifier'].'\', \''.$option['required'].'\', \''.$option['type'].'\', 0, 0'.($option['maxlength'] ? ', \'$option[maxlength]\'' : '').'" '.$optionvalue['unchangeable'].'>'.$optionvalue['value'].'</textarea><span>';
	}

	return $showoption;

}

function quicksearch($sortoptionarray) {
	global $_G;

	$quicksearch = array();
	if($sortoptionarray) {
		foreach($sortoptionarray as $optionid => $option) {
			if($option['search']) {
				$quicksearch[$optionid]['title'] = $option['title'];
				$quicksearch[$optionid]['identifier'] = $option['identifier'];
				$quicksearch[$optionid]['unit'] = $option['unit'];
				$quicksearch[$optionid]['type'] = $option['type'];
				$quicksearch[$optionid]['search'] = $option['search'];
				if(in_array($option['type'], array('radio', 'select', 'checkbox'))) {
					$quicksearch[$optionid]['choices'] = $option['choices'];
				} elseif(!empty($option['searchtxt'])) {
					$choices = array();
					$prevs = 'd';
					foreach($option['searchtxt'] as $choice) {
						$value = "$prevs|$choice";
						if($choice) {
							$quicksearch[$optionid]['choices'][$value] = $prevs == 'd' ? lang('forum/misc', 'lower').$choice.$option['unit'] : $prevs.'-'.$choice.$option['unit'];
							$prevs = $choice;
						}
						$max = $choice;
					}
					$value = "u|$choice";
					$quicksearch[$optionid]['choices'][$value] .= lang('forum/misc', 'higher').$max.$option['unit'];
				}
			}
		}
	}

	return $quicksearch;
}

function sortsearch($sortid, $sortoptionarray, $searchoption = array(), $selecturladd = array(), $sortfid = 0) {
	global $_G;
	$sortid = intval($sortid);
	$selectsql = '';
	$optionide = $searchsorttids = array();

	if($selecturladd) {
		foreach($sortoptionarray[$sortid] as $optionid => $option) {
			if(in_array($option['type'], array('checkbox', 'radio', 'select', 'range'))) {
				$optionide[$option['identifier']] = $option['type'];
			}
		}

		foreach($selecturladd as $fieldname => $value) {
			if($optionide[$fieldname] && $value != 'all') {
				if($optionide[$fieldname] == 'range') {
					$value = explode('|', $value);
					if($value[0] == 'd') {
						$sql = "$fieldname<".intval($value[1]);
					} elseif($value[0] == 'u') {
						$sql = "$fieldname>".intval($value[1]);
					} else {
						$sql = "($fieldname BETWEEN ".intval($value[0])." AND ".intval($value[1]).")";
					}
				} elseif($optionide[$fieldname] == 'checkbox') {
					$sql = '('.DB::field($fieldname, $value).
						' OR '.DB::field($fieldname, "$value\t%", 'like').
						' OR '.DB::field($fieldname, "%\t$value", 'like').
						' OR '.DB::field($fieldname, "%\t$value\t%", 'like').')';
				} elseif($optionide[$fieldname] == 'select') {
					$subvalues = $currentchoices = array();
					if(!empty($_G['forum_optionlist'])) {
						foreach($_G['forum_optionlist'] as $subkey => $subvalue) {
							if($subvalue['identifier'] == $fieldname) {
								$currentchoices = $subvalue['choices'];
								break;
							}
						}
					}
					if(!empty($currentchoices)) {
						foreach($currentchoices as $subkey => $subvalue) {
							if(preg_match('/^'.$value.'\.'.'/i', $subkey) || preg_match('/^'.$value.'$'.'/i', $subkey)) {
								$subvalues[] = $subkey;
							}
						}
					}
					$sql = DB::field($fieldname, $subvalues);
				} else {
					$sql = DB::field($fieldname, $value);
				}
				$selectsql .= "AND $sql ";
			}
		}
	}

	if(!empty($searchoption) && is_array($searchoption)) {
		foreach($searchoption as $optionid => $option) {
			$fieldname = $sortoptionarray[$sortid][$optionid]['identifier'] ? $sortoptionarray[$sortid][$optionid]['identifier'] : 1;
			if($option['value']) {
				if(in_array($option['type'], array('number', 'radio'))) {
					$option['value'] = intval($option['value']);
					$exp = '=';
					if($option['condition']) {
						$exp = $option['condition'] == 1 ? '>' : '<';
					}
					$sql = DB::field($fieldname, $option['value'], $exp);
				} elseif($option['type'] == 'select') {
					$subvalues = $currentchoices = array();
					if(!empty($_G['forum_optionlist'])) {
						foreach($_G['forum_optionlist'] as $subkey => $subvalue) {
							if($subvalue['identifier'] == $fieldname) {
								$currentchoices = $subvalue['choices'];
								break;
							}
						}
					}
					if(!empty($currentchoices)) {
						foreach($currentchoices as $subkey => $subvalue) {
							if(preg_match('/^'.$option['value'].'/i', $subkey)) {
								$subvalues[] = $subkey;
							}
						}
					}
					$sql = DB::field($fieldname, $subvalues);
				} elseif($option['type'] == 'checkbox') {
					$sql = DB::field($fieldname, '%'.implode('%', $option['value']).'%', 'like');
				} elseif($option['type'] == 'range') {
					$value = explode('|', $option['value']);
					if($value[0] == 'd') {
						$sql = "$fieldname<".intval($value[1]);
					} elseif($value[0] == 'u') {
						$sql = "$fieldname>".intval($value[1]);
					} else {
						$sql = $value[0] || $value[1] ? "($fieldname BETWEEN ".intval($value[0])." AND ".intval($value[1]).")" : '';
					}
				} else {
					$sql = DB::field($fieldname, '%'.$option['value'].'%', 'like');
				}
				$selectsql .= "AND $sql ";
			}
		}
	}

	$searchsorttids = C::t('forum_optionvalue')->fetch_all_tid($sortid, "WHERE 1 $selectsql ".($sortfid ? "AND fid='$sortfid'" : ''));

	return $searchsorttids;

}

function showsorttemplate($sortid, $fid, $sortoptionarray, $templatearray, $threadlist, $threadids = array(), $sortmode = false) {
	global $_G;

	$searchtitle = $searchvalue = $searchunit = $stemplate = $searchtids = $sortlistarray = $skipaids = $sortdata = array();

	$sortthreadlist = array();
	foreach(C::t('forum_typeoptionvar')->fetch_all_by_search($sortid, $fid, $threadids) as $sortthread) {
		$optionid = $sortthread['optionid'];
		$sortid = $sortthread['sortid'];
		$tid = $sortthread['tid'];
		$arrayoption = $sortoptionarray[$sortid][$optionid];
		if($sortoptionarray[$sortid][$optionid]['subjectshow']) {
			$_G['optionvaluelist'][$sortid][$tid][$arrayoption['identifier']]['title'] = $arrayoption['title'];
			$_G['optionvaluelist'][$sortid][$tid][$arrayoption['identifier']]['unit'] = $arrayoption['unit'];
			if(in_array($arrayoption['type'], array('radio', 'checkbox', 'select'))) {
				if($arrayoption['type'] == 'checkbox') {
					foreach(explode("\t", $sortthread['value']) as $choiceid) {
						$sortthreadlist[$tid][$arrayoption['title']] .= $arrayoption['choices'][$choiceid].'&nbsp;';
						$_G['optionvaluelist'][$sortid][$tid][$arrayoption['identifier']]['value'] .= $arrayoption['choices'][$choiceid].'&nbsp;';
					}
				} elseif($arrayoption['type'] == 'select') {
					$sortthreadlist[$tid][$arrayoption['title']] = $_G['optionvaluelist'][$sortid][$tid][$arrayoption['identifier']]['value'] = $arrayoption['choices'][$sortthread['value']]['content'];
				} else {
					$sortthreadlist[$tid][$arrayoption['title']] = $_G['optionvaluelist'][$sortid][$tid][$arrayoption['identifier']]['value'] = $arrayoption['choices'][$sortthread['value']];
				}
			} elseif($arrayoption['type'] == 'image') {
				$imgoptiondata = dunserialize($sortthread['value']);
				if(empty($templatearray[$sortid])) {
					$maxwidth = $arrayoption['maxwidth'] ? 'width="'.$arrayoption['maxwidth'].'"' : '';
					$maxheight = $arrayoption['maxheight'] ? 'height="'.$arrayoption['maxheight'].'"' : '';
					$sortthreadlist[$tid][$arrayoption['title']] = $_G['optionvaluelist'][$sortid][$tid][$arrayoption['identifier']]['value'] = $imgoptiondata['url'] ? "<img src=\"$imgoptiondata[url]\" onload=\"thumbImg(this)\" $maxwidth $maxheight border=\"0\">" : '';
				} else {
					$sortthread['value'] = '';
					if($imgoptiondata['aid']) {
						$sortthread['value'] = getforumimg($imgoptiondata['aid'], 0, 300, 300);
					} elseif($imgoptiondata['url']) {
						$sortthread['value'] = $imgoptiondata['url'];
					}
					$sortthreadlist[$tid][$arrayoption['title']] = $_G['optionvaluelist'][$sortid][$tid][$arrayoption['identifier']]['value'] = $sortthread['value'] ? $sortthread['value'] : './static/image/common/nophotosmall.gif';
				}
			} else {
				$sortthreadlist[$tid][$arrayoption['title']] = $_G['optionvaluelist'][$sortid][$tid][$arrayoption['identifier']]['value'] = $sortthread['value'] ? $sortthread['value'] : $arrayoption['defaultvalue'];
			}
			$sortthreadlist[$tid]['sortid'] = $sortid;
			$sortthreadlist[$tid]['expiration'] = $sortthread['expiration'] && $sortthread['expiration'] <= TIMESTAMP ? 1 : 0;
		}
	}

	if($templatearray && $sortthreadlist) {
		foreach($threadlist as $thread) {
			$thread['digest'] = $thread['digest'] ? '&nbsp;<img src="'.$_G['style']['imgdir'].'/digest_'.$thread['digest'].'.gif" class="vm" alt="" title="" />' : '';
			if($thread['highlight']) {
				$thread['subject'] = '<span '.$thread['highlight'].'>'.$thread['subject'].'</span>';
			}
			if($thread['digest']) {
				$thread['subject'] .= ' '.$thread['digest'];
			}
			$sortdata[$thread['tid']]['subject'] = !$sortmode ? '<a href="forum.php?mod=viewthread&tid='.$thread['tid'].'">'.$thread['subject'].'</a>' : $thread['subject'];
			$sortdata[$thread['tid']]['author'] = '<a href="home.php?mod=space&uid='.$thread['authorid'].'" target="_blank">'.$thread['author'].'</a>';
		}

		foreach($sortoptionarray as $sortid => $optionarray) {
			foreach($optionarray as $option) {
				if($option['subjectshow']) {
					$searchtitle[$sortid][] = '/{('.$option['identifier'].')}/';
					$searchvalue[$sortid][] = '/\[('.$option['identifier'].')value\]/';
					$searchvalue[$sortid][] = '/{('.$option['identifier'].')_value}/';
					$searchunit[$sortid][] = '/\[('.$option['identifier'].')unit\]/';
					$searchunit[$sortid][] = '/{('.$option['identifier'].')_unit}/';
				}
			}
		}

		foreach($sortthreadlist as $tid => $option) {
			$sortid = $option['sortid'];
			$sortexpiration[$sortid][$tid] = $option['expiration'];
			$stemplate[$sortid][$tid] = preg_replace(
							array("/\{sortname\}/i", "/\{author\}/i", "/\{subject\}/i", "/\[url\](.+?)\[\/url\]/i"),
							array(
								'<a href="forum.php?mod=forumdisplay&fid='.$sortthreadlist[$tid]['fid'].'&filter=sortid&sortid='.$sortid.'">'.$_G['forum']['threadsorts']['types'][$sortid].'</a>',
								$sortdata[$tid]['author'],
								$sortdata[$tid]['subject'],
								"<a href=\"forum.php?mod=viewthread&tid=$tid\">\\1</a>"
							), stripslashes($templatearray[$sortid]));
			$stemplate[$sortid][$tid] = preg_replace_callback($searchtitle[$sortid], create_function('$matches', 'return showlistoption($matches[1], \'title\', '.intval($tid).', '.intval($sortid).');'), $stemplate[$sortid][$tid]);
			$stemplate[$sortid][$tid] = preg_replace_callback($searchvalue[$sortid], create_function('$matches', 'return showlistoption($matches[1], \'value\', '.intval($tid).', '.intval($sortid).');'), $stemplate[$sortid][$tid]);
			$stemplate[$sortid][$tid] = preg_replace_callback($searchunit[$sortid], create_function('$matches', 'return showlistoption($matches[1], \'unit\', '.intval($tid).', '.intval($sortid).');'), $stemplate[$sortid][$tid]);
		}
	}

	$sortlistarray['template'] = $stemplate;
	$sortlistarray['expiration'] = $sortexpiration;

	return $sortlistarray;
}

function showsortmodetemplate($sortid, $fid, $sortoptionarray, $templatearray, $threadlist, $threadids = array(), &$verify = array()) {
	global $_G;
	$sorttemplate = $replaces = array();
	$sorttemplate['footer'] = $sorttemplate['body'] = $sorttemplate['header'] = '';
	if(strexists($templatearray[$sortid], '[loop]') && strexists($templatearray[$sortid], '[/loop]')) {
		preg_match('/^(.+?)\[loop\](.+?)\[\/loop\](.+?)$/s', $templatearray[$sortid], $r);
		$sorttemplate['header'] = stripslashes($r[1]);
		$templatearray[$sortid] = stripslashes($r[2]);
		$sorttemplate['footer'] = stripslashes($r[3]);
	}
	$rewritespace = is_array($_G['setting']['rewritestatus']) && in_array('home_space', $_G['setting']['rewritestatus']);
	$rewriteviewthread = is_array($_G['setting']['rewritestatus']) && in_array('forum_viewthread', $_G['setting']['rewritestatus']);
	$sortlistarray = showsorttemplate($sortid, $fid, $sortoptionarray, $templatearray, $threadlist, $threadids, true);
	foreach($threadlist as $thread) {
		foreach($thread as $k => $v) {
			$replaces['{'.$k.'}'] = $v;
		}
		$body = $sortlistarray['template'][$sortid][$thread['tid']];
		$replaces['{author_url}'] = $rewritespace ? rewriteoutput('home_space', 1, '', $thread['authorid']) : 'home.php?mod=space&amp;uid='.$thread['authorid'];
		$replaces['{lastposter_url}'] = $rewritespace ? rewriteoutput('home_space', 1, '', '', $thread['lastposter']) : 'home.php?mod=space&amp;username='.$thread['lastposterenc'];
		$replaces['{subject_url}'] = $rewriteviewthread ? rewriteoutput('forum_viewthread', 1, '', $thread['tid']) : 'forum.php?mod=viewthread&amp;tid='.$thread['tid'];
		$replaces['{lastpost_url}'] = 'forum.php?mod=redirect&tid='.$thread['tid'].'&goto=lastpost#lastpost';
		$replaces['{lastpost_url}'] = 'forum.php?mod=redirect&tid='.$thread['tid'].'&goto=lastpost#lastpost';
		$replaces['{avatar_small}'] = avatar($thread['authorid'], 'small', true);
		$replaces['{typename_url}'] = 'forum.php?mod=forumdisplay&fid='.$fid.'&filter=typeid&typeid='.$thread['tid'];
		$replaces['{attachment}'] = ($thread['attachment'] == 2 ? '<img src="'.STATICURL.'image/filetype/image_s.gif" align="absmiddle" />' :
						($thread['attachment'] == 1 ? '<img src="'.STATICURL.'image/filetype/common.gif" align="absmiddle" />' : ''));
		$replaces['{author_verify}'] = $verify[$thread['authorid']] ? $verify[$thread['authorid']] : '';
		if($_G['forum']['ismoderator']) {
			if($thread['fid'] == $fid && $thread['displayorder'] <= 3 || $_G['adminid'] == 1) {
				$replaces['{modcheck}'] = '<input onclick="tmodclick(this)" type="checkbox" name="moderate[]" value="'.$thread['tid'].'" />';
			} else {
				$replaces['{modcheck}'] = '<input type="checkbox" disabled="disabled" />';
			}
		} else {
			$replaces['{modcheck}'] = '';
		}
		$body = str_replace(array_keys($replaces), $replaces, $body);
		$sorttemplate['body'] .= $body;
	}
	return $sorttemplate;
}

function showlistoption($var, $type, $tid, $sortid) {
	global $_G;
	if($_G['optionvaluelist'][$sortid][$tid][$var][$type]) {
		return $_G['optionvaluelist'][$sortid][$tid][$var][$type];
	} else {
		return '';
	}
}

function threadsortshow($sortid, $tid) {
	global $_G;

	loadcache(array('threadsort_option_'.$sortid, 'threadsort_template_'.$sortid));
	$sortoptionarray = $_G['cache']['threadsort_option_'.$sortid];
	$templatearray = $_G['cache']['threadsort_template_'.$sortid];
	$threadsortshow = $optiondata = $searchtitle = $searchvalue = $searchunit = $memberinfofield = $_G['forum_option'] = array();
	if($sortoptionarray) {

		foreach(C::t('forum_typeoptionvar')->fetch_all_by_tid_optionid($tid) as $option) {
			$optiondata[$option['optionid']]['value'] = $option['value'];
			$optiondata[$option['optionid']]['expiration'] = $option['expiration'] && $option['expiration'] <= TIMESTAMP ? 1 : 0;
			$sortdataexpiration = $option['expiration'];
		}

		foreach($sortoptionarray as $optionid => $option) {
			$_G['forum_option'][$option['identifier']]['title'] = $option['title'];
			$_G['forum_option'][$option['identifier']]['unit'] = $option['unit'];
			$_G['forum_option'][$option['identifier']]['type'] = $option['type'];

			if(($option['expiration'] && !$optiondata[$optionid]['expiration']) || empty($option['expiration'])) {
				if(!protectguard($option['protect'])) {
					if($option['type'] == 'checkbox') {
						$_G['forum_option'][$option['identifier']]['value'] = '';
						foreach(explode("\t", $optiondata[$optionid]['value']) as $choiceid) {
							$_G['forum_option'][$option['identifier']]['value'] .= $option['choices'][$choiceid].'&nbsp;';
						}
					} elseif($option['type'] == 'radio') {
						$_G['forum_option'][$option['identifier']]['value'] = $option['choices'][$optiondata[$optionid]['value']];
					} elseif($option['type'] == 'select') {
						$tmpchoiceid = $tmpidentifiervalue = array();
						foreach(explode('.', $optiondata[$optionid]['value']) as $choiceid) {
							$tmpchoiceid[] = $choiceid;
							$tmpidentifiervalue[] = $option['choices'][implode('.', $tmpchoiceid)];
						}
						$_G['forum_option'][$option['identifier']]['value'] = implode(' &raquo; ', $tmpidentifiervalue);
						unset($tmpchoiceid, $tmpidentifiervalue);
					} elseif($option['type'] == 'image') {
						$imgoptiondata = dunserialize($optiondata[$optionid]['value']);
						$threadsortshow['sortaids'][] = $imgoptiondata['aid'];
						if(empty($templatearray['viewthread'])) {
							$maxwidth = $option['maxwidth'] ? 'width="'.$option['maxwidth'].'"' : '';
							$maxheight = $option['maxheight'] ? 'height="'.$option['maxheight'].'"' : '';
							if(!defined('IN_MOBILE')) {
								$_G['forum_option'][$option['identifier']]['value'] = $imgoptiondata['url'] ? "<img src=\"".$imgoptiondata['url']."\" onload=\"thumbImg(this)\" $maxwidth $maxheight border=\"0\">" : '';
							} else {
								$_G['forum_option'][$option['identifier']]['value'] = $imgoptiondata['url'] ? "<a href=\"".$imgoptiondata['url']."\" target=\"_blank\">".lang('forum/misc', 'click_view')."</a>" : '';
							}
						} else {
							$_G['forum_option'][$option['identifier']]['value'] = $imgoptiondata['url'] ? $imgoptiondata['url'] : './static/image/common/nophoto.gif';
						}
					} elseif($option['type'] == 'url') {
						$_G['forum_option'][$option['identifier']]['value'] = $optiondata[$optionid]['value'] ? "<a href=\"".$optiondata[$optionid]['value']."\" target=\"_blank\">".$optiondata[$optionid]['value']."</a>" : '';
					} elseif($option['type'] == 'number') {
						$_G['forum_option'][$option['identifier']]['value'] = $optiondata[$optionid]['value'];
					} else {
						if($option['protect']['status'] && $optiondata[$optionid]['value']) {
							$optiondata[$optionid]['value'] = $option['protect']['mode'] == 1 ? '<image src="'.stringtopic($optiondata[$optionid]['value']).'">' : (!defined('IN_MOBILE') ? '<span id="sortmessage_'.$option['identifier'].'"><a href="###" onclick="ajaxget(\'forum.php?mod=misc&action=protectsort&tid='.$tid.'&optionid='.$optionid.'\', \'sortmessage_'.$option['identifier'].'\');return false;">'.lang('forum/misc', 'click_view').'</a></span>' : $optiondata[$optionid]['value']);
							$_G['forum_option'][$option['identifier']]['value'] = $optiondata[$optionid]['value'] ? $optiondata[$optionid]['value'] : $option['defaultvalue'];
						} elseif($option['type'] == 'textarea') {
							$_G['forum_option'][$option['identifier']]['value'] = $optiondata[$optionid]['value'] != '' ? nl2br($optiondata[$optionid]['value']) : '';
						} else {
							$_G['forum_option'][$option['identifier']]['value'] = $optiondata[$optionid]['value'] != '' ? $optiondata[$optionid]['value'] : $option['defaultvalue'];
						}
					}
				} else {
					if(empty($option['permprompt'])) {
						$_G['forum_option'][$option['identifier']]['value'] = lang('forum/misc', 'view_noperm');
					} else {
						$_G['forum_option'][$option['identifier']]['value'] = $option['permprompt'];
					}

				}
			} else {
				$_G['forum_option'][$option['identifier']]['value'] = lang('forum/misc', 'has_expired');
			}
		}

		$typetemplate = '';
		if($templatearray['viewthread']) {
			foreach($sortoptionarray as $option) {
				$searchtitle[] = '/{('.$option['identifier'].')}/';
				$searchvalue[] = '/\[('.$option['identifier'].')value\]/';
				$searchvalue[] = '/{('.$option['identifier'].')_value}/';
				$searchunit[] = '/\[('.$option['identifier'].')unit\]/';
				$searchunit[] = '/{('.$option['identifier'].')_unit}/';
			}

			$threadexpiration = $sortdataexpiration ? dgmdate($sortdataexpiration) : lang('forum/misc', 'never_expired');
			$typetemplate = preg_replace(array("/\{expiration\}/i"), array($threadexpiration), stripslashes($templatearray['viewthread']));
			$typetemplate = preg_replace_callback($searchtitle, "threadsortshow_callback_showoption_title1", $typetemplate);
			$typetemplate = preg_replace_callback($searchvalue, "threadsortshow_callback_showoption_value1", $typetemplate);
			$typetemplate = preg_replace_callback($searchunit, "threadsortshow_callback_showoption_unit1", $typetemplate);
		}
	}

	$threadsortshow['optionlist'] = !$sortdataexpiration || $sortdataexpiration >= $_G['timestamp'] ? $_G['forum_option'] : 'expire';
	$threadsortshow['typetemplate'] = $typetemplate;
	$threadsortshow['expiration'] = dgmdate($sortdataexpiration, 'd');

	return $threadsortshow;
}

function threadsortshow_callback_showoption_title1($matches) {
	return showoption($matches[1], 'title');
}

function threadsortshow_callback_showoption_value1($matches) {
	return showoption($matches[1], 'value');
}

function threadsortshow_callback_showoption_unit1($matches) {
	return showoption($matches[1], 'unit');
}

function showoption($var, $type) {
	global $_G;
	if($_G['forum_option'][$var][$type] != '') {
		return $_G['forum_option'][$var][$type];
	} else {
		return '';
	}
}

function protectguard($protect) {
	global $_G, $member_verifys;
	if(!isset($member_verifys) && $_G['setting']['verify']['enabled']) {
		$member_verifys = array();
		getuserprofile('verify1');
		foreach($_G['setting']['verify'] as $vid => $verify) {
			if($verify['available'] && $_G['member']['verify'.$vid] == 1) {
				$member_verifys[] = $vid;
			}
		}
	}
	$verifyflag = 0;
	if($_G['setting']['verify']['enabled'] && $protect['verify']) {
		if(array_intersect(explode("\t", $protect['verify']), $member_verifys)) {
			$verifyflag = 1;
		}
	}
	if(($protect['usergroup'] && strstr("\t".$protect['usergroup']."\t", "\t$_G[groupid]\t"))
			|| (empty($protect['usergroup']) && empty($protect['verify']))
			|| $verifyflag
			|| $_G['forum_thread']['authorid'] == $_G['uid']) {
		return false;
	} else {
		return true;
	}
}

function sortthreadsortselectoption($sortid) {
	global $_G;

	if(empty($_G['cache']['threadsort_option_'.$sortid])) {
		return false;
	}
	foreach($_G['cache']['threadsort_option_'.$sortid] as $key => $value) {
		if($value['type'] == 'select' && !empty($value['choices'])) {
			$newsort = array();
			$level = 0;

			foreach($value['choices'] as $subkey => $subvalue) {

				$newsort[$subkey]['content'] = $subvalue;
				$newsort[$subkey]['foptionid'] = trim(substr($subkey, 0, strrpos($subkey, '.'))) ? trim(substr($subkey, 0, strrpos($subkey, '.'))) : '0';
				$newsort[$subkey]['count'] = count(explode('.', $subkey));

				$subkeyarr = explode('.', $subkey);
				if($countsubkeyarr = count($subkeyarr)) {
					$tmpkey = '';
					for($i = 0;$i < $countsubkeyarr;$i++) {
						$subkeyarr[$i] = trim($subkeyarr[$i]);

						if(isset($newsort[$tmpkey.$subkeyarr[$i]]['level'])) {
							if(($countsubkeyarr - $i) > $newsort[$tmpkey.$subkeyarr[$i]]['level']) {
								$newsort[$tmpkey.$subkeyarr[$i]]['level'] = $countsubkeyarr - $i;
							}
						} else {
							$newsort[$tmpkey.$subkeyarr[$i]]['level'] = $countsubkeyarr - $i;
						}
						$tmpkey .= $subkeyarr[$i].'.';
					}
				}
				$newsort[$subkey]['optionid'] = $subkey;
			}
			$_G['cache']['threadsort_option_'.$sortid][$key]['choices'] = $newsort;
		}
	}
}

function cmpchoicekey($stringa, $stringb) {
	$arraya = explode('.', $stringa);
	$arrayb = explode('.', $stringb);
	$counta = count($arraya);
	$countb = count($arrayb);
	if($counta == $countb) {
		foreach($arraya as $key => $value) {
			$valuea = intval(trim($value));
			$valueb = intval(trim($arrayb[$key]));
			if($valuea != $valueb) {
				return ($valuea < $valueb) ? -1 : 1;
			} else {
				continue;
			}
		}
		return 0;
	} else {
		return ($counta < $countb) ? -1 : 1;
	}
}

function threadsort_checkoption($sortid = 0, $unchangeable = 1) {
	global $_G;

	$_G['forum_selectsortid'] = $sortid ? intval($sortid) : '';
	loadcache(array('threadsort_option_'.$sortid));
	sortthreadsortselectoption($sortid);
	$_G['forum_optionlist'] = $_G['cache']['threadsort_option_'.$sortid];
	$_G['forum_checkoption'] = array();
	if(is_array($_G['forum_optionlist'])) {
		foreach($_G['forum_optionlist'] as $optionid => $option) {
			$_G['forum_checkoption'][$option['identifier']]['optionid'] = $optionid;
			$_G['forum_checkoption'][$option['identifier']]['title'] = $option['title'];
			$_G['forum_checkoption'][$option['identifier']]['type'] = $option['type'];
			$_G['forum_checkoption'][$option['identifier']]['required'] = $option['required'] ? 1 : 0;
			$_G['forum_checkoption'][$option['identifier']]['unchangeable'] = $_GET['action'] == 'edit' && $unchangeable && $option['unchangeable'] ? 1 : 0;
			$_G['forum_checkoption'][$option['identifier']]['maxnum'] = $option['maxnum'] ? intval($option['maxnum']) : '';
			$_G['forum_checkoption'][$option['identifier']]['minnum'] = $option['minnum'] ? intval($option['minnum']) : '';
			$_G['forum_checkoption'][$option['identifier']]['maxlength'] = $option['maxlength'] ? intval($option['maxlength']) : '';
		}
	}
}

function threadsort_optiondata($pid, $sortid, $sortoptionarray, $templatearray) {
	global $_G;
	$_G['forum_optiondata'] = $_G['forum_typetemplate'] = $_G['forum_option'] = $_G['forum_memberinfo'] = $searchcontent = array();
	$id = $_G['tid'];

	if($id) {
		foreach(C::t('forum_typeoptionvar')->fetch_all_by_tid_optionid($id) as $option) {
			$_G['forum_optiondata'][$option['optionid']] = $option['value'];
			$_G['forum_optiondata']['expiration'] = $option['expiration'];
		}
	}

	$_G['forum_optiondata']['expiration'] = $_G['forum_optiondata']['expiration'] ? dgmdate($_G['forum_optiondata']['expiration'], 'd') : '';

	foreach($sortoptionarray as $optionid => $option) {
		if($id) {
			$_G['forum_optionlist'][$optionid]['unchangeable'] = $sortoptionarray[$optionid]['unchangeable'] ? 'disabled' : '';
			if($sortoptionarray[$optionid]['type'] == 'radio') {
				$_G['forum_optionlist'][$optionid]['value'] = array($_G['forum_optiondata'][$optionid] => 'checked="checked"');
			} elseif($sortoptionarray[$optionid]['type'] == 'select') {
				$_G['forum_optionlist'][$optionid]['value'] = $_G['forum_optiondata'][$optionid] ? array($_G['forum_optiondata'][$optionid] => 'selected="selected"') : '';
			} elseif($sortoptionarray[$optionid]['type'] == 'checkbox') {
				foreach(explode("\t", $_G['forum_optiondata'][$optionid]) as $value) {
					$_G['forum_optionlist'][$optionid]['value'][$value] = array($value => 'checked="checked"');
				}
			} elseif($sortoptionarray[$optionid]['type'] == 'image') {
				$_G['forum_optionlist'][$optionid]['value'] = dunserialize($_G['forum_optiondata'][$optionid]);
			} else {
				$_G['forum_optionlist'][$optionid]['value'] = $_G['forum_optiondata'][$optionid];
			}
			if(!isset($_G['forum_optiondata'][$optionid])) {
				C::t('forum_typeoptionvar')->insert(array(
					'sortid' => $sortid,
					'tid' => $id,
					'fid' => $_G['fid'],
					'optionid' => $optionid,
				));
			}
		}

		if($templatearray['post']) {
			$_G['forum_option'][$option['identifier']]['title'] = $option['title'];
			$_G['forum_option'][$option['identifier']]['unit'] = $option['unit'];
			$_G['forum_option'][$option['identifier']]['description'] = $option['description'];
			$_G['forum_option'][$option['identifier']]['required'] = $option['required'] ? '*' : '';
			$_G['forum_option'][$option['identifier']]['tips'] = '<span id="check'.$option['identifier'].'"></span>';

			$showoption = gettypetemplate($option, $_G['forum_optionlist'][$optionid], $optionid);
			$_G['forum_option'][$option['identifier']]['value'] = $showoption[$option['identifier']]['value'];

			$searchcontent['title'][] = '/{('.$option['identifier'].')}/';
			$searchcontent['value'][] = '/\[('.$option['identifier'].')value\]/';
			$searchcontent['value'][] = '/{('.$option['identifier'].')_value}/';
			$searchcontent['unit'][] = '/\[('.$option['identifier'].')unit\]/';
			$searchcontent['unit'][] = '/{('.$option['identifier'].')_unit}/';
			$searchcontent['description'][] = '/\[('.$option['identifier'].')description\]/';
			$searchcontent['description'][] = '/{('.$option['identifier'].')_description}/';
			$searchcontent['required'][] = '/\[('.$option['identifier'].')required\]/';
			$searchcontent['required'][] = '/{('.$option['identifier'].')_required}/';
			$searchcontent['tips'][] = '/\[('.$option['identifier'].')tips\]/';
			$searchcontent['tips'][] = '/{('.$option['identifier'].')_tips}/';
		}
	}

	if($templatearray['post']) {
		$typetemplate = $templatearray['post'];
		foreach($searchcontent as $key => $content) {
			$typetemplate = preg_replace_callback($searchcontent[$key], create_function('$matches', 'return showoption($matches[1], \''.addslashes($key).'\');'), stripslashes($typetemplate));
		}

		$_G['forum_typetemplate'] = $typetemplate;
	}
}

function threadsort_validator($sortoption, $pid) {
	global $_G, $var;
	$postaction = $_G['tid'] && $pid ? "edit&tid=$_G[tid]&pid=$pid" : 'newthread';
	$_G['forum_optiondata'] = array();
	foreach($_G['forum_checkoption'] as $var => $option) {
		if($_G['forum_checkoption'][$var]['required'] && ($sortoption[$var] === '' && $_G['forum_checkoption'][$var]['type'] != 'number')) {
			showmessage('threadtype_required_invalid', "forum.php?mod=post&action=$postaction&fid=$_G[fid]&sortid=".$_G['forum_selectsortid'], array('typetitle' => $_G['forum_checkoption'][$var]['title']));
		} elseif($sortoption[$var] && ($_G['forum_checkoption'][$var]['type'] == 'number' && !is_numeric($sortoption[$var]) || $_G['forum_checkoption'][$var]['type'] == 'email' && !isemail($sortoption[$var]))){
			showmessage('threadtype_format_invalid', "forum.php?mod=post&action=$postaction&fid=$_G[fid]&sortid=".$_G['forum_selectsortid'], array('typetitle' => $_G['forum_checkoption'][$var]['title']));
		} elseif($sortoption[$var] && $_G['forum_checkoption'][$var]['maxlength'] && strlen($sortoption[$var]) > $_G['forum_checkoption'][$var]['maxlength']) {
			showmessage('threadtype_toolong_invalid', "forum.php?mod=post&action=$postaction&fid=$_G[fid]&sortid=".$_G['forum_selectsortid'], array('typetitle' => $_G['forum_checkoption'][$var]['title']));
		} elseif($sortoption[$var] && (($_G['forum_checkoption'][$var]['maxnum'] && $sortoption[$var] > $_G['forum_checkoption'][$var]['maxnum']) || ($_G['forum_checkoption'][$var]['minnum'] && $sortoption[$var] < $_G['forum_checkoption'][$var]['minnum']))) {
			showmessage('threadtype_num_invalid', "forum.php?mod=post&action=$postaction&fid=$_G[fid]&sortid=".$_G['forum_selectsortid'], array('typetitle' => $_G['forum_checkoption'][$var]['title']));
		} elseif($sortoption[$var] && $_G['forum_checkoption'][$var]['unchangeable'] && ($_G['tid'] && $pid)) {
			showmessage('threadtype_unchangeable_invalid', "forum.php?mod=post&action=$postaction&fid=$_G[fid]&sortid=".$_G['forum_selectsortid'], array('typetitle' => $_G['forum_checkoption'][$var]['title']));
		} elseif($sortoption[$var] && ($_G['forum_checkoption'][$var]['type'] == 'select')) {
			if($_G['forum_optionlist'][$_G['forum_checkoption'][$var]['optionid']]['choices'][$sortoption[$var]]['level'] != 1) {
				showmessage('threadtype_select_invalid', "forum.php?mod=post&action=$postaction&fid=$_G[fid]&sortid=".$_G['forum_selectsortid'], array('typetitle' => $_G['forum_checkoption'][$var]['title']));
			}
		}
		if($_G['forum_checkoption'][$var]['type'] == 'checkbox') {
			$sortoption[$var] = $sortoption[$var] ? implode("\t", $sortoption[$var]) : '';
		} elseif($_G['forum_checkoption'][$var]['type'] == 'url') {
			$sortoption[$var] = $sortoption[$var] ? (substr(strtolower($sortoption[$var]), 0, 4) == 'www.' ? 'http://'.$sortoption[$var] : $sortoption[$var]) : '';
		}

		if($_G['forum_checkoption'][$var]['type'] == 'image') {
			if($sortoption[$var]['aid']) {
				$_GET['attachnew'][$sortoption[$var]['aid']] = $sortoption[$var];
			}
			$sortoption[$var] = serialize($sortoption[$var]);
		} elseif($_G['forum_checkoption'][$var]['type'] == 'select') {
			$sortoption[$var] = censor(trim($sortoption[$var]));
		} else {
			$sortoption[$var] = dhtmlspecialchars(censor(trim($sortoption[$var])));
		}
		$_G['forum_optiondata'][$_G['forum_checkoption'][$var]['optionid']] = $sortoption[$var];
	}

	return $_G['forum_optiondata'];
}

function getsortedoptionlist() {
	global $_G;

	$forum_optionlist = $_G['forum_optionlist'];
	foreach($_G['forum_optionlist'] as $key => $value) {
		$choicesarr = $value['choices'];
		uksort($choicesarr, 'cmpchoicekey');
		$forum_optionlist[$key]['choices'] = $choicesarr;
	}
	$forum_optionlist = optionlistxml($forum_optionlist, 's');
	$forum_optionlist = '<?xml version="1.0" encoding="'.CHARSET.'"?>'."".'<forum_optionlist>'.$forum_optionlist.'</forum_optionlist>';
	return $forum_optionlist;
}

function optionlistxml($input, $pre = '') {
	$str = '';
	foreach($input as $key => $value) {
		$key = $pre.strval($key);
		if(is_array($value)) {
			$str .= "<$key>";
			$str .= optionlistxml($value, $pre);
			$str .="</$key>";
		} else {
			if(is_bool($value)) {
				$value = ($value == true) ? 'true' : 'false';
			}
			$value = str_replace("\r\n", '<br>', $value);
			if(dhtmlspecialchars($value) != $value) {
				$str .= "<$key><![CDATA[$value]]></$key>";
			} else {
				$str .= "<$key>$value</$key>";
			}
		}
	}
	return $str;
}

?>