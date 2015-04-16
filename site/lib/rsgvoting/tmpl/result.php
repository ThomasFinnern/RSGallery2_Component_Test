	<table border="0" width="200">
	<tr>
		<td><?php echo JText::_('COM_RSGALLERY2_RATING');?>:</td>
		<td><?php echo rsgVoting::calculateAverage($id);?>&nbsp;/&nbsp;<?php echo rsgVoting::getVoteCount($id);?>&nbsp;<?php echo JText::_('COM_RSGALLERY2_VOTES');?></td>
   	</tr>
	</table>
