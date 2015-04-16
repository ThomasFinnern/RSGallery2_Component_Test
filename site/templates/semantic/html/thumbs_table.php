<?php 
/**
 * RSGallery2
 * @version $Id: thumbs_table.php 1084 2012-06-17 15:25:18Z mirjam $
 * @package RSGallery2
 * @copyright (C) 2003 - 2012 RSGallery2
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die(); ?>

<?php
$cols = $rsgConfig->get( 'display_thumbs_colsPerPage' );
$i = 0;

// Show slideshow link when viewing thumbs in table?
$slideshow   = $rsgConfig->get('displaySlideshowGalleryView',0);
if ($slideshow) {
	?>
	<a href='<?php echo JRoute::_("index.php?option=com_rsgallery2&page=slideshow&gid=".$this->gallery->id); ?>'>
	<?php echo JText::_('COM_RSGALLERY2_SLIDESHOW'); ?></a>
	<br />
	<?php
}
?>

<table id="rsg2-thumbsList" border="0">
	<?php foreach( $this->gallery->currentItems() as $item ):
		if( $item->type == 'audio' )
			continue;  // we only handle images

		$thumb = $item->thumb();

		if( $i % $cols== 0) echo "<tr>\n";
		?>
			<td <?php echo ($item->published) ? "" : "class='system-unpublished'";?>>
				<div class="shadow-box" >
				<div class="img-shadow">
				<a href="<?php echo JRoute::_( "index.php?option=com_rsgallery2&page=inline&id=".$item->id ); ?>">
					<img src="<?php echo $thumb->url();?>" alt="<?php echo htmlspecialchars(stripslashes($item->descr), ENT_QUOTES); ?>"/>
				</a>
				</div>
				</div>
				<div class="rsg2-clr"></div>
				<?php if($rsgConfig->get("display_thumbs_showImgName")): ?>
				<br />
				<span class="rsg2_thumb_name">
					<?php echo htmlspecialchars(stripslashes($item->title), ENT_QUOTES); ?>
				</span>
				<?php endif;?>
				<?php if( $this->allowEdit ): ?>
<!--			<div id="rsg2-adminButtons">
					<a href="<?php echo JRoute::_("index.php?option=com_rsgallery2&page=edit_image&id=".$item->id); ?>"><img src="<?php echo JURI::base(); ?>/administrator/images/edit_f2.png" alt="" height="15" /></a>
					<a href="#" onClick="if(window.confirm('<?php echo JText::_('COM_RSGALLERY2_ARE_YOU_SURE_YOU_WANT_TO_DELETE_THIS_IMAGE');?>')) location='<?php echo JRoute::_("index.php?option=com_rsgallery2&page=delete_image&id=".$item->id); ?>'"><img src="<?php echo JURI::base(); ?>/administrator/images/delete_f2.png" alt="" height="15" /></a>
				</div>-->
				<?php endif; ?>
				<?php echo '<br /><i>'.((!$item->published) ? JText::_('JUNPUBLISHED') : '').'</i>';?>
			</td>
		<?php if( ++$i % $cols == 0) echo "</tr>\n"; ?>
	<?php endforeach; ?>
	<?php if( $i % $cols != 0) echo "</tr>\n"; ?>
</table>