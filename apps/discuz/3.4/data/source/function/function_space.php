<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_space.php 34052 2013-09-25 06:18:43Z andyzheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function getblockhtml($blockname,$parameters = array()) {
	global $_G, $space;

	$parameters = empty($parameters) ? array() : $parameters;
	$list = array();
	$sql = $title = $html = $wheresql = $ordersql = $titlemore = $do = $contentclassname = '';
	$view = $from = false;
	$contenttagname = 'div';
	$shownum = 6;

	$uid = intval($space['uid']);

	$shownum = empty($parameters['shownum']) ? $shownum : intval($parameters['shownum']);
	switch ($blockname) {
		case 'personalinfo':
			$do = 'profile';
			space_merge($space, 'profile');

			require_once libfile('function/friend');
			$isfriend = friend_check($space['uid']);
			require_once libfile('function/spacecp');

			loadcache('profilesetting');
			include_once libfile('function/profile');
			$profiles = array();
			$privacy = $space['privacy']['profile'] ? $space['privacy']['profile'] : array();

			foreach($_G['cache']['profilesetting'] as $fieldid=>$field) {
				if($_G['setting']['nsprofiles']) {
					break;
				}
				if(!$field['available'] || in_array($fieldid, array('birthprovince', 'birthdist', 'birthcommunity', 'resideprovince', 'residedist', 'residecommunity'))) {
					continue;
				}
				if(
					$field['available'] && $field['invisible'] != '1' && strlen($space[$fieldid]) > 0 &&
					(
						$field['showinthread'] ||
						$field['showincard'] ||
						(
							$space['self'] || empty($privacy[$fieldid]) || ($isfriend && $privacy[$fieldid] == 1)
						)
					)
				) {
					$val = profile_show($fieldid, $space);
					if($val !== false) {
						if($fieldid == 'realname' && $_G['uid'] != $space['uid'] && !ckrealname(1)) {
							continue;
						}
						if($field['formtype'] == 'file' && $val) {
							$imgurl = getglobal('setting/attachurl').'./profile/'.$val;
							$val = '<span><a href="'.$imgurl.'" target="_blank"><img src="'.$imgurl.'"  style="max-width: 300px;" /></a></span>';
						}
						if ($val == '')  $val = '';
						$html .= '<li><em>'.$field['title'].'</em>'.$val.'</li>';
					}
				}
			}
			$html = $html ? $html : '<li>'.lang('space', 'block_view_profileinfo_noperm').'</li>';
			$html = '<ul id="pprl" class="mbm pbm bbda cl">'.$html.$more.'</ul>';
			$more = lang('space', 'block_profile_all', array('uid' => $uid));
			$html = $html.$more;
			$titlemore = $space['self'] ? lang('space', 'block_profile_edit') : '';
			break;
		case 'profile':
			$do = $blockname;
			$managehtml = '';
			$avatar = empty($parameters['banavatar']) ? 'middle' : $parameters['banavatar'];
			$html .= "<div class=\"hm\"><p><a href=\"home.php?mod=space&uid=$uid\" target=\"_blank\">".avatar($uid,$avatar).'</a></p>';

			$memberfieldforum = C::t('common_member_field_forum')->fetch($space['uid']);
			$space['medals'] = $memberfieldforum['medals'];
			unset($memberfieldforum);
			$usermedals = $medal_detial = $usermedalmenus = '';
			if($space['medals']) {
				loadcache('medals');
				foreach($space['medals'] = explode("\t", $space['medals']) as $key => $medalid) {
					list($medalid, $medalexpiration) = explode("|", $medalid);
					if(isset($_G['cache']['medals'][$medalid]) && (!$medalexpiration || $medalexpiration > TIMESTAMP)) {
						$usermedals .= '<img src="'.STATICURL.'image/common/'.$_G['cache']['medals'][$medalid]['image'].'" id="md_'.$medalid.'" alt="'.$_G['cache']['medals'][$medalid]['name'].'\'" onmouseover="showMenu({\'ctrlid\':this.id, \'menuid\':\'md_'.$medalid.'_menu\', \'pos\':\'12!\'});" />&nbsp;';
						$usermedalmenus .= '
						<div id="md_'.$medalid.'_menu" class="tip tip_4" style="display: none;">
							<div class="tip_horn"></div>
							<div class="tip_c">
								<h4>'.$_G['cache']['medals'][$medalid]['name'].'</h4>
								<p>'.$_G['cache']['medals'][$medalid]['description'].'</p>
							</div>
						</div>';
					}
				}
				if($usermedals) {
					$usermedals = '<p class="md_ctrl"><a href="home.php?mod=medal">'.$usermedals.'</a></p>'.$usermedalmenus;
				}
			}
			$html .= "<h2 class=\"mbn\"><a href=\"home.php?mod=space&uid=$uid\" target=\"_blank\">".$space['username']."</a></h2>$usermedals";
			$html .= '</div><ul class="xl xl2 cl ul_list">';

			$magicinfo = $showmagicgift = false;
			if($_G['setting']['magicstatus'] && $_G['setting']['magics']['gift']) {
				$showmagicgift = true;
				$magicinfo = !empty($space['magicgift']) ? dunserialize($space['magicgift']) : array();
			}
			if(helper_access::check_module('follow')) {
				$html .= '<li class="ul_broadcast"><a href="home.php?mod=space&uid='.$uid.'">'.lang('space', 'block_profile_follow').'</a></li>';
			}
			if ($space['self']) {
				$html .= '<li class="ul_diy"><a href="home.php?mod=space&do=index&diy=yes">'.lang('space', 'block_profile_diy').'</a></li>';
				$html .= '<li class="ul_msg"><a href="home.php?mod=space&uid='.$uid.'&do=wall">'.lang('space', 'block_profile_wall').'</a></li>';
				$html .= '<li class="ul_avt"><a href="home.php?mod=spacecp&ac=avatar">'.lang('space', 'block_profile_avatar').'</a></li>';
				$html .= '<li class="ul_profile"><a href="home.php?mod=spacecp&ac=profile">'.lang('space', 'block_profile_update').'</a></li>';
				if($showmagicgift) {
					$html .= '<li class="ul_magicgift"><div style="'.'background: url('.STATICURL.'image/magic/gift.small.gif) no-repeat 0 50%;'.'">';
					if($magicinfo) {
						$html .= '<a onclick="showWindow(\'magicgift\', this.href, \'get\', 0)" href="home.php?mod=spacecp&ac=magic&op=retiregift">'.lang('magic/gift', 'gift_gc').'</a>';
					} else {
						$html .= '<a onclick="showWindow(\'magicgift\', this.href, \'get\', 0)" href="home.php?mod=magic&mid=gift">'.lang('magic/gift', 'gift_use').'</a>';
					}
					$html .= '</div></li>';
				}
			} else {
				require_once libfile('function/friend');
				$isfriend = friend_check($uid);
				$follow = C::t('home_follow')->fetch_by_uid_followuid($_G['uid'], $uid);
				if($follow) {
					$html .= "<li class='ul_flw'><a href=\"home.php?mod=spacecp&ac=follow&op=del&fuid=$space[uid]\" id=\"followmod\" onclick=\"showWindow(this.id, this.href, 'get', 0);\">".lang('space', 'follow_cancle_follow')."</a></li>";
				} else {
					$html .= "<li class='ul_flw'><a href=\"home.php?mod=spacecp&ac=follow&op=add&hash=".FORMHASH."&fuid=$space[uid]\" id=\"followmod\" onclick=\"showWindow(this.id, this.href, 'get', 0);\">".lang('space', 'follow_follow_ta')."</a></li>";
				}
				if (!$isfriend) {
					$html .= "<li class='ul_add'><a href=\"home.php?mod=spacecp&ac=friend&op=add&uid=$space[uid]&handlekey=addfriendhk_{$space[uid]}\" id=\"a_friend_li_{$space[uid]}\" onclick=\"showWindow(this.id, this.href, 'get', 0);\">".lang('space', 'block_profile_friend_add')."</a></li>";
				} else {
					$html .= "<li class='ul_ignore'><a href=\"home.php?mod=spacecp&ac=friend&op=ignore&uid=$space[uid]&handlekey=ignorefriendhk_{$space[uid]}\" id=\"a_ignore_{$space[uid]}\" onclick=\"showWindow(this.id, this.href, 'get', 0);\">".lang('space', 'block_profile_friend_ignore')."</a></li>";
				}
				$html .= "<li class='ul_msg'><a href=\"home.php?mod=space&uid=$space[uid]&do=wall\">".lang('space', 'block_profile_wall_to_me')."</a></li>";
				$html .= "<li class='ul_poke'><a href=\"home.php?mod=spacecp&ac=poke&op=send&uid=$space[uid]&handlekey=propokehk_{$space[uid]}\" id=\"a_poke_{$space[uid]}\" onclick=\"showWindow(this.id, this.href, 'get', 0);\">".lang('space', 'block_profile_poke')."</a></li>";
				$html .= "<li class='ul_pm'><a href=\"home.php?mod=spacecp&ac=pm&op=showmsg&handlekey=showmsg_$space[uid]&touid=$space[uid]&pmid=0&daterange=2\" id=\"a_sendpm_$space[uid]\" onclick=\"showWindow('showMsgBox', this.href, 'get', 0)\">".lang('space', 'block_profile_sendmessage')."</a></li>";
			}

			$html .= '</ul>';

			$encodeusername = rawurlencode($space['username']);

			if(checkperm('allowbanuser')) {
				$managehtml .= '<li><a href="'.($_G['adminid'] == 1 ? "admin.php?action=members&operation=ban&username=$encodeusername&frames=yes" : "forum.php?mod=modcp&action=member&op=ban&uid=$space[uid]").'" id="usermanageli" onmouseover="showMenu(this.id)" class="showmenu" target="_blank">'.lang('home/template', 'member_manage').'</a></li>';
			} elseif (checkperm('allowedituser')) {
				$managehtml .= '<li><a href="'.($_G['adminid'] == 1 ? "admin.php?action=members&operation=search&username=$encodeusername&submit=yes&frames=yes" : "forum.php?mod=modcp&action=member&op=edit&uid=$space[uid]").'" id="usermanageli" onmouseover="showMenu(this.id)" class="showmenu" target="_blank">'.lang('home/template', 'member_manage').'</a></li>';
			}
			if($_G['adminid'] == 1) {
				$managehtml .= "<li><a href=\"forum.php?mod=modcp&action=thread&op=post&do=search&searchsubmit=1&users=$encodeusername\" id=\"umanageli\" onmouseover=\"showMenu(this.id)\" class=\"showmenu\">".lang('home/template', 'content_manage')."</a></li>";
			}
			if(!empty($managehtml)) {
				$html .= '<hr class="da mtn m0" /><ul class="ptn xl xl2 cl">'.$managehtml.'</ul><ul id="usermanageli_menu" class="p_pop" style="width: 80px; display:none;">';
				if(checkperm('allowbanuser')) {
					$html .= '<li><a href="'.($_G['adminid'] == 1 ? "admin.php?action=members&operation=ban&username=$encodeusername&frames=yes" : "forum.php?mod=modcp&action=member&op=ban&uid=$space[uid]").'" target="_blank">'.lang('home/template', 'user_ban').'</a></li>';
				}
				if (checkperm('allowedituser')) {
					$html .= '<li><a href="'.($_G['adminid'] == 1 ? "admin.php?action=members&operation=search&username=$encodeusername&submit=yes&frames=yes" : "forum.php?mod=modcp&action=member&op=edit&uid=$space[uid]").'" target="_blank">'.lang('home/template', 'user_edit').'</a></li>';
				}
				$html .= '</ul>';
				if($_G['adminid'] == 1) {
					$html .= '<ul id="umanageli_menu" class="p_pop" style="width: 80px; display:none;">';
					$html .= '<li><a href="forum.php?mod=modcp&action=thread&op=post&searchsubmit=1&do=search&users='.$encodeusername.'" target="_blank">'.lang('space', 'manage_post').'</a></li>';
					$html .= '<li><a href="admin.php?action=doing&searchsubmit=1&detail=1&search=true&fromumanage=1&users='.$encodeusername.'" target="_blank">'.lang('space', 'manage_doing').'</a></li>';
					$html .= '<li><a href="admin.php?action=blog&searchsubmit=1&detail=1&search=true&fromumanage=1&uid='.$uid.'" target="_blank">'.lang('space', 'manage_blog').'</a></li>';
					$html .= '<li><a href="admin.php?action=feed&searchsubmit=1&detail=1&fromumanage=1&uid='.$uid.'" target="_blank">'.lang('space', 'manage_feed').'</a></li>';
					$html .= '<li><a href="admin.php?action=album&searchsubmit=1&detail=1&search=true&fromumanage=1&uid='.$uid.'" target="_blank">'.lang('space', 'manage_album').'</a></li>';
					$html .= '<li><a href="admin.php?action=pic&searchsubmit=1&detail=1&search=true&fromumanage=1&users='.$encodeusername.'" target="_blank">'.lang('space', 'manage_pic').'</a></li>';
					$html .= '<li><a href="admin.php?action=comment&searchsubmit=1&detail=1&fromumanage=1&authorid='.$uid.'" target="_blank">'.lang('space', 'manage_comment').'</a></li>';
					$html .= '<li><a href="admin.php?action=share&searchsubmit=1&detail=1&search=true&fromumanage=1&uid='.$uid.'" target="_blank">'.lang('space', 'manage_share').'</a></li>';
					$html .= '<li><a href="admin.php?action=threads&operation=group&searchsubmit=1&detail=1&search=true&fromumanage=1&users='.$encodeusername.'" target="_blank">'.lang('space', 'manage_group_threads').'</a></li>';
					$html .= '<li><a href="admin.php?action=prune&operation=group&searchsubmit=1&detail=1&fromumanage=1&users='.$encodeusername.'" target="_blank">'.lang('space', 'manage_group_prune').'</a></li>';
					$html .= '</ul>';
				}
			}
			if($_G['setting']['magicstatus'] && $_G['setting']['magics']['gift']) {
				$info = !empty($space['magicgift']) ? dunserialize($space['magicgift']) : array();
				if($space['self']) {

				} elseif($info) {
					if($info['left'] && !in_array($_G['uid'], (array)$info['receiver'])) {
						$percredit = min($info['percredit'], $info['left']);
						if($info['credittype']=='credits') {
							$credittype = lang('core', 'title_credit');
						} else {
							$extcredits = str_replace('extcredits', '', $info['credittype']);
							$credittype = $_G['setting']['extcredits'][$extcredits]['title'];
						}
						$html .= '<div id="magicreceivegift">';
						$html .= '<a onclick="showWindow(\'magicgift\', this.href, \'get\', 0)" href="home.php?mod=spacecp&ac=magic&op=receivegift&uid='.$uid.'" title="'.lang('magic/gift', 'gift_receive_gift', array('percredit'=>$percredit,'credittype'=>$credittype)).'">';
						$html .= '<img src="'.STATICURL.'image/magic/gift.gif" alt="gift" />';
						$html .= '</a>';
						$html .= '</div>';
					}
				}
			}
			$html = '<div>'.$html.'</div>';
			break;
		case 'statistic':
			space_merge($space, 'count');
			$html .= '<p class="mbm xw1">';
			if(empty($parameters['banviews'])) $html .= lang('space', 'space_views', array('views' => $space['views'] ? $space['views'] : '--'));
			$html .= '</p><ul class="xl xl2 cl">';

			if(empty($parameters['bancredits'])) {
				$html .= "<li>".lang('space', 'credits').': <a href="home.php?mod=spacecp&ac=credit">'.($space['credits'] ? $space['credits'] : '--')."</a></li>";
				foreach($_G['setting']['extcredits'] as $extcreditid => $extcredit) {
					$html .= "<li>".($extcredit['img'] ? $extcredit['img'].' ' : '').$extcredit['title'].': <a href="home.php?mod=spacecp&ac=credit">'.($space['extcredits'.$extcreditid] ? $space['extcredits'.$extcreditid] : '--').'</a>';
				}
			}
			if(empty($parameters['banfriends'])) $html .= "<li>".lang('space', 'friends').': <a href="home.php?mod=space&uid='.$uid.'&do=friend&view=me&from=space">'.($space['friends'] ? $space['friends'] : '--')."</a></li>";
			if(empty($parameters['banthreads']) && $_G['setting']['allowviewuserthread'] !== -1 || $_G['adminid'] == 1) {
				$html .= "<li>".lang('space', 'threads').': <a href="home.php?mod=space&uid='.$uid.'&do=thread&view=me&from=space">'.($space['threads'] ? $space['threads'] : '--')."</a></li>";
			}
			if(empty($parameters['banblogs'])) $html .= "<li>".lang('space', 'blogs').': <a href="home.php?mod=space&uid='.$uid.'&do=blog&view=me&from=space">'.($space['blogs'] ? $space['blogs'] : '--')."</a></li>";
			if(empty($parameters['banalbums'])) $html .= "<li>".lang('space', 'albums').': <a href="home.php?mod=space&uid='.$uid.'&do=album&view=me&from=space">'.($space['albums'] ? $space['albums'] : '--')."</a></li>";
			if(empty($parameters['bansharings'])) $html .= "<li>".lang('space', 'sharings').': <a href="home.php?mod=space&uid='.$uid.'&do=share&view=me&from=space">'.($space['sharings'] ? $space['sharings'] : '--')."</a></li>";
			$html .= '</ul>';
			$html = '<div>'.$html.'</div>';
			break;

		case 'doing':
			$do = $blockname;
			$view = 'me';
			$from = 'space';
			if(ckprivacy('doing', 'view')) {
				$dolist = array();
				$query = C::t('home_doing')->fetch_all_by_uid_doid(array($uid), '', 'dateline', 0, $shownum, false, true);
				foreach ($query as $value) {
					if($value['status'] == 0 || $value['uid'] == $_G['uid']) {
						$dolist[] = $value;
					}
				}

				if ($dolist) {
					foreach($dolist as $dv) {
						$doid = $dv['doid'];
						$_GET[key] = $key = random(8);
						$html .= "<li class=\"pbn bbda\">";
						$html .= $dv['message'];
						$html .= "&nbsp;<a href=\"home.php?mod=space&uid=$dv[uid]&do=doing&view=me&from=space&doid=$dv[doid]\" target=\"_blank\" class=\"xg1\">".lang('space', 'block_doing_reply')."</a>";
						$html .= "</li>";
					}
				} else {
					$html .= "<p class=\"emp\">".lang('space', 'block_doing_no_content').($space['self'] ? lang('space', 'block_doing_no_content_publish', $space) : '')."</p>";
				}
			} else {
				$html .= "<p class=\"emp\">".lang('space', 'block_view_noperm')."</p>";
			}
			$html = '<ul class="xl">'.$html.'</ul>';
			break;

		case 'stickblog' :
			space_merge($space, 'profile');
			$stickblogs = explode(',', $space['stickblogs']);
			if(!empty($stickblogs)) {
				$bids = array_slice($stickblogs, 0, $shownum);
				if(count($bids)) {
					if(!isset($parameters['showmessage'])) $parameters['showmessage'] = 150;
					$data_blog = C::t('home_blog')->fetch_all($bids);
					if($parameters['showmessage'] > 0) {
						$data_blogfield = C::t('home_blogfield')->fetch_all($bids);
					}
					foreach($data_blog as $curblogid => $value) {
						$value = array_merge($value, (array)$data_blogfield[$curblogid]);
						if(ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
							if($value['pic']) $value['pic'] = pic_cover_get($value['pic'], $value['picflag']);
							$value['message'] = $value['friend'] == 4 ? '' : getstr($value['message'], $parameters['showmessage'], 0, 0, 0, -1);
							$html .= lang('space', 'blog_li', array(
									'uid' => $value['uid'],
									'blogid' => $value['blogid'],
									'subject' => $value['subject'],
									'date' => dgmdate($value['dateline'],'Y-m-d')));
							if(!empty($parameters['showmessage'])) {
								if ($value['pic']) {
									$html .= lang('space', 'blog_li_img', array(
											'uid' => $value['uid'],
											'blogid' => $value['blogid'],
											'src' => $value['pic']));
								}
								$html .= "<dd>$value[message]</dd>";
							}
							$html .= lang('space', 'blog_li_ext', array('uid'=>$value['uid'],'blogid'=>$value['blogid'],'viewnum'=>$value['viewnum'],'replynum'=>$value['replynum']));
							$html .= "</dl>";
						} else {
							$html .= '<p>'.lang('space','block_view_noperm').'</p>';
						}
					}
				}
			}
			$more = $html ? '<p class="ptm" style="text-align: right;"><a href="home.php?mod=space&uid='.$uid.'&do=blog&view=me&from=space">'.lang('space', 'viewmore').'</a></p>' : '';
			$contentclassname = ' xld';
			$html = $html.$more;
			break;
		case 'blog':
			$do = $blockname;
			$view = 'me';
			$from = 'space';
			if(!isset($parameters['showmessage'])) $parameters['showmessage'] = 150;
			$data_blog = C::t('home_blog')->fetch_all_by_uid($uid, 'dateline', 0, $shownum);
			$blogids = array_keys($data_blog);
			$data_blogfield = C::t('home_blogfield')->fetch_all($blogids);
			foreach($data_blog as $curblogid => $value) {
				$value = array_merge($value, (array)$data_blogfield[$curblogid]);
				if(ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
					if($value['pic']) $value['pic'] = pic_cover_get($value['pic'], $value['picflag']);
					$value['message'] = $value['friend'] == 4 ? '' : getstr($value['message'], $parameters['showmessage'], 0, 0, 0, -1);
					$html .= lang('space', 'blog_li', array(
							'uid' => $value['uid'],
							'blogid' => $value['blogid'],
							'subject' => $value['subject'],
							'date' => dgmdate($value['dateline'],'Y-m-d')));
					if(!empty($parameters['showmessage'])) {
						if ($value['pic']) {
							$html .= lang('space', 'blog_li_img', array(
									'uid' => $value['uid'],
									'blogid' => $value['blogid'],
									'src' => $value['pic']));
						}
						$html .= "<dd>$value[message]</dd>";
					}
					$html .= lang('space', 'blog_li_ext', array('uid'=>$value['uid'],'blogid'=>$value['blogid'],'viewnum'=>$value['viewnum'],'replynum'=>$value['replynum']));
					$html .= "</dl>";
				} else {
					$html .= '<p>'.lang('space','block_view_noperm').'</p>';
				}
			}
			if($html) {
				$more = '<p class="ptm" style="text-align: right;"><a href="home.php?mod=space&uid='.$uid.'&do=blog&view=me&from=space">'.lang('space', 'viewmore').'</a></p>';
			} else {
				$html = '<p class="emp">'.lang('space','block_blog_no_content').($space['self'] ? lang('space', 'block_blog_no_content_publish', $space) : '').'</p>';
				$more = '';
			}
			$contentclassname = ' xld';
			$html = $html.$more;
			break;
		case 'album':
			$do = $blockname;
			$view = 'me';
			$from = 'space';
			if(ckprivacy('album', 'view')) {
				$query = C::t('home_album')->fetch_all_by_uid($uid, 'updatetime', 0, $shownum);
				foreach($query as $value) {
					if(ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
						$value['pic'] = pic_cover_get($value['pic'], $value['picflag']);
						$html .= lang('space', 'album_li', array(
								'albumid' =>$value['albumid'],
								'src' => $value['pic'],
								'albumname' => $value['albumname'],
								'uid' => $value['uid'],
								'picnum' => $value['picnum'],
								'date' => dgmdate($value['updatetime'],'n-j')
						));
					}
				}
				if(!$html) {
					$html = '<p class="emp">'.lang('space','block_album_no_content').($space['self'] ? lang('space', 'block_album_no_content_publish', $space) : '').'</p>';
				}
			} else {
				$html .= '<li>'.lang('space','block_view_noperm').'</li>';
			}
			$html = '<ul class="ml cl">'.$html.'</ul>';
			break;

		case 'feed':
			$do = 'home';
			$view = 'me';
			$from = 'space';
			if(!IS_ROBOT && ckprivacy('feed', 'view')) {
				require_once libfile('function/feed');
				$query = C::t('home_feed')->fetch_all_by_uid_dateline($uid, false, 0, $shownum);
				foreach($query as $value) {
					if(ckfriend($value['uid'], $value['friend'], $value['target_ids'])) {
						$html .=  mkfeedhtml(mkfeed($value));
					}
				}
			}
			$contenttagname = 'ul';
			$contentclassname = ' el';
			$html = !$html ?  '<p class="emp">'.lang('space','block_feed_no_content').'</p>' : $html;
			break;
		case 'thread':
			$do = $blockname;
			$view = 'me';
			$from = 'space';
			if ($_G['setting']['allowviewuserthread'] !== -1) {
				$viewfids = str_replace("'", '', $_G['setting']['allowviewuserthread']);
				if(!empty($viewfids)) {
					$viewfids = explode(',', $viewfids);
				}

				foreach(C::t('forum_thread')->fetch_all_by_authorid_displayorder($uid, 0, '>=', null, '', 0, $shownum, null, $viewfids ? $viewfids : null) as $thread) {
					if($thread['author']) {
						$html .= "<li><a href=\"forum.php?mod=viewthread&tid={$thread['tid']}\" target=\"_blank\">{$thread['subject']}</a></li>";
					}
				}
			}
			$html = !$html ? '<p class="emp">'.lang('space','block_thread_no_content').($space['self'] ? lang('space', 'block_thread_no_content_publish', $space) : '').'</p>' : '<ul class="xl">'.$html.'</ul>';
			break;
		case 'friend':
			$do = $blockname;
			$view = 'me';
			$from = 'space';
			require_once libfile('function/friend');

			$friendlist = array();
			$friendlist = friend_list($uid, $shownum);

			$fuids = array_keys($friendlist);
			getonlinemember($fuids);

			foreach ($friendlist as $key => $value) {
				$classname = $_G['ols'][$value['fuid']]?'gol':'';
				$html .= '<li><a href="home.php?mod=space&uid='.$value['fuid'].'" target="_blank" class="avt"><em class="'.$classname.'"></em>'.avatar($value['fuid'],'small').'</a><p><a href="home.php?mod=space&uid='.$value[fuid].'" target="_blank">'.$value['fusername'].'</a></p></li>';
			}
			$html = !$html ? '<p class="emp">'.lang('space','block_friend_no_content').($space['self'] ? lang('space', 'block_friend_no_content_publish', $space) : '').'</p>' : '<ul class="ml mls cl">'.$html.'</ul>';
			break;
		case 'visitor':
			if($space['self']) {
				$do = 'friend';
				$view = 'visitor';
			}

			$list = $fuids = array();
			foreach(C::t('home_visitor')->fetch_all_by_uid($uid, $shownum) as $value) {
				$list[] = $value;
				$fuids[] = $value['vuid'];
			}

			getonlinemember($fuids);

			foreach($list as $value) {
				$html .= "<li>";
				if ($value['vusername'] == '') {
					$html .= lang('space', 'visitor_anonymity');
				} else {
					$html .= lang('space', 'visitor_list', array(
							'uid' => $value['vuid'],
							'cuid' => $uid,
							'username' => $value['vusername'],
							'class' => ($_G['ols'][$value['vuid']]?'gol':''),
							'self' => $value['vuid'] == $_G['uid'] ? 'god' : '',
							'avatar' => avatar($value['vuid'],'small')));
				}
				$html .= "<span class=\"xg2\">".dgmdate($value['dateline'],'u', '9999', 'Y-m-d')."</span>";
				$html .= "</li>";
			}
			$html = !$html ? '<p class="emp">'.lang('space','block_visitor_no_content').($space['self'] ? lang('space', 'block_visitor_no_content_publish', $space) : '').'</p>' : '<ul class="ml mls cl">'.$html.'</ul>';
			break;
		case 'share':
			$do = $blockname;
			$view = 'me';
			$from = 'space';
			if(!IS_ROBOT && ckprivacy('share', 'view')) {
				require_once libfile('function/share');

				foreach(C::t('home_share')->fetch_all_by_uid($uid, 0, $shownum) as $value) {
					$value = mkshare($value);

					$html .= '<li><em><a href="home.php?mod=space&uid='.$value['uid'].'&do=share&id='.$value['sid'].'">'.$value['title_template'].'</a>('.dgmdate($value['dateline'], 'u').')</em><div class="ec cl">';
					if ($value['image']) {
						$html .= '<a href="'.$value['image_link'].'" target="_blank"><img src="'.$value['image'].'" class="tn" alt="" /></a>';
					}
					$html .= '<div class="d">'.$value['body_template'].'</div>';
					if ($value['type'] == 'video') {
						if(!empty($value['body_data']['imgurl'])) {
							$html .= '<table class="mtm" title="'.lang('space', 'click_play').'" onclick="javascript:showFlash(\''.$value['body_data']['host'].'\', \''.$value['body_data']['flashvar'].'\', this, \''.$value['sid'].'\');"><tr><td class="vdtn hm" style="background: url('.$value['body_data']['imgurl'].') no-repeat"><img src="'.STATICURL.'/image/common/vds.png" alt="'.lang('space', 'click_play').'" /></td></tr></table>';
						} else {
							$html .= "<img src=\"".STATICURL."/image/common/vd.gif\" alt=\"".lang('space', 'click_play')."\" onclick=\"javascript:showFlash('{$value['body_data']['host']}', '{$value['body_data']['flashvar']}', this, '{$value['sid']}');\" class=\"tn\" />";
						}
					}elseif ($value['type'] == 'music') {
						$html .= "<img src=\"".STATICURL."/image/common/music.gif\" alt=\"".lang('space', 'click_play')."\" onclick=\"javascript:showFlash('music', '{$value['body_data']['musicvar']}', this, '{$value['sid']}');\" class=\"tn\" />";
					}elseif ($value['type'] == 'flash') {
						$html .= "<img src=\"".STATICURL."/image/common/flash.gif\" alt=\"".lang('space', 'click_view')."\" onclick=\"javascript:showFlash('flash', '{$value['body_data']['flashaddr']}', this, '{$value['sid']}');\" class=\"tn\" />";
					}

					if ($value['body_general']) {
						$html .= '<div class="quote'.($value['image'] ? 'z' : '')."\"><blockquote>$value[body_general]</blockquote></div>";
					}
					$html .= '</div></li>';
				}
				$html = !$html ? '<p class="emp">'.lang('space','block_share_no_content').'</p>' : '<ul class="el">'.$html.'</ul>';
			}
			break;
		case 'wall':
			$do = $blockname;
			$walllist = array();
			if(ckprivacy('wall', 'view')) {
				$query = C::t('home_comment')->fetch_all_by_id_idtype($uid, 'uid', 0, $shownum, '', 'DESC');
				foreach($query as $value) {
					$value['message'] = strlen($value['message'])>500? getstr($value['message'], 500, 0, 0, 0, -1).' ...':$value['message'];
					if($value['status'] == 0 || $value['authorid'] == $_G['uid']) {
						$walllist[] = $value;
					}
				}
			}

			foreach ($walllist as $key => $value) {
				$op = '';
				if ($value['author']) {
					$author_avatar = '<a href="home.php?mod=space&uid='.$value['authorid'].'" target="_blank">'.avatar($value['authorid'],'small').'</a>';
					$author = '<a href="home.php?mod=space&uid='.$value['authorid'].'" id="author_'.$value['cid'].'" target="_blank">'.$value['author'].'</a>';
				}else {
					$author_avatar = '<img src="static/image/magic/hidden.gif" alt="hidden" />';
					$author = $_G['setting']['anonymoustext'];
				}
				if ($value['authorid']==$_G['uid']) {
					$op .= lang('space', 'wall_edit', array('cid'=>$value['cid']));
				}
				if ($value['authorid']==$_G['uid'] || $space['self'] || checkperm('managecomment')){
					$op .= lang('space', 'wall_del', array('cid'=>$value['cid']));
				}
				if ($value['authorid']!=$_G['uid'] && ($value['idtype'] != 'uid' || $space['self'])) {
					$op .= lang('space', 'wall_reply', array('cid'=>$value['cid']));
				}
				$moderate_need = $value['status'] == 1 ? lang('template', 'moderate_need') : '';
				$date = dgmdate($value['dateline'], 'u');
				$replacearr = array('author'=>$author, 'author_avatar' => $author_avatar, 'moderated' => $moderate_need, 'cid' => $value['cid'], 'message'=> $value['message'] , 'date' => $date, 'op'=> $op);

				$html .= lang('space', 'wall_li', $replacearr);
			}
			$html = !empty($walllist) ? $html.lang('space', 'wall_more', array('uid'=>$uid)) : '<p class="emp">'.lang('space','block_wall_no_content').'</p>';
			$html = '<div class="xld xlda el" id="comment_ul">'.$html.'</div>';
			if(helper_access::check_module('wall')) {
				$html = lang('space', 'wall_form', array('uid' => $uid, 'FORMHASH'=>FORMHASH)).'<hr class="da mtm m0">'.$html;
			}
			$titlemore = '<span class="y xw0"><a href="home.php?mod=space&uid='.$uid.'&do=wall">'.lang('space', 'all').'</a></span>';
			break;
		case 'group':
			require_once libfile('function/group');
			$grouplist = mygrouplist($uid, 'lastupdate', array('f.name', 'ff.icon'), $shownum);
			if(empty($grouplist)) $grouplist = array();
			foreach ($grouplist as $groupid => $group) {
				$group['groupid'] = $groupid;
				$html .= lang('space', 'group_li',$group);
			}
			$html = !$html ? '<p class="emp">'.lang('space','block_group_no_content').($space['self'] ? lang('space', ($_G['group']['allowbuildgroup'] ? 'block_group_no_content_publish' : 'block_group_no_content_join'), $space) : '').'</p>' : '<ul class="ml mls cl">'.$html.'</ul>';
			break;
		case 'music':
			if(!empty($parameters['mp3list'])) {
				$authcode = substr(md5($_G['authkey'].$uid), 6, 16);
				$view = ($_G['adminid'] == 1 && $_G['setting']['allowquickviewprofile']) ? '&view=admin' : '';
				$querystring = urlencode("home.php?mod=space&uid=$uid&do=index&op=getmusiclist&hash=$authcode$view&t=".TIMESTAMP);
				$swfurl = STATICURL.'image/common/mp3player.swf?config='.$querystring;
				if(empty($parameters['config']['height']) && $parameters['config']['height'] !== 0) {
					$parameters['config']['height'] = '200px';
				} else {
					$parameters['config']['height'] .= 'px';
				}
				$html = "<script language=\"javascript\" type=\"text/javascript\">document.write(AC_FL_RunContent('id', 'mp3player', 'name', 'mp3player', 'devicefont', 'false', 'width', '100%', 'height', '".$parameters['config']['height']."', 'src', '$swfurl', 'menu', 'false',  'allowScriptAccess', 'never', 'swLiveConnect', 'true', 'wmode', 'transparent'));</script>";
			} else {
				$html = lang('space', 'music_no_content');
			}
			$html = '<div class="ml mls cl">'.$html.'</div>';
			break;

		case 'myapp':
			$html = '';
			break;
		case 'block1':
		case 'block2':
		case 'block3':
		case 'block4':
		case 'block5':
			if($space['self']) {
				$_G['space_group'] = $_G['group'];
			} elseif(empty($_G['space_group'])) {
				$_G['space_group'] = C::t('common_usergroup_field')->fetch($space['groupid']);
			}
			require_once libfile('function/discuzcode');
			if ($_G['space_group']['allowspacediyimgcode']) {
				if (empty($_G['cache']['smilies']['loaded'])) {
					loadcache(array('smilies', 'smileytypes'));
					foreach($_G['cache']['smilies']['replacearray'] AS $skey => $smiley) {
						$_G['cache']['smilies']['replacearray'][$skey] = '[img]'.$_G['siteurl'].'static/image/smiley/'.$_G['cache']['smileytypes'][$_G['cache']['smilies']['typearray'][$skey]]['directory'].'/'.$smiley.'[/img]';
					}
					$_G['cache']['smilies']['loaded'] = 1;
				}
				$parameters['content'] = preg_replace($_G['cache']['smilies']['searcharray'], $_G['cache']['smilies']['replacearray'], trim($parameters['content']));
			}
			if ($_G['space_group']['allowspacediybbcode'] || $_G['space_group']['allowspacediyimgcode'] || $_G['space_group']['allowspacediyhtml'] ){
				$parameters['content'] = discuzcode($parameters['content'], 1, 0, 1, 0, $_G['space_group']['allowspacediybbcode'], $_G['space_group']['allowspacediyimgcode'], $_G['space_group']['allowspacediyhtml']);
			} else {
				$parameters['content'] = dhtmlspecialchars($parameters['content']);
			}
			$parameters['content'] = nl2br($parameters['content']);
			if (empty ($parameters['content'])) $parameters['content'] = lang('space',$blockname);
			$html .= $parameters['content'];
			break;

		default:
			return false;
	}

	if (isset($parameters['title'])) {
		if(empty($parameters['title'])) {
			$title = '';
		} else {
			$view = $view === false ? '' : '&view='.$view;
			$from = $from === false ? '' : '&from='.$from;
			$bnamelink = $do ? '<a href="home.php?mod=space&uid='.$uid.'&do='.$do.$view.$from.'">'.$parameters['title'].'</a>' : $parameters['title'];
			$title = lang('space', 'block_title', array('bname' => $bnamelink, 'more' => $titlemore));
		}
	} else {
		$view = $view === false ? '' : '&view='.$view;
		$from = $from === false ? '' : '&from='.$from;
		$bnamelink = $do ? '<a href="home.php?mod=space&uid='.$uid.'&do='.$do.$view.$from.'">'.getblockdata($blockname).'</a>' : getblockdata($blockname);
		$title = lang('space', 'block_title', array('bname' => $bnamelink, 'more' => $titlemore));
	}
	$html = $title.'<'.$contenttagname.' id="'.$blockname.'_content" class="dxb_bc'.$contentclassname.'">'.$html.'</'.$contenttagname.'>';

	return $html;
}

