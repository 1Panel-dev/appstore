<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: function_exif.php 30348 2012-05-24 03:27:54Z zhengqingpeng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

function getimageinfoval($ImageInfo,$val_arr) {
	$InfoVal	=	exif_lang('unknown');
	foreach($val_arr as $name=>$val) {
		if ($name == $ImageInfo) {
			$InfoVal	=	&$val;
			break;
		}
	}
	return $InfoVal;
}

function getexif($img) {

	$imgtype			=	array("", "GIF", "JPG", "PNG", "SWF", "PSD", "BMP", "TIFF(intel byte order)", "TIFF(motorola byte order)", "JPC", "JP2", "JPX", "JB2", "SWC", "IFF", "WBMP", "XBM");
	$Orientation		=	array("", "top left side", "top right side", "bottom right side", "bottom left side", "left side top", "right side top", "right side bottom", "left side bottom");
	$ResolutionUnit		=	exif_lang('resolutionunit');
	$YCbCrPositioning	=	array("", "the center of pixel array", "the datum point");
	$ExposureProgram	=	exif_lang('exposureprogram');
	$MeteringMode_arr	=	exif_lang('meteringmode');
	$Lightsource_arr	=	exif_lang('lightsource');
	$Flash_arr			=	array(
	"0"		=>	"flash did not fire",
	"1"		=>	"flash fired",
	"5"		=>	"flash fired but strobe return light not detected",
	"7"		=>	"flash fired and strobe return light detected",
	);

	if(!function_exists('exif_read_data')) {
		return exif_lang('img_info');
	}
	$exif = @exif_read_data($img,"IFD0");
	if ($exif === false) {
		$new_img_info	=	exif_lang('img_info');
	} else {
		@$exif = exif_read_data($img, 0, true);
		foreach($exif as $type => $typearr) {
			foreach($typearr as $key => $kval) {
				if(is_array($kval)) {
					foreach($kval as $vkey => $value) {
						$str = dhtmlspecialchars(preg_replace("/[^\[A-Za-z0-9_\.\/:\s-\]]/", '', trim($value)));
						$exif[$type][$key][$vkey] = $str;
					}
				} elseif(!in_array($key, array('ComponentsConfiguration', 'FileSource', 'SceneType'))) {
					$str = dhtmlspecialchars(preg_replace("/[^\[A-Za-z0-9_\.\/:\s-\]]/", '', trim($kval)));
					$exif[$type][$key] = $str;
				}
			}
		}
		$new_img_info	=	array (
		exif_lang('FileName')			=>	$exif[FILE][FileName],
		exif_lang('FileType')		=>	$imgtype[$exif[FILE][FileType]],
		exif_lang('MimeType')		=>	$exif[FILE][MimeType],
		exif_lang('FileSize')		=>	$exif[FILE][FileSize],
		exif_lang('FileDateTime')			=>	date("Y-m-d H:i:s",$exif[FILE][FileDateTime]),
		exif_lang('ImageDescription')		=>	$exif[IFD0][ImageDescription],
		exif_lang('Make')			=>	$exif[IFD0][Make],
		exif_lang('Model')			=>	$exif[IFD0][Model],
		exif_lang('Orientation')			=>	$Orientation[$exif[IFD0][Orientation]],
		exif_lang('XResolution')		=>	$exif[IFD0][XResolution].$ResolutionUnit[$exif[IFD0][ResolutionUnit]],
		exif_lang('YResolution')		=>	$exif[IFD0][YResolution].$ResolutionUnit[$exif[IFD0][ResolutionUnit]],
		exif_lang('Software')		=>	$exif[IFD0][Software],
		exif_lang('DateTime')		=>	$exif[IFD0][DateTime],
		exif_lang('Artist')			=>	$exif[IFD0][Artist],
		exif_lang('YCbCrPositioning')	=>	$YCbCrPositioning[$exif[IFD0][YCbCrPositioning]],
		exif_lang('Copyright')			=>	$exif[IFD0][Copyright],
		exif_lang('Photographer')		=>	$exif[COMPUTED][Copyright.Photographer],
		exif_lang('Editor')		=>	$exif[COMPUTED][Copyright.Editor],
		exif_lang('ExifVersion')		=>	$exif[EXIF][ExifVersion],
		exif_lang('FlashPixVersion')	=>	"Ver. ".number_format($exif[EXIF][FlashPixVersion]/100,2),
		exif_lang('DateTimeOriginal')		=>	$exif[EXIF][DateTimeOriginal],
		exif_lang('DateTimeDigitized')		=>	$exif[EXIF][DateTimeDigitized],
		exif_lang('Height')	=>	$exif[COMPUTED][Height],
		exif_lang('Width')	=>	$exif[COMPUTED][Width],
		exif_lang('ApertureValue')			=>	$exif[EXIF][ApertureValue],
		exif_lang('ShutterSpeedValue')		=>	$exif[EXIF][ShutterSpeedValue],
		exif_lang('ApertureFNumber')		=>	$exif[COMPUTED][ApertureFNumber],
		exif_lang('MaxApertureValue')	=>	"F".$exif[EXIF][MaxApertureValue],
		exif_lang('ExposureTime')		=>	$exif[EXIF][ExposureTime],
		exif_lang('FNumber')		=>	$exif[EXIF][FNumber],
		exif_lang('MeteringMode')		=>	getimageinfoval($exif[EXIF][MeteringMode],$MeteringMode_arr),
		exif_lang('LightSource')			=>	getimageinfoval($exif[EXIF][LightSource], $Lightsource_arr),
		exif_lang('Flash')		=>	getimageinfoval($exif[EXIF][Flash], $Flash_arr),
		exif_lang('ExposureMode')		=>	($exif[EXIF][ExposureMode]==1?exif_lang('manual'):exif_lang('auto')),
		exif_lang('WhiteBalance')		=>	($exif[EXIF][WhiteBalance]==1?exif_lang('manual'):exif_lang('auto')),
		exif_lang('ExposureProgram')		=>	$ExposureProgram[$exif[EXIF][ExposureProgram]],
		exif_lang('ExposureBiasValue')		=>	$exif[EXIF][ExposureBiasValue]."EV",
		exif_lang('ISOSpeedRatings')		=>	$exif[EXIF][ISOSpeedRatings],
		exif_lang('ComponentsConfiguration')		=>	(bin2hex($exif[EXIF][ComponentsConfiguration])=="01020300"?"YCbCr":"RGB"),//'0x04,0x05,0x06,0x00'="RGB" '0x01,0x02,0x03,0x00'="YCbCr"
		exif_lang('CompressedBitsPerPixel')		=>	$exif[EXIF][CompressedBitsPerPixel]."Bits/Pixel",
		exif_lang('FocusDistance')		=>	$exif[COMPUTED][FocusDistance]."m",
		exif_lang('FocalLength')			=>	$exif[EXIF][FocalLength]."mm",
		exif_lang('FocalLengthIn35mmFilm')	=>	$exif[EXIF][FocalLengthIn35mmFilm]."mm",
		exif_lang('UserCommentEncoding')	=>	$exif[COMPUTED][UserCommentEncoding],
		exif_lang('UserComment')		=>	$exif[COMPUTED][UserComment],
		exif_lang('ColorSpace')		=>	($exif[EXIF][ColorSpace]==1?"sRGB":"Uncalibrated"),
		exif_lang('ExifImageLength')	=>	$exif[EXIF][ExifImageLength],
		exif_lang('ExifImageWidth')	=>	$exif[EXIF][ExifImageWidth],
		exif_lang('FileSource')		=>	(bin2hex($exif[EXIF][FileSource])==0x03?"digital still camera":"unknown"),
		exif_lang('SceneType')		=>	(bin2hex($exif[EXIF][SceneType])==0x01?"A directly photographed image":"unknown"),
		exif_lang('ThumbFileType')	=>	$exif[COMPUTED][Thumbnail.FileType],
		exif_lang('ThumbMimeType')	=>	$exif[COMPUTED][Thumbnail.MimeType]
		);
	}
	return $new_img_info;
}

function exif_lang($langkey) {
	return lang('exif', $langkey);
}

?>