<?php
/**
* This file contains code for frontend My Galleries.
* @version $Id: mygalleries.php 1085 2012-06-24 13:44:29Z mirjam $
* @package RSGallery2
* @copyright (C) 2003 - 2012 RSGallery2
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* RSGallery is Free Software
*/
defined( '_JEXEC' ) or die();
global $rsgConfig;
$document= JFactory::getDocument();

if($document->getType() == 'html') {
	$cssTemplate = JURI_SITE."components/com_rsgallery2/templates/".$rsgConfig->template."/css/template.css";
	$document->addStyleSheet($cssTemplate);
	$cssMyGalleries = JURI_SITE."components/com_rsgallery2/lib/mygalleries/mygalleries.css";	
	$document->addStyleSheet($cssMyGalleries);
	
}

//Load required class file
require_once( JPATH_RSGALLERY2_SITE . DS . 'lib' . DS . 'mygalleries' . DS . 'mygalleries.class.php' );
//Need galleries.class.php for rsgGalleriesItem that extends JTable
$rsgOptions_path = JPATH_RSGALLERY2_ADMIN .DS. 'options' .DS;	
require_once( $rsgOptions_path . 'galleries.class.php' );
require_once( $rsgOptions_path . 'images.class.php' );

//Get parameters from URL and/or form
$input =JFactory::getApplication()->input;
//$task   = JRequest::getCmd('task', '' );
$task = $input->get( 'task', '', 'CMD');		
//$id		= JRequest::getInt('id','' );
$id = $input->get( 'id', 0, 'INT');		
//$gid	= JRequest::getInt('gid','' );
$gid = $input->get( 'gid', 0, 'INT');
//$currentState = JRequest::getInt('currentstate','' );
$currentState = $input->get( 'currentstate', 0, 'INT');

//$cid    = JRequest::getVar('cid', array(0) );
$cid = $input->get( 'cid', array(0), 'ARRAY');

switch( $task ){
    case 'saveUploadedItem':
    	saveuploadedItem();
    	break;
    case 'editItem':
    	editItem();
    	break;
    case 'deleteItem':
    	deleteItem();
    	break;
    case 'saveItem':
    	saveItem();
    	break;
    case 'newCat':
    	editCat(null);
    	break;
    case 'editCat':
    	editCat($gid);  		
    	break;
    case 'saveCat':
    	saveCat($gid);
    	break;
    case 'deleteCat':
    	deleteCat();
    	break;
	case 'editStateGallery':
		editStateGallery($gid, 1-$currentState);
		break;
	case 'editStateItem':
		editStateItem($id, 1-$currentState);
		break;
	case 'publishItems':
		editStateItems($cid,1);
		break;
	case 'unpublishItems':
		editStateItems($cid,0);
		break;
	case 'deleteItems':
		deleteItems($cid);
		break;
	default:
		showMyGalleries();
		break;
}

