<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_discuzcode.php 36331 2016-12-28 01:08:45Z nemohou $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include template('forum/discuzcode');

$_G['forum_discuzcode'] = array(
	'pcodecount' => -1,
	'codecount' => 0,
	'codehtml' => array(),
	'passwordlock' => array(),
	'smiliesreplaced' => 0,
	'seoarray' => array(
		0 => '',
		1 => $_SERVER['HTTP_HOST'],
		2 => $_G['setting']['bbname'],
		3 => str_replace('{bbname}', $_G['setting']['bbname'], $_G['setting']['seotitle']),
		4 => $_G['setting']['seokeywords'],
		5 => $_G['setting']['seodescription']
	)
);

if(!isset($_G['cache']['bbcodes']) || !is_array($_G['cache']['bbcodes']) || !is_array($_G['cache']['smilies'])) {
	loadcache(array('bbcodes', 'smilies', 'smileytypes'));
}

function creditshide($creditsrequire, $message, $pid, $authorid) {
	global $_G;
	if($_G['member']['credits'] >= $creditsrequire || $_G['forum']['ismoderator'] || $_G['uid'] && $authorid == $_G['uid']) {
		return tpl_hide_credits($creditsrequire, str_replace('\\"', '"', $message));
	} else {
		return tpl_hide_credits_hidden($creditsrequire);
	}
}

function expirehide($expiration, $creditsrequire, $message, $dateline) {
	$expiration = $expiration ? substr($expiration, 1) : 0;
	if($expiration && $dateline && (TIMESTAMP - $dateline) / 86400 > $expiration) {
		return str_replace('\\"', '"', $message);
	}
	return '[hide'.($creditsrequire ? "=$creditsrequire" : '').']'.str_replace('\\"', '"', $message).'[/hide]';
}

function codedisp($code) {
	global $_G;
	$_G['forum_discuzcode']['pcodecount']++;
	$code = dhtmlspecialchars(str_replace('\\"', '"', $code));
	$code = str_replace("\n", "<li>", $code);
	$_G['forum_discuzcode']['codehtml'][$_G['forum_discuzcode']['pcodecount']] = tpl_codedisp($code);
	$_G['forum_discuzcode']['codecount']++;
	return "[\tDISCUZ_CODE_".$_G['forum_discuzcode']['pcodecount']."\t]";
}

function karmaimg($rate, $ratetimes) {
	$karmaimg = '';
	if($rate && $ratetimes) {
		$image = $rate > 0 ? 'agree.gif' : 'disagree.gif';
		for($i = 0; $i < ceil(abs($rate) / $ratetimes); $i++) {
			$karmaimg .= '<img src="'.$_G['style']['imgdir'].'/'.$image.'" border="0" alt="" />';
		}
	}
	return $karmaimg;
}

