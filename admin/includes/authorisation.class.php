<?php
/**
* Authorisation Manager Class for RSGallery2
* @version $Id$
* @package RSGallery2
* @copyright (C) 2005 - 2012 rsgallery2.net
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* RSGallery2 is Free Software
*/

defined( '_JEXEC' ) or die();

/**
* Authorisation Manager
* Handles authorisation checkes
* @package RSGallery2
*/
class rsgAuthorisation{

	/**
	 * Check for edit (own) authorisation on gallery
	 * $param int gallery id
	 * @return boolean true if authorised, false if not.
	 */
	static function authorisationEditGallery($gallery_id = Null) {
		$user = JFactory::getUser();
		$allowed = false;
		
		// User has to have edit permission or edit own permission and be the owner
		// Check for edit permission
		$canEditGallery = $user->authorise('core.edit', 'com_rsgallery2.gallery.'.$gallery_id);
		if ($canEditGallery){
			$allowed = true;
		} else {
			// No edit permission, check for edit own permission
			$canEditOwnGallery = $user->authorise('core.edit.own', 'com_rsgallery2.gallery.'.$gallery_id);
			if ($canEditOwnGallery) {
				// User has edit own permission, check ownership
				// Get the number of items with this id and with this user as its owner
				$db = JFactory::getDBO();
				$query = $db->getQuery(true);
				$query->select("id, uid");
				$query->from("#__rsgallery2_galleries");
				$query->where("id = ". (int) $gallery_id);
				$query->where("uid = ". (int) $user->id);
				$db->setQuery($query);
				$db->execute();
				if( $db->getNumRows()) {
					// There exists an item with this gallery_id and the user as its owner
					$allowed = true;
				}
				//$result = $db->loadAssocList();
			} 
		}
		return $allowed;
	}

	/**
	 * Check for edit (own) authorisation on item
	 * $param int item id
	 * @return boolean true if authorised, false if not.
	 */
	static function authorisationEditItem($item_id = Null) {
		$user = JFactory::getUser();
		$allowed = false;
		
		// User has to have edit permission or edit own permission and be the owner
		// Check for edit permission
		$canEditItem = $user->authorise('core.edit', 'com_rsgallery2.item.'.$item_id);
		if ($canEditItem){
			$allowed = true;
		} else {
			//No edit permission, check for edit own permission
			$canEditOwnItem = $user->authorise('core.edit.own', 'com_rsgallery2.item.'.$item_id);
			if ($canEditOwnItem) {
				// User has edit own permission, check ownership
				// Get the number of items with this id and with this user as its owner
				$db = JFactory::getDBO();
				$query = $db->getQuery(true);
				$query->select("id, userid");
				$query->from("#__rsgallery2_files");
				$query->where("id = ". (int) $item_id);
				$query->where("userid = ". (int) $user->id);
				$db->setQuery($query);
				$db->execute();
				if( $db->getNumRows()) {
					// There exists an item with this item_id and the user as its owner
					$allowed = true;
				}
				//$result = $db->loadAssocList();
			} 
		}
		return $allowed;
	}

	/**
	 * Check for delete (own) authorisation on gallery
	 * $param int gallery id
	 * @return boolean true if authorised, false if not.
	 */
	static function authorisationDeleteGallery($gallery_id = Null) {
		$user = JFactory::getUser();
		$allowed = false;
		
		// User has to have delete permission or delete own permission and be the owner
		// Check for delete permission
		$canDeleteGallery = $user->authorise('core.delete', 'com_rsgallery2.gallery.'.$gallery_id);
		if ($canDeleteGallery){
			$allowed = true;
		} else {
			// No delete permission, check for delete own permission
			$canDeleteOwnGallery = $user->authorise('rsgallery2.delete.own', 'com_rsgallery2.gallery.'.$gallery_id);
			if ($canDeleteOwnGallery) {
				// User has delete permission, check ownership
				// Get the number of items with this id and with this user as its owner
				$db = JFactory::getDBO();
				$query = $db->getQuery(true);
				$query->select("id, uid");
				$query->from("#__rsgallery2_galleries");
				$query->where("id = ". (int) $gallery_id);
				$query->where("uid = ". (int) $user->id);
				$db->setQuery($query);
				$db->execute();
				if( $db->getNumRows()) {
					// There exists an item with this gallery_id and the user as its owner
					$allowed = true;
				}
				//$result = $db->loadAssocList();
			} 
		}
		return $allowed;
	}
	