function showMyGalleries() {
	global $rsgConfig;
	$mainframe = JFactory::getApplication();
	$my = JFactory::getUser();
	$groups	= $my->getAuthorisedViewLevels();
	$groupsIN = implode(", ",array_unique ($groups));
	$superAdmin = $my->authorise('core.admin');
	$database = JFactory::getDBO();

	//Check if My Galleries is enabled in config, if not .............. 
	if ( !$rsgConfig->get('show_mygalleries') ) die(JText::_('COM_RSGALLERY2_UNAUTHORIZED_ACCESS_ATTEMPT_TO_MY_GALLERIES'));

	//We want to use pagination
	jimport('joomla.html.pagination');	
	//Set limits for pagenav (remembering the pages with getUserSTateFromRequest), total comes later
	$limit 		= $mainframe->getUserStateFromRequest('global.list.limit',
						'limit', $mainframe->get('list_limit'), 'int');
	$limitstart = $mainframe->getUserStateFromRequest( 'limitstart', 'limitstart', 0, 'int' );

	//This query gets all the images (ordering: galleries ordering then files ordering)
	//	Create the query for the images
	$query = $database->getQuery(true);
	$query->select('files.*, galleries.ordering, galleries.access');
	$query->from('#__rsgallery2_files AS files');
	$query->leftJoin('#__rsgallery2_galleries AS galleries ON galleries.id = files.gallery_id');
	$query->leftJoin('#__users AS users ON users.id = files.checked_out');
	if (!$superAdmin) {	
		// No View Access check for Super Administrators
		$query->where('galleries.access IN ('.$groupsIN.')');
	}
	if ($rsgConfig->get('show_mygalleries_onlyOwnItems')) {
		// Show only items owned by current user?
		$query->where('files.userid = '. (int) $my->id);
	}
	$query->order('galleries.ordering, files.ordering');
	//	Now that is The Query for the images, all of them

	//	Get image count to use with the pagination:
	$database->setQuery($query);
	$allImages = $database->loadObjectList();
	$image_count = count($allImages);
	//	Get pagination instance: with our total number, the limit and the limitstart
	$pageNav = new JPagination( $image_count, $limitstart, $limit );
	
	//	Finally, get the set of images to show on this page, which is the
	//	same query as before, but with a limit: setQuery($query, $limitstart, $limit)
	$database->setQuery($query, $pageNav->limitstart, $pageNav->limit); 
	$images = $database->loadObjectList();

	//Get all galleries based on hierarchy
	$rows = myGalleries::recursiveGalleriesList();

	if($my->id) {
		//User is logged in, show it all!
		myGalleries::viewMyGalleriesPage($rows, $images, $pageNav);
	} else {
		//Not logged in, back to main page
		$mainframe->redirect(JRoute::_("index.php?option=com_rsgallery2"), JText::_('COM_RSGALLERY2_MY_GALLERIES_NEED_TO_LOGIN') );
	}	
}

/**
 * Deletes an item through the frontend My Galleries part
 */
function deleteItem() {
	$mainframe = JFactory::getApplication();
	$user = JFactory::getUser();
	$database = JFactory::getDBO();
	$input =JFactory::getApplication()->input;
	//$id = JRequest::getInt( 'id'  , '');
	$id = $input->get( 'id', 0, 'INT');		
	//$Itemid = JRequest::getInt( 'Itemid'  , '');
	$Itemid = $input->get( 'Itemid', 0, 'INT');		
	$redirect = JRoute::_("index.php?option=com_rsgallery2&rsgOption=myGalleries&Itemid=".$Itemid, false);
	
	//Check if delete is allowed for this item
	if (!rsgAuthorisation::authorisationDeleteItem($id)){
		JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
		$mainframe->redirect(JRoute::_( $redirect ));
	}

	if ($id) {		
		$filename 	= galleryUtils::getFileNameFromId($id);
		if (imgUtils::deleteImage($filename)) {
			$mainframe->redirect(JRoute::_("index.php?option=com_rsgallery2&rsgOption=myGalleries&Itemid=$Itemid",false), JText::_('COM_RSGALLERY2_IMAGE_IS_DELETED') );
		} else {
			$mainframe->redirect(JRoute::_("index.php?option=com_rsgallery2&rsgOption=myGalleries&Itemid=$Itemid",false) );
		}
	} else {
		//No ID sent, no delete possible, back to my galleries
		$mainframe->redirect(JRoute::_("index.php?option=com_rsgallery2&rsgOption=myGalleries"), JText::_('COM_RSGALLERY2_NO_ID_PROVIDED_CONTACT_COMPONENT_DEVELOPER') );
	}
}

