<?php
/**
 * @package		RSGallery2
 * @subpackage	TemplateManager
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

// Import library dependencies
require_once(dirname(__FILE__).DS.'extension.php');
jimport( 'joomla.filesystem.folder' );

/**
 * RSGallery2 Template Manager Template Model
 *
 * @package		RSGallery2
 * @subpackage	TemplateManager
 * @since		1.5
 */
class InstallerModelEditHtml extends InstallerModel
{
	/**
	 * Extension Type
	 * @var	string
	 */
	var $_type = 'EditHtml';
	
	/**
	 * Overridden constructor
	 * @access	protected
	 */
	function __construct()
	{
		$mainframe =& JFactory::getApplication();
		
		// Call the parent constructor
		parent::__construct();
		
		// Set state variables from the request
		$this->setState('filter.string', $mainframe->getUserStateFromRequest( "com_rsgallery2_com_installer.templates.string", 'filter', '', 'string' ));
	}
	
	function getItem()
	{
		jimport('joomla.filesystem.file');
		
		// Determine template CSS directory
		$dir = JPATH_RSGALLERY2_SITE .DS. 'templates'.DS.$this->template.DS.'html';
		$file = $dir .DS. $this->filename;
		
		//$content = JFile::read($ini); J3
        // ToDo: Fix undefined variable $ini
        // ToDo: Fix undefined function file_get_contents
		$content = JFile::file_get_contents($ini);
		
		if ($content == false)
		{
            // ToDo deprecated:
			JError::raiseWarning( 500, JText::sprintf('COM_RSGALLERY2_OPERATION_FAILED_COULD_NOT_OPEN', $client->path.$filename) );
		}
		
		$item = new stdClass();
		$this->item = $item;
        // ToDo: Fix undefined variable $filename
		$item->filename = $this->filename;
		$item->content = $content;
		$item->path = $file;
        // ToDo: Fix undefined variable template
		$item->template = $this->template;
		
		return $item;
	}
	
	function save(){
		
		$app = & JFactory::getApplication();
		
		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		$ftp = JClientHelper::getCredentials('ftp');
		
		$file = JPATH_RSGALLERY2_SITE .DS. 'templates'.DS.$this->template.DS.'html'.DS.$this->filename;
		
		// Try to make the css file writeable
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0755')) {
			JError::raiseNotice('SOME_ERROR_CODE', 'Could not make the html file writable');
		}
		
		jimport('joomla.filesystem.file');
		$return = JFile::write($file, $this->content);
		
		// Try to make the css file unwriteable
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0555')) {
			JError::raiseNotice('SOME_ERROR_CODE', 'Could not make the html file unwritable');
		}
		
		if($return)
			$app->enqueueMessage( 'File saved');
		
		return $return;
	}
	
}






