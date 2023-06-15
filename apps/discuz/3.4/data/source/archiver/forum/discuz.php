<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
include loadarchiver('common/header');
?>
<div id="nav">
	<a href="./"><strong><?php echo $_G['setting']['bbname']; ?></strong></a>
</div>
<div id="content">
	<?php foreach($catlist as $key => $cat): ?>
	<h3><?php echo $cat['name']; ?></h3>
	<?php if(!empty($cat['forums'])): ?>
	<ul>
		<?php foreach($cat['forums'] as $fid): ?>
		<li><a href="?fid-<?php echo $fid; ?>.html"><?php echo $forumlist[$fid]['name']; ?></a></li>
		<?php endforeach; ?>
	</ul>
	<?php endif; ?>
	<?php endforeach; ?>
</div>
<div id="end">
	<?php echo lang('forum/archiver', 'full_version'); ?>:
	<a href="../forum.php" target="_blank"><strong><?php echo $_G['setting']['bbname']; ?></strong></a>
</div>
<?php include loadarchiver('common/footer'); ?>