function editItem() {
	$database = JFactory::getDBO();
	$input =JFactory::getApplication()->input;
	//$id = JRequest::getInt('id', null);
	$id = $input->get( 'id', null, 'INT');		
	$user = JFactory::getUser();
	$mainframe = JFactory::getApplication();
	//$Itemid = JRequest::getInt('Itemid', null);
	$Itemid = $input->get( 'Itemid', 0, 'INT');		
	$redirect = JRoute::_("index.php?option=com_rsgallery2&rsgOption=myGalleries&Itemid=".$Itemid, false);

	//Is the user allowed to edit the item?
	//(Check on users that "know the URL" to access the edit screen)
	$allowed = rsgAuthorisation::authorisationEditItem( (int) $id);
	//Redirect if user is not allowed to edit the item 
	if (!$allowed){
		JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
		$mainframe->redirect(JRoute::_( $redirect ));
	}

	if ($id) {

        JHtml::_('behavior.framework',true);

        $query = 'SELECT * FROM `#__rsgallery2_files` WHERE `id` = '. (int) $id;
		$database->setQuery($query);
		$rows = $database->loadObjectList();
		myGalleries::editItem($rows);
	}
}

function saveItem() {
	// Check for request forgeries
    //JRequest::checkToken() or jexit( 'Invalid Token' );
    JSession::checkToken() or jexit( 'Invalid Token' );

	$mainframe = JFactory::getApplication();
	$database = JFactory::getDBO();
	$user = JFactory::getUser();
	$input =JFactory::getApplication()->input;
	//$Itemid = JRequest::getInt('Itemid', '');
	$Itemid = $input->get( 'Itemid', 0, 'INT');		

	//Get id of item
	//$id = JRequest::getInt( 'id'  , '');
	$id = $input->get( 'id', 0, 'INT');		
	
	//Set redirect URL
	$redirect = JRoute::_("index.php?option=com_rsgallery2&rsgOption=myGalleries&Itemid=".$Itemid, false);

	//Create item object and get details
	$row = new rsgImagesItem( $database );
	$row->load($id);

	//Determine if the user is allewed to edit (and thus save) this item
	$allowed = rsgAuthorisation::authorisationEditItem($id);
	if (!$allowed){
		JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
		$mainframe->redirect(JRoute::_( $redirect ));
	}
	
	//Get the POST variables:
	//Not using $row->bind( JRequest::get('post') ) here, too few variables that don't need attention
	//$row->title 	= JRequest::getString( 'title'  , '');
	$row->title 	= $input->get( 'title', '', 'STRING');
		
	//$row->descr 	= JRequest::getVar( 'descr'  , '', 'post', 'string', JREQUEST_ALLOWRAW);
	$row->descr = $input->post->get('descr', '', 'RAW');
	$row->descr = str_replace( '<br>', '<br />', $row->descr );
	//$row->gallery_id= JRequest::getInt( 'gallery_id'  , '');
	$row->gallery_id= $input->get( 'gallery_id', 0, 'INT');		

	//Make the alias for SEF
    $row->alias 	= JFilterOutput::stringURLSafe($row->title);
	
	if (!$row->check()) {
		$mainframe->redirect( $redirect , JText::_('COM_RSGALLERY2_COULD_NOT_UPDATE_IMAGE_DETAILS') );
	}
	if (!$row->store()) {
		$mainframe->redirect( $redirect , JText::_('COM_RSGALLERY2_COULD_NOT_UPDATE_IMAGE_DETAILS') );	
	}
	
	//Redirect after successfull save
	$mainframe->redirect(JRoute::_( $redirect ), JText::_('COM_RSGALLERY2_DETAILS_SAVED_SUCCESFULLY') );
}

