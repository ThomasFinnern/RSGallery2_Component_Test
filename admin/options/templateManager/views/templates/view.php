<?php
/**
 * @version		$Id: view.php 1011 2011-01-26 15:36:02Z mirjam $
 * @package		RSGallery2
 * @subpackage	Template installer
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined( '_JEXEC' ) or die();

/**
 * RSGallery2 Template Manager Templates View
 *
 * @package		RSGallery2
 * @subpackage	Template installer
 * @since		1.5
 */

include_once(dirname(__FILE__).DS.'..'.DS.'default'.DS.'view.php');

/**
 * Class InstallerViewTemplates
 */
class InstallerViewTemplates extends InstallerViewDefault
{
	/**
	 * @param null $tpl
	 */
	function display($tpl=null)
	{
		/*
		 * Set toolbar items for the page
		 */
		JToolBarHelper::makeDefault( 'setDefault' );
		JToolBarHelper::spacer();
		JToolBarHelper::deleteList( '', 'remove' );
		JToolBarHelper::editList( 'template' );
		JToolBarHelper::help( 'screen.installer2' );

		// Get data from the model
		$items		= $this->get('Items');
		$pagination	= $this->get('Pagination');

		$this->items = $items;
		$this->pagination = $pagination;

		parent::showHeader();
		parent::display($tpl);
	}

	/**
	 * @param int $index
	 */
	function loadItem($index=0)
	{
		$item = $this->items[$index];
		$item->id		= $item->directory;
		$item->index	= $index;

		if ($item->isDisabled) {
			$item->cbd		= 'disabled';
			$item->style	= 'style="color:#999999;"';
		} else {
			$item->cbd		= null;
			$item->style	= null;
		}
		$item->author_information = @$item->authorEmail .'<br />'. @$item->authorUrl;

		// $this->assignRef('item', $item);
		//   function assignRef($key, &$val)
		//      if (is_string($key) && substr($key, 0, 1) != '_')
		//      	$this->$key = &$val;
		$this->item = $item;
	}
}