	/**
	 * Check for delete (own) authorisation on item
	 * $param int item id
	 * @return boolean true if authorised, false if not.
	 */
	static function authorisationDeleteItem($item_id = Null) {
		$user = JFactory::getUser();
		$allowed = false;
		
		// User has to have delete permission or delete own permission and be the owner
		// Check for delete permission
		$canDeleteItem = $user->authorise('core.delete', 'com_rsgallery2.item.'.$item_id);
		if ($canDeleteItem){
			$allowed = true;
		} else {
			//No delete permission, check for delete own permission
			$canDeleteOwnItem = $user->authorise('rsgallery2.delete.own', 'com_rsgallery2.item.'.$item_id);
			if ($canDeleteOwnItem) {
				// User has delete own permission, check ownership
				// Get the number of items with this id and with this user as its owner
				$db = JFactory::getDBO();
				$query = $db->getQuery(true);
				$query->select("id, userid");
				$query->from("#__rsgallery2_files");
				$query->where("id = ". (int) $item_id);
				$query->where("userid = ". (int) $user->id);
				$db->setQuery($query);
				$db->execute();
				if( $db->getNumRows()) {
					// There exists an item with this item_id and the user as its owner
					$allowed = true;
				}
				//$result = $db->loadAssocList();
			} 
		}
		return $allowed;
	}
	/**
	 * Check for edit state (own) authorisation on gallery
	 * $param int gallery id
	 * @return boolean true if authorised, false if not.
	 */
	static function authorisationEditStateGallery($gallery_id = Null) {
		$user = JFactory::getUser();
		$allowed = false;
		
		// User has to have edit state permission or edit state own permission and be the owner
		// Check for edit state permission
		$canEditStateItem = $user->authorise('core.edit.state', 'com_rsgallery2.gallery.'.$gallery_id);
		if ($canEditStateItem){
			$allowed = true;
		} else {
			// No edit state permission, check for edit state own permission
			$canEditStateOwnItem = $user->authorise('rsgallery2.edit.state.own', 'com_rsgallery2.gallery.'.$gallery_id);
			if ($canEditStateOwnItem) {
				// User has edit state own permission, check ownership
				// Get the number of items with this id and with this user as its owner
				$db = JFactory::getDBO();
				$query = $db->getQuery(true);
				$query->select("id, uid");
				$query->from("#__rsgallery2_galleries");
				$query->where("id = ". (int) $gallery_id);
				$query->where("uid = ". (int) $user->id);
				$db->setQuery($query);
				$db->execute();
				if( $db->getNumRows()) {
					// There exists an item with this gallery_id and the user as its owner
					$allowed = true;
				}
				//$result = $db->loadAssocList();
			} 
		}
		return $allowed;
	}
	/**
	 * Check for edit state (own) authorisation on item
	 * $param int item id
	 * @return boolean true if authorised, false if not.
	 */
	static function authorisationEditStateItem($item_id = Null) {
		$user = JFactory::getUser();
		$allowed = false;
		
		// User has to have edit state permission or edit state own permission and be the owner
		// Check for edit state permission
		$canEditStateItem = $user->authorise('core.edit.state', 'com_rsgallery2.item.'.$item_id);
		if ($canEditStateItem){
			$allowed = true;
		} else {
			// No edit state permission, check for edit state own permission
			$canEditStateOwnItem = $user->authorise('rsgallery2.edit.state.own', 'com_rsgallery2.item.'.$item_id);
			if ($canEditStateOwnItem) {
				// User has edit state own permission, check ownership
				// Get the number of items with this id and with this user as its owner
				$db = JFactory::getDBO();
				$query = $db->getQuery(true);
				$query->select("id, userid");
				$query->from("#__rsgallery2_files");
				$query->where("id = ". (int) $item_id);
				$query->where("userid = ". (int) $user->id);
				$db->setQuery($query);
				$db->execute();
				if( $db->getNumRows()) {
					// There exists an item with this item_id and the user as its owner
					$allowed = true;
				}
				//$result = $db->loadAssocList();
			} 
		}
		return $allowed;
	}
	/**
	 * Check for create (own) authorisation in parent gallery (check on component permission when gid = 0)
	 * $param int item id
	 * @return boolean true if authorised, false if not.
	 */
	static function authorisationCreate($parent_gallery = Null) {
		$user = JFactory::getUser();
		$allowed = false;

		// If the parent gallery is the root gallery (id 0), check component permission, 
		// otherwise check parent gallery permission.
		if (isset($parent_gallery)) {
			if ($parent_gallery) {
				// User has to have create permission or create own permission and be the owner
				// Check for create permission
				$canCreate = $user->authorise('core.create', 'com_rsgallery2.gallery.'.$parent_gallery);
				if ($canCreate){
					$allowed = true;
				} else {
					// No create permission, check for create own permission
					$canCreateOwn = $user->authorise('rsgallery2.create.own', 'com_rsgallery2.gallery.'.$parent_gallery);
					if ($canCreateOwn) {
						// User has create own permission, check ownership
						// Get the number of items with this id and with this user as its owner
						$db = JFactory::getDBO();
						$query = $db->getQuery(true);
						$query->select("id, uid");
						$query->from("#__rsgallery2_galleries");
						$query->where("id = ". (int) $parent_gallery);
						$query->where("uid = ". (int) $user->id);
						$db->setQuery($query);
						$db->execute();
						if( $db->getNumRows()) {
							// There exists an item with this item_id and the user as its owner
							$allowed = true;
						}
						//$result = $db->loadAssocList();
					} 
				}
			} else {
				// Parent gallery id is 0 (no need to check for create own and ownership)
				if ($user->authorise('core.create', 'com_rsgallery2')){
					$allowed = true;
				}
			}
		}

		return $allowed;
	}