function saveUploadedItem() {
	// Check for request forgeries
	//JRequest::checkToken() or jexit( 'Invalid Token' );
    JSession::checkToken() or jexit( 'Invalid Token' );

	global $rsgConfig;
	$mainframe = JFactory::getApplication();
	$database = JFactory::getDBO();
	$user = JFactory::getUser();
	//Set redirect URL
	$redirect = JRoute::_("index.php?option=com_rsgallery2&rsgOption=myGalleries",false);

	//Get maximum number of images to upload
	$max_images = $rsgConfig->get('uu_maxImages');

	//Get category ID to check rights
	//$gallery_id = JRequest::getInt( 'gallery_id'  , '');
	$input =JFactory::getApplication()->input;
	$gallery_id = $input->get( 'gallery_id', 0, 'INT');		
	
	//Check if user is allowed to upload in this gallery (parent gallery has id $gallery_id)
	if (!rsgAuthorisation::authorisationCreate( (int) $gallery_id)){
		JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
		$mainframe->redirect(JRoute::_( $redirect ));
	}
	
	//Check if number of images is not exceeded for this user
	$query = $database->getQuery(true);
	$query->select('files.id, files.userid');
	$query->from('#__rsgallery2_files AS files');
	$query->where('files.userid = '. (int) $user->id);
	$database->setQuery($query);
	$allImages = $database->loadObjectList();
	$image_count = count($allImages);
	if ($image_count >= $max_images) {
		//Notify user and redirect
		JError::raiseWarning(404, JText::_('COM_RSGALLERY2_MAXIMUM_NUMBER_OF_IMAGES_UPLOADED_REACHED_DELETE_SOME_IMAGES_FIRST'));
		$mainframe->redirect(JRoute::_( $redirect ));
	} else {
		//Go ahead and upload
		$upload = new fileHandler();
		
		//Get parameters from form
		$input =JFactory::getApplication()->input;
		//$i_file = JRequest::getVar( 'i_file', null, 'files', 'array'); 
		$i_file = $input->files->get( 'i_file', null, 'FILES');

		//$gallery_id = JRequest::getInt( 'gallery_id'  , ''); 
		$gallery_id = $input->get( 'gallery_id', 0, 'INT');		
		//$title = JRequest::getString( 'title'  , '');
		$title = $input->get( 'title', '', 'STRING');
		//$descr = JRequest::getVar( 'descr', '', 'post', 'string', JREQUEST_ALLOWRAW );
		$descr = $input->post->get('descr', '', 'RAW');

		//$uploader = JRequest::getVar( 'uploader'  , ''); //No longer used in 3.1.0
		
		//Get filetype
		$file_ext = $upload->checkFileType($i_file['name']);

		//Check whether directories are there and writable
		$check = $upload->preHandlerCheck();
		if ($check !== true ) {
			$mainframe->redirect( $redirect , $check);
		}

		switch ($file_ext) {
			case 'zip':
        		if ($upload->checkSize($i_file) == 1) {
					$ziplist = $upload->extractArchive($i_file);
            		//Set extract dir for uninstall purposes
            		$extractdir = JPATH_ROOT . DS . "media" . DS . $upload->extractDir . DS;
            		//Import images into right folder
            		for ($i = 0; $i<sizeof($ziplist); $i++) {
            			$import = imgUtils::importImage($extractdir . $ziplist[$i], $ziplist[$i], $gallery_id, $title, $descr);
            		}
            		
            		//Clean mediadir
            		fileHandler::cleanMediaDir( $upload->extractDir );

            		//Redirect
            		$mainframe->redirect( $redirect , JText::_('COM_RSGALLERY2_ITEM_UPLOADED_SUCCESFULLY') );
        		} else {
            		//Error message
            		$mainframe->redirect( $redirect , JText::_('COM_RSGALLERY2_ZIP-FILE_IS_TOO_BIG'));
        		}
				break;
			case 'image':
				//Check if image is too big
				if ($i_file['error'] == 1){
					$mainframe->redirect( $redirect , JText::_('COM_RSGALLERY2_IMAGE_SIZE_IS_TOO_BIG_FOR_UPLOAD') );
				}
				
				$file_name = $i_file['name'];
				if ( move_uploaded_file($i_file['tmp_name'], JPATH_ROOT . DS ."media" . DS . $file_name) ) {
					//Import into database and copy to the right places
					$imported = imgUtils::importImage(JPATH_ROOT . DS ."media" . DS . $file_name, $file_name, $gallery_id, $title, $descr);
					
					if ($imported == 1) {
						if (file_exists( JPATH_ROOT . DS ."media" . DS . $file_name ))
							unlink( JPATH_ROOT . DS ."media" . DS . $file_name );
					} else {
						$mainframe->redirect( $redirect , 'Importing image failed! Notify RSGallery2. This should never happen!');
					}
					$mainframe->redirect( $redirect , JText::_('COM_RSGALLERY2_ITEM_UPLOADED_SUCCESFULLY') );
				} else {
					$mainframe->redirect( $redirect , JText::_('COM_RSGALLERY2_UPLOAD_FAILED_BACK_TO_UPLOADSCREEN') );
				}
				break;
			case 'error':
			default:
				$mainframe->redirect( $redirect , JText::_('COM_RSGALLERY2_WRONG_IMAGE_FORMAT_WE_WILL_REDIRECT_YOU_TO_THE_UPLOAD_SCREEN') );
				break;
		}	//end switch ($file_ext)
	}
}

