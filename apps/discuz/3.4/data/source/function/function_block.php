<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_block.php 32895 2013-03-21 04:18:15Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function block_script($blockclass, $script) {
	global $_G;
	$arr = explode('_', $blockclass);
	$dirname = $arr[0];
	$xmlid = null;
	if(strtoupper($dirname) == 'XML' && $script == 'xml' && intval($arr[1])) {
		$xmlid = intval($arr[1]);
	}
	$var = "blockscript_{$dirname}_{$script}";
	$script = 'block_'.$script;
	if(!isset($_G[$var]) || $xmlid) {
		if(@include_once libfile($script, 'class/block/'.$dirname)) {
			$_G[$var] = $xmlid ?  new $script($xmlid) : new $script();
		} else {
			$_G[$var] = false;
		}
	}
	return $_G[$var];
}

function block_get_batch($parameter) {
	global $_G;
	$bids = $parameter && is_array($parameter) ? $parameter : ($parameter ? explode(',', $parameter) : array());
	$bids = array_map('intval', $bids);
	$bids = array_unique($bids);
	$styleids = array();

	if($bids) {
		if(C::t('common_block')->allowmem) {
			if(($cachedata = memory('get', $bids, 'blockcache_')) !== false) {
				foreach ($cachedata as $bid => $block) {
					$_G['block'][$bid] = $block;
					if($block['styleid']) {
						$styleids[$block['styleid']] = $block['styleid'];
					}
				}
				if($styleids) {
					block_getstyle($styleids);
				}
				if(!($bids = array_diff($bids, array_keys($cachedata)))) {
					return true;
				}
			}
		}

		$items = $prelist = array();
		foreach(C::t('common_block_item')->fetch_all_by_bid($bids) as $item) {
			if($item['itemtype'] == '1' && $item['enddate'] && $item['enddate'] < TIMESTAMP) {
				continue;
			} elseif($item['itemtype'] == '1' && (!$item['startdate'] || $item['startdate'] <= TIMESTAMP)) {
				if (!empty($items[$item['bid']][$item['displayorder']])) {
					$prelist[$item['bid']] = array();
				}
				$prelist[$item['bid']][$item['displayorder']] = $item;
			}
			$items[$item['bid']][$item['displayorder']] = $item;
		}
		foreach(C::t('common_block')->fetch_all($bids) as $bid => $block) {
			if(!empty($block['styleid']) && $block['styleid'] > 0) {
				$styleids[] = intval($block['styleid']);
			}
			if(!empty($items[$bid])) {
				ksort($items[$bid]);
				$newitem = array();
				if(!empty($prelist[$bid])) {
					$countpre = 0;
					foreach($items[$bid] as $position => $item) {
						if(empty($prelist[$bid][$position])) {
							if(isset($items[$bid][$position+$countpre])) {
								$newitem[$position+$countpre] = $item;
							}
						} else {
							if ($item['itemtype']=='1') {
								if ($prelist[$bid][$position]['startdate'] >= $item['startdate']) {
									$newitem[$position] = $prelist[$bid][$position];
								} else {
									$newitem[$position] = $item;
								}
							} else {
								$newitem[$position] = $prelist[$bid][$position];
								$countpre++;
								if(isset($items[$bid][$position+$countpre])) {
									$newitem[$position+$countpre] = $item;
								}
							}
						}
					}
					ksort($newitem);
				}
				$block['itemlist'] = empty($newitem) ? $items[$bid] : $newitem;
			}
			$block['param'] = $block['param'] ? dunserialize($block['param']) : array();
			$_G['block'][$bid] = $block;

			if(C::t('common_block')->allowmem) {
				memory('set', 'blockcache_'.$bid, $_G['block'][$bid], C::t('common_block')->cache_ttl);
			}

		}
	}
	if($styleids) {
		block_getstyle($styleids);
	}
}

function block_display_batch($bid) {
	echo block_fetch_content($bid);
}

function block_fetch_content($bid, $isjscall=false, $forceupdate=false) {
	global $_G;
	static $allowmem = null, $cachettl = null;
	if($allowmem === null) {
		$allowmem = ($cachettl = getglobal('setting/memory/diyblockoutput')) !== null && memory('check');
	}

	$str = '';
	$block = empty($_G['block'][$bid])?array():$_G['block'][$bid];
	if(!$block) {
		return;
	}

	if($forceupdate) {
		block_updatecache($bid, true);
		$block = $_G['block'][$bid];
	} elseif($block['cachetime'] > 0 && $_G['timestamp'] - $block['dateline'] > $block['cachetime']) {

		$block['cachetimerange'] = empty($block['cachetimerange']) ? (isset($_G['setting']['blockcachetimerange']) ? $_G['setting']['blockcachetimerange'] : '') : $block['cachetimerange'];
		$inrange = empty($block['cachetimerange']) ? true : false;
		if(!$inrange) {
			$block['cachetimerange'] = explode(',', $block['cachetimerange']);
			$hour = date('G', TIMESTAMP);
			if($block['cachetimerange'][0] <= $block['cachetimerange'][1]) {
				$inrange = $block['cachetimerange'][0] <= $hour && $block['cachetimerange'][1] >= $hour;
			} else {
				$inrange = !($block['cachetimerange'][1] < $hour && $block['cachetimerange'][0] > $hour);
			}
		}

		if($isjscall || $block['punctualupdate']) {
			block_updatecache($bid, true);
			$block = $_G['block'][$bid];
		} elseif((empty($_G['blockupdate']) || $block['dateline'] < $_G['blockupdate']['dateline']) && $inrange) {
			$_G['blockupdate'] = array('bid'=>$bid, 'dateline'=>$block['dateline']);
		}
	}

	$hidediv = $isjscall || $block['blocktype'];

	$_cache_key = 'blockcache_'.($isjscall ? 'js' : 'htm').'_'.$bid;
	if($allowmem && empty($block['hidedisplay']) && empty($block['nocache']) && ($str = memory('get', $_cache_key)) !== false) {

	} else {

		if($hidediv) {
			if($block['summary']) $str .= $block['summary'];
			$str .= block_template($bid);
		} else {
			if($block['title']) $str .= $block['title'];
			$str .= '<div id="portal_block_'.$bid.'_content" class="dxb_bc">';
			if($block['summary']) {
				$str .= "<div class=\"portal_block_summary\">$block[summary]</div>";
			}
			$str .= block_template($bid);
			$str .= '</div>';
		}

		if($allowmem && empty($block['hidedisplay']) && empty($block['nocache'])) {
			memory('set', $_cache_key, $str, C::t('common_block')->cache_ttl);
		}
	}

	if(!$hidediv) {
		$classname = !empty($block['classname']) ? $block['classname'].' ' : '';
		$div = "<div id=\"portal_block_$bid\" class=\"{$classname}block move-span\">";
		if(($_GET['diy'] === 'yes' || $_GET['inajax']) && check_diy_perm()) {
			$div .= "<div class='block-name'>$block[name] (ID:$bid)</div>";
		}
		$str = $div.$str."</div>";
	}
	if($block['blockclass'] == 'html_html' && $block['script'] == 'search') $str = strtr($str, array('{FORMHASH}'=>FORMHASH));
	return !empty($block['hidedisplay']) ? '' : $str;
}

