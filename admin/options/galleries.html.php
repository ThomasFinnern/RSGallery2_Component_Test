<?php
/**
* Galleries option for RSGallery2 - HTML display code
* @version $Id: galleries.html.php 1085 2012-06-24 13:44:29Z mirjam $
* @package RSGallery2
* @copyright (C) 2003 - 2011 RSGallery2
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* RSGallery is Free Software
*/

defined( '_JEXEC' ) or die();

/**
 * Explain what this class does
 * @package RSGallery2
 */
class html_rsg2_galleries{
    /**
     * show list of galleries
     */
	/**
	 * @param $rows
	 * @param $lists
	 * @param $search
	 * @param $pageNav
	 * @throws Exception
	 */
    static function show( &$rows, &$lists, &$search, &$pageNav ){
        global $rsgOption; // , $rsgConfig;

		//$option = JRequest::getCmd('option');
		$input =JFactory::getApplication()->input;
		$option = $input->get( 'option', '', 'CMD');	

		$user = JFactory::getUser();
		$userId = $user->id;
		JHtml::_("behavior.framework");

		//Create 'lookup array' to find whether or not galleries with the same parent
		// can move up/down in their order: $orderLookup[id parent][#] = id child
		$orderLookup = array();
		foreach ($rows as $row) {
			$orderLookup[$row->parent][] = $row->id;
		}   		
		
        ?>
        <form action="index.php" method="post" name="adminForm" id="adminForm">
		<!--form action="<?php echo JRoute::_('index.php?option=com_rsg2&view=galleries'); ?>" method="post" name="adminForm" id="adminForm"-->
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
                    <td width="50%">
                    &nbsp;
                    </td>
                    <td style="white-space:nowrap;" width="50%" align="right">
                        <?php echo JText::_('COM_RSGALLERY2_MAX_LEVELS')?>
                        <?php echo $lists['levellist'];?>
                        <?php echo JText::_('COM_RSGALLERY2_FILTER')?>:
                        <input type="text" name="search" value="<?php echo $search;?>" class="text_area" onChange="document.adminForm.submit();" />
                    </td>
                </tr>
            </table>
            <!-- ?php if (empty($this->items)) : ? -->
            <?php if (count( $rows ) == 0) : ?>
                <div class="alert alert-no-items">
                    <?php echo JText::_('COM_RSGALLERY2_NO_GALLERY_ASSIGNED'); ?>
                </div>
            <?php else : ?>
                <table class="adminlist table table-striped" id="GalleryList">
                    <thead>
                        <tr>
                            <th width="1%">
                                ID
                            </th>
                            <th width="1%">
                                <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count( $rows ); ?>);" />
                            </th>
                            <th class="Name">
                                <?php echo JText::_('COM_RSGALLERY2_NAME')?>
                            </th>
                            <th width="5%">
                                <?php echo JText::_('COM_RSGALLERY2_PUBLISHED')?>
                            </th>
                            <th width="5%">
                                <?php echo JText::_('JGRID_HEADING_ACCESS')?>
                            </th>
                            <th width="5%">
                                <?php echo JText::_('COM_RSGALLERY2_REORDER')?>
                            </th>
                            <th width="2%">
                                <?php echo JText::_('COM_RSGALLERY2_ORDER')?>
                            </th>
                            <th width="2%">
                                <?php echo JHtml::_('grid.order',  $rows); ?>
                            </th>
                            <th width="5%">
                                <?php echo JText::_('COM_RSGALLERY2_ITEMS')?>
                            </th>
                            <th width="5%">
                                <?php echo JText::_('COM_RSGALLERY2_HITS')?>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $k = 0;
                        for ($i=0, $n=count( $rows ); $i < $n; $i++) {
                            $row = &$rows[$i];

                            $link   = "index.php?option=$option&rsgOption=$rsgOption&task=editA&hidemainmenu=1&id=". $row->id;

                            $task   = $row->published ? 'unpublish' : 'publish';
                            $img    = $row->published ? 'publish_g.png' : 'publish_x.png';
                            $alt    = $row->published ? 'Published' : 'Unpublished';

                            $checked    = JHtml::_('grid.checkedout', $row, $i );

                            //Get permissions
                            $can['EditGallery']		= $user->authorise('core.edit',		'com_rsgallery2.gallery.'.$row->id);
                            $can['EditOwnGallery']	= $user->authorise('core.edit.own',	'com_rsgallery2.gallery.'.$row->id) AND ($row->uid == $userId);
                            $can['EditStateGallery']	= $user->authorise('core.edit.state','com_rsgallery2.gallery.'.$row->id);

                            //Use the $orderLookup array to determine if for the same
                            // parent one can still move up/down. First look up the parent info.
                            // combine this with permission
                            $orderkey = array_search($row->id, $orderLookup[$row->parent]);
                            $showMoveUpIcon		= ((isset($orderLookup[$row->parent][$orderkey - 1])) AND ($can['EditStateGallery']));
                            $showMoveDownIcon	= ((isset($orderLookup[$row->parent][$orderkey + 1])) AND ($can['EditStateGallery']));
                            $disabled = $can['EditStateGallery'] ?  '' : 'disabled="disabled"';

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
                                    if ( $row->checked_out && ( $row->checked_out != $user->id ) OR !($can['EditGallery'] OR $can['EditOwnGallery'])) {
                                        echo stripslashes($row->treename);
                                    } else {
                                        ?>
                                        <a href="<?php echo $link; ?>" name="Edit Gallery">
                                        <?php echo stripslashes($row->treename); ?>
                                        </a>
                                        <?php
                                    }
                                    ?>

                                    <a href="<?php echo JRoute::_('index.php?option=com_rsgallery2&rsgOption=images&gallery_id='.$row->id); ?>" >
                                        <img src="<?php echo 'templates/bluestork/images/j_arrow.png';?>"  style="margin: 0px 20px" alt="<?php echo JText::_('COM_RSGALLERY2_ITEMS'); ?>" />
                                    </a>

                                </td>
                                <td align="center">
                                    <?php echo JHtml::_('jgrid.published', $row->published, $i, '', $can['EditStateGallery']); ?>
                                </td>
                                <td>
                                    <?php echo $row->access_level;?>
                                </td>
                                <td class="order">
                                    <span>
                                    <?php echo $pageNav->orderUpIcon( $i , $showMoveUpIcon ); ?>
                                    </span>
                                    <span>
                                    <?php echo $pageNav->orderDownIcon( $i, $n , $showMoveDownIcon); ?>
                                    </span>
                                </td>
                                <td colspan="2" align="center">
                                    <input type="text" name="order[]" <?php echo $disabled; ?> size="5" value="<?php echo $row->ordering; ?>" class="text_area" style="text-align: center" />
                                </td>
                                <td align="center">
                                    <?php $gallery = rsgGalleryManager::get( $row->id ); echo $gallery->itemCount()?>
                                </td>
                                <td align="left">
                                    <?php echo $row->hits; ?>
                                </td>
                            </tr>
                            <?php
                            $k = 1 - $k;
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="10"><?php echo $pageNav->getListFooter(); ?></td>
                        </tr>
                    </tfoot>
                </table>
            <?php endif; ?>
			</div> <!-- j-main-container -->
            <input type="hidden" name="option" value="<?php echo $option;?>" />
            <input type="hidden" name="rsgOption" value="<?php echo $rsgOption;?>" />
            <input type="hidden" name="task" value="" />
            <input type="hidden" name="boxchecked" value="0" />
            <input type="hidden" name="hidemainmenu" value="0" />
        </form>
        <?php
    }

