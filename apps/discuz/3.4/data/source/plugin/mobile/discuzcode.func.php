<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: discuzcode.func.php 36284 2016-12-12 00:47:50Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include template('mobile:discuzcode');

function mobile_discuzcode($param) {
	global $_G;

	list($message, $smileyoff, $bbcodeoff, $htmlon, $allowsmilies, $allowbbcode, $allowimgcode, $allowhtml, $jammer, $parsetype, $authorid, $allowmediacode, $pid, $lazyload, $pdateline, $first) = $param;
	static $authorreplyexist;

	$message = preg_replace(array(lang('forum/misc', 'post_edit_regexp'), lang('forum/misc', 'post_edithtml_regexp'), lang('forum/misc', 'post_editnobbcode_regexp')), '', $message);

	if($pid && strpos($message, '[/password]') !== FALSE) {
		if($authorid != $_G['uid'] && !$_G['forum']['ismoderator']) {
			$message = preg_replace_callback("/\s?\[password\](.+?)\[\/password\]\s?/i", create_function('$matches', 'return parsepassword($matches[1], '.intval($pid).');'), $message);
			if($_G['forum_discuzcode']['passwordlock'][$pid]) {
				return '';
			}
		} else {
			$message = preg_replace("/\s?\[password\](.+?)\[\/password\]\s?/i", "", $message);
			$_G['forum_discuzcode']['passwordauthor'][$pid] = 1;
		}
	}

	$message = preg_replace('/\[\tDISCUZ_CODE_\d+\t\]/', '', $message);
	if($parsetype != 1 && !$bbcodeoff && $allowbbcode && (strpos($message, '[/code]') || strpos($message, '[/CODE]')) !== FALSE) {
		$message = preg_replace_callback("/\s?\[code\](.+?)\[\/code\]\s?/is", 'mobile_discuzcode_callback_mobile_parsecode_1', $message);
	}

	$msglower = strtolower($message);

	$htmlon = $htmlon && $allowhtml ? 1 : 0;

	if(!$htmlon) {
		$message = dhtmlspecialchars($message);
	} else {
		$message = preg_replace("/<script[^\>]*?>(.*?)<\/script>/i", '', $message);
	}

	if(!$smileyoff && $allowsmilies) {
		$message = mobile_parsesmiles($message);
	}

	if($_G['setting']['allowattachurl'] && strpos($msglower, 'attach://') !== FALSE) {
		$message = preg_replace_callback("/attach:\/\/(\d+)\.?(\w*)/i", 'mobile_discuzcode_callback_parseattachurl_12', $message);
	}

	if($allowbbcode) {
		if(strpos($msglower, 'ed2k://') !== FALSE) {
			$message = preg_replace_callback("/ed2k:\/\/([^\/\s'\"]+)\//", 'mobile_discuzcode_callback_mobile_parseed2k_1', $message);
		}
	}

	if(!$bbcodeoff && $allowbbcode) {
		if(strpos($msglower, '[/url]') !== FALSE) {
			$message = preg_replace_callback("/\[url(=((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.|mailto:)?([^\r\n\[\"']+?))?\](.+?)\[\/url\]/is", 'mobile_discuzcode_callback_mobile_parseurl_152', $message);
		}
		if(strpos($msglower, '[/email]') !== FALSE) {
			$message = preg_replace_callback("/\[email(=([A-Za-z0-9\-_.+]+)@([A-Za-z0-9\-_]+[.][A-Za-z0-9\-_.]+))?\](.+?)\[\/email\]/is", 'mobile_discuzcode_callback_mobile_parseemail_14', $message);
		}

		$nest = 0;
		while(strpos($msglower, '[table') !== FALSE && strpos($msglower, '[/table]') !== FALSE){
			$message = preg_replace_callback("/\[table(?:=(\d{1,4}%?)(?:,([\(\)%,#\w ]+))?)?\]\s*(.+?)\s*\[\/table\]/is", 'mobile_discuzcode_callback_mobile_parsetable_123', $message);
			if(++$nest > 4) break;
		}

		$message = str_replace(array(
			'[/color]', '[/backcolor]', '[/size]', '[/font]', '[/align]', '[b]', '[/b]', '[s]', '[/s]', '[hr]', '[/p]',
			'[i=s]', '[i]', '[/i]', '[u]', '[/u]', '[list]', '[list=1]', '[list=a]',
			'[list=A]', "\r\n[*]", '[*]', '[/list]', '[indent]', '[/indent]', '[/float]'
			), array(
			'</font>', '</font>', '', '', '', '<strong>', '</strong>', '<strike>', '</strike>', '<hr class="l" />', '</p>', '', '',
			'', '', '', '<ul>', '<ul type="1" class="litype_1">', '<ul type="a" class="litype_2">',
			'<ul type="A" class="litype_3">', '<li>', '<li>', '</ul>', '', '', ''
			), preg_replace(array(
			"/\[color=([#\w]+?)\]/i",
			"/\[color=((rgb|rgba)\([\d\s\.,]+?\))\]/i",
			"/\[backcolor=([#\w]+?)\]/i",
			"/\[backcolor=((rgb|rgba)\([\d\s\.,]+?\))\]/i",
			"/\[size=(\d{1,2}?)\]/i",
			"/\[size=(\d+(\.\d+)?(px|pt)+?)\]/i",
			"/\[font=([^\[\<]+?)\]/i",
			"/\[align=(left|center|right)\]/i",
			"/\[p=(\d{1,2}|null), (\d{1,2}|null), (left|center|right)\]/i",
			"/\[float=left\]/i",
			"/\[float=right\]/i"
			), array(
			"<font color=\"\\1\">",
			"<font style=\"color:\\1\">",
			"<font style=\"background-color:\\1\">",
			"<font style=\"background-color:\\1\">",
			"",
			"",
			"",
			"",
			"<p>",
			"",
			""
			), $message));

		$message = preg_replace("/\s?\[postbg\]\s*([^\[\<\r\n;'\"\?\(\)]+?)\s*\[\/postbg\]\s?/is", "", $message);

		if($parsetype != 1) {
			if(strpos($msglower, '[/quote]') !== FALSE) {
				$message = preg_replace("/\s?\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s?/is", mobile_quote(), $message);
			}
			if(strpos($msglower, '[/free]') !== FALSE) {
				$message = preg_replace("/\s*\[free\][\n\r]*(.+?)[\n\r]*\[\/free\]\s*/is", mobile_free(), $message);
			}
		}
		if(strpos($msglower, '[/media]') !== FALSE) {
			$message = preg_replace_callback("/\[media=([\w%,]+)\]\s*([^\[\<\r\n]+?)\s*\[\/media\]/is", 'mobile_discuzcode_callback_bbcodeurl_media2', $message);
		}
		if(strpos($msglower, '[/audio]') !== FALSE) {
			$message = preg_replace_callback("/\[audio(=1)*\]\s*([^\[\<\r\n]+?)\s*\[\/audio\]/is", 'mobile_discuzcode_callback_bbcodeurl_href2', $message);
		}
		if(strpos($msglower, '[/flash]') !== FALSE) {
			$message = preg_replace_callback("/\[flash(=(\d+),(\d+))?\]\s*([^\[\<\r\n]+?)\s*\[\/flash\]/is", 'mobile_discuzcode_callback_bbcodeurl_4', $message);
		}

		if($parsetype != 1 && $allowbbcode < 0 && isset($_G['cache']['bbcodes'][-$allowbbcode])) {
			$message = preg_replace($_G['cache']['bbcodes'][-$allowbbcode]['searcharray'], $_G['cache']['bbcodes'][-$allowbbcode]['replacearray'], $message);
		}
		if($parsetype != 1 && strpos($msglower, '[/hide]') !== FALSE && $pid) {
			if($_G['setting']['hideexpiration'] && $pdateline && (TIMESTAMP - $pdateline) / 86400 > $_G['setting']['hideexpiration']) {
				$message = preg_replace("/\[hide[=]?(d\d+)?[,]?(\d+)?\]\s*(.*?)\s*\[\/hide\]/is", "\\3", $message);
				$msglower = strtolower($message);
			}
			if(strpos($msglower, '[hide=d') !== FALSE) {
				$message = preg_replace_callback("/\[hide=(d\d+)?[,]?(\d+)?\]\s*(.*?)\s*\[\/hide\]/is", create_function('$matches', 'return expirehide($matches[1], $matches[2], $matches[3], '.intval($pdateline).');'), $message);
				$msglower = strtolower($message);
			}
			if(strpos($msglower, '[hide]') !== FALSE) {
				if($authorreplyexist === null) {
					if(!$_G['forum']['ismoderator']) {
						if($_G['uid']) {
							$authorreplyexist = C::t('forum_post')->fetch_pid_by_tid_authorid($_G['tid'], $_G['uid']);
						}
					} else {
						$authorreplyexist = TRUE;
					}
				}
				if($authorreplyexist) {
					$message = preg_replace("/\[hide\]\s*(.*?)\s*\[\/hide\]/is", mobile_hide_reply(), $message);
				} else {
					$message = preg_replace("/\[hide\](.*?)\[\/hide\]/is", mobile_hide_reply_hidden(), $message);
				}
			}
			if(strpos($msglower, '[hide=') !== FALSE) {
				$message = preg_replace_callback("/\[hide=(\d+)\]\s*(.*?)\s*\[\/hide\]/is", create_function('$matches', 'return creditshide($matches[1], $matches[2], '.intval($pid).', '.intval($authorid).');'), $message);
			}
		}
	}

	if(strpos($message, '[/tthread]') !== FALSE) {
		$matches = array();
		preg_match('/\[tthread=(.+?),(.+?)\](.*?)\[\/tthread\]/', $message, $matches);
		$message = preg_replace('/\[tthread=(.+?)\](.*?)\[\/tthread\]/', lang('plugin/qqconnect', 'connect_tthread_message', array('username' => $matches[1], 'nick' => $matches[2])), $message);
	}

	if(!$bbcodeoff) {
		if($parsetype != 1 && strpos($msglower, '[swf]') !== FALSE) {
			$message = preg_replace_callback("/\[swf\]\s*([^\[\<\r\n]+?)\s*\[\/swf\]/is", 'mobile_discuzcode_callback_bbcodeurl_1', $message);
		}

		$attrsrc = !IS_ROBOT && $lazyload ? 'file' : 'src';
		if(strpos($msglower, '[/img]') !== FALSE) {
			$message = preg_replace_callback("/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/is", create_function('$matches', 'return '.intval($allowimgcode).' ? mobile_parseimg(0, 0, $matches[1], '.intval($lazyload).', '.intval($pid).', \'onmouseover="img_onmouseoverfunc(this)" \'.('.intval($lazyload).' ? \'lazyloadthumb="1"\' : \'onload="thumbImg(this)"\')) : ('.intval($allowbbcode).' ? (!defined(\'IN_MOBILE\') ? bbcodeurl($matches[1], \'<a href="{url}" target="_blank">{url}</a>\') : bbcodeurl($matches[1], \'\')) : bbcodeurl($matches[1], \'{url}\'));'), $message);
			$message = preg_replace_callback("/\[img=(\d{1,4})[x|\,](\d{1,4})\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/is", create_function('$matches', 'return '.intval($allowimgcode).' ? mobile_parseimg($matches[1], $matches[2], $matches[3], '.intval($lazyload).', '.intval($pid).') : ('.intval($allowbbcode).' ? (!defined(\'IN_MOBILE\') ? bbcodeurl($matches[3], \'<a href=\"{url}\" target=\"_blank\">{url}</a>\') : bbcodeurl($matches[3], \'\')) : bbcodeurl($matches[3], \'{url}\'));'), $message);
		}
	}

	for($i = 0; $i <= $_G['forum_discuzcode']['pcodecount']; $i++) {
		$message = str_replace("[\tDISCUZ_CODE_$i\t]", $_G['forum_discuzcode']['codehtml'][$i], $message);
	}

	unset($msglower);
	$message = preg_replace("/(\[groupid=\d+\].*\[\/groupid\])/i", '', $message);
	$message = preg_replace("/(\r\n|\n|\r){3,}/i", "\\1\\1\\1", $message);
	return $message;
}