function block_updatecache($bid, $forceupdate=false) {
	global $_G;
	if((isset($_G['block'][$bid]['cachetime']) && $_G['block'][$bid]['cachetime'] < 0) || !$forceupdate && discuz_process::islocked('block_update_cache', 5)) {
		return false;
	}
	C::t('common_block')->clear_cache($bid);
	$block = empty($_G['block'][$bid])?array():$_G['block'][$bid];
	if(!$block) {
		return false;
	}
	$obj = block_script($block['blockclass'], $block['script']);
	if(is_object($obj)) {
		C::t('common_block')->update($bid, array('dateline'=>TIMESTAMP));
		$_G['block'][$bid]['dateline'] = TIMESTAMP;
		$theclass = block_getclass($block['blockclass']);
		$thestyle = !empty($block['styleid']) ? block_getstyle($block['styleid']) : dunserialize($block['blockstyle']);

		if(in_array($block['blockclass'], array('forum_thread', 'group_thread', 'space_blog', 'space_pic', 'portal_article'))) {
			$datalist = array();
			$mapping = array('forum_thread'=>'tid', 'group_thread'=>'tid', 'space_blog'=>'blogid', 'space_blog'=>'picid', 'portal_article'=>'aid');
			$idtype = $mapping[$block['blockclass']];
			$bannedids = !empty($block['param']['bannedids']) ? explode(',', $block['param']['bannedids']) : array();
			$bannedsql = $bannedids ? ' AND id NOT IN ('.dimplode($bannedids).')' : '';
			$shownum = intval($block['shownum']);
			$titlelength	= !empty($block['param']['titlelength']) ? intval($block['param']['titlelength']) : 40;
			$summarylength	= !empty($block['param']['summarylength']) ? intval($block['param']['summarylength']) : 80;
			foreach(C::t('common_block_item_data')->fetch_all_by_bid($bid, 1, 0, $shownum * 2, $bannedids, false) as $value) {
				$value['title'] = cutstr($value['title'], $titlelength, '');
				$value['summary'] = cutstr($value['summary'], $summarylength, '');
				$value['itemtype'] = '3';
				$datalist[] = $value;
				$bannedids[] = intval($value['id']);
			}
			$leftnum = $block['shownum'] - count($datalist);
			if($leftnum > 0 && empty($block['isblank'])) {
				if($leftnum != $block['param']['items']) {
					$block['param']['items'] = $leftnum;
					$block['param']['bannedids'] = implode(',',$bannedids);
				}
				$return = $obj->getdata($thestyle, $block['param']);
				$return['data'] = array_merge($datalist, (array)$return['data']);
			} else {
				$return['data'] = $datalist;
			}
		} else {
			$return = $obj->getdata($thestyle, $block['param']);
		}

		if($return['data'] === null) {
			$_G['block'][$block['bid']]['summary'] = $return['html'];
			C::t('common_block')->update($bid, array('summary'=>$return['html']));
		} else {
			$_G['block'][$block['bid']]['itemlist'] = block_updateitem($bid, $return['data']);
		}
	} else {
		C::t('common_block')->update($bid, array('dateline'=>TIMESTAMP+999999, 'cachetime'=>0));
		$_G['block'][$bid]['dateline'] = TIMESTAMP+999999;
	}
	if(C::t('common_block')->allowmem) {
		memory('set', 'blockcache_'.$bid, $_G['block'][$bid], C::t('common_block')->cache_ttl);
		$styleid = $_G['block'][$bid]['styleid'];
		if($styleid && $_G['blockstyle_'.$styleid]) {
			memory('set', 'blockstylecache_'.$styleid, $_G['blockstyle_'.$styleid], C::t('common_block')->cache_ttl);
		}
	}
	discuz_process::unlock('block_update_cache');
}

