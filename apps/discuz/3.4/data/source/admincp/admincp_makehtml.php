<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_makehtml.php 35041 2014-10-29 08:05:36Z nemohou $
 */

if(!defined('IN_DISCUZ') || !defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$operation = in_array($operation, array('all', 'index', 'category', 'article', 'topic', 'aids', 'catids', 'topicids', 'makehtmlsetting', 'cleanhtml')) ? $operation : 'all';

cpheader();
shownav('portal', 'nav_makehtml');

$css = '<style>
		#mk_result {width:100%; margin-top:10px; border: 1px solid #ccc; margin: 0 auto; font-size:16px; text-align:center; display:none; }
		#mk_article, #mk_category, #mk_index{ line-height:30px;}
		#progress_bar{ width:400px; height:25px; border:1px solid #09f; margin: 10px auto 0; display:none;}
		.mk_msg{ width:100%; line-height:120px;}
		</style>';

$result = '<tr><td colspan="15"><div id="mk_result">
			<div id="progress_bar"></div>
			<div id="mk_topic" mktitle="'.$lang['makehtml_topic'].'"></div>
			<div id="mk_article" mktitle="'.$lang['makehtml_article'].'"></div>
			<div id="mk_category" mktitle="'.$lang['makehtml_category'].'"></div>
			<div id="mk_index" mktitle="'.$lang['makehtml_index'].'"></div>
			</div></td></tr>';

if(!in_array($operation, array('aids', 'catids', 'topicids'))) {
	$_nav = array();
	if(!empty($_G['setting']['makehtml']['flag'])) {
		$_nav = array(
			array('makehtml_createall', 'makehtml&operation=all', $operation == 'all'),
			array('makehtml_createindex', 'makehtml&operation=index', $operation == 'index'),
			array('makehtml_createcategory', 'makehtml&operation=category', $operation == 'category'),
			array('makehtml_createarticle', 'makehtml&operation=article', $operation == 'article'),
			array('makehtml_createtopic', 'makehtml&operation=topic', $operation == 'topic')
		);
	}
	$_nav[] = array('config', 'makehtml&operation=makehtmlsetting', $operation == 'makehtmlsetting');
	if(empty($_G['setting']['makehtml']['flag'])) {
		$_nav[] = array('makehtml_clear', 'makehtml&operation=cleanhtml', $operation == 'cleanhtml');
	}
	showsubmenu('html', $_nav, '');
}
if($operation == 'all') {
	showtips('makehtml_tips_all');

	showformheader('makehtml&operation=all');
	showtableheader('');
	echo '<script type="text/javascript" src="'.STATICURL.'js/calendar.js"></script>',
		'<script type="text/javascript" src="'.STATICURL.'js/makehtml.js?1"></script>',
		$css;
	showsetting('start_time', 'starttime', dgmdate(TIMESTAMP, 'Y-m-d'), 'calendar', '', '', '', '1');
	echo '<tr><td colspan="15"><div class="fixsel"><a href="javascript:void(0);" class="btn_big" id="submit_portal_html">'.$lang['makehtml_createall'].'</a></div></td></tr>', $result;
	$adminscript = ADMINSCRIPT;
	echo <<<EOT
<script type="text/JavaScript">
var form = document.forms['cpform'];
form.onsubmit = function(){return false;};
_attachEvent($('submit_portal_html'), 'click', function(){
	$('mk_result').style.display = 'block';
	$('mk_index').style.display = 'none';
	this.innerHTML = '$lang[makehtml_recreate]';
	var starttime = form['starttime'].value;
	if(starttime){
		make_html_article(starttime);
	}
	return false;
});

function make_html_ok() {
	var dom = $('mk_index');
	dom.innerHTML = '<div class="mk_msg">$lang[makehtml_allfilecomplete]</div>';
}
function make_html_index() {
	var dom = $('mk_index');
	dom.innerHTML = '<div class="mk_msg">$lang[makehtml_waitmaking]</div>';
	dom.style.display = 'block';
	new make_html_batch('portal.php?', 0, make_html_ok, dom, 1);
}

function make_html_category(starttime){
	var dom = $('mk_category');
	dom.innerHTML = '<div class="mk_msg">$lang[makehtml_waitmakingcategory]</div>';
	dom.style.display = 'block';
	starttime = starttime || form['starttime'].value;
	var x = new Ajax();
	x.get('$adminscript?action=makehtml&operation=catids&inajax=1&frame=no&starttime='+starttime, function (s) {
		if(s) {
			new make_html_batch('portal.php?mod=list&catid=', s.split(','), make_html_topic, dom);
		} else {
			dom.innerHTML = '$lang[makehtml_nofindcategory]<br/>$lang[makehtml_startmaketopic]<br /><a href="javascript:void(0);" onclick="\$(\'mk_category\').style.display = \'none\';make_html_topic();">$lang[makehtml_browser_error]</a>';
			setTimeout(function(){\$('mk_category').style.display = 'none'; make_html_topic();}, 1000);
		}
	});
}

function make_html_topic(starttime){
	var dom = $('mk_topic');
	dom.innerHTML = '<div class="mk_msg">$lang[makehtml_waitchecktopic]</div>';
	dom.style.display = 'block';
	starttime = starttime || form['starttime'].value;
	var x = new Ajax();
	x.get('$adminscript?action=makehtml&operation=topicids&inajax=1&frame=no&starttime='+starttime, function (s) {
		if(s) {
			new make_html_batch('portal.php?mod=topic&topicid=', s.split(','), make_html_index, dom);
		} else {
			dom.innerHTML = '$lang[makehtml_nofindtopic]<br/>$lang[makehtml_startmakeindex]<br /><a href="javascript:void(0);" onclick="\$(\'mk_topic\').style.display = \'none\';make_html_index();">$lang[makehtml_browser_error]</a>';
			setTimeout(function(){\$('mk_topic').style.display = 'none'; make_html_index();}, 1000);
		}
	});
}

function make_html_article(starttime) {
	var dom = $('mk_article');
	dom.innerHTML = '<div class="mk_msg">$lang[makehtml_waitcheckarticle]</div>';
	dom.style.display = 'block';
	var x = new Ajax();
	x.get('$adminscript?action=makehtml&operation=aids&inajax=1&frame=no&starttime='+starttime, function (s) {
		if(s){
			new make_html_batch('portal.php?mod=view&aid=', s.split(','), make_html_category, dom);
		} else {
			dom.innerHTML = '$lang[makehtml_nofindarticle]<br/>$lang[makehtml_startmakecategory]<br /><a href="javascript:void(0);" onclick="\$(\'mk_article\').style.display = \'none\';make_html_category();">$lang[makehtml_browser_error]</a>';
			setTimeout(function(){\$('mk_article').style.display = 'none'; make_html_category();}, 1000);
		}
	});
}

</script>
EOT;
	showtablefooter();
	showformfooter();
} elseif($operation == 'index') {

	showtips('makehtml_tips_index');

	showformheader('makehtml&operation=index');
	showtableheader('');
	echo '<script type="text/javascript" src="'.STATICURL.'js/makehtml.js?1"></script>', $css;
	echo '<tr><td colspan="15"><div class="fixsel"><a href="javascript:void(0);" class="btn_big" id="submit_portal_html">'.$lang['makehtml_createindex'].'</a></div></td></tr>', $result;
	$adminscript = ADMINSCRIPT;
	echo <<<EOT
<script type="text/JavaScript">
var form = document.forms['cpform'];
form.onsubmit = function(){return false;};
_attachEvent($('submit_portal_html'), 'click', function(){
	$('mk_result').style.display = 'block';
	$('mk_index').style.display = 'none';
	this.innerHTML = '$lang[makehtml_recreate]';
	this.disabled = true;
	make_html_index();
	return false;
});

function make_html_index() {
	var dom = $('mk_index');
	dom.innerHTML = '<div class="mk_msg">$lang[makehtml_waitmaking]</div>';
	dom.style.display = 'block';
	new make_html_batch('portal.php?', 0, null, dom, 1);
}
</script>
EOT;
	showtablefooter();
	showformfooter();
} elseif($operation == 'category') {

	loadcache('portalcategory');
	showtips('makehtml_tips_category');
	showformheader('makehtml&operation=category');
	showtableheader('');
	echo '<script type="text/javascript" src="'.STATICURL.'js/calendar.js"></script>',
		'<script type="text/javascript" src="'.STATICURL.'js/makehtml.js?1"></script>',
		$css;

	showsetting('start_time', 'starttime', '', 'calendar', '', '', '', '1');
	$selectdata = array('category', array(array(0, $lang['makehtml_createallcategory'])));
	mk_format_category(array_keys($_G['cache']['portalcategory']));
	showsetting('makehtml_selectcategory', $selectdata, 0, 'mselect');
	echo '<tr><td colspan="15"><div class="fixsel"><a href="javascript:void(0);" class="btn_big" id="submit_portal_html">'.$lang['makehtml_createcategory'].'</a></div></td></tr>', $result;
	$adminscript = ADMINSCRIPT;
	echo <<<EOT
<script type="text/JavaScript">
var form = document.forms['cpform'];
form.onsubmit = function(){return false;};
_attachEvent($('submit_portal_html'), 'click', function(){
	$('mk_result').style.display = 'block';
	$('mk_index').style.display = 'none';
	this.innerHTML = '$lang[makehtml_recreate]';
	var starttime = form['starttime'].value;
	if(starttime){
		make_html_category(starttime);
	} else {
		var category = form['category'];
		var allcatids = [];
		var selectedids = [];
		for(var i = 0; i < category.options.length; i++) {
			var option = category.options[i];
			if(option.value > 0) {
				allcatids.push(option.value);
			}
			if(option.selected) {
				selectedids.push(option.value);
			}
		}
		if(selectedids.length) {
			new make_html_batch('portal.php?mod=list&catid=', selectedids[0] == 0 ? allcatids : selectedids, make_html_category_ok, $('mk_category'));
		} else {
			var dom = $('mk_index');
			dom.style.display = 'block';
			dom.innerHTML = '$lang[makehtml_nofindcategory]';
		}
	}
	return false;
});

function make_html_category_ok() {
	var dom = $('mk_index');
	dom.style.display = 'block';
	dom.style.color = 'green';
	dom.innerHTML = '<div class="mk_msg">$lang[makehtml_selectcategorycomplete]</div>';
}
function make_html_category(starttime){
	var dom = $('mk_category');
	dom.innerHTML = '<div class="mk_msg">$lang[makehtml_waitmakingcategory]</div>';
	dom.style.display = 'block';
	starttime = starttime || form['starttime'].value;
	var x = new Ajax();
	x.get('$adminscript?action=makehtml&operation=catids&inajax=1&frame=no&starttime='+starttime, function (s) {
		if(s) {
			new make_html_batch('portal.php?mod=list&catid=', s.split(','), make_html_category_ok, dom);
		} else {
			dom.innerHTML = '$lang[makehtml_nofindcategory]';
			setTimeout(function(){\$('mk_category').style.display = 'none'; make_html_index();}, 1000);
		}
	});
}

</script>
EOT;
	showtablefooter();
	showformfooter();
} elseif($operation == 'article') {

	loadcache('portalcategory');
	showtips('makehtml_tips_article');
	showformheader('makehtml&operation=category');
	showtableheader('');
	echo '<script type="text/javascript" src="'.STATICURL.'js/calendar.js"></script>',
		'<script type="text/javascript" src="'.STATICURL.'js/makehtml.js?1"></script>',
		$css;

	showsetting('start_time', 'starttime', dgmdate(TIMESTAMP - 86400, 'Y-m-d'), 'calendar', '', '', '', '1');
	$selectdata = array('category', array(array(0, $lang['makehtml_createallcategory'])));
	mk_format_category(array_keys($_G['cache']['portalcategory']));
	showsetting('makehtml_selectcategory', $selectdata, 0, 'mselect');
	showsetting('makehtml_startid', 'startid', 0, 'text');
	showsetting('makehtml_endid', 'endid', 0, 'text');
	echo '<tr><td colspan="15"><div class="fixsel"><a href="javascript:void(0);" class="btn_big" id="submit_portal_html">'.$lang['makehtml_createarticle'].'</a></div></td></tr>', $result;
	$adminscript = ADMINSCRIPT;
	echo <<<EOT
<script type="text/JavaScript">
var form = document.forms['cpform'];
form.onsubmit = function(){return false;};
_attachEvent($('submit_portal_html'), 'click', function(){
	$('mk_result').style.display = 'block';
	$('mk_index').style.display = 'none';
	this.innerHTML = '$lang[makehtml_recreate]';
	var starttime = form['starttime'].value;
	var category = form['category'];
	var allcatids = [];
	var selectedids = [];
	for(var i = 0; i < category.options.length; i++) {
		var option = category.options[i];
		allcatids.push(option.value);
		if(option.selected) {
			selectedids.push(option.value);
		}
	}
	var startid = parseInt(form['startid'].value);
	var endid = parseInt(form['endid'].value);
	if(starttime || selectedids.length || startid || endid) {
		make_html_article(starttime, selectedids[0] == 0 ? -1 : selectedids, startid, endid);
	} else {
		var dom = $('mk_index');
		dom.style.display = 'block';
		dom.innerHTML = '$lang[makehtml_nofindarticle]';
	}
	return false;
});

function make_html_article_ok() {
	var dom = $('mk_index');
	dom.style.display = 'block';
	dom.style.color = 'green';
	dom.innerHTML = '<div class="mk_msg">$lang[makehtml_allarticlecomplete]</div>';
}

function make_html_article(starttime, catids, startid, endid) {
	catids = catids || -1;
	startid = startid || 0;
	endid = endid || 0;
	var dom = $('mk_article');
	dom.innerHTML = '<div class="mk_msg">$lang[makehtml_waitcheckarticle]</div>';
	dom.style.display = 'block';
	var x = new Ajax();
	x.get('$adminscript?action=makehtml&operation=aids&inajax=1&frame=no&starttime='+starttime+'&catids='+(catids == -1 ? '' : catids.join(','))+'&startid='+startid+'&endid='+endid, function (s) {
		if(s && s.indexOf('<') < 0){
			new make_html_batch('portal.php?mod=view&aid=', s.split(','), make_html_article_ok, dom);
		} else {
			dom.innerHTML = '$lang[makehtml_nofindarticle]';
		}
	});
}
</script>
EOT;
	showtablefooter();
	showformfooter();
} elseif ($operation == 'aids') {
	$starttime = strtotime($_GET['starttime']);
	$catids = $_GET['catids'];
	if($catids) {
		$catids = array_map('intval', explode(',', $catids));
	}
	$startid = intval($_GET['startid']);
	$endid = intval($_GET['endid']);
	$data = array();
	if($starttime || $catids || $startid || $endid) {
		$data = C::t('portal_article_title')->fetch_all_aid_by_dateline($starttime, $catids, $startid, $endid);
	}

	helper_output::xml($data ? implode(',', array_keys($data)) : '');

} elseif($operation == 'topic') {

	showtips('makehtml_tips_topic');
	showformheader('makehtml&operation=topic');
	showtableheader('');
	echo '<script type="text/javascript" src="'.STATICURL.'js/calendar.js"></script>',
		'<script type="text/javascript" src="'.STATICURL.'js/makehtml.js?1"></script>',
		$css;

	showsetting('start_time', 'starttime', '', 'calendar', '', '', '', '1');
	echo '<tr><td colspan="15"><div class="fixsel"><a href="javascript:void(0);" class="btn_big" id="submit_portal_html">'.$lang['makehtml_createtopic'].'</a></div></td></tr>', $result;
	$adminscript = ADMINSCRIPT;
	echo <<<EOT
<script type="text/JavaScript">
var form = document.forms['cpform'];
form.onsubmit = function(){return false;};
_attachEvent($('submit_portal_html'), 'click', function(){
	$('mk_result').style.display = 'block';
	$('mk_index').style.display = 'none';
	this.innerHTML = '$lang[makehtml_recreate]';
	var starttime = form['starttime'].value;
	if(starttime) {
		make_html_topic(starttime);
	} else {
		var dom = $('mk_index');
		dom.style.display = 'block';
		dom.innerHTML = '$lang[makehtml_nofindtopic]';
	}
	return false;
});

function make_html_topic_ok() {
	var dom = $('mk_index');
	dom.style.display = 'block';
	dom.style.color = 'green';
	dom.innerHTML = '<div class="mk_msg">$lang[makehtml_alltopiccomplete]</div>';
}

function make_html_topic(starttime) {
	var dom = $('mk_topic');
	dom.innerHTML = '<div class="mk_msg">$lang[makehtml_waitchecktopic]</div>';
	dom.style.display = 'block';
	var x = new Ajax();
	x.get('$adminscript?action=makehtml&operation=topicids&inajax=1&frame=no&starttime='+starttime, function (s) {
		if(s && s.indexOf('<') < 0){
			new make_html_batch('portal.php?mod=topic&topicid=', s.split(','), make_html_topic_ok, dom);
		} else {
			dom.innerHTML = '$lang[makehtml_nofindtopic]';
		}
	});
}
</script>
EOT;
	showtablefooter();
	showformfooter();
} elseif ($operation == 'topicids') {
	$starttime = strtotime($_GET['starttime']);
	$data = array();
	if($starttime) {
		$data = C::t('portal_topic')->fetch_all_topicid_by_dateline($starttime);
	}

	helper_output::xml($data ? implode(',', array_keys($data)) : '');

} elseif ($operation == 'catids') {
	$starttime = strtotime($_GET['starttime']);
	$data = array();
	if($starttime) {
		loadcache('portalcategory');
		foreach ($_G['cache']['portalcategory'] as $key => $value) {
			if($value['lastpublish'] >= $starttime) {
				$data[$key] = $key;
			}
		}
	}
	helper_output::xml($data ? implode(',', $data) : '');

} elseif ($operation == 'makehtmlsetting') {

	if(!submitcheck('makehtmlsetting')) {
		$setting = $_G['setting'];
		showformheader("makehtml&operation=makehtmlsetting");
		showtableheader('', 'nobottom', 'id="makehtml"'.($_GET['operation'] != 'makehtmlsetting' ? ' style="display: none"' : ''));
		showsetting('setting_functions_makehtml', 'settingnew[makehtml][flag]', $setting['makehtml']['flag'], 'radio', 0, 1);
		showsetting('setting_functions_makehtml_extendname', 'settingnew[makehtml][extendname]', $setting['makehtml']['extendname'] ? $setting['makehtml']['extendname'] : 'html', 'text');
		showsetting('setting_functions_makehtml_articlehtmldir', 'settingnew[makehtml][articlehtmldir]', $setting['makehtml']['articlehtmldir'], 'text');
		$dirformat = array('settingnew[makehtml][htmldirformat]',
				array(array(0, dgmdate(TIMESTAMP, '/Ym/')),
					array(1, dgmdate(TIMESTAMP, '/Ym/d/')),
					array(2, dgmdate(TIMESTAMP, '/Y/m/')),
					array(3, dgmdate(TIMESTAMP, '/Y/m/d/')))
			);
		showsetting('setting_functions_makehtml_htmldirformat', $dirformat, $setting['makehtml']['htmldirformat'], 'select');
		showsetting('setting_functions_makehtml_topichtmldir', 'settingnew[makehtml][topichtmldir]', $setting['makehtml']['topichtmldir'], 'text');
		showsetting('setting_functions_makehtml_indexname', 'settingnew[makehtml][indexname]', $setting['makehtml']['indexname'] ? $setting['makehtml']['indexname'] : 'index', 'text');
		showtagfooter('tbody');
		showtablefooter();
		showsubmit('makehtmlsetting', 'submit');
		showformfooter();
	} else {
		$settingnew = $_GET['settingnew'];
		if(isset($settingnew['makehtml'])) {
			$settingnew['makehtml']['flag'] = intval($settingnew['makehtml']['flag']);
			$settingnew['makehtml']['extendname'] = !$settingnew['makehtml']['extendname'] || !in_array($settingnew['makehtml']['extendname'], array('htm', 'html')) ? 'html' : $settingnew['makehtml']['extendname'];
			if(!$settingnew['makehtml']['indexname']) {
				$settingnew['makehtml']['indexname'] = 'index';
			} else {
				$re = NULL;
				preg_match_all('/[^\w\d\_]/',$settingnew['makehtml']['indexname'],$re);
				if(!empty($re[0]) || strpos('..', $settingnew['makehtml']['indexname']) !== false) {
					cpmsg(cplang('setting_functions_makehtml_indexname_invalid').','.cplang('return'), NULL, 'error');
				}
			}
			$settingnew['makehtml']['articlehtmldir'] = trim($settingnew['makehtml']['articlehtmldir'], ' /\\');
			$re = NULL;
			preg_match_all('/[^\w\d\_\\\\]/',$settingnew['makehtml']['articlehtmldir'],$re);
			if(!empty($re[0]) || !check_html_dir($settingnew['makehtml']['articlehtmldir'])) {
				cpmsg(cplang('setting_functions_makehtml_articlehtmldir_invalid').','.cplang('return'), NULL, 'error');
			}
			$settingnew['makehtml']['topichtmldir'] = trim($settingnew['makehtml']['topichtmldir'], ' /\\');
			$re = NULL;
			preg_match_all('/[^\w\d\_\\\\]/',$settingnew['makehtml']['topichtmldir'],$re);
			if(!empty($re[0]) || !check_html_dir($settingnew['makehtml']['topichtmldir'])) {
				cpmsg(cplang('setting_functions_makehtml_topichtmldir_invalid').','.cplang('return'), NULL, 'error');
			}
			$topichtmldir = realpath($settingnew['makehtml']['topichtmldir']);

			if($topichtmldir === false) {
				dmkdir($settingnew['makehtml']['topichtmldir'], 777, false);
				$topichtmldir = realpath($settingnew['makehtml']['topichtmldir']);
				rmdir($settingnew['makehtml']['topichtmldir']);
				if($topichtmldir === false) {
					cpmsg(cplang('setting_functions_makehtml_topichtmldir_invalid').','.cplang('return'), NULL, 'error');
				}
			}
			$topichtmldir = str_replace(DISCUZ_ROOT, '', $topichtmldir);
			$sysdir = array('api', 'archiver', 'config', 'data/diy', 'data\diy', 'install', 'source', 'static', 'template', 'uc_client', 'uc_server');
			foreach($sysdir as $_dir) {
				if(stripos($topichtmldir, $_dir) === 0) {
					cpmsg(cplang('setting_functions_makehtml_topichtmldir_invalid').','.cplang('return'), NULL, 'error');
				}
			}
			$settingnew['makehtml']['htmldirformat'] = intval($settingnew['makehtml']['htmldirformat']);
			C::t('common_setting')->update('makehtml', $settingnew['makehtml']);
			updatecache('setting');
		}
		cpmsg('setting_update_succeed', 'action=makehtml&operation=makehtmlsetting', 'succeed');
	}


} elseif ($operation == 'cleanhtml') {
	$setting = $_G['setting']['makehtml'];
	if(!empty($setting['flag'])) {
		cpmsg('admincp_makehtml_cleanhtml_error', 'action=makehtml&operation=makehtmlsetting', 'error');
	} else {
		if(!submitcheck('cleanhtml')) {

			showformheader("makehtml&operation=cleanhtml");
			showtableheader();
			showsetting('setting_functions_makehtml_cleanhtml', array('cleandata', array(cplang('setting_functions_makehtml_cleanhtml_index'), cplang('setting_functions_makehtml_cleanhtml_category'), cplang('setting_functions_makehtml_cleanhtml_other'))), 0, 'binmcheckbox');
			showtagfooter('tbody');
			showtablefooter();
			showsubmit('cleanhtml', 'submit');
			showformfooter();
		} else {
			if(isset($_GET['cleandata'])) {
				$cleandata = $_GET['cleandata'];
				if(isset($cleandata[1])) {
					unlink(DISCUZ_ROOT.'./'.$setting['indexname'].'.'.$setting['extendname']);
				}
				if(isset($cleandata[2])) {
					loadcache('portalcategory');
					foreach($_G['cache']['portalcategory'] as $cat) {
						if($cat['fullfoldername']) {
							unlink($cat['fullfoldername'].'/index.'.$setting['extendname']);
						}
					}
				}
				if(isset($cleandata[3])) {
					if(!empty($setting['articlehtmldir']) && $setting['articlehtmldir'] === $setting['topichtmldir']) {
						drmdir(DISCUZ_ROOT.'./'.$setting['articlehtmldir'], $setting['extendname']);
					} elseif(!empty($setting['topichtmldir'])) {
						drmdir(DISCUZ_ROOT.'./'.$setting['topichtmldir'], $setting['extendname']);
					} elseif(!empty($setting['articlehtmldir'])) {
						drmdir(DISCUZ_ROOT.'./'.$setting['articlehtmldir'], $setting['extendname']);
					}
					if(empty($setting['articlehtmldir'])) {
						loadcache('portalcategory');
						foreach($_G['cache']['portalcategory'] as $cat) {
							if($cat['fullfoldername']) {
								if(($dirobj = dir(DISCUZ_ROOT.'./'.$cat['fullfoldername']))) {
									while(false !== ($file = $dirobj->read())) {
										if ($file != "." && $file != "..") {
											$path = $dirobj->path.'/'.$file;
											if(is_dir($path) && false === check_son_folder($file, $cat)) {
												drmdir($path, $setting['extendname']);
											}
										}
									}
									$dirobj->close();
								}
							}
						}
					}
				}
				cpmsg('admincp_makehtml_cleanhtml_succeed', 'action=makehtml&operation=cleanhtml', 'succeed');
			} else {
				cpmsg('admincp_makehtml_cleanhtml_choose_item', 'action=makehtml&operation=cleanhtml', 'error');
			}
		}
	}
}

function mk_format_category($catids) {
	global $_G, $selectdata;
	foreach($catids as $catid) {
		if(!isset($selectdata[1][$catid])) {
			$cate = $_G['cache']['portalcategory'][$catid];
			if($cate['level'] == 0) {
				$selectdata[1][$catid] = array($catid, $cate['catname']);
				mk_format_category($cate['children']);
			} elseif ($cate['level'] == 1) {
				$selectdata[1][$catid] = array($catid, '&nbsp;&nbsp;&nbsp;'.$cate['catname']);
				mk_format_category($cate['children']);
			} elseif ($cate['level'] == 2) {
				$selectdata[1][$catid] = array($catid, '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$cate['catname']);
			}
		}
	}
}

function drmdir($dir, $fileext = 'html') {
	if($dir === '.' || $dir === '..' || strpos($dir, '..') !== false) {
		return false;
	}
	if(substr($dir,-1) === "/") {
		$dir = substr($dir,0,-1);
	}
	if(!file_exists($dir) || !is_dir($dir)) {
		return false;
	} elseif(!is_readable($dir)) {
		return false;
	} else {
		if(($dirobj = dir($dir))) {
			while(false !== ($file = $dirobj->read())) {
				if ($file != "." && $file != "..") {
					$path = $dirobj->path . "/" . $file;
					if(is_dir($path)) {
						drmdir($path);
					} elseif(fileext($path) === $fileext) {
						echo $path,"<br>";
						unlink($path);
					}
				}
			}
			$dirobj->close();
		}
		rmdir($dir);
		return true;
	}
	return false;
}

function check_son_folder($file, $cat) {
	global $_G;
	$category = $_G['cache']['portalcategory'];
	if(!empty($cat['children'])) {
		foreach ($cat['children'] as $catid) {
			if($category[$catid]['upid'] == $cat['catid'] && $category[$catid]['foldername'] == $file) {
				return true;
			}
		}
	}
	return false;
}

function check_html_dir($dir) {
	$dir = str_replace("\\", '/', $dir);
	list($first) = explode('/', $dir);
	if(in_array(strtolower($first), array('uc_server', 'uc_client', 'template', 'static', 'source', 'm', 'install', 'data', 'config', 'api', 'archiver'), true)) {
		return false;
	}
	return true;
}
?>