function mkfeedhtml($value) {
	global $_G;

	$_GET['uid'] = intval($_GET['uid']);
	$_GET['view'] = dhtmlspecialchars($_GET['view']);
	$html = '';
	$html .= "<li class=\"cl $value[magic_class]\" id=\"feed_{$value[feedid]}_li\">";
	$html .= "<div class=\"cl\" {$value[style]}>";
	$html .= "<a class=\"t\" href=\"home.php?mod=space&uid=$_GET[uid]&do=home&view=$_GET[view]&appid=$value[appid]&icon=$value[icon]\" title=\"".lang('space', 'feed_view_only')."\"><img src=\"$value[icon_image]\" /></a>$value[title_template]";
	$html .= "\t<span class=\"xg1\">".dgmdate($value[dateline], 'n-j H:i')."</span>";

	$html .= "<div class=\"ec\">";

	if ($value['image_1']) {
		$html .= "<a href=\"$value[image_1_link]\"{$value[target]}><img src=\"$value[image_1]\" alt=\"\" class=\"tn\" /></a>";
	}
	if ($value['image_2']) {
		$html .= "<a href=\"$value[image_2_link]\"{$value[target]}><img src=\"$value[image_2]\" alt=\"\" class=\"tn\" /></a>";
	}
	if ($value['image_3']) {
		$html .= "<a href=\"$value[image_3_link]\"{$value[target]}><img src=\"$value[image_3]\" alt=\"\" class=\"tn\" /></a>";
	}
	if ($value['image_4']) {
		$html .= "<a href=\"$value[image_4_link]\"{$value[target]}><img src=\"$value[image_4]\" alt=\"\" class=\"tn\" /></a>";
	}

	if ($value['body_template']) {
		$style = $value['image_3'] ? ' style="clear: both; zoom: 1;"' : '';
		$html .= "<div class=\"d\" $style>$value[body_template]</div>";
	}

	if (!empty($value['body_data']['flashvar'])) {
		if(!empty($value['body_data']['imgurl'])) {
			$html .= '<table class="mtm" title="'.lang('space', 'click_play').'" onclick="javascript:showFlash(\''.$value['body_data']['host'].'\', \''.$value['body_data']['flashvar'].'\', this, \''.$value['sid'].'\');"><tr><td class="vdtn hm" style="background: url('.$value['body_data']['imgurl'].') no-repeat"><img src="'.STATICURL.'/image/common/vds.png" alt="'.lang('space', 'click_play').'" /></td></tr></table>';
		} else {
			$html .= "<img src=\"".STATICURL."/image/common/vd.gif\" alt=\"".lang('space', 'click_play')."\" onclick=\"javascript:showFlash('{$value['body_data']['host']}', '{$value['body_data']['flashvar']}', this, '{$value['sid']}');\" class=\"tn\" />";
		}
	}elseif (!empty($value['body_data']['musicvar'])) {
		$html .= "<img src=\"".STATICURL."/image/common/music.gif\" alt=\"".lang('space', 'click_play')."\" onclick=\"javascript:showFlash('music', '{$value['body_data']['musicvar']}', this, '{$value['feedid']}');\" class=\"tn\" />";
	}elseif (!empty($value['body_data']['flashaddr'])) {
		$html .= "<img src=\"".STATICURL."/image/common/flash.gif\" alt=\"".lang('space', 'click_view')."\" onclick=\"javascript:showFlash('flash', '{$value['body_data']['flashaddr']}', this, '{$value['feedid']}');\" class=\"tn\" />";
	}

	if ($value['body_general']) {
		$classname = $value['image_1'] ? ' z' : '';
		$html .= "<div class=\"quote$classname\"><blockquote>$value[body_general]</blockquote></div>";
	}
	$html .= "</div>";
	$html .= "</div>";
	$html .= "</li>";
	return $html;
}

