<?php

/*
	[UCenter] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: upload.class.php 1059 2011-03-01 07:25:09Z monkey $
*/

Class upload{

	var $dir;
	var $thumb_width;
	var $thumb_height;
	var $thumb_ext;
	var $watermark_file;
	var $watermark_pos;
	var $watermark_alpha;
	var $time;

	var $filetypedata = array();
	var $filetypeids = array();
	var $filetypes = array();

	function upload($time = 0) {
		$this->time = $time ? $time : time();
		$this->filetypedata = array(
			'av' => array('av', 'wmv', 'wav'),
			'real' => array('rm', 'rmvb'),
			'binary' => array('dat'),
			'flash' => array('swf'),
			'html' => array('html', 'htm'),
			'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
			'office' => array('doc', 'xls', 'ppt'),
			'pdf' => array('pdf'),
			'rar' => array('rar', 'zip'),
			'text' => array('txt'),
			'bt' => array('bt'),
			'zip' => array('tar', 'rar', 'zip', 'gz'),
		);
		$this->filetypeids = array_keys($this->filetypedata);
		foreach($this->filetypedata as $data) {
			$this->filetypes = array_merge($this->filetypes, $data);
		}
	}

	function set_dir($dir) {
		$this->dir = $dir;
	}

	function set_thumb($width, $height, $ext = '') {
		$this->thumb_width = $width;
		$this->thumb_height = $height;
		$this->thumb_ext = $ext;
	}

	function set_watermark($file, $pos = 9, $alpha = 100) {
		$this->watermark_file = $file;
		$this->watermark_pos = $pos;
		$this->watermark_alpha = $alpha;
	}

	function execute() {
		$arr = array();
		$keys = array_keys($_FILES['attach']['name']);
		foreach($keys as $key) {
			if(!$_FILES['attach']['name'][$key]) {
				continue;
			}
			$file = array(
				'name' => $_FILES['attach']['name'][$key],
				'tmp_name' => $_FILES['attach']['tmp_name'][$key]
			);
			$fileext = $this->fileext($file['name']);
			if(!in_array($fileext, $this->filetypes)) {
				$fileext = '_'.$fileext;
			}

			$tfilename = $this->time.rand(100, 999);
			$filename = '1'.$tfilename.'.'.$fileext;
			$filethumb = '0'.$tfilename.'.'.($this->thumb_ext ? $this->thumb_ext : $fileext);
			$this->copy($file['tmp_name'], $this->dir.'/'.$filename);
			$arr[$key]['file'] = $filename;
			if(in_array($fileext, array('jpg', 'gif', 'png'))) {
				if($this->thumb_width) {
					if($this->thumb($this->thumb_width, $this->thumb_height, $this->dir.'/'.$filename, $this->dir.'/'.$filethumb, ($this->thumb_ext ? $this->thumb_ext : $fileext))) {
						$arr[$key]['thumb'] = $filethumb;
					}
				}
				if($this->watermark_file) {
					$this->waterfile($this->dir.'/'.$filename, $this->watermark_file, $fileext, $this->watermark_pos, $this->watermark_alpha);
				}
			}
		}
		return $arr;
	}

	function mkdir_by_date($date, $dir = '.') {
		list($y, $m, $d) = explode('-', date('Y-m-d', $date));
		!is_dir("$dir/$y") && mkdir("$dir/$y", 0777);
		!is_dir("$dir/$y/$m$d") && mkdir("$dir/$y/$m$d", 0777);
		return "$y/$m$d";
	}

	function mkdir_by_hash($s, $dir = '.') {
		$s = md5($s);
		!is_dir($dir.'/'.$s[0]) && mkdir($dir.'/'.$s[0], 0777);
		!is_dir($dir.'/'.$s[0].'/'.$s[1]) && mkdir($dir.'/'.$s[0].'/'.$s[1], 0777);
		!is_dir($dir.'/'.$s[0].'/'.$s[1].'/'.$s[2]) && mkdir($dir.'/'.$s[0].'/'.$s[1].'/'.$s[2], 0777);
		return $s[0].'/'.$s[1].'/'.$s[2];
	}

	function mkdir_by_uid($uid, $dir = '.') {
		$uid = sprintf("%09d", $uid);
		$dir1 = substr($uid, 0, 3);
		$dir2 = substr($uid, 3, 2);
		$dir3 = substr($uid, 5, 2);
		!is_dir($dir.'/'.$dir1) && mkdir($dir.'/'.$dir1, 0777);
		!is_dir($dir.'/'.$dir1.'/'.$dir2) && mkdir($dir.'/'.$dir1.'/'.$dir2, 0777);
		!is_dir($dir.'/'.$dir1.'/'.$dir2.'/'.$dir3) && mkdir($dir.'/'.$dir1.'/'.$dir2.'/'.$dir3, 0777);
		return $dir1.'/'.$dir2.'/'.$dir3;
	}

	function copy($sourcefile, $destfile) {
		move_uploaded_file($sourcefile, $destfile);
		@unlink($sourcefile);
	}

	function watermark($target, $watermark_file, $ext, $watermarkstatus=9, $watermarktrans=50) {
		$gdsurporttype = array();
		if(function_exists('imageAlphaBlending') && function_exists('getimagesize')) {
			if(function_exists('imageGIF')) $gdsurporttype[]='gif';
			if(function_exists('imagePNG')) $gdsurporttype[]='png';
			if(function_exists('imageJPEG')) {
				$gdsurporttype[]='jpg';
				$gdsurporttype[]='jpeg';
			}
		}
		if($gdsurporttype && in_array($ext, $gdsurporttype) ) {
			$attachinfo	= getimagesize($target);
			$watermark_logo = imageCreateFromGIF($watermark_file);

			$logo_w		= imageSX($watermark_logo);
			$logo_h		= imageSY($watermark_logo);
			$img_w		= $attachinfo[0];
			$img_h		= $attachinfo[1];
			$wmwidth	= $img_w - $logo_w;
			$wmheight	= $img_h - $logo_h;

			$animatedgif = 0;
			if($attachinfo['mime'] == 'image/gif') {
				$fp = fopen($target, 'rb');
				$targetcontent = fread($fp, 9999999);
				fclose($fp);
				$animatedgif = strpos($targetcontent, 'NETSCAPE2.0') === FALSE ? 0 : 1;
			}

			if($watermark_logo && $wmwidth > 10 && $wmheight > 10 && !$animatedgif) {
				switch ($attachinfo['mime']) {
					case 'image/jpeg':
						$dst_photo = imageCreateFromJPEG($target);
						break;
					case 'image/gif':
						$dst_photo = imageCreateFromGIF($target);
						break;
					case 'image/png':
						$dst_photo = imageCreateFromPNG($target);
						break;
				}

				switch($watermarkstatus) {
					case 1:
						$x = +5;
						$y = +5;
						break;
					case 2:
						$x = ($logo_w +	$img_w)	/ 2;
						$y = +5;
						break;
					case 3:
						$x = $img_w - $logo_w-5;
						$y = +5;
						break;
					case 4:
						$x = +5;
						$y = ($logo_h +	$img_h)	/ 2;
						break;
					case 5:
						$x = ($logo_w +	$img_w)	/ 2;
						$y = ($logo_h +	$img_h)	/ 2;
						break;
					case 6:
						$x = $img_w - $logo_w;
						$y = ($logo_h +	$img_h)	/ 2;
						break;
					case 7:
						$x = +5;
						$y = $img_h - $logo_h-5;
						break;
					case 8:
						$x = ($logo_w +	$img_w)	/ 2;
						$y = $img_h - $logo_h;
						break;
					case 9:
						$x = $img_w - $logo_w-5;
						$y = $img_h - $logo_h-5;
						break;
				}

				imageAlphaBlending($watermark_logo, FALSE);
				imagesavealpha($watermark_logo,TRUE);
				imageCopyMerge($dst_photo, $watermark_logo, $x,	$y, 0, 0, $logo_w, $logo_h, $watermarktrans);

				switch($attachinfo['mime']) {
					case 'image/jpeg':
						imageJPEG($dst_photo, $target);
						break;
					case 'image/gif':
						imageGIF($dst_photo, $target);
						break;
					case 'image/png':
						imagePNG($dst_photo, $target);
						break;
				}
			}
		}
	}

	function thumb($forcedwidth, $forcedheight, $sourcefile, $destfile, $destext, $imgcomp = 0) {
		$g_imgcomp=100-$imgcomp;
		$g_srcfile=$sourcefile;
		$g_dstfile=$destfile;
		$g_fw=$forcedwidth;
		$g_fh=$forcedheight;
		$ext = strtolower(substr(strrchr($sourcefile, '.'), 1, 10));
		if(file_exists($g_srcfile)) {
			$g_is = getimagesize($g_srcfile);
			if($g_is[0] < $forcedwidth && $g_is[1] < $forcedheight) {
				copy($sourcefile, $destfile);
				return filesize($destfile);;
			}
			if (($g_is[0] - $g_fw) >= ($g_is[1] - $g_fh)){
				$g_iw=$g_fw;
				$g_ih=($g_fw/$g_is[0])*$g_is[1];
			} else {
				$g_ih=$g_fh;
				$g_iw=($g_ih/$g_is[1])*$g_is[0];
			}
			switch ($ext) {
				case 'jpg':
					$img_src = @imagecreatefromjpeg($g_srcfile);
					!$img_src && $img_src = imagecreatefromgif($g_srcfile);
					break;
				case 'gif':
					$img_src = imagecreatefromgif($g_srcfile);
					break;
				case 'png':
					$img_src = imagecreatefrompng($g_srcfile);
					break;
			}
			$img_dst = imagecreatetruecolor($g_iw, $g_ih);
			imagecopyresampled($img_dst, $img_src, 0, 0, 0, 0, $g_iw, $g_ih, $g_is[0], $g_is[1]);
			switch($destext) {
				case 'jpg':
					imagejpeg($img_dst, $g_dstfile, $g_imgcomp);
					break;
				case 'gif':
					imagegif($img_dst, $g_dstfile, $g_imgcomp);
					break;
			}
			imagedestroy($img_dst);
			return filesize($destfile);
		} else {
			return false;
		}
	}

	function fileext($filename) {
		return substr(strrchr($filename, '.'), 1, 10);
	}

	function get_filetype($ext) {
		foreach($this->filetypedata as $k => $v) {
			if(in_array($ext, $v)) {
				return $k;
			}
		}
		return 'common';
	}
}

?>