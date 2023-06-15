<?php

/**
 *		[Discuz!] (C)2001-2099 Comsenz Inc.
 *		This is NOT a freeware, use is subject to license terms
 *
 *		$Id: Connect.php 36278 2016-12-09 07:52:35Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Cloud_Service_Connect {


	const SPECIAL_GID = 7;
	protected static $_instance;

	public $state = '';

	public static function getInstance() {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct($siteId = '', $siteKey = '') {
	}

	public function connectMergeMember() {
		global $_G;
		static $merged;
		if($merged) {
			return;
		}

		$connect_member = C::t('#qqconnect#common_member_connect')->fetch($_G['uid']);
		if ($connect_member) {
			$_G['member'] = array_merge($_G['member'], $connect_member);
			$user_auth_fields = $connect_member['conisfeed'];
			if ($user_auth_fields == 0) {
				$_G['member']['is_user_info'] = 0;
				$_G['member']['is_feed'] = 0;
			} elseif ($user_auth_fields == 1) {
				$_G['member']['is_user_info'] = 1;
				$_G['member']['is_feed'] = 0;
			} elseif ($user_auth_fields == 2) {
				$_G['member']['is_user_info'] = 1;
				$_G['member']['is_feed'] = 0;
			} elseif ($user_auth_fields == 3) {
				$_G['member']['is_user_info'] = 0;
				$_G['member']['is_feed'] = 0;
			}
			unset($connect_member, $_G['member']['conisfeed']);
		}
		$merged = true;
	}

	public function connectUserBindParams() {
		global $_G;

		$this->connectMergeMember();
		getuserprofile('birthyear');
		getuserprofile('birthmonth');
		getuserprofile('birthday');
		switch ($_G['member']['gender']) {
			case 1 :
				$sex = 'male';
				break;
			case 2 :
				$sex = 'female';
				break;
			default :
				$sex = 'unknown';
		}

		$is_public_email = 2;
		$is_use_qq_avatar = $_G['member']['conisqzoneavatar'] == 1 ? 1 : 2;
		$birthday = sprintf('%04d', $_G['member']['birthyear']).'-'.sprintf('%02d', $_G['member']['birthmonth']).'-'.sprintf('%02d', $_G['member']['birthday']);

		$agent = md5(time().rand().uniqid());
		$inputArray = array (
			'uid' => $_G['uid'],
			'agent' => $agent,
			'time' => TIMESTAMP
		);
		require_once DISCUZ_ROOT.'./config/config_ucenter.php';
		$input = 'uid='.$_G['uid'].'&agent='.$agent.'&time='.TIMESTAMP;
		$avatar_input = authcode($input, 'ENCODE', UC_KEY);

		$params = array (
			'oauth_consumer_key' => $_G['setting']['connectappid'],
			'u_id' => $_G['uid'],
			'username' => $_G['member']['username'],
			'email' => $_G['member']['email'],
			'birthday' => $birthday,
			'sex' => $sex,
			'is_public_email' => $is_public_email,
			'is_use_qq_avatar' => $is_use_qq_avatar,
			's_id' => null,
			'avatar_input' => $avatar_input,
			'avatar_agent' => $agent,
			'site_ucenter_id' => UC_APPID,
			'source' => 'qzone',
		);

		return $params;
	}

	public function connectFeedResendJs() {
		global $_G;

		$jsname = $_G['cookie']['connect_js_name'];
		if($jsname != 'feed_resend') {
			return false;
		}

		$params = dunserialize(base64_decode($_G['cookie']['connect_js_params']));
		$params['sig'] = $this->connectGetSig($params, $this->connectGetSigKey());

		$jsurl = $_G['connect']['discuz_new_feed_url'];
		require_once DISCUZ_ROOT.'/source/plugin/qqconnect/lib/Util.php';
		$utilService = new Cloud_Service_Util();
		$jsurl .= '?' . $utilService->httpBuildQuery($params, '', '&');

		return $jsurl;
	}

	public function connectCookieLoginJs() {
		global $_G;

		return $ajaxUrl = 'connect.php?mod=check&op=cookie';
	}

	public function connectGetSigKey() {
		global $_G;

		return $_G['setting']['connectappid'] . '|' . $_G['setting']['connectappkey'];
	}


	public function connectGetSig($params, $app_key) {
		ksort($params);
		$base_string = '';
		foreach($params as $key => $value) {
			$base_string .= $key.'='.$value;
		}
		$base_string .= $app_key;
		return md5($base_string);
	}

	public function connectParseBbcode($bbcode, $fId, $pId, $isHtml, &$attachImages) {
		include_once libfile('function/discuzcode');

		$result = preg_replace('/\[hide(=\d+)?\].+?\[\/hide\](\r\n|\s)/i', '', $bbcode);
		$result = preg_replace('/\[payto(=\d+)?\].+?\[\/payto\](\r\n|\s)/i', '', $result);
		$result = preg_replace('/\[quote\].*\[\/quote\](\r\n|\n|\r){0,}/is', '', $result);
		$result = discuzcode($result, 0, 0, $isHtml, 1, 2, 1, 0, 0, 0, 0, 1, 0);
		$result = strip_tags($result, '<img><a>');
		$result = preg_replace('/<img src="images\//i', "<img src=\"".$_G['siteurl']."images/", $result);
		$result = $this->connectParseAttach($result, $fId, $pId, $attachImages, $attachImageThumb);
		return $result;
	}

	public function connectParseAttach($content, $fId, $pId, &$attachImages) {
		global $_G;

		$permissions = $this->connectGetUserGroupPermissions(self::SPECIAL_GID, $fId);
		$visitorPermission = $permissions[self::SPECIAL_GID];

		$attachIds = array();
		$attachImages = array ();
		$attachments = C::t('forum_attachment')->fetch_all_by_id('pid', $pId);
		$attachments = C::t('forum_attachment_n')->fetch_all("pid:$pId", array_keys($attachments));

		foreach ($attachments as $k => $attach) {
			$aid = $attach['aid'];
			if($attach['isimage'] == 0 || $attach['price'] > 0 || $attach['readperm'] > $visitorPermission['readPermission'] || in_array($fId, $visitorPermission['forbidViewAttachForumIds']) || in_array($attach['aid'], $attachIds)) {
				continue;
			}

			$imageItem = array ();
			$thumbWidth = '100';
			$thumbHeight = '100';
			$bigWidth = '400';
			$bigHeight = '400';
			$thumbImageURL = $_G['siteurl'] . getforumimg($aid, 1, $thumbWidth, $thumbHeight, 'fixwr');
			$bigImageURL = $_G['siteurl'] . getforumimg($aid, 1, $bigWidth, $bigHeight, 'fixnone');
			$imageItem['aid'] = $aid;
			$imageItem['thumb'] = $thumbImageURL;
			$imageItem['big'] = $bigImageURL;
			if($attach['remote']) {
				$imageItem['path'] = $_G['setting']['ftp']['attachurl'].'forum/'.$attach['attachment'];
				$imageItem['remote'] = true;
			} else {
				$imageItem['path'] = $_G['setting']['attachdir'].'forum/'.$attach['attachment'];
			}

			$attachIds[] = $aid;
			$attachImages[] = $imageItem;
		}

		$this->connectParseAttach_callback_connectParseAttachTag_1($attachNames, 1);

		$content = preg_replace_callback('/\[attach\](\d+)\[\/attach\]/i', array($this, 'connectParseAttach_callback_connectParseAttachTag_1'), $content);

		return $content;
	}

	public function connectParseAttach_callback_connectParseAttachTag_1($matches, $action = 0) {
		static $attachNames = '';

		if($action == 1) {
			$attachNames = $matches;
		} else {
			return $this->connectParseAttachTag($matches[1], $attachNames);
		}
	}

	public function connectParseAttachTag($attachId, $attachNames) {
		include_once libfile('function/discuzcode');
		if(array_key_exists($attachId, $attachNames)) {
			return '<span class="attach"><a href="'.$_G['siteurl'].'/attachment.php?aid='.aidencode($attachId).'">'.$attachNames[$attachId].'</a></span>';
		}
		return '';
	}

	function connectGetUserGroupPermissions($gid, $fid) {
		global $_G;

		loadcache('usergroups');
		$fields = array (
			'groupid' => 'userGroupId',
			'grouptitle' => 'userGroupName',
			'readaccess' => 'readPermission',
			'allowvisit' => 'allowVisit'
		);
		$userGroup = C::t('common_usergroup')->fetch_all($gid);
		$userGroupInfo = array();
		foreach ($userGroup as $id => $value) {
			$userGroupInfo[$id] = array_merge($value, $_G['cache']['usergroups'][$id]);
			$userGroupInfo[$id]['forbidForumIds'] = array ();
			$userGroupInfo[$id]['allowForumIds'] = array ();
			$userGroupInfo[$id]['specifyAllowForumIds'] = array ();
			$userGroupInfo[$id]['allowViewAttachForumIds'] = array ();
			$userGroupInfo[$id]['forbidViewAttachForumIds'] = array ();
			foreach ($fields as $k => $v) {
				$userGroupInfo[$id][$v] = $userGroupInfo[$id][$k];
			}
		}
		$forumField = C::t('forum_forumfield')->fetch($fid);
		$allowViewGroupIds = array ();
		if($forumField['viewperm']) {
			$allowViewGroupIds = explode("\t", $forumField['viewperm']);
		}
		$allowViewAttachGroupIds = array ();
		if($forumField['getattachperm']) {
			$allowViewAttachGroupIds = explode("\t", $forumField['getattachperm']);
		}

		foreach ($userGroupInfo as $groupId => $value) {
			if($forumField['password']) {
				$userGroupInfo[$groupId]['forbidForumIds'][] = $fid;
				continue;
			}
			$perm = unserialize($forumField['formulaperm']);
			if(is_array($perm)) {
				if($perm[0] || $perm[1] || $perm['users']) {
					$userGroupInfo[$groupId]['forbidForumIds'][] = $fid;
					continue;
				}
			}

			if(!$allowViewGroupIds) {
				$userGroupInfo[$groupId]['allowForumIds'][] = $fid;
			} elseif (!in_array($groupId, $allowViewGroupIds)) {
				$userGroupInfo[$groupId]['forbidForumIds'][] = $fid;
			} elseif (in_array($groupId, $allowViewGroupIds)) {
				$userGroupInfo[$groupId]['allowForumIds'][] = $fid;
				$userGroupInfo[$groupId]['specifyAllowForumIds'][] = $fid;
			}

			if(!$allowViewAttachGroupIds) {
				$userGroupInfo[$groupId]['allowViewAttachForumIds'][] = $fid;
			} elseif (!in_array($groupId, $allowViewAttachGroupIds)) {
				$userGroupInfo[$groupId]['forbidViewAttachForumIds'][] = $fid;
			} elseif (in_array($groupId, $allowViewGroupIds)) {
				$userGroupInfo[$groupId]['allowViewAttachForumIds'][] = $fid;
			}
		}

		return $userGroupInfo;
	}

	public function connectOutputPhp($url, $postData = '') {
		global $_G;

		$response = dfsockopen($url, 0, $postData, '', false, $_G['setting']['cloud_api_ip']);
		$result = (array) dunserialize($response);
		return $result;
	}

	public function connectJsOutputMessage($msg = '', $errMsg = '', $errCode = '') {
		$result = array (
			'result' => $msg,
			'errMessage' => $errMsg,
			'errCode' => $errCode
		);
		echo sprintf('con_handle_response(%s);', json_encode($this->_connectUrlencode($result)));
		exit;
	}

	protected function _connectUrlencode($value) {

		if (is_array($value)) {
			foreach ($value as $k => $v) {
				$value[$k] = $this->_connectUrlencode($v);
			}
		} else if (is_string($value)) {
			$value = urlencode(str_replace(array("\r\n", "\r", "\n", "\"", "\/", "\t"), array('\\n', '\\n', '\\n', '\\"', '\\/', '\\t'), $value));
		}

		return $value;
	}

	public function connectCookieLoginParams() {
		global $_G;

		$this->connectMergeMember();
		$oauthToken = $_G['member']['conuin'];
		$api_url = $_G['connect']['api_url'].'/connect/discuz/cookieReport';

		if($oauthToken) {
			$extra = array (
				'oauth_token' => $oauthToken
			);

			$sig_params = $this->connectGetOauthSignatureParams($extra);

			$oauth_token_secret = $_G['member']['conuinsecret'];
			$sig_params['oauth_signature'] = $this->connectGetOauthSignature($api_url, $sig_params, 'POST', $oauth_token_secret);
			$params = array (
				'client_ip' => $_G['clientip'],
				'u_id' => $_G['uid'],
				'version' => 'qzone1.0',
			);

			$params = array_merge($sig_params, $params);
			$params['response_type'] = 'php';

			return $params;
		} else {
			return false;
		}
	}

	function connectAddCookieLogins() {
		global $_G;

		loadcache('connect_has_setting_count');
		if (!$_G['cache']['connect_has_setting_count']) {
			$times = C::t('common_setting')->fetch('connect_login_times');
			C::t('common_setting')->update('connect_login_times', $times + 1);
			savecache('connect_has_setting_count', '1');
		} else {
			C::t('common_setting')->update_count('connect_login_times', 1);
		}

		$life = 86400;
		$current_date = date('Y-m-d');
		dsetcookie('connect_last_report_time', $current_date, $life);

		return true;
	}

	public function connectAjaxOuputMessage($msg = '', $errCode = '') {

		@header("Content-type: text/html; charset=".CHARSET);

		echo "errCode=$errCode&result=$msg";
		exit;
	}

	public function connectGetOauthSignature($url, $params, $method = 'POST', $oauth_token_secret = '') {

		global $_G;

		$method = strtoupper($method);
		if(!in_array($method, array ('GET', 'POST'))) {
			return FALSE;
		}

		$url = urlencode($url);

		require_once DISCUZ_ROOT.'/source/plugin/qqconnect/lib/Util.php';
		$utilService = new Cloud_Service_Util();
		$param_str = urlencode($utilService->httpBuildQuery($params, '', '&'));

		$base_string = $method.'&'.$url.'&'.$param_str;

		$key = $_G['setting']['connectappkey'].'&'.$oauth_token_secret;

		$signature = $utilService->hashHmac('sha1', $base_string, $key);

		return $signature;
	}

	public function connectGetOauthSignatureParams($extra = array ()) {
		global $_G;

		$params = array (
			'oauth_consumer_key' => $_G['setting']['connectappid'],
			'oauth_nonce' => $this->_connectGetNonce(),
			'oauth_signature_method' => 'HMAC_SHA1',
			'oauth_timestamp' => TIMESTAMP
		);
		if($extra) {
			$params = array_merge($params, $extra);
		}
		ksort($params);

		return $params;
	}

	protected function _connectGetNonce() {
		$mt = microtime();
		$rand = mt_rand();

		return md5($mt.$rand);
	}

	public function connectParseXml($contents, $getAttributes = true, $priority = 'tag') {
		if (!$contents) {
			return array();
		}

		if (!function_exists('xml_parser_create')) {
			return array();
		}

		$parser = xml_parser_create('');
		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, trim($contents), $xmlValues);
		xml_parser_free($parser);

		if (!$xmlValues) {
			return;
		}

		$xmlArray = $parent = array();

		$current = &$xmlArray;
		$repeatedTagIndex = array();

		foreach($xmlValues as $data) {
			unset($attributes, $value);
			extract($data);

			$result = $attributesData = array();

			if (isset($value)) {
				if ($priority == 'tag') {
					$result = $value;
				} else {
					$result['value'] = $value;
				}
			}

			if (isset($attributes) && $getAttributes) {
				foreach ($attributes as $attr => $val) {
					if ($priority == 'tag') {
						$attributesData[$attr] = $val;
					} else {
						$result['attr'][$attr] = $val;
					}
				}
			}

			if ($type == 'open') {
				$parent[$level - 1] = &$current;
				if (!is_array($current) || (!in_array($tag, array_keys($current)))) {
					$current[$tag] = $result;
					if ($attributesData) {
						$current[$tag . '_attr'] = $attributesData;
					}
					$repeatedTagIndex[$tag . '_' . $level] = 1;
					$current = &$current[$tag];
				} else {
					if (isset($current[$tag][0])) {
						$current[$tag][$repeatedTagIndex[$tag . '_' . $level]] = $result;
						$repeatedTagIndex[$tag . '_' . $level] ++;
					} else {
						$current[$tag] = array($current[$tag], $result);
						$repeatedTagIndex[$tag . '_' . $level] = 2;
						if (isset($current[$tag . '_attr'])) {
							$current[$tag]['0_attr'] = $current[$tag . '_attr'];
							unset($current[$tag . '_attr']);
						}
					}
					$lastItemIndex = $repeatedTagIndex[$tag . '_' . $level] - 1;
					$current = &$current[$tag][$lastItemIndex];
				}
			} elseif($type == 'complete') {
				if (!isset($current[$tag])) {
					$current[$tag] = $result;
					$repeatedTagIndex[$tag . '_' . $level] = 1;
					if ($priority == 'tag' && $attributesData) {
						$current[$tag . '_attr'] = $attributesData;
					}
				} else {
					if (isset($current[$tag][0]) && is_array($current[$tag])) {
						$current[$tag][$repeatedTagIndex[$tag . '_' . $level]] = $result;
						if ($priority == 'tag' && $getAttributes && $attributesData) {
							$current[$tag][$repeatedTagIndex[$tag . '_' . $level] . '_attr'] = $attributesData;
						}
						$repeatedTagIndex[$tag . '_' . $level] ++;
					} else {
						$current[$tag] = array($current[$tag], $result);
						$repeatedTagIndex[$tag . '_' . $level] = 1;
						if ($priority == 'tag' && $getAttributes) {
							if (isset($current[$tag . '_attr'])) {
								$current[$tag]['0_attr'] = $current[$tag . '_attr'];
								unset($current[$tag . '_attr']);
							}
							if ($attributesData) {
								$current[$tag][$repeatedTagIndex[$tag . '_' . $level] . '_attr'] = $attributesData;
							}
						}
						$repeatedTagIndex[$tag . '_' . $level] ++;
					}
				}
			} elseif($type == 'close') {
				$current = &$parent[$level - 1];
			}
		}

		return $xmlArray[key($parent[0])] ? $xmlArray[key($parent[0])] : $xmlArray;
	}


	public function connectFilterUsername($username) {
		$username = str_replace(' ', '_', trim($username));
		return cutstr($username, 15, '');
	}

	public function connectErrlog($errno, $error) {
		return true;
	}

	function connectCookieLoginReport($loginTimes) {
		global $_G;

		require_once DISCUZ_ROOT.'/source/plugin/qqconnect/lib/Util.php';
		$utilService = new Cloud_Service_Util();
		$response = '';

		if ($loginTimes) {
			$api_url = $_G['connect']['api_url'].'/connect/discuz/batchCookieReport';
			$params = array (
				'oauth_consumer_key' => $_G['setting']['connectappid'],
				'login_times' => $loginTimes,
				'date' => dgmdate(TIMESTAMP - 86400, 'Y-m-d'),
				'ts' => TIMESTAMP,
			);
			$params['sig'] = $this->connectGetSig($params, $this->connectGetSigKey());

			$response = $this->connectOutputPhp($api_url.'?', $utilService->httpBuildQuery($params, '', '&'));
		} else {
			$response = array('status' => 0);
		}

		return $response;
	}
}