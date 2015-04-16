<?php
/**
 * RSGallery2
 * @version $Id: gallery.php 1084 2012-06-17 15:25:18Z mirjam $
 * @package RSGallery2
 * @copyright (C) 2003 - 2012 RSGallery2
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JHtml::_('behavior.framework', true);

global $rsgConfig;

//Testing
echo('<!-- using template parameter: testParameter = ' . $this->params->get('testParameter') .' -->');

//Show My Galleries link (if user is logged in (user id not 0))
if ($rsgConfig->get('show_mygalleries') AND (JFactory::getUser()->id)) {
	echo $this->showRsgHeader();
}

//Show search box
$this->showSearchBox();

//Show introduction text
?>
<div class="intro_text">
	<?php echo $this->gallery->description; ?>
</div>

<?php
//Show limitbox
if( $this->pageNav->total ):
?>
	<div class="rsg2-pagenav-limitbox">
		<form action="<?php echo JRoute::_("index.php?option=com_rsgallery2");?>" method="post">
			<?php echo $this->pageNav->getLimitBox();?>
		</form>
	</div>
<?php
endif;

foreach( $this->kids as $kid ):
?>
<div class="rsg_galleryblock<?php echo ($kid->published) ? "" : " system-unpublished";?>">
	<div class="rsg2-galleryList-status"><?php echo $kid->status;?></div>
	<div class="rsg2-galleryList-thumb">
		<?php echo $kid->thumbHTML; ?>
	</div>
	<div class="rsg2-galleryList-text">
		<?php echo $kid->galleryName;?>
		<span class='rsg2-galleryList-newImages'>
			<sup><?php if( $this->gallery->hasNewImages() ) echo JText::_('COM_RSGALLERY2_NEW'); ?></sup>
		</span>
		<?php echo $this->_showGalleryDetails( $kid );?>
		<div class="rsg2-galleryList-description"><?php echo stripslashes($kid->description);?>
		</div>
	</div>
	<div class="rsg_sub_url_single">
		<?php $this->_subGalleryList( $kid ); ?>
	</div>
</div>
<?php
endforeach;
?>

<div class="rsg2-clr"></div>

<?php if( $this->pageNav->total ): ?>
<div class="pagination">
	<?php echo $this->pageNav->getPagesLinks();?>
	<br/>
	<?php echo $this->pageNav->getResultsCounter(); ?>
</div>
<div class='rsg2-clr'> </div>
<?php endif; ?>

<?php
if($this->gallery->id == 0){
	// Show random and latest only in the top gallery 
	// Show block with random images 
	$this->showImages("random", 3);
	// Show block with latest images
	$this->showImages("latest", 3);
}