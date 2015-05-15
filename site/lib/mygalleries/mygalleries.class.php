<?php
/**
* This file contains My galleries class
* @version $Id: mygalleries.class.php 1085 2012-06-24 13:44:29Z mirjam $
* @package RSGallery2
* @copyright (C) 2003 - 2012 RSGallery2
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* RSGallery is Free Software
*/

defined( '_JEXEC' ) or die();

class myGalleries {

   	function myGalleries() {

   	}
   	
    /**
     * This presents the main My Galleries page
     * @param array $rows Result array with galleries with parent 0 (no longer only for logged in users)
     * @param array $images Result array with image details (no longer only for logged in users)
     * @param array $pageNav Result array with pagenav information
     */
    static function viewMyGalleriesPage($rows, $images, $pageNav) {
        global $rsgConfig;
		$mainframe = JFactory::getApplication();
		$database = JFactory::getDBO();
		$user = JFactory::getUser();

		//User needs to be logged in and My galleries need to be enabled - check is in mygalleries.php function showMyGalleries

        ?>
		<div class="rsg2">
        <h2><?php echo JText::_('COM_RSGALLERY2_MY_GALLERIES');?></h2>

		<?php
		//Load My Galleries javascript file after core-uncompressed.js to override its Joomla.submitbutton function
//		$filename = 'mygalleries.js';
//		$path = 'components/com_rsgallery2/lib/mygalleries/';
//		JHtml::script($filename, $path);
		//As long as I'm unable to get JText with .js files working use this function:
		myGalleries::mygalleriesJavascript();
		
		//IE has a bug for 'disabled' options in a select box. Fix used from http://www.lattimore.id.au/2005/07/01/select-option-disabled-and-the-javascript-solution/
	?>	<!--[if lte IE 9]>
		<script type="text/javascript" src="<?php echo JURI_SITE;?>components/com_rsgallery2/lib/mygalleries/select-option-disabled-emulation.js"></script>
		<![endif]-->
	<?php
		
        //Show User information
        myGalleries::RSGalleryUSerInfo($user->id);

		//Is there at least one gallery where the user is allowed to create a gallery or an item?
		$createAllowedInAGallery = count(rsgAuthorisation::authorisationCreate_galleryList());
		//Is it allowed to create a gallery on the component permission level (e.g. create a gallery with the root (gid = 0) as parent?
		$createAllowedInRoot = rsgAuthorisation::authorisationCreate(0);

		JHtml::_('behavior.framework',true);
        // Set My Galleries tabs options
		$tabOptions = array(
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
		// Start My Galleries tabs 
		echo JHtml::_('tabs.start', 'tab_group_id', $tabOptions);
		// Show My Galleries: My Images tab
		echo JHtml::_('tabs.panel', JText::_('COM_RSGALLERY2_MY_IMAGES'), 'my_images');
		myGalleries::showMyImages($images, $pageNav);
		// Show My Galleries: My Add Image tab: showImageUpload only with create permission for one or more galleries:
		if ($createAllowedInAGallery) {
			echo JHtml::_('tabs.panel', JText::_('COM_RSGALLERY2_ADD_IMAGE'), 'image_upload');
			myGalleries::showImageUpload();
		}
		// Show My Galleries: My Galleries tab
		echo JHtml::_('tabs.panel', JText::_('COM_RSGALLERY2_MY_GALLERIES'), 'my_galleries');
		myGalleries::showMyGalleries($rows);
		// Show My Galleries: Create Gallery tab: only when creation of galleries is allowed in component or in one or more galleries
		if ($createAllowedInRoot OR $createAllowedInAGallery) {
			echo JHtml::_('tabs.panel', JText::_('COM_RSGALLERY2_CREATE_GALLERY'), 'create_gallery');
			myGalleries::showCreateGallery(NULL);
		}
		// End My Galleries tabs
		echo JHtml::_('tabs.end');
		?>
		</div>
        <div class='rsg2-clr'>&nbsp;</div>
        <?php
	}
	
	static function showCreateGallery($rows) {
		//This form is only shown when a user is allowed to create a gallery
		
    	global $rsgConfig;
		$user = JFactory::getUser();
		//$editor =& JFactory::getEditor();
		$editor = JFactory::getConfig()->get('editor'); // name of editor ?
		$editor = JEditor::getInstance($editor);

		//Script for this form is found in myGalleries::mygalleriesJavascript();

	    if ($rows) {
	        foreach ($rows as $row){
	            $catname        = $row->name;
	            $description    = $row->description;
	            $ordering       = $row->ordering;
	            $uid            = $row->uid;
	            $catid          = $row->id;
	            $published      = $row->published;
	            $user           = $row->user;
	            $parent         = $row->parent;
	        }
	    } else{
	        $catname        = "";
	        $description    = "";
	        $ordering       = "";
	        $uid            = "";
	        $catid          = "";
	        $published      = "";
	        $user           = "";
	        $parent         = 0;
	    }
		
		?>
        <form name="createGallery" id="adminForm" method="post"
              action="<?php echo JRoute::_("index.php?option=com_rsgallery2&rsgOption=myGalleries&task=saveCat",true); ?>">
			<table class="adminlist">
				<tr>
					<td colspan="2"><h3><?php echo JText::_('COM_RSGALLERY2_CREATE_GALLERY'); ?></h3>
						<div style="float: right;">
						<?php
						/* Not used since this gives a problem with Joomla SEF on: routes to http://wwww.mysite/index.php/rsgallery2-menu-item# instead of what is given in the task function
						//	JToolBarHelper does not exist in the frontend, using JToolBar here
						jimport( 'joomla.html.toolbar' );
						$bar = new JToolBar( 'MyGalleriesToolBar' );
						//appendButton: button type, class, display text on button, task, bool: selection from adminlist?
						$bar->appendButton( 'Standard', 'save', 'Save', 'createGallery.saveCat', false );
						$bar->appendButton( 'Standard', 'cancel', 'Cancel', 'createGallery.cancel', false );
						echo $bar->render();
						*/
						?>
						<a href="javascript:Joomla.submitbutton('createGallery.saveCat')" class="toolbar">
							<img src="<?php echo JURI_SITE;?>components/com_rsgallery2/images/save-active.png" alt="<?php echo JText::_('JSAVE'); ?>" width="32" />
						</a>
						<a href="javascript:Joomla.submitbutton('createGallery.cancel')" class="toolbar">
							<img src="<?php echo JURI_SITE;?>components/com_rsgallery2/images/cancel-active.png" alt="<?php echo JText::_('JCANCEL'); ?>" width="32" />
						</a>
						</div>
					</td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_RSGALLERY2_PARENT_ITEM');?></td>
					<td>
						<?php
						//Show all galleries where galleries without create permission are disabled, select name is 'parent', no gallery is selected, no additional select attributes, showTopGallery
						galleryUtils::showUserGalSelectListCreateAllowed('parent', '', '',true);
						//Limits to only user owned, does not use permissions:
						//galleryUtils::showCategories(NULL, $my->id, 'parent');
						?>
					</td>
				</tr>
				<tr>
					<td><?php echo JText::_('COM_RSGALLERY2_GALLERY_NAME'); ?></td>
					<td align="left"><input type="text" name="name" size="30" value="<?php echo $catname; ?>" /></td>
				</tr>
				<tr>
					<td>
						<?php echo JText::_('COM_RSGALLERY2_DESCRIPTION'); ?>
					</td>
					<td align="left">
						<?php echo $editor->display( 'description',  $description , '100%', '200', '10', '20' ,false) ; ?>
					</td>
				</tr>
				<!--<?php //Publish option should only show with correct permissions, so removed for now ?>
				<tr>
					<td><?php //echo JText::_('COM_RSGALLERY2_PUBLISHED'); ?></td>
					<td align="left"><input type="checkbox" name="published" value="1" <?php //if ($published==1) echo "checked"; ?> /></td>
				</tr>
				-->
			</table>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="option" value="com_rsgallery2" />
			<!--input type="hidden" name="rsgOption" value="< ? php
				// echo JRequest::getCmd('rsgOption');
			?>" / -->
			<input type="hidden" name="rsgOption" value="<?php echo JFactory::getApplication()->input->get( 'rsgOption', '', 'CMD'); ?>" />
			<!--input type="hidden" name="Itemid" value="< ?php
				// echo JRequest::getInt('Itemid');
			?>" /-->
			<input type="hidden" name="Itemid" value="<?php echo JFactory::getApplication()->input->get( 'Itemid', 0, 'INT'); ?>" />
			<?php echo JHtml::_('form.token'); ?>
        </form>
        <?php
	}
	
   /**
	* Displays details about the logged in user and the privileges he/she has
	* $param integer User ID from Joomla user table
	*/
	static function RSGalleryUserInfo($id) {
		global $rsgConfig;
		$user = JFactory::getUser();
		$maxcat = $rsgConfig->get('uu_maxCat');
		$max_images = $rsgConfig->get('uu_maxImages');
		?>
		<div class="current">
			<table class="adminlist">
			<tr>
				<th colspan="2"><?php echo JText::_('COM_RSGALLERY2_USER_INFORMATION'); ?></th>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_RSGALLERY2_USERNAME'); ?></td>
				<td><?php echo $user->username;?></td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_RSGALLERY2_MAXIMUM_USERGALLERIES'); ?></td>
				<td><?php echo $maxcat;?>&nbsp;(<font color="#008000"><strong><?php echo galleryUtils::userCategoryTotal($user->id);?></strong></font> <?php echo JText::_('COM_RSGALLERY2_NR_OF_USERGALLERIES_CREATED');?>)</td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_RSGALLERY2_MAXIMUM_IMAGES_ALLOWED'); ?></td>
				<td><?php echo $max_images;?>&nbsp;(<font color="#008000"><strong><?php echo galleryUtils::userImageTotal($user->id);?></strong></font> <?php echo JText::_('COM_RSGALLERY2_NR_OF_IMAGES_UPLOADED'); ?>)</td>
			</tr>
			</table>
		</div>
		<?php
	}
	
	static function showImageUpload() {
        global $rsgConfig;
		$my = JFactory::getUser();
		//$editor = JFactory::getEditor();
		$editor = JFactory::getConfig()->get('editor'); // name of editor ?
		$editor = JEditor::getInstance($editor);

		//function showImageUpload should only be called when user has create permission for one or more galleries
        
        //Script for this form is found in myGalleries::mygalleriesJavascript();
        ?>

        <form name="imgUpload" id="adminForm" method="post"
              action="<?php echo JRoute::_("index.php?option=com_rsgallery2&rsgOption=myGalleries&task=saveUploadedItem"); ?>"
              enctype="multipart/form-data">
		<div class="rsg2">
        <table class="adminlist">
            <tr>
                <td>
				<h3><?php echo JText::_('COM_RSGALLERY2_ADD_IMAGE');?></h3>
				</td>
				<td>
                    <div style="float: right;">
						<a href="javascript:Joomla.submitbutton('imgUpload.saveUploadedItem')" class="toolbar">
							<img src="<?php echo JURI_SITE;?>components/com_rsgallery2/images/save-active.png" alt="<?php echo JText::_('JSAVE'); ?>" width="32" />
						</a>
						<a href="javascript:Joomla.submitbutton('imgUpload.cancel')" class="toolbar">
							<img src="<?php echo JURI_SITE;?>components/com_rsgallery2/images/cancel-active.png" alt="<?php echo JText::_('JCANCEL'); ?>" width="32" />
						</a>	
					</div>
				</td>
            </tr>
			<tr>
				<td>
				<?php echo JText::_('COM_RSGALLERY2_GALLERY'); ?>
				</td>
				<td>
					<?php 
					//Show all galleries where galleries without ceate permission are disabled, select name is 'gallery_id', no gallery is selected, no additional select atributes, don't showTopGallery
					galleryUtils::showUserGalSelectListCreateAllowed('gallery_id');
					//Deprecated:
					//galleryUtils::galleriesSelectList(null, 'gallery_id', false);*/
					//Limits to only user owned, does not use permissions:
					//galleryUtils::showCategories(NULL, $my->id, 'gallery_id');
					?>
				</td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_RSGALLERY2_FILENAME') ?></td>
				<td align="left"><input size="49" type="file" name="i_file" /></td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_RSGALLERY2_TITLE') ?>:</td>
				<td align="left"><input name="title" type="text" size="49" /></td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_RSGALLERY2_DESCRIPTION') ?>
				</td>
				<td align="left">
					<?php echo $editor->display( 'descr',  '' , '100%', '200', '10', '20' ,false) ; ?>
				</td>
			</tr>
<?php		if ($rsgConfig->get('graphicsLib') == '') { ?>
			<tr>
				<td><?php echo JText::_('COM_RSGALLERY2_THUMB'); ?></td>
				<td align="left"><input type="file" name="i_thumb" /></td>
			</tr>
<?php 		} ?>
        </table>
        </div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="option" value="com_rsgallery2" />
		<?php echo JHtml::_('form.token'); ?>
		</form>
        <?php
	}

    /**
     * This presents the list of galleries shown in My galleries
     * @param array $rows All galleries (no longer only for logged in users)
     */
    static function showMyGalleries($rows) {
		$database = JFactory::getDBO();
		//$Itemid = JRequest::getInt('Itemid');
		$input =JFactory::getApplication()->input;
		$Itemid = $input->get( 'Itemid', 0, 'INT');		
		//Set variables
		$count = count($rows);
		$user = JFactory::getUser();
		$userId = $user->id;
		
		?>
		<div class="rsg2">
		<table class="adminlist" >
            <tr>
                <td colspan="4"><h3><?php echo JText::_('COM_RSGALLERY2_MY_GALLERIES');?></h3></td>
            </tr>
            <tr>
                <th><div align="left"><?php echo JText::_('COM_RSGALLERY2_GALLERY'); ?></div></th>
                <th width="75"><div align="center"><?php echo JText::_('COM_RSGALLERY2_PUBLISHED'); ?></div></th>
                <th width="75"><div align="center"><?php echo JText::_('COM_RSGALLERY2_DELETE'); ?></div></th>
                <th width="75"><div align="center"><?php echo JText::_('COM_RSGALLERY2_EDIT'); ?></div></th>
            </tr>
            <?php
            if ($count == 0) { //No galleries
                ?>
                <tr><td colspan="4"><?php echo JText::_('COM_RSGALLERY2_NO_USER_GALLERIES_CREATED'); ?></td></tr>
                <?php
            } else { //List of galleries
                foreach ($rows as $row) {
					//Get permissions
					$can['EditGallery']		= rsgAuthorisation::authorisationEditGallery($row->id);
					$can['EditStateGallery']	= rsgAuthorisation::authorisationEditStateGallery($row->id);
					$can['DeleteGallery'] 	= rsgAuthorisation::authorisationDeleteGallery($row->id);
                    ?>
                    <script type="text/javascript">
						//<![CDATA[
						function deleteGallery(gid) {
							var yesno = confirm ("<?php echo JText::_('COM_RSGALLERY2_DELCAT_TEXT');?>");
							if (yesno == true) {
								location = "<?php echo JRoute::_("index.php?option=com_rsgallery2&rsgOption=myGalleries&task=deleteCat", false);?>"+"&gid="+gid;
							}
						}
						//]]>
                    </script>

                    <tr>
                        <td>
							<?php
							$indent = "";
							// Only indent according to the 'level' when there is one
							if (isset($row->level)) {
								for ($i = 0; $i < $row->level; $i++) {
									$indent .= "&nbsp;&nbsp;&nbsp;&nbsp;";
									if ($i == $row->level -1) $indent .= "<sup>|_</sup>";
								}
							}
							if ($can['EditGallery']){
								//name with link
								echo $indent;?>
								<a href="<?php echo JRoute::_('index.php?option=com_rsgallery2&rsgOption=myGalleries&Itemid='.$Itemid.'&task=editCat&gid='.$row->id);?>">
									<?php echo stripslashes($row->name);?>
								</a>
								<?php
							} else {
								//name without link
								?>
									<?php echo $indent.stripslashes($row->name);?>
								<?php
							}
							?>
                        </td>
                        <td>
							<?php
							if ($can['EditStateGallery']){
								//active image with link
								if ($row->published == 1) $img = "published-active.png";
								else $img = "unpublished-active.png";
								?>
								<a href="<?php echo JRoute::_('index.php?option=com_rsgallery2&rsgOption=myGalleries&Itemid='.$Itemid.'&task=editStateGallery&gid='.$row->id.'&currentstate='.$row->published);?>">
									<div align="center">
										<img src="<?php echo JURI_SITE;?>components/com_rsgallery2/images/<?php echo $img;?>" alt="<?php echo JText::_('JACTION_EDITSTATE'); ?>" width="19" position="top" >
									</div>
								</a>
								<?php
							} else {
								//inactive image without link
								if ($row->published == 1) $img = "published-inactive.png";
								else $img = "unpublished-inactive.png";
								?>
								<div align="center">
									<img src="<?php echo JURI_SITE;?>components/com_rsgallery2/images/<?php echo $img;?>" alt="<?php echo JText::_('JACTION_EDITSTATE'); ?>" width="19" position="top" >
								</div>
								<?php
							}
							?>
						</td>
                        <td>
							<?php 
							if ($can['DeleteGallery']) {
								//active image with link
								?>
								<a href="javascript:deleteGallery(<?php echo $row->id;?>);">
									<div align="center">
										<img src="<?php echo JURI_SITE;?>components/com_rsgallery2/images/delete-active.png" alt="<?php echo JText::_('JACTION_DELETE'); ?>" width="19" >
									</div>
								</a>
								
								<?php
							} else {
								//inactive image without link
								?>
								<div align="center">
									<img src="<?php echo JURI_SITE;?>components/com_rsgallery2/images/delete-inactive.png" alt="<?php echo JText::_('JACTION_DELETE'); ?>" width="19" >
								</div>
								<?php
							}
							?>
                        </td>
                        <td>
						<?php
							if ($can['EditGallery']) {
								//active image with link
								?>
								<a href="<?php echo JRoute::_('index.php?option=com_rsgallery2&rsgOption=myGalleries&Itemid='.$Itemid.'&task=editCat&gid='.$row->id);?>">
									<div align="center">
										<img src="<?php echo JURI_SITE;?>/components/com_rsgallery2/images/edit-active.png" alt="<?php echo JText::_('JACTION_EDIT'); ?>" width="19" >
									</div>
								</a>
								<?php
							} else {
								//inactive image without link
								?>
								<div align="center">
									<img src="<?php echo JURI_SITE;?>/components/com_rsgallery2/images/edit-inactive.png" alt="<?php echo JText::_('JACTION_EDIT'); ?>" width="19" >
								</div>
								<?php								
							}
							?>
                        </td>
                    </tr>
                    <?php
				} //for each gallery - end
			} //end list of galleries (if any)
			?>
		</table>
		</div>
    <?php
    }
    /**
     * This will show the images, available to the logged in users in the My Galleries screen
     * under the tab "My Images".
     * @param array $images Result array with image details for the logged in users
     * @param array $pageNav Result array with pagenav details
     */
    static function showMyImages($images, $pageNav) {
        JHtml::_('behavior.tooltip');
		$input =JFactory::getApplication()->input;
		//$option = JRequest::getCmd('option');
		$option = $input->get( 'option', '', 'CMD');
		//$rsgOption = JRequest::getCmd('rsgOption');
		$rsgOption = $input->get( 'rsgOption', null, 'CMD');		
		//$Itemid = JRequest::getInt('Itemid');
		$Itemid = $input->get( 'Itemid', 0, 'INT');		
		//$limit = JRequest::getInt('limit');
		$limit = $input->get( 'limit', 0, 'INT');		
		$user = JFactory::getUser();
		$userId = $user->id;
		jimport( 'joomla.html.html.grid' );
        ?>
		<form name="showMyImages" id="adminForm" method="post"
              action="<?php echo JRoute::_('index.php?option='.$option.'&rsgOption='.$rsgOption.'&Itemid='.$Itemid); ?>" >
        <table class="adminlist" >
			<tr>
				<td colspan="2"><h3><?php echo JText::_('COM_RSGALLERY2_MY_IMAGES'); ?></h3></td>
				<td colspan="4">
					<div style="float: right;">
						<a href="javascript:Joomla.submitbutton('myImages.publishItems')" class="toolbar">
							<img src="<?php echo JURI_SITE;?>components/com_rsgallery2/images/published-active.png" alt="<?php echo JText::_('JSAVE'); ?>" width="32" />
						</a>
						<a href="javascript:Joomla.submitbutton('myImages.unpublishItems')" class="toolbar">
							<img src="<?php echo JURI_SITE;?>components/com_rsgallery2/images/unpublished-active.png" alt="<?php echo JText::_('JSAVE'); ?>" width="32" />
						</a>
						<a href="javascript:Joomla.submitbutton('myImages.deleteItems')" class="toolbar">
							<img src="<?php echo JURI_SITE;?>components/com_rsgallery2/images/delete-active.png" alt="<?php echo JText::_('JCANCEL'); ?>" width="32" />
						</a>	
					</div>
				</td>
			</tr>
			<tr>
				<th colspan="5"><div align="right">
					<?php //Since this is no form this won't work: onchange event: this.form.submit()
						//echo $pageNav->getLimitBox(); ?>
				</div></th>
			</tr>
			<tr>
				<th width="1%">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $images ); ?>);" />
				</th>
				<th maxwidth="20%"><?php echo JText::_('COM_RSGALLERY2_NAME'); ?></th>
				<th><?php echo JText::_('COM_RSGALLERY2_GALLERY'); ?></th>
				<th width="50" align="center"><?php echo JText::_('COM_RSGALLERY2_PUBLISHED'); ?></th>
				<th width="50" align="center"><?php echo JText::_('COM_RSGALLERY2_DELETE'); ?></th>
				<th width="50" align="center"><?php echo JText::_('COM_RSGALLERY2_EDIT'); ?></th>
			</tr>
			
