<?php
/**
 * @version		$Id: router.php 1085 2012-06-24 13:44:29Z mirjam $
 * @package		RSGallery2
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

 /*	- 	Advanced SEF logic at the bottom of this file
	-	Not advanced SEF logic:
		If gid, and it’s not part of a menulink: add ‘gallery’ (category was used <= v2.1.1) and add gid number
		If id then add ‘item’ and id number
		If start then add ‘itemPage’ and limitstart value - 1
		If page then add ‘as’ concatenated with page value
  */

  if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

/**
 * @param $query
 * @return array
 * @throws Exception
 */

function Rsgallery2BuildRoute(&$query) {
	//Get config values
	global $config;
	
	Rsgallery2InitConfig();

	$segments	= array();
	
	//Now define non-advanced SEF as v2 way and advanced SEF as v3 way
	if($config->get("advancedSef") == true) {
		//Find gid from menu --> $menuGid (can be an independant function)
		$app		= JFactory::getApplication();
		$menu		= $app->getMenu();
		if (empty($query['Itemid'])) {
			$menuItem = $menu->getActive();	//Menu item from current active one
		}
		else {
			$menuItem = $menu->getItem($query['Itemid']); //Menu item from query
		}
		$menuGid	= (empty($menuItem->execute['gid'])) ? null : $menuItem->execute['gid'];

		//if $rsgOption exists (e.g. myGalleries or rsgComments)
		if (isset($query['rsgOption'])) {
			//do not SEFify (return now)
			return $segments;
		}
		//if $task = downloadfile
		if (isset($query['task']) AND ($query['task'] == 'downloadfile')) {
			//do not SEFify (return now)
			return $segments;
		}

		//if view is set
		if (isset($query['view'])) {
			//remove view from URL
			unset($query['view']);
		}
		
		//if gid is set
		if (isset($query['gid'])) {
			//check if it is the gallery in the menulink or not
			if ($query['gid'] != $menuGid) {
				//add gid-galleryname
				$segments[] = $query['gid'].'-'.Rsgallery2GetGalleryName($query['gid']);
				if (!(isset($query['page']) AND ($query['page'] == 'inline'))) {
					//remove gid from URL, no longer needed (page is not 'inline')
					unset($query['gid']);
				}
			} //else nothing to do
		}
		if (isset($query['page'])) {
			switch ($query['page']) {
			    case 'slideshow':
			        //(gid-galleryname was already added), leave page in URL
			        break;
			    case 'inline':
					//remove page from URL
					unset($query['page']);
					if (isset($query['id'])) {
						//find gid-galleryname based on id
						$gid = Rsgallery2GetGalleryIdFromItemId($query['id']);
						if ($gid != $menuGid) {
							//add gid-galleryname based on found $gid (not query gid)
							$segments[] = $gid.'-'.Rsgallery2GetGalleryName($gid);
						}
						//add id-itemname based on id
						$segments[] = ($query['id']).'-'. Rsgallery2GetItemName($query['id']);
						//remove id from URL
						unset($query['id']);
					} elseif ((isset($query['gid']))) {
						//find item id based on gid combined with limitstart
						$start = (isset($query['start'])) ? $query['start'] : 0;
						$id = Rsgallery2GetItemIdFromGalleryIdAndLimitstart($query['gid'],$start);
						//add id-itemname
						$segments[] = $id.'-'. Rsgallery2GetItemName($id);
						//remove gid and limitstart from URL
						unset($query['gid']);
						unset($query['start']);
						unset($query['limitstart']);
					}
			        break;
			    default:
			    	break;
			}
		}	
	} else {//not advancedSEF
		static $items;


		//Find gid from menu --> $menuGid (can be an independent function)
		$app		= JFactory::getApplication();
		$menu		= $app->getMenu();
		if (empty($query['Itemid'])) {
			$menuItem = $menu->getActive();	//Menu item from current active one
		}
		else {
			$menuItem = $menu->getItem($query['Itemid']); //Menu item from query
		}
		$menuGid	= (empty($menuItem->execute['gid'])) ? null : $menuItem->execute['gid'];

		$itemid		= isset($query['Itemid']) ? $query['Itemid'] : null;

		// rename catId to gId	//catId could be leftover from versions before 1.14.x
		if(isset($query['catid'])){
			$query['gid'] = $query['catid'];
			unset($query['catid']);
		}
		// direct gallery link
		if(isset($query['gid'])){
			// add the gallery id only if it is not part of the menu link
			if ($query['gid'] != $menuGid) {
				$segments[] = 'gallery';
				$segments[] = Rsgallery2GetGalleryName($query['gid']);
			}
			unset($query['gid']);
		}
		// gallery paging	
		if(isset($query['limitstartg'])){
			$segments[] = 'categoryPage';
			$segments[] = $query['limitstartg'];
			unset($query['limitstartg']);
		}
		// direct item link
		if(isset($query['id'])){
			$segments[] = 'item';
			$segments[] = Rsgallery2GetItemName($query['id']);
			unset($query['id']);
		}
		// item paging
		if(isset($query['start'])){
			$segments[] = 'itemPage';
			$segments[] = $query['start'];
			unset($query['start']);
		}
		// how to show the item
		if(isset($query['page'])){
			$segments[] = 'as' . ucfirst($query['page']);
			unset($query['page']);
		}
	}
		
	return $segments;
}