    /**
     * warns user what will be deleted
     */
	/**
	 * @param $galleries
	 * @throws Exception
	 */
    static function removeWarn( $galleries ){
        global $rsgOption;
		//$option = JRequest::getCmd('option');
        $input =JFactory::getApplication()->input;
		$option = $input->get( 'option', '', 'CMD');

// ToDo FIX: Undefined $rsgConfig
        $config = get_object_vars( $rsgConfig );
        ?>
        <form action="index.php" method="post" name="adminForm">
        <input type="hidden" name="option" value="<?php echo $option;?>" />
        <input type="hidden" name="rsgOption" value="<?php echo $rsgOption;?>" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0" />

<!--         these are the galleries the user has chosen to delete: -->
        <?php foreach( $galleries as $g ): ?>
            <input type="hidden" name="cid[]" value="<?php echo $g->get('id'); ?>" />
        <?php endforeach; ?>
        
        <h2>The following will be deleted:</h2>
        <div style='text-align: left;' >

        <?php html_rsg2_galleries::printTree( $galleries ); ?>
        
        </div>
        </form>
        <?php
    }

	/**
	 * @param $galleries
	 */
    static function printTree( $galleries ){
        echo "<ul>";

        foreach( $galleries as $g ){
            // print gallery details
            echo "<li>". $g->get('name') ." (". count($g->itemRows()) ." images)";
            html_rsg2_galleries::printTree( $g->kids() );
            echo "</li>";
        }
        echo "</ul>";
    }

