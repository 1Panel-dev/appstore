<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: ConnectOAuth.php 34191 2013-10-30 08:07:15Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once DISCUZ_ROOT.'/source/plugin/qqconnect/lib/OAuth.php';


class Cloud_Service_Client_ConnectOAuth extends Cloud_Service_Client_OAuth {

	private $_requestTokenURL = 'http://openapi.qzone.qq.com/oauth/qzoneoauth_request_token';

	private $_oAuthAuthorizeURL = 'http://openapi.qzone.qq.com/oauth/qzoneoauth_authorize';

	private $_accessTokenURL = 'http://openapi.qzone.qq.com/oauth/qzoneoauth_access_token';

	private $_getUserInfoURL = 'http://openapi.qzone.qq.com/user/get_user_info';

	private $_addShareURL = 'http://openapi.qzone.qq.com/share/add_share';

	private $_addTURL = 'http://openapi.qzone.qq.com/t/add_t';

	private $_addPicTURL = 'http://openapi.qzone.qq.com/t/add_pic_t';

	private $_getReportListURL = 'http://openapi.qzone.qq.com/t/get_repost_list';

	private $_oAuthAuthorizeURL_V2 = 'https://graph.qq.com/oauth2.0/authorize';

	private $_accessTokenURL_V2 = 'https://graph.qq.com/oauth2.0/token';

	private $_openIdURL_V2 = 'https://graph.qq.com/oauth2.0/me';

	private $_getUserInfoURL_V2 = 'https://graph.qq.com/user/get_user_info';

	private $_addShareURL_V2 = 'https://graph.qq.com/share/add_share';

	private $_addTURL_V2 = 'https://graph.qq.com/t/add_t';

	private $_addPicTURL_V2 = 'https://graph.qq.com/t/add_pic_t';

	const RESPONSE_ERROR = 999;
	const RESPONSE_ERROR_MSG = 'request failed';

	protected static $_instance;

	public static function getInstance($connectAppId = '', $connectAppKey = '', $apiIp = '') {

		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self($connectAppId = '', $connectAppKey = '', $apiIp = '');
		}