/**
 * @param $segments
 * @return array
 * @throws Exception
 */
function Rsgallery2ParseRoute($segments) {
	//Note: segments show up like: '6:testimage' instead of expected '5-testimage' (don't know why)
	//Get config values
	global $config;
	
	Rsgallery2InitConfig();

	//Now define non-advanced SEF as v2 way and advanced SEF as v3 way
	if ($config->get("advancedSef") == true) {
		//View doesn't need to be added (there is only one view).
		//Check number of parts:
		switch (count($segments)) {
			case 0:
			//0: nothing to do
				break;
			case 1:
			//1: it's (most likely) a gallery, otherwise an item in a subgallery-menuitem
				//Get either gid and galleryname or id and itemname from 1st segment (explode into two parts)
				$partOne = explode(':',$segments[0],2);

				//This could be gid and galleryname: check if it is the correct galleryname
				//or else an id and itemname: check if it it the correct itemname
//Check needed because we don't know if its a gallery or an item
				if (Rsgallery2GetGalleryName($partOne[0]) == $partOne[1]) {
					//add gid //this is never the same as the gid in the menulink
					$vars['gid'] = $partOne[0]; //make sure we have an integer here
				}
//Check not needed per se
//				  elseif (Rsgallery2GetItemName($partOne[0]) == $partOne[1]) {
				  else {
					//add id and &page=inline
					$vars['id'] = $partOne[0]; //make sure we have an integer here
					$vars['page'] = 'inline';
//				} else {
//					//error
				}
				break;
			case 2:
			//2: it's an item
				//Get id and itemname from part 2 (explode into two parts)
				$partTwo = explode(':',$segments[1],2);
//Check not needed per se
//				if (Rsgallery2GetItemName($partTwo[0]) == $partTwo[1]) {
					//add id and &page=inline
					$vars['id'] = (int) $partTwo[0]; //make sure we have an integer here
					$vars['page'] = 'inline';
//				} else {
//					//error
//				}
				break;
			default:
				//error
		}
	} else {//not advancedSEF
		$vars	= array();
		
		// Get the active menu item.
		//$menu	= JSite::getMenu();
		$app = JFactory::getApplication();
		$menu = $app->getMenu();		
		
		$item	= $menu->getActive();

		if(!empty($item)){
			// We only want the gid from the menu-item-link when (this case the menulink refers to a subgallery)
			// - it is the only gid: e.g. no 'category' in $segments (it is not a subgallery of the gallery shown with the menu-item)
			// - we do not have id in the URL, e.g. no 'item' in $segments
			if (!in_array("gallery", $segments) AND !in_array("item", $segments) AND !in_array("category", $segments)) {	//'category' for links created with RSG2 version <= 2.1.1
				if(preg_match( "/gid=([0-9]*)/", $item->link, $matches) != 0){
					$vars['gid'] = $matches[1];
				}
			}
		}
		
		for ($index = 0 ; $index < count($segments) ; $index++){
			switch ($segments[$index]){
				// gallery link (subgallery of the gallery shown with the menu-item)
				case 'category':	//changed 'category' to 'gallery' after version 2.1.1
				case 'gallery':	
					$vars['gid'] = Rsgallery2GetCategoryId($segments[++$index]);
					break;
				// item link
				case 'item':
					$vars['id']  = Rsgallery2GetItemId($segments[++$index]);
					break;
				// gallery paging
				case 'categoryPage':
					$vars['limitstartg'] = 	$segments[++$index];
					$vars['limitstart'] = 1;
					break;
				// item paging
				case 'itemPage':
					$vars['limitstart'] = 	$segments[++$index];
					break;
			}
			// how to show the item
			$pos = strpos($segments[$index],'as'); 
			if($pos !== false && $pos == 0) {
				$vars['page'] = strtolower(substr($segments[$index],2));
			}
		}
		
		if(isset($vars["id"]) && !isset($vars['page'])) {
			$vars['page'] = "inline";
		}
	}//END of if ($config->get("advancedSef") == true)

	return $vars;
}