function &getlayout($layout='') {
	$layoutarr = array(
			'1:2:1' => array('240', '480', '240'),
			'1:1:2' => array('240', '240', '480'),
			'2:1:1' => array('480', '240', '240'),
			'2:2' => array('480', '480'),
			'1:3' => array('240', '720'),
			'3:1' => array('720', '240'),
			'1:4' => array('190', '770'),
			'4:1' => array('770', '190'),
			'2:2:1' => array('385', '385', '190'),
			'1:2:2' => array('190', '385', '385'),
			'1:1:3' => array('190', '190', '570'),
			'1:3:1' => array('190', '570', '190'),
			'3:1:1' => array('570', '190', '190'),
			'3:2' => array('575', '385'),
			'2:3' => array('385', '575')
	);

	if (!empty($layout)) {
		$rt = (isset($layoutarr[$layout])) ? $layoutarr[$layout] : false;
	} else {
		$rt = $layoutarr;
	}

	return $rt;
}

function getblockdata($blockname = '') {
	$blockarr = lang('space', 'blockdata');
	$r = empty($blockname) ? $blockarr : (isset($blockarr[$blockname]) ? $blockarr[$blockname] : false);
	return $r;
}

function check_ban_block($blockname, $space) {
	global $_G;
	$return = true;
	loadcache('usergroup_'.$space['groupid']);
	if($blockname == 'group' && !helper_access::check_module('group')) {
		$return = false;
	} elseif($blockname == 'thread' && $_G['setting']['allowviewuserthread'] === -1) {
		$return = false;
	} elseif($blockname == 'myapp') {
		loadcache('usergroup_'.$space['groupid']);
		if(empty($_G['setting']['my_app_status']) || empty($_G['cache']['usergroup_'.$space['groupid']]['allowmyop'])) {
			$return = false;
		}
	}
	return $return;
}
?>