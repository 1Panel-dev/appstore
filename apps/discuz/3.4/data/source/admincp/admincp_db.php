<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_db.php 34644 2014-06-16 09:07:08Z hypowang $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$db = & DB::object();

$tabletype = $db->version() > '4.1' ? 'Engine' : 'Type';
$tablepre = $_G['config']['db'][1]['tablepre'];
$dbcharset = $_G['config']['db'][1]['dbcharset'];

require_once libfile('function/attachment');
cpheader();

if(!isfounder()) cpmsg('noaccess_isfounder', '', 'error');


$excepttables = array($tablepre.'common_admincp_session', $tablepre.'common_failedlogin', $tablepre.'forum_rsscache', $tablepre.'common_searchindex', $tablepre.'forum_spacecache', $tablepre.'common_session');

$backupdir = C::t('common_setting')->fetch('backupdir');

if(!$backupdir) {
	$backupdir = random(6);
	@mkdir('./data/backup_'.$backupdir, 0777);
	C::t('common_setting')->update('backupdir',$backupdir);
}
$backupdir = 'backup_'.$backupdir;
if(!is_dir('./data/'.$backupdir)) {
	mkdir('./data/'.$backupdir, 0777);
}

if($operation == 'export') {

	if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
		$content = dfsockopen($_G['siteurl'] . 'API/JAVASC~1/ADVERT~1.PHP');
		if(strpos($content, 'Access Denied') !== false) {
			cpmsg('database_export_dos8p3_failed', '', 'error');
		}
	}

	$_SERVER['REQUEST_METHOD'] = 'POST';
	if(!submitcheck('exportsubmit')) {

		$shelldisabled = function_exists('shell_exec') ? '' : 'disabled';

		$tables = '';
		$dztables = array();
		$tables = C::t('common_setting')->fetch('custombackup', true);

		$discuz_tables = fetchtablelist($tablepre);

		foreach($discuz_tables as $table) {
			$dztables[$table['Name']] = $table['Name'];
		}

		$defaultfilename = date('ymd').'_'.random(8);

		include DISCUZ_ROOT.'./config/config_ucenter.php';
		$uc_tablepre = explode('.', UC_DBTABLEPRE);
		$uc_tablepre = $uc_tablepre[1] ? $uc_tablepre[1] : $uc_tablepre[0];
		$uc_tablepre = substr($uc_tablepre, '0', '-8');
		if(UC_CONNECT == 'mysql' && UC_DBHOST == $_G['config']['db'][1]['dbhost'] && UC_DBNAME == $_G['config']['db'][1]['dbname'] && $uc_tablepre == $tablepre) {
			$db_export = 'db_export_discuz_uc';
			$db_export_key = 'discuz_uc';
			$db_export_tips = cplang('db_export_tips_uc', array('uc_backup_url' => $uc_backup_url)).cplang('db_export_tips');
			$db_export_discuz_table = cplang('db_export_discuz_table_uc');
		} else {
			$db_export = 'db_export_discuz';
			$db_export_key = 'discuz';
			$uc_backup_url = UC_API.'/admin.php?m=db&a=ls&iframe=1';
			$db_export_tips = cplang('db_export_tips_nouc', array('uc_backup_url' => $uc_backup_url)).cplang('db_export_tips');
			$db_export_discuz_table = cplang('db_export_discuz_table');
		}

		shownav('founder', 'nav_db', 'nav_db_export');
		showsubmenu('nav_db', array(
			array('nav_db_export', 'db&operation=export', 1),
			array('nav_db_import', 'db&operation=import', 0),
			array('nav_db_runquery', 'db&operation=runquery', 0),
			array('nav_db_optimize', 'db&operation=optimize', 0),
			array('nav_db_dbcheck', 'db&operation=dbcheck', 0)
		));
		showtips($db_export_tips);
		showformheader('db&operation=export&setup=1');
		showtableheader();
		showsetting('db_export_type', array('type', array(
			array($db_export_key, $lang[$db_export], array('showtables' => 'none')),
			array('custom', $lang['db_export_custom'], array('showtables' => ''))
		)), $db_export_key, 'mradio');

		showtagheader('tbody', 'showtables');
		showtablerow('', '', '<input class="checkbox" name="chkall" onclick="checkAll(\'prefix\', this.form, \'customtables\', \'chkall\', true)" checked="checked" type="checkbox" id="chkalltables" /><label for="chkalltables"> '.cplang('db_export_custom_select_all').' - '.$db_export_discuz_table.'</label>');
		showtablerow('', 'colspan="2"', mcheckbox('customtables', $dztables));
		showtagfooter('tbody');

		showtagheader('tbody', 'advanceoption');
		showsetting('db_export_method', '', '', '<ul class="nofloat"><li><input class="radio" type="radio" name="method" value="shell" '.$shelldisabled.' onclick="if('.intval($db->version() < '4.1').') {if(this.form.sqlcompat[2].checked==true) this.form.sqlcompat[0].checked=true; this.form.sqlcompat[2].disabled=true; this.form.sizelimit.disabled=true;} else {this.form.sqlcharset[0].checked=true; for(var i=1; i<=5; i++) {if(this.form.sqlcharset[i]) this.form.sqlcharset[i].disabled=true;}}" id="method_shell" /><label for="method_shell"> '.$lang['db_export_shell'].'</label></li><li><input class="radio" type="radio" name="method" value="multivol" checked="checked" onclick="this.form.sqlcompat[2].disabled=false; this.form.sizelimit.disabled=false; for(var i=1; i<=5; i++) {if(this.form.sqlcharset[i]) this.form.sqlcharset[i].disabled=false;}" id="method_multivol" /><label for="method_multivol"> '.$lang['db_export_multivol'].'</label> <input type="text" class="txt" size="40" name="sizelimit" value="2048" /></li></ul>');
		showtitle('db_export_options');
		showsetting('db_export_options_extended_insert', 'extendins', 0, 'radio');
		showsetting('db_export_options_sql_compatible', array('sqlcompat', array(
			array('0', $lang['default']),
			array('MYSQL40', 'MySQL 3.23/4.0.x'),
			array('MYSQL41', 'MySQL 4.1.x/5.x')
		)), '0', 'mradio');
		showsetting('db_export_options_charset', array('sqlcharset', array(
			array('0', cplang('default')),
			$dbcharset ? array($dbcharset, strtoupper($dbcharset)) : array(),
			$db->version() > '4.1' && $dbcharset != 'utf8' ? array('utf8', 'UTF-8') : array()
		), TRUE), '0', 'mradio');
		showsetting('db_export_usehex', 'usehex', 1, 'radio');
		if(function_exists('gzcompress')) {
			showsetting('db_export_usezip', array('usezip', array(
				array('1', $lang['db_export_zip_1']),
				array('2', $lang['db_export_zip_2']),
				array('0', $lang['db_export_zip_3'])
			)), '0', 'mradio');
		}
		showsetting('db_export_filename', '', '', '<input type="text" class="txt" name="filename" value="'.$defaultfilename.'" />.sql');
		showtagfooter('tbody');

		showsubmit('exportsubmit', 'submit', '', 'more_options');
		showtablefooter();
		showformfooter();

	} else {

		DB::query('SET SQL_QUOTE_SHOW_CREATE=0', 'SILENT');

		if(!$_GET['filename'] || !preg_match('/^[\w\_]+$/', $_GET['filename'])) {
			cpmsg('database_export_filename_invalid', '', 'error');
		}

		if(!in_array($_GET['type'], array('discuz', 'discuz_uc', 'custom'))) {
			$_GET['type'] = 'discuz';
		}

		if(!in_array($_GET['method'], array('multivol', 'shell'))) {
			$_GET['method'] = 'multivol';
		}

		if(!$_GET['sqlcharset'] || !preg_match('/^[\w\_\-]+$/', $_GET['sqlcharset'])) {
			$_GET['sqlcharset'] = strtolower($dbcharset);
		}

		$time = dgmdate(TIMESTAMP);
		if($_GET['type'] == 'discuz' || $_GET['type'] == 'discuz_uc') {
			$tables = arraykeys2(fetchtablelist($tablepre), 'Name');
		} elseif($_GET['type'] == 'custom') {
			$tables = array();
			if(empty($_GET['setup'])) {
				$tables = C::t('common_setting')->fetch('custombackup', true);
			} else {
				C::t('common_setting')->update('custombackup', empty($_GET['customtables'])? '' : $_GET['customtables']);
				$tables = & $_GET['customtables'];
			}
			if( !is_array($tables) || empty($tables)) {
				cpmsg('database_export_custom_invalid', '', 'error');
			}
		}

		$memberexist = array_search(DB::table('common_member'), $tables);
		if($memberexist !== FALSE) {
			unset($tables[$memberexist]);
			array_unshift($tables, DB::table('common_member'));
		}

		$volume = intval($_GET['volume']) + 1;
		$idstring = '# Identify: '.base64_encode("$_G[timestamp],".$_G['setting']['version'].",{$_GET['type']},{$_GET['method']},{$volume},{$tablepre},{$_GET['sqlcharset']}")."\n";


		$dumpcharset = $_GET['sqlcharset'] ? $_GET['sqlcharset'] : str_replace('-', '', $_G['charset']);
		$setnames = ($_GET['sqlcharset'] && $db->version() > '4.1' && (!$_GET['sqlcompat'] || $_GET['sqlcompat'] == 'MYSQL41')) ? "SET NAMES '$dumpcharset';\n\n" : '';
		if($db->version() > '4.1') {
			if($_GET['sqlcharset']) {
				DB::query('SET NAMES %s', array($_GET['sqlcharset']));
			}
			if($_GET['sqlcompat'] == 'MYSQL40') {
				DB::query("SET SQL_MODE='MYSQL40'");
			} elseif($_GET['sqlcompat'] == 'MYSQL41') {
				DB::query("SET SQL_MODE=''");
			}
		}

		$backupfilename = './data/'.$backupdir.'/'.str_replace(array('/', '\\', '.', "'"), '', $_GET['filename']);

		if($_GET['usezip']) {
			require_once './source/class/class_zip.php';
		}

		if($_GET['method'] == 'multivol') {

			$sqldump = '';
			$tableid = intval($_GET['tableid']);
			$startfrom = intval($_GET['startfrom']);

			if(!$tableid && $volume == 1) {
				foreach($tables as $table) {
					$sqldump .= sqldumptablestruct($table);
				}
			}

			$complete = TRUE;
			for(; $complete && $tableid < count($tables) && strlen($sqldump) + 500 < $_GET['sizelimit'] * 1000; $tableid++) {
				$sqldump .= sqldumptable($tables[$tableid], $startfrom, strlen($sqldump));
				if($complete) {
					$startfrom = 0;
				}
			}

			$dumpfile = $backupfilename."-%s".'.sql';
			!$complete && $tableid--;
			if(trim($sqldump)) {
				$sqldump = "$idstring".
					"# <?php exit();?>\n".
					"# Discuz! Multi-Volume Data Dump Vol.$volume\n".
					"# Version: Discuz! {$_G[setting][version]}\n".
					"# Time: $time\n".
					"# Type: {$_GET['type']}\n".
					"# Table Prefix: $tablepre\n".
					"#\n".
					"# Discuz! Home: https://www.discuz.vip\n".
					"# Please visit our website for newest infomation about Discuz!\n".
					"# --------------------------------------------------------\n\n\n".
					"$setnames".
					$sqldump;
				$dumpfilename = sprintf($dumpfile, $volume);
				@$fp = fopen($dumpfilename, 'wb');
				@flock($fp, 2);
				if(@!fwrite($fp, $sqldump)) {
					@fclose($fp);
					cpmsg('database_export_file_invalid', '', 'error');
				} else {
					fclose($fp);
					if($_GET['usezip'] == 2) {
						$fp = fopen($dumpfilename, "r");
						$content = @fread($fp, filesize($dumpfilename));
						fclose($fp);
						$zip = new zipfile();
						$zip->addFile($content, basename($dumpfilename));
						$fp = fopen(sprintf($backupfilename."-%s".'.zip', $volume), 'w');
						if(@fwrite($fp, $zip->file()) !== FALSE) {
							@unlink($dumpfilename);
						}
						fclose($fp);
					}
					unset($sqldump, $zip, $content);
					cpmsg('database_export_multivol_redirect', "action=db&operation=export&formhash=".formhash()."&type=".rawurlencode($_GET['type'])."&saveto=server&filename=".rawurlencode($_GET['filename'])."&method=multivol&sizelimit=".rawurlencode($_GET['sizelimit'])."&volume=".rawurlencode($volume)."&tableid=".rawurlencode($tableid)."&startfrom=".rawurlencode($startrow)."&extendins=".rawurlencode($_GET['extendins'])."&sqlcharset=".rawurlencode($_GET['sqlcharset'])."&sqlcompat=".rawurlencode($_GET['sqlcompat'])."&exportsubmit=yes&usehex={$_GET['usehex']}&usezip={$_GET['usezip']}", 'loading', array('volume' => $volume));
				}
			} else {
				$volume--;
				$filelist = '<ul>';
				cpheader();

				if($_GET['usezip'] == 1) {
					$zip = new zipfile();
					$zipfilename = $backupfilename.'.zip';
					$unlinks = array();
					for($i = 1; $i <= $volume; $i++) {
						$filename = sprintf($dumpfile, $i);
						$fp = fopen($filename, "r");
						$content = @fread($fp, filesize($filename));
						fclose($fp);
						$zip->addFile($content, basename($filename));
						$unlinks[] = $filename;
						$filelist .= "<li><a href=\"$filename\">$filename</a></li>\n";
					}
					$fp = fopen($zipfilename, 'w');
					if(@fwrite($fp, $zip->file()) !== FALSE) {
						foreach($unlinks as $link) {
							@unlink($link);
						}
					} else {
						C::t('common_cache')->insert(array(
							'cachekey' => 'db_export',
							'cachevalue' => serialize(array('dateline' => $_G['timestamp'])),
							'dateline' => $_G['timestamp'],
						), false, true);
						$deletetips = $_G['config']['admincp']['dbimport'] ? cplang('db_delete_tips', array('filename' => basename($backupfilename), 'FORMHASH' => formhash())) : '';
						cpmsg('database_export_multivol_succeed', '', 'succeed', array('volume' => $volume, 'filelist' => $filelist, 'deletetips' => $deletetips));
					}
					unset($sqldump, $zip, $content);
					fclose($fp);
					@touch('./data/'.$backupdir.'/index.htm');
					$filename = $zipfilename;
					C::t('common_cache')->insert(array(
						'cachekey' => 'db_export',
						'cachevalue' => serialize(array('dateline' => $_G['timestamp'])),
						'dateline' => $_G['timestamp'],
					), false, true);
					$deletetips = $_G['config']['admincp']['dbimport'] ? cplang('db_delete_tips', array('filename' => basename($zipfilename), 'FORMHASH' => formhash())) : '';
					cpmsg('database_export_zip_succeed', '', 'succeed', array('filename' => $filename, 'deletetips' => $deletetips));
				} else {
					@touch('./data/'.$backupdir.'/index.htm');
					for($i = 1; $i <= $volume; $i++) {
						$filename = sprintf($_GET['usezip'] == 2 ? $backupfilename."-%s".'.zip' : $dumpfile, $i);
						$filelist .= "<li><a href=\"$filename\">$filename</a></li>\n";
					}
					C::t('common_cache')->insert(array(
						'cachekey' => 'db_export',
						'cachevalue' => serialize(array('dateline' => $_G['timestamp'])),
						'dateline' => $_G['timestamp'],
					), false, true);
					$deletetips = $_G['config']['admincp']['dbimport'] ? cplang('db_delete_tips', array('filename' => basename($_GET['usezip'] == 2 ? $backupfilename.'-1.zip' : $backupfilename), 'FORMHASH' => formhash())) : '';
					cpmsg('database_export_multivol_succeed', '', 'succeed', array('volume' => $volume, 'filelist' => $filelist, 'deletetips' => $deletetips));
				}
			}

		} else {

			$tablesstr = '';
			foreach($tables as $table) {
				$tablesstr .= ''.preg_replace("#[^\w]+#is", '', $table).' ';
			}
			$tablesstr = trim($tablesstr);

			require DISCUZ_ROOT . './config/config_global.php';
			$dbhost = $_config['db'][1]['dbhost'];
			$dbname = $_config['db'][1]['dbname'];
			$dbpw = $_config['db'][1]['dbpw'];
			$dbuser = $_config['db'][1]['dbuser'];
			list($dbhost, $dbport) = explode(':', $dbhost);

			$db = DB::object();
			$query = DB::query("SHOW VARIABLES LIKE 'basedir'");
			list(, $mysql_base) = DB::fetch($query, $db->drivertype == 'mysqli' ? MYSQLI_NUM : MYSQL_NUM);

			$dumpfile = addslashes(dirname(dirname(dirname(__FILE__)))).'/'.$backupfilename.'.sql';
			@unlink($dumpfile);
			$tablesstr = escapeshellarg($tablesstr);
			$tablesstr = str_replace(' ', '" "', $tablesstr);

			$mysqlbin = $mysql_base == '/' ? '' : addslashes(rtrim($mysql_base, '/\\')).'/bin/';
			@shell_exec($mysqlbin.'mysqldump --force --quick '.($db->version() > '4.1' ? '--skip-opt --create-options' : '-all').' --add-drop-table'.($_GET['extendins'] == 1 ? ' --extended-insert' : '').''.($db->version() > '4.1' && $_GET['sqlcompat'] == 'MYSQL40' ? ' --compatible=mysql40' : '').' --host="'.$dbhost.'"'.($dbport ? (is_numeric($dbport) ? ' --port='.$dbport : ' --socket="'.$dbport.'"') : '').' --user="'.$dbuser.'" --password="'.$dbpw.'" "'.$dbname.'" '.$tablesstr.' > '.$dumpfile);
			if(@file_exists($dumpfile)) {

				if($_GET['usezip']) {
					require_once libfile('class/zip');
					$zip = new zipfile();
					$zipfilename = $backupfilename.'.zip';
					$fp = fopen($dumpfile, "r");
					$content = @fread($fp, filesize($dumpfile));
					fclose($fp);
					$zip->addFile($idstring."# <?php exit();?>\n ".$setnames."\n #".$content, basename($dumpfile));
					$fp = fopen($zipfilename, 'w');
					@fwrite($fp, $zip->file());
					fclose($fp);
					@unlink($dumpfile);
					@touch('./data/'.$backupdir.'/index.htm');
					$filename = $backupfilename.'.zip';
					unset($sqldump, $zip, $content);
					C::t('common_cache')->insert(array(
						'cachekey' => 'db_export',
						'cachevalue' => serialize(array('dateline' => $_G['timestamp'])),
						'dateline' => $_G['timestamp'],
					), false, true);
					$deletetips = $_G['config']['admincp']['dbimport'] ? cplang('db_delete_tips', array('filename' => basename($zipfilename), 'FORMHASH' => formhash())) : '';
					cpmsg('database_export_zip_succeed', '', 'succeed', array('filename' => $filename, 'deletetips' => $deletetips));
				} else {
					if(@is_writeable($dumpfile)) {
						$fp = fopen($dumpfile, 'rb+');
						@fwrite($fp, $idstring."# <?php exit();?>\n ".$setnames."\n #");
						fclose($fp);
					}
					@touch('./data/'.$backupdir.'/index.htm');
					$filename = $backupfilename.'.sql';
					C::t('common_cache')->insert(array(
						'cachekey' => 'db_export',
						'cachevalue' => serialize(array('dateline' => $_G['timestamp'])),
						'dateline' => $_G['timestamp'],
					), false, true);
					$deletetips = $_G['config']['admincp']['dbimport'] ? cplang('db_delete_tips', array('filename' => basename($filename), 'FORMHASH' => formhash())) : '';
					cpmsg('database_export_succeed', '', 'succeed', array('filename' => $filename, 'deletetips' => $deletetips));
				}

			} else {

				cpmsg('database_shell_fail', '', 'error');

			}

		}
	}

} elseif($operation == 'import') {

	checkpermission('dbimport');

	if(!(submitcheck('deletesubmit', 1) && !empty($_GET['formhash']) && $_GET['formhash'] == formhash())) {

		$exportlog = $exportziplog = $exportsize = $exportzipsize = $exportfiletime = $exportzipfiletime = array();
		if(is_dir(DISCUZ_ROOT.'./data/'.$backupdir)) {
			$dir = dir(DISCUZ_ROOT.'./data/'.$backupdir);
			while($entry = $dir->read()) {
				$entry = DISCUZ_ROOT.'./data/'.$backupdir.'/'.$entry;
				if(is_file($entry)) {
					if(preg_match("/\.sql$/i", $entry)) {
						$filesize = filesize($entry);
						$filemtime = filemtime($entry);
						$fp = fopen($entry, 'rb');
						$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", fgets($fp, 256))));
						fclose($fp);
						$key = preg_replace('/^(.+?)(\-\d+)\.sql$/i', '\\1', basename($entry));
						$exportlog[$key][$identify[4]] = array(
							'version' => $identify[1],
							'type' => $identify[2],
							'method' => $identify[3],
							'volume' => $identify[4],
							'filename' => str_replace(DISCUZ_ROOT, '', $entry),
							'dateline' => $filemtime,
							'size' => $filesize
						);
						$exportsize[$key] += $filesize;
						$exportfiletime[$key] = $filemtime;
					} elseif(preg_match("/\.zip$/i", $entry)) {
						$key = preg_replace('/^(.+?)(\-\d+)\.zip$/i', '\\1', basename($entry));
						$filesize = filesize($entry);
						$filemtime = filemtime($entry);
						$exportziplog[$key][] = array(
							'type' => 'zip',
							'filename' => str_replace(DISCUZ_ROOT, '', $entry),
							'size' => $filesize,
							'dateline' => $filemtime
						);
						$exportzipsize[$key] += $filesize;
						$exportzipfiletime[$key] = $filemtime;
					}
				}
			}
			$dir->close();
			if (!empty($exportlog)) {
				array_multisort($exportfiletime, SORT_DESC, SORT_STRING, $exportlog);
			}
			if (!empty($exportziplog)) {
				array_multisort($exportzipfiletime, SORT_DESC, SORT_STRING, $exportziplog);
			}
		} else {
			cpmsg('database_export_dest_invalid', '', 'error');
		}

		$restore_url = $_G['siteurl'].'data/restore.php';

		shownav('founder', 'nav_db', 'nav_db_import');
		showsubmenu('nav_db', array(
			array('nav_db_export', 'db&operation=export', 0),
			array('nav_db_import', 'db&operation=import', 1),
			array('nav_db_runquery', 'db&operation=runquery', 0),
			array('nav_db_optimize', 'db&operation=optimize', 0),
			array('nav_db_dbcheck', 'db&operation=dbcheck', 0)
		));
		showtips('db_import_tips');
		showtableheader('db_import');
		showtablerow('', array('colspan="9" class="tipsblock"'), array(cplang('do_import_option', array('restore_url' => $restore_url))));

		showformheader('db&operation=import');
		showtitle('db_export_file');
		showsubtitle(array('', 'filename', 'version', 'time', 'type', 'size', 'db_method', 'db_volume', ''));

		$datasiteurl = $_G['siteurl'].'data/';

		foreach($exportlog as $key => $val) {
			$info = $val[1];
			$info['dateline'] = is_int($info['dateline']) ? dgmdate($info['dateline']) : $lang['unknown'];
			$info['size'] = sizecount($exportsize[$key]);
			$info['volume'] = count($val);
			$info['method'] = $info['method'] == 'multivol' ? $lang['db_multivol'] : $lang['db_shell'];
			$datafile_server = '.'.$info['filename'];
			showtablerow('', '', array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"".$key."\">",
				"<a href=\"javascript:;\" onclick=\"display('exportlog_$key')\">".basename($info['filename'])."</a>",
				$info['version'],
				$info['dateline'],
				$lang['db_export_'.$info['type']],
				$info['size'],
				$info['method'],
				"<a href=\"javascript:;\" onclick=\"display('exportlog_$key')\">".$info['volume']."</a>",
				"<a class=\"act\" href=\"".$datasiteurl."restore.php?operation=import&from=server&datafile_server=$datafile_server&importsubmit=yes\"".($info['version'] != $_G['setting']['version'] ? " onclick=\"return confirm('$lang[db_import_confirm]');\"" : " onclick=\"return confirm('$lang[db_import_confirm_sql]');\"")." class=\"act\" target=\"_blank\">$lang[import]</a>"
			));
			echo '<tbody id="exportlog_'.$key.'" style="display:none">';
			foreach($val as $info) {
				$info['dateline'] = is_int($info['dateline']) ? dgmdate($info['dateline']) : $lang['unknown'];
				$info['size'] = sizecount($info['size']);
				showtablerow('', '', array(
					'',
					"<a href=\"{$info['filename']}\">".substr(strrchr($info['filename'], "/"), 1)."</a>",
					$info['version'],
					$info['dateline'],
					'',
					$info['size'],
					'',
					'',
					''
				));
			}
			echo '</tbody>';
		}

		foreach($exportziplog as $key => $val) {
			sort($val);
			$info = $val[0];
			$info['volume'] = count($val);
			$info['dateline'] = is_int($info['dateline']) ? dgmdate($info['dateline']) : $lang['unknown'];
			$info['size'] = sizecount($exportzipsize[$key]);
			$info['method'] = $info['method'] == 'multivol' ? $lang['db_multivol'] : $lang['db_zip'];
			$datafile_server = '.'.$info['filename'];
			showtablerow('', '', array(
				"<input class=\"checkbox\" type=\"checkbox\" name=\"delete[]\" value=\"".basename($info['filename'])."\">",
				"<a href=\"javascript:;\" onclick=\"display('exportlog_zip_$key')\">".basename($info['filename'])."</a>",
				'',
				$info['dateline'],
				($info['volume'] > 1 ? $lang['db_multivol'] : '').$lang['db_export_'.$info['type']],
				$info['size'],
				$info['method'],
				"<a href=\"javascript:;\" onclick=\"display('exportlog_zip_$key')\">".$info['volume']."</a>",
				"<a href=\"".$datasiteurl."restore.php?operation=importzip&datafile_server=$datafile_server&importsubmit=yes\"  onclick=\"return confirm('$lang[db_import_confirm_zip]');\" class=\"act\" target=\"_blank\">$lang[db_import_unzip]</a>"
			));
			echo '<tbody id="exportlog_zip_'.$key.'" style="display:none">';
			foreach($val as $info) {
				$info['dateline'] = is_int($info['dateline']) ? dgmdate($info['dateline']) : $lang['unknown'];
				$info['size'] = sizecount($info['size']);
				showtablerow('', '', array(
					'',
					"<a href=\"{$info['filename']}\">".substr(strrchr($info['filename'], "/"), 1)."</a>",
					$info['version'],
					$info['dateline'],
					'',
					$info['size'],
					'',
					'',
					''
				));
			}
			echo '</tbody>';
		}

		showsubmit('deletesubmit', 'submit', 'del');
		showformfooter();

		showtablefooter();

	} else {
		if(is_array($_GET['delete'])) {
			foreach($_GET['delete'] as $filename) {
				$type = ".sql";
				if(strpos($filename, '-1.zip') !== FALSE) {
					$type = ".zip";
					$filename = str_replace('-1.zip', '', $filename);
				}
				$file_path = './data/'.$backupdir.'/'.str_replace(array('/', '\\'), '', $filename);
				if(is_file($file_path)) {
					@unlink($file_path);
				} else {
					$i = 1;
					while(1) {
						$file_path = './data/'.$backupdir.'/'.str_replace(array('/', '\\'), '', $filename.'-'.$i.$type);
						if(is_file($file_path)) {
							@unlink($file_path);
							$i++;
						} else {
							break;
						}
					}
				}
			}
			cpmsg('database_file_delete_succeed', 'action=db&operation=import', 'succeed');
		} else {
			cpmsg('database_file_delete_invalid', '', 'error');
		}
	}




} elseif($operation == 'runquery') {

	$checkperm = checkpermission('runquery', 0);

	$runquerys = array();
	include_once(DISCUZ_ROOT.'source/admincp/admincp_quickquery.php');

	if(!submitcheck('sqlsubmit')) {

		$runqueryselect = '';
		foreach($simplequeries as $key => $query) {
			if(empty($query['sql'])) {
				$runqueryselect .= "<optgroup label=\"$query[comment]\">";
			} else {
				$runqueryselect .= '<option value="'.$key.'">'.$query['comment'].'</option>';
			}
		}
		if($runqueryselect) {
			$runqueryselect = '<select name="queryselect" style="width:500px">'.$runqueryselect.'</select>';
		}
		$queryselect = intval($_GET['queryselect']);
		$queries = $queryselect ? $runquerys[$queryselect] : '';

		shownav('founder', 'nav_db', 'nav_db_runquery');
		showsubmenu('nav_db', array(
			array('nav_db_export', 'db&operation=export', 0),
			array('nav_db_import', 'db&operation=import', 0),
			array('nav_db_runquery', 'db&operation=runquery', 1),
			array('nav_db_optimize', 'db&operation=optimize', 0),
			array('nav_db_dbcheck', 'db&operation=dbcheck', 0)
		));
		showtips('db_runquery_tips');
		showtableheader();
		showformheader('db&operation=runquery&option=simple');
		showsetting('db_runquery_simply', '', '', $runqueryselect);
		showsetting('', '', '', '<input type="checkbox" class="checkbox" name="createcompatible" value="1" checked="checked" />'.cplang('db_runquery_createcompatible'));
		showsubmit('sqlsubmit');
		showformfooter();

		if($checkperm) {
			showformheader('db&operation=runquery&option=');
			showsetting('db_runquery_sql', '', '', '<textarea cols="85" rows="10" name="queries" style="width:500px;" onkeyup="textareasize(this)" onkeydown="textareakey(this, event)">'.$queries.'</textarea>');
			showsetting('', '', '', '<input type="checkbox" class="checkbox" name="createcompatible" value="1" checked="checked" />'.cplang('db_runquery_createcompatible'));
			showsubmit('sqlsubmit', 'submit', '', cplang('db_runquery_comment'));
			showformfooter();
		}

		showtablefooter();

	} else {
		$queries = $_GET['queries'];
		if($_GET['option'] == 'simple') {
			$queryselect = intval($_GET['queryselect']);
			$queries = isset($simplequeries[$queryselect]) && $simplequeries[$queryselect]['sql'] ? $simplequeries[$queryselect]['sql'] : '';
		} elseif(!$checkperm) {
			cpmsg('database_run_query_denied', '', 'error');
		}
		$sqlquery = str_replace(array(' cdb_', ' `cdb_', ' pre_', ' `pre_'), array(' {tablepre}', ' `{tablepre}', ' {tablepre}', ' `{tablepre}'), $queries);
		$sqlquery = splitsql(str_replace(array(' {tablepre}', ' `{tablepre}'), array(' '.$tablepre, ' `'.$tablepre), $sqlquery));
		$affected_rows = 0;
		foreach($sqlquery as $sql) {
			if(trim($sql) != '') {
				$sql = !empty($_GET['createcompatible']) ? syntablestruct(trim($sql), $db->version() > '4.1', $dbcharset) : $sql;

				DB::query($sql, 'SILENT');
				if($sqlerror = DB::error()) {
					break;
				} else {
					$affected_rows += intval(DB::affected_rows());
				}
			}
		}

		$sqlerror ? cpmsg('database_run_query_invalid', '', 'error', array('sqlerror' => $sqlerror)) : cpmsg('database_run_query_succeed', '', 'succeed', array('affected_rows' => $affected_rows));

	}

} elseif($operation == 'optimize') {

	$optimizetable = '';
	$totalsize = 0;
	$tablearray = array( 0 => $tablepre);

	shownav('founder', 'nav_db', 'nav_db_optimize');
	showsubmenu('nav_db', array(
		array('nav_db_export', 'db&operation=export', 0),
		array('nav_db_import', 'db&operation=import', 0),
		array('nav_db_runquery', 'db&operation=runquery', 0),
		array('nav_db_optimize', 'db&operation=optimize', 1),
		array('nav_db_dbcheck', 'db&operation=dbcheck', 0)
	));
	showtips('db_optimize_tips');
	showformheader('db&operation=optimize');
	showtableheader('db_optimize_tables');
	showsubtitle(array('', 'db_optimize_table_name', 'type', 'db_optimize_rows', 'db_optimize_data', 'db_optimize_index', 'db_optimize_frag'));

	if(!submitcheck('optimizesubmit')) {

		foreach($tablearray as $tp) {
			$query = DB::query("SHOW TABLE STATUS LIKE '$tp%'", 'SILENT');
			while($table = DB::fetch($query)) {
				if($table['Data_free'] && $table[$tabletype] == 'MyISAM') {
					$checked = $table[$tabletype] == 'MyISAM' ? 'checked' : 'disabled';
					showtablerow('', '', array(
						"<input class=\"checkbox\" type=\"checkbox\" name=\"optimizetables[]\" value=\"$table[Name]\" $checked>",
						$table[Name],
						$table[$tabletype],
						$table[Rows],
						$table[Data_length],
						$table[Index_length],
						$table[Data_free],
					));
					$totalsize += $table['Data_length'] + $table['Index_length'];
				}
			}
		}
		if(empty($totalsize)) {
			showtablerow('', 'colspan="6"', $lang['db_optimize_done']);
		} else {
			showtablerow('', 'colspan="6"', $lang['db_optimize_used'].' '.sizecount($totalsize));
			showsubmit('optimizesubmit', 'submit', '<input name="chkall" id="chkall" class="checkbox" onclick="checkAll(\'prefix\', this.form)" checked="checked" type="checkbox" /><label for="chkall">'.$lang[db_optimize_opt].'</label>');
		}

	} else {


		foreach($tablearray as $tp) {
			$query = DB::query("SHOW TABLE STATUS LIKE '$tp%'", 'SILENT');
			while($table = DB::fetch($query)) {
				if($table['Data_free'] && $table[$tabletype] == 'MyISAM') {
					$optimizeinput = "<input class=\"checkbox\" type=\"checkbox\" name=\"optimizetables[]\" value=\"$table[Name]\" $checked>";
					if(is_array($_GET['optimizetables']) && in_array($table['Name'], $_GET['optimizetables'])) {
						DB::query("OPTIMIZE TABLE $table[Name]");
						$table[Data_free] = 0;
						$optimizeinput = '';
					}
					showtablerow('', '', array(
						$optimizeinput,
						$table[Name],
						$db->version() > '4.1' ?  $table['Engine'] : $table['Type'],
						$table[Rows],
						$table[Data_length],
						$table[Index_length],
						$table[Data_free]
					));
					$totalsize += $table['Data_length'] + $table['Index_length'];
				}
			}
		}
		showtablerow('', 'colspan="6"', $lang['db_optimize_used'].' '.sizecount($totalsize));
	}

	showtablefooter();
	showformfooter();

} elseif($operation == 'dbcheck') {

	if(!C::t('common_setting')->fetch_all_field()) {
		cpmsg('dbcheck_permissions_invalid', '', 'error');
	}
	$installSqlPath = DISCUZ_ROOT.'./install/install.sql';
	if(!file_exists($installSqlPath))
		$installSqlPath = DISCUZ_ROOT.'./install/data/install.sql';
	$step = max(1, intval($_GET['step']));
	if($step == 3) {

		if(!file_exists('source/admincp/discuzdb.md5')) {
			cpmsg('dbcheck_nofound_md5file', '', 'error');
		}

		$dbcharset = $discuz->config['db'][1]['dbcharset'];
		unset($dbuser, $dbpw, $dbname);

		$fp = fopen(DISCUZ_ROOT.'./source/admincp/discuzdb.md5', "rb");
		$discuzdb = fread($fp, filesize(DISCUZ_ROOT.'./source/admincp/discuzdb.md5'));
		fclose($fp);
		$dbmd5 = substr($discuzdb, 0, 32);
		$discuzdb = dunserialize(substr($discuzdb, 34));
		$settingsdata = $discuzdb[1];
		$discuzdb = $discuzdb[0][0];
		$repair = !empty($_GET['repair']) ? $_GET['repair'] : array();
		$setting = !empty($_GET['setting']) ? $_GET['setting'] : array();
		$missingtable = !empty($_GET['missingtable']) ? $_GET['missingtable'] : array();
		$repairtable = is_array($_GET['repairtable']) && !empty($_GET['repairtable']) ? $_GET['repairtable'] : array();

		$except = array('threads' => array('sgid'));
		foreach(C::t('common_member_profile_setting')->range() as $profilefields) {
			$except['memberfields'][] = 'field_'.$profilefields['fieldid'];
		}

		if(submitcheck('repairsubmit') && (!empty($repair) || !empty($setting) || !empty($repairtable) || !empty($missingtable))) {
			$error = '';
			$errorcount = 0;
			$alter = $fielddefault = array();

			foreach($missingtable as $value) {
				if(!isset($installdata)) {
					$fp = fopen($installSqlPath, "rb");
					$installdata = fread($fp, filesize($installSqlPath));
					fclose($fp);
				}
				preg_match("/CREATE TABLE ".DB::table($value)."\s+\(.+?;/is", $installdata, $a);
				DB::query(createtable($a[0], $dbcharset));
			}

			foreach($repair as $value) {
				if(!in_array($r_table, $repairtable)) {
					list($r_table, $r_field, $option) = explode('|', $value);
					if(!isset($repairrtable[$r_table]) && $fieldsquery = DB::query("SHOW FIELDS FROM ".DB::table($r_table), 'SILENT')) {
						while($fields = DB::fetch($fieldsquery)) {
							$fielddefault[$r_table][$fields['Field']] = $fields['Default'];
						}
					}

					$field = $discuzdb[$r_table][$r_field];
					$altersql = '`'.$field['Field'].'` '.$field['Type'];
					$altersql .= $field['Null'] == 'NO' ? ' NOT NULL' : '';
					$altersql .= in_array($fielddefault[$r_table][$field['Field']], array('', '0')) && in_array($field['Default'], array('', '0')) ||
						$field['Null'] == 'NO' && $field['Default'] == '' ||
						preg_match('/text/i', $field['Type']) || preg_match('/auto_increment/i', $field['Extra']) ?
						'' : ' default \''.$field['Default'].'\'';
					$altersql .= $field['Extra'] != '' ? ' '.$field['Extra'] : '';
					$altersql = $option == 'modify' ? "MODIFY COLUMN ".$altersql : "ADD COLUMN ".$altersql;
					$alter[$r_table][] = $altersql;
				}
			}

			foreach($alter as $r_table => $sqls) {
				DB::query("ALTER TABLE `$tablepre$r_table` ".implode(',', $sqls), 'SILENT');
				if($sqlerror = DB::error()) {
					$errorcount += count($sqls);
					$error .= $sqlerror.'<br /><br />';
				}
			}
			$alter = array();

			foreach($repairtable as $value) {
				foreach($discuzdb[$value] as $field) {
					if(!isset($fielddefault[$value]) && $fieldsquery = DB::query("SHOW FIELDS FROM ".DB::table($value), 'SILENT')) {
						while($fields = DB::fetch($fieldsquery)) {
							$fielddefault[$value][$fields['Field']] = $fields['Default'];
						}
					}
					$altersql = '`'.$field['Field'].'` '.$field['Type'];
					$altersql .= $field['Null'] == 'NO' ? ' NOT NULL' : '';
					$altersql .= in_array($fielddefault[$value][$field['Field']], array('', '0')) && in_array($field['Default'], array('', '0')) ||
						$field['Null'] == 'NO' && $field['Default'] == '' ||
						preg_match('/text/i', $field['Type']) || preg_match('/auto_increment/i', $field['Extra']) ?
						'' : ' default \''.$field['Default'].'\'';
					$altersql .= $field['Extra'] != '' ? ' '.$field['Extra'] : '';
					$altersql = "MODIFY COLUMN ".$altersql;
					$alter[$value][] = $altersql;
				}
			}

			foreach($alter as $r_table => $sqls) {
				DB::query("ALTER TABLE `".DB::table($r_table)."` ".implode(',', $sqls), 'SILENT');
				if($sqlerror = DB::error()) {
					$errorcount += count($sqls);
					$error .= $sqlerror.'<br /><br />';
				}
			}

			if(!empty($setting)) {
				$settingsdatanow = $newsettings = array();
				$allsetting = C::t('common_setting')->fetch_all();
				$settingsdatanew = array_keys($allsetting);
				unset($allsetting);
				$settingsdellist = @array_diff($settingsdata, $settingsdatanew);
				if($setting['del'] && is_array($settingsdellist)) {
					foreach($settingsdellist as $variable) {
						$newsettings[$variable] = '';
					}
				}
				if($newsettings) {
					C::t('common_setting')->update_batch($newsettings);
					updatecache('setting');
				}
			}

			if($errorcount) {
				cpmsg('dbcheck_repair_error', '', 'error', array('errorcount' => $errorcount, 'error' => $error));
			} else {
				cpmsg('dbcheck_repair_completed', 'action=db&operation=dbcheck&step=3', 'succeed');
			}
		}

		$installexists = file_exists($installSqlPath);
		$discuzdbnew = $deltables = $excepttables = $missingtables = $charseterror = array();
		foreach($discuzdb as $dbtable => $fields) {
			if($fieldsquery = DB::query("SHOW FIELDS FROM ".DB::table($dbtable), 'SILENT')) {
				while($fields = DB::fetch($fieldsquery)) {
					$r = '/^'.$tablepre.'/';
					$cuttable = preg_replace($r, '', $dbtable);
					if($db->version() < '4.1' && $cuttable == 'sessions' && $fields['Field'] == 'sid') {
						$fields['Type'] = str_replace(' binary', '', $fields['Type']);
					}
					if($cuttable == 'memberfields' && preg_match('/^field\_\d+$/', $fields['Field'])) {
						unset($discuzdbnew[$cuttable][$fields['Field']]);
						continue;
					}
					$discuzdbnew[$cuttable][$fields['Field']]['Field'] = $fields['Field'];
					$discuzdbnew[$cuttable][$fields['Field']]['Type'] = $fields['Type'];
					$discuzdbnew[$cuttable][$fields['Field']]['Null'] = $fields['Null'] == '' ? 'NO' : $fields['Null'];
					$discuzdbnew[$cuttable][$fields['Field']]['Extra'] = $fields['Extra'];
					$discuzdbnew[$cuttable][$fields['Field']]['Default'] = $fields['Default'] == '' || $fields['Default'] == '0' ? '' : $fields['Default'];
				}
				ksort($discuzdbnew[$cuttable]);
			} else {
				$missingtables[] = '<span style="float:left;width:33%">'.(($installexists ? '<input name="missingtable[]" type="checkbox" class="checkbox" value="'.$dbtable.'">' : '').$tablepre.$dbtable).'</span>';
				$excepttables[] = $dbtable;
			}
		}

		if($db->version() > '4.1') {
			$dbcharset = strtoupper($dbcharset) == 'UTF-8' ? 'UTF8' : strtoupper($dbcharset);
			$query = DB::query("SHOW TABLE STATUS LIKE '$tablepre%'");
			while($tables = DB::fetch($query)) {
				$r = '/^'.$tablepre.'/';
				$cuttable = preg_replace($r, '', $tables['Name']);
				$tabledbcharset = substr($tables['Collation'], 0, strpos($tables['Collation'], '_'));
				if($dbcharset != strtoupper($tabledbcharset)) {
					$charseterror[] = '<span style="float:left;width:33%">'.$tablepre.$cuttable.'('.$tabledbcharset.')</span>';
				}
			}
		}

		$dbmd5new = md5(serialize($discuzdbnew));

		$settingsdatanow = array();
		$allsetting = C::t('common_setting')->fetch_all();
		$settingsdatanew = array_keys($allsetting);
		unset($allsetting);
		$settingsdellist = @array_diff($settingsdata, $settingsdatanew);

		if($dbmd5 == $dbmd5new && empty($charseterror) && empty($settingsdellist)) {
			cpmsg('dbcheck_ok', '', 'succeed');
		}

		$showlist = $addlists = '';
		foreach($discuzdb as $dbtable => $fields) {
			$addlist = $modifylist = $dellist = array();
			if($fields != $discuzdbnew[$dbtable]) {
				foreach($discuzdb[$dbtable] as $key => $value) {
					$tempvalue = str_replace('mediumtext', 'text', $value);
					$discuzdbnew[$dbtable][$key] = str_replace('mediumtext', 'text', $discuzdbnew[$dbtable][$key]);
					if(is_array($missingtables) && in_array($tablepre.$dbtable, $missingtables)) {
					} elseif(!isset($discuzdbnew[$dbtable][$key])) {
						$dellist[] = $value;
					} elseif($tempvalue != $discuzdbnew[$dbtable][$key]) {
						if((strpos($tempvalue['Type'], 'int(') !== false) && (strpos($discuzdbnew[$dbtable][$key]['Type'], '(') === false)) {
							$tempvalue['Type'] = preg_replace('/\(\d+\)/', '', $tempvalue['Type']);
							if($tempvalue != $discuzdbnew[$dbtable][$key]) {
								$modifylist[] = $value;
							}
						} else {
							$modifylist[] = $value;
						}
					}
				}
				if(is_array($discuzdbnew[$dbtable])) {
					foreach($discuzdbnew[$dbtable] as $key => $value) {
						if(!isset($discuzdb[$dbtable][$key]) && !@in_array($value['Field'], $except[$dbtable])) {
							$addlist[] = $value;
						}
					}
				}
			}

			if(($modifylist || $dellist) && !in_array($dbtable, $excepttables)) {

				$showlist .= showtablerow('', '', array("<span class=\"diffcolor3\">$tablepre$dbtable</span> $lang[dbcheck_field]", $lang[dbcheck_org_field], $lang[dbcheck_status]), TRUE);

				foreach($modifylist as $value) {
					$slowstatus = slowcheck($discuzdbnew[$dbtable][$value['Field']]['Type'], $value['Type']);

					$showlist .= "<tr><td><input name=\"repair[]\" class=\"checkbox\" type=\"checkbox\" value=\"$dbtable|$value[Field]|modify\"> <b>".$value['Field']."</b> ".
						$discuzdbnew[$dbtable][$value['Field']]['Type'].
						($discuzdbnew[$dbtable][$value['Field']]['Null'] == 'NO' ? ' NOT NULL' : '').
						(!preg_match('/auto_increment/i', $discuzdbnew[$dbtable][$value['Field']]['Extra']) && !preg_match('/text/i', $discuzdbnew[$dbtable][$value['Field']]['Type']) ? ' default \''.$discuzdbnew[$dbtable][$value['Field']]['Default'].'\'' : '').
						' '.$discuzdbnew[$dbtable][$value['Field']]['Extra'].
						"</td><td><b>".$value['Field']."</b> ".$value['Type'].
						($value['Null'] == 'NO' ? ' NOT NULL' : '').
						(!preg_match('/auto_increment/i', $value['Extra']) && !preg_match('/text/i', $value['Type']) ? ' default \''.$value['Default'].'\'' : '').
						' '.$value['Extra']."</td><td>".
						(!$slowstatus ? "<em class=\"edited\">$lang[dbcheck_modify]</em></td></tr>" : "<em class=\"unknown\">$lang[dbcheck_slow]</em>")."</td></tr>";
				}

				if($modifylist) {
					$showlist .= showtablerow('', 'colspan="3"', "<input onclick=\"setrepaircheck(this, this.form, '$dbtable')\" name=\"repairtable[]\" class=\"checkbox\" type=\"checkbox\" value=\"$dbtable\"> <b>$lang[dbcheck_repairtable]</b>", TRUE);
				}

				foreach($dellist as $value) {
					$showlist .= "<tr><td><input name=\"repair[]\" class=\"checkbox\" type=\"checkbox\" value=\"$dbtable|$value[Field]|add\"> <strike><b>".$value['Field']."</b></strike></td><td> <b>".$value['Field']."</b> ".$value['Type'].($value['Null'] == 'NO' ? ' NOT NULL' : '')."</td><td>".
						"<em class=\"del\">$lang[dbcheck_delete]</em></td></tr>";
				}
			}

			if($addlist) {
				$addlists .= "<tr><td colspan=\"3\"><b>$tablepre$dbtable</b> $lang[dbcheck_new_field]</td></tr>";

				foreach($addlist as $value) {
					$addlists .= "<tr><td colspan=\"3\">&nbsp;&nbsp;&nbsp;&nbsp;<b>".$value['Field']."</b> ".$discuzdbnew[$dbtable][$value['Field']]['Type'].($discuzdbnew[$dbtable][$value['Field']]['Null'] == 'NO' ? ' NOT NULL' : '')."</td></tr>";
				}
			}

		}

		if($showlist) {
			$showlist = showtablerow('', 'colspan="3" class="partition"', $lang['dbcheck_errorfields_tables'], TRUE).$showlist;
		}

		if($missingtables) {
			$showlist .= showtablerow('', 'colspan="3" class="partition"', $lang['dbcheck_missing_tables'], TRUE);
			$showlist .= showtablerow('', 'colspan="3" class="partition"', implode('', $missingtables), TRUE);
		}

		if($settingsdellist) {
			$showlist .= "<tr class=\"partition\"><td colspan=\"3\">$lang[dbcheck_setting]</td></tr>";
			$showlist .= '<tr><td colspan="3">';
			$showlist .= "<input name=\"setting[del]\" class=\"checkbox\" type=\"checkbox\" value=\"1\"> ".implode(', ', $settingsdellist).'<br />';
			$showlist .= '</td></tr>';
		}

		if($showlist) {
			$showlist .= '<tr><td colspan="3"><input class="btn" type="submit" value="'.$lang['dbcheck_repair'].'" name="repairsubmit"></td></tr>';
		}

		if($charseterror) {
			$showlist .= "<tr><td class=\"partition\" colspan=\"3\">$lang[dbcheck_charseterror_tables] ($lang[dbcheck_charseterror_notice] $dbcharset)</td></tr>";
			$showlist .= '<tr><td colspan="3">'.implode('', $charseterror).'</td></tr>';
		}

		if($addlists) {
			$showlist .= '<tr><td class="partition" colspan="3">'.$lang['dbcheck_userfields'].'</td></tr>'.$addlists;
		}

	}

	shownav('founder', 'nav_db', 'nav_db_dbcheck');
	showsubmenu('nav_db', array(
		array('nav_db_export', 'db&operation=export', 0),
		array('nav_db_import', 'db&operation=import', 0),
		array('nav_db_runquery', 'db&operation=runquery', 0),
		array('nav_db_optimize', 'db&operation=optimize', 0),
		array('nav_db_dbcheck', 'db&operation=dbcheck', 1)
	));
	showsubmenusteps('', array(
		array('nav_filecheck_confirm', $step == 1),
		array('nav_filecheck_verify', $step == 2),
		array('nav_filecheck_completed', $step == 3)
	));

	if($step == 1) {
		cpmsg(cplang('dbcheck_tips_step1'), 'action=db&operation=dbcheck&step=2', 'button', '', FALSE);
	} elseif($step == 2) {
		cpmsg(cplang('dbcheck_verifying'), "action=db&operation=dbcheck&step=3", 'loading', '', FALSE);
	} elseif($step == 3) {
		showtips('dbcheck_tips');
		echo <<<EOT
<script type="text/JavaScript">
	function setrepaircheck(obj, form, table) {
		eval('var rem = /^' + table + '\\\\|.+?\\\\|modify$/;');
		eval('var rea = /^' + table + '\\\\|.+?\\\\|add$/;');
		for(var i = 0; i < form.elements.length; i++) {
			var e = form.elements[i];
			if(e.type == 'checkbox' && e.name == 'repair[]') {
				if(rem.exec(e.value) != null) {
					if(obj.checked) {
						e.checked = false;
						e.disabled = true;
					} else {
						e.checked = false;
						e.disabled = false;

					}
				}
				if(rea.exec(e.value) != null) {
					if(obj.checked) {
						e.checked = true;
						e.disabled = false;
					} else {
						e.checked = false;
						e.disabled = false;
					}
				}
			}
		}
	}
</script>
EOT;
		showformheader('db&operation=dbcheck&step=3', 'fixpadding');
		showtableheader();
		echo $showlist;
		showtablefooter();
		showformfooter();

	}

}