function mobile_discuzcode_callback_mobile_parsecode_1($matches) {
	return mobile_parsecode($matches[1]);
}

function mobile_discuzcode_callback_parseattachurl_12($matches) {
	return parseattachurl($matches[1], $matches[2], 1);
}

function mobile_discuzcode_callback_mobile_parseed2k_1($matches) {
	return mobile_parseed2k($matches[1]);
}

function mobile_discuzcode_callback_mobile_parseurl_152($matches) {
	return mobile_parseurl($matches[1], $matches[5], $matches[2]);
}

function mobile_discuzcode_callback_mobile_parseemail_14($matches) {
	return strip_tags(parseemail($matches[1], $matches[4]));
}

function mobile_discuzcode_callback_mobile_parsetable_123($matches) {
	return mobile_parsetable($matches[1], $matches[2], $matches[3]);
}

function mobile_discuzcode_callback_bbcodeurl_media2($matches) {
	return bbcodeurl($matches[2], '<a class="media" href="{url}" target="_blank">{url}</a>');
}

function mobile_discuzcode_callback_bbcodeurl_href2($matches) {
	return bbcodeurl($matches[2], '<a href="{url}" target="_blank">{url}</a>');
}

function mobile_discuzcode_callback_bbcodeurl_4($matches) {
	return bbcodeurl($matches[4], '<a href="{url}" target="_blank">{url}</a>');
}

