<?php
/**
* This file contains the non-presentation processing for the Admin section of RSGallery.
* @version $Id: admin.rsgallery2.php 1085 2012-06-24 13:44:29Z mirjam $
* @package RSGallery2
* @copyright (C) 2003 - 2012 RSGallery2
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* RSGallery is Free Software
*/
defined( '_JEXEC' ) or die();

// Initialize RSG2 core functionality
require_once( JPATH_COMPONENT.'/init.rsgallery2.php' );

$Rsg2DebugActive = $rsgConfig->get('debug');
if ($Rsg2DebugActive)
{
	// Include the JLog class.
	jimport('joomla.log.log');

	// Get the date for log file name
	$date = JFactory::getDate()->format('Y-m-d');

	// Add the logger.
	JLog::addLogger(
		// Pass an array of configuration options
		array(
				// Set the name of the log file
				//'text_file' => substr($application->scope, 4) . ".log.php",
				'text_file' => 'rsgallery2.adm.log.'.$date.'.php',

				// (optional) you can change the directory
				'text_file_path' => 'logs'
		),
		JLog::ALL ^ JLog::DEBUG // leave out db messages
	);
	
	// start logging...
	JLog::add('Start rsgallery2.php in admin: debug active in RSGallery2'); //, JLog::DEBUG);
}

//Instantiate user variables but don't show a front end template
rsgInstance::instance( 'request', false );

//Load Tooltips
JHtml::_('behavior.tooltip');

require_once JPATH_COMPONENT.'/helpers/rsgallery2.php';

//Access check
$canAdmin	= JFactory::getUser()->authorise('core.admin',	'com_rsgallery2');
$canManage	= JFactory::getUser()->authorise('core.manage',	'com_rsgallery2');
if (!$canManage) {
	// return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');
	return;	// 150518 Does not return JError::raiseWarning object $error 
}

$document = JFactory::getDocument();
//$document->addStyleSheet( JURI::base( true )."/components/com_rsgallery2/admin.rsgallery2.css");
$document->addStyleSheet( JURI_SITE."administrator/components/com_rsgallery2/admin.rsgallery2.css");

//require_once( JApplicationHelper::getPath('admin_html') );
require_once(JPATH_COMPONENT.'/admin.rsgallery2.html.php');///J3

global $opt, $catid, $uploadStep, $numberOfUploads, $e_id ;
$input =JFactory::getApplication()->input;
//$task				= JRequest::getCmd('task');
$task               = $input->get( 'task', '', 'CMD');		
//$option			= strtolower(JRequest::getCmd('option'));
$option             = strtolower($input->get( 'option', '', 'CMD'));		
//$catid			= JRequest::getInt('catid', null);
$catid              = $input->get( 'catid', null, 'INT');		

//$uploadStep			= JRequest::getInt('uploadStep', 0 );
$uploadStep = $input->get( 'uploadStep', 0, 'INT');		
//$numberOfUploads	= JRequest::getInt('numberOfUploads', 1 );
$numberOfUploads = $input->get( 'numberOfUploads', 1, 'INT');		

//$firstCid = JRequest::getInt('cid', 0);
$firstCid = $input->get( 'cid', 0, 'INT');					
//$id     = JRequest::getInt('id', 0 );
$id     = $input->get( 'id', 0, 'INT');					

//$rsgOption = JRequest::getCmd('rsgOption', null );
$rsgOption = $input->get( 'rsgOption', null, 'CMD');		

$my = JFactory::getUser();

if($Rsg2DebugActive)
{
	//$Delim = "\n";
	$Delim = " ";
    // show active task
    $DebTxt = "==> base.rsgallery2.php".$Delim ."----------".$Delim;
    $DebTxt = $DebTxt . "\$task: $task".$Delim;
    $DebTxt = $DebTxt . "\$option: $option".$Delim;
    $DebTxt = $DebTxt . "\$catid: $catid".$Delim;
    $DebTxt = $DebTxt . "\$firstCid: $firstCid".$Delim;
    $DebTxt = $DebTxt . "\$id: $id".$Delim;
    $DebTxt = $DebTxt . "\$rsgOption: $rsgOption".$Delim;

    JLog::add($DebTxt); //, JLog::DEBUG);
}

