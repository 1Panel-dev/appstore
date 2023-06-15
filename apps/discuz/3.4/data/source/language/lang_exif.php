<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: lang_exif.php 27449 2012-02-01 05:32:35Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$lang = array(

	'unknown' => '未知',
	'resolutionunit' => array('', '', '英寸', '厘米'),
	'exposureprogram' => array('未定义', '手动', '标准程序', '光圈先决', '快门先决', '景深先决', '运动模式', '肖像模式', '风景模式'),
	'meteringmode' => array(
		'0'		=>	'未知',
		'1'		=>	'平均',
		'2'		=>	'中央重点平均测光',
		'3'		=>	'点测',
		'4'		=>	'分区',
		'5'		=>	'评估',
		'6'		=>	'局部',
		'255'	=>	'其他'
		),
	'lightsource' => array(
		'0'		=>	'未知',
		'1'		=>	'日光',
		'2'		=>	'荧光灯',
		'3'		=>	'钨丝灯',
		'10'	=>	'闪光灯',
		'17'	=>	'标准灯光A',
		'18'	=>	'标准灯光B',
		'19'	=>	'标准灯光C',
		'20'	=>	'D55',
		'21'	=>	'D65',
		'22'	=>	'D75',
		'255'	=>	'其他'
		),
	'img_info' => array ('文件信息' => '没有图片EXIF信息'),

	'FileName' => '文件名',
	'FileType' => '文件类型',
	'MimeType' => '文件格式',
	'FileSize' => '文件大小',
	'FileDateTime' => '时间戳',
	'ImageDescription' => '图片说明',
	'auto'     => '自动',
	'Make'     => '制造商',
	'Model'    => '型号',
	'Orientation' => '方向',
	'XResolution' => '水平分辨率',
	'YResolution' => '垂直分辨率',
	'Software'    => '创建软件',
	'DateTime'    => '修改时间',
	'Artist'      => '作者',
	'YCbCrPositioning' => 'YCbCr位置控制',
	'Copyright'   => '版权',
	'Photographer'=> '摄影版权',
	'Editor'      => '编辑版权',
	'ExifVersion' => 'Exif版本',
	'FlashPixVersion' => 'FlashPix版本',
	'DateTimeOriginal' => '拍摄时间',
	'DateTimeDigitized'=> '数字化时间',
	'Height'  => '拍摄分辨率高',
	'Width'   => '拍摄分辨率宽',
	'ApertureValue' => '光圈',
	'ShutterSpeedValue' => '快门速度',
	'ApertureFNumber'   => '快门光圈',
	'MaxApertureValue'  => '最大光圈值',
	'ExposureTime'      => '曝光时间',
	'FNumber'           => 'F-Number',
	'MeteringMode'      => '测光模式',
	'LightSource'       => '光源',
	'Flash'             => '闪光灯',
	'ExposureMode'		=> '曝光模式',
	'manual'            => '手动',
	'WhiteBalance'      => '白平衡',
	'ExposureProgram'   => '曝光程序',
	'ExposureBiasValue' => '曝光补偿',
	'ISOSpeedRatings'   => 'ISO感光度',
	'ComponentsConfiguration' => '分量配置',
	'CompressedBitsPerPixel'  => '图像压缩率',
	'FocusDistance'     => '对焦距离',
	'FocalLength'       => '焦距',
	'FocalLengthIn35mmFilm' => '等价35mm焦距',
	'UserCommentEncoding' => '用户注释编码',
	'UserComment'		=> '用户注释',
	'ColorSpace'		=> '色彩空间',
	'ExifImageLength'   => 'Exif图像宽度',
	'ExifImageWidth'    => 'Exif图像高度',
	'FileSource'        => '文件来源',
	'SceneType'			=> '场景类型',
	'ThumbFileType'     => '缩略图文件格式',
	'ThumbMimeType'     => '缩略图Mime格式'
);

?>