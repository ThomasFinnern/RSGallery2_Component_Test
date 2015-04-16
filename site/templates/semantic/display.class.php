<?php
/**
 * @version $Id: display.class.php 1089 2012-07-09 11:51:28Z mirjam $
 * @package RSGallery2
 * @copyright (C) 2003 - 2012 RSGallery2
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();

/**
 * Template class for RSGallery2
 * @package RSGallery2
 * @author Ronald Smit <ronald.smit@rsdev.nl>
 */
class rsgDisplay_semantic extends rsgDisplay{

	function inline(){
		$this->display( 'inline.php' );
	}

	/**
	* Show main gallery page
	*/
	function showMainGalleries() {
		global $rsgConfig;
		$app = JFactory::getApplication();
		
		$gallery =  rsgInstance::getGallery();
		$this->gallery = $gallery;
		
		//Get values for page navigation from URL
		$limit = $app->getUserStateFromRequest("galleryviewlimit", 'limit', $rsgConfig->get('galcountNrs'), 'int');
		//$limitstart = JRequest::getInt( 'limitstart', 0 );
		$input = JFactory::getApplication()->input;
		$limitstart = $input->get( 'limitstart', 0, 'INT');					
		//Get number of galleries including main gallery
		$this->kids = $gallery->kids();
		$kidCountTotal = count( $gallery->kids() );

		$this->pageNav = false;
		
		//Show page navigation if selected in backend
		if(( $rsgConfig->get('dispLimitbox') == 1 &&
		    $kidCountTotal > $limit )  ||
			$rsgConfig->get('dispLimitbox') == 2 )
			{
//			require_once( JPATH_RSGALLERY2_ADMIN . DS . 'includes' . DS . 'gpagination.php' );
			//When users wants "All" galleries to show, $limit = 0, no need to slice
			if ($limit) {
				$this->kids = array_slice( $this->kids, $limitstart, $limit );
			}
		}
		$this->pageNav = new JPagination($kidCountTotal, $limitstart, $limit );
		$this->display( 'gallery.php' );
		
	}


    /***************************
		non page public functions
	***************************/
	/**
	 * Shows the gallery details block when set in the backend
	 */
	function _showGalleryDetails( $kid ) {
		global $rsgConfig;
		$slideshow   = $rsgConfig->get('displaySlideshow') && $kid->itemCount() > 1;
		$owner 		 = $rsgConfig->get('showGalleryOwner');
		$size 		 = $rsgConfig->get('showGallerySize');
		$date 		 = $rsgConfig->get('showGalleryDate');
		$includeKids = $rsgConfig->get('includeKids',true);

		//Check if items need to be shown
		if ( ($slideshow + $owner + $size + $date) > 0 ) {
			?>
			<div class="rsg_gallery_details">
			<div class="rsg2_details">
			<?php
			if ($slideshow) {
				?>
				<a href='<?php echo JRoute::_("index.php?option=com_rsgallery2&page=slideshow&gid=".$kid->get('id')); ?>'>
				<?php echo JText::_('COM_RSGALLERY2_SLIDESHOW'); ?></a>
				<br />
				<?php
			}
			
			if ($owner) {
				echo JText::_('COM_RSGALLERY2_OWNER-'); 
				echo $kid->owner;?>
				<br />
				<?php
			} 
			
			if ($size) {
				echo JText::_('COM_RSGALLERY2_SIZE-'); 
				echo galleryUtils::getFileCount($kid->get('id'), $includeKids). ' ' . JText::_('COM_RSGALLERY2_IMAGES');?>
				<br />
				<?php
			}
			
			if ($date) {
				echo JText::_('COM_RSGALLERY2_CREATED-').""; 
				echo JHTML::_("date", $kid->date,JText::_('COM_RSGALLERY2_DATE_FORMAT_LC3') );
				?>
				<br />
				<?php
			}
			?>
			</div>
			</div>
			<?php
		}
	}
    
    /***************************
		private functions
	***************************/