///Get the toolbar in here for J3 compatibility (since toolbar.rsgallery2.php is no longer autoloaded)
require_once( JPATH_COMPONENT.'/toolbar.rsgallery2.php');

/**
 * this is the new $rsgOption switch.  each option will have a switch for $task within it.
 */
switch( $rsgOption ) {
    case 'galleries':
        require_once( $rsgOptions_path . 'galleries.php' );
    	break;
    case 'images':
        require_once( $rsgOptions_path . 'images.php' );
    	break;
    case 'comments':
        require_once( $rsgOptions_path . 'comments.php' );
   		break;
    case 'config':
        require_once( $rsgOptions_path . 'config.php' );
    	break;
//	case 'template':
//		require_once( $rsgOptions_path . 'templates.php' );
//		break;
	case 'installer':
		require_once( $rsgOptions_path . 'installer.php' );
		break;
	case 'maintenance':
    	require_once( $rsgOptions_path . 'maintenance.php' );
    	break;
}

// only use the legacy task switch if rsgOption is not used. [MK not truly legacy but still used!]
// these tasks require admin or super admin privileges.
if( $rsgOption == '' ){
	// 140701 original: switch ( JRequest::getCmd('task', null) ){
	$task = $input->get( 'task', '', 'CMD');		
	switch ( $task ){
		//Special/debug tasks
		case 'purgeEverything':
			purgeEverything();	//canAdmin check in this function
			HTML_RSGallery::showCP();
			HTML_RSGallery::RSGalleryFooter();
			break;
		case 'reallyUninstall':
			reallyUninstall();	//canAdmin check in this function
			HTML_RSGallery::showCP();
			HTML_RSGallery::RSGalleryFooter();
			break;
		//Config tasks
		// this is just a kludge until all links and form vars to configuration functions have been updated to use $rsgOption = 'config';
		/*
		case 'applyConfig':
		case 'saveConfig':
		case "showConfig":
		*/
		case 'config_dumpVars':
		case 'config_rawEdit_apply':
		case 'config_rawEdit_save':
		case 'config_rawEdit':
			$rsgOption = 'config';
			require_once( $rsgOptions_path . 'config.php' );
		break;
		//Image tasks
		case "edit_image":
			HTML_RSGallery::RSGalleryHeader('edit', JText::_('COM_RSGALLERY2_EDIT'));
			editImageX($option, firstCid);
			HTML_RSGallery::RSGalleryFooter();
			break;

		case "uploadX":
			JFactory::getApplication()->enqueueMessage( 'Marked for removal: uploadX', 'Notice' );
			HTML_RSGallery::RSGalleryHeader('browser', JText::_('COM_RSGALLERY2_UPLOAD'));
			showUpload();
			HTML_RSGallery::RSGalleryFooter();
			break;

		case "batchuploadX":
			JFactory::getApplication()->enqueueMessage( 'Marked for removal: batchuploadX', 'Notice' );
			HTML_RSGallery::RSGalleryHeader('', JText::_('COM_RSGALLERY2_UPLOAD_ZIP-FILE'));
			batch_upload($option, $task);
			HTML_RSGallery::RSGalleryFooter();
			break;
		case "save_batchuploadX":
			JFactory::getApplication()->enqueueMessage( 'Marked for removal: save_batchuploadX', 'Notice' );
			save_batchupload();
			break;
		//Image and category tasks
		case "categories_orderup":
		case "images_orderup":
			orderRSGallery( firstCid, -1, $option, $task );
			break;
		case "categories_orderdown":
		case "images_orderdown":
			orderRSGallery( firstCid, 1, $option, $task );
			break;
		//Special/debug tasks
		case 'viewChangelog':
			HTML_RSGallery::RSGalleryHeader('viewChangelog', JText::_('COM_RSGALLERY2_CHANGELOG'));
			viewChangelog();
			HTML_RSGallery::RSGalleryFooter();
			break;
		case "controlPanel":
		default:
			HTML_RSGallery::showCP();
			HTML_RSGallery::RSGalleryFooter();
			break;
	}
}