function block_template($bid) {
	global $_G;

	$block = empty($_G['block'][$bid]) ? array() : $_G['block'][$bid];

	$theclass = block_getclass($block['blockclass'], false);
	$thestyle = !empty($block['styleid']) ? block_getstyle($block['styleid']) : dunserialize($block['blockstyle']);
	if(empty($block) || empty($theclass) || empty($thestyle)) {
		return false;
	}
	$template = block_build_template($thestyle['template']);
	if(!empty($block['itemlist'])) {
		if($thestyle['moreurl']) {
			$template = str_replace('{moreurl}', 'portal.php?mod=block&bid='.$bid, $template);
		}
		$fields = array('picwidth'=>array(), 'picheight'=>array(), 'target'=>array(), 'currentorder'=>array());
		if($block['hidedisplay']) {
			$fields = array_merge($fields, $theclass['fields']);
		} else {
			$thestyle['fields'] = !empty($thestyle['fields']) && is_array($thestyle['fields']) ? $thestyle['fields'] : block_parse_fields($template);
			foreach($thestyle['fields'] as $k) {
				if(isset($theclass['fields'][$k])) {
					$fields[$k] = $theclass['fields'][$k];
				}
			}
		}

		$order = 0;
		$dynamicparts = array();
		foreach($block['itemlist'] as $position=>$blockitem) {
			$itemid = $blockitem['itemid'];
			$order++;

			$rkey = $rpattern = $rvalue = $rtpl = array();
			$rkeyplug = false;
			if(isset($thestyle['template']['index']) && is_array($thestyle['template']['index']) && isset($thestyle['template']['index'][$order])) {
				$rkey[] = 'index_'.$order;
				$rpattern[] = '/\s*\[index='.$order.'\](.*?)\[\/index\]\s*/is';
				$rvalue[] = '';
				$rtpl[] = $thestyle['template']['index'][$order];
			}
			if(empty($rkey)) {
				$rkey[] = 'loop';
				$rpattern[] = '/\s*\[loop\](.*?)\[\/loop\]\s*/is';
				$rvalue[] = isset($dynamicparts['loop']) ? $dynamicparts['loop'][1] : '';
				if(is_array($thestyle['template']['order']) && isset($thestyle['template']['order'][$order])) {
					$rtpl[] = $thestyle['template']['order'][$order];
				} elseif(is_array($thestyle['template']['order']) && isset($thestyle['template']['order']['odd']) && ($order % 2 == 1)) {
					$rtpl[] = $thestyle['template']['order']['odd'];
				} elseif(is_array($thestyle['template']['order']) && isset($thestyle['template']['order']['even']) && ($order % 2 == 0)) {
					$rtpl[] = $thestyle['template']['order']['even'];
				} else {
					$rtpl[] = $thestyle['template']['loop'];
				}
			}
			if(!empty($thestyle['template']['indexplus'])) {
				foreach($thestyle['template']['indexplus'] as $k=>$v) {
					if(isset($v[$order])) {
						$rkey[] = 'index'.$k.'='.$order;
						$rkeyplug = true;
						$rpattern[] = '/\[index'.$k.'='.$order.'\](.*?)\[\/index'.$k.'\]/is';
						$rvalue[] = '';
						$rtpl[] = $v[$order];
					}
				}
			}
			if(empty($rkeyplug)) {
				if(!empty($thestyle['template']['loopplus'])) {
					foreach($thestyle['template']['loopplus'] as $k=>$v) {
						$rkey[] = 'loop'.$k;
						$rpattern[] = '/\s*\[loop'.$k.'\](.*?)\[\/loop'.$k.'\]\s*/is';
						$rvalue[] = isset($dynamicparts['loop'.$k]) ? $dynamicparts['loop'.$k][1] : '';
						if(is_array($thestyle['template']['orderplus'][$k]) && isset($thestyle['template']['orderplus'][$k][$order])) {
							$rtpl[] = $thestyle['template']['orderplus'][$k][$order];
						} elseif(is_array($thestyle['template']['orderplus'][$k]) && isset($thestyle['template']['orderplus'][$k]['odd']) && ($order % 2 == 1)) {
							$rtpl[] = $thestyle['template']['orderplus'][$k]['odd'];
						} elseif(is_array($thestyle['template']['orderplus'][$k]) && isset($thestyle['template']['orderplus'][$k]['even']) && ($order % 2 == 0)) {
							$rtpl[] = $thestyle['template']['orderplus'][$k]['even'];
						} else {
							$rtpl[] = $thestyle['template']['loopplus'][$k];
						}
					}
				}
			}
			$blockitem['fields'] = is_array($blockitem['fields']) ? $blockitem['fields'] : dunserialize($blockitem['fields']);
			$blockitem['fields'] = is_array($blockitem['fields']) ? $blockitem['fields'] : array();
			if(!empty($blockitem['showstyle'])) {
				$blockitem['fields']['showstyle'] = dunserialize($blockitem['showstyle']);
			}
			$blockitem = $blockitem['fields'] + $blockitem;

			$blockitem['picwidth'] = !empty($block['picwidth']) ? intval($block['picwidth']) : 'auto';
			$blockitem['picheight'] = !empty($block['picheight']) ? intval($block['picheight']) : 'auto';
			$blockitem['target'] = !empty($block['target']) ? ' target="_'.$block['target'].'"' : '';
			$blockitem['currentorder'] = $order;
			$blockitem['parity'] = $order % 2;

			$searcharr = $replacearr = array();
			$searcharr[] = '{parity}';
			$replacearr[] = $blockitem['parity'];
			foreach($fields as $key=>$field) {
				$replacevalue = $blockitem[$key];
				$field['datatype'] = !empty($field['datatype']) ? $field['datatype'] : '';
				if($field['datatype'] == 'int') {// int
					$replacevalue = intval($replacevalue);
				} elseif($field['datatype'] == 'string') {
					$replacevalue = preg_quote($replacevalue);
				} elseif($field['datatype'] == 'date') {
					$replacevalue = dgmdate($replacevalue, $block['dateuformat'] ? 'u' : $block['dateformat'], '9999', $block['dateuformat'] ? $block['dateformat'] : '');
				} elseif($field['datatype'] == 'title') {//title
					$searcharr[] = '{title-title}';
					$replacearr[] = preg_quote(!empty($blockitem['fields']['fulltitle']) ? $blockitem['fields']['fulltitle'] : dhtmlspecialchars($replacevalue));
					$searcharr[] = '{alt-title}';
					$replacearr[] = preg_quote(!empty($blockitem['fields']['fulltitle']) ? $blockitem['fields']['fulltitle'] : dhtmlspecialchars($replacevalue));
					$replacevalue = preg_quote($replacevalue);
					if($blockitem['showstyle'] && ($style = block_showstyle($blockitem['showstyle'], 'title'))) {
						$replacevalue = '<font style="'.$style.'">'.$replacevalue.'</font>';
					}
				} elseif($field['datatype'] == 'summary') {//summary
					$replacevalue = preg_quote($replacevalue);
					if($blockitem['showstyle'] && ($style = block_showstyle($blockitem['showstyle'], 'summary'))) {
						$replacevalue = '<font style="'.$style.'">'.$replacevalue.'</font>';
					}
				} elseif($field['datatype'] == 'pic') {
					if($blockitem['picflag'] == '1') {
						if(!$_G['setting']['ftp']['on'] || file_exists($_G['setting']['attachdir'].$replacevalue)) {
							$replacevalue = $_G['setting']['attachurl'].$replacevalue;
						} else {
							$replacevalue = (preg_match('/^https?:\/\//is', $replacevalue) ? '' : $_G['setting']['ftp']['attachurl']).$replacevalue;
						}
					} elseif ($blockitem['picflag'] == '2') {
						$replacevalue = (preg_match('/^https?:\/\//is', $replacevalue) ? '' : $_G['setting']['ftp']['attachurl']).$replacevalue;
					}
					if($blockitem['picflag'] && $block['picwidth'] && $block['picheight'] && $block['picwidth'] != 'auto' && $block['picheight'] != 'auto') {
						if($blockitem['makethumb'] == 1) {
							if($blockitem['picflag'] == '1') {
								if(!$_G['setting']['ftp']['on'] || file_exists($_G['setting']['attachdir'].$blockitem['thumbpath'])) {
									$replacevalue = $_G['setting']['attachurl'].$blockitem['thumbpath'];
								} else {
									$replacevalue = (preg_match('/^https?:\/\//is', $blockitem['thumbpath']) ? '' : $_G['setting']['ftp']['attachurl']).$blockitem['thumbpath'];
								}
							} elseif ($blockitem['picflag'] == '2') {
								$replacevalue = (preg_match('/^https?:\/\//is', $blockitem['thumbpath']) ? '' : $_G['setting']['ftp']['attachurl']).$blockitem['thumbpath'];
							}
						} elseif(!$_G['block_makethumb'] && !$blockitem['makethumb']) {
							C::t('common_block_item')->update($itemid, array('makethumb'=>2));
							require_once libfile('class/image');
							$image = new image();
							$thumbpath = block_thumbpath($block, $blockitem);
							if($_G['setting']['ftp']['on'] && !empty($_G['setting']['ftp']['host'])) {
								$ftp = & discuz_ftp::instance();
								$ftp->connect();
								if($ftp->connectid && $ftp->ftp_size($thumbpath) > 0) {
									$picflag = 1; //common_block_pic表中的picflag标识（0本地，1远程）
									$_G['block_makethumb'] = true;
									@unlink($_G['setting']['attachdir'].'./'.$thumbpath);
									C::t('common_block_item')->update($itemid, array('picflag' => 2));
									$replacevalue = (preg_match('/^https?:\/\//is', $thumbpath) ? '' : $_G['setting']['ftp']['attachurl']).$thumbpath;
								} else {
									$image->Thumb($replacevalue, $thumbpath, $block['picwidth'], $block['picheight'], 2);
									if(file_exists($_G['setting']['attachdir'].'./'.$thumbpath)) {
										if(ftpperm(fileext($thumbpath), filesize($_G['setting']['attachdir'].'./'.$thumbpath)) && $ftp->upload($_G['setting']['attachurl'].'/'.$thumbpath, $thumbpath)) {
											$picflag = 1; //common_block_pic表中的picflag标识（0本地，1远程）
											$_G['block_makethumb'] = true;
											@unlink($_G['setting']['attachdir'].'./'.$thumbpath);
											C::t('common_block_item')->update($itemid, array('picflag' => 2));
											$replacevalue = (preg_match('/^https?:\/\//is', $thumbpath) ? '' : $_G['setting']['ftp']['attachurl']).$thumbpath;
										} else {
											$picflag = 0; //common_block_pic表中的picflag标识（0本地，1远程）
											$_G['block_makethumb'] = true;
										}
									}
								}
							} elseif(file_exists($_G['setting']['attachdir'].'./'.$thumbpath) || ($return = $image->Thumb($replacevalue, $thumbpath, $block['picwidth'], $block['picheight'], 2))) {
								$picflag = 0; //common_block_pic表中的picflag标识（0本地，1远程）
								$_G['block_makethumb'] = true;
							}
							if($_G['block_makethumb']) {
								C::t('common_block_item')->update($itemid, array('makethumb'=>1, 'thumbpath' => $thumbpath));
								C::t('common_block')->clear_cache($block['bid']);
								$thumbdata = array('bid' => $block['bid'], 'itemid' => $itemid, 'pic' => $thumbpath, 'picflag' => $picflag, 'type' => '0');
								C::t('common_block_pic')->insert($thumbdata);
							}
						}
					}
				}
				$searcharr[] = '{'.$key.'}';
				$replacearr[] = $replacevalue;

				if($block['hidedisplay']) {
					if(strpos($replacevalue, "\\") !== false) {
						$replacevalue = str_replace(
								array('\.', '\\\\', '\+', '\*', '\?', '\[', '\^', '\]', '\$', '\(', '\)', '\{', '\}', '\=', '\!', '\<', '\>', '\|', '\:', '\-'),
								array('.', '\\', '+', '*', '?', '[', '^', ']', '$', '(', ')', '{', '}', '=', '!', '<', '>', '|', ':', '-'), $replacevalue);
					}
					$_G['block_'.$bid][$order-1][$key] = $replacevalue;
				}
			}
			foreach($rtpl as $k=>$str_template) {
				if($str_template) {
					$str_template = preg_replace('/title=[\'"]{title}[\'"]/', 'title="{title-title}"', $str_template);
					$str_template = preg_replace('/alt=[\'"]{title}[\'"]/', 'alt="{alt-title}"', $str_template);
					$rvalue[$k] .= str_replace($searcharr, $replacearr, $str_template);
					$dynamicparts[$rkey[$k]] = array($rpattern[$k], $rvalue[$k]);
				}
			}
		}// foreach($block['itemlist'] as $itemid=>$blockitem) {

		foreach($dynamicparts as $value) {
			$template = preg_replace($value[0], $value[1], $template);
		}
		$template = str_replace('\\', '&#92;', stripslashes($template));
	}
	$template = preg_replace('/\s*\[(order\d*)=\w+\](.*?)\[\/\\1\]\s*/is', '', $template);
	$template = preg_replace('/\s*\[(index\d*)=\w+\](.*?)\[\/\\1\]\s*/is', '', $template);
	$template = preg_replace('/\s*\[(loop\d{0,1})\](.*?)\[\/\\1\]\s*/is', '', $template);
	return $template;
}