function discuzcode($message, $smileyoff = false, $bbcodeoff = false, $htmlon = 0, $allowsmilies = 1, $allowbbcode = 1, $allowimgcode = 1, $allowhtml = 0, $jammer = 0, $parsetype = '0', $authorid = '0', $allowmediacode = '0', $pid = 0, $lazyload = 0, $pdateline = 0, $first = 0) {
	global $_G;

	static $authorreplyexist;

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
		$message = preg_replace_callback("/\s?\[code\](.+?)\[\/code\]\s?/is", 'discuzcode_callback_codedisp_1', $message);
	}

	$msglower = strtolower($message);

	$htmlon = $htmlon && $allowhtml ? 1 : 0;

	if(!$htmlon) {
		$message = dhtmlspecialchars($message);
	} else {
		$message = preg_replace("/<script[^\>]*?>(.*?)<\/script>/i", '', $message);
	}

	if($_G['setting']['plugins']['func'][HOOKTYPE]['discuzcode']) {
		$_G['discuzcodemessage'] = & $message;
		$param = func_get_args();
		hookscript('discuzcode', 'global', 'funcs', array('param' => $param, 'caller' => 'discuzcode'), 'discuzcode');
	}

	if(!$smileyoff && $allowsmilies) {
		$message = parsesmiles($message);
	}

	if($_G['setting']['allowattachurl'] && strpos($msglower, 'attach://') !== FALSE) {
		$message = preg_replace_callback("/attach:\/\/(\d+)\.?(\w*)/i", 'discuzcode_callback_parseattachurl_12', $message);
	}

	if($allowbbcode) {
		if(strpos($msglower, 'ed2k://') !== FALSE) {
			$message = preg_replace_callback("/ed2k:\/\/([^\/\s'\"]+)\//", 'discuzcode_callback_parseed2k_1', $message);
		}
	}

	if(!$bbcodeoff && $allowbbcode) {
		if(strpos($msglower, '[/url]') !== FALSE) {
			$message = preg_replace_callback("/\[url(=((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.|mailto:|tel:|magnet:)?([^\r\n\[\"']+?))?\](.+?)\[\/url\]/is", 'discuzcode_callback_parseurl_152', $message);
		}
		if(strpos($msglower, '[/email]') !== FALSE) {
			$message = preg_replace_callback("/\[email(=([A-Za-z0-9\-_.+]+)@([A-Za-z0-9\-_]+[.][A-Za-z0-9\-_.]+))?\](.+?)\[\/email\]/is", 'discuzcode_callback_parseemail_14', $message);
		}

		$nest = 0;
		while(strpos($msglower, '[table') !== FALSE && strpos($msglower, '[/table]') !== FALSE){
			$message = preg_replace_callback("/\[table(?:=(\d{1,4}%?)(?:,([\(\)%,#\w ]+))?)?\]\s*(.+?)\s*\[\/table\]/is", 'discuzcode_callback_parsetable_123', $message);
			if(++$nest > 4) break;
		}

		$message = str_replace(array(
			'[/color]', '[/backcolor]', '[/size]', '[/font]', '[/align]', '[b]', '[/b]', '[s]', '[/s]', '[hr]', '[/p]',
			'[i=s]', '[i]', '[/i]', '[u]', '[/u]', '[list]', '[list=1]', '[list=a]',
			'[list=A]', "\r\n[*]", '[*]', '[/list]', '[indent]', '[/indent]', '[/float]'
			), array(
			'</font>', '</font>', '</font>', '</font>', '</div>', '<strong>', '</strong>', '<strike>', '</strike>', '<hr class="l" />', '</p>', '<i class="pstatus">', '<i>',
			'</i>', '<u>', '</u>', '<ul>', '<ul type="1" class="litype_1">', '<ul type="a" class="litype_2">',
			'<ul type="A" class="litype_3">', '<li>', '<li>', '</ul>', '<blockquote>', '</blockquote>', '</span>'
			), preg_replace(array(
			"/\[color=([#\w]+?)\]/i",
			"/\[color=((rgb|rgba)\([\d\s,]+?\))\]/i",
			"/\[backcolor=([#\w]+?)\]/i",
			"/\[backcolor=((rgb|rgba)\([\d\s,]+?\))\]/i",
			"/\[size=(\d{1,2}?)\]/i",
			"/\[size=(\d{1,2}(\.\d{1,5})?(px|pt)+?)\]/i",
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
			"<font size=\"\\1\">",
			"<font style=\"font-size:\\1\">",
			"<font>",
			"<font face=\"\\1\">",
			"<div align=\"\\1\">",
			"<p style=\"line-height:\\1px;text-indent:\\2em;text-align:\\3\">",
			"<span style=\"float:left;margin-right:5px\">",
			"<span style=\"float:right;margin-left:5px\">"
			), $message));

		if($pid && !defined('IN_MOBILE')) {
			$message = preg_replace_callback("/\s?\[postbg\]\s*([^\[\<\r\n;'\"\?\(\)]+?)\s*\[\/postbg\]\s?/is", create_function('$matches', 'return parsepostbg($matches[1], '.intval($pid).');'), $message);
		} else {
			$message = preg_replace("/\s?\[postbg\]\s*([^\[\<\r\n;'\"\?\(\)]+?)\s*\[\/postbg\]\s?/is", "", $message);
		}

		if($parsetype != 1) {
			if(strpos($msglower, '[/quote]') !== FALSE) {
				$message = preg_replace("/\s?\[quote\][\n\r]*(.+?)[\n\r]*\[\/quote\]\s?/is", tpl_quote(), $message);
			}
			if(strpos($msglower, '[/free]') !== FALSE) {
				$message = preg_replace("/\s*\[free\][\n\r]*(.+?)[\n\r]*\[\/free\]\s*/is", tpl_free(), $message);
			}
		}
		if(!defined('IN_MOBILE') || !in_array(constant('IN_MOBILE'), array('1', '3', '4'))) {
			if(strpos($msglower, '[/media]') !== FALSE) {
				$message = preg_replace_callback("/\[media=([\w%,]+)\]\s*([^\[\<\r\n]+?)\s*\[\/media\]/is", $allowmediacode ? 'discuzcode_callback_parsemedia_12' : 'discuzcode_callback_bbcodeurl_2', $message);
			}
			if(strpos($msglower, '[/audio]') !== FALSE) {
				$message = preg_replace_callback("/\[audio(=1)*\]\s*([^\[\<\r\n]+?)\s*\[\/audio\]/is", $allowmediacode ? 'discuzcode_callback_parseaudio_2' : 'discuzcode_callback_bbcodeurl_2', $message);
			}
			if(strpos($msglower, '[/flash]') !== FALSE) {
				$message = preg_replace_callback("/\[flash(=(\d+),(\d+))?\]\s*([^\[\<\r\n]+?)\s*\[\/flash\]/is", $allowmediacode ? 'discuzcode_callback_parseflash_234' : 'discuzcode_callback_bbcodeurl_4', $message);
			}
		} else {
			if(strpos($msglower, '[/media]') !== FALSE) {
				$message = preg_replace("/\[media=([\w%,]+)\]\s*([^\[\<\r\n]+?)\s*\[\/media\]/is", "[media]\\2[/media]", $message);
			}
			if(strpos($msglower, '[/audio]') !== FALSE) {
				$message = preg_replace("/\[audio(=1)*\]\s*([^\[\<\r\n]+?)\s*\[\/audio\]/is", "[media]\\2[/media]", $message);
			}
			if(strpos($msglower, '[/flash]') !== FALSE) {
				$message = preg_replace("/\[flash(=(\d+),(\d+))?\]\s*([^\[\<\r\n]+?)\s*\[\/flash\]/is", "[media]\\4[/media]", $message);
			}
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
							$_post = C::t('forum_post')->fetch('tid:'.$_G['tid'], $pid);
							$authorreplyexist = $_post['tid'] == $_G['tid'] ? C::t('forum_post')->fetch_pid_by_tid_authorid($_G['tid'], $_G['uid']) : FALSE;
						}
					} else {
						$authorreplyexist = TRUE;
					}
				}
				if($authorreplyexist) {
					$message = preg_replace("/\[hide\]\s*(.*?)\s*\[\/hide\]/is", tpl_hide_reply(), $message);
				} else {
					$message = preg_replace("/\[hide\](.*?)\[\/hide\]/is", tpl_hide_reply_hidden(), $message);
					$message = '<script type="text/javascript">replyreload += \',\' + '.$pid.';</script>'.$message;
				}
			}
			if(strpos($msglower, '[hide=') !== FALSE) {
				$message = preg_replace_callback("/\[hide=(\d+)\]\s*(.*?)\s*\[\/hide\]/is", create_function('$matches', 'return creditshide($matches[1], $matches[2], '.intval($pid).', '.intval($authorid).');'), $message);
			}
		}
	}

	if(!$bbcodeoff) {
		if($parsetype != 1 && strpos($msglower, '[swf]') !== FALSE) {
			$message = preg_replace_callback("/\[swf\]\s*([^\[\<\r\n]+?)\s*\[\/swf\]/is", 'discuzcode_callback_bbcodeurl_1', $message);
		}
		$attrsrc = !IS_ROBOT && $lazyload ? 'file' : 'src';
		if(strpos($msglower, '[/img]') !== FALSE) {
			$message = preg_replace_callback("/\[img\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/is", create_function('$matches', 'return '.intval($allowimgcode).' ? parseimg(0, 0, $matches[1], '.intval($lazyload).', '.intval($pid).', \'onmouseover="img_onmouseoverfunc(this)" \'.('.intval($lazyload).' ? \'lazyloadthumb="1"\' : \'onload="thumbImg(this)"\')) : ('.intval($allowbbcode).' ? (!defined(\'IN_MOBILE\') ? bbcodeurl($matches[1], \'<a href="{url}" target="_blank">{url}</a>\') : bbcodeurl($matches[1], \'\')) : bbcodeurl($matches[1], \'{url}\'));'), $message);
			$message = preg_replace_callback("/\[img=(\d{1,4})[x|\,](\d{1,4})\]\s*([^\[\<\r\n]+?)\s*\[\/img\]/is", create_function('$matches', 'return '.intval($allowimgcode).' ? parseimg($matches[1], $matches[2], $matches[3], '.intval($lazyload).', '.intval($pid).') : ('.intval($allowbbcode).' ? (!defined(\'IN_MOBILE\') ? bbcodeurl($matches[3], \'<a href="{url}" target="_blank">{url}</a>\') : bbcodeurl($matches[3], \'\')) : bbcodeurl($matches[3], \'{url}\'));'), $message);
		}
	}

	for($i = 0; $i <= $_G['forum_discuzcode']['pcodecount']; $i++) {
		$message = str_replace("[\tDISCUZ_CODE_$i\t]", $_G['forum_discuzcode']['codehtml'][$i], $message);
	}

	unset($msglower);

	if($jammer) {
		$message = preg_replace_callback("/\r\n|\n|\r/", 'discuzcode_callback_jammer', $message);
	}
	if($first) {
		if(helper_access::check_module('group')) {
			$message = preg_replace("/\[groupid=(\d+)\](.*)\[\/groupid\]/i", lang('forum/template', 'fromgroup').': <a href="forum.php?mod=forumdisplay&fid=\\1" target="_blank">\\2</a>', $message);
		} else {
			$message = preg_replace("/(\[groupid=\d+\].*\[\/groupid\])/i", '', $message);
		}

	}
	return $htmlon ? $message : nl2br(str_replace(array("\t", '   ', '  '), array('&nbsp; &nbsp; &nbsp; &nbsp; ', '&nbsp; &nbsp;', '&nbsp;&nbsp;'), $message));
}

