<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * Gallery Form Field class to create contents of dropdown box for 
 * gallery selection in RSGallery2.
 */
class JFormFieldGalleryList extends JFormFieldList {
	/**
	 * The field type.
	 *
	 * @var         string
	 */
    protected $type = 'GalleryList';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return  array  The field option objects
	 *
	 * @since   1.6
	 */
	protected function getOptions()
	{
		$options = array();

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
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}
		
		// Merge any additional options in the XML definition.
		// $options[] = JHtml::_('select.option', $key, $value);
		$options = array_merge(parent::getOptions(), $options);
		
		return $options;
    }
}