    /**
     * @todo this alternate gallery view needs to be moved to an html file and added as a template parameter
	 * Seems to be used nowhere
     */
/*    function _showDouble( $kids ) {
		global $rsgConfig;
        $i = 0;
		echo"<div class='rsg_double_fix'>";
        foreach ( $kids as $kid ) {
			$i++;
            ?>
            <div class="rsg_galleryblock_double_<?php echo $i?>">
				<div class="rsg2-galleryList-status"><?php echo $kid->status;?></div>
				<div class="rsg2-galleryList-thumb_double">
					<?php echo $kid->thumbHTML; ?>
				</div>
				<div class="rsg2-galleryList-text_double">
					<?php echo $kid->galleryName;?>
					<span class='rsg2-galleryList-newImages'>
						<sup><?php echo galleryUtils::newImages($kid->get('id')); ?></sup>
					</span>
					<?php echo $this->_showGalleryDetails( $kid );?>
					<div class="rsg2-galleryList-description"><?php echo $kid->description;?>
					</div>
				</div>
				<div class="rsg_sub_url"><?php $this->_subGalleryList( $kid ); ?>
				</div>
			</div>
            <?php
			if($i>1){
				$i = 0;
			}
        }
		echo "</div>";
    }/**/
    
    /**
     * @todo this alternate gallery view needs to be moved to an html file and added as a template parameter
	 * Seems to be used nowhere
     */
/*    function _showBox( $kids, $subgalleries ) {
        ?>
		<div class="rsg_box_block">
            <?php
            $i = 0;
            foreach ( $kids as $kid ) {
                $i++;
				if($i>3){
					$i = 1;
					}
			 ?>
                <div class="rsg_box_box_<?php echo $i;?>">
                    <div class="rsg_galleryblock">
                    	<div>
							<div class="rsg2-galleryList-status"><?php echo $kid->status; ?></div>
                            <?php echo $kid->galleryName;?>
                            <sup><span class='rsg2-galleryList-newImages'><?php echo galleryUtils::newImages($kid->get('id')); ?></span></sup>
                            <div class='rsg2-galleryList-totalImages'>(<?php echo galleryUtils::getFileCount($kid->get('id')).' '. JText::_('COM_RSGALLERY2_IMAGES');?>)</div>
                        </div>
						<div>
                        	<div class="rsg2-galleryList-thumb_box">
								<?php echo $kid->thumbHTML; ?>
                        	</div>
                        	<div class="rsg2-galleryList-text_box">
                          		<?php echo $this->_showGalleryDetails( $kid );?>
                        	</div>
                    	</div>
                        <div class="rsg2-galleryList-description_box">
                            	<?php echo $kid->description;?>
						</div>
                        <div class="rsg_sub_url">
                        		<?php $this->_subGalleryList( $kid ); ?> 
                        </div>
                    </div>
                </div>
                <?php
            }
			?>
            </div>
        <?php
    }/**/

	/**
	 * Shows thumbnails for gallery
	 */
	function showThumbs() {
		global $rsgConfig;
		$my = JFactory::getUser();
		
		// For superadministrators (they have core.admin) this includes the unpublished items
		$itemCount = $this->gallery->itemCount();

		$limit = $rsgConfig->get("display_thumbs_maxPerPage") ;
		//$limitstart = JRequest::getInt( 'limitstart' );
		$input = JFactory::getApplication()->input;
		$limitstart = $input->get( 'limitstart', 0, 'INT');					
		
		//instantiate page navigation
		jimport('joomla.html.pagination');
		$pagenav = new JPagination( $itemCount, $limitstart, $limit );//MK gaat goed: thumbs in gallery
	
		// increase the gallery hit counter
		$this->gallery->hit();
		
		if( !$this->gallery->itemCount() ){
			if( $this->gallery->id ){
				// if gallery is not the root gallery display the message
				echo JText::_('COM_RSGALLERY2_NO_IMAGES_IN_GALLERY');
			}
			// no items to display, so we can return;
			return;
		}
		//Old rights management. If user is owner or user is Super Administrator, you can edit this gallery
		if(( $my->id <> 0 ) and (( $this->gallery->uid == $my->id ) OR ( $my->usertype == 'Super Administrator' )))
			$this->allowEdit = true;
		else
			$this->allowEdit = true;

		switch( $rsgConfig->get( 'display_thumbs_style' )){
			case 'float':
				$this->display( 'thumbs_float.php' );
			break;
			case 'table':
				$this->display( 'thumbs_table.php' );
			break;
		}
		?>
		<div class="pagination">
				<?php
				if( $itemCount > $limit ){
					echo $pagenav->getPagesLinks();
					echo "<br /><br />".$pagenav->getPagesCounter();
				}
				?>
		</div>
		<?php
	}
    
