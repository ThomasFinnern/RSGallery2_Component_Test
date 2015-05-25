<?php
/**
* Maintenance option for RSGallery2 - HTML display code
* @version $Id: maintenance.html.php 1037 2011-08-03 14:22:00Z mirjam $
* @package RSGallery2
* @copyright (C) 2003 - 2006 RSGallery2
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* RSGallery is Free Software
*/

// no direct access
defined( '_JEXEC' ) or die();

/**
 * Handles HTML screens for image option 
 * @package RSGallery2
 */
class html_rsg2_maintenance {

	/**
	 *
	 */
	static function showMaintenanceCP() {
		?>
		<div id="rsg2-thisform">
		<div id='cpanel'>
			<p>
				<?php
				$link = 'index.php?option=com_rsgallery2&amp;rsgOption=maintenance&amp;task=consolidateDB';
				html_rsg2_maintenance::quickiconBar( $link, 'blockdevice.png', 
					JText::_('COM_RSGALLERY2_MAINT_CONSOLDB'), JText::_('COM_RSGALLERY2_MAINT_CONSOLDB_TXT') );
				?>
				<br>
				<br>
				<br>
				<br>
			</p>
			<p>
				<?php
				$link = 'index.php?option=com_rsgallery2&amp;rsgOption=maintenance&amp;task=regenerateThumbs';
				html_rsg2_maintenance::quickiconBar( $link, 'menu.png', 
					JText::_('COM_RSGALLERY2_MAINT_REGEN'), JText::_('COM_RSGALLERY2_MAINT_REGEN_TXT') );
				?>
				<br>
				<br>
				<br>
				<br>
			</p>
			<p>
				<?php
				$link = 'index.php?option=com_rsgallery2&amp;rsgOption=maintenance&amp;task=optimizeDB';
				html_rsg2_maintenance::quickiconBar( $link, 'db_optimize.png', JText::_('COM_RSGALLERY2_MAINT_OPTDB'), 
					JText::_('COM_RSGALLERY2_MAINT_OPTDB_TXT') );
				?>
				<br>
				<br>
				<br>
				<br>
			</p>
		</div>
		<div class='rsg2-clr'>&nbsp;</div>
		</div>
		<?php
	}
	
	 /**
      * Used by showCP to generate buttons
      * @param string $link URL for button link
      * @param string $image Image name for button image
	  * @param $title
	  * @param string $text Text to show in button
      */
	static function quickiconBar( $link, $image, $title, $text = "" ) {
	    ?>
	    <div style="float:left;">
	    <div class="icon-bar">
	        <a href="<?php echo $link; ?>">
	            <div class="iconimage">
	                <div class="rsg2-icon"><img src="<?php echo JURI_SITE;?>/administrator/components/com_rsgallery2/images/<?php echo $image;?>" alt="alternate text" /></div>
					<div class="rsg2-text">
						<span class="maint-title"><?php echo $title;?></span>
						<span class="maint-text"><?php echo $text;?></span></div>
	            </div>
	        </a>
	    </div>
	    </div>
	    <div class='rsg2-clr'>&nbsp;</div>
	    <?php
	}

	/**
	 * @param $lists
	 */
	static function regenerateImages($lists) {
		global $rsgConfig;
		?>
		<script language="Javascript">
	        // old:function submitbutton(pressbutton){
	        Joomla.submitbutton = function(pressbutton) {
				
	            var form = document.adminForm;
	            
				if (pressbutton != 'cancel'){
	                Joomla.submitform( pressbutton );
	                return;
	            } else {
	                window.history.go(-1);
	                return;
	                
	            }
	        }
	    </script>
		<form name="adminForm" method="post" action="index.php">
		<table width="500">
		<tr>
			<td>
			<table class="adminform">
				<tr>
					<td valign="top" width="300"><p><?php echo JText::_('COM_RSGALLERY2_SELECT_GALLERIES_TO_REGENERATE_THUMBNAILS_FROM')?></p></td>
					<td valign="top">
						<fieldset>
						<legend><?php echo JText::_('COM_RSGALLERY2_SELECT_GALLERY')?></legend>
						<?php echo $lists['gallery_dropdown'];?>
						</fieldset>
						<p>
							<?php echo JText::sprintf('COM_RSGALLERY2_NEW_WIDTH_DISPLAY', $rsgConfig->get('image_width'))?>
							<br>
							<?php echo JText::sprintf('COM_RSGALLERY2_NEW_WIDTH_THUMB', $rsgConfig->get('thumb_width'))?>
						</p>
					</td>
					<td width="10">&nbsp;</td>
				</tr>
				<tr>
					<td colspan="3"></td>
				</tr>
			</table>
			</td>
		</tr>
		</table>
		<input type="hidden" name="option" value="com_rsgallery2" />
		<input type="hidden" name="rsgOption" value="maintenance" />
	    <input type="hidden" name="task" value="" />
		</form>
		<?php
	}