			<?php
			if (count($images) > 0) {
				?>
				<script type="text/javascript">
					function deleteImage(id,itemid) {
						var yesno = confirm ('<?php echo JText::_('COM_RSGALLERY2_ARE_YOU_SURE_YOU_WANT_TO_DELETE_THIS_IMAGE');?>');
						if (yesno == true) {
							location = 'index.php?option=com_rsgallery2&Itemid='+itemid+'&rsgOption=myGalleries&task=deleteItem&id='+id;
						}
					}
				</script>
				
				<?php
				for ($i=0, $n=count( $images ); $i < $n; $i++) {
					$image = $images[$i];
				//foreach ($images as $image) {
					global $rsgConfig;
					//Get permissions
					$can['EditImage']		= rsgAuthorisation::authorisationEditItem($image->id);	
					$can['EditStateImage']	= rsgAuthorisation::authorisationEditStateItem($image->id);	
					$can['DeleteImage'] 	= rsgAuthorisation::authorisationDeleteItem($image->id);
					?>
					<tr>
						<td>
							<?php 
							// If the image is checked out, get the username ('editor') who checked it out
							if ($image->checked_out) {
								$image->editor = JFactory::getUser($image->checked_out)->get('username');
							} 
							$checked 	= JHtml::_('grid.checkedout', $image, $i );
							echo $checked;
							?>
						</td>
						<td>
							<?php 
							//Tooltip with or without link
							if ($can['EditImage']){
								//tooltip: tip, tiptitle, tipimage, tiptext, url, depreciated bool=1 (@todo: this link has two // in it between root and imgPath_thumb)
								echo JHtml::tooltip('<img src="'.JUri::root().$rsgConfig->get('imgPath_thumb').'/'.$image->name.'.jpg" alt="'.$image->name.'" />',
								$image->name,
								JURI_SITE."components/com_rsgallery2/images/notice-info_19.png",
								'',
								"index.php?option=com_rsgallery2&Itemid=".$Itemid."&rsgOption=myGalleries&task=editItem&id=".$image->id);
								} else {
								echo JHtml::tooltip('<img src="'.JUri::root().$rsgConfig->get('imgPath_thumb').'/'.$image->name.'.jpg" alt="'.$image->name.'" />',
								$image->name,
								JURI_SITE."components/com_rsgallery2/images/notice-info_19.png", '', '', '');
							}
							echo galleryUtils::subText($image->title,30).' ('.galleryUtils::subText($image->name).') ';
							?>
						</td>
						<td><?php echo galleryUtils::getCatnameFromId($image->gallery_id)?>
						</td>
						<td>
							<?php 
							if ($can['EditStateImage']){
								//active images with link
								if ($image->published == 1) $img = "published-active.png";
								else $img = "unpublished-active.png";
								?>
								<a href="<?php echo JRoute::_('index.php?option=com_rsgallery2&rsgOption=myGalleries&Itemid='.$Itemid.'&task=editStateItem&id='.$image->id.'&currentstate='.$image->published);?>">
								<div align="center">
									<img src="<?php echo JURI_SITE;?>components/com_rsgallery2/images/<?php echo $img;?>" alt="<?php echo JText::_('JACTION_EDITSTATE'); ?>" width="19" position="top" >
								</div>
								</a>
								<?php
							} else {
								//inactive images without link
								if ($image->published == 1) $img = "published-inactive.png";
								else $img = "unpublished-inactive.png";
								?>
								<div align="center">
									<img src="<?php echo JURI_SITE;?>components/com_rsgallery2/images/<?php echo $img;?>" alt="<?php echo JText::_('JACTION_EDITSTATE'); ?>" width="19" position="top" >
								</div>
								<?php
							}
							?>
						</td>
						<td>
							<?php
							if ($can['DeleteImage']) {
								//active image with link
								?>
								<a href="javascript:deleteImage(<?php echo $image->id.','.$Itemid;?>);">
									<div align="center">
										<img src="<?php echo JURI_SITE;?>components/com_rsgallery2/images/delete-active.png" alt="<?php echo JText::_('JACTION_DELETE'); ?>" width="19"  >
									</div>
								</a>
								<?php
								} else {
								//inactive image without link
								?>
								<div align="center">
									<img src="<?php echo JURI_SITE;?>components/com_rsgallery2/images/delete-inactive.png" alt="<?php echo JText::_('JACTION_DELETE'); ?>" width="19"  >
								</div>
								<?php
								}
							?>
						</td>
						<td>
							<?php
							if ($can['EditImage']){
								//active image with link
								?>
								<a href="<?php echo JRoute::_('index.php?option=com_rsgallery2&rsgOption=myGalleries&Itemid='.$Itemid.'&task=editItem&id='.$image->id);?>">
								<div align="center">
									<img src="<?php echo JURI_SITE;?>/components/com_rsgallery2/images/edit-active.png" alt="<?php echo JText::_('JACTION_EDIT'); ?>" width="19" >
								</div>
								</a>
								<?php
								} else {
								//inactive image without link
								?>
								<div align="center">
									<img src="<?php echo JURI_SITE;?>/components/com_rsgallery2/images/edit-inactive.png" alt="<?php echo JText::_('JACTION_EDIT'); ?>" width="19" >
								</div>
								<?php
								}
							?>
						</td>
					</tr>
					<?php
				} // End foreach ($images as $image)
			} //End list of images
			else
			{ //No images for this user
				?>
				<tr><td colspan="5"><?php echo JText::_('COM_RSGALLERY2_NO_IMAGES_IN_USER_GALLERIES'); ?></td></tr>
				<?php
			}
				?>
				<tr>
					<td colspan="5">
						<div class="pagination">
							<?php 
								echo "<br>".$pageNav->getListFooter();
							?>
						</div>
					</td>
				</tr>
		</table>
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="rsgOption" value="<?php echo $rsgOption;?>" />
		<input type="hidden" name="Itemid" value="<?php echo $Itemid;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		</form>
		<?php
    }
    
    static function editItem($rows) {
        global $rsgConfig;
		$my = JFactory::getUser();
		//$editor = JFactory::getEditor();
		$editor = JFactory::getConfig()->get('editor'); // name of editor ?
		$editor = JEditor::getInstance($editor);

        foreach ($rows as $row) {
            $filename       = $row->name;
            $title          = htmlspecialchars($row->title, ENT_QUOTES);
            //$description    = $row->descr;
            $description    = htmlspecialchars($row->descr, ENT_QUOTES);
            $id             = $row->id;
            $limitstart     = $row->ordering - 1;
            $gallery_id     = $row->gallery_id;
        }

		//IE has a bug for 'disabled' options in a select box. Fix used from http://www.lattimore.id.au/2005/07/01/select-option-disabled-and-the-javascript-solution/
	?>	<!--[if lt IE 8]>
		<script type="text/javascript" src="<?php echo JURI_SITE;?>components/com_rsgallery2/lib/mygalleries/select-option-disabled-emulation.js"></script>
		<![endif]-->
		
		<script type="text/javascript">
			Joomla.submitbutton = function(formTask) {
				var action = formTask.split('.');
				//var formName = action[0];
				var task = action[1];
				var form = document.editItem; //since J!1.6: specific formname different than adminForm possible
				if (task == 'cancel') {
					form.reset();
					history.back();
					return;
				}

				<?php echo $editor->save('descr') ; ?>

				// Field validation, when OK submit.
				if (form.gallery_id.value <= "0") {
					alert( "<?php echo JText::_('COM_RSGALLERY2_YOU_MUST_SELECT_A_GALLERY'); ?>" );
				}
				else if (form.descr.value == ""){
					alert( "<?php echo JText::_('COM_RSGALLERY2_YOU_MUST_PROVIDE_A_DESCRIPTION'); ?>" );
				}
				else{
					Joomla.submitform(task, form);
					return;
				}
			}
		</script>
	
		<?php
        echo "<h3>".JText::_('COM_RSGALLERY2_EDIT_IMAGE')."</h3>";
        ?>

        <form name="editItem" id="adminForm" method="post"
              action="<?php echo JRoute::_("index.php?option=com_rsgallery2&rsgOption=myGalleries&task=saveItem"); ?>">
			<table class="adminlist" >
				<tr>
					<td colspan="3">
						<div style="float: right;">
							<a href="javascript:Joomla.submitbutton('editItem.saveItem')" class="toolbar">
								<img src="<?php echo JURI_SITE;?>components/com_rsgallery2/images/save-active.png" alt="<?php echo JText::_('JSAVE'); ?>" width="32" />
							</a>
							<a href="javascript:Joomla.submitbutton('editItem.cancel')" class="toolbar">
								<img src="<?php echo JURI_SITE;?>components/com_rsgallery2/images/cancel-active.png" alt="<?php echo JText::_('JCANCEL'); ?>" width="32" />
							</a>	
						</div>
					</td>
				</tr>
				<tr>
					<td align="left"><?php echo JText::_('COM_RSGALLERY2_CATEGORY_NAME'); ?></td>
					<td align="left">
						<?php 
						//Show all galleries where galleries without create permission are disabled, select name is 'gallery_id', $gallery_id is selected, no additional select atributes, don't showTopGallery
						galleryUtils::showUserGalSelectListCreateAllowed('gallery_id', $gallery_id);
						//Limits to only user owned, does not use permissions:
						//galleryUtils::showCategories(NULL, $my->id, 'gallery_id');
						?>
					</td>
					<td rowspan="3"><img src="<?php echo imgUtils::getImgThumb($filename); ?>" alt="<?php echo $title; ?>"  /></td>
				</tr>
				<tr>
					<td align="left"><?php echo JText::_('COM_RSGALLERY2_FILENAME'); ?></td>
					<td  align="left"><strong><?php echo $filename; ?></strong></td>
					<!-- 2nd row of rowspan 3 -->
				</tr>
				<tr>
					<td align="left"><?php echo JText::_('COM_RSGALLERY2_TITLE');?></td>
					<td align="left"><input type="text" name="title" size="30" value="<?php echo $title; ?>" /></td>
					<!-- 3rd row of rowspan 3 -->
				</tr>
				<tr>
					<td align="left" valign="top"><?PHP echo JText::_('COM_RSGALLERY2_DESCRIPTION'); ?></td>
					<td align="left" colspan="2">
					<?php echo $editor->display( 'descr', stripslashes($description) , '100%', '200', '10', '20',false ) ; ?>
					</td>
				</tr>
			</table>
			<input type="hidden" name="id" 		value="<?php echo $id; ?>" />
			<input type="hidden" name="task" 	value="" />
			<input type="hidden" name="option" 	value="com_rsgallery2" />
			<!-- input type="hidden" name="Itemid" value="<?php
				// echo JRequest::getInt('Itemid');
			?>" / -->
			<input type="hidden" name="Itemid" value="<?php echo JFactory::getApplication()->input->get( 'Itemid', 0, 'INT'); ?>" />
			<!-- input type="hidden" name="rsgOption" value="<?php
			// echo JRequest::getCmd('rsgOption');
			?>" / -->
			<input type="hidden" name="rsgOption" value="<?php echo JFactory::getApplication()->input->get( 'rsgOption', '', 'CMD'); ?>" />
			
			<?php echo JHtml::_('form.token'); ?>
        </form>
        <?php
    }
    
