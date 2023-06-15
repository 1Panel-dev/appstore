<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: home_editor.php 35193 2015-02-02 02:15:19Z hypowang $
 */
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

if (empty($_GET['charset']) || !in_array(strtolower($_GET['charset']), array('gbk', 'big5', 'utf-8')))
	$_GET['charset'] = '';
$allowhtml = empty($_GET['allowhtml']) ? 0 : 1;

$doodle = empty($_GET['doodle']) ? 0 : 1;
$isportal = empty($_GET['isportal']) ? 0 : 1;
if (empty($_GET['op'])) {
	?>
	<html>
		<head>
			<meta http-equiv="Content-Type" content="text/html;charset=<?php echo $_GET['charset']; ?>" />
			<title>Editor</title>
			<script type="text/javascript" src="static/js/common.js"></script>
			<script type="text/javascript" src="static/js/home.js"></script>
			<script language="javascript" src="static/image/editor/editor_base.js"></script>
			<style type="text/css">
				body{margin:0;padding:0;}
				body, td, input, button, select, textarea {font: 12px/1.5em Tahoma, Arial, Helvetica, snas-serif;}
				textarea { resize: none; font-size: 14px; line-height: 1.8em; }
				.submit { padding: 0 10px; height: 22px; border: 1px solid; border-color: #DDD #264F6E #264F6E #DDD; background: #2782D6; color: #FFF; line-height: 20px; letter-spacing: 1px; cursor: pointer; }
				a.dm{text-decoration:none}
				a.dm:hover{text-decoration:underline}
				a{font-size:12px}
				img{border:0}
				td.icon{width:24px;height:24px;text-align:center;vertical-align:middle}
				td.sp{width:8px;height:24px;text-align:center;vertical-align:middle}
				td.xz{width:47px;height:24px;text-align:center;vertical-align:middle}
				td.bq{width:49px;height:24px;text-align:center;vertical-align:middle}
				div a.n{height:16px;line-height:16px;display:block;padding:2px;color:#000000;text-decoration:none}
				div a.n:hover{background:#E5E5E5}
				.r_op { float: right; }
				.eMenu{position:absolute;margin-top: -2px;background:#FFFFFF;border:1px solid #C5C5C5;padding:4px}
				.eMenu ul, .eMenu ul li { margin: 0; padding: 0; }
				.eMenu ul li{list-style: none;float:left}
				#editFaceBox { padding: 5px; }
				#editFaceBox li { width: 25px; height: 25px; overflow: hidden; }
				.t_input { padding: 3px 2px; border-style: solid; border-width: 1px; border-color: #7C7C7C #C3C3C3 #DDD; line-height: 16px; }
				a.n1{height:16px;line-height:16px;display:block;padding:2px;color:#000000;text-decoration:none}
				a.n1:hover{background:#E5E5E5}
				a.cs{height:15px;position:relative}
				*:lang(zh) a.cs{height:12px}
				.cs .cb{font-size:0;display:block;width:10px;height:8px;position:absolute;left:4px;top:3px;cursor:hand!important;cursor:pointer}
				.cs span{position:absolute;left:19px;top:0px;cursor:hand!important;cursor:pointer;color:#333}

				.fRd1 .cb{background-color:#800}
				.fRd2 .cb{background-color:#800080}
				.fRd3 .cb{background-color:#F00}
				.fRd4 .cb{background-color:#F0F}
				.fBu1 .cb{background-color:#000080}
				.fBu2 .cb{background-color:#00F}
				.fBu3 .cb{background-color:#0FF}
				.fGn1 .cb{background-color:#008080}
				.fGn2 .cb{background-color:#008000}
				.fGn3 .cb{background-color:#808000}
				.fGn4 .cb{background-color:#0F0}
				.fYl1 .cb{background-color:#FC0}
				.fBk1 .cb{background-color:#000}
				.fBk2 .cb{background-color:#808080}
				.fBk3 .cb{background-color:#C0C0C0}
				.fWt0 .cb{background-color:#FFF;border:1px solid #CCC}

				.mf_nowchose{height:30px;background-color:#DFDFDF;border:1px solid #B5B5B5;border-left:none}
				.mf_other{height:30px;border-left:1px solid #B5B5B5}
				.mf_otherdiv{height:30px;width:30px;border:1px solid #FFF;border-right-color:#D6D6D6;border-bottom-color:#D6D6D6;background-color:#F8F8F8}
				.mf_otherdiv2{height:30px;width:30px;border:1px solid #B5B5B5;border-left:none;border-top:none}
				.mf_link{font-size:12px;color:#000000;text-decoration:none}
				.mf_link:hover{font-size:12px;color:#000000;text-decoration:underline}

				.ico{height:24px;width:24px;vertical-align:middle;text-align:center}
				.ico2{height:24px;width:27px;vertical-align:middle;text-align:center}
				.ico3{height:24px;width:25px;vertical-align:middle;text-align:center}
				.ico4{height:24px;width:8px;vertical-align:middle;text-align:center}

				.edTb { background: #F2F2F2; }
				.icons a,.sepline,.switch{background-image:url(static/image/editor/editor.gif)}

				.toobar, .toobarmini{position:relative;height:26px;overflow:hidden}
				.toobarmini .icoSwitchTxt, .toobarmini .tble{ display: none !important;}
				.toobar .icoSwitchMdi{ display: none;}

				.tble{position:absolute;left:0;top:2px }
				*:lang(zh) .tble{top:2px}
				.tbri{width:60px;position:absolute;right:3px;top:2px;}

				.icons a{width:20px;height:20px;background-repeat:no-repeat;display:block;float:left;border:1px solid #F2F2F2;}
				*:lang(zh) .icons a{margin-right:1px}
				.icons a:hover{border-color: #369 #CCC;background-color:#FFF}
				a.icoCut{background-position:-140px -60px;}
				a.icoCpy{background-position:-160px -60px;}
				a.icoPse{background-position:-40px -60px}
				a.icoFfm{background-position:-100px 0}
				a.icoFsz{background-position:-120px 0;}
				a.icoWgt{background-position:0 0;}
				a.icoIta{background-position:-20px 0;}
				a.icoUln{background-position:-40px 0;}
				a.icoAgn{background-position:-60px 0}
				a.icoAgL{background-position:-80px -20px}
				a.icoAgC{background-position:-240px -40px}
				a.icoAgR{background-position:-260px -40px}
				a.icoLst{background-position:-100px -20px}
				a.icoOdt{background-position:-180px -60px}
				a.icoIdt{background-position:-180px -60px}
				a.icoFcl{background-position:-60px 0}
				a.icoBcl{background-position:-80px 0}
				a.icoUrl{background-position:-40px -20px;}
				a.icoMoveUrl{background-position:-60px -20px}
				a.icoRenew {background-position:-180px -40px}
				a.icoFace {background-position:-20px -20px}
				a.icoPage {background-position:-200px -60px}
				a.icoDown {background-position:-80px -60px}
				a.icoDoodle {background-position:-260px -60px}
				a.icoImg{background-position:0 -20px}
				a.icoAttach{background-position:-200px -20px}
				a.icoSwf{background-position:-240px -20px}
				a.icoSwitchTxt{background-position:-220px -60px;float:right}
				a.icoFullTxt{ float: right; width: 35px; height: 20px; line-height: 20px; border: 1px solid #C2D5E3; background: url(static/image/common/card_btn.png) repeat-x 0 100%; text-align: center; color: #333; text-decoration: none; }
				a.icoSwitchMdi{background-position:-239px -60px;float:right}


				.edTb{border-bottom:1px solid #c5c5c5;background-position:0 -28px}
				.sepline{width:4px;height:20px;margin-top:2px;margin-right:3px;background-position:-476px 0;background-repeat:no-repeat;float:left }
			</style>
			<script language="JavaScript">
				function fontname(obj){format('fontname',obj.innerHTML);obj.parentNode.style.display='none'}
				function fontsize(size,obj){format('fontsize',size);obj.parentNode.style.display='none'}
			</script>
		</head>
		<body style="overflow-y:hidden">
			<div >

				<table cellpadding="0" cellspacing="0" width="100%" height="100%">
					<tr>
						<td height="31">
							<table width="100%" border="0" cellpadding="0" cellspacing="0" class="edTb">
								<tr>
									<td height="31" style="padding-left:3px">

										<div class="toobar" id="dvToolbar">
											<div class="icons tble">
												<a href="javascript:;" class="icoCut" title="<?php echo lang('home/editor', 'editor_cut'); ?>" onClick="format('Cut');return false;"></a>
												<a href="javascript:;" class="icoCpy" title="<?php echo lang('home/editor', 'editor_copy'); ?>" onClick="format('Copy');return false;"></a>
												<a href="javascript:;" class="icoPse" title="<?php echo lang('home/editor', 'editor_paste'); ?>" onClick="format('Paste');return false;"></a>
												<div class="sepline"></div>
												<a href="javascript:;" class="icoFfm" id="imgFontface" title="<?php echo lang('home/editor', 'editor_font'); ?>" onClick="fGetEv(event);fDisplayElement('fontface','');return false;"></a>
												<a href="javascript:;" class="icoFsz" id="imgFontsize" title="<?php echo lang('home/editor', 'editor_fontsize'); ?>" onClick="fGetEv(event);fDisplayElement('fontsize','');return false;"></a>
												<a href="javascript:;" class="icoWgt" onClick="format('Bold');return false;" title="<?php echo lang('home/editor', 'editor_fontbold'); ?>"></a>
												<a href="javascript:;" class="icoIta" title="<?php echo lang('home/editor', 'editor_fontitalic'); ?>" onClick="format('Italic');return false;"></a>
												<a href="javascript:;" class="icoUln" onClick="format('Underline');return false;" title="<?php echo lang('home/editor', 'editor_fontunderline'); ?>"></a>
												<a href="javascript:;" class="icoFcl" title="<?php echo lang('home/editor', 'editor_funtcolor'); ?>" onClick="foreColor(event);return false;" id="imgFontColor"></a>
												<a href="javascript:;" class="icoAgL" id="imgJustifyleft" onClick="fGetEv(event);format('Justifyleft');return false;" title="<?php echo lang('home/editor', 'editor_align_left'); ?>"></a>
												<a href="javascript:;" class="icoAgC" id="imgJustifycenter" onClick="fGetEv(event);format('Justifycenter');return false;" title="<?php echo lang('home/editor', 'editor_align_center'); ?>"></a>
												<a href="javascript:;" class="icoAgR" id="imgJustifyright" onClick="fGetEv(event);format('Justifyright');return false;" title="<?php echo lang('home/editor', 'editor_align_right'); ?>"></a>

												<a href="javascript:;" class="icoLst" id="imgList" onClick="fGetEv(event);fDisplayElement('divList','');return false;"title="<?php echo lang('home/editor', 'editor_list'); ?>"></a>
												<a href="javascript:;" class="icoOdt" id="imgInOut" onClick="fGetEv(event);fDisplayElement('divInOut','');return false;" title="<?php echo lang('home/editor', 'editor_indent'); ?>"></a>
												<div class="sepline"></div>
												<a href="javascript:;" class="icoUrl" id="icoUrl" onClick="createLink(event, 1);return false;" title="<?php echo lang('home/editor', 'editor_hyperlink'); ?>"></a>
												<a href="javascript:;" class="icoMoveUrl" onClick="clearLink();return false;" title="<?php echo lang('home/editor', 'editor_remove_link'); ?>"></a>
												<a href="javascript:;" class="icoImg" id="icoImg" onClick="parent.createImageBox(<?php echo ($isportal ? 'parent.check_catid' : '')?>);return false;" title="<?php echo lang('home/editor', 'editor_link_image'); ?>"></a>
	<?php if ($isportal) { ?>
													<a href="javascript:;" class="icoAttach" id="icoAttach" onClick="parent.createAttachBox(<?php echo ($isportal ? 'parent.check_catid' : '')?>);return false;" title="<?php echo lang('home/editor', 'editor_link_attach'); ?>"></a>
												<?php } ?>
												<a href="javascript:;" class="icoSwf" id="icoSwf" onClick="createFlash(event, 1);return false;" title="<?php echo lang('home/editor', 'editor_link_flash'); ?>"></a>
												<a href="javascript:;" class="icoFace" id="faceBox" onClick="faceBox(event);return false;" title="<?php echo lang('home/editor', 'editor_insert_smiley'); ?>"></a>
	<?php if ($doodle) { ?>
													<a href="javascript:;" class="icoDoodle" id="doodleBox" onClick="doodleBox(event, this.id);return false;" title="<?php echo lang('home/editor', 'editor_doodle'); ?>"></a>
												<?php } ?>
												<?php if ($isportal) { ?>
													<a href="javascript:;" class="icoPage" id="icoPage" onClick="pageBreak(event, 1);return false;" title="<?php echo lang('home/editor', 'editor_pagebreak'); ?>"></a>
													<a href="javascript:;" class="icoDown" id="icoDown" onClick="parent.downRemoteFile();return false;" title="<?php echo lang('home/editor', 'editor_download_remote'); ?>"></a>
	<?php } ?>
												<a href="javascript:;" class="icoRenew" onClick="renewContent();return false;" title="<?php echo lang('home/editor', 'editor_restore'); ?>"></a>
												<?php if ($allowhtml) { ?>
													<input type="checkbox" value="1" name="switchMode" id="switchMode" style="float:left;margin-top:6px!important;margin-top:2px" onClick="setMode(this.checked)" onMouseOver="fSetModeTip(this)" onMouseOut="fHideTip()">
												<?php } else { ?>
													<input type="hidden" value="1" name="switchMode" id="switchMode">
												<?php } ?>

											</div>
											<div class="icons tbri">
												<a href="javascript:;" class="icoSwitchMdi" title="<?php echo lang('home/editor', 'editor_switch_media'); ?>" onClick="changeEditType(true, event);return false;"></a>
												<a href="javascript:;" class="icoSwitchTxt" title="<?php echo lang('home/editor', 'editor_switch_text'); ?>" onClick="changeEditType(false, event);return false;"></a>
												<a href="javascript:;" class="icoFullTxt" onClick="changeEditFull(true, event);return false;"><?php echo lang('home/editor', 'editor_full_screen'); ?></a>
											</div>
										</div>

									</td>
								</tr>
							</table>

							<div style="width:100px;height:100px;position:absolute;display:none;top:-500px;left:-500px" ID="dvPortrait"></div>
							<div id="fontface" class="eMenu" style="z-index:99;display:none;top:35px;left:2px;width:110px;height:265px">
								<a href="javascript:;" onClick="fontname(this);return false;" class="n" style="font:normal 12px '<?php echo lang('home/editor', 'editor_font_song'); ?>';"><?php echo lang('home/editor', 'editor_font_song'); ?></a>
								<a href="javascript:;" onClick="fontname(this);return false;" class="n" style="font:normal 12px '<?php echo lang('home/editor', 'editor_font_hei'); ?>';"><?php echo lang('home/editor', 'editor_font_hei'); ?></a>
								<a href="javascript:;" onClick="fontname(this);return false;" class="n" style="font:normal 12px '<?php echo lang('home/editor', 'editor_font_kai'); ?>';"><?php echo lang('home/editor', 'editor_font_kai'); ?></a>
								<a href="javascript:;" onClick="fontname(this);return false;" class="n" style="font:normal 12px '<?php echo lang('home/editor', 'editor_font_li'); ?>';"><?php echo lang('home/editor', 'editor_font_li'); ?></a>
								<a href="javascript:;" onClick="fontname(this);return false;" class="n" style="font:normal 12px '<?php echo lang('home/editor', 'editor_font_you'); ?>';"><?php echo lang('home/editor', 'editor_font_you'); ?></a>
								<a href="javascript:;" onClick="fontname(this);return false;" class="n" style="font:normal 12px Arial;">Arial</a>
								<a href="javascript:;" onClick="fontname(this);return false;" class="n" style="font:normal 12px 'Arial Narrow';">Arial Narrow</a>
								<a href="javascript:;" onClick="fontname(this);return false;" class="n" style="font:normal 12px 'Arial Black';">Arial Black</a>
								<a href="javascript:;" onClick="fontname(this);return false;" class="n" style="font:normal 12px 'Comic Sans MS';">Comic Sans MS</a>
								<a href="javascript:;" onClick="fontname(this);return false;" class="n" style="font:normal 12px Courier;">Courier</a>
								<a href="javascript:;" onClick="fontname(this);return false;" class="n" style="font:normal 12px System;">System</a>
								<a href="javascript:;" onClick="fontname(this);return false;" class="n" style="font:normal 12px 'Times New Roman';">Times New Roman</a>
								<a href="javascript:;" onClick="fontname(this);return false;" class="n" style="font:normal 12px Verdana;">Verdana</a>
							</div>
							<div id="fontsize" class="eMenu" style="display:none;top:35px;left:26px;width:125px;height:120px">
								<a href="javascript:;" onClick="fontsize(1,this);return false;" class="n" style="font-size:xx-small;line-height:120%;"><?php echo lang('home/editor', 'editor_fontsize_xxsmall'); ?></a>
								<a href="javascript:;" onClick="fontsize(2,this);return false;" class="n" style="font-size:x-small;line-height:120%;"><?php echo lang('home/editor', 'editor_fontsize_xsmall'); ?></a>
								<a href="javascript:;" onClick="fontsize(3,this);return false;" class="n" style="font-size:small;line-height:120%;"><?php echo lang('home/editor', 'editor_fontsize_small'); ?></a>
								<a href="javascript:;" onClick="fontsize(4,this);return false;" class="n" style="font-size:medium;line-height:120%;"><?php echo lang('home/editor', 'editor_fontsize_medium'); ?></a>
								<a href="javascript:;" onClick="fontsize(5,this);return false;" class="n" style="font-size:large;line-height:120%;"><?php echo lang('home/editor', 'editor_fontsize_large'); ?></a>
							</div>

							<div id="divList" class="eMenu" style="display:none;top:35px;left:26px;width:64px;height:40px;"><a href="javascript:;" onClick="format('Insertorderedlist');fHide(this.parentNode);return false;" class="n"><?php echo lang('home/editor', 'editor_list_order'); ?></a><a href="javascript:;" onClick="format('Insertunorderedlist');fHide(this.parentNode);return false;" class="n"><?php echo lang('home/editor', 'editor_list_unorder'); ?></a></div>
							<div id="divInOut" class="eMenu" style="display:none;top:35px;left:26px;width:64px;height:40px;"><a href="javascript:;" onClick="format('Indent');fHide(this.parentNode);return false;" class="n"><?php echo lang('home/editor', 'editor_indent_inc'); ?></a><a href="javascript:;" onClick="format('Outdent');fHide(this.parentNode);return false;" class="n"><?php echo lang('home/editor', 'editor_indent_dec'); ?></a></div>

							<div id="dvForeColor" class="eMenu" style="display:none;top:35px;left:26px;width:90px;">
								<a href="javascript:;" onClick="format(gSetColorType,'#800000');return false;" class="n cs fRd1"><b class="cb"></b><span><?php echo lang('home/editor', 'editor_color_darkred'); ?></span></a>
								<a href="javascript:;" onClick="format(gSetColorType,'#800080');return false;" class="n cs fRd2"><b class="cb"></b><span><?php echo lang('home/editor', 'editor_color_purple'); ?></span></a>
								<a href="javascript:;" onClick="format(gSetColorType,'#F00000');return false;" class="n cs fRd3"><b class="cb"></b><span><?php echo lang('home/editor', 'editor_color_red'); ?></span></a>
								<a href="javascript:;" onClick="format(gSetColorType,'#F000F0');return false;" class="n cs fRd4"><b class="cb"></b><span><?php echo lang('home/editor', 'editor_color_pink'); ?></span></a>
								<a href="javascript:;" onClick="format(gSetColorType,'#000080');return false;" class="n cs fBu1"><b class="cb"></b><span><?php echo lang('home/editor', 'editor_color_darkblue'); ?></span></a>
								<a href="javascript:;" onClick="format(gSetColorType,'#0000F0');return false;" class="n cs fBu2"><b class="cb"></b><span><?php echo lang('home/editor', 'editor_color_blue'); ?></span></a>
								<a href="javascript:;" onClick="format(gSetColorType,'#00F0F0');return false;" class="n cs fBu3"><b class="cb"></b><span><?php echo lang('home/editor', 'editor_color_lakeblue'); ?></span></a>
								<a href="javascript:;" onClick="format(gSetColorType,'#008080');return false;" class="n cs fGn1"><b class="cb"></b><span><?php echo lang('home/editor', 'editor_color_greenblue'); ?></span></a>
								<a href="javascript:;" onClick="format(gSetColorType,'#008000');return false;" class="n cs fGn2"><b class="cb"></b><span><?php echo lang('home/editor', 'editor_color_green'); ?></span></a>
								<a href="javascript:;" onClick="format(gSetColorType,'#808000');return false;" class="n cs fGn3"><b class="cb"></b><span><?php echo lang('home/editor', 'editor_color_olives'); ?></span></a>
								<a href="javascript:;" onClick="format(gSetColorType,'#00F000');return false;" class="n cs fGn4"><b class="cb"></b><span><?php echo lang('home/editor', 'editor_color_lightgreen'); ?></span></a>
								<a href="javascript:;" onClick="format(gSetColorType,'#F0C000');return false;" class="n cs fYl1"><b class="cb"></b><span><?php echo lang('home/editor', 'editor_color_orange'); ?></span></a>
								<a href="javascript:;" onClick="format(gSetColorType,'#000000');return false;" class="n cs fBk1"><b class="cb"></b><span><?php echo lang('home/editor', 'editor_color_black'); ?></span></a>
								<a href="javascript:;" onClick="format(gSetColorType,'#808080');return false;" class="n cs fBk2"><b class="cb"></b><span><?php echo lang('home/editor', 'editor_color_grey'); ?></span></a>
								<a href="javascript:;" onClick="format(gSetColorType,'#C0C0C0');return false;" class="n cs fBk3"><b class="cb"></b><span><?php echo lang('home/editor', 'editor_color_silver'); ?></span></a>
								<a href="javascript:;" onClick="format(gSetColorType,'#FFFFFF');return false;" class="n cs fWt0"><b class="cb"></b><span><?php echo lang('home/editor', 'editor_color_white'); ?></span></a>
							</div>

							<div id="editFaceBox" class="eMenu" style="display:none;top:35px;left:26px;width:165px;"></div>

							<div id="createUrl" class="eMenu" style="display:none;top:35px;left:26px;width:300px;font-size:12px">
	<?php echo lang('home/editor', 'editor_prompt_textlink'); ?>:<br/>
								<input type="text" id="insertUrl" name="url" value="http://" onfocus="checkURL(this, 1);" onblur="checkURL(this, 0);" class="t_input" style="width: 190px;"> <input type="button" onclick="createLink();" name="createURL" value="<?php echo lang('home/editor', 'editor_ok'); ?>" class="submit" /> <a href="javascript:;" onclick="fHide($('createUrl'));return false;"><?php echo lang('home/editor', 'editor_cancel'); ?></a>
							</div>
							<div id="createImg" class="eMenu" style="display:none;top:35px;left:26px;width:300px;font-size:12px">
	<?php echo lang('home/editor', 'editor_prompt_imagelink'); ?>:<br/>
								<input type="text" id="imgUrl" name="imgUrl" value="http://" onfocus="checkURL(this, 1);" onblur="checkURL(this, 0);" class="t_input" style="width: 190px;" /> <input type="button" onclick="createImg();" name="createURL" value="<?php echo lang('home/editor', 'editor_ok'); ?>" class="submit" /> <a href="javascript:;" onclick="fHide($('createImg'));return false;"><?php echo lang('home/editor', 'editor_cancel'); ?></a>
							</div>
							<div id="createSwf" class="eMenu" style="display:none;top:35px;left:26px;width:400px;font-size:12px">
	<?php echo lang('home/editor', 'editor_prompt_videolink'); ?>:<br/>
								<select name="vtype" id="vtype">
									<option value="0"><?php echo lang('home/editor', 'editor_prompt_video_flash'); ?></option>
									<option value="1"><?php echo lang('home/editor', 'editor_prompt_video_media'); ?></option>
									<option value="2"><?php echo lang('home/editor', 'editor_prompt_video_real'); ?></option>
									<option value="3"><?php echo lang('home/editor', 'editor_prompt_mp3'); ?></option>
								</select>
								<input type="text" id="videoUrl" name="videoUrl" value="http://" onfocus="checkURL(this, 1);" onblur="checkURL(this, 0);" class="t_input" style="width: 200px;" />
								<input type="button" onclick="createFlash();" name="createURL" value="<?php echo lang('home/editor', 'editor_ok'); ?>" class="submit" />
								<a href="javascript:;" onclick="fHide($('createSwf'));return false;"><?php echo lang('home/editor', 'editor_cancel'); ?></a>
							</div>
							<div id="createPage" class="eMenu" style="display:none;top:35px;left:26px;width:300px;font-size:12px">
	<?php echo lang('home/editor', 'editor_prompt_pagetitle'); ?>:<br/>
								<input type="text" id="pageTitle" name="pageTitle" value="" class="t_input" style="width: 190px;" /> <input type="button" onclick="pageBreak();" name="createURL" value="<?php echo lang('home/editor', 'editor_ok'); ?>" class="submit" /> <a href="javascript:;" onclick="fHide($('createPage'));return false;"><?php echo lang('home/editor', 'editor_cancel'); ?></a>
							</div>

						</td></tr>
					<tr><td>
							<textarea id="dvtext" style="overflow-y:auto; margin-top: 0; padding:0px 4px 4px;width:100%;height:100%;word-wrap:break-word;border:0;display:none;"></textarea>
							<div id="dvhtml" style="height:100%;width:100%;overflow:hidden">
								<SCRIPT LANGUAGE="JavaScript">
									function blank_load() {
										var inihtml = '';
										var obj = parent.document.getElementById('uchome-ttHtmlEditor');
										if(obj) {
											inihtml = obj.value;
										}
										if(! inihtml && !window.Event) {
											inihtml = '<div></div>';
										}
										window.frames['HtmlEditor'].document.body.innerHTML = inihtml;
									}
									document.write('<div id="divEditor" style="padding-left:4px;height:100%;background-color:#fff"><IFRAME class="HtmlEditor" ID="HtmlEditor" name="HtmlEditor" style="height:100%;width:100%;" frameBorder="0" marginHeight=0 marginWidth=0 src="home.php?mod=editor&op=blank&charset=<?php echo $_GET['charset']; ?>" onload="blank_load();"></IFRAME></div>');

								</SCRIPT>
								<textarea id="sourceEditor" style="overflow-y:auto;padding-left:4px;width:100%;height:100%;word-wrap:break-word;display:none;border:0;"></textarea>
							</div>
						</td>
					</tr>
				</table>
			</div>
			<input type="hidden" name="uchome-editstatus" id="uchome-editstatus" value="html">
		</body>
	</html>
	<?php
} else {
	?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
	<HTML>
		<HEAD>
			<meta http-equiv="Content-Type" content="text/html;charset=<?php echo $_GET['charset']; ?>" />
			<title>New Document</title>
			<style>
				body { margin: 0; padding: 0; word-wrap: break-word; font-size:14px; line-height:1.8em; font-family: Tahoma, Arial, Helvetica, snas-serif; }
			</style>
			<meta content="mshtml 6.00.2900.3132" name=generator>
		</head>
		<body>
		</body>
	</html>
<?php
}?>