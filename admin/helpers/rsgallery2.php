<?php
/**
* RSGallery2 Helper
* @version $Id: rsgallery2.php 1019 2011-04-12 14:16:47Z mirjam $
* @package RSGallery2
* @copyright (C) 2003 - 2011 RSGallery2
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
**/

// No direct access
defined('_JEXEC') or die;

/**
 * RSGallery2 component helper.
 *
 * @since		3.0
 */
class RSGallery2Helper
{
	public static $extension = 'com_rsgallery2';

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param	int	$galleryId	The gallery ID.
	 * @return	JObject
	 */
	public static function getActions($galleryId = 0) {
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($galleryId)) {
			$assetName = 'com_rsgallery2';
		} else {
			$assetName = 'com_rsgallery2.gallery.'.(int) $galleryId;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.delete', 'core.edit', 'core.edit.state', 'core.edit.own'
		);

		foreach ($actions as $action) {
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}
}