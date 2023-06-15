<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

ob_start();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<base href="<?php echo $_G['siteurl']; ?>archiver/" />
<title><?php if(!empty($navtitle)): echo $navtitle.' - '; endif; if(empty($nobbname)): echo $_G['setting']['bbname'].' - '; endif;?> Powered by Discuz! Archiver</title>
<?php echo $_G['setting']['seohead']; ?>

<meta name="keywords" content="<?php if(!empty($metakeywords)): echo dhtmlspecialchars($metakeywords); endif;?>" />
<meta name="description" content="<?php if(!empty($metadescription)): echo dhtmlspecialchars($metadescription).' ';endif; echo ','.$_G['setting']['bbname'];?>" />
<meta name="generator" content="Discuz! <?php echo $_G['setting']['version']; ?>" />
<meta name="author" content="Discuz! Team and Comsenz UI Team" />
<meta name="copyright" content="2001-2023, Tencent Cloud." />
<style type="text/css">
	body {font-family: Verdana;FONT-SIZE: 12px;MARGIN: 0;color: #000000;background: #ffffff;}
	img {border:0;}
	li {margin-top: 8px;}
	.page {padding: 4px; border-top: 1px #EEEEEE solid}
	.author {background-color:#EEEEFF; padding: 6px; border-top: 1px #ddddee solid}
	#nav, #content, #end {padding: 8px; border: 1px solid #EEEEEE; clear: both; width: 95%; margin: auto; margin-top: 10px;}
	#header, #footer { margin-top: 20px; }
	#loginform {text-align: center;}
</style>
</head>
<body vlink="#333333" link="#333333">
<center id="header">
<?php echo adshow('headerbanner'); ?>
<h2><?php echo $_G['setting']['bbname']; ?>'s Archiver </h2>
</center>