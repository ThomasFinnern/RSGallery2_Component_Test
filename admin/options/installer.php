<?php
/**
* templates option for RSGallery2
* @version $Id: installer.php 1019 2011-04-12 14:16:47Z mirjam $
* @package RSGallery2
* @copyright (C) 2003 - 2011 RSGallery2
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* RSGallery is Free Software
*/

defined('_JEXEC') or die();

// Only those with core.manage can get here via $rsgOption = installer
// Check if core.admin is allowed
if (!JFactory::getUser()->authorise('core.admin', 'com_rsgallery2')) {
	// return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');
	return;
} else {
	define( 'rsgOptions_installer_path', $rsgOptions_path . 'templateManager' );
	require_once( rsgOptions_installer_path .DS. 'admin.installer.php' );
}