function discuzcode_callback_codedisp_1($matches) {
	return codedisp($matches[1]);
}

function discuzcode_callback_parseattachurl_12($matches) {
	return parseattachurl($matches[1], $matches[2], 1);
}

function discuzcode_callback_parseed2k_1($matches) {
	return parseed2k($matches[1]);
}

function discuzcode_callback_parseurl_152($matches) {
	return parseurl($matches[1], $matches[5], $matches[2]);
}

function discuzcode_callback_parseemail_14($matches) {
	return parseemail($matches[1], $matches[4]);
}

function discuzcode_callback_parsetable_123($matches) {
	return parsetable($matches[1], $matches[2], $matches[3]);
}

function discuzcode_callback_parsemedia_12($matches) {
	return parsemedia($matches[1], $matches[2]);
}

function discuzcode_callback_bbcodeurl_2($matches) {
	return bbcodeurl($matches[2], '<a href="{url}" target="_blank">{url}</a>');
}

function discuzcode_callback_parseaudio_2($matches) {
	return parseaudio($matches[2], 400);
}

function discuzcode_callback_parseflash_234($matches) {
	return parseflash($matches[2], $matches[3], $matches[4]);
}

function discuzcode_callback_bbcodeurl_4($matches) {
	return bbcodeurl($matches[4], '<a href="{url}" target="_blank">{url}</a>');
}

