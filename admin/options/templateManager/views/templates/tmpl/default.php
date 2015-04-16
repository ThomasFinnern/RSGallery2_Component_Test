<?php
/**
 * @version		$Id: default.php 1012 2011-02-01 15:13:13Z mirjam $
 * @package		RSGallery2
 * @subpackage	Template installer
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */
 ?>
 <form action="index.php" method="post" name="adminForm">
	<table class="adminform">
		<tbody>
			<tr>
				<td width="100%"><?php echo JText::_( 'COM_RSGALLERY2_DESCTEMPLATES' );echo " ".JText::_( 'COM_RSGALLERY2_DESCTEMPLATES_NOTE' ); ?></td>
			</tr>
		</tbody>
	</table>

	<?php if (count($this->items)) : ?>
	<table class="adminlist" cellspacing="1">
		<thead>
			<tr>
				<th class="title" width="10px"><?php echo JText::_( 'COM_RSGALLERY2_NUM' ); ?></th>
				<th class="title"><?php echo JText::_( 'COM_RSGALLERY2_TEMPLATE' ); ?></th>
				<th class="title" align="center"><?php echo JText::_( 'COM_RSGALLERY2_DEFAULT' ); ?></th>
				<th class="title" width="10%" align="center"><?php echo JText::_( 'COM_RSGALLERY2_VERSION' ); ?></th>
				<th class="title" width="15%"><?php echo JText::_( 'COM_RSGALLERY2_DATE' ); ?></th>
				<th class="title" width="25%"><?php echo JText::_( 'COM_RSGALLERY2_AUTHOR' ); ?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="6"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
		</tfoot>
		<tbody>
		<?php for ($i=0, $n=count($this->items), $rc=0; $i < $n; $i++, $rc = 1 - $rc) : ?>
			<?php
				$this->loadItem($i);
				echo $this->loadTemplate('item');
			?>
		<?php endfor; ?>
		</tbody>
	</table>
	<?php else : ?>
		<?php echo JText::_( 'COM_RSGALLERY2_THERE_ARE_NO_TEMPLATES_INSTALLED' ); ?>
	<?php endif; ?>
	<input type="hidden" name="task" value="manage" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="option" value="com_rsgallery2" />
	<input type="hidden" name="rsgOption" value="installer" />
	<input type="hidden" name="type" value="templates" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>