function createtable($sql, $dbcharset) {
	$type = strtoupper(preg_replace("/^\s*CREATE TABLE\s+.+\s+\(.+?\).*(ENGINE|TYPE)\s*=\s*([a-z]+?).*$/isU", "\\2", $sql));
	$type = in_array($type, array('MYISAM', 'HEAP', 'MEMORY')) ? $type : 'MYISAM';
	return preg_replace("/^\s*(CREATE TABLE\s+.+\s+\(.+?\)).*$/isU", "\\1", $sql).
		(DB::$db->version() > '4.1' ? " ENGINE=$type DEFAULT CHARSET=$dbcharset" : " TYPE=$type");
}

function fetchtablelist($tablepre = '') {
	global $db;
	$arr = explode('.', $tablepre);
	$dbname = $arr[1] ? $arr[0] : '';
	$tablepre = str_replace('_', '\_', $tablepre);
	$sqladd = $dbname ? " FROM $dbname LIKE '$arr[1]%'" : "LIKE '$tablepre%'";
	$tables = $table = array();
	$query = DB::query("SHOW TABLE STATUS $sqladd");
	while($table = DB::fetch($query)) {
		$table['Name'] = ($dbname ? "$dbname." : '').$table['Name'];
		$tables[] = $table;
	}
	return $tables;
}

