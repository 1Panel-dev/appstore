<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_search.php 36278 2016-12-09 07:52:35Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
function searchkey($keyword, $field, $returnsrchtxt = 0) {
	$srchtxt = '';
	if($field && $keyword) {
		if(preg_match("(AND|\+|&|\s)", $keyword) && !preg_match("(OR|\|)", $keyword)) {
			$andor = ' AND ';
			$keywordsrch = '1';
			$keyword = preg_replace("/( AND |&| )/is", "+", $keyword);
		} else {
			$andor = ' OR ';
			$keywordsrch = '0';
			$keyword = preg_replace("/( OR |\|)/is", "+", $keyword);
		}
		$keyword = str_replace('*', '%', addcslashes($keyword, '%_'));
		$srchtxt = $returnsrchtxt ? $keyword : '';
		foreach(explode('+', $keyword) as $text) {
			$text = trim(daddslashes($text));
			if($text) {
				$keywordsrch .= $andor;
				$keywordsrch .= str_replace('{text}', $text, $field);
			}
		}
		$keyword = " AND ($keywordsrch)";
	}
	return $returnsrchtxt ? array($srchtxt, $keyword) : $keyword;
}

function highlight($text, $words, $prepend) {
	$text = str_replace('\"', '"', $text);
	foreach($words AS $key => $replaceword) {
		$text = str_replace($replaceword, '<highlight>'.$replaceword.'</highlight>', $text);
	}
	return "$prepend$text";
}

function bat_highlight($message, $words, $color = '#ff0000') {
	if(!empty($words)) {
		$highlightarray = explode(' ', $words);
		$sppos = strrpos($message, chr(0).chr(0).chr(0));
		if($sppos !== FALSE) {
			$specialextra = substr($message, $sppos + 3);
			$message = substr($message, 0, $sppos);
		}
		bat_highlight_callback_highlight_21($highlightarray, 1);
		$message = preg_replace_callback("/(^|>)([^<]+)(?=<|$)/sU", 'bat_highlight_callback_highlight_21', $message);
		$message = preg_replace("/<highlight>(.*)<\/highlight>/siU", "<strong><font color=\"$color\">\\1</font></strong>", $message);
		if($sppos !== FALSE) {
			$message = $message.chr(0).chr(0).chr(0).$specialextra;
		}
	}
	return $message;
}

function bat_highlight_callback_highlight_21($matches, $action = 0) {
	static $highlightarray = array();

	if($action == 1) {
		$highlightarray = $matches;
	} else {
		return highlight($matches[2], $highlightarray, $matches[1]);
	}
}

?>