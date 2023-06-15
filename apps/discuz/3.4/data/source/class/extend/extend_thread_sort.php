<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: extend_thread_sort.php 34201 2013-11-04 06:21:04Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class extend_thread_sort extends extend_thread_base {

	public function before_newthread() {
		if($_GET['typeoption']) {
			$this->param['extramessage'] = "\t".implode("\t", $_GET['typeoption']);
		}
	}

	public function after_newthread() {
		global $_G;

		$sortid = $this->param['sortid'];
		$pid = $this->pid;
		$_G['forum_optiondata'] = array();
		if($this->forum['threadsorts']['types'][$sortid] && !$this->forum['allowspecialonly']) {
			$_G['forum_optiondata'] = threadsort_validator($_GET['typeoption'], $pid);
		}

		if($this->forum['threadsorts']['types'][$sortid] && !empty($_G['forum_optiondata']) && is_array($_G['forum_optiondata'])) {
			$sortaids = array();
			$filedname = $valuelist = $separator = '';
			$fid = $this->forum['fid'];
			$tid = $this->tid;
			foreach($_G['forum_optiondata'] as $optionid => $value) {
				if($value) {
					$filedname .= $separator.$_G['forum_optionlist'][$optionid]['identifier'];
					$valuelist .= $separator."'".daddslashes($value)."'";
					$separator = ' ,';
				}

				if($_G['forum_optionlist'][$optionid]['type'] == 'image') {
					$identifier = $_G['forum_optionlist'][$optionid]['identifier'];
					$sortaids[] = intval($_GET['typeoption'][$identifier]['aid']);
				}
				$typeexpiration = intval($_GET['typeexpiration']);

				C::t('forum_typeoptionvar')->insert(array(
					'sortid' => $sortid,
					'tid' => $tid,
					'fid' => $fid,
					'optionid' => $optionid,
					'value' => $value,
					'expiration' => ($typeexpiration ? $this->param['publishdate'] + $typeexpiration : 0),
				));
			}

			if($filedname && $valuelist) {
				C::t('forum_optionvalue')->insert($sortid, "($filedname, tid, fid) VALUES ($valuelist, '{$tid}', '$fid')");
			}

			if($sortaids) {
				foreach($sortaids as $sortaid) {
					convertunusedattach($sortaid, $tid, $pid);
				}
			}
		}

	}

	public function before_editpost($parameters) {
		global $_G;
		$sortid = $parameters['sortid'];
		$isfirstpost = $this->post['first'] ? 1 : 0;
		if($isfirstpost) {

			$parameters['typeid'] = isset($this->forum['threadtypes']['types'][$parameters['typeid']]) ? $parameters['typeid'] : 0;
			if(!$this->forum['ismoderator'] && !empty($this->forum['threadtypes']['moderators'][$this->thread['typeid']])) {
				$parameters['typeid'] = $this->thread['typeid'];
			}
			$parameters['sortid'] = isset($this->forum['threadsorts']['types'][$parameters['sortid']]) ? $parameters['sortid'] : 0;
			$typeexpiration = intval($_GET['typeexpiration']);

			if(!$parameters['typeid'] && $this->forum['threadtypes']['required'] && !$this->thread['special']) {
				showmessage('post_type_isnull');
			}


			if($this->forum['threadsorts']['types'][$sortid] && $_G['forum_checkoption']) {
				$_G['forum_optiondata'] = threadsort_validator($_GET['typeoption'], $this->post['pid']);
			}

			$this->param['threadimageaid'] = 0;
			$this->param['threadimage'] = array();

			if($this->forum['threadsorts']['types'][$parameters['sortid']] && $_G['forum_optiondata'] && is_array($_G['forum_optiondata'])) {
				$sql = $separator = $filedname = $valuelist = '';
				foreach($_G['forum_optiondata'] as $optionid => $value) {
					$value = censor(daddslashes($value));
					if($_G['forum_optionlist'][$optionid]['type'] == 'image') {
						$identifier = $_G['forum_optionlist'][$optionid]['identifier'];
						$newsortaid = intval($_GET['typeoption'][$identifier]['aid']);
						if($newsortaid && $_GET['oldsortaid'][$identifier] && $newsortaid != $_GET['oldsortaid'][$identifier]) {
							$attach = C::t('forum_attachment_n')->fetch('tid:'.$this->thread['tid'], $_GET['oldsortaid'][$identifier]);
							C::t('forum_attachment')->delete($_GET['oldsortaid'][$identifier]);
							C::t('forum_attachment_n')->delete('tid:'.$this->thread['tid'], $_GET['oldsortaid'][$identifier]);
							dunlink($attach);
							$this->param['threadimageaid'] = $newsortaid;
							convertunusedattach($newsortaid, $this->thread['tid'], $this->post['pid']);
						}
					}
					if($_G['forum_optionlist'][$optionid]['unchangeable']) {
						continue;
					}
					if(($_G['forum_optionlist'][$optionid]['search'] || in_array($_G['forum_optionlist'][$optionid]['type'], array('radio', 'select', 'number'))) && $value) {
						$filedname .= $separator.$_G['forum_optionlist'][$optionid]['identifier'];
						$valuelist .= $separator."'$value'";
						$sql .= $separator.$_G['forum_optionlist'][$optionid]['identifier']."='$value'";
						$separator = ' ,';
					}
					C::t('forum_typeoptionvar')->update_by_tid($this->thread['tid'], array('value' => $value, 'sortid' => $parameters['sortid']), false, false, $optionid);
				}

				if($typeexpiration) {
					C::t('forum_typeoptionvar')->update_by_tid($this->thread['tid'], array('expiration' => (TIMESTAMP + $typeexpiration)), false, false, null, $parameters['sortid']);
				}

				if($sql || ($filedname && $valuelist)) {
					if(C::t('forum_optionvalue')->fetch_all_tid($parameters['sortid'], "WHERE tid='".$this->thread['tid']."'")) {
						if($sql) {
							C::t('forum_optionvalue')->update($parameters['sortid'], $this->thread['tid'], $this->forum['fid'], $sql);
						}
					} elseif($filedname && $valuelist) {
						C::t('forum_optionvalue')->insert($parameters['sortid'], "($filedname, tid, fid) VALUES ($valuelist, '".$this->thread['tid']."', '".$this->forum['fid']."')");
					}
				}
			}
		}
	}

	public function after_deletepost() {
		$isfirstpost = $this->post['first'] ? 1 : 0;
		if($isfirstpost) {
			C::t('forum_typeoptionvar')->delete_by_tid($this->thread['tid']);
		}
	}
}

?>