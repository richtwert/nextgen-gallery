<?php 
/**
Template Page for the album overview

Follow variables are useable :

	$albumID     : Current ID of the album
	$galleries   : Contain all galleries inside this album
	$mode        : Contain the selected mode (extended or compact)

 You can check the content when you insert the tag <?php var_dump($variable) ?>
 If you would like to show the timestamp of the image ,you can use <?php echo $exif['created_timestamp'] ?>
**/
?>
<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?><?php if (!empty ($galleries)) : ?>

<div class="ngg-albumoverview">		

	<!-- List of galleries -->
	<?php foreach ($galleries as $gallery) : ?>
	
	<div class="ngg-album-compact">
		<div class="ngg-album-compactbox">
			<div class="ngg-album-link">
				<a class="Link" href="<?php echo $gallery->pagelink ?>">
					<img class="Thumb" alt="<?php echo $gallery->title ?>" src="<?php echo $gallery->previewurl ?>"/>
				</a>
			</div>
		</div>
		<h4><a class="ngg-album-desc" title="<?php echo $gallery->title ?>" href="<?php echo $gallery->pagelink ?>" ><?php echo $gallery->title ?></a></h4>
		<p><strong><?php echo $gallery->counter ?></strong> <?php _e('Photos', 'nggallery') ?></p>
	</div>

 	<?php endforeach; ?>

</div>
<div class="ngg-clear"></div>

<?php endif; ?>