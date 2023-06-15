<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: secqaa_calc.php 10395 2010-05-11 04:48:31Z monkey $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class secqaa_calc {

	var $version = '1.0';
	var $name = 'calc_name';
	var $description = 'calc_desc';
	var $copyright = '<a href="https://www.discuz.vip/" target="_blank">Discuz!</a>';
	var $customname = '';

	function make(&$question) {
		$a = rand(1, 90);
		$b = rand(1, 10);
		if(rand(0, 1)) {
			$question = $a.' + '.$b.' = ?';
			$answer = $a + $b;
		} else {
			$question = $a.' - '.$b.' = ?';
			$answer = $a - $b;
		}
		return $answer;
	}

}

?>