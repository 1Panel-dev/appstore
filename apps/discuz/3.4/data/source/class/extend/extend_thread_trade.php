<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: extend_thread_trade.php 34221 2013-11-15 09:10:23Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class extend_thread_trade extends extend_thread_base {

	private $trademessage;

	public function before_newthread($parameters) {

		$item_price = floatval($_GET['item_price']);
		$item_credit = intval($_GET['item_credit']);
		$_GET['item_name'] = censor($_GET['item_name']);
		if(!trim($_GET['item_name'])) {
			return $this->showmessage('trade_please_name');
		} elseif($this->group['maxtradeprice'] && $item_price > 0 && ($this->group['mintradeprice'] > $item_price || $this->group['maxtradeprice'] < $item_price)) {
			return $this->showmessage('trade_price_between', '', array('mintradeprice' => $this->group['mintradeprice'], 'maxtradeprice' => $this->group['maxtradeprice']));
		} elseif($this->group['maxtradeprice'] && $item_credit > 0 && ($this->group['mintradeprice'] > $item_credit || $this->group['maxtradeprice'] < $item_credit)) {
			return $this->showmessage('trade_credit_between', '', array('mintradeprice' => $this->group['mintradeprice'], 'maxtradeprice' => $this->group['maxtradeprice']));
		} elseif(!$this->group['maxtradeprice'] && $item_price > 0 && $this->group['mintradeprice'] > $item_price) {
			return $this->showmessage('trade_price_more_than', '', array('mintradeprice' => $this->group['mintradeprice']));
		} elseif(!$this->group['maxtradeprice'] && $item_credit > 0 && $this->group['mintradeprice'] > $item_credit) {
			return $this->showmessage('trade_credit_more_than', '', array('mintradeprice' => $this->group['mintradeprice']));
		} elseif($item_price <= 0 && $item_credit <= 0) {
			return $this->showmessage('trade_pricecredit_need');
		} elseif($_GET['item_number'] < 1) {
			return $this->showmessage('tread_please_number');
		}

		if(!empty($_FILES['tradeattach']['tmp_name'][0])) {
			$_FILES['attach'] = array_merge_recursive((array)$_FILES['attach'], $_FILES['tradeattach']);
		}

		if(($this->group['allowpostattach'] || $this->group['allowpostimage']) && is_array($_FILES['attach'])) {
			foreach($_FILES['attach']['name'] as $attachname) {
				if($attachname != '') {
					checklowerlimit('postattach', 0, 1, $this->forum['fid']);
					break;
				}
			}
		}

		$this->trademessage = $parameters['message'];
		$this->param['message'] = '';
	}

	public function after_newthread() {
		if(!$this->tid) {
			return;
		}
		$this->trademessage = preg_replace('/\[attachimg\](\d+)\[\/attachimg\]/is', '[attach]\1[/attach]', $this->trademessage);
		$pid = insertpost(array(
			'fid' => $this->forum['fid'],
			'tid' => $this->tid,
			'first' => '0',
			'author' => $this->member['username'],
			'authorid' => $this->member['uid'],
			'subject' => $this->param['subject'],
			'dateline' => getglobal('timestamp'),
			'message' => $this->trademessage,
			'useip' => getglobal('clientip'),
			'invisible' => 0,
			'anonymous' => $this->param['isanonymous'],
			'usesig' => $_GET['usesig'],
			'htmlon' => $this->param['htmlon'],
			'bbcodeoff' => 0,
			'smileyoff' => $this->param['smileyoff'],
			'parseurloff' => $this->param['parseurloff'],
			'attachment' => 0,
			'tags' => $this->param['tagstr'],
			'status' => (defined('IN_MOBILE') ? 8 : 0)
		));
		if(!empty($_GET['tradeaid'])) {
			convertunusedattach($_GET['tradeaid'], $this->tid, $pid);
		}

		($this->group['allowpostattach'] || $this->group['allowpostimage']) && ($_GET['attachnew'] || $_GET['tradeaid']) && updateattach($this->param['displayorder'] == -4 || $this->param['modnewthreads'], $this->tid, $pid, $_GET['attachnew']);
		require_once libfile('function/trade');
		$author = !$this->param['isanonymous'] ? $this->member['username'] : '';
		trade_create(array(
			'tid' => $this->tid,
			'pid' => $pid,
			'aid' => $_GET['tradeaid'],
			'item_expiration' => $_GET['item_expiration'],
			'thread' => $this->thread,
			'discuz_uid' => $this->member['uid'],
			'author' => $author,
			'seller' => empty($_GET['paymethod']) && $_GET['seller'] ? dhtmlspecialchars(trim($_GET['seller'])) : '',
			'tenpayaccount' => $_GET['tenpay_account'],
			'item_name' => $_GET['item_name'],
			'item_price' => $_GET['item_price'],
			'item_number' => $_GET['item_number'],
			'item_quality' => $_GET['item_quality'],
			'item_locus' => $_GET['item_locus'],
			'transport' => $_GET['transport'],
			'postage_mail' => $_GET['postage_mail'],
			'postage_express' => $_GET['postage_express'],
			'postage_ems' => $_GET['postage_ems'],
			'item_type' => $_GET['item_type'],
			'item_costprice' => $_GET['item_costprice'],
			'item_credit' => $_GET['item_credit'],
			'item_costcredit' => $_GET['item_costcredit']
		));
		$this->param['tradepid'] = $pid;


	}

	public function before_feed() {
		if(!empty($_GET['addfeed']) && $this->forum['allowfeed'] && !$this->param['isanonymous']) {
			$this->feed['icon'] = 'goods';
			$this->feed['title_template'] = 'feed_thread_goods_title';
			if($_GET['item_price'] > 0) {
				if($this->setting['creditstransextra'][5] != -1 && $_GET['item_credit']) {
					$this->feed['body_template'] = 'feed_thread_goods_message_1';
				} else {
					$this->feed['body_template'] = 'feed_thread_goods_message_2';
				}
			} else {
				$this->feed['body_template'] = 'feed_thread_goods_message_3';
			}
			$pid = $this->param['tradepid'];
			$this->feed['body_data'] = array(
				'itemname'=> "<a href=\"forum.php?mod=viewthread&do=tradeinfo&tid=".$this->tid."&pid=$pid\">$_GET[item_name]</a>",
				'itemprice'=> $_GET['item_price'],
				'itemcredit'=> $_GET['item_credit'],
				'creditunit'=> $this->setting['extcredits'][$this->setting['creditstransextra'][5]]['unit'].$this->setting['extcredits'][$this->setting['creditstransextra'][5]]['title']
			);
			if($_GET['tradeaid']) {
				$this->feed['images'] = array(getforumimg($_GET['tradeaid']));
				$this->feed['image_links'] = array("forum.php?mod=viewthread&do=tradeinfo&tid=".$this->tid."&pid=$pid");
			}
			if($_GET['tradeaid']) {
				$attachment = C::t('forum_attachment_n')->fetch('tid:'.$this->tid, $_GET['tradeaid']);
				if(in_array($attachment['filetype'], array('image/gif', 'image/jpeg', 'image/png'))) {
					$imgurl = $this->setting['attachurl'].'forum/'.($attachment['thumb'] && $attachment['filetype'] != 'image/gif' ? getimgthumbname($attachment['attachment']) : $attachment['attachment']);
					$this->feed['images'][] = $attachment['attachment'] ? $imgurl : '';
					$this->feed['image_links'][] = $attachment['attachment'] ? "forum.php?mod=viewthread&tid=".$this->tid : '';
				}
			}
		}
	}

	public function after_feed() {
		global $extra;
		$values = array('fid' => $this->forum['fid'], 'tid' => $this->tid, 'pid' => $this->pid, 'coverimg' => '');
		$values = array_merge($values, (array)$this->param['values'], $this->param['param']);
		if(!empty($_GET['continueadd'])) {
			showmessage('post_newthread_succeed', "forum.php?mod=post&action=reply&fid=".$this->forum['fid']."&tid=".$this->tid."&addtrade=yes", $values, array('header' => true));
		} else {
			showmessage('post_newthread_succeed', "forum.php?mod=viewthread&tid=".$this->tid."&extra=$extra", $values);
		}
	}

	public function before_newreply($parameters) {
		$item_price = floatval($_GET['item_price']);
		$item_credit = intval($_GET['item_credit']);
		if(!trim($_GET['item_name'])) {
			return $this->showmessage('trade_please_name');
		} elseif($this->group['maxtradeprice'] && $item_price > 0 && ($this->group['mintradeprice'] > $item_price || $this->group['maxtradeprice'] < $item_price)) {
			return $this->showmessage('trade_price_between', '', array('mintradeprice' => $this->group['mintradeprice'], 'maxtradeprice' => $this->group['maxtradeprice']));
		} elseif($this->group['maxtradeprice'] && $item_credit > 0 && ($this->group['mintradeprice'] > $item_credit || $this->group['maxtradeprice'] < $item_credit)) {
			return $this->showmessage('trade_credit_between', '', array('mintradeprice' => $this->group['mintradeprice'], 'maxtradeprice' => $this->group['maxtradeprice']));
		} elseif(!$this->group['maxtradeprice'] && $item_price > 0 && $this->group['mintradeprice'] > $item_price) {
			return $this->showmessage('trade_price_more_than', '', array('mintradeprice' => $this->group['mintradeprice']));
		} elseif(!$this->group['maxtradeprice'] && $item_credit > 0 && $this->group['mintradeprice'] > $item_credit) {
			return $this->showmessage('trade_credit_more_than', '', array('mintradeprice' => $this->group['mintradeprice']));
		} elseif($item_price <= 0 && $item_credit <= 0) {
			return $this->showmessage('trade_pricecredit_need');
		} elseif($_GET['item_number'] < 1) {
			return $this->showmessage('tread_please_number');
		}
	}

	public function after_newreply() {
		if(!$this->pid) {
			return;
		}
		if($this->param['special'] == 2 && $this->group['allowposttrade'] && $this->thread['authorid'] == $this->member['uid'] && !empty($_GET['trade']) && !empty($_GET['item_name'])) {
			$author = (!$this->param['isanonymous']) ? $this->member['username'] : '';
			require_once libfile('function/trade');
			trade_create(array(
				'tid' => $this->thread['tid'],
				'pid' => $this->pid,
				'aid' => $_GET['tradeaid'],
				'item_expiration' => $_GET['item_expiration'],
				'thread' => $this->thread,
				'discuz_uid' => $this->member['uid'],
				'author' => $author,
				'seller' => empty($_GET['paymethod']) && $_GET['seller'] ? dhtmlspecialchars(trim($_GET['seller'])) : '',
				'item_name' => $_GET['item_name'],
				'item_price' => $_GET['item_price'],
				'item_number' => $_GET['item_number'],
				'item_quality' => $_GET['item_quality'],
				'item_locus' => $_GET['item_locus'],
				'transport' => $_GET['transport'],
				'postage_mail' => $_GET['postage_mail'],
				'postage_express' => $_GET['postage_express'],
				'postage_ems' => $_GET['postage_ems'],
				'item_type' => $_GET['item_type'],
				'item_costprice' => $_GET['item_costprice'],
				'item_credit' => $_GET['item_credit'],
				'item_costcredit' => $_GET['item_costcredit']
			));

			if(!empty($_GET['tradeaid'])) {
				convertunusedattach($_GET['tradeaid'], $this->thread['tid'], $this->pid);
			}
		}

		if(!$this->forum['allowfeed'] || !$_GET['addfeed']) {
			$this->after_replyfeed();
		}
	}

	public function before_replyfeed() {
		if($this->forum['allowfeed'] && !$this->param['isanonymous']) {
			if($this->param['special'] == 2 && !empty($_GET['trade'])) {
				$creditstransextra = $this->setting['creditstransextra'];
				$extcredits = $this->setting['extcredits'];
				$this->feed['icon'] = 'goods';
				$this->feed['title_template'] = 'feed_thread_goods_title';
				if($_GET['item_price'] > 0) {
					if($creditstransextra[5] != -1 && $_GET['item_credit']) {
						$this->feed['body_template'] = 'feed_thread_goods_message_1';
					} else {
						$this->feed['body_template'] = 'feed_thread_goods_message_2';
					}
				} else {
					$this->feed['body_template'] = 'feed_thread_goods_message_3';
				}
				$this->feed['body_data'] = array(
					'itemname'=> "<a href=\"forum.php?mod=viewthread&do=tradeinfo&tid=".$this->thread['tid']."&pid=".$this->pid."\">".dhtmlspecialchars($_GET['item_name'])."</a>",
					'itemprice'=> $_GET['item_price'],
					'itemcredit'=> $_GET['item_credit'],
					'creditunit'=> $extcredits[$creditstransextra[5]]['unit'].$extcredits[$creditstransextra[5]]['title'],
				);
				if($_GET['tradeaid']) {
					$this->feed['images'] = array(getforumimg($_GET['tradeaid']));
					$this->feed['image_links'] = array("forum.php?mod=viewthread&do=tradeinfo&tid=".$this->thread['tid']."&pid=".$this->pid);
				}
			}
		}
	}

	public function after_replyfeed() {
		global $extra;
		if($this->param['special'] == 2 && $this->group['allowposttrade'] && $this->thread['authorid'] == $this->member['uid']) {
			if(!empty($_GET['continueadd'])) {
				dheader("location: forum.php?mod=post&action=reply&fid=".$this->forum['fid']."&firstpid=".$this->pid."&tid=".$this->thread['tid']."&addtrade=yes");
			} else {
				if($this->param['modnewreplies']) {
					$url = "forum.php?mod=viewthread&tid=".$this->thread['tid'];
				} else {
					$url = "forum.php?mod=viewthread&tid=".$this->thread['tid']."&pid=".$this->pid."&page=".$this->param['page']."&extra=".$extra."#pid".$this->pid;
				}
				return $this->showmessage('trade_add_succeed', $url, $this->param['showmsgparam']);
			}
		}
	}

	public function before_editpost($parameters) {
		global $closed;
		if($parameters['special'] == 2 && $this->group['allowposttrade']) {

			if($trade = C::t('forum_trade')->fetch_goods($this->thread['tid'], $this->post['pid'])) {
				$seller = empty($_GET['paymethod']) && $_GET['seller'] ? censor(dhtmlspecialchars(trim($_GET['seller']))) : '';
				$item_name = censor(dhtmlspecialchars(trim($_GET['item_name'])));
				$item_price = floatval($_GET['item_price']);
				$item_credit = intval($_GET['item_credit']);
				$item_locus = censor(dhtmlspecialchars(trim($_GET['item_locus'])));
				$item_number = intval($_GET['item_number']);
				$item_quality = intval($_GET['item_quality']);
				$item_transport = intval($_GET['item_transport']);
				$postage_mail = intval($_GET['postage_mail']);
				$postage_express = intval(trim($_GET['postage_express']));
				$postage_ems = intval($_GET['postage_ems']);
				$item_type = intval($_GET['item_type']);
				$item_costprice = floatval($_GET['item_costprice']);

				if(!trim($item_name)) {
					showmessage('trade_please_name');
				} elseif($this->group['maxtradeprice'] && $item_price > 0 && ($this->group['mintradeprice'] > $item_price || $this->group['maxtradeprice'] < $item_price)) {
					showmessage('trade_price_between', '', array('mintradeprice' => $this->group['mintradeprice'], 'maxtradeprice' => $this->group['maxtradeprice']));
				} elseif($this->group['maxtradeprice'] && $item_credit > 0 && ($this->group['mintradeprice'] > $item_credit || $this->group['maxtradeprice'] < $item_credit)) {
					showmessage('trade_credit_between', '', array('mintradeprice' => $this->group['mintradeprice'], 'maxtradeprice' => $this->group['maxtradeprice']));
				} elseif(!$this->group['maxtradeprice'] && $item_price > 0 && $this->group['mintradeprice'] > $item_price) {
					showmessage('trade_price_more_than', '', array('mintradeprice' => $this->group['mintradeprice']));
				} elseif(!$this->group['maxtradeprice'] && $item_credit > 0 && $this->group['mintradeprice'] > $item_credit) {
					showmessage('trade_credit_more_than', '', array('mintradeprice' => $this->group['mintradeprice']));
				} elseif($item_price <= 0 && $item_credit <= 0) {
					showmessage('trade_pricecredit_need');
				} elseif($item_number < 1) {
					showmessage('tread_please_number');
				}

				if($trade['aid'] && $_GET['tradeaid'] && $trade['aid'] != $_GET['tradeaid']) {
					$attach = C::t('forum_attachment_n')->fetch('tid:'.$this->thread['tid'], $trade['aid']);
					C::t('forum_attachment')->delete($trade['aid']);
					C::t('forum_attachment_n')->delete('tid:'.$this->thread['tid'], $trade['aid']);
					dunlink($attach);
					$this->param['threadimageaid'] = $_GET['tradeaid'];
					convertunusedattach($_GET['tradeaid'], $this->thread['tid'], $this->post['pid']);
				}

				$expiration = $_GET['item_expiration'] ? @strtotime($_GET['item_expiration']) : 0;
				$closed = $expiration > 0 && @strtotime($_GET['item_expiration']) < TIMESTAMP ? 1 : $closed;

				switch($_GET['transport']) {
					case 'seller':$item_transport = 1;break;
					case 'buyer':$item_transport = 2;break;
					case 'virtual':$item_transport = 3;break;
					case 'logistics':$item_transport = 4;break;
				}
				if(!$item_price || $item_price <= 0) {
					$item_price = $postage_mail = $postage_express = $postage_ems = '';
				}

				$data = array('aid' => $_GET['tradeaid'], 'account' => $seller, 'tenpayaccount' => $_GET['tenpay_account'], 'subject' => $item_name, 'price' => $item_price, 'amount' => $item_number, 'quality' => $item_quality, 'locus' => $item_locus, 'transport' => $item_transport, 'ordinaryfee' => $postage_mail, 'expressfee' => $postage_express, 'emsfee' => $postage_ems, 'itemtype' => $item_type, 'expiration' => $expiration, 'closed' => $closed, 'costprice' => $item_costprice, 'credit' => $item_credit, 'costcredit' => $_GET['item_costcredit']);
				C::t('forum_trade')->update($this->thread['tid'], $this->post['pid'], $data);
				if(!empty($_GET['infloat'])) {
					$viewpid = C::t('forum_post')->fetch_threadpost_by_tid_invisible($this->thread['tid']);
					$viewpid = $viewpid['pid'];
					$this->param['redirecturl'] = "forum.php?mod=viewthread&tid=".$this->thread['tid']."&viewpid=$viewpid#pid$viewpid";
				} else {
					$this->param['redirecturl'] = "forum.php?mod=viewthread&do=tradeinfo&tid=".$this->thread['tid']."&pid=".$this->post['pid'];
				}
			}

		}
	}

	public function after_deletepost() {
		if($this->thread['special'] == 2) {
			C::t('forum_trade')->delete_by_id_idtype($this->post['pid'], 'pid');
		}
	}
}

?>