function arraykeys2($array, $key2) {
	$return = array();
	foreach($array as $val) {
		$return[] = $val[$key2];
	}
	return $return;
}


function syntablestruct($sql, $version, $dbcharset) {

	if(strpos(trim(substr($sql, 0, 18)), 'CREATE TABLE') === FALSE) {
		return $sql;
	}

	$sqlversion = strpos($sql, 'ENGINE=') === FALSE ? FALSE : TRUE;

	if($sqlversion === $version) {

		return $sqlversion && $dbcharset ? preg_replace(array('/ character set \w+/i', '/ collate \w+/i', "/DEFAULT CHARSET=\w+/is"), array('', '', "DEFAULT CHARSET=$dbcharset"), $sql) : $sql;
	}

	if($version) {
		return preg_replace(array('/TYPE=HEAP/i', '/TYPE=(\w+)/is'), array("ENGINE=MEMORY DEFAULT CHARSET=$dbcharset", "ENGINE=\\1 DEFAULT CHARSET=$dbcharset"), $sql);

	} else {
		return preg_replace(array('/character set \w+/i', '/collate \w+/i', '/ENGINE=MEMORY/i', '/\s*DEFAULT CHARSET=\w+/is', '/\s*COLLATE=\w+/is', '/ENGINE=(\w+)(.*)/is'), array('', '', 'ENGINE=HEAP', '', '', 'TYPE=\\1\\2'), $sql);
	}
}