function block_showstyle($showstyle, $key) {
	$style = '';
	if(!empty($showstyle["{$key}_b"])) {
		$style .= 'font-weight: 900;';
	}
	if(!empty($showstyle["{$key}_i"])) {
		$style .= 'font-style: italic;';
	}
	if(!empty($showstyle["{$key}_u"])) {
		$style .= 'text-decoration: underline;';
	}
	if(!empty($showstyle["{$key}_c"])) {
		$style .= 'color: '.$showstyle["{$key}_c"].';';
	}
	return $style;
}


function block_setting($blockclass, $script, $values = array()) {
	global $_G;

	$return = array();
	$obj = block_script($blockclass, $script);
	if(!is_object($obj)) return $return;
	return block_makeform($obj->getsetting(), $values);
}

function block_makeform($blocksetting, $values){
	global $_G;
	static $randomid = 0, $calendar_loaded = false;
	$return = array();
	foreach($blocksetting as $settingvar => $setting) {
		$varname = in_array($setting['type'], array('mradio', 'mcheckbox', 'select', 'mselect')) ?
			($setting['type'] == 'mselect' ? array('parameter['.$settingvar.'][]', $setting['value']) : array('parameter['.$settingvar.']', $setting['value']))
			: 'parameter['.$settingvar.']';
		$value = isset($values[$settingvar]) ? $values[$settingvar] : $setting['default'];
		$type = $setting['type'];
		$s = $comment = '';
		if(preg_match('/^([\w]+?)_[\w]+$/i', $setting['title'], $match)) {
			$langscript = $match[1];
			$setname = lang('block/'.$langscript, $setting['title']);
			$comment = lang('block/'.$langscript, $setting['title'].'_comment', array(), '');
		} else {
			$langscript = '';
			$setname = $setting['title'];
		}
		$check = array();
		if($type == 'radio') {
			$value ? $check['true'] = "checked" : $check['false'] = "checked";
			$value ? $check['false'] = '' : $check['true'] = '';
			$s .= '<label for="randomid_'.(++$randomid).'" class="lb"><input type="radio" name="'.$varname.'" id="randomid_'.$randomid.'" class="pr" value="1" '.$check['true'].'>'.lang('core', 'yes').'</label>'.
				'<label for="randomid_'.(++$randomid).'" class="lb"><input type="radio" name="'.$varname.'" id="randomid_'.$randomid.'" class="pr" value="0" '.$check['false'].'>'.lang('core', 'no').'</label>';
		} elseif($type == 'text' || $type == 'password' || $type == 'number') {
			$s .= '<input type="'.$type.'" name="'.$varname.'" class="px" value="'.dhtmlspecialchars($value).'" />';
		} elseif($type == 'textarea') {
			$s .= '<textarea name="'.$varname.'" class="pt" rows="4" cols="40">'.dhtmlspecialchars($value).'</textarea>';
		} elseif($type == 'mtextarea') {
			$s .= '<textarea name="'.$varname.'" class="pt" rows="4" cols="40" onblur="blockCheckTag(this);">'.dhtmlspecialchars($value).'</textarea>';
		} elseif($type == 'select') {
			$s .= '<select name="'.$varname[0].'" class="ps">';
			foreach($varname[1] as $option) {
				$selected = $option[0] == $value ? ' selected="selected"' : '';
				$s .= '<option value="'.$option[0].'"'.$selected.'>'.($langscript ? lang('block/'.$langscript, $option[1]) : $option[1]).'</option>';
			}
			$s .= '</select>';
		} elseif($type == 'mradio') {
			if(is_array($varname)) {
				$radiocheck = array($value => ' checked');
				$s .= '<ul'.(empty($varname[2]) ?  ' class="pr"' : '').'>';
				foreach($varname[1] as $varary) {
					if(is_array($varary) && !empty($varary)) {
						$s .= '<li'.($radiocheck[$varary[0]] ? ' class="checked"' : '').'><label for="randomid_'.(++$randomid).'"><input type="radio" name="'.$varname[0].'" id="randomid_'.$randomid.'" class="pr" value="'.$varary[0].'"'.$radiocheck[$varary[0]].'>'.($langscript ? lang('block/'.$langscript, $varary[1]) : $varary[1]).'</label></li>';
					}
				}
				$s .= '</ul>';
			}
		} elseif($type == 'mcheckbox') {
			$s .= '<ul class="nofloat">';
			foreach($varname[1] as $varary) {
				if(is_array($varary) && !empty($varary)) {
					$checked = is_array($value) && in_array($varary[0], $value) ? ' checked' : '';
					$s .= '<li'.($checked ? ' class="checked"' : '').'><label for="randomid_'.(++$randomid).'"><input type="checkbox" name="'.$varname[0].'[]" id="randomid_'.$randomid.'" class="pc" value="'.$varary[0].'"'.$checked.'>'.($langscript ? lang('block/'.$langscript, $varary[1]) : $varary[1]).'</label></li>';
				}
			}
			$s .= '</ul>';
		} elseif($type == 'mselect') {
			$s .= '<select name="'.$varname[0].'" class="ps" multiple="multiple" size="10">';
			foreach($varname[1] as $option) {
				$selected = is_array($value) && in_array($option[0], $value) ? ' selected="selected"' : '';
				$s .= '<option value="'.$option[0].'"'.$selected.'>'.($langscript ? lang('block/'.$langscript, $option[1]) : $option[1]).'</option>';
			}
			$s .= '</select>';
		} elseif($type == 'calendar') {
			if(! $calendar_loaded) {
				$s .= "<script type=\"text/javascript\" src=\"{$_G[setting][jspath]}calendar.js?".VERHASH."\"></script>";
				$calendar_loaded = true;
			}
			$s .= '<input type="text" name="'.$varname.'" class="px" value="'.dhtmlspecialchars($value).'" onclick="showcalendar(event, this, true)" />';
		} elseif($type == 'district') {
			include_once libfile('function/profile');
			$elems = $vals = array();
			$districthtml = '';
			foreach($setting['value'] as $fieldid) {
				$elems[] = 'parameter['.$fieldid.']';
				$vals[$fieldid] = $values[$fieldid];
				if(!empty($values[$fieldid])) {
					$districthtml .= $values[$fieldid].'<input type="hidden" name="parameter['.$fieldid.']" value="'.$values[$fieldid].'" /> ';
				}
			}
			$containertype = strpos($setting['title'], 'birthcity') !== false ? 'birth' : 'reside';
			$containerid = 'randomid_'.(++$randomid);
			if($districthtml) {
				$s .= $districthtml;
				$s .= '&nbsp;&nbsp;<a href="javascript:;" onclick="showdistrict(\''.$containerid.'\', ['.dimplode($elems).'], '.count($elems).', \'\', \''.$containertype.'\'); return false;">'.lang('spacecp', 'profile_edit').'</a>';
				$s .= '<p id="'.$containerid.'"></p>';
			} else {
				$s .= "<div id=\"$containerid\">".showdistrict($vals, $elems, $containerid, null, $containertype).'</div>';
			}
		} elseif($type == 'file') {
			$s .= '<input type="'.$type.'" name="'.$varname.'" class="pf" value="'.dhtmlspecialchars($value).'" />';
		} elseif($type == 'mfile') {
			$s .= '<label for="'.$settingvar.'way_remote"'.' class="lb"><input type="radio" name="'.$settingvar.'_chk" id="'.$settingvar.'way_remote" class="pr" onclick="showpicedit(\''.$settingvar.'\');" checked="checked">'.lang('portalcp', 'remote').'</label>';
			$s .= '<label for="'.$settingvar.'way_upload"'.' class="lb"><input type="radio" name="'.$settingvar.'_chk" id="'.$settingvar.'way_upload" class="pr" onclick="showpicedit(\''.$settingvar.'\');">'.lang('portalcp', 'upload').'</label><br />';
			$s .= '<input type="text" name="'.$varname.'" id="'.$settingvar.'_remote" class="px" value="'.dhtmlspecialchars($value).'" />';
			$s .= '<input type="file" name="'.$settingvar.'" id="'.$settingvar.'_upload" class="pf" value="" style="display:none" />';
		} else {
			$s .= $type;
		}
		$return[] = array('title' => $setname, 'html' => $s, 'comment'=>$comment);
	}
	return $return;
}
function block_updateitem($bid, $items=array()) {
	global $_G;
	$block = $_G['block'][$bid];
	if(!$block) {
		if(!($block = C::t('common_block')->fetch($bid))) {
			return false;
		}
		$_G['block'][$bid] = $block;
	}
	$block['shownum'] = max($block['shownum'], 1);
	$showlist = array();
	$archivelist = array();
	$prelist = array();
	$oldvalue = $fixedvalue = $fixedkeys = array();
	foreach(C::t('common_block_item')->fetch_all_by_bid($bid, true) as $value) {
		$key = $value['idtype'].'_'.$value['id'];
		if($value['itemtype'] == '1') {
			$fixedvalue[$value['displayorder']][] = $value;
			$fixedkeys[$key] = 1;
			continue;
		} elseif(!isset($oldvalue[$key])) {
			$oldvalue[$key] = $value;
		} else {
			$archivelist[$value['itemid']] = 1;
		}
	}

	$processkeys = array();
	$itemcount = count($items);
	for($k = 0; $k < $itemcount; $k++) {
		$v = $items[$k];
		$key = $v['idtype'].'_'.$v['id'];
		if(isset($fixedkeys[$key])) {
			$items[$k] = null;
		} elseif(isset($oldvalue[$key]) && !isset($processkeys[$key])) {
			if($oldvalue[$key]['itemtype'] == '2') {
				$items[$k] = $oldvalue[$key];
			} else {
				$items[$k]['itemid'] = $oldvalue[$key]['itemid'];
			}
			unset($oldvalue[$key]);
			$processkeys[$key] = 1;
		} elseif(isset($processkeys[$key])) {
			unset($items[$k]);
		}
	}

	$items = array_filter($items);

	foreach($oldvalue as $value) {
		$archivelist[$value['itemid']] = 1;
	}
	for($i = 1; $i <= $block['shownum']; $i++) {
		$jump = false;
		if(isset($fixedvalue[$i])) {
			foreach($fixedvalue[$i] as $value) {
				if($value['startdate'] > TIMESTAMP) {
					$prelist[] = $value;
				} elseif((!$value['startdate'] || $value['startdate'] <= TIMESTAMP)
						&& (!$value['enddate'] || $value['enddate'] > TIMESTAMP)) {
					$showlist[] = $value;
					$jump = true;
				} else {
					$archivelist[$value['itemid']] = 1;
				}
			}
		}
		if(!$jump) {
			$curitem = array();
			if(!($curitem = array_shift($items))) {
				break;
			}
			$curitem['displayorder'] = $i;

			$curitem['makethumb'] = 0;
			if($block['picwidth'] && $block['picheight'] && $curitem['picflag']) { //picflag=0为url地址
				$thumbpath = empty($curitem['thumbpath']) ? block_thumbpath($block, $curitem) : $curitem['thumbpath'];
				if($_G['setting']['ftp']['on'] && !empty($_G['setting']['ftp']['host'])) {
					if(empty($ftp) || empty($ftp->connectid)) {
						$ftp = & discuz_ftp::instance();
						$ftp->connect();
					}
					if($ftp->ftp_size($thumbpath) > 0) {
						$curitem['makethumb'] = 1;
						$curitem['picflag'] = 2;
					}
				} else if(file_exists($_G['setting']['attachdir'].$thumbpath)) {
					$curitem['makethumb'] = 1;
					$curitem['picflag'] = 1;
				}
				$curitem['thumbpath'] = $thumbpath;
			}
			if(is_array($curitem['fields'])) {
				$curitem['fields'] = serialize($curitem['fields']);
			}

			$showlist[] = $curitem;
		}
	}
	foreach($items as $value) {
		if(!empty($value['itemid'])) {
			$archivelist[$value['itemid']] = 1;
		}
	}
	if($archivelist) {
		$delids = array_keys($archivelist);
		C::t('common_block_item')->delete_by_itemid_bid($delids, $bid);
		block_delete_pic($bid, $delids);
	}
	$inserts = $itemlist = array();
	$itemlist = array_merge($showlist, $prelist);
	C::t('common_block_item')->insert_batch($bid, $itemlist);

	$showlist = array_filter($showlist);
	return $showlist;
}

