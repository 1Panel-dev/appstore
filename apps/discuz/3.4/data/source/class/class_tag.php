<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class_tag.php 28830 2012-03-14 08:30:08Z chenmengshu $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
class tag
{
	public function add_tag($tags, $itemid, $idtype = 'tid', $returnarray = 0) {
		if($tags == '' || !in_array($idtype, array('', 'tid', 'blogid', 'uid'))) {
			return;
		}

		$tags = str_replace(array(chr(0xa3).chr(0xac), chr(0xa1).chr(0x41), chr(0xef).chr(0xbc).chr(0x8c)), ',', censor($tags));
		if(strexists($tags, ',')) {
			$tagarray = array_unique(explode(',', $tags));
		} else {
			$langcore = lang('core');
			$tags = str_replace($langcore['fullblankspace'], ' ', $tags);
			$tagarray = array_unique(explode(' ', $tags));
		}
		$tagcount = 0;
		foreach($tagarray as $tagname) {
			$tagname = trim($tagname);
			if(preg_match('/^([\x7f-\xff_-]|\w|\s){2,20}$/', $tagname)) {
				$status = $idtype != 'uid' ? 0 : 3;
				$result = C::t('common_tag')->get_bytagname($tagname, $idtype);
				if($result['tagid']) {
					if($result['status'] == $status) {
						$tagid = $result['tagid'];
					}
				} else {
					$tagid = C::t('common_tag')->insert($tagname,$status);
				}
				if($tagid) {
					if($itemid) {
						C::t('common_tagitem')->replace($tagid,$itemid,$idtype);
					}
					$tagcount++;
					if(!$returnarray) {
						$return .= $tagid.','.$tagname."\t";
					} else {
						$return[$tagid] = $tagname;
					}

				}
				if($tagcount > 4) {
					unset($tagarray);
					break;
				}
			}
		}
		return $return;
	}

	public function update_field($tags, $itemid, $idtype = 'tid', $typeinfo = array()) {

		if($idtype == 'tid') {
			$tagstr = C::t('forum_post')->fetch_threadpost_by_tid_invisible($itemid);
			$tagstr = $tagstr['tags'];

		} elseif($idtype == 'blogid') {
			$tagstr = C::t('home_blogfield')->fetch_tag_by_blogid($itemid);

		} else {
			return '';
		}

		$tagarray = $tagidarray = $tagarraynew = array();
		$results = C::t('common_tagitem')->select(0, $itemid, $idtype);
		foreach($results as $result) {
			$tagidarray[] = $result['tagid'];
		}
		if($tagidarray) {
			$results = C::t('common_tag')->get_byids($tagidarray);
			foreach($results as $result) {
				$tagarray[$result[tagid]] = $result['tagname'];
			}
		}
		$tags = $this->add_tag($tags, $itemid, $idtype, 1);
		foreach($tags as $tagid => $tagname) {
			$tagarraynew[] = $tagname;
			if(empty($tagarray[$tagid])) {
				$tagstr = $tagstr.$tagid.','.$tagname."\t";
			}
		}
		foreach($tagarray as $tagid => $tagname) {
			if(!in_array($tagname, $tagarraynew)) {
				C::t('common_tagitem')->delete($tagid, $itemid, $idtype);
				$tagstr = str_replace("$tagid,$tagname\t", '', $tagstr);
			}
		}
		return $tagstr;
	}

	public function copy_tag($oldid, $newid, $idtype = 'tid') {
		$results = C::t('common_tagitem')->select(0, $oldid, $idtype);
		foreach($results as $result) {
			C::t('common_tagitem')->insert(array(
					'tagid' => $result['tagid'],
					'itemid' => $newid,
					'idtype' => $idtype
			));
		}
	}