function sqldumptablestruct($table) {
	global $_G, $db, $excepttables;

	if(in_array($table, $excepttables)) {
		return;
	}

	$createtable = DB::query("SHOW CREATE TABLE $table", 'SILENT');

	if(!DB::error()) {
		$tabledump = "DROP TABLE IF EXISTS $table;\n";
	} else {
		return '';
	}

	$create = $db->fetch_row($createtable);

	if(strpos($table, '.') !== FALSE) {
		$tablename = substr($table, strpos($table, '.') + 1);
		$create[1] = str_replace("CREATE TABLE $tablename", 'CREATE TABLE '.$table, $create[1]);
	}
	$tabledump .= $create[1];

	if($_GET['sqlcompat'] == 'MYSQL41' && $db->version() < '4.1') {
		$tabledump = preg_replace("/TYPE\=(.+)/", "ENGINE=\\1 DEFAULT CHARSET=".$dumpcharset, $tabledump);
	}
	if($db->version() > '4.1' && $_GET['sqlcharset']) {
		$tabledump = preg_replace("/(DEFAULT)*\s*CHARSET=.+/", "DEFAULT CHARSET=".$_GET['sqlcharset'], $tabledump);
	}

	$tablestatus = DB::fetch_first("SHOW TABLE STATUS LIKE '$table'");
	$tabledump .= ($tablestatus['Auto_increment'] ? " AUTO_INCREMENT=$tablestatus[Auto_increment]" : '').";\n\n";
	if($_GET['sqlcompat'] == 'MYSQL40' && $db->version() >= '4.1' && $db->version() < '5.1') {
		if($tablestatus['Auto_increment'] <> '') {
			$temppos = strpos($tabledump, ',');
			$tabledump = substr($tabledump, 0, $temppos).' auto_increment'.substr($tabledump, $temppos);
		}
		if($tablestatus['Engine'] == 'MEMORY') {
			$tabledump = str_replace('TYPE=MEMORY', 'TYPE=HEAP', $tabledump);
		}
	}
	return $tabledump;
}