function mobile_discuzcode_callback_bbcodeurl_1($matches) {
	return bbcodeurl($matches[1], ' <img src="'.STATICURL.'image/filetype/flash.gif" align="absmiddle" alt="" /> <a href="{url}" target="_blank">Flash: {url}</a> ');
}

function mobile_parseurl($url, $text, $scheme) {
	global $_G;
	if(!$url && preg_match("/((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.)[^\[\"']+/i", trim($text), $matches)) {
		$url = $matches[0];
		$length = 65;
		if(strlen($url) > $length) {
			$text = substr($url, 0, intval($length * 0.5)).' ... '.substr($url, - intval($length * 0.3));
		}
		$url = substr(strtolower($url), 0, 4) == 'www.' ? 'http://'.$url : $url;
		return '<a href="'.$url.'">'.$text.'</a>';
	} else {
		$url = substr($url, 1);
		if(substr(strtolower($url), 0, 4) == 'www.') {
			$url = 'http://'.$url;
		}
		$url = !$scheme ? $_G['siteurl'].$url : $url;
		return '<a href="'.$url.'">'.$text.'</a>';
	}
}

function mobile_parsecode($code) {
	global $_G;
	$_G['forum_discuzcode']['pcodecount']++;
	$code = dhtmlspecialchars(str_replace('\\"', '"', $code));
	$code = str_replace("\n", "<li>", $code);
	$_G['forum_discuzcode']['codehtml'][$_G['forum_discuzcode']['pcodecount']] = mobile_codedisp($code);
	$_G['forum_discuzcode']['codecount']++;
	return "[\tDISCUZ_CODE_".$_G['forum_discuzcode']['pcodecount']."\t]";
}