function editCat($id) {
	global $rsgConfig;
	$database = JFactory::getDBO();
	$user = JFactory::getUser();
	$mainframe = JFactory::getApplication();
	$redirect = JRoute::_("index.php?option=com_rsgallery2&rsgOption=myGalleries", false);

	//Only when $id exists!
	if ($id) {
		//Check if user is allowed to edit this gallery 
		$allowed = rsgAuthorisation::authorisationEditGallery($id);
		if (!$allowed){
			JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
			$mainframe->redirect(JRoute::_( $redirect ));
		}

        JHtml::_('behavior.framework',true);

        //Edit category
		$query = 'SELECT * FROM `#__rsgallery2_galleries` WHERE `id` ='. (int) $id;
		$database->setQuery($query);
		$rows = $database->LoadObjectList();
		myGalleries::editCat($rows);

	} else { //Create new gallery (does this ever happen?)
		//Check if maximum number of usercats are already made
		$count = galleryUtils::userCategoryTotal($user->id);
		if ($count >= $rsgConfig->get('uu_maxCat') ) {
			$mainframe->redirect(JRoute::_("index.php?option=com_rsgallery2&page=my_galleries"), JText::_('COM_RSGALLERY2_MAX_USERCAT_ALERT') );
		} else {
            JHtml::_('behavior.framework',true);

            //New category
			myGalleries::editCat();
		}
	}
}

	/**
	 * Saves gallery, either new or the one that was edited
	 * $param int gallery id (0 in case of a new gallery)
	 */