	public function merge_tag($tagidarray, $newtag, $idtype = '') {
		$newtag = str_replace(',', '', $newtag);
		$newtag = trim($newtag);
		if(!$newtag) {
			return 'tag_empty';
		}
		if(preg_match('/^([\x7f-\xff_-]|\w|\s){2,20}$/', $newtag)) {
			$tidarray = $blogidarray = array();
			$newtaginfo = $this->add_tag($newtag, 0, $idtype, 1);
			foreach($newtaginfo as $tagid => $tagname) {
				$newid = $tagid;
			}
			$tagidarray = array_diff((array)$tagidarray, (array)$newid);
			if($idtype !== 'uid') {
				$tagnames = C::t('common_tag')->get_byids($tagidarray);
				$results = C::t('common_tagitem')->select($tagidarray);
				foreach($results as $result) {
					$result['tagname'] = addslashes($tagnames[$result[tagid]]['tagname']);
					if($result['idtype'] == 'tid') {
						$itemid = $result[itemid];
						if(!isset($tidarray[$itemid])) {
							$post = C::t('forum_post')->fetch_threadpost_by_tid_invisible($itemid);
							$tidarray[$itemid] = $post['tags'];
						}
						$tidarray[$itemid] = str_replace("$result[tagid],$result[tagname]\t", '', $tidarray[$itemid]);
					} elseif($result['idtype'] == 'blogid') {
						$itemid = $result[itemid];
						if(!isset($blogidarray[$itemid])) {
							$blogfield = C::t('home_blogfield')->fetch($itemid);
							$blogidarray[$itemid] = $blogfield['tag'];
						}
						$blogidarray[$itemid] = str_replace("$result[tagid],$result[tagname]\t", '', $blogidarray[$itemid]);
					}
				}
			}
			$checkunique = array();
			$checktagids = $tagidarray;
			$checktagids[] = $newid;
			$results = C::t('common_tagitem')->select($checktagids);
			foreach($results as $row) {
				if($checkunique[$row['itemid'].'_'.$row['idtype']] == 'deleted' || empty($checkunique[$row['itemid'].'_'.$row['idtype']])) {
					if(empty($checkunique[$row['itemid'].'_'.$row['idtype']])) {
						$checkunique[$row['itemid'].'_'.$row['idtype']] = 1;
					}
				} else {
					C::t('common_tagitem')->unique($row['tagid'], $row['itemid'], $row['idtype']);
					$checkunique[$row['itemid'].'_'.$row['idtype']] = 'deleted';
				}
			}
			C::t('common_tagitem')->merge_by_tagids($newid, $tagidarray);
			C::t('common_tag')->delete_byids($tagidarray);

			if($tidarray) {
				foreach($tidarray as $key => $var) {
					C::t('forum_post')->update_by_tid('tid:'.$key, $key, array('tags' => $var), false, false, 1);
					C::t('forum_post')->concat_threadtags_by_tid($key, "$newid,$newtag\t");
				}
			}
			if($blogidarray) {
				foreach($blogidarray as $key => $var) {
					C::t('home_blogfield')->update($key, array('tag' => $var.$newid.','.$newtag.'\t'));
				}
			}
		} else {
			return 'tag_length';
		}
		return 'succeed';
	}

	public function delete_tag($tagidarray, $idtype = '') {
		$tidarray = $blogidarray = array();
		if(!is_array($tagidarray)) {
			return false;
		}
		if($idtype != 'uid') {
			$tagnames = C::t('common_tag')->get_byids($tagidarray);
			$items = C::t('common_tagitem')->select($tagidarray);
			foreach($items as $result) {
				$result['tagname'] = addslashes($tagnames[$result[tagid]]['tagname']);
				if($result['idtype'] == 'tid') {
					$itemid = $result[itemid];
					if(!isset($tidarray[$itemid])) {
						$post = C::t('forum_post')->fetch_threadpost_by_tid_invisible($itemid);
						$tidarray[$itemid] = $post['tags'];
					}
					$tidarray[$itemid] = str_replace("$result[tagid],$result[tagname]\t", '', $tidarray[$itemid]);
				} elseif($result['idtype'] == 'blogid') {
					$itemid = $result[itemid];
					if(!isset($blogidarray[$itemid])) {
						$blogfield = C::t('home_blogfield')->fetch($itemid);
						$blogidarray[$itemid] = $blogfield['tag'];
					}
					$blogidarray[$itemid] = str_replace("$result[tagid],$result[tagname]\t", '', $blogidarray[$itemid]);
				}
			}
		}

		if($tidarray) {
			foreach($tidarray as $key => $var) {
				C::t('forum_post')->update_by_tid('tid:'.$key, $key, array('tags' => $var), false, false, 1);
			}
		}
		if($blogidarray) {
			foreach($blogidarray as $key => $var) {
				C::t('home_blogfield')->update($key, array('tag' => $var));
			}
		}
		C::t('common_tag')->delete_byids($tagidarray);
		C::t('common_tagitem')->delete($tagidarray);
		return true;
	}

}
?>