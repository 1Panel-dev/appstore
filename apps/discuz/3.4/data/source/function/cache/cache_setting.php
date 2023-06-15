<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: cache_setting.php 36353 2017-01-17 07:19:28Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function build_cache_setting() {
	global $_G;

	$skipkeys = array('posttableids', 'mastermobile', 'masterqq', 'masteremail', 'closedreason',
		'creditsnotify', 'backupdir', 'custombackup', 'jswizard', 'maxonlines', 'modreasons', 'newsletter',
		'postno', 'postnocustom', 'customauthorinfo', 'domainwhitelist', 'ipregctrl',
		'ipverifywhite', 'fastsmiley', 'defaultdoing', 'antitheftsetting',
		);
	$serialized = array('reginput', 'memory', 'search', 'creditspolicy', 'ftp', 'secqaa', 'ec_credit', 'qihoo', 'spacedata',
		'infosidestatus', 'uc', 'indexhot', 'relatedtag', 'sitemessage', 'uchome', 'heatthread', 'recommendthread',
		'disallowfloat', 'allowviewuserthread', 'advtype', 'click', 'card', 'rewritestatus', 'rewriterule', 'privacy', 'focus',
		'forumkeys', 'article_tags', 'verify', 'seotitle', 'seodescription', 'seokeywords', 'domain', 'ranklist', 'my_search_data',
		'seccodedata', 'inviteconfig', 'advexpiration', 'allowpostcomment', /*(IN_MOBILE)*/ 'mobile', 'connect', 'upgrade', 'patch', 'strongpw',
		'posttable_info', 'threadtable_info', 'profilegroup', 'antitheft', 'makehtml', 'guestviewthumb', 'grid', 'guesttipsinthread', 'accountguard',
		'security_usergroups_white_list', 'security_forums_white_list',
		);

	$data = array();

	foreach(C::t('common_setting')->fetch_all_not_key($skipkeys) as $setting) {
		if($setting['skey'] == 'extcredits') {
			if(is_array($setting['svalue'] = dunserialize($setting['svalue']))) {
				foreach($setting['svalue'] as $key => $value) {
					if($value['available']) {
						unset($setting['svalue'][$key]['available']);
					} else {
						unset($setting['svalue'][$key]);
					}
				}
			}
		} elseif($setting['skey'] == 'creditsformula') {
			if(!checkformulacredits($setting['svalue'])) {
				$setting['svalue'] = '$member[\'extcredits1\']';
			} else {
				$setting['svalue'] = preg_replace("/(friends|doings|blogs|albums|polls|sharings|digestposts|posts|threads|oltime|extcredits[1-8])/", "\$member['\\1']", $setting['svalue']);
			}
		} elseif($setting['skey'] == 'maxsmilies') {
			$setting['svalue'] = $setting['svalue'] <= 0 ? -1 : $setting['svalue'];
		} elseif($setting['skey'] == 'threadsticky') {
			$setting['svalue'] = explode(',', $setting['svalue']);
		} elseif($setting['skey'] == 'attachdir') {
			$setting['svalue'] = preg_replace("/\.asp|\\0/i", '0', $setting['svalue']);
			$setting['svalue'] = str_replace('\\', '/', substr($setting['svalue'], 0, 2) == './' ? DISCUZ_ROOT.$setting['svalue'] : $setting['svalue']);
			$setting['svalue'] .= substr($setting['svalue'], -1, 1) != '/' ? '/' : '';
		} elseif($setting['skey'] == 'attachurl') {
			$setting['svalue'] .= substr($setting['svalue'], -1, 1) != '/' ? '/' : '';
		} elseif($setting['skey'] == 'onlinehold') {
			$setting['svalue'] = $setting['svalue'] * 60;
		} elseif(in_array($setting['skey'], $serialized)) {
			$setting['svalue'] = @dunserialize($setting['svalue'], $setting['skey']);
			if($setting['skey'] == 'search') {
				foreach($setting['svalue'] as $key => $val) {
					foreach($val as $k => $v) {
						$setting['svalue'][$key][$k] = max(0, intval($v));
					}
				}
			} elseif($setting['skey'] == 'ftp') {
				$setting['svalue']['attachurl'] .= substr($setting['svalue']['attachurl'], -1, 1) != '/' ? '/' : '';
			} elseif($setting['skey'] == 'inviteconfig') {
				$setting['svalue']['invitecodeprompt'] = stripslashes($setting['svalue']['invitecodeprompt']);
			} elseif($setting['skey'] == 'profilegroup') {
				$profile_settings = C::t('common_member_profile_setting')->fetch_all_by_available(1);
				foreach($setting['svalue'] as $key => $val) {
					$temp = array();
					foreach($profile_settings as $pval) {
						if(in_array($pval['fieldid'], $val['field'])) {
							$temp[$pval['fieldid']] = $pval['fieldid'];
						}
					}
					foreach($val['field'] as $fieldid) {
						if(!in_array($fieldid, $temp)) {
							$temp[$fieldid] = $fieldid;
						}
					}
					$setting['svalue'][$key]['field'] = $temp;
				}
				C::t('common_setting')->update('profilegroup', $setting['svalue']);
			}
		}
		$_G['setting'][$setting['skey']] = $data[$setting['skey']] = $setting['svalue'];
	}

	$usergroup = C::t('common_usergroup')->fetch_by_credits($data['initcredits'], '');
	$data['newusergroupid'] = $usergroup['groupid'];
	$data['buyusergroupexists'] = C::t('common_usergroup')->buyusergroup_exists();

	if($data['srchhotkeywords']) {
		$data['srchhotkeywords'] = explode("\n", $data['srchhotkeywords']);
	}

	if($data['search']) {
		$searchstatus = 0;
		foreach($data['search'] as $item) {
			if($item['status']) {
				$searchstatus = 1;
				break;
			}
		}
		if(!$searchstatus) {
			$data['search'] = array();
		}
	}

	$data['creditspolicy'] = array_merge($data['creditspolicy'], get_cachedata_setting_creditspolicy());

	if($data['heatthread']['iconlevels']) {
		$data['heatthread']['iconlevels'] = explode(',', $data['heatthread']['iconlevels']);
		arsort($data['heatthread']['iconlevels']);
	} else {
		$data['heatthread']['iconlevels'] = array();
	}
	if($data['verify']) {
		foreach($data['verify'] as $key => $value) {
			if($value['available']) {
				if(!empty($value['unverifyicon'])) {
					$icourl = parse_url($value['unverifyicon']);
					if(!$icourl['host'] && !file_exists($value['unverifyicon'])) {
						$data['verify'][$key]['unverifyicon'] = $data['attachurl'].'common/'.$value['unverifyicon'];
					}
				}
				if(!empty($value['icon'])) {
					$icourl = parse_url($value['icon']);
					if(!$icourl['host'] && !file_exists($value['icon'])) {
						$data['verify'][$key]['icon'] = $data['attachurl'].'common/'.$value['icon'];
					}
				}
			}
		}
	}

	if($data['recommendthread']['status']) {
		if($data['recommendthread']['iconlevels']) {
			$data['recommendthread']['iconlevels'] = explode(',', $data['recommendthread']['iconlevels']);
			arsort($data['recommendthread']['iconlevels']);
		} else {
			$data['recommendthread']['iconlevels'] = array();
		}
	} else {
		$data['recommendthread'] = array('allow' => 0);
	}

	if($data['commentnumber'] && !$data['allowpostcomment']) {
		$data['commentnumber'] = 0;
	}

	if(!empty($data['ftp'])) {
		if(!empty($data['ftp']['allowedexts'])) {
			$data['ftp']['allowedexts'] = str_replace(array("\r\n", "\r"), array("\n", "\n"), $data['ftp']['allowedexts']);
			$data['ftp']['allowedexts'] = explode("\n", strtolower($data['ftp']['allowedexts']));
			array_walk($data['ftp']['allowedexts'], 'trim');
		}
		if(!empty($data['ftp']['disallowedexts'])) {
			$data['ftp']['disallowedexts'] = str_replace(array("\r\n", "\r"), array("\n", "\n"), $data['ftp']['disallowedexts']);
			$data['ftp']['disallowedexts'] = explode("\n", strtolower($data['ftp']['disallowedexts']));
			array_walk($data['ftp']['disallowedexts'], 'trim');
		}
		$data['ftp']['connid'] = 0;
	}

	if(!empty($data['forumkeys'])) {
		$data['forumfids'] = array_flip($data['forumkeys']);
	} else {
		$data['forumfids'] = array();
	}

	$data['commentitem'] = explode("\t", $data['commentitem']);
	$commentitem = array();
	foreach($data['commentitem'] as $k => $v) {
		$tmp = explode(chr(0).chr(0).chr(0), $v);
		if(count($tmp) > 1) {
			$commentitem[$tmp[0]] = $tmp[1];
		} else {
			$commentitem[$k] = $v;
		}
	}
	$data['commentitem'] = $commentitem;

	if($data['allowviewuserthread']['allow']) {
		$data['allowviewuserthread'] = is_array($data['allowviewuserthread']['fids']) && $data['allowviewuserthread']['fids'] && !in_array('', $data['allowviewuserthread']['fids']) ? dimplode($data['allowviewuserthread']['fids']) : '';
	} else {
		$data['allowviewuserthread'] = -1;
	}

	include_once DISCUZ_ROOT.'./source/discuz_version.php';
	$_G['setting']['version'] = $data['version'] = DISCUZ_VERSION;

	$data['sitemessage']['time'] = !empty($data['sitemessage']['time']) ? $data['sitemessage']['time'] * 1000 : 0;
	foreach (array('register', 'login', 'newthread', 'reply') as $type) {
		$data['sitemessage'][$type] = !empty($data['sitemessage'][$type]) ? explode("\n", $data['sitemessage'][$type]) : array();
	}

	$data['cachethreadon'] = C::t('forum_forum')->fetch_threadcacheon_num() ? 1 : 0;
	$data['disallowfloat'] = is_array($data['disallowfloat']) ? implode('|', $data['disallowfloat']) : '';

	if(!$data['imagelib']) unset($data['imageimpath']);

	if(is_array($data['relatedtag']['order'])) {
		asort($data['relatedtag']['order']);
		$relatedtag = array();
		foreach($data['relatedtag']['order'] AS $k => $v) {
			$relatedtag['status'][$k] = $data['relatedtag']['status'][$k];
			$relatedtag['name'][$k] = $data['relatedtag']['name'][$k];
			$relatedtag['limit'][$k] = $data['relatedtag']['limit'][$k];
			$relatedtag['template'][$k] = $data['relatedtag']['template'][$k];
		}
		$data['relatedtag'] = $relatedtag;

		foreach((array)$data['relatedtag']['status'] AS $appid => $status) {
			if(!$status) {
				unset($data['relatedtag']['limit'][$appid]);
			}
		}
		unset($data['relatedtag']['status'], $data['relatedtag']['order'], $relatedtag);
	}

	$data['domain']['defaultindex'] = isset($data['defaultindex']) && $data['defaultindex'] != '#' ? $data['defaultindex'] : '';
	$data['domain']['holddomain'] = isset($data['holddomain']) ? $data['holddomain'] : '';
	$data['domain']['list'] = array();
	foreach(C::t('common_domain')->fetch_all_by_idtype(array('subarea', 'forum', 'topic', 'channel', 'plugin')) as $value) {
		if($value['idtype'] == 'plugin') {
			$plugin = C::t('common_plugin')->fetch($value['id']);
			if(!$plugin || !$plugin['available']) {
				continue;
			}
			$value['id'] = $plugin['identifier'];
		}
		$data['domain']['list'][$value['domain'].'.'.$value['domainroot']] = array('id' => $value['id'], 'idtype' => $value['idtype']);
	}
	writetocache('domain', getcachevars(array('domain' => $data['domain'])));

	$data['seccodedata'] = is_array($data['seccodedata']) ? $data['seccodedata'] : array();
	if($data['seccodedata']['type'] == 2) {
		if(extension_loaded('ming')) {
			unset($data['seccodedata']['background'], $data['seccodedata']['adulterate'],
			$data['seccodedata']['ttf'], $data['seccodedata']['angle'],
			$data['seccodedata']['color'], $data['seccodedata']['size'],
			$data['seccodedata']['animator']);
		} else {
			$data['seccodedata']['animator'] = 0;
		}
	} elseif($data['seccodedata']['type'] == 99) {
		$data['seccodedata']['width'] = 32;
		$data['seccodedata']['height'] = 24;
	}

	$data['watermarktype'] = !empty($data['watermarktype']) ? dunserialize($data['watermarktype']) : array();
	$data['watermarktext'] = !empty($data['watermarktext']) ? dunserialize($data['watermarktext']) : array();
	foreach($data['watermarktype'] as $k => $v) {
		if($data['watermarktype'][$k] == 'text' && $data['watermarktext']['text'][$k]) {
			if($data['watermarktext']['text'][$k] && strtoupper(CHARSET) != 'UTF-8') {
				$data['watermarktext']['text'][$k] = diconv($data['watermarktext']['text'][$k], CHARSET, 'UTF-8', true);
			}
			$data['watermarktext']['text'][$k] = bin2hex($data['watermarktext']['text'][$k]);
			if(file_exists('static/image/seccode/font/en/'.$data['watermarktext']['fontpath'][$k])) {
				$data['watermarktext']['fontpath'][$k] = 'static/image/seccode/font/en/'.$data['watermarktext']['fontpath'][$k];
			} elseif(file_exists('static/image/seccode/font/ch/'.$data['watermarktext']['fontpath'][$k])) {
				$data['watermarktext']['fontpath'][$k] = 'static/image/seccode/font/ch/'.$data['watermarktext']['fontpath'][$k];
			} else {
				$data['watermarktext']['fontpath'][$k] = 'static/image/seccode/font/'.$data['watermarktext']['fontpath'][$k];
			}
			$data['watermarktext']['color'][$k] = preg_replace_callback('/#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/', 'build_cache_setting_callback_hexdec_123', $data['watermarktext']['color'][$k]);
			$data['watermarktext']['shadowcolor'][$k] = preg_replace_callback('/#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/', 'build_cache_setting_callback_hexdec_123', $data['watermarktext']['shadowcolor'][$k]);
		} else {
			$data['watermarktext']['text'][$k] = '';
			$data['watermarktext']['fontpath'][$k] = '';
			$data['watermarktext']['color'][$k] = '';
			$data['watermarktext']['shadowcolor'][$k] = '';
		}
	}

	$data['styles'] = array();
	foreach(C::t('common_style')->fetch_all_data(false, 1) as $style) {
		$data['styles'][$style['styleid']] = dhtmlspecialchars($style['name']);
	}

	$exchcredits = array();
	$allowexchangein = $allowexchangeout = FALSE;
	foreach((array)$data['extcredits'] as $id => $credit) {
		$data['extcredits'][$id]['img'] = $credit['img'] ? '<img style="vertical-align:middle" src="'.$credit['img'].'" />' : '';
		if(!empty($credit['ratio'])) {
			$exchcredits[$id] = $credit;
			$credit['allowexchangein'] && $allowexchangein = TRUE;
			$credit['allowexchangeout'] && $allowexchangeout = TRUE;
		}
		$data['creditnotice'] && $data['creditnames'][] = str_replace("'", "\'", dhtmlspecialchars($id.'|'.$credit['title'].'|'.$credit['unit']));
	}
	$data['creditnames'] = $data['creditnotice'] ? @implode(',', $data['creditnames']) : '';

	$creditstranssi = explode(',', $data['creditstrans']);
	$data['creditstrans'] = $creditstranssi[0];
	unset($creditstranssi[0]);
	$data['creditstransextra'] = $creditstranssi;
	for($i = 1; $i < 13; $i++) {
		$data['creditstransextra'][$i] = $data['creditstrans'] ? (!$data['creditstransextra'][$i] ? $data['creditstrans'] : $data['creditstransextra'][$i]) : 0;
	}
	$data['exchangestatus'] = $allowexchangein && $allowexchangeout;
	$data['transferstatus'] = isset($data['extcredits'][$data['creditstrans']]);

	list($data['zoomstatus'], $data['imagemaxwidth']) = explode("\t", $data['zoomstatus']);
	$data['imagemaxwidth'] = intval($data['imagemaxwidth']);

	require_once DISCUZ_ROOT.'./config/config_ucenter.php';
	$data['ucenterurl'] = UC_API;

	foreach(C::t('common_magic')->fetch_all_data(1) as $magic) {
		$magic['identifier'] = str_replace(':', '_', $magic['identifier']);
		$data['magics'][$magic['identifier']] = $magic['name'];
	}

	$data['tradeopen'] = C::t('common_usergroup_field')->count_by_field('allowposttrade', 1) ? 1 : 0;
	$data['medalstatus'] = intval(C::t('forum_medal')->count_by_available());

	$focus = array();
	if($data['focus']['data']) {
		foreach($data['focus']['data'] as $k => $v) {
			if($v['available'] && $v['position']) {
				foreach($v['position'] as $position) {
					$focus[$position][$k] = $k;
				}
			}
		}
	}
	$data['focus'] = $focus;

	list($data['plugins'], $data['pluginlinks'], $data['hookscript'], $data['hookscriptmobile'], $data['threadplugins'], $data['specialicon']) = get_cachedata_setting_plugin();

	if(empty($data['defaultindex'])) $data['defaultindex'] = array();
	list($data['navs'], $data['subnavs'], $data['menunavs'], $data['navmns'], $data['navmn'], $data['navdms'], $data['navlogos']) = get_cachedata_mainnav();

	$data['footernavs'] = get_cachedata_footernav();
	$data['spacenavs'] = get_cachedata_spacenavs();
	$data['mynavs'] = get_cachedata_mynavs();
	$data['topnavs'] = get_cachedata_topnav();
	$data['profilenode'] = get_cachedata_threadprofile();

	require_once DISCUZ_ROOT.'./uc_client/client.php';
	$ucapparray = uc_app_ls();
	$data['allowsynlogin'] = isset($ucapparray[UC_APPID]['synlogin']) ? $ucapparray[UC_APPID]['synlogin'] : 1;
	$appnamearray = array('UCHOME','XSPACE','DISCUZ','SUPESITE','SUPEV','ECSHOP','ECMALL','OTHER');
	$data['ucapp'] = $data['ucappopen'] = array();
	$data['uchomeurl'] = '';
	$data['discuzurl'] = $_G['siteurl'];
	$appsynlogins = 0;
	foreach($ucapparray as $apparray) {
		if($apparray['appid'] != UC_APPID) {
			if(!empty($apparray['synlogin'])) {
				$appsynlogins = 1;
			}
			if($data['uc']['navlist'][$apparray['appid']] && $data['uc']['navopen']) {
				$data['ucapp'][$apparray['appid']]['name'] = $apparray['name'];
				$data['ucapp'][$apparray['appid']]['url'] = $apparray['url'];
			}
		} else {
			$data['discuzurl'] = $apparray['url'];
		}
		if(!empty($apparray['viewprourl'])) {
			$data['ucapp'][$apparray['appid']]['viewprourl'] = $apparray['url'].$apparray['viewprourl'];
		}
		foreach($appnamearray as $name) {
			if($apparray['type'] == $name && $apparray['appid'] != UC_APPID) {
				$data['ucappopen'][$name] = 1;
				if($name == 'UCHOME') {
					$data['uchomeurl'] = $apparray['url'];
				} elseif($name == 'XSPACE') {
					$data['xspaceurl'] = $apparray['url'];
				}
			}
		}
	}
	$data['allowsynlogin'] = $data['allowsynlogin'] && $appsynlogins ? 1 : 0;
	$data['homeshow'] = $data['uchomeurl'] && $data['uchome']['homeshow'] ? $data['uchome']['homeshow'] : '0';

	unset($data['allowthreadplugin']);
	if($data['jspath'] == 'data/cache/') {
		writetojscache();
	} elseif(!$data['jspath']) {
		$data['jspath'] = 'static/js/';
	}

	if(!$data['csspathv']) {
		$data['csspathv'] = 'data/cache/';
	}
	$data['csspath'] = $data['csspathv'].'style_';

	if($data['cacheindexlife']) {
		$cachedir = DISCUZ_ROOT.'./'.$data['cachethreaddir'];
		$tidmd5 = substr(md5(0), 3);
		@unlink($cachedir.'/'.$tidmd5[0].'/'.$tidmd5[1].'/'.$tidmd5[2].'/0.htm');
	}

	$reginputbwords = array('username', 'password', 'password2', 'email');
	if(in_array($data['reginput']['username'], $reginputbwords) || !preg_match('/^[A-z]\w+?$/', $data['reginput']['username'])) {
		$data['reginput']['username'] = random(6);
	}
	if(in_array($data['reginput']['password'], $reginputbwords) || !preg_match('/^[A-z]\w+?$/', $data['reginput']['password'])) {
		$data['reginput']['password'] = random(6);
	}
	if(in_array($data['reginput']['password2'], $reginputbwords) || !preg_match('/^[A-z]\w+?$/', $data['reginput']['password2'])) {
		$data['reginput']['password2'] = random(6);
	}
	if(in_array($data['reginput']['email'], $reginputbwords) || !preg_match('/^[A-z]\w+?$/', $data['reginput']['email'])) {
		$data['reginput']['email'] = random(6);
	}

	$defaultcurhost = empty($_G['setting']['domain']['app']['default']) ? '{CURHOST}' : $_G['setting']['domain']['app']['default'];
	$output = array('str'=>array(), 'preg' => array()); //str为二级域名的查找和替换，preg为rewrite和默认域名的查找和替换
	$_G['domain'] = array();
	if(is_array($_G['setting']['domain']['app'])) {
		$apps = $_G['setting']['domain']['app'];
		$repflag = $apps['portal'] || $apps['forum'] || $apps['group'] || $apps['home'] || $apps['default'];
		foreach($apps as $app => $domain) {
			if(in_array($app, array('default', 'mobile'))) {
				continue;
			}
			$appphp = "{$app}.php";
			if(!$domain) {
				$domain = $defaultcurhost;
			}
			if($domain != '{CURHOST}') {
				$domain = $_G['scheme'].'://'.$domain.$_G['siteport'].'/';
			}
			if($repflag) {
				$output['str']['search'][$app] = "<a href=\"{$app}.php";
				$output['str']['replace'][$app] = '<a href="'.$domain.$appphp;
				$_G['domain']['pregxprw'][$app] = '<a href\="('.preg_quote($domain, '/').')'.$appphp;
			} else {
				$_G['domain']['pregxprw'][$app] = '<a href\="()'.$appphp;
			}
		}
	}
	if($_G['setting']['rewritestatus'] || $output['str']['search']) {
		if($_G['setting']['rewritestatus']) {
			require_once libfile('function/admincp');
			$output['preg'] = rewritedata(0);
		}
		if($output['preg']) {
			foreach($data['footernavs'] as $id => $nav) {
				foreach ($output['preg']['search'] as $key => $value) {
					$data['footernavs'][$id]['code'] = preg_replace_callback($value, create_function('$matches', 'return '.$output['preg']['replace'][$key].';'), $nav['code']);
				}
			}
			foreach($data['spacenavs'] as $id => $nav) {
				foreach ($output['preg']['search'] as $key => $value) {
					$data['spacenavs'][$id]['code'] = preg_replace_callback($value, create_function('$matches', 'return '.$output['preg']['replace'][$key].';'), $nav['code']);
				}
			}
			foreach($data['mynavs'] as $id => $nav) {
				foreach ($output['preg']['search'] as $key => $value) {
					$data['mynavs'][$id]['code'] = preg_replace_callback($value, create_function('$matches', 'return '.$output['preg']['replace'][$key].';'), $nav['code']);
				}
			}
			foreach($data['topnavs'] as $id => $nav) {
				foreach ($output['preg']['search'] as $key => $value) {
					$data['topnavs'][$id]['code'] = preg_replace_callback($value, create_function('$matches', 'return '.$output['preg']['replace'][$key].';'), $nav['code']);
				}
			}
			foreach($data['plugins']['jsmenu'] as $id => $nav) {
				foreach ($output['preg']['search'] as $key => $value) {
					$data['plugins']['jsmenu'][$id]['url'] = preg_replace_callback($value, create_function('$matches', 'return '.$output['preg']['replace'][$key].';'), $nav['url']);
				}
			}
		}
	}
	$data['output'] = $output;
	$data['connect'] = in_array('qqconnect', $data['plugins']['available']) ? $data['connect'] : array();

	$data['parseflv'] = get_cachedata_discuzcode_parseflv();

	$data['securesiteurl'] = $_G['siteurl'];

	if($data['membersplit']) {
		C::t('common_member_archive')->check_table();
	}

	savecache('setting', $data);
	$_G['setting'] = $data;
}

