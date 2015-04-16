<?php
/**
 * @version		$Id: template.php 1011 2011-01-26 15:36:02Z mirjam $
 * @package		Joomla
 * @subpackage	Templates
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * @package		Joomla
 * @subpackage	Templates
 */
class TemplatesHelper
{
	static function isTemplateDefault($template, $clientId)
	{
		global $rsgConfig;
		
		return $template == $rsgConfig->template ? 1 : 0;
	}
	
	static function parseXMLTemplateFiles($templateBaseDir)
	{
		// Read the template folder to find templates
		jimport('joomla.filesystem.folder');
		$templateDirs = JFolder::folders($templateBaseDir);
		
		$rows = array();
		
		// Check that the directory contains an xml file
		foreach ($templateDirs as $templateDir)
		{
			if(!$data = TemplatesHelper::parseXMLTemplateFile($templateBaseDir, $templateDir)){
				continue;
			} else {
				$rows[] = $data;
			}
		}
		
		return $rows;
	}
	
	static function parseXMLTemplateFile($templateBaseDir, $templateDir)
	{
		// Check of the xml file exists
		if(!is_file($templateBaseDir.DS.$templateDir.DS.'templateDetails.xml')) {
			return false;
		}
		
//		JApplicationHelper::parseXMLInstallFile is deprecated in J3, need to use JInstaller::parseXMLInstallFile instead.			
//		$xml = JApplicationHelper::parseXMLInstallFile($templateBaseDir.DS.$templateDir.DS.'templateDetails.xml');
		$xml = JInstaller::parseXMLInstallFile($templateBaseDir.DS.$templateDir.DS.'templateDetails.xml');
		
		if ($xml['type'] != 'rsgTemplate') {
			return false;
		}
		
		$data = new StdClass();
		$data->directory = $templateDir;
		
		foreach($xml as $key => $value) {
			$data->$key = $value;
		}
		
		$data->checked_out = 0;
		$data->mosname = JString::strtolower(str_replace(' ', '_', $data->name));
		
		return $data;
	}
	
}