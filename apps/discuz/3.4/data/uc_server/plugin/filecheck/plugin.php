<?php


!defined('IN_UC') && exit('Access Denied');

class control extends pluginbase {

	var $md5data = array();

	function control() {
		$this->pluginbase();
	}

	function onindex() {

		if(!$ucfiles = @file(UC_ROOT.'./control/admin/ucfiles.md5')) {
			$this->message('file_check_failed');
		}

		$this->load('app');
		$applist = $_ENV['app']->get_apps();
		$this->view->assign('applist', $applist);

		$this->checkfiles('./', '', 0);
		$this->checkfiles('data/', '\.htm');
		$this->checkfiles('api/', '\.php|\.htm');
		$this->checkfiles('control/', '\.php|\.htm|\.md5', 1, 'ucfiles.md5');
		$this->checkfiles('model/', '\.php|\.htm');
		$this->checkfiles('lib/', '\.php|\.htm');
		$this->checkfiles('plugin/', '\.php|\.htm|\.xml');
		$this->checkfiles('upgrade/', '\.php');
		$this->checkfiles('images/', '\..+?');
		$this->checkfiles('js/', '\.js|\.htm');
		$this->checkfiles('release/', '\.php');
		$this->checkfiles('view/', '\.php|\.htm');

		foreach($ucfiles as $line) {
			$file = trim(substr($line, 34));
			$md5datanew[$file] = substr($line, 0, 32);
			if($md5datanew[$file] != $this->md5data[$file]) {
				$modifylist[$file] = $this->md5data[$file];
			}
			$md5datanew[$file] = $this->md5data[$file];
		}

		$weekbefore = $timestamp - 604800;
		$addlist = @array_diff_assoc($this->md5data, $md5datanew);
		$dellist = @array_diff_assoc($md5datanew, $this->md5data);
		$modifylist = @array_diff_assoc($modifylist, $dellist);
		$showlist = @array_merge($this->md5data, $md5datanew);
		$doubt = 0;
		$dirlist = array('modify' => array(), 'del' => array(), 'add' => array(), 'doubt' => array());
		foreach($showlist as $file => $md5) {
			$dir = dirname($file);
			if(@array_key_exists($file, $modifylist)) {
				$fileststus = 'modify';
			} elseif(@array_key_exists($file, $dellist)) {
				$fileststus = 'del';
			} elseif(@array_key_exists($file, $addlist)) {
				$fileststus = 'add';
			} else {
				$filemtime = @filemtime($file);
				if($filemtime > $weekbefore) {
					$fileststus = 'doubt';
					$doubt++;
				} else {
					$fileststus = '';
				}
			}
			if(file_exists($file)) {
				$filemtime = @filemtime($file);
				$fileststus && $dirlist[$fileststus][$dir][basename($file)] = array(number_format(filesize($file)).' Bytes', $this->date($filemtime));
			} else {
				$fileststus && $dirlist[$fileststus][$dir][basename($file)] = array('', '');
			}
		}

		$result = $resultjs = '';
		$dirnum = 0;
		foreach($dirlist as $status => $filelist) {
			$dirnum++;
			$result .= '<div id="status_'.$status.'" style="display:'.($status != 'modify' ? 'none' : '').'">';
			foreach($filelist as $dir => $files) {
				$result .= '<br /><br /><u><b><a>'.$dir.'</a></b></u><br />';
				foreach($files as $filename => $file) {
					$result .= '<div style="clear:both"><b style="float:left;width: 30%">'.$filename.'</b><div style="float:left;width: 20%">'.$file[0].'</div><div style="float:left;width: 20%">'.$file[1].'</div></div>';
				}
			}
			$result .= '<br /><br /></div>';
			$resultjs .= '$(\'status_'.$status.'\').style.display=\'none\';';
		}
		$modifiedfiles = count($modifylist);
		$deletedfiles = count($dellist);
		$unknownfiles = count($addlist);

		$result .= '<script>function showresult(o) {'.$resultjs.'$(\'status_\' + o).style.display=\'\';}</script>';
		$this->view->assign('result', $result);
		$this->view->assign('modifiedfiles', $modifiedfiles);
		$this->view->assign('deletedfiles', $deletedfiles);
		$this->view->assign('unknownfiles', $unknownfiles);
		$this->view->assign('doubt', $doubt);
		$this->view->display('plugin_filecheck');
	}

	function checkfiles($currentdir, $ext = '', $sub = 1, $skip = '') {
		$dir = @opendir(UC_ROOT.$currentdir);
		$exts = '/('.$ext.')$/i';
		$skips = explode(',', $skip);

		while($entry = @readdir($dir)) {
			$file = $currentdir.$entry;
			if($entry != '.' && $entry != '..' && (($ext && preg_match($exts, $entry) || !$ext) || $sub && is_dir($file)) && !in_array($entry, $skips)) {
				if($sub && is_dir($file)) {
					$this->checkfiles($file.'/', $ext, $sub, $skip);
				} else {
					if(is_dir($file)) {
						$this->md5data[$file] = md5($file);
					} else {
						$this->md5data[$file] = md5_file($file);
					}
				}
			}
		}
	}
}