function build_cache_setting_callback_hexdec_123($matches) {
	return hexdec($matches[1]).','.hexdec($matches[2]).','.hexdec($matches[3]);
}

function get_cachedata_setting_creditspolicy() {
	$data = array();
	foreach(C::t('common_credit_rule')->fetch_all_by_action(array('promotion_visit', 'promotion_register')) as $creditrule) {
		$ruleexist = false;
		for($i = 1; $i <= 8; $i++) {
			if($creditrule['extcredits'.$i]) {
				$ruleexist = true;
			}
		}
		$data[$creditrule['action']] = $ruleexist;
	}
	return $data;
}

function get_cachedata_setting_plugin($method = '') {
	global $_G;
	$hookfuncs = array('common', 'discuzcode', 'template', 'deletemember', 'deletethread', 'deletepost', 'avatar', 'savebanlog', 'cacheuserstats', 'undeletethreads', 'recyclebinpostundelete', 'threadpubsave', 'profile_node');
	$data = $adminmenu = array();
	$data['plugins'] = $data['pluginlinks'] = $data['hookscript'] = $data['hookscriptmobile'] = $data['threadplugins'] = $data['specialicon'] = array();
	$data['plugins']['func'] = $data['plugins']['available'] = array();
	foreach(C::t('common_plugin')->fetch_all_data() as $plugin) {
		$available = !$method && $plugin['available'] || $method && ($plugin['available'] || $method == $plugin['identifier']);
		$addadminmenu = $plugin['available'] && C::t('common_pluginvar')->count_by_pluginid($plugin['pluginid']) ? TRUE : FALSE;
		$plugin['modules'] = dunserialize($plugin['modules']);
		if($available) {
			$data['plugins']['available'][] = $plugin['identifier'];
			$data['plugins']['version'][$plugin['identifier']] = $plugin['version'];
		}
		$plugin['directory'] = $plugin['directory'].((!empty($plugin['directory']) && substr($plugin['directory'], -1) != '/') ? '/' : '');
		if(is_array($plugin['modules'])) {
			unset($plugin['modules']['extra']);
			foreach($plugin['modules'] as $k => $module) {
				if($available && isset($module['name'])) {
					$module['displayorder'] = $plugin['modules']['system'] ? ($module['displayorder'] < 1000 ? $module['displayorder'] : 999) : $module['displayorder'] + 1000;
					$k = '';
					switch($module['type']) {
						case 1:
							$navtype = 0;
						case 23:
							if($module['type'] == 23) $navtype = 1;
						case 24:
							if($module['type'] == 24) $navtype = 2;
						case 25:
							if($module['type'] == 25) $navtype = 3;
						case 27:
							if($module['type'] == 27) $navtype = 4;
							$module['url'] = $module['url'] ? $module['url'] : 'plugin.php?id='.$plugin['identifier'].':'.$module['name'];
							if(!(C::t('common_nav')->count_by_navtype_type_identifier($navtype, 3, $plugin['identifier']))) {
								C::t('common_nav')->insert(array(
								'name' => $module['menu'],
								'title' => $module['navtitle'],
								'url' => $module['url'],
								'type' => 3,
								'identifier' => $plugin['identifier'],
								'navtype' => $navtype,
								'available' => 1,
								'icon' => $module['navicon'],
								'subname' => $module['navsubname'],
								'suburl' => $module['navsuburl'],
								));
							}
							break;
						case 5:
							$k = 'jsmenu';
							$module['url'] = $module['url'] ? $module['url'] : 'plugin.php?id='.$plugin['identifier'].':'.$module['name'];
							list($module['menu'], $module['title']) = explode('/', $module['menu']);
							$module['menu'] = $module['type'] == 1 ? ($module['menu'].($module['title'] ? '<span>'.$module['title'].'</span>' : '')) : $module['menu'];
							$data['plugins'][$k][] = array('displayorder' => $module['displayorder'], 'adminid' => $module['adminid'], 'url' => "<a href=\"$module[url]\" id=\"mn_plink_$module[name]\">$module[menu]</a>");
							break;
						case 14:
							$k = 'faq';
						case 15:
							$k = !$k ? 'modcp_base' : $k;
						case 16:
							$k = !$k ? 'modcp_tools' : $k;
						case 7:
							$k = !$k ? 'spacecp' : $k;
						case 17:
							$k = !$k ? 'spacecp_profile' : $k;
						case 19:
							$k = !$k ? 'spacecp_credit' : $k;
						case 21:
							$k = !$k ? 'portalcp' : $k;
						case 26:
							$k = !$k ? 'space_thread' : $k;
							$data['plugins'][$k][$plugin['identifier'].':'.$module['name']] = array('displayorder' => $module['displayorder'], 'adminid' => $module['adminid'], 'name' => $module['menu'], 'url' => $module['url'], 'directory' => $plugin['directory']);
							break;
						case 3:
							$addadminmenu = TRUE;
							break;
						case 4:
							$data['plugins']['include'][$plugin['identifier']] = array('displayorder' => $module['displayorder'], 'adminid' => $module['adminid'], 'script' => $plugin['directory'].$module['name']);
							break;
						case 11:
							$k = 'hookscript';
						case 28:
							$k = !$k ? 'hookscriptmobile' : $k;
							$script = $plugin['directory'].$module['name'];
							@include_once DISCUZ_ROOT.'./source/plugin/'.$script.'.class.php';
							$classes = get_declared_classes();
							$classnames = array();
							$namekey = ($k == 'hookscriptmobile' ? 'mobile' : '').'plugin_'.$plugin['identifier'];
							$cnlen = strlen($namekey);
							foreach($classes as $classname) {
								if(substr($classname, 0, $cnlen) == $namekey) {
									$hscript = substr($classname, $cnlen + 1);
									$classnames[$hscript ? $hscript : 'global'] = $classname;
								}
							}
							foreach($classnames as $hscript => $classname) {
								$hookmethods = get_class_methods($classname);
								foreach($hookmethods as $funcname) {
									if($hscript == 'global' && in_array($funcname, $hookfuncs)) {
										$data['plugins']['func'][$k][$funcname] = true;
										if($funcname == 'profile_node') {
											$data['plugins']['profile_node'][$plugin['identifier']] = $script;
										}
									}
									$v = explode('_', $funcname);
									$curscript = $v[0];
									if(!$curscript || $classname == $funcname) {
										continue;
									}
									if($hscript == 'home' && in_array($curscript, array('space', 'spacecp'))) {
										$curscript .= '_'.$v[1];
									}
									if(strpos($funcname, '__') !== false) {
										$curscript = current(explode('__', $funcname));
									}
									if(!@in_array($script, $data[$k][$hscript][$curscript]['module'])) {
										$data[$k][$hscript][$curscript]['module'][$plugin['identifier']] = $script;
										$data[$k][$hscript][$curscript]['adminid'][$plugin['identifier']] = $module['adminid'];
									}
									if(preg_match('/\_output$/', $funcname)) {
										$varname = preg_replace('/\_output$/', '', $funcname);
										$data[$k][$hscript][$curscript]['outputfuncs'][$varname][] = array('displayorder' => $module['displayorder'], 'func' => array($plugin['identifier'], $funcname));
									} elseif(preg_match('/\_message$/', $funcname)) {
										$varname = preg_replace('/\_message$/', '', $funcname);
										$data[$k][$hscript][$curscript]['messagefuncs'][$varname][] = array('displayorder' => $module['displayorder'], 'func' => array($plugin['identifier'], $funcname));
									} else {
										$data[$k][$hscript][$curscript]['funcs'][$funcname][] = array('displayorder' => $module['displayorder'], 'func' => array($plugin['identifier'], $funcname));
									}
								}
							}
							break;
						case 12:
							$script = $plugin['directory'].$module['name'];
							@include_once DISCUZ_ROOT.'./source/plugin/'.$script.'.class.php';
							if(class_exists('threadplugin_'.$plugin['identifier'])) {
								$classname = 'threadplugin_'.$plugin['identifier'];
								$hookclass = new $classname;
								if($hookclass->name) {
									$data['threadplugins'][$plugin['identifier']]['name'] = $hookclass->name;
									$data['threadplugins'][$plugin['identifier']]['icon'] = $hookclass->iconfile;
									$data['threadplugins'][$plugin['identifier']]['module'] = $script;
								}
							}
							break;
					}
				}
			}
		}
		if($addadminmenu) {
			$adminmenu[$plugin['modules']['system'] ? 0 : 1][] = array('url' => "plugins&operation=config&do=$plugin[pluginid]", 'action' => 'plugins_config_'.$plugin['pluginid'], 'name' => $plugin['name']);
		}
	}
	if(!$method) {
		$_G['setting']['plugins']['available'] = $data['plugins']['available'];
		if($adminmenu[0]) {
			$adminmenu[0] = array_merge(
				array(array('name' => 'plugins_system', 'sub' => 1)),
				$adminmenu[0],
				array(array('name' => 'plugins_system', 'sub' => 2))
			);
		}
		savecache('adminmenu', array_merge((array)$adminmenu[0], (array)$adminmenu[1]));
	}



	$data['pluginhooks'] = array();
	foreach(array('hookscript', 'hookscriptmobile') as $hooktype) {
		foreach($data[$hooktype] as $hscript => $hookscript) {
			foreach($hookscript as $curscript => $scriptdata) {
				if(is_array($scriptdata['funcs'])) {
					foreach($scriptdata['funcs'] as $funcname => $funcs) {
						usort($funcs, 'pluginmodulecmp');
						$tmp = array();
						foreach($funcs as $k => $v) {
							$tmp[$k] = $v['func'];
						}
						$data[$hooktype][$hscript][$curscript]['funcs'][$funcname] = $tmp;
					}
				}
				if(is_array($scriptdata['outputfuncs'])) {
					foreach($scriptdata['outputfuncs'] as $funcname => $funcs) {
						usort($funcs, 'pluginmodulecmp');
						$tmp = array();
						foreach($funcs as $k => $v) {
							$tmp[$k] = $v['func'];
						}
						$data[$hooktype][$hscript][$curscript]['outputfuncs'][$funcname] = $tmp;
					}
				}
				if(is_array($scriptdata['messagefuncs'])) {
					foreach($scriptdata['messagefuncs'] as $funcname => $funcs) {
						usort($funcs, 'pluginmodulecmp');
						$tmp = array();
						foreach($funcs as $k => $v) {
							$tmp[$k] = $v['func'];
						}
						$data[$hooktype][$hscript][$curscript]['messagefuncs'][$funcname] = $tmp;
					}
				}
			}
		}
	}

	foreach(array('links', 'spacecp', 'include', 'jsmenu', 'space', 'spacecp', 'spacecp_profile', 'spacecp_credit', 'faq', 'modcp_base', 'modcp_member', 'modcp_forum') as $pluginkey) {
		if(is_array($data['plugins'][$pluginkey])) {
			if(in_array($pluginkey, array('space', 'spacecp', 'spacecp_profile', 'spacecp_credit', 'faq', 'modcp_base', 'modcp_tools'))) {
				uasort($data['plugins'][$pluginkey], 'pluginmodulecmp');
			} else {
				usort($data['plugins'][$pluginkey], 'pluginmodulecmp');
			}
		}
	}

	return array($data['plugins'], $data['pluginlinks'], $data['hookscript'], $data['hookscriptmobile'], $data['threadplugins'], $data['specialicon']);

}

