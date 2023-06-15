<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_faq.php 25246 2011-11-02 03:34:53Z zhangguosheng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();
$operation = $operation ? $operation : 'list';

if($operation == 'list') {

	if(!submitcheck('faqsubmit')) {

		shownav('extended', 'faq');
		showsubmenu('faq');
		showformheader('faq&operation=list');
		showtableheader();
		echo '<tr><th class="td25"></th><th><strong>'.$lang['display_order'].'</stong></th><th style="width:350px"><strong>'.$lang['faq_thread'].'</strong></th><th></th></tr>';

		$faqparent = $faqsub = array();
		$faqlists = $faqselect = '';
		foreach(C::t('forum_faq')->fetch_all_by_fpid() as $faq) {
			if(empty($faq['fpid'])) {
				$faqparent[$faq['id']] = $faq;
				$faqselect .= "<option value=\"$faq[id]\">$faq[title]</option>";
			} else {
				$faqsub[$faq['fpid']][] = $faq;
			}
		}

		foreach($faqparent as $parent) {
			$disabled = !empty($faqsub[$parent['id']]) ? 'disabled' : '';
			showtablerow('', array('', 'class="td23 td28"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$parent[id]\" $disabled>",
				"<input type=\"text\" class=\"txt\" size=\"3\" name=\"displayorder[$parent[id]]\" value=\"$parent[displayorder]\">",
				"<div class=\"parentnode\"><input type=\"text\" class=\"txt\" size=\"30\" name=\"title[$parent[id]]\" value=\"".dhtmlspecialchars($parent['title'])."\"></div>",
				"<a href=\"".ADMINSCRIPT."?action=faq&operation=detail&id=$parent[id]\" class=\"act\">".$lang['detail']."</a>"
			));
			if(!empty($faqsub[$parent['id']])) {
				foreach($faqsub[$parent['id']] as $sub) {
					showtablerow('', array('', 'class="td23 td28"'), array(
						"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$sub[id]\">",
						"<input type=\"text\" class=\"txt\" size=\"3\" name=\"displayorder[$sub[id]]\" value=\"$sub[displayorder]\">",
						"<div class=\"node\"><input type=\"text\" class=\"txt\" size=\"30\" name=\"title[$sub[id]]\" value=\"".dhtmlspecialchars($sub['title'])."\"></div>",
						"<a href=\"".ADMINSCRIPT."?action=faq&operation=detail&id=$sub[id]\" class=\"act\">".$lang['detail']."</a>"
					));
				}
			}
			echo '<tr><td></td><td></td><td colspan="2"><div class="lastnode"><a href="###" onclick="addrow(this, 1, '.$parent['id'].')" class="addtr">'.cplang('faq_additem').'</a></div></td></tr>';
		}
		echo '<tr><td></td><td></td><td colspan="2"><div><a href="###" onclick="addrow(this, 0, 0)" class="addtr">'.cplang('faq_addcat').'</a></div></td></tr>';

		echo <<<EOT
<script type="text/JavaScript">
var rowtypedata = [
	[[1,''], [1,'<input name="newdisplayorder[]" value="" size="3" type="text" class="txt">', 'td25'], [1, '<input name="newtitle[]" value="" size="30" type="text" class="txt">'], [1, '<input type="hidden" name="newfpid[]" value="0" />']],
	[[1,''], [1,'<input name="newdisplayorder[]" value="" size="3" type="text" class="txt">', 'td25'], [1, '<div class=\"node\"><input name="newtitle[]" value="" size="30" type="text" class="txt"></div>'], [1, '<input type="hidden" name="newfpid[]" value="{1}" />']]
];
</script>
EOT;

		showsubmit('faqsubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		if($_GET['delete']) {
			C::t('forum_faq')->delete($_GET['delete']);
		}

		if(is_array($_GET['title'])) {
			foreach($_GET['title'] as $id => $val) {
				C::t('forum_faq')->update($id, array(
					'displayorder' => $_GET['displayorder'][$id],
					'title' => $_GET['title'][$id]
				));
			}
		}

		if(is_array($_GET['newtitle'])) {
			foreach($_GET['newtitle'] as $k => $v) {
				$v = trim($v);
				if($v) {
					C::t('forum_faq')->insert(array(
						'fpid' => intval($_GET['newfpid'][$k]),
						'displayorder' => intval($_GET['newdisplayorder'][$k]),
						'title' => $v
					));
				}
			}
		}

		cpmsg('faq_list_update', 'action=faq&operation=list', 'succeed');

	}

} elseif($operation == 'detail') {
	$id = $_GET['id'];
	if(!submitcheck('detailsubmit')) {

		$faq = C::t('forum_faq')->fetch($id);
		if(!$faq) {
			cpmsg('faq_nonexistence', '', 'error');
		}

		foreach(C::t('forum_faq')->fetch_all_by_fpid(0) as $parent) {
			$faqselect .= "<option value=\"$parent[id]\" ".($faq['fpid'] == $parent['id'] ? 'selected' : '').">$parent[title]</option>";
		}

		shownav('extended', 'faq');
		showsubmenu('faq');
		showformheader("faq&operation=detail&id=$id");
		showtableheader();
		showtitle('faq_edit');
		showsetting('faq_title', 'titlenew', $faq['title'], 'text');
		if(!empty($faq['fpid'])) {
			showsetting('faq_sortup', '', '', '<select name="fpidnew"><option value=\"\">'.$lang['none'].'</option>'.$faqselect.'</select>');
			showsetting('faq_identifier', 'identifiernew', $faq['identifier'], 'text');
			showsetting('faq_keywords', 'keywordnew', $faq['keyword'], 'text');
			showsetting('faq_content', 'messagenew', $faq['message'], 'textarea');
		}
		showsubmit('detailsubmit');
		showtablefooter();
		showformfooter();

	} else {

		if(!$_GET['titlenew']) {
			cpmsg('faq_no_title', '', 'error');
		}

		if(!empty($_GET['identifiernew'])) {
			if(C::t('forum_faq')->check_identifier($_GET['identifiernew'], $id)) {
				cpmsg('faq_identifier_invalid', '', 'error');
			}
		}

		if(strlen($_GET['keywordnew']) > 50) {
			cpmsg('faq_keyword_toolong', '', 'error');
		}

		$fpidnew = $_GET['fpidnew'] ? intval($_GET['fpidnew']) : 0;
		$titlenew = trim($_GET['titlenew']);
		$messagenew = trim($_GET['messagenew']);
		$identifiernew = trim($_GET['identifiernew']);
		$keywordnew = trim($_GET['keywordnew']);

		C::t('forum_faq')->update($id, array(
			'fpid' => $fpidnew,
			'identifier' => $identifiernew,
			'keyword' => $keywordnew,
			'title' => $titlenew,
			'message' => $messagenew,
		));

		cpmsg('faq_list_update', 'action=faq&operation=list', 'succeed');

	}

}

?>