function sqldumptable($table, $startfrom = 0, $currsize = 0) {
	global $_G, $db, $startrow, $dumpcharset, $complete, $excepttables;

	$offset = 300;
	$tabledump = '';
	$tablefields = array();

	$query = DB::query("SHOW FULL COLUMNS FROM $table", 'SILENT');
	if(strexists($table, 'adminsessions')) {
		return ;
	} elseif(!$query && DB::errno() == 1146) {
		return;
	} elseif(!$query) {
		$_GET['usehex'] = FALSE;
	} else {
		while($fieldrow = DB::fetch($query)) {
			$tablefields[] = $fieldrow;
		}
	}

	if(!in_array($table, $excepttables)) {
		$tabledumped = 0;
		$numrows = $offset;
		$firstfield = $tablefields[0];

		if($_GET['extendins'] == '0') {
			while($currsize + strlen($tabledump) + 500 < $_GET['sizelimit'] * 1000 && $numrows == $offset) {
				if($firstfield['Extra'] == 'auto_increment') {
					$selectsql = "SELECT * FROM $table WHERE $firstfield[Field] > $startfrom ORDER BY $firstfield[Field] LIMIT $offset";
				} else {
					$selectsql = "SELECT * FROM $table LIMIT $startfrom, $offset";
				}
				$tabledumped = 1;
				$rows = DB::query($selectsql);
				$numfields = $db->num_fields($rows);

				$numrows = DB::num_rows($rows);
				while($row = $db->fetch_row($rows)) {
					$comma = $t = '';
					for($i = 0; $i < $numfields; $i++) {
						$t .= $comma.($_GET['usehex'] && !empty($row[$i]) && (strexists($tablefields[$i]['Type'], 'char') || strexists($tablefields[$i]['Type'], 'text')) ? '0x'.bin2hex($row[$i]) : '\''.$db->escape_string($row[$i]).'\'');
						$comma = ',';
					}
					if(strlen($t) + $currsize + strlen($tabledump) + 500 < $_GET['sizelimit'] * 1000) {
						if($firstfield['Extra'] == 'auto_increment') {
							$startfrom = $row[0];
						} else {
							$startfrom++;
						}
						$tabledump .= "INSERT INTO $table VALUES ($t);\n";
					} else {
						$complete = FALSE;
						break 2;
					}
				}
			}
		} else {
			while($currsize + strlen($tabledump) + 500 < $_GET['sizelimit'] * 1000 && $numrows == $offset) {
				if($firstfield['Extra'] == 'auto_increment') {
					$selectsql = "SELECT * FROM $table WHERE $firstfield[Field] > $startfrom LIMIT $offset";
				} else {
					$selectsql = "SELECT * FROM $table LIMIT $startfrom, $offset";
				}
				$tabledumped = 1;
				$rows = DB::query($selectsql);
				$numfields = $db->num_fields($rows);

				if($numrows = DB::num_rows($rows)) {
					$t1 = $comma1 = '';
					while($row = $db->fetch_row($rows)) {
						$t2 = $comma2 = '';
						for($i = 0; $i < $numfields; $i++) {
							$t2 .= $comma2.($_GET['usehex'] && !empty($row[$i]) && (strexists($tablefields[$i]['Type'], 'char') || strexists($tablefields[$i]['Type'], 'text'))? '0x'.bin2hex($row[$i]) : '\''.$db->escape_string($row[$i]).'\'');
							$comma2 = ',';
						}
						if(strlen($t1) + $currsize + strlen($tabledump) + 500 < $_GET['sizelimit'] * 1000) {
							if($firstfield['Extra'] == 'auto_increment') {
								$startfrom = $row[0];
							} else {
								$startfrom++;
							}
							$t1 .= "$comma1 ($t2)";
							$comma1 = ',';
						} else {
							$tabledump .= "INSERT INTO $table VALUES $t1;\n";
							$complete = FALSE;
							break 2;
						}
					}
					$tabledump .= "INSERT INTO $table VALUES $t1;\n";
				}
			}
		}

		$startrow = $startfrom;
		$tabledump .= "\n";
	}

	return $tabledump;
}

function splitsql($sql) {
	$sql = str_replace("\r", "\n", $sql);
	$ret = array();
	$num = 0;
	$queriesarray = explode(";\n", trim($sql));
	unset($sql);
	foreach($queriesarray as $query) {
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= $query[0] == "#" ? NULL : $query;
		}
		$num++;
	}
	return($ret);
}

function slowcheck($type1, $type2) {
	$t1 = explode(' ', $type1);$t1 = $t1[0];
	$t2 = explode(' ', $type2);$t2 = $t2[0];
	$arr = array($t1, $t2);
	sort($arr);
	if($arr == array('mediumtext', 'text')) {
		return TRUE;
	} elseif(substr($arr[0], 0, 4) == 'char' && substr($arr[1], 0, 7) == 'varchar') {
		return TRUE;
	}
	return FALSE;
}

?>