function saveCat($gid) {
	// Check for request forgeries
	//JRequest::checkToken() or jexit( 'Invalid Token' );
    JSession::checkToken() or jexit( 'Invalid Token' );

	global $rsgConfig;
	$mainframe = JFactory::getApplication();
	$user = JFactory::getUser();
	$database = JFactory::getDBO();

	//Set redirect URL
	$redirect = JRoute::_("index.php?option=com_rsgallery2&rsgOption=myGalleries", false);
	
	// When saving a gallery that was edited: gid <> 0 => Check edit permission (of this gallery)
	// When saving a new gallery: gid = 0 => Check create permission (for the parent gallery)
	if ($gid) {
		// Check edit permission on the (existing) gallery that is being saved
		$allowed = rsgAuthorisation::authorisationEditGallery($gid);
		if (!$allowed){
			JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
			$mainframe->redirect(JRoute::_( $redirect ));
		}
	} else {
		//Check if user is allowed to create a gallery in the chosen gallery:
		//$parent_gallery = JRequest::getInt( 'parent');
		$input =JFactory::getApplication()->input;
		$parent_gallery = $input->get( 'parent', 0, 'INT');		

		if (!rsgAuthorisation::authorisationCreate($parent_gallery)){
			JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
			$mainframe->redirect(JRoute::_( $redirect ));
		}
	}

	//Get number of galleries allowed and already present
	$maxcats  = $rsgConfig->get('uu_maxCat');	
	$userCatTotal = galleryUtils::userCategoryTotal($user->id);

	//Check if user is allowed to create more galleries (only if this is a new gallery)
	if ((!$gid) AND ($userCatTotal >= $maxcats)) {
		?>
		<script type="text/javascript">
			alert('<?php echo JText::_('COM_RSGALLERY2_MAX_USERCAT_ALERT');?>');
			location = '<?php echo JRoute::_("index.php?option=com_rsgallery2&page=my_galleries", false); ?>';
		</script>
		<?php
		//$mainframe->redirect( $redirect ,JText::_('COM_RSGALLERY2_MAX_USERCAT_ALERT'));
	} else {
		//Instantiate the gallery object
		$row = new rsgGalleriesItem( $database );
		//Not a new gallery? then load its data
		if ($gid){
			$row->load($gid);
		}
		//Bind user input to $row (parent, name, description, existing gallery: gid, ordering)
		//if (!$row->bind( JRequest::get('post') )) {
		$input =JFactory::getApplication()->input;
		$array = $input->post->getArray();
		// if (!$row->bind( $input->post)) {
		if (!$row->bind($array)) {
			echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}

		//Description: html is allowed
		/* ToDo: May be used to write unwanted code into database
		Check if raw data may be replaced by Jinput with filter
		see below

		$input_options = JFilterInput::getInstance(
			array(
				'img','p','a','u','i','b','strong','span','div','ul','li','ol','h1','h2','h3','h4','h5',
				'table','tr','td','th','tbody','theader','tfooter','br'
			),
			array(
				'src','width','height','alt','style','href','rel','target','align','valign','border','cellpading',
				'cellspacing','title','id','class'
			)
		);

		$postData = new JInput($_POST, array('filter' => $input_options));
		 */
		//$row->description = JRequest::getVar( 'description', '', 'post', 'string', JREQUEST_ALLOWRAW );
		$input =JFactory::getApplication()->input;
		$row->description = $input->post->get('description', '', 'RAW');

		//Code cleaner for xhtml transitional compliance 
		$row->description = str_replace( '<br>', '<br />', $row->description );
		//Make the alias for SEF (no matter if it existed or not for frontend editing)
		$row->alias = JFilterOutput::stringURLSafe($row->name);
		//Get/do some additional stuff
		$row->date = date( 'Y-m-d H:i:s' );
		if (!$row->uid){	//Don't change owner
			$row->uid = $user->id;
		}
		//Do some checks (overloads JTable::check() with rsgGalleriesItem::check())
		if (!$row->check()) {
			echo "<script> alert('row->check: ".$row->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}
		//And store the row (this is where the asset is also stored; JTable::store())
		if (!$row->store()) {
			echo "<script> alert('row->store: ".$row->getError()."'); window.history.go(-1); </script>\n";
			exit();
		}
		//Then checkin and reorder (JTable:checkin() and JTable::reorder())
		$row->checkin();
		$row->reorder( );
		//Finally redirect with success message
		if ($gid) {
			$mainframe->redirect( $redirect , JText::_('COM_RSGALLERY2_GALLERY_DETAILS_UPDATED') );
		} else {		
			$mainframe->redirect( $redirect , JText::_('COM_RSGALLERY2_NEW_GALLERY_CREATED') );
		}
	}
	//JText::_('COM_RSGALLERY2_ALERT_NONEWCAT')
	//JText::_('COM_RSGALLERY2_COULD_NOT_UPDATE_GALLERY_DETAILS')
}