function discuzcode_callback_bbcodeurl_1($matches) {
	return bbcodeurl($matches[1], ' <img src="'.STATICURL.'image/filetype/flash.gif" align="absmiddle" alt="" /> <a href="{url}" target="_blank">Flash: {url}</a> ');
}

function discuzcode_callback_jammer($matches) {
	return jammer();
}

function parseurl($url, $text, $scheme) {
	global $_G;
	if(!$url && preg_match("/((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|thunder|qqdl|synacast){1}:\/\/|www\.)[^\[\"']+/i", trim($text), $matches)) {
		$url = $matches[0];
		$length = 65;
		if(strlen($url) > $length) {
			$text = substr($url, 0, intval($length * 0.5)).' ... '.substr($url, - intval($length * 0.3));
		}
		return '<a href="'.(substr(strtolower($url), 0, 4) == 'www.' ? 'http://'.$url : $url).'" target="_blank">'.$text.'</a>';
	} else {
		$url = substr($url, 1);
		if(substr(strtolower($url), 0, 4) == 'www.') {
			$url = 'http://'.$url;
		}
		$url = !$scheme ? $_G['siteurl'].$url : $url;
		return '<a href="'.$url.'" target="_blank">'.$text.'</a>';
	}
}

function parseflash($w, $h, $url) {
	$w = !$w ? 550 : $w;
	$h = !$h ? 400 : $h;
	preg_match("/((https?){1}:\/\/|www\.)[^\r\n\[\"'\?]+(\.swf|\.flv)(\?[^\r\n\[\"'\?]+)?/i", $url, $matches);
	$url = $matches[0];
	$randomid = 'swf_'.random(3);
	if(fileext($url) != 'flv') {
		return '<span id="'.$randomid.'"></span><script type="text/javascript" reload="1">$(\''.$randomid.'\').innerHTML=AC_FL_RunContent(\'width\', \''.$w.'\', \'height\', \''.$h.'\', \'allowNetworking\', \'internal\', \'allowScriptAccess\', \'never\', \'src\', encodeURI(\''.$url.'\'), \'quality\', \'high\', \'bgcolor\', \'#ffffff\', \'wmode\', \'transparent\', \'allowfullscreen\', \'true\');</script>';
	} else {
		return '<span id="'.$randomid.'"></span><script type="text/javascript" reload="1">$(\''.$randomid.'\').innerHTML=AC_FL_RunContent(\'width\', \''.$w.'\', \'height\', \''.$h.'\', \'allowNetworking\', \'internal\', \'allowScriptAccess\', \'never\', \'src\', \''.STATICURL.'image/common/flvplayer.swf\', \'flashvars\', \'file='.rawurlencode($url).'\', \'quality\', \'high\', \'wmode\', \'transparent\', \'allowfullscreen\', \'true\');</script>';
	}
}