/**
 * Get the alias of a gallery based on its id (gid)
 * 
 *  @param int $gid Numerical value of the gallery
 *	@return string String Alias of the gallery
 * 
 **/
function Rsgallery2GetGalleryName($gid){
	//Get config values
	global $config;
	
	Rsgallery2InitConfig();
	
	// Fetch the gallery alias from the database if advanced sef is active,
	// else return the numerical value	
	if($config->get("advancedSef") == true) {
		$dbo = JFactory::getDBO();
		$query = 'SELECT `alias` FROM `#__rsgallery2_galleries` WHERE `id`='. (int) $gid;
		$dbo->setQuery($query);
		$result = $dbo->execute();
		if($dbo->getNumRows($result) != 1){
			// Gallery alias was not unique or is unknown, use the numeric value instead.
			$segment = $gid;
		}
		else{			
			$segment = $dbo->loadResult($result);
		}
	} else {// No advanced SEF
		$segment = $gid;
	}
	
	return $segment;
}

/**
 * Converts a category SEF alias to its id
 * 
 *  @param string $categoryName mixed SEF alias or id of the category
 *	@return int id of the category
 * 
 **/
function Rsgallery2GetCategoryId($categoryName){
	//Get config values
	global $config;
	
	Rsgallery2InitConfig();
	
	// fetch the gallery id from the database if advanced sef is active
	if($config->get("advancedSef") == true) {
		//not used
	} else {
		$id = $categoryName;
	}
	return $id;
}

/**
 * Converts a item SEF alias to its id
 * 
 *  @param string $itemName $categoryName mixed SEF alias or id of the category
 *	@return int id of the category
 * 
 **/
function Rsgallery2GetItemId($itemName){
	// Get config values
	global $config;
	
	Rsgallery2InitConfig();
	
	// fetch the gallery id from the database if advanced sef is active
	if($config->get("advancedSef") == true) {
		//not used
	} else{
		$id = $itemName;
	}
	
	return $id;
}

/**
 * Get an item alias based on its id
 * 
 *  @param int $id Numeral id of the item
 *	@return string Alias of the item
 * 
 **/
function Rsgallery2GetItemName($id){
	// Get config values
	global $config;
	
	Rsgallery2InitConfig();
	
	// Getch the item alias from the database if advanced sef is active,
	// else return the numerical value	
	if($config->get("advancedSef") == true) {
		$dbo = JFactory::getDBO();
		$query = 'SELECT `alias` FROM `#__rsgallery2_files` WHERE `id`='. (int) $id;
		$result = $dbo->execute($query);
		
		$dbo->setQuery($query);
		$result = $dbo->execute();
		if($dbo->getNumRows($result) != 1){
			// Item id not found (or found multiple times?!)
			$segment = $id;
		} else{			
			$segment = $dbo->loadResult($result);
		}
	} else {
		$segment = $id;
	}
	
	return $segment;
}
/**
 * Get the gallery id (gid) based on the id of an item
 * 
 *  @param int $id  Numeral id of the item
 *	@return int Id of the gallery (gid)
 * 
 **/
function Rsgallery2GetGalleryIdFromItemId($id){
	//Get config values
	global $config;

	Rsgallery2InitConfig();
	
	// Getch the gallery id (gid) from the database based on the id of an item
	$dbo = JFactory::getDBO();
	$query = 'SELECT `gallery_id` FROM `#__rsgallery2_files` WHERE `id`='. (int) $id;
	$result = $dbo->execute($query);
	
	$dbo->setQuery($query);
	$result = $dbo->execute();
	$countRows = $dbo->getNumRows($result);
	if ($countRows == 1) {
		// Item id not found (or found multiple times?!)
		$gid = $dbo->loadResult($result);
	} else {
		//Redirect user and display error...
		if ($countRows == 0) {			
			//...item not found
			$msg = JText::sprintf('COM_RSGALLERY2_ROUTER_IMAGE_ID_NOT_FOUND', $id);
		} else {
			//...non unique id in table, should never happen
			$msg = JText::_('COM_RSGALLERY2_SHOULD_NEVER_HAPPEN');
		}
		$app = JFactory::getApplication();
		JFactory::getLanguage()->load("com_rsgallery2");
		$app->redirect("index.php", $msg);
	}
	
	return $gid;
}
/**
 * Get the id of an item based on the given gallery id and limitstart
 * 
 *  @param int $gid Numeral id of the gallery (gid)
 *  @param int $limitstart Numeral
 *	@return int Id of the item (id)
 * @throws Exception
 */
