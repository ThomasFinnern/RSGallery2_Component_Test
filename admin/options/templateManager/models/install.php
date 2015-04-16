<?php
/**
 * @version		$Id: install.php 1011 2011-01-26 15:36:02Z mirjam $
 * @package		Joomla
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );
jimport( 'joomla.installer.installer' );
jimport('joomla.installer.helper');

/**
 * RSGallery2 Template Manager Install Model
 *
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class InstallerModelInstall extends JModelLegacy
{
	/** @var object JTable object */
	var $_table = null;

	/** @var object JTable object */
	var $_url = null;

	/**
	 * Overridden constructor
	 * @access	protected
	 */
	function __construct()
	{
		parent::__construct();

	}

	/**
	 * @return bool
	 * @throws Exception
	 */
	function install()
	{
		$mainframe = JFactory::getApplication();

		$this->setState('action', 'install');

		// 140701 original: switch(JRequest::getWord('installtype'))
		$input =JFactory::getApplication()->input;
		$installtype = $input->get( 'installtype',  '', 'WORD');					
		switch($installtype)
		{
			case 'folder':
				$package = $this->_getPackageFromFolder();
				break;

			case 'upload':
				$package = $this->_getPackageFromUpload();
				break;

			case 'url':
				$package = $this->_getPackageFromUrl();
				break;

			default:
				$this->setState('message', 'No Install Type Found');
				return false;
				break;
		}

		// Was the package unpacked?
		if (!$package) {
			$this->setState('message', 'Unable to find install package');
			return false;
		}

		// Get a database connector
		//$db =  JFactory::getDBO();

		// Get an installer instance
		$installer = JInstaller::getInstance();
		require_once( rsgOptions_installer_path .DS. 'adapters' .DS. 'rsgtemplate.php' );
		$installer->setAdapter( 'rsgTemplate', new JInstaller_rsgTemplate( $installer ) );  

		// Install the package
		if (!$installer->install($package['dir'])) {
			// There was an error installing the package
			$msg = JText::sprintf('COM_RSGALLERY2_INSTALLEXT', JText::_($package['type']), JText::_('COM_RSGALLERY2_ERROR'));
			$result = false;
		} else {
			// Package installed sucessfully
			$msg = JText::sprintf('COM_RSGALLERY2_INSTALLEXT', JText::_($package['type']), JText::_('COM_RSGALLERY2_SUCCESS'));
			$result = true;
		}

		// Set some model state values
		$mainframe->enqueueMessage($msg);
		$this->setState('name', $installer->get('name'));
		$this->setState('result', $result);
		$this->setState('message', $installer->message);
		$this->setState('extension.message', $installer->get('extension.message'));

		// Cleanup the install files
		if (!is_file($package['packagefile'])) {
			$config = JFactory::getConfig();
			$package['packagefile'] = $config->getValue('config.tmp_path').DS.$package['packagefile'];
		}

		JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

		return $result;
	}

	/**
	 * string The class name for the installer
	 *
	 * @return bool|mixed
	 * @throws Exception
	 */
	static function _getPackageFromUpload()
	{
		// Get the uploaded file information
		// $userfile = JRequest::getVar('install_package', null, 'files', 'array' );
		$input =JFactory::getApplication()->input;
		$userfile = $input->get('install_package', null, 'FILES'); 

		// Make sure that file uploads are enabled in php
		if (!(bool) ini_get('file_uploads')) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('COM_RSGALLERY2_WARNINSTALLFILE'));
			return false;
		}

		// Make sure that zlib is loaded so that the package can be unpacked
		if (!extension_loaded('zlib')) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('COM_RSGALLERY2_WARNINSTALLZLIB'));
			return false;
		}

		// If there is no uploaded file, we have a problem...
		if (!is_array($userfile) ) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('COM_RSGALLERY2_NO_FILE_SELECTED'));
			return false;
		}

		// Check if there was a problem uploading the file.
		if ( $userfile['error'] || $userfile['size'] < 1 )
		{
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('COM_RSGALLERY2_WARNINSTALLUPLOADERROR'));
			return false;
		}

		// Build the appropriate paths
		$config = JFactory::getConfig();
		$tmp_dest 	= $config->getValue('config.tmp_path').DS.$userfile['name'];
		$tmp_src	= $userfile['tmp_name'];

		// Move uploaded file
		jimport('joomla.filesystem.file');
		$uploaded = JFile::upload($tmp_src, $tmp_dest);

		// Unpack the downloaded package file
		$package = JInstallerHelper::unpack($tmp_dest);

		return $package;
	}

	/**
	 * Install an extension from a directory
	 *
	 * @static
	 * @return bool True on success
	 * @since 1.0
	 * @return bool
	 * @throws Exception
	 */
	static function _getPackageFromFolder()
	{
	
		// Get the path to the package to install
		//$p_dir = JRequest::getString('install_directory');
		$input =JFactory::getApplication()->input;
		$p_dir = $input->get( 'install_directory', '', 'STRING');
		$p_dir = JPath::clean( $p_dir );

		// Did you give us a valid directory?
		if (!is_dir($p_dir)) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('COM_RSGALLERY2_PLEASE_ENTER_A_PACKAGE_DIRECTORY'));
			return false;
		}

		// Detect the package type
		$type = JInstallerHelper::detectType($p_dir);

		// Did you give us a valid package?
		if (!$type) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('COM_RSGALLERY2_PATH_DOES_NOT_HAVE_A_VALID_PACKAGE'));
			return false;
		}

		$package['packagefile'] = null;
		$package['extractdir'] = null;
		$package['dir'] = $p_dir;
		$package['type'] = $type;

		return $package;
	}

	/**
	 * Install an extension from a URL
	 *
	 * @static
	 * @return bool|mixed True on success
	 * @since 1.5
	 * @throws Exception
	 */
	static function _getPackageFromUrl()
	{
		// Get a database connector
		$db =  JFactory::getDBO();

		// Get the URL of the package to install
		//$url = JRequest::getString('install_url');
		$input =JFactory::getApplication()->input;
		$url = $input->get( 'install_url', '', 'STRING');
		
		// Did you give us a URL?
		if (!$url) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('COM_RSGALLERY2_PLEASE_ENTER_A_URL'));
			return false;
		}

		// Download the package at the URL given
		$p_file = JInstallerHelper::downloadPackage($url);

		// Was the package downloaded?
		if (!$p_file) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('COM_RSGALLERY2_INVALID_URL'));
			return false;
		}

		$config = JFactory::getConfig();
		$tmp_dest 	= $config->getValue('config.tmp_path');

		// Unpack the downloaded package file
		$package = JInstallerHelper::unpack($tmp_dest.DS.$p_file);

		return $package;
	}
}