function mobile_parseimg($width, $height, $url) {
	global $_G;
	$url = htmlspecialchars(str_replace(array('<', '>'), '', str_replace('\\"', '\"', $url)));
	if(strtolower(substr($url, 0, 7)) == 'static/') {
		$url = $_G['siteurl'].$url;
	}
	if(!in_array(strtolower(substr($url, 0, 6)), array('http:/', 'https:', 'ftp://'))) {
		$url = 'http://'.$url;
	}
	$extra = ($width > 0 ? 'width="'.$width.'" ' : '').($height > 0 ? 'height="'.$height.'" ' : '');
	return mobile_image($url, $extra);
}

function mobile_parsesmiles(&$message) {
	global $_G;
	static $enablesmiles;
	if($enablesmiles === null) {
		$url = !in_array(strtolower(substr(STATICURL, 0, 6)), array('http:/', 'https:', 'ftp://')) ? $_G['siteurl'] : '';
		$enablesmiles = false;
		if(!empty($_G['cache']['smilies']) && is_array($_G['cache']['smilies'])) {
			foreach($_G['cache']['smilies']['replacearray'] AS $key => $smiley) {
				$enablesmiles[$key] = '<img src="'.$url.STATICURL.'image/smiley/'.$_G['cache']['smileytypes'][$_G['cache']['smilies']['typearray'][$key]]['directory'].'/'.$smiley.'" />';
			}
		}
	}
	$enablesmiles && $message = preg_replace($_G['cache']['smilies']['searcharray'], $enablesmiles, $message, $_G['setting']['maxsmilies']);
	return $message;
}