function parseed2k($url) {
	global $_G;
	list(,$type, $name, $size,) = explode('|', $url);
	$url = 'ed2k://'.$url.'/';
	$name = addslashes($name);
	if($type == 'file') {
		$ed2kid = 'ed2k_'.random(3);
		return '<a id="'.$ed2kid.'" href="'.$url.'" target="_blank">'.dhtmlspecialchars(urldecode($name)).' ('.sizecount($size).')</a><script language="javascript">$(\''.$ed2kid.'\').innerHTML=htmlspecialchars(unescape(decodeURIComponent(\''.$name.'\')))+\' ('.sizecount($size).')\';</script>';
	} else {
		return '<a href="'.$url.'" target="_blank">'.$url.'</a>';
	}
}

function parseattachurl($aid, $ext, $ignoretid = 0) {
	global $_G;
	require_once libfile('function/attachment');
	$_G['forum_skipaidlist'][] = $aid;
	if(!empty($ext)) {
		$attach = C::t('forum_attachment_n')->fetch('aid:'.$aid, $aid);
		if(!in_array(attachtype(fileext($attach['filename'])."\t", 'id'), array(9, 10))) {
			$ext = 0;
		}
	}
	return $_G['siteurl'].'forum.php?mod=attachment&aid='.aidencode($aid, $ext, $ignoretid ? '' : $_G['tid']).($ext ? '&request=yes&_f=.'.$ext : '');
}

function parseemail($email, $text) {
	$text = str_replace('\"', '"', $text);
	if(!$email && preg_match("/\s*([A-Za-z0-9\-_.+]+)@([A-Za-z0-9\-_]+[.][A-Za-z0-9\-_.]+)\s*/i", $text, $matches)) {
		$email = trim($matches[0]);
		return '<a href="mailto:'.$email.'">'.$email.'</a>';
	} else {
		return '<a href="mailto:'.substr($email, 1).'">'.$text.'</a>';
	}
}

function parsetable($width, $bgcolor, $message) {
	if(strpos($message, '[/tr]') === FALSE && strpos($message, '[/td]') === FALSE) {
		$rows = explode("\n", $message);
		$s = !defined('IN_MOBILE') ? '<table cellspacing="0" class="t_table" '.
			($width == '' ? NULL : 'style="width:'.$width.'"').
			($bgcolor ? ' bgcolor="'.$bgcolor.'">' : '>') : '<table>';
		foreach($rows as $row) {
			$s .= '<tr><td>'.str_replace(array('\|', '|', '\n'), array('&#124;', '</td><td>', "\n"), $row).'</td></tr>';
		}
		$s .= '</table>';
		return $s;
	} else {
		if(!preg_match("/^\[tr(?:=([\(\)\s%,#\w]+))?\]\s*\[td([=\d,%]+)?\]/", $message) && !preg_match("/^<tr[^>]*?>\s*<td[^>]*?>/", $message)) {
			return str_replace('\\"', '"', preg_replace("/\[tr(?:=([\(\)\s%,#\w]+))?\]|\[td([=\d,%]+)?\]|\[\/td\]|\[\/tr\]/", '', $message));
		}
		if(substr($width, -1) == '%') {
			$width = substr($width, 0, -1) <= 98 ? intval($width).'%' : '98%';
		} else {
			$width = intval($width);
			$width = $width ? ($width <= 560 ? $width.'px' : '98%') : '';
		}
		$message = preg_replace_callback("/\[tr(?:=([\(\)\s%,#\w]+))?\]\s*\[td(?:=(\d{1,4}%?))?\]/i", 'parsetable_callback_parsetrtd_12', $message);
		$message = preg_replace_callback("/\[\/td\]\s*\[td(?:=(\d{1,4}%?))?\]/i", 'parsetable_callback_parsetrtd_1', $message);
		$message = preg_replace_callback("/\[tr(?:=([\(\)\s%,#\w]+))?\]\s*\[td(?:=(\d{1,2}),(\d{1,2})(?:,(\d{1,4}%?))?)?\]/i", 'parsetable_callback_parsetrtd_1234', $message);
		$message = preg_replace_callback("/\[\/td\]\s*\[td(?:=(\d{1,2}),(\d{1,2})(?:,(\d{1,4}%?))?)?\]/i", 'parsetable_callback_parsetrtd_123', $message);
		$message = preg_replace("/\[\/td\]\s*\[\/tr\]\s*/i", '</td></tr>', $message);
		return (!defined('IN_MOBILE') ? '<table cellspacing="0" class="t_table" '.
			($width == '' ? NULL : 'style="width:'.$width.'"').
			($bgcolor ? ' bgcolor="'.$bgcolor.'">' : '>') : '<table>').
			str_replace('\\"', '"', $message).'</table>';
	}
}

function parsetable_callback_parsetrtd_12($matches) {
	return parsetrtd($matches[1], 0, 0, $matches[2]);
}

function parsetable_callback_parsetrtd_1($matches) {
	return parsetrtd('td', 0, 0, $matches[1]);
}

function parsetable_callback_parsetrtd_1234($matches) {
	return parsetrtd($matches[1], $matches[2], $matches[3], $matches[4]);
}