    /**
     * Shows main item
     */
	function showItem(){
		global $rsgConfig;
		
		$item = rsgInstance::getItem();

    	// increase hit counter
		if (is_object($item)){	//Can this be achieved in a better way? When an item is unpublished (there is no $item object) a user gets a "Call to a member function hit() on a non-object" error without this check. With Joomla SEF we get a Notice "Could not find an image with image id ." (without the id number) and without Joomla SEF we get a blank page?!
			$item->hit();
		}

		?>
		<table <?php echo ($item->published) ? "" : "class='system-unpublished'";?> border="0" cellspacing="0" cellpadding="0" width="100%">
			<tr>
				<td><h2 class='rsg2_display_name' align="center"><?php echo htmlspecialchars(stripslashes($item->title), ENT_QUOTES); ?></h2></td>
			</tr>
			<tr>
				<td>
				<div align="center">
					<?php
					$this->currentItem = $item;
					$this->display("item_" .  $item->type . ".php");
    				?>
				</div>
				</td>
			</tr>
			<tr>
				<td><?php $this->_writeDownloadLink( $item->id );?></td>
			</tr>
		</table>
		<?php
	}
 
	/**
	 * Show page navigation for Display image
	 */
	function showDisplayPageNav() {//MK this is where the images are shown with limit=1
		$gallery = rsgGalleryManager::get();
		//$itemId = JRequest::getInt( 'id', 0 );
		$input = JFactory::getApplication()->input;
		$itemId = $input->get( 'id', 0, 'INT');					
		if( $itemId != 0 ){
			// if the item id is set then we need to set the gid instead
			// having the id variable set in the querystring breaks the page navigation

			// i have not found any other way to remove a query variable from the router
			// JPagination uses the router to build the current route, so removing it from the 
			// request variables only does not work.
			$app	= JFactory::getApplication();
			$router = $app->getRouter();

			$router->setVar('gid',$gallery->id);
			$router->setVar('id',Null);				//unsets the var id from JRouter
			
			// set the limitstart so the pagination knows what page to start from
			$itemIndex = $gallery->indexOfItem($itemId);
			$router->setVar("limitstart", $itemIndex);
			JRequest::setVar('limitstart', $itemIndex);
		}

		$pageNav = $gallery->getPagination();	
		$pageLinks = $pageNav->getPagesLinks(); 

		?>
		<div align="center">
		 <div class="pagination">
			<?php echo $pageLinks; ?>
		 </div>
		</div>
		<?php
	}