   /**
	* Method to return a list of all galleries that a user has permission for a given action
	* @param	string	$action	The action
	* @return	array	List of galleries that the user can do this action to (empty array if none). Galleries may be unpublished
	*/
	static function authorisationCreate_galleryList(){
		$user = JFactory::getUser();
		
		// Brute force method: get all gallery rows for the component and check each one
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select("id, uid");
		$query->from("#__rsgallery2_galleries");
		$db->setQuery($query);
		$db->execute();
		$allGalleries = $db->loadObjectList('id');
		$allowedGalleries = array();
		
		foreach ($allGalleries as $gallery) {
			$allowed = false;
			// User has to have create permission or create own permission and be the owner
			// Check for create permission
			$canCreate = $user->authorise('core.create', 'com_rsgallery2.gallery.'.$gallery->id);
			if ($canCreate){
				$allowed = true;
			} else {
				// No create permission, check for create own permission
				$canCreateOwn = $user->authorise('rsgallery2.create.own', 'com_rsgallery2.gallery.'.$gallery->id);
				if ($canCreateOwn) {
					// User has create own permission, check ownership
					// Get the number of items with this id and with this user as its owner
					$isOwner = ($user->id === $gallery->uid);
					if ($isOwner) {
						$allowed = true;
					}
				} 
			}
			if ($allowed) {
				$allowedGalleries[] = (int) $gallery->id;     
			}
		}
		return $allowedGalleries;
	}
	
}
//end class //
?>