function block_thumbpath($block, $item) {
	global $_G;
	$hash = md5($item['pic'].'-'.$item['picflag'].':'.$block['picwidth'].'|'.$block['picheight']);
	return 'block/'.substr($hash, 0, 2).'/'.$hash.'.jpg';
}

function block_getclass($classname, $getstyle=false) {
	global $_G;
	if(!isset($_G['cache']['blockclass'])) {
		loadcache('blockclass');
	}
	$theclass = array();
	list($c1, $c2) = explode('_', $classname);
	if(is_array($_G['cache']['blockclass']) && isset($_G['cache']['blockclass'][$c1]['subs'][$classname])) {
		$theclass = $_G['cache']['blockclass'][$c1]['subs'][$classname];
		if($getstyle && !isset($theclass['style'])) {
			foreach(C::t('common_block_style')->fetch_all_by_blockclass($classname) as $value) {
				$value['template'] = !empty($value['template']) ? (array)(dunserialize($value['template'])) : array();
				$value['fields'] = !empty($value['fields']) ? (array)(dunserialize($value['fields'])) : array();
				$key = 'blockstyle_'.$value['styleid'];
				$_G[$key] = $value;
				$theclass['style'][$value['styleid']] = $value;
			}
			$_G['cache']['blockclass'][$c1]['subs'][$classname] = $theclass;
		}
	}
	return $theclass;
}