function get_cachedata_mainnav() {
	global $_G;

	$data['navs'] = $data['subnavs'] = $data['menunavs'] = $data['navmns'] = $data['navmn'] = $data['navdms'] = $navids = array();
	foreach(C::t('common_nav')->fetch_all_mainnav() as $nav) {
		if($nav['available'] < 0) {
			continue;
		}
		$id = $nav['type'] == 0 ? $nav['identifier'] : 100 + $nav['id'];
		if($nav['identifier'] == 1 && $nav['type'] == 0 && !helper_access::check_module('portal')) {
			$nav['available'] = 0;
		}
		if($nav['identifier'] == 3 && $nav['type'] == 0 && !helper_access::check_module('group')) {
			$nav['available'] = 0;
		}
		if($nav['identifier'] == 4 && $nav['type'] == 0 && !helper_access::check_module('feed')) {
			$nav['available'] = 0;
		}
		if($nav['type'] == 3) {
			if(!in_array($nav['identifier'], $_G['setting']['plugins']['available'])) {
				continue;
			}
		}
		if($nav['identifier'] == 5 && $nav['type'] == 0 && !$_G['setting']['my_app_status']) {
			$nav['available'] = 0;
		}
		if($nav['identifier'] == 8 && $nav['type'] == 0 && !$_G['setting']['ranklist']['status']) {
			$nav['available'] = 0;
		}
		$nav['style'] = parsehighlight($nav['highlight']);
		$data['navs'][$id]['navname'] = $nav['name'];
		$data['navs'][$id]['filename'] = $nav['url'];
		$data['navs'][$id]['available'] = $nav['available'];
		$nav['name'] = $nav['name'].($nav['title'] ? '<span>'.$nav['title'].'</span>' : '');
		$subnavs = '';
		if(!($nav['identifier'] == 5 && $nav['type'] == 0)) {
			foreach(C::t('common_nav')->fetch_all_subnav($nav['id']) as $subnav) {
				$item = "<a href=\"$subnav[url]\" hidefocus=\"true\" ".($subnav['title'] ? "title=\"$subnav[title]\" " : '').($subnav['target'] == 1 ? "target=\"_blank\" " : '').parsehighlight($subnav['highlight']).">$subnav[name]</a>";
				$liparam = !$nav['subtype'] || !$nav['subcols'] ? '' : ' style="width:'.sprintf('%1.1f', (1 / $nav['subcols']) * 100).'%"';
				$subnavs .= '<li'.$liparam.'>'.$item.'</li>';
			}
		}
		list($navid) = explode('.', basename($nav['url']));
		if($nav['type'] || $navid == 'misc' || $nav['identifier'] == 6) {
			if($nav['type'] == 4) {
				$navid = 'P'.$nav['identifier'];
			} elseif($nav['type'] == 5) {
				$navid = 'F'.$nav['identifier'];
			} else {
				$navid = 'N'.substr(md5(($nav['url'] != '#' ? $nav['url'] : $nav['name'])), 0, 4);
			}
		}
		$navid = 'mn_'.$navid;
		if(in_array($navid, $navids)) {
			$navid .= '_'.$nav['identifier'];
		}
		$navids[] = $navid;
		$onmouseover = '';
		if($subnavs) {
			if($nav['subtype']) {
				$onmouseover = 'navShow(\''.substr($navid, 3).'\')';
				$data['subnavs'][$navid] = $subnavs;
			} else {
				$onmouseover = 'showMenu({\'ctrlid\':this.id,\'ctrlclass\':\'hover\',\'duration\':2})';
				$data['menunavs'][] = '<ul class="p_pop h_pop" id="'.$navid.'_menu" style="display: none">'.$subnavs.'</ul>';
			}
		}
		if($nav['identifier'] == 6 && $nav['type'] == 0) {
			if(!empty($_G['setting']['plugins']['jsmenu'])) {
				$onmouseover .= "showMenu({'ctrlid':this.id,'ctrlclass':'hover','menuid':'plugin_menu'})";
			} else {
				$data['navs'][$id]['available'] = 0;
				continue;
			}
		}
		if($nav['identifier'] == 5 && $nav['type'] == 0) {
			$onmouseover = 'delayShow(this, function () {showMenu({\'ctrlid\':\'mn_userapp\',\'pos\':\'43!\',\'ctrlclass\':\'a\',\'duration\':2});showUserApp();})';
			$data['menunavs'][] = '<div class="p_pop h_pop" id="'.$navid.'_menu" style="display: none"></div>';
			$data['subnavs'][$navid] = '';
		}

		if($nav['logo']) {
			$navlogo = str_replace('{STATICURL}', STATICURL, $nav['logo']);
			if(!preg_match("/^".preg_quote(STATICURL, '/')."/i", $navlogo) && !(($valueparse = parse_url($navlogo)) && isset($valueparse['host']))) {
				$navlogo = $_G['setting']['attachurl'].'common/'.$nav['logo'];
			}
			$data['navlogos'][$navid] = '<a href="'.$nav['url'].'" title="'.$_G['setting']['bbname'].'"><img src="'.$navlogo.'" alt="'.$_G['setting']['bbname'].'" border="0" /></a>';
		}

		$purl = parse_url($nav['url']);
		$getvars = array();
		if($purl['query']) {
			parse_str($purl['query'], $getvars);
			$data['navmns'][$purl['path']][] = array($getvars, $navid);
		} elseif($purl['host']) {
			$data['navdms'][strtolower($purl['host'].$purl['path'])] = $navid;
		} elseif($purl['path']) {
			$data['navmn'][$purl['path']] = $navid;
		}
		if($nav['type'] == 0) {
			$domainkey = substr($purl['path'], 0, -strlen(strrchr($purl['path'], '.')));
			if(!empty($_G['setting']['domain']['app'][$domainkey]) && !in_array(strtolower($nav['title']), array('follow', 'guide', 'collection', 'blog', 'album', 'favorite', 'friend', 'share', 'doing'))) {
				$nav['url'] = $_G['scheme'].'://'.$_G['setting']['domain']['app'][$domainkey];
			}
		}

		$data['navs'][$id]['navid'] = $navid;
		$data['navs'][$id]['level'] = $nav['level'];
		$data['navs'][$id]['nav'] = "id=\"$navid\" ".($onmouseover ? 'onmouseover="'.$onmouseover.'"' : '')."><a href=\"$nav[url]\" hidefocus=\"true\" ".($nav['title'] ? "title=\"$nav[title]\" " : '').($nav['target'] == 1 ? "target=\"_blank\" " : '')." $nav[style]>$nav[name]".($nav['identifier'] == 5 && $nav['type'] == 0 ? '<b class="icon_down"></b>' : '')."</a";
	}
	$data['menunavs'] = implode('', $data['menunavs']);

	return array($data['navs'], $data['subnavs'], $data['menunavs'], $data['navmns'], $data['navmn'], $data['navdms'], $data['navlogos']);

}