function parsetable_callback_parsetrtd_123($matches) {
	return parsetrtd('td', $matches[1], $matches[2], $matches[3]);
}

function parsetrtd($bgcolor, $colspan, $rowspan, $width) {
	return ($bgcolor == 'td' ? '</td>' : '<tr'.($bgcolor && !defined('IN_MOBILE') ? ' style="background-color:'.$bgcolor.'"' : '').'>').'<td'.($colspan > 1 ? ' colspan="'.$colspan.'"' : '').($rowspan > 1 ? ' rowspan="'.$rowspan.'"' : '').($width && !defined('IN_MOBILE') ? ' width="'.$width.'"' : '').'>';
}

function parseaudio($url, $width = 400) {
	$url = addslashes($url);
        if(!in_array(strtolower(substr($url, 0, 6)), array('http:/', 'https:', 'ftp://', 'rtsp:/', 'mms://')) && !preg_match('/^static\//', $url) && !preg_match('/^data\//', $url)) {
		return dhtmlspecialchars($url);
	}
	$type = fileext($url);
	$randomid = random(3);
	return '<ignore_js_op><div id="'.$type.'_'.$randomid.'" class="media"><div id="'.$type.'_'.$randomid.'_container" class="media_container"></div><div id="'.$type.'_'.$randomid.'_tips" class="media_tips"><a href="'.$url.'" target="_blank">'.lang('template', 'parse_av_tips').'</a></div></div><script type="text/javascript">detectPlayer("'.$type.'_'.$randomid.'", "'.$type.'", "'.$url.'", "'.$width.'", "66");</script></ignore_js_op>';
}

function parsemedia($params, $url) {
	$params = explode(',', $params);

	if(preg_match('/^(auto|100%|[0-9]{1,2}%)$/', $params[1], $matches)) {
		$width = $matches[1];
	} else {
		$width = ($params[1] > 0 && $params[1] < 8192) ? intval($params[1]) : 800;
	}

	if(preg_match('/^(auto|100%|[0-9]{1,2}%)$/', $params[2], $matches)) {
		$height = $matches[1];
	} else {
		$height = ($params[2] > 0 && $params[2] < 4096) ? intval($params[2]) : 600;
	}

	$width = defined('IN_MOBILE') ? '100%' : $width;
	$height = defined('IN_MOBILE') ? 'auto' : $height;

	$url = addslashes($url);
        if(!in_array(strtolower(substr($url, 0, 6)), array('http:/', 'https:', 'ftp://', 'rtsp:/', 'mms://')) && !preg_match('/^static\//', $url) && !preg_match('/^data\//', $url)) {
		return dhtmlspecialchars($url);
	}

	if($flv = parseflv($url, $width, $height)) {
		return $flv;
	}
	if(in_array(count($params), array(3, 4))) {
		$type = fileext($url);
		$url = str_replace(array('<', '>'), '', str_replace('\\"', '\"', $url));
		if(in_array($params[0], array('rtsp', 'mms'))) {
			$mediaid = 'media_'.random(3);
			return $params[0] == 'rtsp' ? '<object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="'.$width.'" height="'.$height.'"><param name="autostart" value="0" /><param name="src" value="'.$url.'" /><param name="controls" value="imagewindow" /><param name="console" value="'.$mediaid.'_" /><embed src="'.$url.'" autostart="0" type="audio/x-pn-realaudio-plugin" controls="imagewindow" console="'.$mediaid.'_" width="'.$width.'" height="'.$height.'"></embed></object><br /><object classid="clsid:CFCDAA03-8BE4-11CF-B84B-0020AFBBCCFA" width="'.$width.'" height="32"><param name="src" value="'.$url.'" /><param name="controls" value="controlpanel" /><param name="console" value="'.$mediaid.'_" /><embed src="'.$url.'" autostart="0" type="audio/x-pn-realaudio-plugin" controls="controlpanel" console="'.$mediaid.'_" width="'.$width.'" height="32"></embed></object>' : '<object classid="clsid:6BF52A52-394A-11d3-B153-00C04F79FAA6" width="'.$width.'" height="'.$height.'"><param name="invokeURLs" value="0"><param name="autostart" value="0" /><param name="url" value="'.$url.'" /><embed src="'.$url.'" autostart="0" type="application/x-mplayer2" width="'.$width.'" height="'.$height.'"></embed></object>';
		}
		$audio = array('aac', 'flac', 'ogg', 'mp3', 'm4a', 'weba', 'wma', 'mid', 'wav', 'ra', 'ram');
		$video = array('rm', 'rmvb', 'flv', 'swf', 'asf', 'asx', 'wmv', 'avi', 'mpg', 'mpeg', 'mp4', 'm4v', '3gp', 'ogv', 'webm', 'mov');
		if (in_array($type, $audio)) {
			return parseaudio($url, $width);
		} else if (in_array($type, $video)) {
			$randomid = random(3);
			return '<ignore_js_op><div id="'.$type.'_'.$randomid.'" class="media"><div id="'.$type.'_'.$randomid.'_container" class="media_container"></div><div id="'.$type.'_'.$randomid.'_tips" class="media_tips"><a href="'.$url.'" target="_blank">'.lang('template', 'parse_av_tips').'</a></div></div><script type="text/javascript">detectPlayer("'.$type.'_'.$randomid.'", "'.$type.'", "'.$url.'", "'.$width.'", "'.$height.'");</script></ignore_js_op>';
		} else {
			return '<a href="'.$url.'" target="_blank">'.$url.'</a>';
		}
	}
	return;
}