static function editCat($rows = null) {
    global $rsgConfig;
	$my = JFactory::getUser();
	//$editor =& JFactory::getEditor();
	$editor = JFactory::getConfig()->get('editor'); // name of editor ?
	$editor = JEditor::getInstance($editor);

	//IE has a bug for 'disabled' options in a select box. Fix used from http://www.lattimore.id.au/2005/07/01/select-option-disabled-and-the-javascript-solution/
?>	<!--[if lt IE 8]>
	<script type="text/javascript" src="<?php echo JURI_SITE;?>components/com_rsgallery2/lib/mygalleries/select-option-disabled-emulation.js"></script>
	<![endif]-->	
	
    <script type="text/javascript">
        Joomla.submitbutton = function(formTask) {
			var action = formTask.split('.');
			//var formName = action[0];
			var task = action[1];
            var form = document.editGallery;
            if (task == 'cancel') {
                form.reset();
                history.back();
                return;
            }

			<?php echo $editor->save( 'description' ) ; ?>

			// Field validation, when OK submit.
			if (form.name.value == "") {
				alert( "<?php echo JText::_('COM_RSGALLERY2_YOU_MUST_PROVIDE_A_GALLERY_NAME'); ?>" );
			} else if (form.description.value == ""){
				alert( "<?php echo JText::_('COM_RSGALLERY2_YOU_MUST_PROVIDE_A_DESCRIPTION'); ?>" );
			} else if (form.parent.value < "0"){ //Top gallery may be allowed
				alert( "<?php echo JText::_('COM_RSGALLERY2_YOU_NEED_TO_SELECT_A_PARENT_GALLERY'); ?>" );
			} else {
				Joomla.submitform(task, form);
				return;
			}
        }
    </script>
	
    <?php
    if ($rows) {
        foreach ($rows as $row){
            $name        = htmlspecialchars($row->name, ENT_QUOTES);
            $description    = htmlspecialchars($row->description, ENT_QUOTES);
            $ordering       = $row->ordering;
            $uid            = $row->uid;
            $gid          	= $row->id;
            $published      = $row->published;
            $user           = $row->user;
            $parent         = $row->parent;
        }
    }
    else{
        $name	        = "";
        $description    = "";
        $ordering       = "";
        $uid            = "";
        $gid          	= "";
        $published      = "";
        $user           = "";
        $parent         = "";
    }
    ?>
	<h3><?php echo JText::_('COM_RSGALLERY2_EDIT_GALLERY'); ?></h3>
	<form name="editGallery" id="adminForm" method="post"
          action="<?php echo JRoute::_("index.php?option=com_rsgallery2&rsgOption=myGalleries&task=saveCat"); ?>">
        <table class="adminlist" >
			<tr>
				<th colspan="2">
					<div style="float: right;">
						<a href="javascript:Joomla.submitbutton('editGallery.saveCat')" class="toolbar">
							<img src="<?php echo JURI_SITE;?>components/com_rsgallery2/images/save-active.png" alt="<?php echo JText::_('JSAVE'); ?>" width="32" />
						</a>
						<a href="javascript:Joomla.submitbutton('editGallery.cancel')" class="toolbar">
							<img src="<?php echo JURI_SITE;?>components/com_rsgallery2/images/cancel-active.png" alt="<?php echo JText::_('JCANCEL'); ?>" width="32" />
						</a>	
					</div>				
				</th>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_RSGALLERY2_PARENT_ITEM');?></td>
				<td>
					<?php 
					//Show all galleries where galleries without create permission are disabled, select name is 'parent', $parent is selected, no additional select atributes, showTopGallery
					galleryUtils::showUserGalSelectListCreateAllowed('parent', $row->parent, '', true);			
					?>
				</td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_RSGALLERY2_GALLERY_NAME'); ?></td>
				<td align="left"><input type="text" name="name" size="30" value="<?php echo $name; ?>" /></td>
			</tr>
			<tr>
				<td><?php echo JText::_('COM_RSGALLERY2_DESCRIPTION'); ?></td>
				<td>
					<?php
					echo $editor->display( 'description',  $description , '100%', '200', '10', '20', false ) ; ?>
				</td>
			</tr>
			<!--<?php //Publish option should only show with correct permissions, so removed for now ?>
			<tr>
				<td><?php echo JText::_('COM_RSGALLERY2_PUBLISHED'); ?></td>
				<td align="left"><input type="checkbox" name="published" value="1" <?php //if ($published==1) echo "checked"; ?> /></td>
			</tr>
			-->
        <input type="hidden" name="gid" value="<?php echo $gid; ?>" />
        <input type="hidden" name="ordering" value="<?php echo $ordering; ?>" />
		<input type="hidden" name="task" 	value="" />
		<input type="hidden" name="option" 	value="com_rsgallery2" />
		<?php echo JHtml::_('form.token'); ?>
        </table>
	</form>
        <?php
    }

