<?php
/*
* @version $Id: gallery.php 1073 2012-05-14 12:35:41Z mirjam $
* @package RSGallery2
* @copyright (C) 2005 - 2015 RSGallery2
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* RSGallery2 is Free Software
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * Gallery Form Field class to create contents of dropdown box for 
 * gallery selection in RSGallery2.
 * Includes "-- Select -- " as first entry
 */
class JFormFieldGallerySelect extends JFormFieldList {
	/**
	 * The field type.
	 *
	 * @var         string
	 */
	protected $type = 'GallerySelect';
	
	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  string array  An array of JHtml options.
	 */
	protected function getOptions() 
	{	
		$options = array();
		$galleries = array();

		// $user = JFactory::getUser(); // Todo: Restrict to accessible galleries
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true)
			->select('id As value, name As text')
			->from('#__rsgallery2_galleries AS a')
			->order('a.name');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$galleries = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}
		
		
//		// Add select option (no value)
//		$options[] = JHtml::_('select.option', -1, JText::_('COM_RSGALLERY2_SELECT_GALLERY_FROM_LIST'));
//		foreach($galleries as $gallery)
//		{	
//			$options[] = JHtml::_('select.option', $gallery->gid, $gallery->name);
//		}
//		$options = array_merge(parent::getOptions() , $options);
		
		// Merge any additional options in the XML definition.
		// $options[] = JHtml::_('select.option', $key, $value);
		// $options[] = array("value" => 1, "text" => "1");

		// Add "select title"
		$options[] = array("value" => -1, "text" => JText::_('COM_RSGALLERY2_SELECT_GALLERY_FROM_LIST'));
		$options = array_merge($options, $galleries);
		// Merge with base options
		$options = array_merge(parent::getOptions() , $options);

		return $options;
	}
}