function bbcodeurl($url, $tags) {
	if(!preg_match("/<.+?>/s", $url)) {
		if(!in_array(strtolower(substr($url, 0, 6)), array('http:/', 'https:', 'ftp://', 'rtsp:/', 'mms://')) && !preg_match('/^static\//', $url) && !preg_match('/^data\//', $url)) {
			$url = 'http://'.$url;
		}
		return str_replace(array('submit', 'member.php?mod=logging'), array('', ''), str_replace('{url}', addslashes($url), $tags));
	} else {
		return '&nbsp;'.$url;
	}
}

function jammer() {
	$randomstr = '';
	for($i = 0; $i < mt_rand(5, 15); $i++) {
		$randomstr .= chr(mt_rand(32, 59)).' '.chr(mt_rand(63, 126));
	}
	return mt_rand(0, 1) ? '<font class="jammer">'.$randomstr.'</font>'."\r\n" :
		"\r\n".'<span style="display:none">'.$randomstr.'</span>';
}

function highlightword($text, $words, $prepend) {
	$text = str_replace('\"', '"', $text);
	foreach($words AS $key => $replaceword) {
		$text = str_replace($replaceword, '<highlight>'.$replaceword.'</highlight>', $text);
	}
	return "$prepend$text";
}

function parseflv($url, $width = 0, $height = 0) {
	global $_G;
	$lowerurl = strtolower($url);
	$flv = $iframe = $imgurl = '';
	if(empty($_G['setting']['parseflv']) || !is_array($_G['setting']['parseflv'])) {
		return FALSE;
	}

	foreach($_G['setting']['parseflv'] as $script => $checkurl) {
		$check = FALSE;
		foreach($checkurl as $row) {
			if(strpos($lowerurl, $row) !== FALSE) {
			    $check = TRUE;
			    break;
			}
		}
		if($check) {
			@include_once libfile('media/'.$script, 'function');
			if(function_exists('media_'.$script)) {
			    list($flv, $iframe, $url, $imgurl) = call_user_func('media_'.$script, $url, $width, $height);
			}
			break;
		}
	}
	if($flv || $iframe) {
		if(!$width && !$height) {
			return array('flv' => $flv, 'iframe' => $iframe, 'imgurl' => $imgurl);
		} else {
			$width = addslashes($width);
			$height = addslashes($height);
			$flv = addslashes($flv);
			$iframe = addslashes($iframe);
			$randomid = 'flv_'.random(3);
			$player_iframe = $iframe ? "\"<iframe src='$iframe' border='0' scrolling='no' framespacing='0' allowfullscreen='true' style='max-width: 100%' width='$width' height='$height' frameborder='no'></iframe>\"" : '';
			$player_flv = $flv ? "AC_FL_RunContent('width', '$width', 'height', '$height', 'allowNetworking', 'internal', 'allowScriptAccess', 'never', 'src', '$flv', 'quality', 'high', 'bgcolor', '#ffffff', 'wmode', 'transparent', 'allowfullscreen', 'true')" : '';
			$player = (!empty($player_iframe) && !empty($player_flv)) ? "detectHtml5Support() ? $player_iframe : $player_flv" : (empty($player_iframe) ? $player_flv : $player_iframe);
			return '<ignore_js_op><span id="'.$randomid.'"></span><script type="text/javascript" reload="1">document.getElementById(\''.$randomid.'\').innerHTML=('.$player.');</script></ignore_js_op>';
		}
	} else {
		return FALSE;
	}
}