	/**
	* Writes the edit form for new and existing record
	*
	* A new record is defined when <var>$row</var> is passed with the <var>id</var>
	* property set to 0.
	* @param rsgGallery $row The gallery object
	* @param array $lists An array of select lists
	* @param object $params Parameters
	* @param string $option The option
	*/
	static function edit( &$row, &$lists, &$params, $option ) {
		global $rsgOption, $rsgConfig;

		JHtml::_('behavior.formvalidation');
		jimport("joomla.filter.output");
		$user = JFactory::getUser();
		// $editor = JFactory::getEditor();
		$editor = JFactory::getConfig()->get('editor');
		$editor = JEditor::getInstance($editor);
		
		JFilterOutput::objectHTMLSafe( $row, ENT_QUOTES );

		//Can user see/change permissions?
		$canAdmin = $user->authorise('core.admin', 'com_rsgallery2');
		$canEditStateGallery = $user->authorise('core.edit.state','com_rsgallery2.gallery.'.$row->id);

		//Get form for J!1.6 ACL rules (load library, get path to XML, get form)
		jimport( 'joomla.form.form' );
		JForm::addFormPath(JPATH_ADMINISTRATOR.'/components/com_rsgallery2/models/forms/');
		$form = JForm::getInstance('com_rsgallery2.params','gallery',array( 'load_data' => true ));
		//Get the data for the form from $row (but only matching XML fields will get data here: asset_id)
		$form->bind($row);

		//--- title of form ----------------------------
		// image exists
		if ($row->id > 0)
		{
			JToolBarHelper::title( JText::_('COM_RSGALLERY2_EDIT_GALLERY'), 'generic.png' );
		}
		else
		{
			$Text = JText::_('COM_RSGALLERY2_NEW') . " " . JText::_('COM_RSGALLERY2_GALLERY');
			JToolBarHelper::title($Text, 'generic.png' );
		}
		
		//$task = JRequest::getCmd( 'task'  , '');
		$input =JFactory::getApplication()->input;
		$task = $input->get( 'task', '', 'CMD');		
	
		JHtml::_("Behavior.framework");
		?>
		<script type="text/javascript">
		Joomla.submitbutton = function(task) {
			var form = document.adminForm;
			
			if (task == 'cancel') {
				Joomla.submitform(task);
				return;
			}
			
			if (document.formvalidator.isValid(document.id('adminForm'))) {
				Joomla.submitform(task);
				return;
			} else {
				alert( "<?php echo JText::_('COM_RSGALLERY2_YOU_MUST_PROVIDE_A_GALLERY_NAME');?>" );
				return;
			}
		}
	
		function selectAll() {
			if(document.adminForm.checkbox0.checked) {
				for (i = 0; i < 12; i++) {
					document.getElementById('p' + i).checked=true;
				}
			} else {
				for (i = 0; i < 12; i++) {
					document.getElementById('p' + i).checked=false;
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
					<td width="20%" align="right">
					<?php echo JText::_('COM_RSGALLERY2_NAME')?>
					</td>
					<td width="80%">
					<input class="text_area required" type="text"  name="name" size="50" maxlength="250" value="<?php echo stripslashes($row->name);?>"/>
					</td>
				</tr>
				<tr>
					<td width="20%" align="right">
					<?php echo JText::_('COM_RSGALLERY2_ALIAS')?>
					</td>
					<td width="80%">
					<input class="text_area" type="text" name="alias" size="50" maxlength="250" value="<?php echo stripslashes($row->alias);?>" />
					</td>
				</tr>
				<tr>
					<td align="right">
					<?php echo JText::_('COM_RSGALLERY2_OWNER');?>
					</td>
					<td>
					<?php echo $lists['uid']; ?>
					</td>
				</tr>
				<tr>
					<td size="2" align="right">
						<?php echo JText::_('JFIELD_ACCESS_LABEL');?>
					</td>
					<td>
						<div>
						<?php 
						//3rd argument = id selected, e.g. 1: Public, 2: Registered, etc.
						echo JHtml::_('access.assetgrouplist', 'access', $row->access);
						?>
						</div>
					</td>
				</tr>
<?php			if ($canAdmin) { ?>
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
<?php			} ?>
				<tr>
					<td valign="top" align="right">
					<?php echo JText::_('COM_RSGALLERY2_DESCRIPTION')?>
					</td>
					<td>
					<?php
					// parameters : area name, content, hidden field, width, height, rows, cols
					echo $editor->display ( 'description',  stripslashes($row->description) , '100%', '300', '10', '20' ,false); 
					?>
					</td>
				</tr>
				<tr>
					<td align="right">
					<?php echo JText::_('COM_RSGALLERY2_PARENT_ITEM');?>
					</td>
					<td>
					<?php echo $lists['parent']; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" align="right">
					<?php echo JText::_('COM_RSGALLERY2_GALLERY_THUMBNAIL');?>
					</td>
					<td>
					<?php echo imgUtils::showThumbNames($row->id, $row->thumb_id); ?>
					</td>
				</tr>
		<?php	if ($canEditStateGallery) {	?>
				<tr>
					<td valign="top" align="right">
					<?php echo JText::_('COM_RSGALLERY2_ORDERING');?>
					</td>
					<td>
					<?php echo $lists['ordering']; ?>
					</td>
				</tr>
		<?php	}	?>
				<tr>
					<td valign="top" align="right">
					<?php echo JText::_('COM_RSGALLERY2_PUBLISHED');?>
					</td>
					<td>
					<?php echo $lists['published']; ?>
					</td>
				</tr>
				</table>
			</td>
<!--		<?php // Removed the parameters section of the gallery for J3 (Backend > RSGallery2 > Galleries > Edit a gallery, there used to be an unused Parameters section on the right"?>
			<td width="40%" valign="top">
				<table class="adminform">
				<tr>
					<th colspan="1">
					<?php //echo JText::_('COM_RSGALLERY2_PARAMETERS');?>
					</th>
				</tr>
				<tr>
					<td>
					<?php //echo $params->render();?>
					</td>
				</tr>
				</table><br/>
			</td>
-->
		</tr>
		</table>

		<div class="clr"></div>

		<?php 	//Create the rules slider at the bottom of the page
			if ($canAdmin) { 
		?>
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
<?php 	} ?>

		<input type="hidden" name="id" value="<?php echo $row->id; ?>" />
		<input type="hidden" name="rsgOption" value="<?php echo $rsgOption;?>" />
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		</form>
		<?php
	}
}