function mobile_parsetable($width, $bgcolor, $message) {
	if(strpos($message, '[/tr]') === FALSE && strpos($message, '[/td]') === FALSE) {
		$rows = explode("\n", $message);
		$s = '<ul>';
		foreach($rows as $row) {
			$s .= '<li>'.str_replace(array('\|', '|', '\n'), array('&#124;', '</li><li>', "\n"), $row).'</li>';
		}
		$s .= '</ul>';
		return $s;
	} else {
		if(!preg_match("/^\[tr(?:=([\(\)\s%,#\w]+))?\]\s*\[td([=\d,%]+)?\]/", $message) && !preg_match("/^<tr[^>]*?>\s*<td[^>]*?>/", $message)) {
			return str_replace('\\"', '"', preg_replace("/\[tr(?:=([\(\)\s%,#\w]+))?\]|\[td([=\d,%]+)?\]|\[\/td\]|\[\/tr\]/", '', $message));
		}
		$message = preg_replace_callback("/\[tr(?:=([\(\)\s%,#\w]+))?\]\s*\[td(?:=(\d{1,4}%?))?\]/i", 'mobile_parsetable_callback_mobile_parsetrtd_12', $message);
		$message = preg_replace_callback("/\[\/td\]\s*\[td(?:=(\d{1,4}%?))?\]/i", 'mobile_parsetable_callback_mobile_parsetrtd_1', $message);
		$message = preg_replace_callback("/\[tr(?:=([\(\)\s%,#\w]+))?\]\s*\[td(?:=(\d{1,2}),(\d{1,2})(?:,(\d{1,4}%?))?)?\]/i", 'mobile_parsetable_callback_mobile_parsetrtd_1234', $message);
		$message = preg_replace_callback("/\[\/td\]\s*\[td(?:=(\d{1,2}),(\d{1,2})(?:,(\d{1,4}%?))?)?\]/i", 'mobile_parsetable_callback_mobile_parsetrtd_123', $message);
		return '<table class="dzcode_table" cellspacing="0" '.
			($bgcolor ? ' bgcolor="'.$bgcolor.'">' : '>').
			str_replace('\\"', '"', preg_replace("/\[\/td\]\s*\[\/tr\]\s*/i", '</td></tr>', $message)
			).'</table>';
	}
}

function mobile_parsetable_callback_mobile_parsetrtd_12($matches) {
	return mobile_parsetrtd($matches[1], 0, 0, $matches[2]);
}

function mobile_parsetable_callback_mobile_parsetrtd_1($matches) {
	return mobile_parsetrtd('td', 0, 0, $matches[1]);
}

function mobile_parsetable_callback_mobile_parsetrtd_1234($matches) {
	return mobile_parsetrtd($matches[1], $matches[2], $matches[3], $matches[4]);
}

function mobile_parsetable_callback_mobile_parsetrtd_123($matches) {
	return mobile_parsetrtd('td', $matches[1], $matches[2], $matches[3]);
}

function mobile_parsetrtd($bgcolor, $colspan, $rowspan, $width) {
	return ($bgcolor == 'td' ? '</td></tr>' : '<tr'.($bgcolor ? ' style="background-color:'.$bgcolor.'"' : '').'>').'<td class="dzcode_td">';
}

function mobile_parseed2k($url) {
	global $_G;
	list(,$type, $name, $size,) = explode('|', $url);
	$name = addslashes($name);
	if($type == 'file') {
		return '<a ed2k="'.urlencode($url).'">'.$name.' ('.sizecount($size).')</a>';
	} else {
		return '<a ed2k="'.urlencode($url).'">'.$url.'</a>';
	}
}

?>