function block_getdiyurl($tplname, $diymod = false) {
	$mod = $id = $script = $url = '';
	$flag = 0;
	if (empty ($tplname)) {
		$flag = 2;
	} else {
		list($script,$tpl) = explode('/',$tplname);
		if (!empty($tpl)) {
			$arr = array();
			preg_match_all('/(.*)\_(\d{1,9})/', $tpl,$arr);
			$mod = empty($arr[1][0]) ? $tpl : $arr[1][0];
			$id = max(intval($arr[2][0]),0);
			if($script == 'ranklist') {
				$script = 'misc';
				$mod = 'ranklist&type='.$mod;
			} else {
				switch ($mod) {
					case 'index' :
						$mod = 'index';
						break;
					case 'discuz' :
						$flag = 0;
						if($id){
							$mod = 'index&gid='.$id;
						} else {
							$mod = 'index';
						}
						break;
					case 'space_home' :
						$mod = 'space';
						break;
					case 'forumdisplay' :
						$flag = $id ? 0 : 1;
						$mod .= '&fid='.$id;
						break;
					case 'viewthread' :
						$flag = $id ? 0 : 1;
						$mod = 'forumdisplay&fid='.$id;
						break;
					case 'list' :
						$flag = $id ? 0 : 1;
						$mod .= '&catid='.$id;
						break;
					case 'portal_topic_content' :
						$flag = $id ? 0 : 1;
						$mod = 'topic&topicid='.$id;
						break;
					case 'view' :
						$flag = $id ? 0 : 1;
						$mod .= '&aid='.$id;
						break;
					default :
						break;
				}
			}
		}
		$url = empty($mod) || $flag == '1' ? '' : $script.'.php?mod='.$mod.($diymod?'&diy=yes':'');
	}
	return array('url'=>$url,'flag'=>$flag);
}