		return self::$_instance;
	}

	public function __construct($connectAppId = '', $connectAppKey = '', $apiIp = '') {

		if(!$connectAppId || !$connectAppKey) {
			global $_G;
			$connectAppId = $_G['setting']['connectappid'];
			$connectAppKey = $_G['setting']['connectappkey'];
		}
		$this->setAppkey($connectAppId, $connectAppKey);
		if(!$this->_appKey || !$this->_appSecret) {
			throw new Exception('connectAppId/connectAppKey Invalid', __LINE__);
		}

		if(!$apiIp) {
			global $_G;
			$apiIp = $_G['setting']['connect_api_ip'] ? $_G['setting']['connect_api_ip'] : '';
		}

		if($apiIp && !$_G['setting']['connect']['oauth2']) {
			$this->setApiIp($apiIp);
		}
	}

	public function connectGetRequestToken($callback, $clientIp = '') {

		$extra = array();

		$extra['oauth_callback'] = rawurlencode($callback);

		if ($clientIp) {
			$extra['oauth_client_ip'] = $clientIp;
		}

		$this->setTokenSecret('');
		$response = $this->_request($this->_requestTokenURL, $extra);

		parse_str($response, $params);
		if($params['oauth_token'] && $params['oauth_token_secret']) {
			return $params;
		} else {
			$params['error_code'] = $params['error_code'] ? $params['error_code'] : self::RESPONSE_ERROR;
			throw new Exception($params['error_code'], __LINE__);
		}

	}

	public function getOAuthAuthorizeURL($requestToken) {

		$params = array(
			'oauth_consumer_key' => $this->_appKey,
			'oauth_token' => $requestToken,
		);
		require_once DISCUZ_ROOT.'/source/plugin/qqconnect/lib/Util.php';
		$utilService = new Cloud_Service_Util();
		$oAuthAuthorizeURL = $this->_oAuthAuthorizeURL.'?'.$utilService->httpBuildQuery($params, '', '&');

		return $oAuthAuthorizeURL;
	}

	private function _connectIsValidOpenid($openId, $timestamp, $sig) {

		$key = $this->_appSecret;
		$str = $openId.$timestamp;
		$signature = $this->customHmac($str, $key);
		return $sig == $signature;
	}

	public function connectGetAccessToken($params, $requestTokenSecret) {

		if(!$this->_connectIsValidOpenid($params['openid'], $params['timestamp'], $params['oauth_signature'])) {
			throw new Exception('openId signature invalid', __LINE__);
		}

		if(!$params['oauth_token'] || !$params['oauth_vericode']) {
			throw new Exception('requestToken/vericode invalid', __LINE__);
		}

		$extra = array(
			'oauth_token' => $params['oauth_token'],
			'oauth_vericode' => $params['oauth_vericode'],
		);
		$this->setTokenSecret($requestTokenSecret);
		$response = $this->_request($this->_accessTokenURL, $extra);

		parse_str($response, $result);
		if($result['oauth_token'] && $result['oauth_token_secret'] && $result['openid']) {
			return $result;
		} else {
			$result['error_code'] = $result['error_code'] ? $result['error_code'] : self::RESPONSE_ERROR;
			throw new Exception($result['error_code'], __LINE__);
		}
	}

	public function connectGetUserInfo($openId, $accessToken, $accessTokenSecret) {

		$extra = array(
			'oauth_token' => $accessToken,
			'openid' => $openId,
			'format' => 'xml',
		);
		$this->setTokenSecret($accessTokenSecret);
		$response = $this->_request($this->_getUserInfoURL, $extra);

		$data = $this->_xmlParse($response);
		if(isset($data['ret']) && $data['ret'] == 0) {
			return $data;
		} else {
			throw new Exception($data['msg'], $data['ret']);
		}
	}

	private function _request($requestURL, $extra = array(), $oauthMethod = 'GET', $multi) {

		if(!$this->_appKey || !$this->_appSecret) {
			throw new Exception('appKey or appSecret not init');
		}

		if(strtoupper(CHARSET) != 'UTF-8') {
			foreach((array)$extra as $k => $v) {
				$extra[$k] = diconv($v, CHARSET, 'UTF-8');
			}
		}

		return $this->getRequest($requestURL, $extra, $oauthMethod, $multi);
	}

	private function _xmlParse($data) {

		require_once DISCUZ_ROOT.'/source/plugin/qqconnect/lib/Connect.php';
		$connectService = new Cloud_Service_Connect();
		$data = $connectService->connectParseXml($data);
		if (strtoupper(CHARSET) != 'UTF-8') {
			$data = $this->_iconv($data, 'UTF-8', CHARSET);
		}

		if(!isset($data['ret']) && !isset($data['errcode'])) {
			$data = array(
				'ret' => self::RESPONSE_ERROR,
				'msg' => self::RESPONSE_ERROR_MSG
			);
		}

		return $data;
	}

	private function _iconv($data, $inputCharset, $outputCharset) {
		if (is_array($data)) {
			foreach($data as $key => $val) {
				$value = array_map(array(__CLASS__, '_iconv'), array($val), array($inputCharset), array($outputCharset));
				$result[$key] = $value[0];
			}
		} else {
			$result = diconv($data, $inputCharset, $outputCharset);
		}
		return $result;

	}

	public function connectAddShare($openId, $accessToken, $accessTokenSecret, $params) {
		if(!$params['title'] || !$params['url']) {
			throw new Exception('Required Parameter Missing');
		}

		$paramsName = array('title', 'url', 'comment', 'summary', 'images', 'source', 'type', 'playurl', 'nswb');

		if($params['title']) {
			$params['title'] = cutstr($params['title'], 72, '');
		}

		if($params['comment']) {
			$params['comment'] = cutstr($params['comment'], 80, '');
		}

		if($params['summary']) {
			$params['summary'] = cutstr($params['summary'], 160, '');
		}

		if($params['images']) {
			$params['images'] = cutstr($params['images'], 255, '');
		}

		if($params['playurl']) {
			$params['playurl'] = cutstr($params['playurl'], 256, '');
		}

		$extra = array(
			'oauth_token' => $accessToken,
			'openid' => $openId,
			'format' => 'xml',
		);

		foreach($paramsName as $name) {
			if($params[$name]) {
				$extra[$name] = $params[$name];
			}
		}

		$this->setTokenSecret($accessTokenSecret);
		$response = $this->_request($this->_addShareURL, $extra, 'POST');

		$data = $this->_xmlParse($response);
		if(isset($data['ret']) && $data['ret'] == 0) {
			return $data;
		} else {
			throw new Exception($data['msg'], $data['ret']);
		}

	}

	public function connectAddPicT($openId, $accessToken, $accessTokenSecret, $params) {
		if(!$params['content'] || !$params['pic']) {
			throw new Exception('Required Parameter Missing');
		}

		$paramsName = array('content', 'pic', 'clientip', 'jing', 'wei', 'syncflag');
		$extra = array(
			'oauth_token' => $accessToken,
			'openid' => $openId,
			'format' => 'xml',
		);

		foreach($paramsName as $name) {
			if($params[$name]) {
				$extra[$name] = $params[$name];
			}
		}
		$pic = $extra['pic'];
		unset($extra['pic']);

		$this->setTokenSecret($accessTokenSecret);
		$response = $this->_request($this->_addPicTURL, $extra, 'POST', array('pic' => $pic, 'remote' => $params['remote'] ? true : false));

		$data = $this->_xmlParse($response);
		if(isset($data['ret']) && $data['ret'] == 0) {
			return $data;
		} else {
			throw new Exception($data['msg'], $data['ret']);
		}
	}


	public function connectAddT($openId, $accessToken, $accessTokenSecret, $params) {
		if(!$params['content']) {
			throw new Exception('Required Parameter Missing');
		}

		$paramsName = array('content', 'clientip', 'jing', 'wei');
		$extra = array(
			'oauth_token' => $accessToken,
			'openid' => $openId,
			'format' => 'xml',
		);

		foreach($paramsName as $name) {
			if($params[$name]) {
				$extra[$name] = $params[$name];
			}
		}

		$this->setTokenSecret($accessTokenSecret);
		$response = $this->_request($this->_addTURL, $extra, 'POST');

		$data = $this->_xmlParse($response);
		if(isset($data['ret']) && $data['ret'] == 0) {
			return $data;
		} else {
			throw new Exception($data['msg'], $data['ret']);
		}

	}

	public function connectGetRepostList($openId, $accessToken, $accessTokenSecret, $params) {
		if(!isset($params['flag']) || !$params['rootid'] || !isset($params['pageflag']) || !isset($params['pagetime']) || !$params['reqnum'] || !isset($params['twitterid'])) {
			throw new Exception('Required Parameter Missing');
		}

		$paramsName = array('flag', 'rootid', 'pageflag', 'pagetime', 'reqnum', 'twitterid');
		$extra = array(
			'oauth_token' => $accessToken,
			'openid' => $openId,
			'format' => 'xml',
		);

		foreach($paramsName as $name) {
			if($params[$name]) {
				$extra[$name] = $params[$name];
			}
		}
		$this->setTokenSecret($accessTokenSecret);
		$response = $this->_request($this->_getReportListURL, $extra, 'GET');
		$data = $this->_xmlParse($response);
		if(isset($data['ret']) && $data['ret'] == 0) {
			return $data;
		} else {
			throw new Exception($data['msg'], $data['ret']);
		}
	}

	public function getOAuthAuthorizeURL_V2($redirect_uri) {
		$params = array(
			'response_type' => 'code',
			'client_id' => $this->_appKey,
			'redirect_uri' => $redirect_uri,
			'state' => md5(FORMHASH),
			'scope' => 'get_user_info,add_share,add_t,add_pic_t,get_repost_list',
		);
		require_once DISCUZ_ROOT.'/source/plugin/qqconnect/lib/Util.php';
		$utilService = new Cloud_Service_Util();
		return $this->_oAuthAuthorizeURL_V2.'?'.$utilService->httpBuildQuery($params, '', '&');
	}

	public function connectGetOpenId_V2($redirect_uri, $code) {
		$params = array(
			'grant_type' => 'authorization_code',
			'client_id' => $this->_appKey,
			'redirect_uri' => $redirect_uri,
			'client_secret' => $this->_appSecret,
			'code' => $code
		);
		require_once DISCUZ_ROOT.'/source/plugin/qqconnect/lib/Util.php';
		$utilService = new Cloud_Service_Util();
		$response = $this->dfsockopen($this->_accessTokenURL_V2.'?'.$utilService->httpBuildQuery($params, '', '&'));
		parse_str($response, $result);
		if($result['access_token'] && $result['refresh_token']) {
			$params = array(
				'access_token' => $result['access_token']
			);
			$response = $this->callback($this->dfsockopen($this->_openIdURL_V2.'?'.$utilService->httpBuildQuery($params, '', '&')));
			if($response->openid) {
				$result = array(
					'openid' => $response->openid,
					'access_token' => $result['access_token']
				);
				return $result;
			} else {
				$result->error = $result->error ? $result->error : self::RESPONSE_ERROR;
				throw new Exception($result->error, __LINE__);
			}
		} else {
			$result = $this->callback($response);
			$result->error = $result->error ? $result->error : self::RESPONSE_ERROR;
			throw new Exception($result->error, __LINE__);
		}
	}

	public function connectGetUserInfo_V2($openId, $accessToken) {
		$params = array(
			'access_token' => $accessToken,
			'oauth_consumer_key' => $this->_appKey,
			'openid' => $openId
		);
		require_once DISCUZ_ROOT.'/source/plugin/qqconnect/lib/Util.php';
		$utilService = new Cloud_Service_Util();
		$response = $this->dfsockopen($this->_getUserInfoURL_V2.'?'.$utilService->httpBuildQuery($params, '', '&'));

		$data = json_decode($response, true);
		$data = $this->_iconv($data, 'UTF-8', CHARSET);

		if(isset($data['ret']) && $data['ret'] == 0) {
			return $data;
		} else {
			throw new Exception($data['msg'], $data['ret']);
		}
	}

	public function connectAddShare_V2($openId, $accessToken, $params) {
		if(!$params['title'] || !$params['url']) {
			throw new Exception('Required Parameter Missing');
		}

		$paramsName = array('title', 'url', 'comment', 'summary', 'images', 'source', 'type', 'playurl', 'nswb');

		if($params['title']) {
			$params['title'] = diconv(cutstr($params['title'], 72, ''), CHARSET, 'UTF-8');
		}

		if($params['comment']) {
			$params['comment'] = diconv(cutstr($params['comment'], 80, ''), CHARSET, 'UTF-8');
		}

		if($params['summary']) {
			$params['summary'] = diconv(cutstr($params['summary'], 160, ''), CHARSET, 'UTF-8');
		}

		if($params['images']) {
			$params['images'] = cutstr($params['images'], 255, '');
		}

		if($params['playurl']) {
			$params['playurl'] = cutstr($params['playurl'], 256, '');
		}

		$extra = array(
			'access_token' => $accessToken,
			'oauth_consumer_key' => $this->_appKey,
			'openid' => $openId,
			'format' => 'xml',
		);

		foreach($paramsName as $name) {
			if($params[$name]) {
				$extra[$name] = $params[$name];
			}
		}

		$response = $this->dfsockopen($this->_addShareURL_V2, $extra);

		$data = $this->_xmlParse($response);
		if(isset($data['ret']) && $data['ret'] == 0) {
			return $data;
		} else {
			throw new Exception($data['msg'], $data['ret']);
		}

	}

	public function connectAddPicT_V2($openId, $accessToken, $params) {
		if(!$params['content'] || !$params['pic']) {
			throw new Exception('Required Parameter Missing');
		}

		$extra = array(
			'access_token' => $accessToken,
			'oauth_consumer_key' => $this->_appKey,
			'openid' => $openId,
			'content' => diconv($params['content'], CHARSET, 'UTF-8'),
			'format' => 'xml',
		);

		$response = $this->dfsockopen($this->_addPicTURL_V2, $extra, array('pic' => $params['pic']));

		$data = $this->_xmlParse($response);
		if(isset($data['ret']) && $data['ret'] == 0) {
			return $data;
		} else {
			throw new Exception($data['msg'], $data['ret']);
		}
	}

	public function connectAddT_V2($openId, $accessToken, $params) {
		if(!$params['content']) {
			throw new Exception('Required Parameter Missing');
		}
		$extra = array(
			'access_token' => $accessToken,
			'oauth_consumer_key' => $this->_appKey,
			'openid' => $openId,
			'content' => diconv($params['content'], CHARSET, 'UTF-8'),
			'format' => 'xml',
		);

		$response = $this->dfsockopen($this->_addTURL_V2, $extra);

		$data = $this->_xmlParse($response);
		if(isset($data['ret']) && $data['ret'] == 0) {
			return $data;
		} else {
			throw new Exception($data['msg'], $data['ret']);
		}

	}

}