/*
 * Javascript for My galleries forms which use Joomla.submitbutton function.
 * toolbarbutton sends information in formname.task format.
 */
static function mygalleriesJavascript() {
	//$editor =& JFactory::getEditor();
	$editor = JFactory::getConfig()->get('editor'); // name of editor ?
	$editor = JEditor::getInstance($editor);	
	?>
	<script type="text/javascript">
	Joomla.submitbutton = function(formTask) {
		var action = formTask.split('.');
		var formName = action[0];
		var task = action[1];

		//Three forms available: createGallery, imgUpload and myImages
		if (formName == 'myImages') {
			if (task == 'deleteItems') {
				var answer = confirm ("<?php echo JText::_('COM_RSGALLERY2_ARE_YOU_SURE_YOU_WANT_TO_DELETE_SELECTED_ITEMS'); ?>")
				if (!answer) {
					//Don't delete!
					return;
				}
			}
			Joomla.submitform(task, form);
			return;
		}
		if (formName == 'createGallery') {
			var form = document.createGallery; //since J!1.6: specific formname different than adminForm possible
			if (task == 'cancel') {
				//form.task = createGallery.cancel
				form.reset();
				return;
			} else if (task == 'saveCat') {
				//form.task = createGallery.saveCat
				<?php echo $editor->save('description') ; ?>
				// Field validation, if OK then submit
				if (form.parent.value == "-1") {
					alert( "<?php echo JText::_('COM_RSGALLERY2_YOU_NEED_TO_SELECT_A_PARENT_GALLERY'); ?>" );
				} else if (form.name.value == "") {
					alert( "<?php echo JText::_('COM_RSGALLERY2_YOU_MUST_PROVIDE_A_GALLERY_NAME'); ?>" );
				} else if (form.description.value == ""){
					alert( "<?php echo JText::_('COM_RSGALLERY2_YOU_MUST_PROVIDE_A_DESCRIPTION'); ?>" );
				} else{
					Joomla.submitform(task, form);
					return;
				}
			}
		} else if (formName == 'imgUpload') {
			var form = document.imgUpload; //since J!1.6: specific formname different than adminForm possible
			if (task == 'cancel') {
				//form.task = imgUpload.cancel
				form.reset();
				return;
			} else if (task == 'saveUploadedItem') {
				//form.task = imgUpload.saveUploadedItem
				<?php echo $editor->save('descr') ; ?>
				//Field validation, if OK then submit
				if (form.gallery_id.value <= 0) {
					alert( "<?php echo JText::_('COM_RSGALLERY2_YOU_MUST_SELECT_A_GALLERY'); ?>" );
				} 
				else if (form.i_file.value == "") {
					alert( "<?php echo JText::_('COM_RSGALLERY2_YOU_MUST_PROVIDE_A_FILE_TO_UPLOAD'); ?>" );
				}
			else {
				Joomla.submitform(task, form);
				return;
			}
			return;
			}
		}
		return;
	}
	</script>
	<?php
	}
	
   /* 
	* Function recursiveGalleriesList gets a list of galleries with their id, parent en hierarchy level ordered by ordering and subgalleries grouped by their parent.
	* $id		Gallery parent number
	* $list		The list to return
	* $children	The 2dim. array with children
	* $maxlevel Maximum depth of levels
	* $level	Hierarchy level (e.g. sub gallery of root is level 1)
	* return	Array
	*/
	static function recursiveGalleriesList(){
		global $rsgConfig;
		$user = JFactory::getUser();
		$groups	= $user->getAuthorisedViewLevels();
		$groupsIN = implode(", ",array_unique ($groups));
		$superAdmin = $user->authorise('core.admin');

		//Function to help out
		function treerecurse($id,  $list, &$children, $maxlevel=20, $level=0) {
			//if there are children for this id and the max.level isn't reached
			if (@$children[$id] && $level <= $maxlevel) {
				//add each child to the $list and ask for its children
				foreach ($children[$id] as $v) {
					$id = $v->id;	//gallery id
					$list[$id] = $v;
					$list[$id]->level = $level;
					//$list[$id]->children = count(@$children[$id]);
					$list = treerecurse($id,  $list, $children, $maxlevel, $level+1);
				}
			}
			return $list;
		}
		
		// Get a list of all galleries (id/parent) ordered by parent/ordering
		$database = JFactory::getDBO();
		$query = $database->getQuery(true);
		$query->select('*');
		$query->from('#__rsgallery2_galleries');
		$query->order('parent, ordering');
		// If user is not a Super Admin then use View Access Levels
		if (!$superAdmin) { // No View Access check for Super Administrators
			$query->where('access IN ('.$groupsIN.')'); //@todo use trash state: published=-2
		}
		if ($rsgConfig->get('show_mygalleries_onlyOwnGalleries')) {
			// Show only galleries owned by current user?
			$query->where('uid = '. (int) $user->id);
		}		

		$database->setQuery( $query );
		$allGalleries = $database->loadObjectList();
		
		// Two options: either 1. frontend logged in My Galleries user will see all 
		// galleries, or 2. (s)he wil see only the galleries that (s)he owns. In case of 1.
		// I want to show the hierarchy, in case of 2 I won't show the hierarchy. Reason 
		// not to show the hierarchy: what if the user does not own the parent?
		if ($rsgConfig->get('show_mygalleries_onlyOwnGalleries')) {
			// Show the user all galleries
			return $allGalleries;
		} else {
			// Establish the hierarchy by first getting the children: 2dim. array $children[parentid][]
			$children = array();
			if ( $allGalleries ) {
				foreach ( $allGalleries as $v ) {
					$pt     = $v->parent;
					$list   = @$children[$pt] ? $children[$pt] : array();
					array_push( $list, $v );
					$children[$pt] = $list;
				}
			}
			// Get list of galleries with (grand)children in the right order and with level info
			$recursiveGalleriesList = treerecurse( 0, array(), $children, 20, 0 );
			return $recursiveGalleriesList;
		}
	}
	
}//end class
?>
