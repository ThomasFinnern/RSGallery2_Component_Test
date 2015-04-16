<?php
/**
* @version		$Id: admin.installer.php 1011 2011-01-26 15:36:02Z mirjam $
* @package		Joomla
* @subpackage	Installer
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once( rsgOptions_installer_path . DS . 'helpers' . DS . 'template.php');
require_once( rsgOptions_installer_path . DS . 'controller.php' );

$controller = new InstallerController( array(
			'default_task' => 'installform',
			'model_path'=>rsgOptions_installer_path.DS.'models',
			'view_path'=>rsgOptions_installer_path.DS.'views'
	));

// 140701 original: $controller->set('task_type', JRequest::getCmd('type'));
// 140701 original: $controller->execute( JRequest::getCmd('task') );
$input =JFactory::getApplication()->input;
$type = $input->get( 'type', '', 'CMD');		
$task = $input->get( 'task', '', 'CMD');			
$controller->set('task_type', $type);
$controller->execute( $task );
$controller->redirect();