function get_cachedata_footernav() {
	global $_G;

	$data['footernavs'] = array();
	foreach(C::t('common_nav')->fetch_all_by_navtype(1) as $nav) {
		$nav['extra'] = '';
		if(!$nav['type']) {
			if($nav['identifier'] == 'report') {
				$nav['url'] = 'javascript:;';
				$nav['extra'] = ' onclick="showWindow(\'miscreport\', \'misc.php?mod=report&url=\'+REPORTURL);return false;"';
			} elseif($nav['identifier'] == 'archiver') {
				if(!$_G['setting']['archiver']) {
					continue;
				} else {
					$domain = $_G['setting']['domain']['app']['forum'] ? $_G['setting']['domain']['app']['forum'] : ($_G['setting']['domain']['app']['default'] ? $_G['setting']['domain']['app']['default'] : '');
					$nav['url'] = ($domain ? $_G['scheme'].'://'.$domain.'/' : '').$nav['url'];
				}
			}
		}
		$nav['code'] = '<a href="'.$nav['url'].'"'.($nav['title'] ? ' title="'.$nav['title'].'"' : '').($nav['target'] == 1 ? ' target="_blank"' : '').' '.parsehighlight($nav['highlight']).$nav['extra'].'>'.$nav['name'].'</a>';
		$id = $nav['type'] == 0 ? $nav['identifier'] : 100 + $nav['id'];
		$data['footernavs'][$id] = array('available' => $nav['available'], 'navname' => $nav['name'], 'code' => $nav['code'], 'type' => $nav['type'], 'level' => $nav['level'], 'id' => $nav['identifier']);
	}
	return $data['footernavs'];
}