function block_clear() {
	$uselessbids = $usingbids = $bids = array();
	$bids = C::t('common_block')->fetch_all_bid_by_blocktype(0,1000);
	$usingbids = array_keys(C::t('common_template_block')->fetch_all_by_bid($bids));
	$uselessbids = array_diff($bids, $usingbids);
	if (!empty($uselessbids)) {
		C::t('common_block_item')->delete_by_bid($uselessbids);
		C::t('common_block_item_data')->delete_by_bid($uselessbids);
		C::t('common_block_favorite')->delete_by_bid($uselessbids);
		C::t('common_block_permission')->delete_by_bid_uid_inheritedtplname($uselessbids);
		C::t('common_block')->delete($uselessbids);
		C::t('common_block')->optimize();
		C::t('common_block_item')->optimize();
		block_delete_pic($uselessbids);
	}
}

function block_getstyle($styleids = array()) {
	global $_G;
	static $allowmem = null, $cachettl =null;
	if($allowmem === null) {
		$allowmem = ($cachettl = getglobal('setting/memory/diyblock')) !== null && memory('check');
	}

	$pre = 'blockstyle_';
	if(($ret = $styleids && !is_array($styleids) ? $styleids : false)) {
		if($_G[$pre.$ret]) {
			return $_G[$pre.$ret];
		} else {
			$styleids = (array)$styleids;
		}
	}
	$cacheprekey = 'blockstylecache_';
	$styleids = array_map('intval', $styleids);
	$styleids = array_unique($styleids);

	if($styleids) {
		if($allowmem) {
			if(($cachedata = memory('get', $styleids, $cacheprekey)) !== false) {
				foreach ($cachedata as $styleid => $style) {
					$_G[$pre.$styleid] = $style;
				}
				if(!($styleids = array_diff($styleids, array_keys($cachedata)))) {
					return $ret ? $_G[$pre.$ret] : true;
				}
			}
		}

		if($styleids) {
			foreach(C::t('common_block_style')->fetch_all($styleids) as $styleid => $value) {
				$value['template'] = !empty($value['template']) ? (array)(dunserialize($value['template'])) : array();
				$value['fields'] = !empty($value['fields']) ? (array)(dunserialize($value['fields'])) : array();
				$_G[$pre.$styleid] = $value;
				if($allowmem) {
					memory('set', $cacheprekey.$styleid, $_G[$pre.$styleid], $cachettl);
				}
			}
		}
		return $ret ? $_G[$pre.$ret] : true;
	}
	return array();
}

function blockclass_cache() {
	global $_G;
	$data = $dirs = $styles = $dataconvert = array();
	$dir = DISCUZ_ROOT.'/source/class/block/';
	$dh = opendir($dir);
	while(($filename=readdir($dh))) {
		if(is_dir($dir.$filename) && substr($filename,0,1) != '.') {
			$dirs[$filename] = $dir.$filename.'/';
		}
	}
	ksort($dirs);
	foreach($dirs as $name=>$dir) {
		$blockclass = $blockconvert = array();
		if(file_exists($dir.'blockclass.php')) {
			include_once($dir.'blockclass.php');
		}
		if(empty($blockclass['name'])) {
			$blockclass['name'] = $name;
		} else {
			$blockclass['name'] = dhtmlspecialchars($blockclass['name']);
		}
		$blockclass['subs'] = array();

		$dh = opendir($dir);
		while(($filename = readdir($dh))) {
			$match = $infos = $oneinfo = $fieldsconvert = array();
			$scriptname = $scriptclass = '';
			if(preg_match('/^(block_[\w]+)\.php$/i', $filename, $match)) {
				$scriptclass = $match[1];
				$scriptname = preg_replace('/^block_/i', '', $scriptclass);
				include_once $dir.$filename;
				if(class_exists($scriptclass, false)) {
					$obj = new $scriptclass();
					if(method_exists($obj, 'name') && method_exists($obj, 'blockclass') && method_exists($obj, 'fields')
							&& method_exists($obj, 'getsetting') && method_exists($obj, 'getdata')) {
						if($scriptclass == 'block_xml') {
							foreach($obj->blockdata as $one) {
								$oneinfo['name'] = dhtmlspecialchars($one['data']['name']);
								$oneinfo['blockclass'] = array($one['id'], $oneinfo['name']);
								$oneinfo['fields'] = dhtmlspecialchars($one['data']['fields']);

								foreach($one['data']['style'] as $value) {
									$arr = array(
										'blockclass'=>'xml_'.$one['id'],
										'name' => dhtmlspecialchars($value['name']),
									);
									block_parse_template($value['template'], $arr);
									$styles[$arr['hash']] = $arr;
								}
								$infos[] = $oneinfo;
							}
						} else {
							$oneinfo['name'] = $obj->name();
							$oneinfo['blockclass'] = $obj->blockclass();
							$oneinfo['fields'] = $obj->fields();
							$infos[] = $oneinfo;
						}
					}
					if(method_exists($obj, 'fieldsconvert')) {
						$fieldsconvert = $obj->fieldsconvert();
					}
				}
			}
			foreach($infos as $info) {
				if($info['name'] && is_array($info['blockclass']) && $info['blockclass'][0] && $info['blockclass'][1]) {
					list($key, $title) = $info['blockclass'];
					$key = $name.'_'.$key;
					if(!isset($blockclass['subs'][$key])) {
						$blockclass['subs'][$key] = array(
							'name' => $title,
							'fields' => $info['fields'],
							'script' => array()
						);
					}
					$blockclass['subs'][$key]['script'][$scriptname] = $info['name'];
					if(!isset($blockconvert[$key]) && !empty($fieldsconvert)) {
						$blockconvert[$key] = $fieldsconvert;
					}
				}
			}
		}

		if($blockclass['subs']) {
			$data[$name] = $blockclass;

			$blockstyle = array();
			if(file_exists($dir.'blockstyle.php')) {
				include_once($dir.'blockstyle.php');
			}
			if($blockstyle) {
				foreach($blockstyle as $value) {
					$arr = array(
						'blockclass'=>$name.'_'.$value['blockclass'],
						'name' => $value['name']
					);
					block_parse_template($value['template'], $arr);
					$styles[$arr['hash']] = $arr;
				}
			}
		}

		if(!empty($blockconvert)) {
			$dataconvert[$name] = $blockconvert;
		}

	}

	if($styles) {
		$hashes = array_keys($styles);
		foreach(C::t('common_block_style')->fetch_all_by_hash($hashes) as $value) {
			unset($styles[$value['hash']]);
		}
		if($styles) {
			C::t('common_block_style')->insert_batch($styles);
		}
	}
	savecache('blockclass', $data);
	savecache('blockconvert', $dataconvert);
}

