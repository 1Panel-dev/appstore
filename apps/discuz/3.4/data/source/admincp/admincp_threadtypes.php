<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_threadtypes.php 36345 2017-01-12 01:55:04Z nemohou $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

cpheader();

$classoptionmenu = array();
$curclassname = '';
foreach(C::t('forum_typeoption')->fetch_all_by_classid(0) as $option) {
	if($_GET['classid'] == $option['optionid']) {
		$curclassname = $option['title'];
	}
	$classoptionmenu[] = array($option['title'], "threadtypes&operation=typeoption&classid=$option[optionid]", $_GET['classid'] == $option['optionid']);
}

$mysql_keywords = array( 'ADD', 'ALL', 'ALTER', 'ANALYZE', 'AND', 'AS', 'ASC', 'ASENSITIVE', 'BEFORE', 'BETWEEN', 'BIGINT', 'BINARY', 'BLOB', 'BOTH', 'BY', 'CALL', 'CASCADE', 'CASE', 'CHANGE', 'CHAR', 'CHARACTER', 'CHECK', 'COLLATE', 'COLUMN', 'CONDITION', 'CONNECTION', 'CONSTRAINT', 'CONTINUE', 'CONVERT', 'CREATE', 'CROSS', 'CURRENT_DATE', 'CURRENT_TIME', 'CURRENT_TIMESTAMP', 'CURRENT_USER', 'CURSOR', 'DATABASE', 'DATABASES', 'DAY_HOUR', 'DAY_MICROSECOND', 'DAY_MINUTE', 'DAY_SECOND', 'DEC', 'DECIMAL', 'DECLARE', 'DEFAULT', 'DELAYED', 'DELETE', 'DESC', 'DESCRIBE', 'DETERMINISTIC', 'DISTINCT', 'DISTINCTROW', 'DIV', 'DOUBLE', 'DROP', 'DUAL', 'EACH', 'ELSE', 'ELSEIF', 'ENCLOSED', 'ESCAPED', 'EXISTS', 'EXIT', 'EXPLAIN', 'FALSE', 'FETCH', 'FLOAT', 'FLOAT4', 'FLOAT8', 'FOR', 'FORCE', 'FOREIGN', 'FROM', 'FULLTEXT', 'GOTO', 'GRANT', 'GROUP', 'HAVING', 'HIGH_PRIORITY', 'HOUR_MICROSECOND', 'HOUR_MINUTE', 'HOUR_SECOND', 'IF', 'IGNORE', 'IN', 'INDEX', 'INFILE', 'INNER', 'INOUT', 'INSENSITIVE', 'INSERT', 'INT', 'INT1', 'INT2', 'INT3', 'INT4', 'INT8', 'INTEGER', 'INTERVAL', 'INTO', 'IS', 'ITERATE', 'JOIN', 'KEY', 'KEYS', 'KILL', 'LABEL', 'LEADING', 'LEAVE', 'LEFT', 'LIKE', 'LIMIT', 'LINEAR', 'LINES', 'LOAD', 'LOCALTIME', 'LOCALTIMESTAMP', 'LOCK', 'LONG', 'LONGBLOB', 'LONGTEXT', 'LOOP', 'LOW_PRIORITY', 'MATCH', 'MEDIUMBLOB', 'MEDIUMINT', 'MEDIUMTEXT', 'MIDDLEINT', 'MINUTE_MICROSECOND', 'MINUTE_SECOND', 'MOD', 'MODIFIES', 'NATURAL', 'NOT', 'NO_WRITE_TO_BINLOG', 'NULL', 'NUMERIC', 'ON', 'OPTIMIZE', 'OPTION', 'OPTIONALLY', 'OR', 'ORDER', 'OUT', 'OUTER', 'OUTFILE', 'PRECISION', 'PRIMARY', 'PROCEDURE', 'PURGE', 'RAID0', 'RANGE', 'READ', 'READS', 'REAL', 'REFERENCES', 'REGEXP', 'RELEASE', 'RENAME', 'REPEAT', 'REPLACE', 'REQUIRE', 'RESTRICT', 'RETURN', 'REVOKE', 'RIGHT', 'RLIKE', 'SCHEMA', 'SCHEMAS', 'SECOND_MICROSECOND', 'SELECT', 'SENSITIVE', 'SEPARATOR', 'SET', 'SHOW', 'SMALLINT', 'SPATIAL', 'SPECIFIC', 'SQL', 'SQLEXCEPTION', 'SQLSTATE', 'SQLWARNING', 'SQL_BIG_RESULT', 'SQL_CALC_FOUND_ROWS', 'SQL_SMALL_RESULT', 'SSL', 'STARTING', 'STRAIGHT_JOIN', 'TABLE', 'TERMINATED', 'THEN', 'TINYBLOB', 'TINYINT', 'TINYTEXT', 'TO', 'TRAILING', 'TRIGGER', 'TRUE', 'UNDO', 'UNION', 'UNIQUE', 'UNLOCK', 'UNSIGNED', 'UPDATE', 'USAGE', 'USE', 'USING', 'UTC_DATE', 'UTC_TIME', 'UTC_TIMESTAMP', 'VALUES', 'VARBINARY', 'VARCHAR', 'VARCHARACTER', 'VARYING', 'WHEN', 'WHERE', 'WHILE', 'WITH', 'WRITE', 'X509', 'XOR', 'YEAR_MONTH', 'ZEROFILL', 'ACTION', 'BIT', 'DATE', 'ENUM', 'NO', 'TEXT', 'TIME');
if(!$operation) {

	$navlang = 'threadtype_infotypes';
	$operation = 'type';
	$changetype = 'threadsorts';

	if(!submitcheck('typesubmit')) {

		$forumsarray = $fidsarray = array();
		$query = C::t('forum_forum')->fetch_all_for_threadsorts();
		foreach($query as $forum) {
			$forum[$changetype] = dunserialize($forum[$changetype]);
			if(is_array($forum[$changetype]['types'])) {
				foreach($forum[$changetype]['types'] as $typeid => $name) {
					$forumsarray[$typeid][] = '<a href="'.ADMINSCRIPT.'?action=forums&operation=edit&fid='.$forum['fid'].'&anchor=threadtypes">'.$forum['name'].'</a>';
					$fidsarray[$typeid][] = $forum['fid'];
				}
			}
		}

		$threadtypes = '';
		$query = C::t('forum_threadtype')->fetch_all_for_order();
		foreach($query as $type) {
			$tmpstr = "<a href=\"".ADMINSCRIPT."?action=threadtypes&operation=export&sortid=$type[typeid]\" class=\"act nowrap\">$lang[export]</a>";
			$threadtypes .= showtablerow('', array('class="td25"', 'class="td28"', 'class="td29"', 'class="td29"', 'title="'.cplang('forums_threadtypes_forums_comment').'"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$type[typeid]\">",
				"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayordernew[$type[typeid]]\" value=\"$type[displayorder]\">",
				"<input type=\"text\" class=\"txt\" size=\"15\" name=\"namenew[$type[typeid]]\" value=\"".dhtmlspecialchars($type['name'])."\">",
				"<input type=\"text\" class=\"txt\" size=\"30\" name=\"descriptionnew[$type[typeid]]\" value=\"$type[description]\">",
				is_array($forumsarray[$type['typeid']]) ? '<ul class="lineheight"><li class="left">'.implode(',&nbsp;</li><li class="left"> ', $forumsarray[$type['typeid']])."</li></ul><input type=\"hidden\" name=\"fids[$type[typeid]]\" value=\"".implode(', ', $fidsarray[$type['typeid']])."\">" : '',
				"<a href=\"".ADMINSCRIPT."?action=threadtypes&operation=sortdetail&sortid=$type[typeid]\" class=\"act nowrap\">$lang[detail]</a>&nbsp;&nbsp;
				<a href=\"".ADMINSCRIPT."?action=threadtypes&operation=sorttemplate&sortid=$type[typeid]\" class=\"act nowrap\">$lang[threadtype_template]</a>",
				$tmpstr,
			), TRUE);
		}

?>
<script type="text/JavaScript">
var rowtypedata = [
	[
		[1, '', 'td25'],
		[1, '<input type="text" class="txt" name="newdisplayorder[]" size="2" value="">', 'td28'],
		[1, '<input type="text" class="txt" name="newname[]" size="15">', 'td29'],
		[1, '<input type="text" class="txt" name="newdescription[]" size="30" value="">', 'td29'],
		[2, '']
	],
];
</script>
<?php
		shownav('forum', 'threadtype_infotypes');
		showsubmenu('threadtype_infotypes', array(
			array('threadtype_infotypes_type', 'threadtypes', 1),
			array('threadtype_infotypes_content', 'threadtypes&operation=content', 0),
			array(array('menu' => ($curclassname ? $curclassname : 'threadtype_infotypes_option'), 'submenu' => $classoptionmenu), '', 0)
		));

		showformheader("threadtypes&", 'enctype', 'threadtypeform');
		showtableheader('');
		showsubtitle(array('', 'display_order', cplang('name').' '.cplang('tiny_bbcode_support'), 'description', 'forums_relation', '', ''), 'header', array('', 'width="60"', 'width="110"', 'width="210"', '', 'width="90"', 'width="60"'));
		echo $threadtypes;
		echo '<tr><td class="td25"></td><td colspan="5"><div>'.'<span class="filebtn"><input type="hidden" name="importtype" value="file" /><input type="file" name="importfile" class="pf" size="1" onchange="uploadthreadtypexml($(\'threadtypeform\'), \''.ADMINSCRIPT.'?action=threadtypes&operation=import\');" /><a class="addtr" href="JavaScript:;">'.$lang['import'].'</a></span>'.'<a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['threadtype_infotypes_add'].'</a></div></td>';

		showsubmit('typesubmit', 'submit', 'del');
		showtablefooter();
		showformfooter();

	} else {

		$updatefids = $modifiedtypes = array();

		if(is_array($_GET['delete'])) {

			if($_GET['delete']) {
				C::t('forum_typeoptionvar')->delete_by_sortid($_GET['delete']);
				C::t('forum_typevar')->delete($_GET['delete']);
				$affected_rows = C::t('forum_threadtype')->delete($_GET['delete']);
			}

			foreach($_GET['delete'] as $_GET['sortid']) {
				C::t('forum_optionvalue')->drop($_GET['sortid']);
			}

			if($_GET['delete'] && $affected_rows) {
				C::t('forum_thread')->update_sortid_by_sortid(0, $_GET['delete']);
				foreach($_GET['delete'] as $id) {
					if(is_array($_GET['namenew']) && isset($_GET['namenew'][$id])) {
						unset($_GET['namenew'][$id]);
					}
					if(!empty($_GET['fids'][$id])) {
						foreach(explode(',', $_GET['fids'][$id]) as $fid) {
							if($fid = intval($fid)) {
								$updatefids[$fid]['deletedids'][] = intval($id);
							}
						}
					}
				}
			}
		}

		if(is_array($_GET['namenew']) && $_GET['namenew']) {
			foreach($_GET['namenew'] as $typeid => $val) {
				$_GET['descriptionnew'] = is_array($_GET['descriptionnew']) ? $_GET['descriptionnew'] : array();
				$data = array(
					'name' => trim($_GET['namenew'][$typeid]),
					'description' => dhtmlspecialchars(trim($_GET['descriptionnew'][$typeid])),
					'displayorder' => intval($_GET['displayordernew'][$typeid]),
					'special' => 1,
				);
				$affected_rows = C::t('forum_threadtype')->update($typeid, $data);
				if($affected_rows) {
					$modifiedtypes[] = $typeid;
				}
			}

			if($modifiedtypes = array_unique($modifiedtypes)) {
				foreach($modifiedtypes as $id) {
					if(!empty($_GET['fids'][$id])) {
						foreach(explode(',', $_GET['fids'][$id]) as $fid) {
							if($fid = intval($fid)) {
								$updatefids[$fid]['modifiedids'][] = $id;
							}
						}
					}
				}
			}
		}

		if($updatefids) {
			$query = C::t('forum_forum')->fetch_all_info_by_fids(array_keys($updatefids));
			foreach($query as $forum) {
				if($forum[$changetype] == '') continue;
				$fid = $forum['fid'];
				$forum[$changetype] = dunserialize($forum[$changetype]);
				if($updatefids[$fid]['deletedids']) {
					foreach($updatefids[$fid]['deletedids'] as $id) {
						unset($forum[$changetype]['types'][$id], $forum[$changetype]['flat'][$id], $forum[$changetype]['selectbox'][$id]);
					}
				}
				if($updatefids[$fid]['modifiedids']) {
					foreach($updatefids[$fid]['modifiedids'] as $id) {
						if(isset($forum[$changetype]['types'][$id])) {
							$_GET['namenew'][$id] = trim(strip_tags($_GET['namenew'][$id]));
							$forum[$changetype]['types'][$id] = $_GET['namenew'][$id];
							if(isset($forum[$changetype]['selectbox'][$id])) {
								$forum[$changetype]['selectbox'][$id] = $_GET['namenew'][$id];
							} else {
								$forum[$changetype]['flat'][$id] = $_GET['namenew'][$id];
							}
						}
					}
				}
				C::t('forum_forumfield')->update($fid, array($changetype => serialize($forum[$changetype])));
			}
		}

		if(is_array($_GET['newname'])) {
			foreach($_GET['newname'] as $key => $value) {
				if($newname1 = trim(strip_tags($value))) {
					if(C::t('forum_threadtype')->checkname($newname1)) {
						cpmsg('forums_threadtypes_duplicate', '', 'error');
					}
					$data = array(
						'name' => $newname1,
						'description' => dhtmlspecialchars(trim($_GET['newdescription'][$key])),
						'displayorder' => $_GET['newdisplayorder'][$key],
						'special' => 1,
					);
					C::t('forum_threadtype')->insert($data);
				}
			}
		}

		cpmsg('forums_threadtypes_succeed', 'action=threadtypes', 'succeed');

	}

} elseif($operation == 'typeoption') {

	if(!submitcheck('typeoptionsubmit')) {

		if($_GET['classid']) {
			$typetitle = C::t('forum_typeoption')->fetch($_GET['classid']);
			if(!$typetitle['title']) {
				cpmsg('threadtype_infotypes_noexist', 'action=threadtypes', 'error');
			}

			$typeoptions = '';
			foreach(C::t('forum_typeoption')->fetch_all_by_classid($_GET['classid']) as $option) {
				$option['type'] = $lang['threadtype_edit_vars_type_'. $option['type']];
				$typeoptions .= showtablerow('', array('class="td25"', 'class="td28"'), array(
					"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$option[optionid]\">",
					"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayorder[$option[optionid]]\" value=\"$option[displayorder]\">",
					"<input type=\"text\" class=\"txt\" size=\"15\" name=\"title[$option[optionid]]\" value=\"".dhtmlspecialchars($option['title'])."\">",
					"$option[identifier]<input type=\"hidden\" name=\"identifier[$option[optionid]]\" value=\"$option[identifier]\">",
					$option['type'],
					"<a href=\"".ADMINSCRIPT."?action=threadtypes&operation=optiondetail&optionid=$option[optionid]\" class=\"act\">$lang[detail]</a>"
				), TRUE);
			}
		}

		echo <<<EOT
<script type="text/JavaScript">
	var rowtypedata = [
		[
			[1, '', 'td25'],
			[1, '<input type="text" class="txt" size="2" name="newdisplayorder[]" value="0">', 'td28'],
			[1, '<input type="text" class="txt" size="15" name="newtitle[]">'],
			[1, '<input type="text" class="txt" size="15" name="newidentifier[]">'],
			[1, '<select name="newtype[]"><option value="number">$lang[threadtype_edit_vars_type_number]</option><option value="text" selected>$lang[threadtype_edit_vars_type_text]</option><option value="textarea">$lang[threadtype_edit_vars_type_textarea]</option><option value="radio">$lang[threadtype_edit_vars_type_radio]</option><option value="checkbox">$lang[threadtype_edit_vars_type_checkbox]</option><option value="select">$lang[threadtype_edit_vars_type_select]</option><option value="calendar">$lang[threadtype_edit_vars_type_calendar]</option><option value="email">$lang[threadtype_edit_vars_type_email]</option><option value="image">$lang[threadtype_edit_vars_type_image]</option><option value="url">$lang[threadtype_edit_vars_type_url]</option><option value="range">$lang[threadtype_edit_vars_type_range]</option></select>'],
			[1, '']
		],
	];
</script>
EOT;

		shownav('forum', 'threadtype_infotypes');
		showsubmenu('threadtype_infotypes', array(
			array('threadtype_infotypes_type', 'threadtypes', 0),
			array('threadtype_infotypes_content', 'threadtypes&operation=content', 0),
			array(array('menu' => ($curclassname ? $curclassname : 'threadtype_infotypes_option'), 'submenu' => $classoptionmenu), 1)
		));
		showformheader("threadtypes&operation=typeoption&typeid={$_GET['typeid']}");
		showhiddenfields(array('classid' => $_GET['classid']));
		showtableheader();

		showsubtitle(array('', 'display_order', 'name', 'threadtype_variable', 'threadtype_type', ''));
		echo $typeoptions;
		echo '<tr><td></td><td colspan="5"><div><a href="###" onclick="addrow(this, 0)" class="addtr">'.$lang['threadtype_infotypes_add_option'].'</a></div></td></tr>';
		showsubmit('typeoptionsubmit', 'submit', 'del');

		showtablefooter();
		showformfooter();

	} else {

		if($ids = dimplode($_GET['delete'])) {
			C::t('forum_typeoption')->delete($_GET['delete']);
			C::t('forum_typevar')->delete(null, $_GET['delete']);
		}

		if(is_array($_GET['title'])) {
			foreach($_GET['title'] as $id => $val) {
				if(in_array(strtoupper($_GET['identifier'][$id]), $mysql_keywords)) {
					continue;
				}
				C::t('forum_typeoption')->update($id, array(
					'displayorder' => $_GET['displayorder'][$id],
					'title' => $_GET['title'][$id],
					'identifier' => $_GET['identifier'][$id],
				));
			}
		}

		if(is_array($_GET['newtitle'])) {
			foreach($_GET['newtitle'] as $key => $value) {
				$newtitle1 = dhtmlspecialchars(trim($value));
				$newidentifier1 = trim($_GET['newidentifier'][$key]);
				if($newtitle1 && $newidentifier1) {
					if(in_array(strtoupper($newidentifier1), $mysql_keywords)) {
						cpmsg('threadtype_infotypes_optionvariable_iskeyword', '', 'error');
					}
					if(C::t('forum_typeoption')->fetch_all_by_identifier($newidentifier1, 0, 1) || strlen($newidentifier1) > 40  || !ispluginkey($newidentifier1)) {
						cpmsg('threadtype_infotypes_optionvariable_invalid', '', 'error');
					}
					$data = array(
						'classid' => $_GET['classid'],
						'displayorder' => $_GET['newdisplayorder'][$key],
						'title' => $newtitle1,
						'identifier' => $newidentifier1,
						'type' => $_GET['newtype'][$key],
					);
					C::t('forum_typeoption')->insert($data);
				} elseif($newtitle1 && !$newidentifier1) {
					cpmsg('threadtype_infotypes_option_invalid', 'action=threadtypes&operation=typeoption&classid='.$_GET['classid'], 'error');
				}
			}
		}
		updatecache('threadsorts');
		cpmsg('threadtype_infotypes_succeed', 'action=threadtypes&operation=typeoption&classid='.$_GET['classid'], 'succeed');

	}

} elseif($operation == 'optiondetail') {

	$option = C::t('forum_typeoption')->fetch($_GET['optionid']);
	if(!$option) {
		cpmsg('typeoption_not_found', '', 'error');
	}

	if(!submitcheck('editsubmit')) {


		shownav('forum', 'threadtype_infotypes');
		showsubmenu('threadtype_infotypes', array(
			array('threadtype_infotypes_type', 'threadtypes', 0),
			array('threadtype_infotypes_content', 'threadtypes&operation=content', 0),
			array(array('menu' => ($curclassname ? $curclassname : 'threadtype_infotypes_option'), 'submenu' => $classoptionmenu), '', 1)
		));

		$typeselect = '<select name="typenew" onchange="var styles, key;styles=new Array(\'number\',\'text\',\'radio\', \'checkbox\', \'textarea\', \'select\', \'image\', \'calendar\', \'range\', \'info\'); for(key in styles) {var obj=$(\'style_\'+styles[key]); if(obj) { obj.style.display=styles[key]==this.options[this.selectedIndex].value?\'\':\'none\';}}">';
		foreach(array('number', 'text', 'radio', 'checkbox', 'textarea', 'select', 'calendar', 'email', 'url', 'image', 'range') as $type) {
			$typeselect .= '<option value="'.$type.'" '.($option['type'] == $type ? 'selected' : '').'>'.$lang['threadtype_edit_vars_type_'.$type].'</option>';
		}
		$typeselect .= '</select>';

		$option['rules'] = dunserialize($option['rules']);
		$option['protect'] = dunserialize($option['protect']);

		$groups = $forums = array();
		foreach(C::t('common_usergroup')->range() as $group) {
			$groups[] = array($group['groupid'], $group['grouptitle']);
		}
		$verifys = array();
		if($_G['setting']['verify']['enabled']) {
			foreach($_G['setting']['verify'] as $key => $verify) {
				if($verify['available'] == 1) {
					$verifys[] = array($key, $verify['title']);
				}
			}
		}

		foreach(C::t('common_member_profile_setting')->fetch_all_by_available_formtype(1, 'text') as $result) {
			$threadtype_profile = !$threadtype_profile ? "<select id='rules[text][profile]' name='rules[text][profile]'><option value=''></option>" : $threadtype_profile."<option value='{$result[fieldid]}' ".($option['rules']['profile'] == $result['fieldid'] ? "selected='selected'" : '').">{$result[title]}</option>";
		}
		$threadtype_profile .= "</select>";

		showformheader("threadtypes&operation=optiondetail&optionid=$_GET[optionid]");
		showtableheader();
		showtitle('threadtype_infotypes_option_config');
		showsetting('name', 'titlenew', $option['title'], 'text');
		showsetting('threadtype_variable', 'identifiernew', $option['identifier'], 'text');
		showsetting('type', '', '', $typeselect);
		showsetting('threadtype_edit_desc', 'descriptionnew', $option['description'], 'textarea');
		showsetting('threadtype_unit', 'unitnew', $option['unit'], 'text');
		showsetting('threadtype_expiration', 'expirationnew', $option['expiration'], 'radio');
		if(in_array($option['type'], array('calendar', 'number', 'text', 'email', 'textarea'))) {
			showsetting('threadtype_protect', 'protectnew[status]', $option['protect']['status'], 'radio', 0, 1);
			showsetting('threadtype_protect_mode', array('protectnew[mode]', array(
				array(1, $lang['threadtype_protect_mode_pic']),
				array(2, $lang['threadtype_protect_mode_html'])
			)), $option['protect']['mode'], 'mradio');
			showsetting('threadtype_protect_usergroup', array('protectnew[usergroup][]', $groups), explode("\t", $option['protect']['usergroup']), 'mselect');
			$verifys && showsetting('threadtype_protect_verify', array('protectnew[verify][]', $verifys), explode("\t", $option['protect']['verify']), 'mselect');
			showsetting('threadtype_protect_permprompt', 'permpromptnew', $option['permprompt'], 'textarea');
		}

		showtagheader('tbody', "style_calendar", $option['type'] == 'calendar');
		showtitle('threadtype_edit_vars_type_calendar');
		showsetting('threadtype_edit_inputsize', 'rules[calendar][inputsize]', $option['rules']['inputsize'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', "style_number", $option['type'] == 'number');
		showtitle('threadtype_edit_vars_type_number');
		showsetting('threadtype_edit_maxnum', 'rules[number][maxnum]', $option['rules']['maxnum'], 'text');
		showsetting('threadtype_edit_minnum', 'rules[number][minnum]', $option['rules']['minnum'], 'text');
		showsetting('threadtype_edit_inputsize', 'rules[number][inputsize]', $option['rules']['inputsize'], 'text');
		showsetting('threadtype_defaultvalue', 'rules[number][defaultvalue]', $option['rules']['defaultvalue'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', "style_text", $option['type'] == 'text');
		showtitle('threadtype_edit_vars_type_text');
		showsetting('threadtype_edit_textmax', 'rules[text][maxlength]', $option['rules']['maxlength'], 'text');
		showsetting('threadtype_edit_inputsize', 'rules[text][inputsize]', $option['rules']['inputsize'], 'text');
		showsetting('threadtype_edit_profile', '', '', $threadtype_profile);
		showsetting('threadtype_defaultvalue', 'rules[text][defaultvalue]', $option['rules']['defaultvalue'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', "style_textarea", $option['type'] == 'textarea');
		showtitle('threadtype_edit_vars_type_textarea');
		showsetting('threadtype_edit_textmax', 'rules[textarea][maxlength]', $option['rules']['maxlength'], 'text');
		showsetting('threadtype_edit_colsize', 'rules[textarea][colsize]', $option['rules']['colsize'], 'text');
		showsetting('threadtype_edit_rowsize', 'rules[textarea][rowsize]', $option['rules']['rowsize'], 'text');
		showsetting('threadtype_defaultvalue', 'rules[textarea][defaultvalue]', $option['rules']['defaultvalue'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', "style_select", $option['type'] == 'select');
		showtitle('threadtype_edit_vars_type_select');
		showsetting('threadtype_edit_select_choices', 'rules[select][choices]', $option['rules']['choices'], 'textarea');
		showsetting('threadtype_edit_inputsize', 'rules[select][inputsize]', $option['rules']['inputsize'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', "style_radio", $option['type'] == 'radio');
		showtitle('threadtype_edit_vars_type_radio');
		showsetting('threadtype_edit_choices', 'rules[radio][choices]', $option['rules']['choices'], 'textarea');
		showtagfooter('tbody');

		showtagheader('tbody', "style_checkbox", $option['type'] == 'checkbox');
		showtitle('threadtype_edit_vars_type_checkbox');
		showsetting('threadtype_edit_choices', 'rules[checkbox][choices]', $option['rules']['choices'], 'textarea');
		showtagfooter('tbody');

		showtagheader('tbody', "style_image", $option['type'] == 'image');
		showtitle('threadtype_edit_vars_type_image');
		showsetting('threadtype_edit_images_weight', 'rules[image][maxwidth]', $option['rules']['maxwidth'], 'text');
		showsetting('threadtype_edit_images_height', 'rules[image][maxheight]', $option['rules']['maxheight'], 'text');
		showsetting('threadtype_edit_inputsize', 'rules[image][inputsize]', $option['rules']['inputsize'], 'text');
		showtagfooter('tbody');

		showtagheader('tbody', "style_range", $option['type'] == 'range');
		showtitle('threadtype_edit_vars_type_range');
		showsetting('threadtype_edit_maxnum', 'rules[range][maxnum]', $option['rules']['maxnum'], 'text');
		showsetting('threadtype_edit_minnum', 'rules[range][minnum]', $option['rules']['minnum'], 'text');
		showsetting('threadtype_edit_inputsize', 'rules[range][inputsize]', $option['rules']['inputsize'], 'text');
		showsetting('threadtype_edit_searchtxt', 'rules[range][searchtxt]', $option['rules']['searchtxt'], 'text');
		showtagfooter('tbody');

		showsubmit('editsubmit');
		showtablefooter();
		showformfooter();

	} else {

		$titlenew = trim($_GET['titlenew']);
		$_GET['identifiernew'] = trim($_GET['identifiernew']);
		if(!$titlenew || !$_GET['identifiernew']) {
			cpmsg('threadtype_infotypes_option_invalid', '', 'error');
		}

		if(in_array(strtoupper($_GET['identifiernew']), $mysql_keywords)) {
			cpmsg('threadtype_infotypes_optionvariable_iskeyword', '', 'error');
		}

		if(C::t('forum_typeoption')->fetch_all_by_identifier($_GET['identifiernew'], 0, 1, $_GET['optionid']) || strlen($_GET['identifiernew']) > 40  || !ispluginkey($_GET['identifiernew'])) {
			cpmsg('threadtype_infotypes_optionvariable_invalid', '', 'error');
		}

		$_GET['protectnew']['usergroup'] = $_GET['protectnew']['usergroup'] ? implode("\t", $_GET['protectnew']['usergroup']) : '';
		$_GET['protectnew']['verify'] = $_GET['protectnew']['verify'] ? implode("\t", $_GET['protectnew']['verify']) : '';

		C::t('forum_typeoption')->update($_GET['optionid'], array(
			'title' => $titlenew,
			'description' => $_GET['descriptionnew'],
			'identifier' => $_GET['identifiernew'],
			'type' => $_GET['typenew'],
			'unit' => $_GET['unitnew'],
			'expiration' => $_GET['expirationnew'],
			'protect' => serialize($_GET['protectnew']),
			'rules' => serialize($_GET['rules'][$_GET['typenew']]),
			'permprompt' => $_GET['permpromptnew'],
		));

		if($_GET['identifiernew'] != $option['identifier'] && $_GET['typenew'] == $option['type']){
			if(in_array($_GET['typenew'], array('radio'))) {
				$type_tableoption_sql = "smallint(6) UNSIGNED NOT NULL DEFAULT '0'";
			} elseif(in_array($_GET['typenew'], array('number', 'range'))) {
				$type_tableoption_sql = "int(10) UNSIGNED NOT NULL DEFAULT '0'";
			} elseif($_GET['typenew'] == 'select') {
				$type_tableoption_sql = "varchar(50) NOT NULL";
			} else {
				$type_tableoption_sql = "mediumtext NOT NULL";
			}
			$typevar_list = DB::fetch_all('SELECT sortid FROM %t WHERE optionid=%d', array('forum_typevar', $_GET['optionid']));
			foreach($typevar_list as $typevar) {
				C::t('forum_optionvalue')->alter($typevar['sortid'], "change ".$option['identifier']." ".$_GET['identifiernew']." ".$type_tableoption_sql);
			}
		}

		updatecache('threadsorts');
		cpmsg('threadtype_infotypes_option_succeed', 'action=threadtypes&operation=typeoption&classid='.$option['classid'], 'succeed');
	}

} elseif($operation == 'sortdetail') {

	if(!submitcheck('sortdetailsubmit')) {
		$threadtype = C::t('forum_threadtype')->fetch($_GET['sortid']);
		$threadtype['modelid'] = isset($_GET['modelid']) ? intval($_GET['modelid']) : $threadtype['modelid'];

		$sortoptions = $jsoptionids = '';
		$showoption = array();
		$typevararr = C::t('forum_typevar')->fetch_all_by_sortid($_GET['sortid'], 'ASC');
		$typeoptionarr = C::t('forum_typeoption')->fetch_all(array_keys($typevararr));
		foreach($typevararr as $option) {
			$option['title'] = $typeoptionarr[$option['optionid']]['title'];
			$option['type'] = $typeoptionarr[$option['optionid']]['type'];
			$option['identifier'] = $typeoptionarr[$option['optionid']]['identifier'];
			$jsoptionids .= "optionids.push($option[optionid]);\r\n";
			$optiontitle[$option['identifier']] = $option['title'];
			$showoption[$option['optionid']]['optionid'] = $option['optionid'];
			$showoption[$option['optionid']]['title'] = $option['title'];
			$showoption[$option['optionid']]['type'] = $lang['threadtype_edit_vars_type_'. $option['type']];
			$showoption[$option['optionid']]['identifier'] = $option['identifier'];
			$showoption[$option['optionid']]['displayorder'] = $option['displayorder'];
			$showoption[$option['optionid']]['available'] = $option['available'];
			$showoption[$option['optionid']]['required'] = $option['required'];
			$showoption[$option['optionid']]['unchangeable'] = $option['unchangeable'];
			$showoption[$option['optionid']]['search'] = $option['search'];
			$showoption[$option['optionid']]['subjectshow'] = $option['subjectshow'];
		}
		unset($typevararr, $typeoptionarr);

		if($existoption && is_array($existoption)) {
			$optionids = array();
			foreach($existoption as $optionid => $val) {
				$optionids[] = $optionid;
			}
			foreach(C::t('forum_typeoption')->fetch_all($optionids) as $option) {
				$showoption[$option['optionid']]['optionid'] = $option['optionid'];
				$showoption[$option['optionid']]['title'] = $option['title'];
				$showoption[$option['optionid']]['type'] = $lang['threadtype_edit_vars_type_'. $option['type']];
				$showoption[$option['optionid']]['identifier'] = $option['identifier'];
				$showoption[$option['optionid']]['required'] = $existoption[$option['optionid']];
				$showoption[$option['optionid']]['available'] = 1;
				$showoption[$option['optionid']]['unchangeable'] = 0;
				$showoption[$option['optionid']]['model'] = 1;
			}
		}

		$searchtitle = $searchvalue = $searchunit = array();
		foreach($showoption as $optionid => $option) {
			$sortoptions .= showtablerow('id="optionid'.$optionid.'"', array('class="td25"', 'class="td28 td23"'), array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$option[optionid]\">",
				"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayorder[$option[optionid]]\" value=\"$option[displayorder]\">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"available[$option[optionid]]\" value=\"1\" ".($option['available'] ? 'checked' : '')." ".($option['model'] ? 'disabled' : '').">",
				dhtmlspecialchars($option['title']),
				$option['type'],
				"<input class=\"checkbox\" type=\"checkbox\" name=\"required[$option[optionid]]\" value=\"1\" ".($option['required'] ? 'checked' : '')." ".($option['model'] ? 'disabled' : '').">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"unchangeable[$option[optionid]]\" value=\"1\" ".($option['unchangeable'] ? 'checked' : '').">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"search[$option[optionid]][form]\" value=\"1\" ".(getstatus($option['search'], 1) == 1 ? 'checked' : '').">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"search[$option[optionid]][font]\" value=\"1\" ".(getstatus($option['search'], 2) == 1 ? 'checked' : '').">",
				"<input class=\"checkbox\" type=\"checkbox\" name=\"subjectshow[$option[optionid]]\" value=\"1\" ".($option['subjectshow'] ? 'checked' : '').">",
				"<a href=\"".ADMINSCRIPT."?action=threadtypes&operation=optiondetail&optionid=$option[optionid]\" class=\"act\" target=\"_blank\">".$lang['edit']."</a>"
			), TRUE);
			$searchtitle[] = '/{('.$option['identifier'].')}/e';
			$searchvalue[] = '/\[('.$option['identifier'].')value\]/e';
			$searchunit[] = '/\[('.$option['identifier'].')unit\]/e';
		}

		shownav('forum', 'threadtype_infotypes');
		showsubmenu('threadtype_infotypes', array(
			array('threadtype_infotypes_type', 'threadtypes', 1),
			array('threadtype_infotypes_content', 'threadtypes&operation=content', 0),
			array(array('menu' => ($curclassname ? $curclassname : 'threadtype_infotypes_option'), 'submenu' => $classoptionmenu), '', 0)
		));
		showsubmenu('forums_edit_threadsorts');
		showtips('forums_edit_threadsorts_tips');

		showformheader("threadtypes&operation=sortdetail&sortid={$_GET['sortid']}");
		showtableheader('threadtype_infotypes_validity', 'nobottom');
		showsetting('threadtype_infotypes_validity', 'typeexpiration', $threadtype['expiration'], 'radio');
		showtablefooter();

		showtableheader("$threadtype[name] - $lang[threadtype_infotypes_add_option]", 'noborder fixpadding');
		showtablerow('', 'id="classlist"', '');
		showtablerow('', 'id="optionlist"', '');
		showtablefooter();

		showtableheader("$threadtype[name] - $lang[threadtype_infotypes_exist_option]", 'noborder fixpadding', 'id="sortlist"');
		showsubtitle(array('<input type="checkbox" name="chkall" id="chkall" class="checkbox" onclick="checkAll(\'prefix\', this.form,\'delete\')" /><label for="chkall">'.cplang('del').'</label>', 'display_order', 'available', 'name', 'type', 'required', 'unchangeable', 'threadtype_infotypes_formsearch', 'threadtype_infotypes_fontsearch', 'threadtype_infotypes_show', ''));
		echo $sortoptions;
		showtablefooter();

?>

<input type="submit" class="btn" name="sortdetailsubmit" value="<?php echo $lang['submit'];?>">
</form>
<script type="text/JavaScript">
	var optionids = new Array();
	<?php echo $jsoptionids;?>

	function checkedbox() {
		var tags = $('optionlist').getElementsByTagName('input');
		for(var i=0; i<tags.length; i++) {
			if(in_array(tags[i].value, optionids)) {
				tags[i].checked = true;
			}
		}
	}
	function insertoption(optionid) {
		var x = new Ajax();
		x.optionid = optionid;
		x.get('<?php echo ADMINSCRIPT;?>?action=threadtypes&operation=sortlist&inajax=1&optionid=' + optionid, function(s, x) {
			if(!in_array(x.optionid, optionids)) {
				var div = document.createElement('div');
				div.style.display = 'none';
				$('append_parent').appendChild(div);
				div.innerHTML = '<table>' + s + '</table>';
				var tr = div.getElementsByTagName('tr');
				var trs = $('sortlist').getElementsByTagName('tr');
				tr[0].id = 'optionid' + optionid;
				trs[trs.length - 1].parentNode.appendChild(tr[0]);
				$('append_parent').removeChild(div);
				optionids.push(x.optionid);
			} else {
				$('optionid' + x.optionid).parentNode.removeChild($('optionid' + x.optionid));
				for(var i=0; i<optionids.length; i++) {
					if(optionids[i] == x.optionid) {
						optionids[i] = 0;
					}
				}
			}
		});
	}
</script>
<script type="text/JavaScript">ajaxget('<?php echo ADMINSCRIPT;?>?action=threadtypes&operation=classlist', 'classlist');</script>
<script type="text/JavaScript">ajaxget('<?php echo ADMINSCRIPT;?>?action=threadtypes&operation=optionlist&sortid=<?php echo $_GET['sortid'];?>', 'optionlist', '', '', '', checkedbox);</script>
<?php

	} else {
		$threadtype = C::t('forum_threadtype')->fetch($_GET['sortid']);
		if($_GET['typeexpiration'] != $threadtype['expiration']) {
			$query = C::t('forum_forum')->fetch_all_for_threadsorts();
			$fidsarray = array();
			foreach($query as $forum) {
				$forum['threadsorts'] = dunserialize($forum['threadsorts']);
				if(is_array($forum['threadsorts']['types'])) {
					foreach($forum['threadsorts']['types'] as $typeid => $name) {
						$typeid == $_GET['sortid'] && $fidsarray[$forum['fid']] = $forum['threadsorts'];
					}
				}
			}
			if($fidsarray) {
				foreach($fidsarray as $changefid => $forumthreadsorts) {
					$forumthreadsorts['expiration'][$_GET['sortid']] = $_GET['typeexpiration'];
					C::t('forum_forumfield')->update($changefid, array('threadsorts' => serialize($forumthreadsorts)));
				}
			}
		}
		C::t('forum_threadtype')->update($_GET['sortid'], array('special' => 1, 'modelid' => $_GET['modelid'], 'expiration' => $_GET['typeexpiration']));

		if(submitcheck('sortdetailsubmit')) {

			$orgoption = $orgoptions = $addoption = array();
			foreach(C::t('forum_typevar')->fetch_all_by_sortid($_GET['sortid']) as $orgoption) {
				$orgoptions[] = $orgoption['optionid'];
			}

			$addoption = $addoption ? (array)$addoption + (array)$_GET['displayorder'] : (array)$_GET['displayorder'];

			@$newoptions = array_keys($addoption);

			if(empty($addoption)) {
				cpmsg('threadtype_infotypes_invalid', '', 'error');
			}

			@$delete = array_merge((array)$_GET['delete'], array_diff($orgoptions, $newoptions));

			if($delete) {
				if($ids = dimplode($delete)) {
					C::t('forum_typevar')->delete($_GET['sortid'], $delete);
				}
				foreach($delete as $id) {
					unset($addoption[$id]);
				}
			}

			$insertoptionid = $indexoption = array();
			$create_table_sql = $separator = $create_tableoption_sql = '';

			if(is_array($addoption) && !empty($addoption)) {
				foreach(C::t('forum_typeoption')->fetch_all(array_keys($addoption)) as $option) {
					$insertoptionid[$option['optionid']]['type'] = $option['type'];
					$insertoptionid[$option['optionid']]['identifier'] = $option['identifier'];
				}

				if(!C::t('forum_optionvalue')->showcolumns($_GET['sortid'])) {
					$fields = '';
					foreach($addoption as $optionid => $option) {
						$identifier = $insertoptionid[$optionid]['identifier'];
						if($identifier) {
							if(in_array($insertoptionid[$optionid]['type'], array('radio'))) {
								$create_tableoption_sql .= "$separator$identifier smallint(6) UNSIGNED NOT NULL DEFAULT '0'";
							} elseif(in_array($insertoptionid[$optionid]['type'], array('number', 'range'))) {
								$create_tableoption_sql .= "$separator$identifier int(10) UNSIGNED NOT NULL DEFAULT '0'";
							} elseif($insertoptionid[$optionid]['type'] == 'select') {
								$create_tableoption_sql .= "$separator$identifier varchar(50) NOT NULL";
							} else {
								$create_tableoption_sql .= "$separator$identifier mediumtext NOT NULL";
							}
							$separator = ' ,';
							if(in_array($insertoptionid[$optionid]['type'], array('radio', 'select', 'number'))) {
								$indexoption[] = $identifier;
							}
						}
					}
					$fields .= ($create_tableoption_sql ? $create_tableoption_sql.',' : '')."tid mediumint(8) UNSIGNED NOT NULL DEFAULT '0',fid smallint(6) UNSIGNED NOT NULL DEFAULT '0',dateline int(10) UNSIGNED NOT NULL DEFAULT '0',expiration int(10) UNSIGNED NOT NULL DEFAULT '0',";
					$fields .= "KEY (fid), KEY(dateline)";
					if($indexoption) {
						foreach($indexoption as $index) {
							$fields .= "$separator KEY $index ($index)";
							$separator = ' ,';
						}
					}
					$dbcharset = $_G['config']['db'][1]['dbcharset'];
					$dbcharset = empty($dbcharset) ? str_replace('-','',CHARSET) : $dbcharset;

					C::t('forum_optionvalue')->create($_GET['sortid'], $fields, $dbcharset);
				} else {
					$tables = C::t('forum_optionvalue')->showcolumns($_GET['sortid']);

					foreach($addoption as $optionid => $option) {
						$identifier = $insertoptionid[$optionid]['identifier'];
						if(!$tables[$identifier]) {
							$fieldname = $identifier;
							if(in_array($insertoptionid[$optionid]['type'], array('radio'))) {
								$fieldtype = 'smallint(6) UNSIGNED NOT NULL DEFAULT \'0\'';
							} elseif(in_array($insertoptionid[$optionid]['type'], array('number', 'range'))) {
								$fieldtype = 'int(10) UNSIGNED NOT NULL DEFAULT \'0\'';
							} elseif($insertoptionid[$optionid]['type'] == 'select') {
								$fieldtype = 'varchar(50) NOT NULL';
							} else {
								$fieldtype = 'mediumtext NOT NULL';
							}
							C::t('forum_optionvalue')->alter($_GET['sortid'], "ADD $fieldname $fieldtype");

							if(in_array($insertoptionid[$optionid]['type'], array('radio', 'select', 'number'))) {
								C::t('forum_optionvalue')->alter($_GET['sortid'], "ADD INDEX ($fieldname)");
							}
						}
					}
				}
				foreach($addoption as $id => $val) {
					$optionid = C::t('forum_typeoption')->fetch($id);
					if($optionid) {
						$data = array(
							'sortid' => $_GET['sortid'],
							'optionid' => $id,
							'available' => 1,
							'required' => intval($val),
						);
						C::t('forum_typevar')->insert($data, 0, 0, 1);
						$search_bit = 0;
						foreach($_GET['search'][$id] AS $key => $val) {
							if($val == 1) {
								if($key == 'font') {
									$search_bit = setstatus(2, 1, $search_bit);
								} elseif($key == 'form') {
									$search_bit = setstatus(1, 1, $search_bit);
								}
							}
						}

						C::t('forum_typevar')->update($_GET['sortid'], $id, array(
							'displayorder' => $_GET['displayorder'][$id],
							'available' => $_GET['available'][$id],
							'required' => $_GET['required'][$id],
							'unchangeable' => $_GET['unchangeable'][$id],
							'search' => $search_bit,
							'subjectshow' => $_GET['subjectshow'][$id],
						));
					} else {
						C::t('forum_typevar')->delete($_GET['sortid'], $id);
					}
				}
			}

			updatecache('threadsorts');
			cpmsg('threadtype_infotypes_succeed', 'action=threadtypes&operation=sortdetail&sortid='.$_GET['sortid'], 'succeed');

		}

	}

} elseif($operation == 'sorttemplate') {

	if(!submitcheck('sorttemplatesubmit')) {
		$threadtype = C::t('forum_threadtype')->fetch($_GET['sortid']);
		$showoption = '';
		$typevararr = C::t('forum_typevar')->fetch_all_by_sortid($_GET['sortid'], 'ASC');
		$typeoptionarr = C::t('forum_typeoption')->fetch_all(array_keys($typevararr));
		foreach($typevararr as $option) {
			$option['title'] = $typeoptionarr[$option['optionid']]['title'];
			$option['type'] = $typeoptionarr[$option['optionid']]['type'];
			$option['identifier'] = $typeoptionarr[$option['optionid']]['identifier'];
			$showoption .= '<button onclick="settip(this, \''.$option['identifier'].'\')" type="button">'.$option['title'].'</button>&nbsp;&nbsp;';
		}
		unset($typevararr, $typeoptionarr);
		showsubmenu('threadtype_infotypes', array(
				array('threadtype_infotypes_type', 'threadtypes', 1),
				array('threadtype_infotypes_content', 'threadtypes&operation=content', 0),
				array(array('menu' => ($curclassname ? $curclassname : 'threadtype_infotypes_option'), 'submenu' => $classoptionmenu), '', 0)
			));

		showformheader("threadtypes&operation=sorttemplate&sortid={$_GET['sortid']}");
		echo '<script type="text/JavaScript">var currentAnchor = \'ltype\';</script>'.
			'<div class="itemtitle" style="width:100%;margin-bottom:5px;"><ul class="tab1" id="submenu">'.
			'<li id="nav_ttype" onclick="showanchor(this)" class="current"><a href="#"><span>'.$lang['threadtype_template_viewthread'].'</span></a></li>'.
			'<li id="nav_stype" onclick="showanchor(this)"><a href="#"><span>'.$lang['threadtype_template_forumdisplay'].'</span></a></li>'.
			'<li id="nav_ptype" onclick="showanchor(this)"><a href="#"><span>'.$lang['threadtype_template_post'].'</span></a></li>'.
			'<li id="nav_btype" onclick="showanchor(this)"><a href="#"><span>'.$lang['threadtype_template_diy'].'</span></a></li>'.
			'</ul></div>';

		echo '<div id="ttype">'.
			$showoption.
			'<div id="ttype_tip"></div>'.
			'<br /><textarea cols="100" rows="15" id="ttypetemplate" name="typetemplate" style="width: 95%;" onkeyup="textareasize(this)" onkeydown="textareakey(this, event)">'.dhtmlspecialchars($threadtype['template']).'</textarea>'.
			'</div>';

		echo '<div id="stype" style="display:none">'.
			'<button onclick="settip(this, \'subject\', \'subject/'.$lang['threadtype_template_threadtitle'].'|subject_url/'.$lang['threadtype_template_threadurl'].'|tid/'.$lang['threadtype_template_threadid'].'\')" type="button">'.$lang['threadtype_template_threadtitle'].'</button>&nbsp;&nbsp;'.
			'<textarea id="subject_sample" style="display:none" onkeyup="textareasize(this)" onkeydown="textareakey(this, event)"><a href="{subject_url}">{subject}</a></textarea>'.
			'<button onclick="settip(this, \'\', \'dateline/'.$lang['threadtype_template_dateline'].'\')" type="button">'.$lang['threadtype_template_dateline'].'</button>&nbsp;&nbsp;'.
			'<button onclick="settip(this, \'author\', \'author/'.$lang['threadtype_template_author'].'|authorid/'.$lang['threadtype_template_authorid'].'|author_url/'.$lang['threadtype_template_authorurl'].'|avatar_small/'.$lang['threadtype_template_authoravatar'].'|author_verify/'.$lang['threadtype_template_authorverify'].'\')" type="button">'.$lang['threadtype_template_threadauthor'].'</button>&nbsp;&nbsp;'.
			'<textarea id="author_sample" style="display:none" onkeyup="textareasize(this)" onkeydown="textareakey(this, event)"><a href="{author_url}">{author}</a></textarea>'.
			'<button onclick="settip(this, \'\', \'views/'.$lang['threadtype_template_threadviews'].'\')" type="button">'.$lang['threadtype_template_threadviews'].'</button>&nbsp;&nbsp;'.
			'<button onclick="settip(this, \'\', \'replies/'.$lang['threadtype_template_threadreplies'].'\')" type="button">'.$lang['threadtype_template_threadreplies'].'</button>&nbsp;&nbsp;'.
			'<button onclick="settip(this, \'lastpost\', \'lastpost/'.$lang['threadtype_template_lastpostdateline'].'|lastpost_url/'.$lang['threadtype_template_lastposturl'].'|lastposter/'.$lang['threadtype_template_lastpostuser'].'|lastposter_url/'.$lang['threadtype_template_lastpostuserurl'].'\')" type="button">'.$lang['threadtype_template_lastpost'].'</button>&nbsp;&nbsp;'.
			'<textarea id="lastpost_sample" style="display:none" onkeyup="textareasize(this)" onkeydown="textareakey(this, event)"><a href="{lastpost_url}">{lastpost}</a> by <a href="{lastposter_url}">{lastposter}</a></textarea>'.
			'<button onclick="settip(this, \'typename\', \'typename/'.$lang['threadtype_template_threadtypename'].'|typename_url/'.$lang['threadtype_template_threadtypeurl'].'\')" type="button">'.$lang['threadtype_template_threadtype'].'</button>&nbsp;&nbsp;'.
			'<textarea id="typename_sample" style="display:none" onkeyup="textareasize(this)" onkeydown="textareakey(this, event)"><a href="{typename_url}">{typename}</a></textarea>'.
			'<button onclick="settip(this, \'\', \'attachment/'.$lang['threadtype_template_attachmentexist'].'\')" type="button">'.$lang['threadtype_template_attachment'].'</button>&nbsp;&nbsp'.
			'<button onclick="settip(this, \'\', \'modcheck/'.$lang['threadtype_template_modcheck'].'\')" type="button">'.$lang['threadtype_template_threadmod'].'</button>&nbsp;&nbsp'.
			'<button onclick="settip(this, \'loop\', \'/'.$lang['threadtype_template_loop'].'\')" type="button">[loop]...[/loop]</button>&nbsp;&nbsp;'.
			'<textarea id="loop_sample" style="display:none" onkeyup="textareasize(this)" onkeydown="textareakey(this, event)">
<table><tr><td>'.$lang['threadtype_template_title'].'</td></tr>
[loop]<tr><td><a href="{subject_url}">{subject}</a></td></tr>[/loop]
</table>
			</textarea>'.
			'<br />'.
			$showoption.
			'<div id="stype_tip"></div>'.
			'<br /><textarea cols="100" rows="15" id="stypetemplate" name="stypetemplate" style="width: 95%;" onkeyup="textareasize(this)" onkeydown="textareakey(this, event)">'.dhtmlspecialchars($threadtype['stemplate']).'</textarea>'.
			'</div>';

		echo '<div id="ptype" style="display:none">'.
			$showoption.
			'<div id="ptype_tip"></div>'.
			'<br /><textarea cols="100" rows="15" id="ptypetemplate" name="ptypetemplate" style="width: 95%;" onkeyup="textareasize(this)" onkeydown="textareakey(this, event)">'.dhtmlspecialchars($threadtype['ptemplate']).'</textarea>'.
			'</div>';

		echo '<div id="btype" style="display:none">'.
			'<button onclick="settip(this, \'subject\', \'subject/'.$lang['threadtype_template_threadtitle'].'|subject_url/'.$lang['threadtype_template_threadurl'].'|tid/'.$lang['threadtype_template_threadid'].'\')" type="button">'.$lang['threadtype_template_threadtitle'].'</button>&nbsp;&nbsp;'.
			'<button onclick="settip(this, \'\', \'dateline/'.$lang['threadtype_template_dateline'].'\')" type="button">'.$lang['threadtype_template_dateline'].'</button>&nbsp;&nbsp;'.
			'<button onclick="settip(this, \'author\', \'author/'.$lang['threadtype_template_author'].'|authorid/'.$lang['threadtype_template_authorid'].'|author_url/'.$lang['threadtype_template_authorurl'].'|avatar_small/'.$lang['threadtype_template_authoravatar'].'|author_verify/'.$lang['threadtype_template_authorverify'].'\')" type="button">'.$lang['threadtype_template_threadauthor'].'</button>&nbsp;&nbsp;'.
			'<button onclick="settip(this, \'\', \'views/'.$lang['threadtype_template_threadviews'].'\')" type="button">'.$lang['threadtype_template_threadviews'].'</button>&nbsp;&nbsp;'.
			'<button onclick="settip(this, \'\', \'replies/'.$lang['threadtype_template_threadreplies'].'\')" type="button">'.$lang['threadtype_template_threadreplies'].'</button>&nbsp;&nbsp;'.
			'<button onclick="settip(this, \'lastpost\', \'lastpost/'.$lang['threadtype_template_lastpostdateline'].'|lastpost_url/'.$lang['threadtype_template_lastposturl'].'|lastposter/'.$lang['threadtype_template_lastpostuser'].'|lastposter_url/'.$lang['threadtype_template_lastpostuserurl'].'\')" type="button">'.$lang['threadtype_template_lastpost'].'</button>&nbsp;&nbsp;'.
			'<button onclick="settip(this, \'typename\', \'typename/'.$lang['threadtype_template_threadtypename'].'|typename_url/'.$lang['threadtype_template_threadtypeurl'].'\')" type="button">'.$lang['threadtype_template_threadtype'].'</button>&nbsp;&nbsp;'.
			'<button onclick="settip(this, \'\', \'attachment/'.$lang['threadtype_template_attachmentexist'].'\')" type="button">'.$lang['threadtype_template_attachment'].'</button>&nbsp;&nbsp'.
			'<button onclick="settip(this, \'loop\', \'/'.$lang['threadtype_template_loop'].'\')" type="button">[loop]...[/loop]</button>&nbsp;&nbsp;'.
			'<br />'.
			$showoption.
			'<div id="btype_tip"></div>'.
			'<br /><textarea cols="100" rows="15" id="btypetemplate" name="btypetemplate" style="width: 95%;" onkeyup="textareasize(this)" onkeydown="textareakey(this, event)">'.dhtmlspecialchars($threadtype['btemplate']).'</textarea>'.
			'</div>'.
			'<input type="submit" class="btn" name="sorttemplatesubmit" value="'.$lang['submit'].'"></form>';

		echo '<script>
		function settip(obj, id, tips) {
			var tips = !tips ? 0 : tips.split(\'|\');
			var tipid = obj.parentNode.id + \'_tip\', s1 = \'\', s2 = \'\', s3 = \'\';
			if(!tips) {
				s1 += \'<td>{\' + id + \'}</td>\';
				s2 += \'<td>'.$lang['threadtype_template_varname'].'(\' + obj.innerHTML + \')</td>\';
				s1 += \'<td>{\' + id + \'_value}</td>\';
				s2 += \'<td>'.$lang['threadtype_template_varvalue'].'</td>\';
				s1 += \'<td>{\' + id + \'_unit}</td>\';
				s2 += \'<td>'.$lang['threadtype_template_varunit'].'</td>\';
				if(obj.parentNode.id == \'ptype\') {
					s1 += \'<td>{\' + id + \'_required}</td>\';
					s2 += \'<td>'.$lang['threadtype_template_requiredflag'].'</td>\';
					s1 += \'<td>{\' + id + \'_tips}</td>\';
					s2 += \'<td>'.$lang['threadtype_template_tipflag'].'</td>\';
					s1 += \'<td>{\' + id + \'_description}</td>\';
					s2 += \'<td>'.$lang['threadtype_template_briefdes'].'</td>\';
				}
				if(obj.parentNode.id == \'ptype\') {
					s3 = \'<dt><strong class="rq">{\' + id + \'_required}</strong>{\' + id + \'}</dt><dd>{\' + id + \'_value} {\' + id + \'_unit} {\' + id + \'_tips} {\' + id + \'_description}</dd>\r\n\';
				} else {
					s3 = obj.parentNode.id == \'ttype\' ? \'<dt>{\' + id + \'}:</dt><dd>{\' + id + \'_value} {\' + id + \'_unit}</dd>\r\n\' : \'<p><em>{\' + id + \'}:</em>{\' + id + \'_value} {\' + id + \'_unit}</p>\r\n\';
				}
			} else {
				for(i = 0;i < tips.length;i++) {
					var i0 = tips[i].substr(0, tips[i].indexOf(\'/\'));
					var i1 = tips[i].substr(tips[i].indexOf(\'/\') + 1);
					if(i0) {
						s1 += \'<td>{\' + i0 + \'}</td>\';
					}
					s2 += \'<td>\' + i1 + \'</td>\';
				}
				if($(id + \'_sample\')) {
					s3 = $(id + \'_sample\').innerHTML;
				}
			}
			$(tipid).innerHTML = \'<table class="tb tb2">\' +
				(s1 ? \'<tr><td class="bold" width="50">'.$lang['threadtype_template_tag'].'</td>\' + s1 + \'</tr>\' : \'\') +
				\'<tr><td class="bold" width="50">'.$lang['threadtype_template_intro'].'</td>\' + s2 + \'</tr></table>\';
			if(s3) {
				$(tipid).innerHTML += \'<table class="tb tb2"><tr><td class="bold" width="50">'.$lang['threadtype_template_example'].'</td><td colspan="6"><textarea style="width: 95%;" rows="2" readonly onclick="this.select()" id="\' + obj.parentNode.id + \'_sample">\' + s3 + \'</textarea></td></tr></table>\';
			}
		}
		</script>';
	} else {
		C::t('forum_threadtype')->update($_GET['sortid'], array(
			'special' => 1,
			'template' => $_GET['typetemplate'],
			'stemplate' => $_GET['stypetemplate'],
			'ptemplate' => $_GET['ptypetemplate'],
			'btemplate' => $_GET['btypetemplate'],
			'expiration' => $_GET['typeexpiration'],
		));
		updatecache('threadsorts');
		cpmsg('threadtype_infotypes_succeed', 'action=threadtypes&operation=sorttemplate&sortid='.$_GET['sortid'], 'succeed');
	}

} elseif($operation == 'content') {

	if(!submitcheck('searchsortsubmit', 1) && !submitcheck('delsortsubmit') && !submitcheck('sendpmsubmit')) {

		shownav('forum', 'threadtype_infotypes');
		showsubmenu('threadtype_infotypes', array(
			array('threadtype_infotypes_type', 'threadtypes', 0),
			array('threadtype_infotypes_content', 'threadtypes&operation=content', 1),
			array(array('menu' => ($curclassname ? $curclassname : 'threadtype_infotypes_option'), 'submenu' => $classoptionmenu))
		));

		$_GET['sortid'] = intval($_GET['sortid']);
		$threadtypes = '<select name="sortid" onchange="window.location.href = \'?action=threadtypes&operation=content&sortid=\'+ this.options[this.selectedIndex].value"><option value="0">'.cplang('none').'</option>';
		$query = C::t('forum_threadtype')->fetch_all_for_order();
		foreach($query as $type) {
			$threadtypes .= '<option value="'.$type['typeid'].'" '.($_GET['sortid'] == $type['typeid'] ? 'selected="selected"' : '').'>'.dhtmlspecialchars($type['name']).'</option>';
		}
		$threadtypes .= '</select>';

		showformheader('threadtypes&operation=content');
		showtableheader('threadtype_content_choose');
		showsetting('threadtype_content_name', '', '', $threadtypes);

		if($_GET['sortid']) {
			showtableheader('threadtype_content_sort_by_conditions');
			loadcache(array('threadsort_option_'.$_GET['sortid']));

			$sortoptionarray = $_G['cache']['threadsort_option_'.$_GET['sortid']];
			if(is_array($sortoptionarray)) foreach($sortoptionarray as $optionid => $option) {
				$optionshow = '';
				if($option['search']) {
					if(in_array($option['type'], array('radio', 'checkbox', 'select'))){
						if($option['type'] == 'select') {
							$optionshow .= '<select name="searchoption['.$optionid.'][value]"><option value="0">'.cplang('unlimited').'</option>';
							foreach($option['choices'] as $id => $value) {
								$optionshow .= '<option value="'.$id.'" '.($_GET['searchoption'][$optionid]['value'] == $id ? 'selected="selected"' : '').'>'.$value.'</option>';
							}
							$optionshow .= '</select><input type="hidden" name="searchoption['.$optionid.'][type]" value="select">';
						} elseif($option['type'] == 'radio') {
							$optionshow .= '<input type="radio" class="radio" name="searchoption['.$optionid.'][value]" value="0" checked="checked"]>'.cplang('unlimited').'&nbsp;';
							foreach($option['choices'] as $id => $value) {
								$optionshow .= '<input type="radio" class="radio" name="searchoption['.$optionid.'][value]" value="'.$id.'" '.($_GET['searchoption'][$optionid]['value'] == $id ? 'checked="checked"' : '').'> '.$value.' &nbsp;';
							}
							$optionshow .= '<input type="hidden" name="searchoption['.$optionid.'][type]" value="radio">';
						} elseif($option['type'] == 'checkbox') {
							foreach($option['choices'] as $id => $value) {
								$optionshow .= '<input type="checkbox" class="checkbox" name="searchoption['.$optionid.'][value]['.$id.']" value="'.$id.'" '.($_GET['searchoption'][$optionid]['value'] == $id ? 'checked="checked"' : '').'> '.$value.'';
							}
							$optionshow .= '<input type="hidden" name="searchoption['.$optionid.'][type]" value="checkbox">';
						}
					} elseif(in_array($option['type'], array('number', 'text', 'email', 'calendar', 'image', 'url', 'textarea', 'upload', 'range'))) {
						if ($option['type'] == 'calendar') {
							$optionshow .= '<script type="text/javascript" src="'.$_G['setting']['jspath'].'calendar.js?'.VERHASH.'"></script><input type="text" name="searchoption['.$optionid.'][value]" class="txt" value="'.$_GET['searchoption'][$optionid]['value'].'" onclick="showcalendar(event, this, false)" />';
						} elseif($option['type'] == 'number') {
							$optionshow .= '<select name="searchoption['.$optionid.'][condition]">
								<option value="0" '.($_GET['searchoption'][$optionid]['condition'] == 0 ? 'selected="selected"' : '').'>'.cplang('equal_to').'</option>
								<option value="1" '.($_GET['searchoption'][$optionid]['condition'] == 1 ? 'selected="selected"' : '').'>'.cplang('more_than').'</option>
								<option value="2" '.($_GET['searchoption'][$optionid]['condition'] == 2 ? 'selected="selected"' : '').'>'.cplang('lower_than').'</option>
							</select>&nbsp;&nbsp;
							<input type="text" class="txt" name="searchoption['.$optionid.'][value]" value="'.$_GET['searchoption'][$optionid]['value'].'" />
							<input type="hidden" name="searchoption['.$optionid.'][type]" value="number">';
						} elseif($option['type'] == 'range') {
							$optionshow .= '<input type="text" name="searchoption['.$optionid.'][value][min]" size="16" value="'.$_GET['searchoption'][$optionid]['value']['min'].'" /> -
							<input type="text" name="searchoption['.$optionid.'][value][max]" size="16" value="'.$_GET['searchoption'][$optionid]['value']['max'].'" />
							<input type="hidden" name="searchoption['.$optionid.'][type]" value="range">';
						} else {
							$optionshow .= '<input type="text" name="searchoption['.$optionid.'][value]" class="txt" value="'.$_GET['searchoption'][$optionid]['value'].'" />';
						}
					}
					$optionshow .=  '&nbsp;'.$option['unit'];
					showsetting($option['title'], '', '', $optionshow);
				}
			}
		}

		showsubmit('searchsortsubmit', 'submit');
		showtablefooter();
		showformfooter();

	} else {

		if(submitcheck('searchsortsubmit', 1)) {

			if(empty($_GET['searchoption']) && !$_GET['sortid']) {
				cpmsg('threadtype_content_no_choice', 'action=threadtypes&operation=content', 'error');
			}
			$mpurl = ADMINSCRIPT.'?action=threadtypes&operation=content&sortid='.$_GET['sortid'].'&searchsortsubmit=true';
			if(!is_array($_GET['searchoption'])) {
				$mpurl .= '&searchoption='.$_GET['searchoption'];
				$_GET['searchoption'] = dunserialize(base64_decode($_GET['searchoption']));
			} else {
				$mpurl .= '&searchoption='.base64_encode(serialize($_GET['searchoption']));
			}

			shownav('forum', 'threadtype_infotypes');
			showsubmenu('threadtype_infotypes', array(
				array('threadtype_infotypes_type', 'threadtypes', 0),
				array('threadtype_infotypes_content', 'threadtypes&operation=content', 1),
				array(array('menu' => ($curclassname ? $curclassname : 'threadtype_infotypes_option'), 'submenu' => $classoptionmenu))
			));

			loadcache('forums');
			loadcache(array('threadsort_option_'.$_GET['sortid']));
			require_once libfile('function/threadsort');
			sortthreadsortselectoption($_GET['sortid']);
			$sortoptionarray = $_G['cache']['threadsort_option_'.$_GET['sortid']];
			$selectsql = '';
			if($_GET['searchoption']) {
				foreach($_GET['searchoption'] as $optionid => $option) {
					$fieldname = $sortoptionarray[$optionid]['identifier'] ? $sortoptionarray[$optionid]['identifier'] : 1;
					if($option['value']) {
						if(in_array($option['type'], array('number', 'radio'))) {
							$option['value'] = intval($option['value']);
							$exp = '=';
							if($option['condition']) {
								$exp = $option['condition'] == 1 ? '>' : '<';
							}
							$sql = "$fieldname$exp'$option[value]'";
						} elseif($option['type'] == 'select') {
							$subvalues = $currentchoices = array();
							if(!empty($sortoptionarray)) {
								foreach($sortoptionarray as $subkey => $subvalue) {
									if($subvalue['identifier'] == $fieldname) {
										$currentchoices = $subvalue['choices'];
										break;
									}
								}
							}
							if(!empty($currentchoices)) {
								foreach($currentchoices as $subkey => $subvalue) {
									if(preg_match('/^'.$option['value'].'/i', $subkey)) {
										$subvalues[] = $subkey;
									}
								}
							}
							$sql = "$fieldname IN (".dimplode($subvalues).")";
						} elseif($option['type'] == 'checkbox') {
							$sql = "$fieldname LIKE '%".(implode("%", $option['value']))."%'";
						} elseif($option['type'] == 'range') {
							$sql = $option['value']['min'] || $option['value']['max'] ? "$fieldname BETWEEN ".intval($option['value']['min'])." AND ".intval($option['value']['max'])."" : '';
						} else {
							$sql = "$fieldname LIKE '%$option[value]%'";
						}
						$selectsql .= $and."$sql ";
						$and = 'AND ';
					}
				}

				$selectsql = trim($selectsql);
				$searchtids = C::t('forum_optionvalue')->fetch_all_tid($_GET['sortid'], $selectsql ? 'WHERE '.$selectsql : '');
			}

			if($searchtids) {
				$lpp = max(5, empty($_GET['lpp']) ? 50 : intval($_GET['lpp']));
				$start_limit = ($page - 1) * $lpp;

				$threadcount = C::t('forum_thread')->count_by_tid_fid($searchtids);
				if($threadcount) {
					foreach(C::t('forum_thread')->fetch_all_by_tid($searchtids, $start_limit, $lpp) as $thread) {
						$threads .= showtablerow('', array('class="td25"', '', '', 'class="td28"', 'class="td28"'), array(
						"<input class=\"checkbox\" type=\"checkbox\" name=\"tidsarray[]\" value=\"$thread[tid]\"/>".
						"<input type=\"hidden\" name=\"fidsarray[]\" value=\"$thread[fid]\"/>",
						"<a href=\"forum.php?mod=viewthread&tid=$thread[tid]\" target=\"_blank\">$thread[subject]</a>",
						"<a href=\"forum.php?mod=forumdisplay&fid=$thread[fid]\" target=\"_blank\">{$_G['cache'][forums][$thread[fid]][name]}</a>",
						"<a href=\"home.php?mod=space&uid=$thread[authorid]\" target=\"_blank\">$thread[author]</a>",
						$thread['replies'],
						$thread['views'],
						dgmdate($thread['lastpost'], 'd'),
						), TRUE);
					}

					$multipage = multi($threadcount, $lpp, $page, $mpurl, 0, 3);
				}
			}

			showformheader('threadtypes&operation=content');
			showtableheader('admin', 'fixpadding');
			showsubtitle(array('', 'subject', 'forum', 'author', 'threads_replies', 'threads_views', 'threads_lastpost'));
			echo $threads;
			echo $multipage;
			showsubmit('', '', '', "<input type=\"submit\" class=\"btn\" name=\"delsortsubmit\" value=\"{$lang[threadtype_content_delete]}\"/>");
			showtablefooter();
			showformfooter();

		} elseif(submitcheck('delsortsubmit')) {

			require_once libfile('function/post');

			if($_GET['tidsarray']) {
				require_once libfile('function/delete');
				deletethread($_GET['tidsarray']);

				if($_G['setting']['globalstick']) {
					updatecache('globalstick');
				}

				if($_GET['fidsarray']) {
					foreach(explode(',', $_GET['fidsarray']) as $fid) {
						updateforumcount(intval($fid));
					}
				}
			}
			cpmsg('threadtype_content_delete_succeed', 'action=threadtypes&operation=content', 'succeed');

		}
	}

} elseif($operation == 'classlist') {

	$classoptions = '';
	$classidarray = array();
	$classid = $_GET['classid'] ? $_GET['classid'] : 0;
	foreach(C::t('forum_typeoption')->fetch_all_by_classid($classid) as $option) {
		$classidarray[] = $option['optionid'];
		$classoptions .= "<a href=\"#ol\" onclick=\"ajaxget('".ADMINSCRIPT."?action=threadtypes&operation=optionlist&typeid={$_GET['typeid']}&classid=$option[optionid]', 'optionlist', 'optionlist', 'Loading...', '', checkedbox)\">$option[title]</a> &nbsp; ";
	}

	include template('common/header');
	echo $classoptions;
	include template('common/footer');
	exit;

} elseif($operation == 'optionlist') {
	$classid = $_GET['classid'];
	if(!$classid) {
		$classid = C::t('forum_typeoption')->fetch_all_by_classid(0, 0, 1);
		$classid = $classid[0]['optionid'];
	}
	$option = $options = array();
	foreach(C::t('forum_typevar')->fetch_all_by_sortid($_GET['typeid']) as $option) {
		$options[] = $option['optionid'];
	}

	$optionlist = '';
	foreach(C::t('forum_typeoption')->fetch_all_by_classid($classid) as $option) {
		$optionlist .= "<input ".(in_array($option['optionid'], $options) ? ' checked="checked" ' : '')."class=\"checkbox\" type=\"checkbox\" name=\"typeselect[]\" id=\"typeselect_$option[optionid]\" value=\"$option[optionid]\" onclick=\"insertoption(this.value);\" /><label for=\"typeselect_$option[optionid]\">".dhtmlspecialchars($option['title'])."</label>&nbsp;&nbsp;";
	}
	include template('common/header');
	echo $optionlist;
	include template('common/footer');
	exit;

} elseif($operation == 'sortlist') {
	$optionid = $_GET['optionid'];
	$option = C::t('forum_typeoption')->fetch($optionid);
	include template('common/header');
	$option['type'] = $lang['threadtype_edit_vars_type_'. $option['type']];
	$option['available'] = 1;
	showtablerow('', array('class="td25"', 'class="td28 td23"'), array(
		"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"$option[optionid]\" ".($option['model'] ? 'disabled' : '').">",
		"<input type=\"text\" class=\"txt\" size=\"2\" name=\"displayorder[$option[optionid]]\" value=\"$option[displayorder]\">",
		"<input class=\"checkbox\" type=\"checkbox\" name=\"available[$option[optionid]]\" value=\"1\" ".($option['available'] ? 'checked' : '')." ".($option['model'] ? 'disabled' : '').">",
		dhtmlspecialchars($option['title']),
		$option[type],
		"<input class=\"checkbox\" type=\"checkbox\" name=\"required[$option[optionid]]\" value=\"1\" ".($option['required'] ? 'checked' : '')." ".($option['model'] ? 'disabled' : '').">",
		"<input class=\"checkbox\" type=\"checkbox\" name=\"unchangeable[$option[optionid]]\" value=\"1\" ".($option['unchangeable'] ? 'checked' : '').">",
		"<input class=\"checkbox\" type=\"checkbox\" name=\"search[$option[optionid]][form]\" value=\"1\" ".(getstatus($option['search'], 1) == 1 ? 'checked' : '').">",
		"<input class=\"checkbox\" type=\"checkbox\" name=\"search[$option[optionid]][font]\" value=\"1\" ".(getstatus($option['search'], 2) == 1 ? 'checked' : '').">",
		"<input class=\"checkbox\" type=\"checkbox\" name=\"subjectshow[$option[optionid]]\" value=\"1\" ".($option['subjectshow'] ? 'checked' : '').">",
		"<a href=\"".ADMINSCRIPT."?action=threadtypes&operation=optiondetail&optionid=$option[optionid]\" class=\"act\">".$lang['edit']."</a>"
	));
	include template('common/footer');
	exit;
} elseif($operation == 'import') {

	$sortid = 0;
	$newthreadtype = getimportdata('Discuz! Threadtypes');

	if($newthreadtype) {
		$idcmp = $searcharr = $replacearr = $indexoption = array();
		$create_tableoption_sql = $separator = '';
		$i = 0;
		foreach($newthreadtype as $key => $value) {
			if(!$i) {
				if($newname1 = trim(strip_tags($value['name']))) {
					$findname = 0;
					$tmpnewname1 = $newname1;
					$decline = '_';
					while(!$findname) {
						if(C::t('forum_threadtype')->checkname($tmpnewname1)) {
							$tmpnewname1 = $newname1.$decline.random(6);
							$decline .= '_';
						} else {
							$findname = 1;
						}
					}
					$newname1 = $tmpnewname1;
					$data = array(
						'name' => $newname1,
						'description' => dhtmlspecialchars(trim($value['ttdescription'])),
						'special' => 1,
					);
					$sortid = C::t('forum_threadtype')->insert($data, 1);
				}
				$i = 1;

				if(empty($value['identifier'])) {
					cpmsg('threadtype_import_succeed', 'action=threadtypes', 'succeed');
				}
			}

			$typeoption = array(
				'classid' => $value['classid'],
				'expiration' => $value['tpexpiration'],
				'protect' => $value['protect'],
				'title' => $value['title'],
				'description' => $value['tpdescription'],
				'type' => $value['type'],
				'unit' => $value['unit'],
				'rules' => $value['rules'],
				'permprompt' => $value['permprompt'],
			);

			$findidentifier = 0;
			$tmpidentifier = trim($value['identifier']);
			if(strlen($tmpidentifier) > 40 || !ispluginkey($tmpidentifier)) {
				cpmsg('threadtype_infotypes_optionvariable_invalid', 'action=threadtypes', 'error');
			}
			$decline = '_';
			while(!$findidentifier) {
				if(C::t('forum_typeoption')->fetch_all_by_identifier($tmpidentifier, 0, 1) || in_array(strtoupper($tmpidentifier), $mysql_keywords)) {
					$tmpidentifier = $value['identifier'].$decline.$sortid;
					if(strlen($tmpidentifier) > 40) {
						cpmsg('threadtype_infotypes_optionvariable_invalid', 'action=threadtypes', 'error');
					}
					$decline .= '_';
				} else {
					$findidentifier = 1;
				}
			}
			$typeoption['identifier'] = $tmpidentifier;
			$idcmp[$value['identifier']] = $tmpidentifier;

			$newoptionid = C::t('forum_typeoption')->insert($typeoption, true);

			$typevar = array(
				'sortid' => $sortid,
				'optionid' => $newoptionid,
				'available' => $value['available'],
				'required' => $value['required'],
				'unchangeable' => $value['unchangeable'],
				'search' => $value['search'],
				'displayorder' => $value['displayorder'],
				'subjectshow' => $value['subjectshow'],
			);
			C::t('forum_typevar')->insert($typevar);

			if($tmpidentifier) {
				if(in_array($value['type'], array('radio'))) {
					$create_tableoption_sql .= "$separator$tmpidentifier smallint(6) UNSIGNED NOT NULL DEFAULT '0'";
				} elseif(in_array($value['type'], array('number', 'range'))) {
					$create_tableoption_sql .= "$separator$tmpidentifier int(10) UNSIGNED NOT NULL DEFAULT '0'";
				} elseif($value['type'] == 'select') {
					$create_tableoption_sql .= "$separator$tmpidentifier varchar(50) NOT NULL";
				} else {
					$create_tableoption_sql .= "$separator$tmpidentifier mediumtext NOT NULL";
				}
				$separator = ' ,';
				if(in_array($value['type'], array('radio', 'select', 'number'))) {
					$indexoption[] = $tmpidentifier;
				}
			}
		}

		foreach($idcmp as $k => $v) {
			if($k != $v) {
				$searcharr[] = '{'.$k;
				$searcharr[] = '['.$k;
				$replacearr[] = '{'.$v;
				$replacearr[] = '['.$v;
			}
		}

		$threadtype = array(
			'icon' => $value['icon'],
			'special' => $value['special'],
			'modelid' => $value['modelid'],
			'expiration' => $value['ttexpiration'],
			'template' => str_replace($searcharr, $replacearr, $value['template']),
			'stemplate' => str_replace($searcharr, $replacearr, $value['stemplate']),
			'ptemplate' => str_replace($searcharr, $replacearr, $value['ptemplate']),
			'btemplate' => str_replace($searcharr, $replacearr, $value['btemplate']),
		);
		DB::update('forum_threadtype', $threadtype, array('typeid' => $sortid));

		$fields = ($create_tableoption_sql ? $create_tableoption_sql.',' : '')."tid mediumint(8) UNSIGNED NOT NULL DEFAULT '0',fid smallint(6) UNSIGNED NOT NULL DEFAULT '0',dateline int(10) UNSIGNED NOT NULL DEFAULT '0',expiration int(10) UNSIGNED NOT NULL DEFAULT '0',";
		$fields .= "KEY (fid), KEY(dateline)";
		if($indexoption) {
			foreach($indexoption as $index) {
				$fields .= "$separator KEY $index ($index)";
				$separator = ' ,';
			}
		}
		$dbcharset = $_G['config']['db'][1]['dbcharset'];
		$dbcharset = empty($dbcharset) ? str_replace('-','',CHARSET) : $dbcharset;
		C::t('forum_optionvalue')->create($sortid, $fields, $dbcharset);

		updatecache('threadsorts');
	}
	cpmsg('threadtype_import_succeed', 'action=threadtypes', 'succeed');

} elseif($operation == 'export') {

	$sortid = intval($_GET['sortid']);
	$typevarlist = array();
	$typevararr = C::t('forum_typevar')->fetch_all_by_sortid($sortid);
	$typeoptionarr = C::t('forum_typeoption')->fetch_all(array_keys($typevararr));
	$threadtypearr = C::t('forum_threadtype')->fetch($sortid);
	foreach($typevararr as $typevar) {
		$typeoption = $typeoptionarr[$typevar['optionid']];
		$typevar = array_merge($threadtypearr, $typevar);
		$typevar = array_merge($typeoption, $typevar);
		$typevar['tpdescription'] = $typeoption['description'];
		$typevar['ttdescription'] = $threadtypearr['description'];
		$typevar['tpexpiration'] = $typeoption['expiration'];
		$typevar['ttexpiration'] = $threadtypearr['expiration'];
		unset($typevar['fid']);
		$typevarlist[] = $typevar;
	}
	if(empty($typevarlist)) {
		$threadtype = C::t('forum_threadtype')->fetch($sortid);
		$threadtype['ttdescription'] = $threadtype['description'];
		unset($threadtype['fid']);
		$typevarlist[] = $threadtype;
	}

	if(empty($typevarlist)) {
		cpmsg('threadtype_export_error');
	}

	exportdata('Discuz! Threadtypes', $typevarlist[0]['typeid'], $typevarlist);
}

?>