function parseimg($width, $height, $src, $lazyload, $pid, $extra = '') {
	global $_G;
	static $styleoutput = null;
	if($_G['setting']['domainwhitelist_affectimg']) {
		$tmp = parse_url($src);
		if(!empty($tmp['host']) && !iswhitelist($tmp['host'])) {
			return $src;
		}
	}
	if(strstr($src, 'file:') || substr($src, 1, 1) == ':') {
		return $src;
	}
	if($width > $_G['setting']['imagemaxwidth']) {
		$height = intval($_G['setting']['imagemaxwidth'] * $height / $width);
		$width = $_G['setting']['imagemaxwidth'];
		if(defined('IN_MOBILE')) {
			$extra = '';
		} else {
			$extra = 'onmouseover="img_onmouseoverfunc(this)" onclick="zoom(this)" style="cursor:pointer"';
		}
	}
	$attrsrc = !IS_ROBOT && $lazyload ? 'file' : 'src';
	$rimg_id = random(5);
	$GLOBALS['aimgs'][$pid][] = $rimg_id;
	$guestviewthumb = !empty($_G['setting']['guestviewthumb']['flag']) && empty($_G['uid']);
	$img = '';
	if($guestviewthumb) {
		if(!isset($styleoutput)) {
			$img .= guestviewthumbstyle();
			$styleoutput = true;
		}
		$img .= '<div class="guestviewthumb"><img id="aimg_'.$rimg_id.'" class="guestviewthumb_cur" onclick="showWindow(\'login\', \'{loginurl}\'+\'&referer=\'+encodeURIComponent(location))" '.$attrsrc.'="{url}" border="0" alt="" />
				<br><a href="{loginurl}" onclick="showWindow(\'login\', this.href+\'&referer=\'+encodeURIComponent(location));">'.lang('forum/template', 'guestviewthumb').'</a></div>';

	} else {
		if(defined('IN_MOBILE')) {
			$img = '<img'.($width > 0 ? ' width="'.$width.'"' : '').($height > 0 ? ' height="'.$height.'"' : '').' src="{url}" border="0" alt="" />';
		} else {
			$img = '<img id="aimg_'.$rimg_id.'" onclick="zoom(this, this.src, 0, 0, '.($_G['setting']['showexif'] ? 1 : 0).')" class="zoom"'.($width > 0 ? ' width="'.$width.'"' : '').($height > 0 ? ' height="'.$height.'"' : '').' '.$attrsrc.'="{url}" '.($extra ? $extra.' ' : '').'border="0" alt="" />';
		}
	}
	$code = bbcodeurl($src, $img);
	if($guestviewthumb) {
		$code = str_replace('{loginurl}', 'member.php?mod=logging&action=login', $code);
	}
	return $code;
}

function parsesmiles(&$message) {
	global $_G;
	static $enablesmiles;
	if($enablesmiles === null) {
		$enablesmiles = false;
		if(!empty($_G['cache']['smilies']) && is_array($_G['cache']['smilies'])) {
			foreach($_G['cache']['smilies']['replacearray'] AS $key => $smiley) {
				$_G['cache']['smilies']['replacearray'][$key] = '<img src="'.STATICURL.'image/smiley/'.$_G['cache']['smileytypes'][$_G['cache']['smilies']['typearray'][$key]]['directory'].'/'.$smiley.'" smilieid="'.$key.'" border="0" alt="" />';
			}
			$enablesmiles = true;
		}
	}
	$enablesmiles && $message = preg_replace($_G['cache']['smilies']['searcharray'], $_G['cache']['smilies']['replacearray'], $message, $_G['setting']['maxsmilies']);
	return $message;
}

function parsepostbg($bgimg, $pid) {
	global $_G;
	static $postbg;
	if($postbg[$pid]) {
		return '';
	}
	loadcache('postimg');
	foreach($_G['cache']['postimg']['postbg'] as $postbg) {
		if($postbg['url'] != $bgimg) {
			continue;
		}
		$bgimg = dhtmlspecialchars(basename($bgimg), ENT_QUOTES);
		$postbg[$pid] = true;
		$_G['forum_posthtml']['header'][$pid] .= '<style type="text/css">#pid'.$pid.'{background-image:url("'.STATICURL.'image/postbg/'.$bgimg.'");}</style>';
		break;
	}
	return '';
}

function parsepassword($password, $pid) {
	global $_G;
	static $postpw;
	if($postpw[$pid]) {
		return '';
	}
	$postpw[$pid] = true;
	if(empty($_G['cookie']['postpw_'.$pid]) || $_G['cookie']['postpw_'.$pid] != md5($password)) {
		$_G['forum_discuzcode']['passwordlock'][$pid] = 1;
	}
	return '';
}

function guestviewthumbstyle() {
	static $styleoutput = null;
	$return = '';
	if ($styleoutput === null) {
		global $_G;
		$return = '<style>.guestviewthumb {margin:10px auto; text-align:center;}.guestviewthumb a {font-size:12px;}.guestviewthumb_cur {cursor:url("'.IMGDIR.'/scf.gif"), default; max-width:'.$_G['setting']['guestviewthumb']['width'].'px;}.ie6 .guestviewthumb_cur { width:'.$_G['setting']['guestviewthumb']['width'].'px !important;}</style>';
		$styleoutput = true;
	}
	return $return;
}
?>