	/**
	 * Shows details of image
	 */
	function showDisplayImageDetails() {
		global $rsgConfig;
		
		$gallery = rsgGalleryManager::get();

		// if no details need to be displayed then exit
		
		if (! ( $rsgConfig->get("displayDesc") || $rsgConfig->get("displayVoting") || $rsgConfig->get("displayComments") || $rsgConfig->get("displayEXIF") ))
			return;

		// JHtmlTabs
		$options = array(
			'onActive' => 'function(title, description){
				description.setStyle("display", "block");
				title.addClass("open").removeClass("closed");
			}',
			'onBackground' => 'function(title, description){
				description.setStyle("display", "none");
				title.addClass("closed").removeClass("open");
			}',
			'startOffset' => 0,  // 0 starts on the first tab, 1 starts the second, etc...
			'useCookie' => true, // this must not be a string. Don't use quotes.
		);
		echo JHtml::_('tabs.start', 'page_inline_tabs', $options);
		
		if ( $rsgConfig->get("displayDesc") ) {
			echo JHtml::_('tabs.panel', JText::_('COM_RSGALLERY2_DESCRIPTION'), 'page_inline_tabs_description');
			$this->_showDescription(); 
		}
		
		if ( $rsgConfig->get("displayVoting") ) {
			echo JHtml::_('tabs.panel', JText::_('COM_RSGALLERY2_VOTING'), 'page_inline_tabs_voting');
			$this->_showVotes();
		}
		
		if ( $rsgConfig->get("displayComments") ) {
			echo JHtml::_('tabs.panel', JText::_('COM_RSGALLERY2_COMMENTS'), 'page_inline_tabs_comments');
			$this->_showComments();
		}
	
		if ($rsgConfig->get("displayEXIF") ) {
			echo JHtml::_('tabs.panel', JText::_('COM_RSGALLERY2_EXIF'), 'page_inline_tabs_exif');
			$this->_showEXIF();
		}

		echo JHtml::_('tabs.end');
	}

    /**
     * Show description
     */
	function _showDescription( ) {
		global $rsgConfig;
		$item = rsgInstance::getItem();
		
		if( $rsgConfig->get('displayHits')):
		?>
		<p class="rsg2_hits"><?php echo JText::_('COM_RSGALLERY2_HITS'); ?> <span><?php echo $item->hits; ?></span></p>
		<?php
		endif;
		
		if ( $item->descr ):
		?>
		<p class="rsg2_description"><?php  echo stripslashes($item->descr); ?></p>
		<?php
		endif;
	}

	
	/**
	* list sub galleries in a gallery
	* @param rsgGallery parent gallery
	*/
	function _subGalleryList( $parent ){
		global $rsgConfig;
		$includeKids = $rsgConfig->get('includeKids',true);
		$user = JFactory::getUser();
		$kids = $parent->kids();

		if( count( $kids ) == 0 ) return;
		
		echo JText::_('COM_RSGALLERY2_SUBGALLERIES');
		
		$kid = array_shift( $kids );
		
		while( true ){
			?>
			<a href="<?php echo JRoute::_("index.php?option=com_rsgallery2&gid=".$kid->id); ?>">
				<?php echo htmlspecialchars(stripslashes($kid->name), ENT_QUOTES); ?>
				(<?php echo galleryUtils::getFileCount($kid->get('id'), $includeKids). ' ' . JText::_('COM_RSGALLERY2_IMAGES'); ?>)</a><?php
				
				//Show owner icon (blue with O)
				if ( $kid->uid == $user->id ) {
					echo JHTML::tooltip(JText::_('COM_RSGALLERY2_YOU_ARE_THE_OWNER_OF_THIS_GALLERY'), null, '../../../components/com_rsgallery2/images/status_owner.png',null,null,0);
				}
				//Show unpublished icon (red with H)
				if ($kid->published == 0) {
					echo JHTML::tooltip(JText::_('COM_RSGALLERY2_THIS_GALLERY_IS_NOT_PUBLISHED'), 	null, '../../../components/com_rsgallery2/images/status_hidden.png',null,null,0);
				}
				//Show upload possible icon (green with U)
				if (rsgAuthorisation::authorisationCreate($kid->id)) {
					echo JHTML::tooltip(JText::_('COM_RSGALLERY2_YOU_CAN_UPLOAD_IN_THIS_GALLERY'), null, '../../../components/com_rsgallery2/images/status_upload.png',null,null,0);
				}

			if( $kid = array_shift( $kids ))
				echo ', ';
			else
				break;
		}
	}
}