function get_cachedata_spacenavs() {
	global $_G;
	$data['spacenavs'] = array();
	foreach(C::t('common_nav')->fetch_all_by_navtype(2) as $nav) {
		if($nav['available'] < 0) {
			continue;
		}
		if($nav['icon']) {
			$navicon = str_replace('{STATICURL}', STATICURL, $nav['icon']);
			if(!preg_match("/^".preg_quote(STATICURL, '/')."/i", $navicon) && !(($valueparse = parse_url($navicon)) && isset($valueparse['host']))) {
				$navicon = $_G['setting']['attachurl'].'common/'.$nav['icon'].'?'.random(6);
			}
			$nav['icon'] = '<img src="'.$navicon.'" width="16" height="16" />';
		}
		$nav['allowsubnew'] = 1;
		if(!$nav['subname'] || !$nav['suburl'] || substr($nav['subname'], 0, 1) == "\t") {
			$nav['allowsubnew'] = 0;
			$nav['subname'] = substr($nav['subname'], 1);
		}
		$nav['extra'] = '';
		if(!$nav['type'] && ($nav['identifier'] == 'magic' && !$_G['setting']['magicstatus'] || $nav['identifier'] == 'medal' && !$_G['setting']['medalstatus'])) {
			continue;
		}
		if(!$nav['type'] && $nav['allowsubnew']) {
			if($nav['identifier'] == 'share') {
				$nav['extra'] = ' onclick="showWindow(\'share\', this.href, \'get\', 0);return false;"';
			} elseif($nav['identifier'] == 'thread') {
				$nav['extra'] = ' onclick="showWindow(\'nav\', this.href);return false;"';
			} elseif($nav['identifier'] == 'thread') {
				$nav['extra'] = ' onclick="showWindow(\'nav\', this.href);return false;"';
			} elseif($nav['identifier'] == 'activity') {
				if($_G['setting']['activityforumid']) {
					$nav['suburl'] = 'forum.php?mod=post&action=newthread&fid='.$_G['setting']['activityforumid'].'&special=4';
				} else {
					$nav['extra'] = ' onclick="showWindow(\'nav\', this.href);return false;"';
				}
			} elseif($nav['identifier'] == 'poll') {
				if($_G['setting']['pollforumid']) {
					$nav['suburl'] = 'forum.php?mod=post&action=newthread&fid='.$_G['setting']['pollforumid'].'&special=1';
				} else {
					$nav['extra'] = ' onclick="showWindow(\'nav\', this.href);return false;"';
				}
			} elseif($nav['identifier'] == 'reward') {
				if($_G['setting']['rewardforumid']) {
					$nav['suburl'] = 'forum.php?mod=post&action=newthread&fid='.$_G['setting']['rewardforumid'].'&special=3';
				} else {
					$nav['extra'] = ' onclick="showWindow(\'nav\', this.href);return false;"';
				}
			} elseif($nav['identifier'] == 'debate') {
				if($_G['setting']['debateforumid']) {
					$nav['suburl'] = 'forum.php?mod=post&action=newthread&fid='.$_G['setting']['debateforumid'].'&special=5';
				} else {
					$nav['extra'] = ' onclick="showWindow(\'nav\', this.href);return false;"';
				}
			} elseif($nav['identifier'] == 'trade') {
				if($_G['setting']['tradeforumid']) {
					$nav['suburl'] = 'forum.php?mod=post&action=newthread&fid='.$_G['setting']['tradeforumid'].'&special=2';
				} else {
					$nav['extra'] = ' onclick="showWindow(\'nav\', this.href);return false;"';
				}
			} elseif($nav['identifier'] == 'credit') {
				$nav['allowsubnew'] = $_G['setting']['ec_ratio'] && ($_G['setting']['ec_account'] || $_G['setting']['ec_tenpay_opentrans_chnid'] || $_G['setting']['ec_tenpay_bargainor']);
			}
		}
		$nav['subcode'] = $nav['allowsubnew'] ? '<span><a href="'.$nav['suburl'].'"'.($nav['target'] == 1 ? ' target="_blank"' : '').$nav['extra'].'>'.$nav['subname'].'</a></span>' : '';
		if($nav['name'] != '{hr}') {
				$nav['code'] = '<li>'.$nav['subcode'].'<a href="'.$nav['url'].'"'.($nav['title'] ? ' title="'.$nav['title'].'"' : '').($nav['target'] == 1 ? ' target="_blank"' : '').'>'.$nav['icon'].$nav['name'].'</a></li>';
		} else {
			$nav['code'] = '</ul><hr class="da" /><ul>';
		}
		$id = $nav['type'] == 0  ? $nav['identifier'] : 100 + $nav['id'];
		$data['spacenavs'][$id] = array('available' => $nav['available'], 'navname' => $nav['name'], 'code' => $nav['code'], 'level' => $nav['level']);
	}
	return $data['spacenavs'];
}