function deleteCat() {
	global $rsgConfig;
	$mainframe = JFactory::getApplication();
	$my = JFactory::getUser();
	$database = JFactory::getDBO();

	//Get values from URL
	//$gid = JRequest::getInt( 'gid'  , null);
	$input =JFactory::getApplication()->input;
	$gid = $input->get( 'gid', null, 'INT');		
	
	//Set redirect URL
	$redirect = JRoute::_("index.php?option=com_rsgallery2&rsgOption=myGalleries",false);

	//Is user allowed to delete this gallery?
	$allowed = rsgAuthorisation::authorisationDeleteGallery( (int) $gid);
	if (!$allowed) {
		$mainframe->redirect( $redirect ,JText::_('COM_RSGALLERY2_PERMISSION_NOT_ALLOWED_DELETE_GALLERY'));
	} else {
		//Check if gallery has children
		$query = 'SELECT COUNT(1) FROM `#__rsgallery2_galleries` WHERE `parent` = '. (int) $gid;
		$database->setQuery($query);
		$count = $database->loadResult();
		if ($count > 0) {
			$mainframe->redirect( $redirect ,JText::_('COM_RSGALLERY2_USERCAT_SUBCATS'));
		}
		
		//No children from here, so lets continue
		//Get rsgImagesItem object
		$gallery_row = new rsgGalleriesItem( $database );
		//Get category details
		$gallery_row->load($gid);

		//Delete images
		$query = 'SELECT `name` FROM `#__rsgallery2_files` WHERE `gallery_id` = '. (int) $gid;
		$database->setQuery($query);
		$result = $database->loadColumn();
		$error = 0;
		foreach ($result as $filename) {
			if ( !imgUtils::deleteImage($filename) ) 
				$error++;
		}
		
		//Error checking
		if ($error == 0) {
			//Gallery can be deleted
			if (!$gallery_row->delete($gid)){
				$mainframe->redirect( $redirect ,JText::_('COM_RSGALLERY2_GALLERY_COULD_NOT_BE_DELETED'));
			} else {
				//Ok, goto mainpage
				$mainframe->redirect( $redirect ,JText::_('COM_RSGALLERY2_GALLERY_DELETED'));
			}
		} else {
			//Abort and return to mainscreen
			$mainframe->redirect( $redirect ,JText::_('COM_RSGALLERY2_GALLERY_NOT_DELETED_SINCE_NOT_ALL_IMAGES_DELETED'));
		}
	}
}

function editStateGallery($galleryId, $newState) {
	$mainframe = JFactory::getApplication();
	$user = JFactory::getUser();
	$database = JFactory::getDBO();
	
	//Set redirect URL
	$redirect = JRoute::_("index.php?option=com_rsgallery2&rsgOption=myGalleries", false);

	//Check if user is allowed to edit the state of this gallery (to prevent direct access)
	if (!rsgAuthorisation::authorisationEditStateGallery( (int) $galleryId)){
		JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
		$mainframe->redirect(JRoute::_( $redirect ));
	}
	
	if ($galleryId) {
		$query = 'UPDATE `#__rsgallery2_galleries` '.
			' SET `published` = '. (int) $newState .
			' WHERE `id` = '. (int) $galleryId;
		$database->setQuery($query);
		if ($database->execute()) {
			$mainframe->redirect( $redirect , JText::_('COM_RSGALLERY2_GALLERY_DETAILS_UPDATED') );
		} else {
			$mainframe->redirect( $redirect , JText::_('COM_RSGALLERY2_COULD_NOT_UPDATE_GALLERY_DETAILS') );
		}
	}
}