	/**
	 * @param $db_name
	 * @param $file_display
	 * @param $file_original
	 * @param $file_thumb
	 * @param $files_total
	 */
	static function consolidateDB($db_name, $file_display, $file_original, $file_thumb, $files_total) {
	    global $rsgConfig;
	    require_once( JPATH_RSGALLERY2_ADMIN.'/config.rsgallery2.php' );
	    $file_diff = array_diff($files_total, $db_name);
	    ?>
	    <script language="Javascript">
	    function db_create() {
	    	alert('Database creation does not work yet!');
	    }	    
	    </script>
	    <form method="post" action="index.php?option=com_rsgallery2&rsgOption=maintenance&task=createDbEntries" name="adminForm">
	    <input type="hidden" name="t_id" value="" />
	    <input type="hidden" name="g_id" value="" />
	    <table width="100%" border="0">
	    	<tr>
	    	<td width="15%">&nbsp;</td>
	    	<td width="70%">
			    <table class="adminlist" border="0">
			    <tr>
			    	<td colspan="9" align="center">
				    	<div style="clear: both; margin: 3px; margin-top: 10px; padding: 5px 15px; display: block; float: left; border: 1px solid #cc0000; background: #ffffcc; text-align: left; width: 80%;">
		    				<p style="color: #CC0000;">
		    				<img src="<?php echo JURI_SITE;?>/includes/js/ThemeOffice/warning.png" alt="Warning icon" />
							NOTICE:<br />Experimental at this stage. Single image regeneration works. <br /> Database entries do NOT work!.
							<?php //echo JText::_('COM_RSGALLERY2_CONSDB_NOTICE');?>
							</p>
						</div>
						<div class='rsg2-clr'>&nbsp;</div>
			    	</td>
			    </tr>
			    <tr>
			    	<th>#</th>
			        <th><?php echo JText::_('COM_RSGALLERY2_FILENAME');?></th>
			        <th align="center"><?php echo JText::_('COM_RSGALLERY2_IN-DATABASE');?></th>
			        <th align="center"><?php echo JText::_('COM_RSGALLERY2_DISPLAY-FOLDER');?></th>
		        	<th align="center"><?php echo JText::_('COM_RSGALLERY2_ORIGINAL-FOLDER');?></th>
			        <th align="center"><?php echo JText::_('COM_RSGALLERY2_THUMB-FOLDER');?></th>
			        <th>&nbsp;</th>
			        <th align="center"><?php echo JText::_('COM_RSGALLERY2_IMAGE');?></th>
			        <th align="center"><?php echo JText::_('COM_RSGALLERY2_ACTION');?></th>
			    </tr>
			    <tr>
			        <td colspan="9">&nbsp;</td>
			    </tr>
			    <?php 
			    $yes    = '<td align="center"><img src="'.JURI_SITE.'administrator/components/com_rsgallery2/images/tick.png" alt="'.JText::_('COM_RSGALLERY2_IMAGE_IN_FOLDER').'" border="0"></td>';
				$no     = '<td align="center"><img src="'.JURI_SITE.'administrator/components/com_rsgallery2/images/publish_x.png" alt="'.JText::_('COM_RSGALLERY2_IMAGE_NOT_IN_FOLDER').'" border="0"></td>';				
			    $z = 0;
			    $c = 0;
			    //Check database and crossreference against filesystem
			    foreach ($db_name as $name)
			        {
			        $c++;
			        $i = 0;
			        $fid = galleryUtils::getFileIdFromName($name);
			        $html = "<td><input type=\"checkbox\" id=\"cb$c\" name=\"xid[]\" value=\"$name\" onclick=\"isChecked(this.checked);\" /></td><td>$name</td>".$yes;
			        if (in_array($name, $file_display )) {
			            $i++;
			            $html .= $yes;
			            $display = true;
			        } else {
			            $z++;
			            $html .= $no;
			            $display = false;
					}
			        if (in_array($name, $file_original )) {
			            $i++;
			            $html .= $yes;
			            $original = true; 
			        } else {
			            $z++;
			            $html .= $no;
			            $original = false;
					}
			        if (in_array($name, $file_thumb )) {
			            $i++;
			            $html .= $yes;
			            $thumb = true;
					} else {
			            $z++;
			            $html .= $no;
			            $thumb = false;
					}
					
			        if ($i < 3) {
                        ?>
                        <tr>
                            <?php
                            echo $html;
                            ?>
                            <td>&nbsp;</td>
                            <td>
                                <img src="<?php echo imgUtils::getImgThumb( $name );?>" name="image" width="<?php echo $rsgConfig->get('thumb_width')?>" alt="<?php echo $name;?>"/>
                            </td>
                            <td align="center">
                                <a href="index.php?option=com_rsgallery2&rsgOption=maintenance&task=deleteImages&name=<?php echo $name;?>"><?php echo '[&nbsp;'.JText::_('COM_RSGALLERY2_DELETE_FROM_DATABASE').'&nbsp;]'?></a><br />
                                <?php
                                if ($original == true OR $display == true) {
                                    ?>
                                    <a href="index.php?option=com_rsgallery2&rsgOption=maintenance&task=createImages&id=<?php echo $fid;?>"><?php echo '[&nbsp;'.JText::_('COM_RSGALLERY2_CREATE_MISSING_IMAGES').'&nbsp;]'?></a>
                                    <?php
                                    }
                                    ?>
                            </td>
			            </tr>
			            <?php
			        } else {
			            continue;
					}
				}
			    ?>
			    </tr>
			    
			    <?php
			    $zz = 0;
			    $t = 0;
			    //Check filesystem and crossreference against database
			    foreach ($file_diff as $diff) {
			        $t++;
			        $y = 0;
			        
			        $html2 = "<tr><td><input type=\"checkbox\" id=\"cb$t\" name=\"xid[]\" value=\"$t\" onclick=\"isChecked(this.checked);\" /></td><td><font color=\"#FF0000\">$diff</font></td>$no";
			        if (in_array($diff, $file_display ))
			            {
			            $y++;
			            $html2 .= $yes;
			            $display2 = true;
			            }
			        else
			            {
			            $zz++;
			            $html2 .= $no;
			            $display2 = false;
			            }
			        if (in_array($diff, $file_original ))
			            {
			            $y++;
			            $html2 .= $yes;
			            $original2 = true;
			            }
			        else
			            {
			            $zz++;
			            $html2 .= $no;
			            $original2 = false;
			            }
			        if (in_array($diff, $file_thumb ))
			            {
			            $y++;
			            $html2 .= $yes;
			            $thumb2 = true;
			            }
			        else
			            {
			            $zz++;
			            $html2 .= $no;
			            $thumb2 = false;
			            }
			        if ($y < 4)
			            {
			            echo $html2;
			            ?>
			            <td>
			            	<?php echo galleryUtils::galleriesSelectList(NULL,'gallery_id[]', false, false);?>
			            	<input type="hidden" name="name[]" value="<?php echo $diff;?>" />
			            </td>
			            <td>
			            	<img src="<?php echo imgUtils::getImgThumb( $diff );?>" name="image" width="<?php echo $rsgConfig->get('thumb_width')?>" />
			            </td>
			            <td align="center">
			                <a href="javascript:void();" onClick="javascript:db_create();"><?php echo '[&nbsp;'.JText::_('COM_RSGALLERY2_CREATE_DATABASE_ENTRY').'&nbsp;]';?></a><br />
			                <a href="index.php?option=com_rsgallery2&rsgOption=maintenance&task=deleteImages&name=<?php echo $diff;?>"><?php echo '[&nbsp;'.JText::_('COM_RSGALLERY2_DELETE_IMAGES'),'&nbsp;]'?></a>&nbsp;
			                <?php
			                if ($original2 == true AND $display2 == true AND $thumb2 == true)
			                    {
			                    continue;
			                    }
			                else
			                    {
			                    ?>
			                    <br /><a href="index.php?option=com_rsgallery2&rsgOption=maintenance&task=createImages&name=<?php echo $diff;?>"><?php echo '[&nbsp;'.JText::_('COM_RSGALLERY2_CREATE_MISSING_IMAGES').'&nbsp;]';?></a>
			                    <?php
			                    }
			                    ?>
			            </td>
			            <?php
			            }
			        else
			            {
			            continue;
			            }
			        }
			        if ($t == 0 AND $z == 0)
			        	echo "<tr><td colspan=\"8\"><font color=\"#008000\"><strong>".JText::_('COM_RSGALLERY2_NO_INCONSISTENCIES_IN_DATABASE')."</strong></font></td>";
			    ?>
			    </tr>
			    <tr>
			        <th colspan="9" align="center">
			        	<a href="index.php?option=com_rsgallery2&rsgOption=maintenance&task=consolidateDB">Refresh</a>
			        </th>
			    </tr>
			    <!--
			    <tr>
			    	<td colspan="2"><input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo (count( $db_name ) + count( $file_diff )); ?>);" /></td>
			    	<td colspan="5"> With selection:<br /> 
			    		<a href="javascript:void();" onClick="javascript:alert('<?php echo JText::_('COM_RSGALLERY2_NOT_WORKING_YET') ?>');"><?php echo JText::_('COM_RSGALLERY2_DELETE_FROM_FILESYSTEM') ?></a>&nbsp;|&nbsp; 
			    		<a href="javascript:void();" onClick="javascript:alert('<?php echo JText::_('COM_RSGALLERY2_NOT_WORKING_YET') ?>');"><?php echo JText::_('COM_RSGALLERY2_CREATE_MISSING_IMAGES')?></a>&nbsp;|&nbsp;
			    		<a href="javascript:void();" onClick="javascript:alert('<?php echo JText::_('COM_RSGALLERY2_NOT_WORKING_YET') ?>');"><?php echo JText::_('COM_RSGALLERY2_CREATE_DATABASE_ENTRIES')?></a>
			    	</td>
			
			    </tr>
			    -->
			    </table>
	    </td>
	    <td width="15%">&nbsp;</td>
	    </tr>
	    </table>
	    </form>
	    <?php
	}
}
?>