<?php
if (!isset($class)) {
	$class = false;
}
if (!isset($close)) {
	$close = true;
}
?>
<div class="alert<?php echo ($class) ? ' ' . $class : null; ?>" id="flashGrowlMessage">
<?php if ($close): ?>
	<a class="close" data-dismiss="alert" href="#">×</a>
<?php endif; ?>
	<span id="flashGrowlMessageText">
	<?php echo $message; ?>
	</span>
</div>