function Rsgallery2GetItemIdFromGalleryIdAndLimitstart($gid,$limitstart){
	//Get config values
	global $config;
	Rsgallery2InitConfig();
	
	// Getch the gallery id (gid) from the database based on the id of an item
	$dbo = JFactory::getDBO();
	$query = $dbo->getQuery(true);
	$query->select('id');
	$query->from('#__rsgallery2_files');
	$query->where('`gallery_id`='. (int) $gid);
	// Only for superadministrators this includes the unpublished items
	if (!JFactory::getUser()->authorise('core.admin','com_rsgallery2')) {
		$query->where('`published` = 1');
	}
	$query->order('ordering');
	$result = $dbo->execute($query);
	$dbo->setQuery($query);
	$result = $dbo->execute();
	$countRows = $dbo->getNumRows($result);
	if ($countRows > 0) {
		$column= $dbo->loadColumn();
		$id = $column[$limitstart];
	} else {
		//todo: error //need to have non-zero number of items
		//Redirect user and display error...
		$app = JFactory::getApplication();
		JFactory::getLanguage()->load("com_rsgallery2");
		$app->redirect("index.php", JText::sprintf('COM_RSGALLERY2_COULD_NOT_FIND_IMAGE_BASED_ON_GALLERYID_AND_LIMITSTART', (int) $gid, (int) $limitstart));//todo add to languange file
	}
	return $id;
}

/**
 * Gets the configuration settings for RSGallery
 */
function Rsgallery2InitConfig() {
	global $config;
	
	if($config == null){
		if (!defined('JPATH_RSGALLERY2_ADMIN')){
			define('JPATH_RSGALLERY2_ADMIN', JPATH_ROOT. DS .'administrator' . DS . 'components' . DS . 'com_rsgallery2');
		}
		require_once(JPATH_RSGALLERY2_ADMIN . DS . 'includes' . DS . 'config.class.php');
		$config = new rsgConfig();
	}
}

/*	SEF logic and info
==> All links have option and Itemid for the menu-item
==> Then we have 
	view	only in menulink: discard for now with only 1 view --> remove view from URL
	gid		with limitstart: shows an item --> add galleryname and itemname
			without limitstart and not in menulink: shows subgallery --> add galleryname
			without limitstart and in menulink: shows subgallery --> do not add galleryname
	id		without task=downloadfile: shows item --> add galleryname and itemname
			with task=downloadfile --> do not SEFify
	page	page=slideshow --> add galleryname, leave page in URL
			page=inline, needed to show item --> remove page from URL 
	limitstart	only in combination with gid --> see gid on what to do
	task	task=downloadfile --> do not SEFify
==> Logic to SEFify link:
	//Find task, view, gid, page, id from query
	//Find gid from menu
	//Check if gid from menu is equal to gid from query
	if (there is a rsgOption)) {
		//do not SEFify (return now)
	}
	if ($task = 'downloadfile') {
		//do not SEFify (return now)
	}
	if (view is set) {
		//remove view from URL
	}
	if (gid is set) {
		//check if it is the gallery in the menulink or not
		if (gid is not the one in the menulink) {
			//add gid-galleryname
			if (page is not 'inline') {
				//remove gid from URL, no longer needed
			}
		} //else nothing to do
	}
	if (page is set) {
		$page = 'slideshow'
			//(gid-galleryname was already added), leave page in URL
		$page = 'inline'
			//remove page from URL
			if (id is set) {
				//find gid-galleryname based on id
				if (gid found not equal to gid in menulink) {
					//add gid-galleryname
				}
				//add id-itemname based on id
				//remove id from URL
			} elseif 
				//add id-itemname based on gid combined with limitstart (where limitstart=0 if it isn't there)
				//remove gid and limitstart from URL			
			}
	}
		
==> unSEFify logic
	//View doesn't need to be added (there is only one view).
	//Check number of parts:
	//0: nothing to do
	//1: it's (most likely) a gallery, otherwise an item in a subgallery-menuitem
	If (only 1 part) {
		//Get either gid and galleryname or id and itemname from 1st segment (explode)
		if (gid-galleryname combination exists) {
			//add gid //this is never the same as the gid in the menulink
		} elseif (id-itemname combination exists) {
			//add id and &page=inline
		} else {
			//error
		}
	}
	//2: it's an item
	If (two parts) {
		//Get id and itemname from part 2 (explode)
		if (id-itemname combination exists) {
			//add id and &page=inline
		} else {
			//error
		}
	}
*/
