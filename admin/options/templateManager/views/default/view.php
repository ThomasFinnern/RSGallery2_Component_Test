<?php
/**
 * @version		$Id: view.php 1011 2011-01-26 15:36:02Z mirjam $
 * @package		Joomla
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die();

jimport('joomla.application.component.view');

/**
 * RSGallery2 Template Manager Default View
 *
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class InstallerViewDefault extends JViewLegacy
{
    /**
     * @param null $config
     */
	function __construct($config = null)
	{
		parent::__construct($config);
		$this->_addPath('template', $this->_basePath.DS.'views'.DS.'default'.DS.'tmpl');
	}

	/**
	 * @param null $tpl template
	 * @throws Exception
	 */
	function display($tpl=null)
	{
		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::title( JText::_('COM_RSGALLERY2_RSGALLERY2_TEMPLATE_MANAGER'), 'install.png' );

		// Document
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_RSGALLERY2_RSGALLERY2_TEMPLATE_MANAGER').' : '.$this->getName());

		// Get data from the model
		$state	= $this->get('State');

		// Are there messages to display ?
		$showMessage	= false;
		if ( is_object($state) )
		{
			$message1		= $state->get('message');
			$message2		= $state->get('extension.message');
			$showMessage	= ( $message1 || $message2 );
		}

		// assign is deprecated. Use native PHP syntax
		// $this->assign('showMessage',	$showMessage);
		$this->showMessage = $showMessage;
		$this->state = $state;

		JHtml::_('behavior.tooltip');
		parent::display($tpl);
	}

	/**
	 * Should be overloaded by extending view
	 *
	 * @param	int $index
	 */
	function loadItem($index=0)
	{
	}

	/**
	 * @throws Exception
	 */
	static function showHeader(){
		
		//$ext	= JRequest::getWord('type');
		$input =JFactory::getApplication()->input;
		$ext = $input->get( 'type',  '', 'WORD');					
		
		$subMenus = array(
				JText::_('COM_RSGALLERY2_MANAGE') => 'templates'
				);
		
		JHtmlSidebar::addEntry(JText::_('COM_RSGALLERY2_RSG2_CONTROL_PANEL'), 'index.php?option=com_rsgallery2', false);
		JHtmlSidebar::addEntry(JText::_('COM_RSGALLERY2_INSTALL'), '#" onclick="javascript:document.adminForm.type.value=\'\';submitbutton(\'installer\');', !in_array( $ext, $subMenus));
		foreach ($subMenus as $name => $extension) {
			JHtmlSidebar::addEntry($name , '#" onclick="javascript:document.adminForm.type.value=\''.$extension.'\';submitbutton(\'manage\');', ($extension == $ext));
		}
		
	}

	/**
	 * @throws Exception
	 */
	static function showTemplateHeader(){
		
		//$ext	= JRequest::getWord('type', 'templateGeneral');
		$input =JFactory::getApplication()->input;
		$ext = $input->get( 'type',  'templateGeneral', 'WORD');					
		if($ext =='templates') 
			$ext = 'templateGeneral';
		
		$subMenus = array(
				JText::_('COM_RSGALLERY2_GENERAL') => 'templateGeneral',
				JText::_('COM_RSGALLERY2_CSS') => 'templateCSS',
				JText::_('COM_RSGALLERY2_HTML') => 'templateHTML'
				);

		foreach ($subMenus as $name => $extension) {
			JHtmlSidebar::addEntry($name , '#" onclick="javascript:document.adminForm.type.value=\''.$extension.'\';submitbutton(\'template\');', ($extension == $ext));
		}
		
	}
}

