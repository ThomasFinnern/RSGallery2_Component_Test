<?php
/**
* This file contains the HTML for the search library.
* @version xxx
* @package RSGallery2
* @copyright (C) 2003 - 2006 RSGallery2
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* RSGallery is Free Software
*/

defined( '_JEXEC' ) or die();

class html_rsg2_search {
	
	static function showSearchBox() {
		$document= JFactory::getDocument();

		if($document->getType() == 'html') {
			$document->addStyleSheet(JURI_SITE."/components/com_rsgallery2/lib/rsgcomments/rsgsearch.css");
		}
    	?>

    	<div align="right">
    	<form name="rsg2_search" method="post" action="<?php echo JRoute::_('index.php');?>">
    		<?php echo JText::_('COM_RSGALLERY2_SEARCH');?>
    		<input type="text" name="searchtext" class="searchbox" onblur="if(this.value=='') this.value='<?php echo JText::_('COM_RSGALLERY2_KEYWORDS');?>';" onfocus="if(this.value=='<?php echo JText::_('COM_RSGALLERY2_KEYWORDS');?>') this.value='';" value='<?php echo JText::_('COM_RSGALLERY2_KEYWORDS');?>' />
			<input type="hidden" name="option" value="com_rsgallery2" />
			<input type="hidden" name="rsgOption" value="search" />
			<input type="hidden" name="task" value="showResults" />
    	</form>
    	</div>
    	<?php
    }
	
	function showResults($result, $searchtext) {
		global $rsgConfig;
		html_rsg2_search::showSearchBox();
		//Format number of hits
		$count = count($result);
		$count = "<span style=\"font-weight:bold;\">".$count."</span>";
		?>
		<table width="100%" style="border-bottom: thin solid #CCCCCC;">
		<tr>
			<td><div align="right"><a href="index.php?option=com_rsgallery2"><?php echo JText::_('COM_RSGALLERY2_MAIN_GALLERY_PAGE');?></a></div></td>
		</tr>
		<tr>
			<td><h3><?php echo JText::_('COM_RSGALLERY2_RSGALLERY2_SEARCH_RESULTS');?></h3></td>
		</tr>
		<tr>
			<td>
				<span style="font-style:italic;">
				<?php echo JText::_('COM_RSGALLERY2_THERE_ARE') .' '. $count . JText::_('COM_RSGALLERY2_RESULTS_FOR');?>
					<span style="font-weight:bold;";><?php echo $searchtext;?></span>
				<span>
			</td>
		</tr>
		</table>
		<br />
		<?php
		if ($result) {
			foreach ($result as $match) {
				?>
				<table width="100%" border="0" style="border-bottom: thin solid #CCCCCC;">
				<tr>
					<td width="<?php echo $rsgConfig->get('thumb_width');?>">
					<div class="img-shadow">
						<a href="index.php?option=com_rsgallery2&page=inline&id=<?php echo $match->item_id;?>">
						<img border="0" src="<?php echo JURI_SITE.$rsgConfig->get('imgPath_thumb') . "/" . imgUtils::getImgNameThumb($match->itemname);?>" alt="image" />
						</a>
					</div>
					</td>
					<td valign="top">
						<a href="index.php?option=com_rsgallery2&page=inline&id=<?php echo $match->item_id;?>">
							<span style="font-weight:bold;"><?php echo galleryUtils::highlight_keywords($match->title, $searchtext);?></span>
						</a>
						<p><?php echo galleryUtils::highlight_keywords($match->descr, $searchtext);?></p>
						<p style="color: #999999;font-size:10px;">
							[<?php echo JText::_('COM_RSGALLERY2_GALLERY_NAME');?>:<a href="<?php echo JRoute::_("index.php?option=com_rsgallery2&gid=".$match->gallery_id);?>"><?php echo $match->name;?></a>]
							<?php
							if ($match->userid > 0) {
								echo "[".JText::_('COM_RSGALLERY2_OWNER').":&nbsp;".galleryUtils::genericGetUsername($match->userid)."]";
							}
						?>
						</p>
					</td>
				</tr>
				</table>
				<br />
				<?php
			}
		}
	}
}
?>