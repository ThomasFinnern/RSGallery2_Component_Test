<?php
/*
* @version $Id: gallery.php 1073 2012-05-14 12:35:41Z mirjam $
* @package RSGallery2
* @copyright (C) 2005 - 2012 RSGallery2
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* RSGallery2 is Free Software
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// import the list field type
jimport('joomla.html.html.list');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Gallery Form Field class to create contents of dropdown box for 
 * gallery selection in RSGallery2.
 */
class JFormFieldGallery extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var         string
	 */
	protected $type = 'gallery';
	
	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  string array           An array of JHtml options.
	 */
	protected function getOptions() 
	{	
		$db = JFactory::getDBO();
		//Get galleries for optionlist from database
		$query = 'SELECT id as gid, name'
		. ' FROM #__rsgallery2_galleries'
		. ' WHERE published = 1'
		. ' ORDER BY name'
		;
		$db->setQuery( $query );
		$galleries = $db->loadObjectList();
		
		//Add default option (no value)
		$options[] = JHtml::_('select.option', 0, JText::_('COM_RSGALLERY2_ROOT_GALLERY'));
		foreach($galleries as $gallery)
		{	
			$options[] = JHtml::_('select.option', $gallery->gid, $gallery->name);
		}
		$options = array_merge(parent::getOptions() , $options);
		
		return $options;
	}
}