function block_parse_template($str_template, &$arr) {

	$arr['makethumb'] = strexists($str_template, '{picwidth}') ? 1 : 0;
	$arr['getpic'] = strexists($str_template, '{pic}') ? 1 : 0;
	$arr['getsummary'] = strexists($str_template, '{summary}') ? 1 : 0;
	$arr['settarget'] = strexists($str_template, '{target}') ? 1 : 0;
	$arr['moreurl'] = strexists($str_template, '{moreurl}') ? 1 : 0;
	$fields = block_parse_fields($str_template);
	$arr['fields'] = serialize($fields);

	$template = array();
	$template['raw'] = $str_template;
	$template['header'] = $template['footer'] = '';
	$template['loop'] = $template['loopplus'] = $template['order'] = $template['orderplus'] = $template['index'] = $template['indexplus'] = array();

	$match = array();
	if(preg_match('/\[loop\](.*?)\[\/loop]/is', $str_template, $match)) {
		$template['loop'] = trim($match[1]);
	}
	$match = array();
	if(preg_match_all('/\[(loop\d)\](.*?)\[\/\\1]/is', $str_template, $match)) {
		foreach($match[1] as $key=>$value) {
			$content = trim($match[2][$key]);
			$k = intval(str_replace('loop', '', $value));
			$template['loopplus'][$k] = $content;
		}
	}
	$match = array();
	if(preg_match_all('/\[order=(\d+|odd|even)\](.*?)\[\/order]/is', $str_template, $match)) {
		foreach($match[1] as $key => $order) {
			$template['order'][$order] = trim($match[2][$key]);
		}
	}
	$match = array();
	if(preg_match_all('/\[(order\d+)=(\d+|odd|even)\](.*?)\[\/\\1]/is', $str_template, $match)) {
		foreach($match[1] as $key=>$value) {
			$content = trim($match[3][$key]);
			$order = $match[2][$key];
			$k = intval(str_replace('order', '', $value));
			$template['orderplus'][$k][$order] = $content;
		}
	}
	$match = array();
	if(preg_match_all('/\[index=(\d+)\](.*?)\[\/index]/is', $str_template, $match)) {
		foreach($match[1] as $key=>$order) {
			$template['index'][$order] = trim($match[2][$key]);
		}
	}
	$match = array();
	if(preg_match_all('/\[(index\d+)=(\d+)\](.*?)\[\/\\1]/is', $str_template, $match)) {
		foreach($match[1] as $key=>$value) {
			$content = trim($match[3][$key]);
			$order = intval($match[2][$key]);
			$k = intval(str_replace('index', '', $value));
			$template['indexplus'][$k][$order] = $content;
		}
	}
	$arr['template'] = serialize($template);
	$arr['hash'] = substr(md5($arr['blockclass'].'|'.$arr['template']), 8, 8);
}

function block_parse_fields($template) {
	$fields = array();
	if(preg_match_all('/\{(\w+)\}/', $template, $matches)) {
		foreach($matches[1] as $fieldname) {
			$fields[] = $fieldname;
		}
		$fields = array_unique($fields);
		$fields = array_diff($fields, array('picwidth', 'picheight', 'target', ''));
		$fields = array_values($fields);
	}
	return $fields;
}

function block_build_template($template) {
	if(! is_array($template)) {
		return $template;
	}
	if(!empty($template['raw'])) {
		return $template['raw'];
	}
	$str_template = $template['header'];
	if($template['loop']) {
		$str_template .= "\n[loop]\n{$template['loop']}\n[/loop]";
	}
	if(!empty($template['order']) && is_array($template['order'])) {
		foreach($template['order'] as $key=>$value) {
			$str_template .= "\n[order={$key}]\n{$value}\n[/order]";
		}
	}
	$str_template .= $template['footer'];
	return $str_template;
}

function block_isrecommendable($block) {
	return !empty($block) && in_array($block['blockclass'], array('forum_thread', 'group_thread', 'portal_article', 'space_pic', 'space_blog')) ? true : false;
}

function block_delete_pic($bid, $itemid = array()) {
	global $_G;
	if(!empty($bid)) {
		$picids = array();
		foreach(C::t('common_block_pic')->fetch_all_by_bid_itemid($bid, $itemid) as $value) {
			$picids[$value['picid']] = $value['picid'];
			if($value['picflag']) {
				ftpcmd('delete', $value['pic']);
			} else {
				@unlink($_G['setting']['attachdir'].'/'.$value['pic']);
			}
		}
		if(!empty($picids)) {
			C::t('common_block_pic')->delete($picids);
		}
	}
}

function update_template_block($targettplname, $tpldirectory, $blocks) {
	if(!empty($targettplname)) {
		if(empty($blocks)) {
			C::t('common_template_block')->delete_by_targettplname($targettplname, $tpldirectory);
		} else {
			$oldbids = array();
			$oldbids = array_keys(C::t('common_template_block')->fetch_all_by_targettplname($targettplname, $tpldirectory));
			$newaddbids = array_diff($blocks, $oldbids);
			C::t('common_template_block')->delete_by_targettplname($targettplname, $tpldirectory);
			if($tpldirectory === './template/default') {
				C::t('common_template_block')->delete_by_targettplname($targettplname, '');
			}
			$blocks = array_unique($blocks);
			C::t('common_template_block')->insert_batch($targettplname, $tpldirectory, $blocks);
			if(!empty($newaddbids)) {
				require_once libfile('class/blockpermission');
				$tplpermission = & template_permission::instance();
				$tplpermission->add_blocks($targettplname, $newaddbids);
			}
		}
	}
}
?>