function get_cachedata_mynavs() {
	global $_G;

	$data['mynavs'] = array();
	foreach(C::t('common_nav')->fetch_all_by_navtype(3) as $nav) {
		if($nav['available'] < 0) {
			continue;
		}
		if($nav['icon']) {
			$navicon = str_replace('{STATICURL}', STATICURL, $nav['icon']);
			if(!preg_match("/^".preg_quote(STATICURL, '/')."/i", $navicon) && !(($valueparse = parse_url($navicon)) && isset($valueparse['host']))) {
				$navicon = $_G['setting']['attachurl'].'common/'.$nav['icon'].'?'.random(6);
			}
			$navicon = preg_match('/^(https?:)?\/\//i', $navicon) ? $navicon : $_G['siteurl'].$navicon;
			$nav['icon'] = ' style="background-image:url('.$navicon.') !important"';
		}
		$nav['code'] = '<a href="'.$nav['url'].'"'.($nav['title'] ? ' title="'.$nav['title'].'"' : '').($nav['target'] == 1 ? ' target="_blank"' : '').$nav['icon'].'>'.$nav['name'].'</a>';
		$id = $nav['type'] == 0 ? $nav['identifier'] : 100 + $nav['id'];
		$data['mynavs'][$id] = array('available' => $nav['available'], 'navname' => $nav['name'], 'code' => $nav['code'], 'level' => $nav['level']);
	}
	return $data['mynavs'];
}