/**
 * @param string $filename The name of the php (temporary) uploaded file
 * @param string $userfile_name The name of the file to put in the temp directory
 * @param string $msg The message to return
 * @return bool
 */
function uploadFile( $filename, $userfile_name, &$msg ) {
	
	$baseDir = JPATH_SITE . '/media' ;

	if (file_exists( $baseDir )) {
		if (is_writable( $baseDir )) {
			if (move_uploaded_file( $filename, $baseDir . $userfile_name )) {
				// Try making the file writeable first. 				
				// if (JClientFtp::chmod( $baseDir . $userfile_name, 0777 )) {
				//if (JPath::setPermissions( $baseDir . $userfile_name, 0777 )) {
				if (JPath::setPermissions( $baseDir . $userfile_name)) {
					return true;
				} else {
					$msg = JText::_('COM_RSGALLERY2_FAILED_TO_CHANGE_THE_PERMISSIONS_OF_THE_UPLOADED_FILE');
				}
			} else {
				$msg = JText::_('COM_RSGALLERY2_FAILED_TO_MOVE_UPLOADED_FILE_TO_MEDIA_DIRECTORY');
			}
		} else {
			$msg = JText::_('COM_RSGALLERY2_UPLOAD_FAILED_AS_MEDIA_DIRECTORY_IS_NOT_WRITABLE');
		}
	} else {
		$msg = JText::_('COM_RSGALLERY2_UPLOAD_FAILED_AS_MEDIA_DIRECTORY_DOES_NOT_EXIST');
	}
	return false;
}

/**
 *
 */
function viewChangelog(){
    echo '<pre>';
    readfile( JPATH_RSGALLERY2_ADMIN.'/changelog.php' );
    echo '</pre>';
}

/**
 * deletes all pictures, thumbs and their database entries. It leaves category information in DB intact.
 * this is a quick n dirty function for development, it shouldn't be available for regular users.
 * @return object
 */
function purgeEverything(){
    global $rsgConfig;

	//Access check
	$canAdmin	= JFactory::getUser()->authorise('core.admin',	'com_rsgallery2');
	if (!$canAdmin) {
		// return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
		JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');
		return;	// 150518 Does not return JError::raiseWarning object $error 		
	} else {
		$fullPath_thumb = JPATH_ROOT.$rsgConfig->get('imgPath_thumb') . '/';
		$fullPath_display = JPATH_ROOT.$rsgConfig->get('imgPath_display') . '/';
		$fullPath_original = JPATH_ROOT.$rsgConfig->get('imgPath_original') . '/';

		processAdminSqlQueryVerbosely( 'DELETE FROM #__rsgallery2_files', JText::_('COM_RSGALLERY2_PURGED_IMAGE_ENTRIES_FROM_DATABASE') );
		processAdminSqlQueryVerbosely( 'DELETE FROM #__rsgallery2_galleries', JText::_('COM_RSGALLERY2_PURGED_GALLERIES_FROM_DATABASE') );
		processAdminSqlQueryVerbosely( 'DELETE FROM #__rsgallery2_config', JText::_('COM_RSGALLERY2_PURGED_CONFIG_FROM_DATABASE') );
		processAdminSqlQueryVerbosely( 'DELETE FROM #__rsgallery2_comments', JText::_('COM_RSGALLERY2_PURGED_COMMENTS_FROM_DATABASE') );
		processAdminSqlQueryVerbosely( 'DELETE FROM #__rsgallery2_acl', JText::_('COM_RSGALLERY2_ACCESS_CONTROL_DATA_DELETED' ));
		
		// remove thumbnails
		HTML_RSGALLERY::printAdminMsg( JText::_('COM_RSGALLERY2_REMOVING_THUMB_IMAGES') );
		foreach ( glob( $fullPath_thumb.'*' ) as $filename ) {
			if( is_file( $filename )) unlink( $filename );
		}
		
		// remove display imgs
		HTML_RSGALLERY::printAdminMsg( JText::_('COM_RSGALLERY2_REMOVING_ORIGINAL_IMAGES') );
		foreach ( glob( $fullPath_display.'*' ) as $filename ) {
			if( is_file( $filename )) unlink( $filename );
		}
		
		// remove display imgs
		HTML_RSGALLERY::printAdminMsg( JText::_('COM_RSGALLERY2_REMOVING_ORIGINAL_IMAGES') );
		foreach ( glob( $fullPath_original.'*' ) as $filename ) {
			if( is_file( $filename )) unlink( $filename );
		}
		
		HTML_RSGALLERY::printAdminMsg( JText::_('COM_RSGALLERY2_PURGED'), true );
	}

    return;
}