function editStateItem($id, $newState) {
	$mainframe = JFactory::getApplication();
	//$Itemid = JRequest::getInt( 'Itemid'  , '');
	$input =JFactory::getApplication()->input;
	$Itemid = $input->get( 'Itemid', 0, 'INT');		
	$database = JFactory::getDBO();
	$user = JFactory::getUser();
	//Set redirect URL
	$redirect = JRoute::_("index.php?option=com_rsgallery2&rsgOption=myGalleries&Itemid=$Itemid", false);

	//Check if user is allowed to edit the state of this item (to prevent direct access)
	if (!rsgAuthorisation::authorisationEditStateItem( (int) $id)){
		JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
		$mainframe->redirect(JRoute::_( $redirect ));
	}

	$query = 'UPDATE `#__rsgallery2_files` '.
			' SET `published` = ' . (int) $newState .
			' WHERE `id` = ' . (int) $id;
	$database->setQuery($query);

	if ($database->execute()) {
		$mainframe->redirect(JRoute::_( $redirect ), JText::_('COM_RSGALLERY2_DETAILS_SAVED_SUCCESFULLY') );
	} else {
		//echo JText::_('COM_RSGALLERY2_ERROR-').mysql_error();
		$mainframe->redirect( $redirect , JText::_('COM_RSGALLERY2_COULD_NOT_UPDATE_IMAGE_DETAILS') );
	}
}
function editStateItems($cid,$newstate=0) {
	$mainframe = JFactory::getApplication();
	//$Itemid = JRequest::getInt( 'Itemid'  , '');
	$input =JFactory::getApplication()->input;
	$Itemid = $input->get( 'Itemid', 0, 'INT');		
	$database = JFactory::getDBO();
	$user = JFactory::getUser();
	$redirect = JRoute::_("index.php?option=com_rsgallery2&rsgOption=myGalleries&Itemid=$Itemid", false);
	
	//Get array of item ids where user is allowed to edit the state of the item
	$allowedId = Null;
	foreach ($cid as &$value) {
		if (rsgAuthorisation::authorisationEditStateItem( (int) $value)){
			$allowedId[] = (int) $value;
		}
	}

	// Are there galleries for which we have permission?
	if ($allowedId) {
		//Create query to edit state of allowed items
		$query = 'UPDATE `#__rsgallery2_files` SET '.
			' `published` = ' . (int) $newstate .
			' WHERE `id` '.
			' IN (' .implode(',',$allowedId).')';
		$database->setQuery($query);
		if ($database->execute()) {
			$mainframe->redirect(JRoute::_( $redirect ), JText::_('COM_RSGALLERY2_DETAILS_SAVED_SUCCESFULLY') );
		} else {
			$mainframe->redirect( $redirect , JText::_('COM_RSGALLERY2_COULD_NOT_UPDATE_IMAGE_DETAILS') );
		}
	} else {
		// Not allowed to update anything
		$mainframe->redirect( $redirect , JText::_('COM_RSGALLERY2_COULD_NOT_UPDATE_IMAGE_DETAILS') );
	}
}

function deleteItems($cid) {
	$mainframe = JFactory::getApplication();
	//$Itemid = JRequest::getInt( 'Itemid'  , '');
	$input =JFactory::getApplication()->input;
	$Itemid = $input->get( 'Itemid', 0, 'INT');		
	$database = JFactory::getDBO();
	$user = JFactory::getUser();
	$redirect = JRoute::_("index.php?option=com_rsgallery2&rsgOption=myGalleries&Itemid=$Itemid", false);
	$success = false;

	//Get array of items where user is allowed to delete the item
	foreach ($cid as &$value) {
		if (rsgAuthorisation::authorisationDeleteItem($value)){
			$filename 	= galleryUtils::getFileNameFromId($value);
			//delete the image
			imgUtils::deleteImage($filename);
			$success = true;
		} else {
			$title = galleryUtils::getTitleFromId($value);
			JError::raiseWarning(404, JText::_('COM_RSGALLERY2_PERMISSION_NOT_ALLOWED_DELETE_ITEM'));
			JError::raiseWarning(404, $title);
		}
		
	}
	if ($success) {
		$msg = JText::_('COM_RSGALLERY2_MAGE-S_DELETED_SUCCESFULLY');
	}
	$mainframe->redirect(JRoute::_("index.php?option=com_rsgallery2&rsgOption=myGalleries&Itemid=$Itemid",false), $msg );
}
?>