function get_cachedata_topnav() {
	global $_G;

	$data['topnavs'] = array();
	foreach(C::t('common_nav')->fetch_all_by_navtype(4) as $nav) {
		$nav['extra'] = '';
		if(!$nav['type']) {
			if($nav['identifier'] == 'sethomepage') {
				$nav['url'] = 'javascript:;';
				$nav['extra'] = ' onclick="setHomepage(\''.$_G['siteurl'].'\');"';
			} elseif($nav['identifier'] == 'setfavorite') {
				$nav['url'] = $_G['siteurl'];
				$nav['extra'] = ' onclick="addFavorite(this.href, \''.addslashes($_G['setting']['bbname']).'\');return false;"';
			}
		}
		$nav['code'] = '<a href="'.$nav['url'].'"'.($nav['title'] ? ' title="'.$nav['title'].'"' : '').($nav['target'] == 1 ? ' target="_blank"' : '').' '.parsehighlight($nav['highlight']).$nav['extra'].'>'.$nav['name'].'</a>';
		$id = $nav['type'] == 0 ? $nav['identifier'] : 100 + $nav['id'];
		$data['topnavs'][$nav['subtype']][$id] = array('available' => $nav['available'], 'navname' => $nav['name'], 'code' => $nav['code'], 'type' => $nav['type'], 'level' => $nav['level'], 'id' => $nav['identifier']);
	}
	return $data['topnavs'];
}