/**
 * drops all RSG2 tables, deletes image directory structure
 * use before uninstalling to REALLY uninstall
 * @todo This is a quick hack.  make it work on all OS and with non default directories.
 * @return object
 */
function reallyUninstall(){
    
    //Access check
	$canAdmin	= JFactory::getUser()->authorise('core.admin',	'com_rsgallery2');
	if (!$canAdmin) {
		// return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
		JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');
		return;	// 150518 Does not return JError::raiseWarning object $error 
	} else {
		passthru( "rm -r ".JPATH_SITE."/images/rsgallery");
		HTML_RSGALLERY::printAdminMsg( JText::_('COM_RSGALLERY2_USED_RM_-R_TO_ATTEMPT_TO_REMOVE_JPATH_SITE_IMAGES_RSGALLERY') );

		processAdminSqlQueryVerbosely( 'DROP TABLE IF EXISTS #__rsgallery2_acl', JText::_('COM_RSGALLERY2_DROPED_TABLE___RSGALLERY2_GALLERIES') );
		processAdminSqlQueryVerbosely( 'DROP TABLE IF EXISTS #__rsgallery2_files', JText::_('COM_RSGALLERY2_DROPED_TABLE___RSGALLERY2_FILES') );
		processAdminSqlQueryVerbosely( 'DROP TABLE IF EXISTS #__rsgallery2_cats', JText::_('COM_RSGALLERY2_DROPED_TABLE___RSGALLERY2_GALLERIES') );
		processAdminSqlQueryVerbosely( 'DROP TABLE IF EXISTS #__rsgallery2_galleries', JText::_('COM_RSGALLERY2_DROPED_TABLE___RSGALLERY2_GALLERIES') );
		processAdminSqlQueryVerbosely( 'DROP TABLE IF EXISTS #__rsgallery2_config', JText::_('COM_RSGALLERY2_DROPED_TABLE___RSGALLERY2_CONFIG') );
		processAdminSqlQueryVerbosely( 'DROP TABLE IF EXISTS #__rsgallery2_comments', JText::_('COM_RSGALLERY2_DROPED_TABLE___RSGALLERY2_COMMENTS') );

		HTML_RSGALLERY::printAdminMsg( JText::_('COM_RSGALLERY2_REAL_UNINST_DONE') );
	}

    return;
}

/**
 * runs a sql query, displays admin message on success or error on error
 * @param string $query sql query
 * @param string $successMsg message to display on success
 * @return boolean value indicating success
 */
function processAdminSqlQueryVerbosely( $query, $successMsg ){
    $database = JFactory::getDBO();
    
    $database->setQuery( $query );
    $database->execute();
    if($database->getErrorMsg()){
            HTML_RSGALLERY::printAdminMsg( $database->getErrorMsg(), true );
            return false;
    }
    else{
        HTML_RSGALLERY::printAdminMsg( $successMsg );
        return true;
    }
}

/**
 * @param string $option
 */
function cancelGallery($option) {
    global $mainframe;

    $mainframe->redirect("index.php?option=$option");
}

