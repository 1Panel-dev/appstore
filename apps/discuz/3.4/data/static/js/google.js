/*
	[Discuz!] (C)2001-2099 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: google.js 23838 2011-08-11 06:51:58Z monkey $
*/

document.writeln('<script type="text/javascript">');
document.writeln('function validate_google(theform) {');
document.writeln('	if(theform.site.value == 1) {');
document.writeln('		theform.q.value = \'site:' + google_host + ' \' + theform.q.value;');
document.writeln('	}');
document.writeln('}');
document.writeln('function submitFormWithChannel(channelname) {');
document.writeln('	document.gform.channel.value=channelname;');
document.writeln('	document.gform.submit();');
document.writeln('	return;');
document.writeln('}');
document.writeln('</script>');
document.writeln('<form name="gform" id="gform" method="get" autocomplete="off" action="https://www.google.com/search?" target="_blank" onSubmit="validate_google(this);">');
document.writeln('<input type="hidden" name="client" value="' + (!google_client ? 'aff-discuz' : google_client) + '" />');
document.writeln('<input type="hidden" name="ie" value="' + google_charset + '" />');
document.writeln('<input type="hidden" name="oe" value="UTF-8" />');
document.writeln('<input type="hidden" name="hl" value="' + google_hl + '" />');
document.writeln('<input type="hidden" name="lr" value="' + google_lr + '" />');
document.writeln('<input type="hidden" name="channel" value="search" />');
document.write('<div onclick="javascript:submitFormWithChannel(\'logo\')" style="cursor:pointer;float: left;width:70px;height:23px;background: url(' + STATICURL + 'image/common/Google_small.png) !important;background: none;filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'' + STATICURL+ 'image/common/Google_small.png\', sizingMethod=\'scale\')"><img src="' + STATICURL + 'image/common/none.gif" border="0" alt="Google" /></div>');
document.writeln('&nbsp;&nbsp;<input type="text" class="txt" size="20" name="q" id="q" maxlength="255" value=""></input>');
document.writeln('<select name="site">');
document.writeln('<option value="0"' + google_default_0 + '>网页搜索</option>');
document.writeln('<option value="1"' + google_default_1 + '>站内搜索</option>');
document.writeln('</select>');
document.writeln('&nbsp;<button type="submit" name="sa" value="true">搜索</button>');
document.writeln('</form>');