function get_cachedata_threadprofile() {
	global $_G;
	if(!helper_dbtool::isexisttable('forum_threadprofile')) {
		return;
	}
	$threadprofiles = C::t('forum_threadprofile')->fetch_all();
	$threadprofile_group = C::t('forum_threadprofile_group')->fetch_all();
	$data = array();
	foreach($threadprofiles as $id => $threadprofile) {
		if($threadprofile['global']) {
			$data['template'][0] = dunserialize($threadprofile['template']);
		}
	}
	foreach($threadprofile_group as $group) {
		if($threadprofiles[$group['tpid']]) {
			$id = $threadprofiles[$group['tpid']]['global'] ? 0 : $group['tpid'];
			if(!isset($data['template'][$id])) {
				$data['template'][$id] = dunserialize($threadprofiles[$group['tpid']]['template']);
			}
			if($id) {
				$data['groupid'][$group['gid']] = $id;
			}
		}
	}
	foreach($data['template'] as $id => $template) {
		foreach($template as $type => $row) {
			$data['template'][$id][$type] = preg_replace_callback('/\{([\w:]+)(=([^}]+?))?\}(([^}]+?)\{\*\}([^}]+?)\{\/\\1\})?/s', create_function('$matches', 'return get_cachedata_threadprofile_nodeparse('.intval($id).', \''.addslashes($type).'\', $matches[1], $matches[5], $matches[6], $matches[3]);'), $template[$type]);
		}
	}
	$data['code'] = $_G['cachedata_threadprofile_code'];
	return $data;
}

function get_cachedata_threadprofile_nodeparse($id, $type, $name, $s, $e, $extra) {
	$s = stripslashes($s);
	$e = stripslashes($e);
	$extra = stripslashes($extra);
	global $_G;
	$hash = random(8);
	$_G['cachedata_threadprofile_code'][$id][$type]['{'.$hash.'}'] = array($name, $s, $e, $extra);
	return '{'.$hash.'}';
}

function get_cachedata_discuzcode_parseflv() {
	$mediadir = DISCUZ_ROOT.'./source/function/media';
	$parseflv = array();
	if(file_exists($mediadir)) {
		$mediadirhandle = dir($mediadir);
		while($entry = $mediadirhandle->read()) {
			if(!in_array($entry, array('.', '..')) && preg_match("/^media\_([\_\w]+)\.php$/", $entry, $entryr) && substr($entry, -4) == '.php' && is_file($mediadir.'/'.$entry)) {
				$checkurl = array();
				@include_once libfile('media/'.$entryr[1], 'function');
				$parseflv[$entryr[1]] = $checkurl;
			}
		}
	}
	return $parseflv;
}

function writetojscache() {
	$dir = DISCUZ_ROOT.'static/js/';
	$dh = opendir($dir);
	$remove = array(
	array(
		'/(^|\r|\n)\/\*.+?\*\/(\r|\n)/is',
		"/([^\\\:]{1})\/\/.+?(\r|\n)/",
		'/\/\/note.+?(\r|\n)/i',
		'/\/\/debug.+?(\r|\n)/i',
		'/(^|\r|\n)(\s|\t)+/',
		'/(\r|\n)/',
	), array(
		'',
		'\1',
		'',
		'',
		'',
		'',
	));
	while(($entry = readdir($dh)) !== false) {
		if(fileext($entry) == 'js') {
			$jsfile = $dir.$entry;
			$fp = fopen($jsfile, 'r');
			$jsdata = @fread($fp, filesize($jsfile));
			fclose($fp);
			$jsdata = preg_replace($remove[0], $remove[1], $jsdata);
			if(@$fp = fopen(DISCUZ_ROOT.'./data/cache/'.$entry, 'w')) {
				fwrite($fp, $jsdata);
				fclose($fp);
			} else {
				exit('Can not write to cache files, please check directory ./data/ and ./data/cache/ .');
			}
		}
	}
}

function pluginmodulecmp($a, $b) {
	return $a['displayorder'] > $b['displayorder'] ? 1 : -1;
}

function parsehighlight($highlight) {
	if($highlight) {
		$colorarray = array('', 'red', 'orange', 'yellow', 'green', 'cyan', 'blue', 'purple', 'gray');
		$string = sprintf('%02d', $highlight);
		$stylestr = sprintf('%03b', $string[0]);

		$style = ' style="';
		$style .= $stylestr[0] ? 'font-weight: bold;' : '';
		$style .= $stylestr[1] ? 'font-style: italic;' : '';
		$style .= $stylestr[2] ? 'text-decoration: underline;' : '';
		$style .= $string[1] ? 'color: '.$colorarray[$string[1]] : '';
		$style .= '"';
	} else {
		$style = '';
	}
	return $style;
}

?>