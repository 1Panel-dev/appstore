<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: notify_invite.php 34251 2013-11-25 03:10:11Z nemohou $
 */

define('IN_API', true);
define('CURSCRIPT', 'api');
define('DISABLEXSSCHECK', true);

require '../../source/class/class_core.php';
require '../../source/function/function_forum.php';

$discuz = C::app();
$discuz->init();

$apitype = empty($_GET['attach']) || !preg_match('/^[a-z0-9]+$/i', $_GET['attach']) ? 'alipay' : $_GET['attach'];
require_once DISCUZ_ROOT.'./api/trade/api_'.$apitype.'.php';
$PHP_SELF = $_SERVER['PHP_SELF'];
$_G['siteurl'] = dhtmlspecialchars($_G['scheme'].'://'.$_SERVER['HTTP_HOST'].preg_replace("/\/+(api\/trade)?\/*$/i", '', substr($PHP_SELF, 0, strrpos($PHP_SELF, '/'))).'/');
$notifydata = trade_notifycheck('invite');
if($notifydata['validator']) {
	$orderid = $notifydata['order_no'];
	$postprice = $notifydata['price'];
	$order = C::t('forum_order')->fetch($orderid);
	if($order && floatval($postprice) == floatval($order['price']) && ($apitype == 'tenpay' || $_G['setting']['ec_account'] == $_REQUEST['seller_email'])) {

		if($order['status'] == 1) {
			C::t('forum_order')->update($orderid, array('status' => '2', 'buyer' => "$notifydata[trade_no]\t$apitype", 'confirmdate' => $_G['timestamp']));
			$codes = $codetext = array();
			$dateline = TIMESTAMP;
			for($i=0; $i<$order['amount']; $i++) {
				$code = strtolower(random(6));
				$codetext[] = $code;
				$codes[] = "('0', '$code', '$dateline', '".($_G['group']['maxinviteday']?($_G['timestamp']+$_G['group']['maxinviteday']*24*3600):$_G['timestamp']+86400*10)."', '$order[email]', '$_G[clientip]', '$orderid')";
				$invitedata = array(
							'uid' => 0,
							'code' => $code,
							'dateline' => $dateline,
							'endtime' => $_G['group']['maxinviteday'] ? ($_G['timestamp']+$_G['group']['maxinviteday']*24*3600) : $_G['timestamp']+86400*10,
							'email' => $order['email'],
							'inviteip' => $_G['clientip'],
							'orderid' => $orderid
						);
				C::t('common_invite')->insert($invitedata);
			}
			C::t('forum_order')->delete_by_submitdate($_G['timestamp']-60*86400);

			$submitdate = dgmdate($order['submitdate']);
			$confirmdate = dgmdate(TIMESTAMP);
			if(!function_exists('sendmail')) {
				include libfile('function/mail');
			}
			$add_member_subject = $_G['setting']['bbname'].' - '.lang('forum/misc', 'invite_payment');
			$add_member_message = lang('email', 'invite_payment_email_message', array(
				'orderid' => $order['orderid'],
				'codetext' => implode('<br />', $codetext),
				'siteurl' => $_G['siteurl'],
				'bbname' => $_G['setting']['bbname'],
			));
			if(!sendmail($order['email'], $add_member_subject, $add_member_message)) {
				runlog('sendmail', "$order[email] sendmail failed.");
			}
		}

	}
}
if($notifydata['location']) {
	if($apitype == 'tenpay') {
		echo <<<EOS
<meta name="TENCENT_ONLINE_PAYMENT" content="China TENCENT">
<html>
<body>
<script language="javascript" type="text/javascript">
window.location.href='$_G[siteurl]misc.php?mod=buyinvitecode&action=paysucceed&orderid=$orderid';
</script>
</body>
</html>
EOS;
	} else {
		dheader('location: '.$_G['siteurl'].'misc.php?mod=buyinvitecode&action=paysucceed&orderid='.$orderid);
	}
} else {
	exit($notifydata['notify']);
}

?>