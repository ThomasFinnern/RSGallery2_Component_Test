<?php
/**
 * @version		$Id: default_item.php 1012 2011-02-01 15:13:13Z mirjam $
 * @package		RSGallery2
 * @subpackage	Template installer
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */
 ?>
<tr class="<?php echo "row".$this->item->index % 2; ?>" <?php echo $this->item->style; ?>>
	<td>
		<?php echo $this->pagination->getRowOffset( $this->item->index ); ?>
	</td>
	<td>
		<input type="radio" id="cb<?php echo $this->item->index;?>" name="template" value="<?php echo $this->item->id; ?>" onclick="isChecked(this.checked);" <?php echo $this->item->cbd; ?> />
		<span class="bold"><?php echo $this->item->name; ?></span>
	</td>
	<td align="center">
		<?php if($this->item->isDefault){ echo '<img src="'.JURI_SITE.'administrator/components/com_rsgallery2/images/tick.png" alt="" border="0">'; }?>
	</td>
	<td align="center">
		<?php echo @$this->item->version != '' ? $this->item->version : '&nbsp;'; ?>
	</td>
	<td>
		<?php echo @$this->item->creationDate != '' ? $this->item->creationDate : '&nbsp;'; ?>
	</td>
	<td>
		<span class="editlinktip hasTip" title="<?php echo JText::_( 'COM_RSGALLERY2_AUTHOR_INFORMATION' );?>::<?php echo $this->item->author_information; ?>">
			<?php echo @$this->item->author != '' ? $this->item->author : '&nbsp;'; ?>
		</span>
	</td>
</tr>
