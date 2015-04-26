<?php
/**
* Images option for RSGallery2 - HTML display code
* @version $Id: images.html.php 1057 2012-01-09 17:00:55Z mirjam $
* @package RSGallery2
* @copyright (C) 2003 - 2012 RSGallery2
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* RSGallery is Free Software
*/

// no direct access
defined( '_JEXEC' ) or die();

/**
 * Handles HTML screens for image option 
 * @package RSGallery2
 */
class html_rsg2_images {

	/**
	 * @param $option
	 * @param $rows
	 * @param $lists
	 * @param $search
	 * @param $pageNav
	 * @throws Exception
	 */
	static function showImages( $option, &$rows, &$lists, &$search, &$pageNav ) {
		global $rsgOption, $rsgConfig;
		// $option = JRequest::getCmd('option');
		$input =JFactory::getApplication()->input;		
		$option = $input->get( 'option', '', 'CMD');		
		$user 	= JFactory::getUser();
		$userId = $user->id;
		
		?>
        <form action="index.php" method="post" name="adminForm" id="adminForm">
        <!--form action="<?php echo JRoute::_('index.php?option=com_rsg2&view=images'); ?>" method="post" name="adminForm" id="adminForm"-->

            <?php if (count(JHtmlSidebar::getEntries()) > 0) : ?>
                <div id="j-sidebar-container" class="span2">
                    <?php echo JHtmlSidebar::render( ); ?>
                </div>
                <div id="j-main-container" class="span10">
            <?php else : ?>
                <div id="j-main-container">
            <?php endif;?>

            <div class="clearfix"> </div>

            <?php
            // Search tools bar
            // echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
            ?>

			<table border="0" width="100%">
				<tr>
					<td align="left" width="50%">
						&nbsp;
					</td>
					<td align="right" width="50%">
						<?php echo JText::_('COM_RSGALLERY2_COPY-MOVE')?>
						<?php echo $lists['move_id'];?>
						<?php echo JText::_('COM_RSGALLERY2_FILTER')?>
						<input type="text" name="search" value="<?php echo $search;?>" class="text_area" onChange="document.adminForm.submit();" />
						<?php echo $lists['gallery_id'];?>
					</td>
				</tr>
			</table>

			<!-- ?php if (empty($this->items)) : ? -->
			<?php if (count( $rows ) == 0) : ?>
				<div class="alert alert-no-items">
					<?php echo JText::_('COM_RSGALLERY2_GALLERY_HAS_NO_IMAGES_ASSIGNED'); ?>
				</div>
			<?php else : ?>
				<table class="adminlist table table-striped" id="GalleryList">
					<!-- table class="adminlist" -->
					<thead>
						<tr>
							<th width="1%">ID</th>
							<th width="1%">
								<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows ); ?>);" />
							</th>
							<th class="title"><?php echo JText::_('COM_RSGALLERY2_TITLE_FILENAME')?></th>
							<th width="5%"><?php echo JText::_('COM_RSGALLERY2_PUBLISHED')?></th>
							<th width="5%"><?php echo JText::_('COM_RSGALLERY2_REORDER')?></th>
							<th width="2%"><?php echo JText::_('COM_RSGALLERY2_ORDER')?></th>
							<th width="2%">
								<?php echo JHtml::_('grid.order',  $rows); ?>
							</th>
							<th width="15%" align="left"><?php echo JText::_('COM_RSGALLERY2_GALLERY')?></th>
							<th width="5%"><?php echo JText::_('COM_RSGALLERY2_HITS')?></th>
							<th width=""><?php echo JText::_('COM_RSGALLERY2_DATE__TIME')?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$k = 0;
						for ($i=0, $n=count( $rows ); $i < $n; $i++) {
							$row = &$rows[$i];
							//Get permissions
							$can['EditItem']		= $user->authorise('core.edit',		'com_rsgallery2.item.'.$row->id);
							$can['EditOwnItem']	= $user->authorise('core.edit.own',	'com_rsgallery2.item.'.$row->id) AND ($row->userid == $userId);
							$can['EditStateItem']	= $user->authorise('core.edit.state','com_rsgallery2.item.'.$row->id);
							$can['EditGallery']		= $user->authorise('core.edit',		'com_rsgallery2.gallery.'.$row->gallery_id);
							$showMoveUpIcon = (($row->gallery_id == @$rows[$i-1]->gallery_id) AND ($can['EditStateItem']));
							$showMoveDownIcon = (($row->gallery_id == @$rows[$i+1]->gallery_id) AND ($can['EditStateItem']));
							$disabled = $can['EditStateItem'] ?  '' : 'disabled="disabled"';

							$link 	= 'index.php?option=com_rsgallery2&rsgOption='.$rsgOption.'&task=editA&hidemainmenu=1&id='. $row->id;

							$checked 	= JHtml::_('grid.checkedout', $row, $i );

							$row->cat_link 	= 'index.php?option=com_rsgallery2&rsgOption=galleries&task=editA&hidemainmenu=1&id='. $row->gallery_id;
							?>
							<tr class="<?php echo "row$k"; ?>">
								<td>
									<?php echo $row->id; ?>
								</td>
								<td>
									<?php echo $checked; ?>
								</td>
								<td>
									<?php
									//Checked out and not owning this item OR not allowed to edit (own) gallery: show name, else show linked name
									if (( $row->checked_out && ( $row->checked_out != $user->id )) OR !($can['EditItem'] OR $can['EditOwnItem']) ) {
										echo $row->title.'&nbsp;('.$row->name.')';
									} else {
										$gallery = rsgGalleryManager::getGalleryByItemID($row->id);
										if($gallery !== null){
											if (is_a( $gallery->getItem($row->id), 'rsgItem_audio' ) ) {
												$type = 'audio';
											} else {
												$type = 'image';
											}
										}
										echo JHtml::tooltip('<img src="'.JURI_SITE.$rsgConfig->get('imgPath_thumb').'/'.$row->name.'.jpg" alt="'.$row->name.'" />',
										 JText::_('COM_RSGALLERY2_EDIT_IMAGES'),
										 $row->name,
										 htmlspecialchars(stripslashes($row->title), ENT_QUOTES).'&nbsp;('.$row->name.')',
										 $link,
										1);
									}
									?>
								</td>
								<td align="center">
									<?php echo JHtml::_('jgrid.published', $row->published, $i, '', $can['EditStateItem']); ?>
								</td>
								<td class="order">
									<span>
										<?php echo $pageNav->orderUpIcon( $i, $showMoveUpIcon ); ?>
									</span>
									<span>
										<?php echo $pageNav->orderDownIcon( $i, $n, $showMoveDownIcon); ?>
									</span>
								</td>
								<td colspan="2" align="center">
								<input type="text" name="order[]" <?php echo $disabled; ?> size="5" value="<?php echo $row->ordering; ?>" class="text_area" style="text-align: center" />
								</td>
								<td>
									<?php 	if ($can['EditGallery']) { ?>
										<a href="<?php echo $row->cat_link; ?>" title="Edit Image">
									<?php 		echo $row->category; ?>
										</a>
									<?php 	} else {
												echo $row->category;
											}	?>
								</td>
								<td align="left">
									<?php echo $row->hits; ?>
								</td>
								<td align="left">
									<?php echo JHtml::Date($row->date, JText::_('COM_RSGALLERY2_DATE_FORMAT_WITH_TIME'));?>
								</td>
							</tr>
						<?php
							$k = 1 - $k;
							}
						?>
					</tbody>

					<tfoot>
						<tr>
							<td colspan="11"><?php echo $pageNav->getListFooter(); ?></td>
						</tr>
					</tfoot>
				</table>
				<input type="hidden" name="option" value="<?php echo $option;?>" />
				<input type="hidden" name="rsgOption" value="<?php echo $rsgOption;?>" />
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" name="hidemainmenu" value="0">
			<?php endif;?>
		</form>
	<?php
	}

	/**
	* Writes the edit form for new and existing record
	*
	* A new record is defined when <var>$row</var> is passed with the <var>id</var>
	* property set to 0.
	* @param mosWeblink $row The weblink object
	* @param array $listsAn array of select lists
	* @param object $params Parameters
	* @param string $option The option
	*/
	static function editImage( &$row, &$lists, &$params, $option ) {
		global $rsgOption;
		jimport("joomla.filter.output");
		JHtml::_('behavior.formvalidation');
		JFilterOutput::objectHTMLSafe( $row, ENT_QUOTES );
		//$editor = JFactory::getEditor();
		$editor = JFactory::getConfig()->get('editor'); // name of editor ?
		$editor = JEditor::getInstance($editor);

		//Can user see/change permissions?
		$canAdmin = JFactory::getUser()->authorise('core.admin', 'com_rsgallery2');
		$canEditStateItem = JFactory::getUser()->authorise('core.edit.state','com_rsgallery2.item.'.$row->id);

		//--- title of form ----------------------------
		// image exists
		if ($row->id > 0)
		{
			JToolBarHelper::title( JText::_('COM_RSGALLERY2_EDIT_IMAGE'), 'generic.png' );
		}
		else
		{
			$Text = JText::_('COM_RSGALLERY2_NEW') . " " . JText::_('COM_RSGALLERY2_IMAGE');
			JToolBarHelper::title($Text, 'generic.png' );
		}
		
		?>
		<script type="text/javascript">
			Joomla.submitbutton = function(task) {
				if (document.formvalidator.isValid(document.id('adminForm'))) {
					// Basic validation ok (input name required):
					// Validate gallery_id
					if (document.adminForm.gallery_id.value <= "0"){//'document'+form name+input name+'value'
						alert('<?php echo JText::_('COM_RSGALLERY2_YOU_MUST_SELECT_A_GALLERY');?>');
						return;
					}
					Joomla.submitform(task, document.getElementById('adminForm'));
				} else {
					if (document.adminForm.title.value == ""){
						alert('<?php echo JText::_('COM_RSGALLERY2_PLEASE_PROVIDE_A_VALID_IMAGE_TITLE');?>');
						return;
					}
				}
			}
		</script>
		
		<form action="index.php" method="post" name="adminForm" id="adminForm" class="form-validate">
			<table width="100%">
				<tr>
					<td width="60%" valign="top">
						<table class="adminform">
							<tr>
								<th colspan="2">
									&nbsp;
									<!-- ?php echo JText::_('COM_RSGALLERY2_DETAILS')? -->
								</th>
							</tr>
							<tr>
								<td width="20%" align="right"><?php echo JText::_('COM_RSGALLERY2_NAME')?></td>
								<td width="80%">
									<input class="text_area required" type="text" name="title" size="50" maxlength="250" value="<?php echo $row->title;?>" />
								</td>
							</tr>
							<tr>
								<td width="20%" align="right"><?php echo JText::_('COM_RSGALLERY2_ALIAS')?></td>
								<td width="80%">
									<input class="text_area" type="text" name="alias" size="50" maxlength="250" value="<?php echo $row->alias;?>" />
								</td>
							</tr>
							<tr>
								<td align="right">
								<?php echo JText::_('COM_RSGALLERY2_OWNER');?>
								</td>
								<td>
								<?php echo $lists['userid']; ?>
								</td>
							</tr>
							<tr>
								<td width="20%" align="right"><?php echo JText::_('COM_RSGALLERY2_FILENAME')?></td>
								<td width="80%"><?php echo $row->name;?></td>
							</tr>
							<tr>
								<td valign="top" align="right"><?php echo JText::_('COM_RSGALLERY2_GALLERY')?></td>
								<td><?php echo $lists['gallery_id']; ?></td>
							</tr>
							<?php if ($canAdmin) { ?>
								<tr>
									<td size="2" align="right">
										<?php echo JText::_('COM_RSGALLERY2_PERMISSIONS');?>
									</td>
									<td>
										<!--div class="button2-left"-->
											<div class="blank">
												<button type="button" onclick="document.location.href='#access-rules';">
												<?php echo JText::_('JGLOBAL_PERMISSIONS_ANCHOR'); ?></button>
											</div>
										<!--/div-->
									</td>
								</tr>
							<?php } ?>
							<tr>
								<td valign="top" align="right" witdth="20%">
									<?php echo JText::_('COM_RSGALLERY2_DESCRIPTION')?>
								</td width="80%">
								<td>
									<?php
										// parameters : area name, content, hidden field, width, height, rows, cols
										// display($name, $html, $width, $height, $col, $row, $buttons=true, $id=null, $params=array())
										echo $editor->display('descr',  $row->descr , '50%', '80', '10', '20' ,false);
									?>
								</td>
							</tr>
							<?php if ($canEditStateItem) {	?>
								<tr>
									<td valign="top" align="right"><?php echo JText::_('COM_RSGALLERY2_ORDERING')?></td>
									<td><?php echo $lists['ordering']; ?></td>
								</tr>
							<?php }	?>
							<tr>
								<td valign="top" align="right"><?php echo JText::_('COM_RSGALLERY2_PUBLISHED')?></td>
								<td><?php echo $lists['published']; ?></td>
							</tr>
						</table>
					</td>
					<td width="40%" valign="top">
						<table class="adminform">
							<tr>
								<th colspan="1"><?php echo JText::_('COM_RSGALLERY2_ITEM_PREVIEW')?></th>
							</tr>
							<tr>
								<td>
									<div align="center">
									<?php
									//MK returns rsgItem_image for given item id (or rsgItem_audio)
									//$item = rsgGalleryManager::getItem( $row->id ); -> getItem deprecated
									$gallery = rsgGalleryManager::get();
									$item = $gallery->getItem( $row->id );
									$original = $item->original();
									$thumb 		= $item->thumb();

									switch($item->type){
										case "audio":{
										?>
										<object type="application/x-shockwave-flash" width="400" height="15" data="<?php echo JURI_SITE ?>/components/com_rsgallery2/flash/xspf/xspf_player_slim.swf?song_title=<?php echo $row->name?>&song_url=<?php echo audioUtils::getAudio($row->name)?>"><param name="movie" value="<?php echo JURI_SITE ?>/components/com_rsgallery2/flash/xspf/xspf_player_slim.swf?song_title=<?php echo $item->title;?>&song_url=<?php echo $original->url();?>" /></object>
										<?php
											break;
										}
										case "video":{
											// OS flv player from http://www.osflv.com
										?>
										<object type="application/x-shockwave-flash"
												width="400"
												height="300"
												data="<?php echo JURI_SITE ?>/components/com_rsgallery2/flash/player.swf?movie=<?php echo $display->name; ?>" >
												<param name="movie" value="<?php echo JURI_SITE ?>/components/com_rsgallery2/flash/player.swf?movie=<?php echo $display->name; ?>" />
												<embed src="<?php echo JURI_SITE ?>/components/com_rsgallery2/flash/player.swf?movie=<?php echo $display->url(); ?>"
														width="400"
														height="340"
														allowFullScreen="false"
														type="application/x-shockwave-flash">
										</object>
										<?php
											break;
										}
										case "image":{
											$display	= $item->display();
										?>
											<img src="<?php echo $display->url() ?>" alt="<?php echo htmlspecialchars( stripslashes( $item->descr ), ENT_QUOTES );?>" />
										<?php
											break;
										}
										default:
										{
											?> Unsuported item <?php
											break;
										}
									}
									?>
										<br />
									</div>
								</td>
							</tr>
						</table>
						<table class="adminform">
							<tr>
								<th colspan="1"><?php echo JText::_('COM_RSGALLERY2_PARAMETERS')?></th>
							</tr>
							<tr>
								<td>
									<!-- Note: ToDo: the $params->render is not available in J3
										(administrator/components/com_rsgallery2/options/images.html.php lin 344) 
										
									///Try this for J3:
/*$params2 = new JForm('params');
$params2->loadFile($file);///var_dump($row);
$params2->bind( $row->params );

$fields = $params2->getFieldset('params');
foreach( $fields AS $field => $obj ){
  echo $params2->getLabel( $field, null );
  echo $params2->getInput( $field, null, null );	
}*/
///JForm has no render method as used in images.html.php line  343	
										
										-->
									<?php //echo $params->render();?>&nbsp;
								</td>
							</tr>
						</table>
						<table class="adminform">
							<tr>
								<th colspan="1"><?php echo JText::_('COM_RSGALLERY2_LINKS_TO_IMAGE')?></th>
							</tr>
							<tr>
								<td>
									<table width="100%" class="imagelist">
										<?php if ( $item->type == 'image' || $item->type == "video" ) {?>
										<tr>
											<td width="40%" align="right" valign="top">
												<a href="<?php echo $thumb->url();?>" target="_blank" title="<?php echo $item->name;?>">
													<?php echo JText::_('COM_RSGALLERY2_THUMB'); ?>
												</a>:
											</td>
											<td><input type="text" name="thumb_url" class="text_area" size="80" value="<?php echo $thumb->url();?>" /></td>
										</tr>
										<tr>
											<td width="40%" align="right" valign="top"><a href="<?php echo $display->url();?>" target="_blank" title="<?php echo $item->name;?>"><?php echo JText::_('COM_RSGALLERY2_DISPLAY'); ?></a>:
											</td>
											<td ><input type="text" name="display_url" class="text_area" size="80" value="<?php echo $display->url();?>" />
											</td>
										</tr>
										<?php }?>
										<tr>
											<td width="40%" align="right" valign="top"><a href="<?php echo $original->url();?>" target="_blank" title="<?php echo $item->name;?>"><?php echo JText::_('COM_RSGALLERY2_ORIGINAL'); ?></a>:</td>
											<td><input type="text" name="original_url" class="text_area" size="80" value="<?php echo $original->url();?>" /></td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>

			<?php
				//Get form for J!1.6 ACL rules (load library, get path to XML, get specific form)
				jimport( 'joomla.form.form' );
				JForm::addFormPath(JPATH_ADMINISTRATOR.'/components/com_rsgallery2/models/forms/');
				$form = JForm::getInstance('com_rsgallery2.params','item',array( 'load_data' => true ));

				//Get the data for the form from $row (but only matching XML fields will get data here: asset_id)
				$form->bind($row);

				//Create the rules slider at the bottom of the page
			?>

			<div class="clr"></div>

			<?php if ($canAdmin) { ?>
				<br/>
				<!--div  class="width-100 fltlft" -->
				<div  class="fltlft">
					<?php echo JHtml::_('sliders.start','permissions-sliders-'.$row->id, array('useCookie'=>1)); ?>
					<?php echo JHtml::_('sliders.panel',JText::_('COM_RSGALLERY2_FIELDSET_RULES'), 'access-rules'); ?>
					<fieldset class="panelform">
						<?php echo $form->getLabel('rules'); ?>
						<?php echo $form->getInput('rules'); ?>
					</fieldset>
					<?php echo JHtml::_('sliders.end'); ?>
			  	</div>
			<?php } ?>

			<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
			<input type="hidden" name="name" value="<?php echo $row->name; ?>" />
			<input type="hidden" name="option" value="<?php echo $option;?>" />
			<input type="hidden" name="rsgOption" value="<?php echo $rsgOption;?>" />
			<input type="hidden" name="task" value="" />
		</form>
		<?php
	}

	/**
	* Writes the edit form for new and existing record
	*
	* A new record is defined when <var>$row</var> is passed with the <var>id</var>
	* property set to 0.
	* @param array $lists An array of select lists
	* @param string $option The option
	*/
	static function uploadImage( $lists, $option ) {
		global $rsgOption;
		JHtml::_('behavior.formvalidation');
		//$editor = JFactory::getEditor();
		$editor = JFactory::getConfig()->get('editor'); // name of editor ?
		$editor = JEditor::getInstance($editor);
		
		?>
		<script type="text/javascript">
		Joomla.submitbutton = function(task) {
			if (task == 'cancel') {
				Joomla.submitform(task);
				return;
			}

			if (document.formvalidator.isValid(document.id('uploadForm'))) {
				if (document.adminForm.gallery_id.value <= '0'){
					alert('<?php echo JText::_('COM_RSGALLERY2_YOU_MUST_SELECT_A_GALLERY');?>');
					return;
				}
				Joomla.submitform(task, document.getElementById('uploadForm'));
			} else {
				alert( "<?php echo JText::_('COM_RSGALLERY2_NO_FILE_WAS_SELECTED_IN_ONE_OR_MORE_FIELDS')?>" );
			}
		}
		</script>

		<?php 
		require_once(JPATH_RSGALLERY2_ADMIN . DS . 'includes' . DS . 'script.php');
		?>
		<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="uploadForm" enctype="multipart/form-data" class="form-validate">
		<table class="adminheading">
		<tr>
			<th>
			<?php echo JText::_('COM_RSGALLERY2_ITEM')?>&nbsp;
			<?php echo JText::_('COM_RSGALLERY2_UPLOAD')?>
			</th>
		</tr>
		</table>

                <?php if (count(JHtmlSidebar::getEntries()) > 0) : ?>
                    <div id="j-sidebar-container" class="span2">
                        <?php echo JHtmlSidebar::render( ); ?>
                    </div>
                    <div id="j-main-container" class="span10">
                <?php else : ?>
                    <div id="j-main-container">
                <?php endif;?>
    
                <div class="clearfix"> </div>
    
		<table width="100%">
		<tr>
			<td width="60%" valign="top">
				<table class="adminform">
				<tr>
					<th colspan="2" width="20%">
						<?php echo JText::_('COM_RSGALLERY2_UPLOAD_DETAILS')?>
					</th>
				</tr>
				<tr>
					<td width="20%" align="right"></td>
					<td width="80%"><?php echo $lists['gallery_id']?></td>
				</tr>
				<tr>
					<td valign="top" align="right">
						<?php echo JText::_('COM_RSGALLERY2_GENERIC_DESCRIPTION')?>
					</td>
					<td>
						<?php echo $editor->display( 'descr',  '' , '100%', '200', '10', '20' ,false ) ; ?>
					</td>
				</tr>
				</table>
				<br />
				<table class="adminform">
				<tr>
					<th colspan="2">
					<?php echo JText::_('COM_RSGALLERY2_ITEM_FILES')?>
					</th>
				</tr>
				<tr>
					<td  width="20%" valign="top" align="right">
					<?php echo JText::_('COM_RSGALLERY2_ITEMS')?>
					</td>
					<td width="80%">
						<?php echo JText::_('COM_RSGALLERY2_TITLE')?>&nbsp;<input class="text" type="text" id= "title" name="title[]" value="" size="60" maxlength="250" /><br /><br />
						<?php echo JText::_('COM_RSGALLERY2_FILE')?>&nbsp;<input type="file" size="48" id="images" name="images[]" class="required" /><br /><hr />
    					<span id="moreAttachments"></span>
    					<a href="javascript:addAttachment(); void(0);"><?php echo JText::_('COM_RSGALLERY2_MORE_FILES')?></a><br />
    					<noscript><input type="file" size="48" name="images[]" /><br /></noscript>
					</td>
				</tr>
				</table>
			</td>
			<td width="40%" valign="top">
				<table class="adminform">
				<tr>
					<th colspan="1">
					<?php echo JText::_('COM_RSGALLERY2_PARAMETERS')?>
					</th>
				</tr>
				<tr>
					<td>
					<?php /*echo $params->render();*/?>
					</td>
				</tr>
				</table>
			</td>
		</tr>
		</table>
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="rsgOption" value="<?php echo $rsgOption;?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}
	
	/**
	* First step in batchupload: displays form to indicate either zip (archive) or ftp upload and
	* which gallery to use.
	*
	* Either zip (archive) or ftp upload can be choosen, gallery can be specified here or in step 2.
	* @param string The option
	*/
	static function batchupload($option) {
		/* Info for javascript on input element names and values:
		Step 1
		Button: Next -->	task=batchupload
		Radio zip/ftp name:	batchmethod (values: zip, ftp)
		filename:		zip_file
		ftppathname:		ftppath
		Radio yes/no name:	selcat (values: 1 (select now), 0 (select in step 2)
		select gal.name:	xcat
		*/
	
        global $rsgConfig, $task, $rsgOption;
		JHtml::_('behavior.formvalidation');
        $FTP_path = $rsgConfig->get('ftp_path');
        $size = round( ini_get('upload_max_filesize') * 1.024 );
        ?>
        <script language="javascript" type="text/javascript">
        Joomla.submitbutton = function(task) {
            var form = document.adminForm;
 
			//Get the upload method and gallery select method (2 options each)
            for (i=0;i<document.forms[0].batchmethod.length;i++) {
                if (document.forms[0].batchmethod[i].checked) {
                    upload_method = document.forms[0].batchmethod[i].value;
                }
            }
            for (i=0;i<document.forms[0].selcat.length;i++) {
                if (document.forms[0].selcat[i].checked) {
                    selcat_method = document.forms[0].selcat[i].value;
                }
            }

			if (task == 'batchupload') {
				// do field validation
				if (upload_method == 'zip') {
					if (form.zip_file.value == '') {
						alert( "<?php echo JText::_('COM_RSGALLERY2_ZIP-UPLOAD_SELECTED_BUT_NO_FILE_CHOSEN');?>" );
						return;
					} else if (form.xcat.value <= 0 && selcat_method == '1') {
						alert("<?php echo JText::_('COM_RSGALLERY2_PLEASE_CHOOSE_A_CATEGORY_FIRST');?>");
						return;
					} else {
						form.submit();
					}
				} else if (upload_method == 'ftp') {
					if (form.ftppath.value == '') {
						alert( " <?php echo JText::_('COM_RSGALLERY2_FTP_UPLOAD_CHOSEN_BUT_NO_FTP-PATH_PROVIDED');?>" );	
						return;
					} else if (form.xcat.value <= 0 && selcat_method == '1') {
						alert("<?php echo JText::_('COM_RSGALLERY2_PLEASE_CHOOSE_A_CATEGORY_FIRST');?>");
						return;
					} else {
						form.submit();
					}
				}
            }
        }
        </script>

        <form name="adminForm" action="index.php" method="post" enctype="multipart/form-data" class="form-validate">
            <?php if (count(JHtmlSidebar::getEntries()) > 0) : ?>
                <div id="j-sidebar-container" class="span2">
                    <?php echo JHtmlSidebar::render( ); ?>
                </div>
                <div id="j-main-container" class="span10">
            <?php else : ?>
                <div id="j-main-container">
            <?php endif;?>

            <div class="clearfix"> </div>
        <table width="100%">
        <tr>
            <!-- td width="50">&nbsp;</td-->
            <td>
				<th colspan="3"><font size="4"><?php echo JText::_('COM_RSGALLERY2_STEP_1');?></font></th>
                <table class="adminform">
                <tr>
                    <!-- th colspan="3"><font size="4"><?php echo JText::_('COM_RSGALLERY2_STEP_1');?></font></th -->
					<td>&nbsp;</td>
                </tr>
                <tr>
                    <td width="200">
                        <strong><?php echo JText::_('COM_RSGALLERY2_SPECIFY_UPLOAD_METHOD');?></strong>
                        <?php
                            echo JHtml::tooltip( JText::_('COM_RSGALLERY2_BATCH_METHOD_TIP'), JText::_('COM_RSGALLERY2_SPECIFY_UPLOAD_METHOD') );
                        ?>
                    </td>
                    <td width="200">
                        <input type="radio" value="zip" name="batchmethod" CHECKED/>
                        <?php echo JText::_('COM_RSGALLERY2_ZIP-FILE'); ?>
                    </td>
                    <td width="60%">
                        <input type="file" name="zip_file" style="border: blue solid 2px; width: 100%; padding-right: 12px;" size="40" />
                        <div style=color:#FF0000;font-weight:bold;font-size:smaller;>
                            <?php echo JText::_('COM_RSGALLERY2_UPLOAD_LIMIT_IS').' ' . $size .' '.JText::_('COM_RSGALLERY2_MEGABYTES_SET_IN_PHPINI');?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td width="200">&nbsp;
					</td>
                    <td width="200">
                        <input type="radio" value="ftp" name="batchmethod" />
                        <?php echo JText::_('COM_RSGALLERY2_FTP-PATH');?>&nbsp;<?php echo JHtml::tooltip( JText::_('COM_RSGALLERY2_BATCH_FTP_PATH_OVERL'), JText::_('COM_RSGALLERY2_FTP-PATH')); ?>
                    </td>
                    <td>
						<?php echo JText::sprintf('COM_RSGALLERY2_FTP_BASE_PATH', ""); ?><!-- br -->&nbsp;<?php echo JPATH_SITE.DS; ?>
                    </td>
                </tr>
                <tr>
                    <td width="200">&nbsp;
					</td>
                    <td width="200">&nbsp;
					<td width="60%">
                        <input type="text" name="ftppath" style="border: blue solid 2px; width: 100%; margin-bottom: 0px;
                            padding-bottom: 0px;" value="<?php echo $FTP_path; ?>" size="30" />
                        <div style="color:#FF0000;font-weight:bold;font-size:smaller;margin-top: 0px;padding-top: 0px;">
                            <?php echo JText::_('COM_RSGALLERY2_PATH_MUST_START_WITH_BASE_PATH');?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;<br /></td>
					<td>&nbsp;</td>
                </tr>
                <tr>
                <td valign="top"><strong><?php echo JText::_('COM_RSGALLERY2_SPECIFY_GALLERY');?></strong></td>
                    <td valign="top">
                        <input type="radio" name="selcat" value="1" CHECKED/>&nbsp;&nbsp;<?php echo JText::_('COM_RSGALLERY2_YES_ALL_ITEMS_IN');?>&nbsp;
                    </td>
                    <td valign="top">
                        <?php echo galleryUtils::galleriesSelectList( null, 'xcat', false, Null, 0, true);?>
                    </td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                    <td colspan="2"><input type="radio" name="selcat" value="0" />&nbsp;&nbsp;<?php echo JText::_('COM_RSGALLERY2_NO_SPECIFY_GALLERY_PER_IMAGE_IN_STEP_2'); ?></td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;<br /></td>
                </tr>
                <tr class="row1">
                    <th colspan="3">
                        <div align="center" style="visibility: hidden;">
                        <input type="button" name="something" value="<?php echo JText::_('COM_RSGALLERY2_NEXT_ARROW');?>" onClick="submitbutton('batchupload');" />
                        </div>
                        </th>
                </tr>
                </table>
            </td>
            <td width="300">&nbsp;</td>
        </tr>
        </table>
        <input type="hidden" name="uploaded" value="1" />
        <input type="hidden" name="option" value="com_rsgallery2" />
        <input type="hidden" name="rsgOption" value="<?php echo $rsgOption;?>" />
        <input type="hidden" name="task" value="batchupload" />
        <input type="hidden" name="boxchecked" value="0" />
        </form>
        <?php
        }

	/**
	* Second step in batchupload: displays all images to be uploaded. Specifics of images
	* can be changed here
	*
	* All uploaded images are shown. They can be deleted, get a title and/or description and
	* the gallery can be choosen if that wasn't done in step 1.
	* @param array $ziplist Array with stings: filenames of uploaded images
	* @param string $extractDir Location where files are extracted
	*/
	static function batchupload_2( $ziplist, $extractDir ){
		/* Info for javascript on input element names and values:
		Step 2
		Button: Upload --> 	task=save_batchupload
		Delete checkbox name: 	delete[1]
		Item title field name:	ptitle[]
		Gallery select name:	category[]
		Description area name:	descr[]
		*/
        global $rsgOption;
		JHtml::_('behavior.formvalidation');
        JHtml::_('behavior.framework');
		
		$input =JFactory::getApplication()->input;
		
		$database = JFactory::getDBO();
        //Get variables from form
        //$selcat 		= JRequest::getInt('selcat'  , null);
		$selcat         = $input->get( 'selcat', null, 'INT');
        //$ftppath 		= JRequest::getVar('ftppath'  , null);
		$ftppath 		= $input->get( 'ftppath', null, 'PATH');
        //$xcat 			= JRequest::getInt('xcat'  , null);
		$xcat           = $input->get( 'xcat', null, 'INT');
        //$batchmethod 	= JRequest::getVar('batchmethod'  , null);
		$batchmethod 		= $input->get( 'batchmethod', null, 'STRING');
        ?>
		<script language="javascript" type="text/javascript">
        Joomla.submitbutton = function(task) {
            var form = document.adminForm,
				missingCat = false,
				categories = $$('#adminForm input[name^=category]', '#adminForm select[name^=category]');
         
            for (i=0 ; i<categories.length ; i++) {
				if (categories[i].value <= 0) {
					alert("<?php echo JText::_('COM_RSGALLERY2_ALL_IMAGES_MUST_BE_PART_OF_A_GALERY');?>"+' (#'+i+')');
					return;
				}
            }
  
			if (task == 'save_batchupload'){
				Joomla.submitform(task);
			}
        }
        </script>

        <form action="index.php" method="post" name="adminForm" id="adminForm" class="form-validate">
        <table class="adminform">
        <tr>
            <th colspan="5" class="sectionname"><font size="4"><?php echo JText::_('COM_RSGALLERY2_STEP_2');?></font></th>
        </tr>
        <tr>
        <?php

        // Create selection of galleries only once
        // ToDo: in above selection (galleryUtils::galleriesSelectList) is 'xcat' chosen for $listName. Here
        // it is 'category[]'. Why
        //galleriesSelectList( $galleryid = null, $listName = 'gallery_id', $style = true,
        //      $javascript = NULL, $showUnauthorised = 1, $excludeTopGallery = false)
        //galleryUtils::galleriesSelectList( null, 'xcat', false, Null, 0, true);
        $GalleryListSelection = galleryUtils::galleriesSelectList( null, 'category[]', false, Null, 0, true);

        // Initialize k (the column reference) to zero.
        $k = 0;
        $i = 0;

        foreach ($ziplist as $filename) {
        	$k++;
        	//Check if filename is dir
        	if ( is_dir(JPATH_ROOT. DS . 'media' . DS . $extractDir . DS . $filename) ) {
        		continue;
        	} else {
        		//Check if file is allowed
        		$allowed_ext = array('gif','jpg','png');
        		$allowedVideo_ext = array('flv','avi','mov');
        		$ext = fileHandler::getImageType( JPATH_ROOT. DS . 'media' . DS . $extractDir . DS . $filename );
				if ( in_array($ext, $allowedVideo_ext) ) {
        			// build preview image
					$basePath = JPATH_SITE . DS .'media' . DS . $extractDir . DS;
					require_once( JPATH_RSGALLERY2_ADMIN. DS .'includes' . DS . 'video.utils.php' );
					Ffmpeg::capturePreviewImage( $basePath . $filename, $basePath . $filename . '.png');
					$displayImage = $filename . '.png';
					$i++;
				}
				else{
					if ( !in_array($ext, $allowed_ext) ) {
        				continue;
        			} else {
						$displayImage = $filename;
        				$i++;
        			}
				}
        	}
            ?>
            <td align="center" valign="top" bgcolor="#CCCCCC">
                <table class="adminform" border="0" cellspacing="1" cellpadding="1">
                    <tr>
                        <th colspan="2">&nbsp;</th>
                    </tr>
                    <tr>
                        <td colspan="2" align="right"><?php echo JText::_('COM_RSGALLERY2_DELETE');?> #<?php echo $i - 1;?>: <input type="checkbox" name="delete[<?php echo $i - 1;?>]" value="true" /></td>
                    </tr>
                    <tr>
                        <td align="center" colspan="2">
                        	<img src="<?php echo JURI_SITE . "/media/" . $extractDir . "/" . $displayImage;?>" alt="" border="1" width="100"  />
                        <input type="hidden" value="<?php echo $filename;?>" name="filename[]" />
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo JText::_('COM_RSGALLERY2_TITLE');?></td>
                        <td>
                            <input type="text" name="ptitle[]" size="15" />
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo JText::_('COM_RSGALLERY2_GALLERY');?></td>
                        <td><?php
                            if ($selcat == 1 && $xcat !== '0')
                            {
                                ?>
                                <input type="text" name="cat_text" value="<?php echo htmlspecialchars(stripslashes(galleryUtils::getCatnameFromId($xcat)));?>" readonly />
                                <input type="hidden" name="category[]" value="<?php echo $xcat;?>" />
                                <?php
                            }
                            else
                            {
    							// echo galleryUtils::galleriesSelectList( null, 'category[]', false );
                                echo $GalleryListSelection;
                            }
                             ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo JText::_('COM_RSGALLERY2_DESCRIPTION');?></td>
                        <td><textarea cols="15" rows="2" name="descr[]"></textarea></td>
                    </tr>
                </table>
            </td>
            <?php
            if ($k == 5)
            {
                echo "</tr><tr>";
                $k = 0;
            }
        }
        ?>
        </table>

        <input type="hidden" name="teller" value="<?php echo $i;?>" />
        <input type="hidden" name="extractdir" value="<?php echo $extractDir;?>" />
        <input type="hidden" name="option" value="com_rsgallery2" />
        <input type="hidden" name="rsgOption" value="<?php echo $rsgOption;?>" />
        <input type="hidden" name="task" value="save_batchupload" />

        </form>
        <?php
	}
}
