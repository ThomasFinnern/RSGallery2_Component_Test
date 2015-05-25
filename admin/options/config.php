<?php
/**
* Galleries option for RSGallery2
* @version $Id: config.php 1085 2012-06-24 13:44:29Z mirjam $
* @package RSGallery2
* @copyright (C) 2003 - 2012 RSGallery2
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* RSGallery is Free Software
*/

defined( '_JEXEC' ) or die();

require_once( $rsgOptions_path . 'config.html.php' );

// Only those with core.manage can get here via $rsgOption = config
// Check if core.admin is allowed
if (!JFactory::getUser()->authorise('core.admin', 'com_rsgallery2')) {
	// return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
    JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');
	return;	// 150518 Does not return JError::raiseWarning object $error 
} else {
	switch( $task ){
		case 'cancel';
			cancelConfig($option);
			break;
		case 'config_dumpVars':
			//HTML_RSGallery::RSGalleryHeader('viewChangelog', JText::_('COM_RSGALLERY2_CONFIGURATION_VARIABLES'));
			config_dumpVars();
			HTML_RSGallery::RSGalleryFooter();
			break;
		case 'applyConfig':
			//HTML_RSGallery::RSGalleryHeader('config', JText::_('COM_RSGALLERY2_CONFIGURATION'));
			saveConfig();
			showConfig($option);
			HTML_RSGallery::RSGalleryFooter();
		break;
		case 'saveConfig':
			//HTML_RSGallery::RSGalleryHeader('cpanel', JText::_('COM_RSGALLERY2_CONTROL_PANEL'));
			saveConfig();
			HTML_RSGallery::showCP();
			HTML_RSGallery::RSGalleryFooter();
		break;
		case "showConfig":
			//HTML_RSGallery::RSGalleryHeader('config', JText::_('COM_RSGALLERY2_CONFIGURATION'));
			showConfig();
			HTML_RSGallery::RSGalleryFooter();
		break;

		case 'config_rawEdit_apply':
			//HTML_RSGallery::RSGalleryHeader('config_rawEdit', JText::_('COM_RSGALLERY2_CONFIGURATION_RAW_EDIT'));
			saveConfig();
			config_rawEdit( );
			HTML_RSGallery::RSGalleryFooter();
		break;
		case 'config_rawEdit_save':
			//HTML_RSGallery::RSGalleryHeader('cpanel', JText::_('COM_RSGALLERY2_CONTROL_PANEL'));
			saveConfig();
			HTML_RSGallery::showCP();
			HTML_RSGallery::RSGalleryFooter();
		break;
		case 'config_rawEdit':
			//HTML_RSGallery::RSGalleryHeader('config_rawEdit', JText::_('COM_RSGALLERY2_CONFIGURATION_RAW_EDIT'));
			config_rawEdit( );
			HTML_RSGallery::RSGalleryFooter();
		break;
	}	//end of task switch
}

/**
 *
 */
function config_dumpVars(){
    global $rsgConfig;

    $vars = get_object_vars( $rsgConfig );

    echo '<pre>';
    print_r( $rsgConfig );
    echo '</pre>';
}

/**
 * @param bool $save
 */
function config_rawEdit( $save=false ){
    if( $save ){
        // save
    }

    html_rsg2_config::config_rawEdit();
}

/**
 * @todo if thumbname size has changed, advise user to regenerate thumbs
 * @throws Exception
 */
function saveConfig(){
    global $rsgConfig;
    $rsgConfig = new rsgConfig();
    
    if( $rsgConfig->saveConfig( $_REQUEST )){
		JFactory::getApplication()->enqueueMessage( JText::_('COM_RSGALLERY2_CONFIGURATION_SAVED') );
		// save successful, try creating some image directories if we were asked to
		
		// 140701 original: if( JRequest::getBool( 'createImgDirs' ))
		$input =JFactory::getApplication()->input;		
		$createImgDirs = $input->get( 'createImgDirs', false , 'BOOL');		
		if( $createImgDirs)
			JFactory::getApplication()->enqueueMessage( JText::_('COM_RSGALLERY2_CREATING_IMAGE_DIRECTORIES_NOT_IMPLEMENTED_YET') );
    } else {
			JFactory::getApplication()->enqueueMessage( JText::_('COM_RSGALLERY2_ERROR_SAVING_CONFIGURATION'), 'Error' );
    }
    
}

/**
 *
 */
function showConfig(){
    global $rsgConfig;

    $langs      = array();
    //$imageLib   = array();
    $lists      = array();

    /**
     * detect available graphics libraries
     * @todo call imgUtils graphics lib detection when it is built
    */
    $graphicsLib = array();

    $result = GD2::detect();
	if( $result )
		$graphicsLib[] = JHtml::_("select.option", 'gd2', $result );
	else
		$graphicsLib[] = JHtml::_("select.option", 'gd2', JText::_('COM_RSGALLERY2_GD2_NOT_DETECTED') );

    $result = ImageMagick::detect();
    if( $result )
        $graphicsLib[] = JHtml::_("select.option", 'imagemagick', $result );
    else
        $graphicsLib[] = JHtml::_("select.option", 'imagemagick', JText::_('COM_RSGALLERY2_IMAGEMAGICK_NOT_DETECTED') );

    $result = Netpbm::detect();
    if( $result )
        $graphicsLib[] = JHtml::_("select.option", 'netpbm', $result );
    else
        $graphicsLib[] = JHtml::_("select.option", 'netpbm', JText::_('COM_RSGALLERY2_NETPBM_NOT_DETECTED') );
    
    
    $lists['graphicsLib'] = JHtml::_("select.genericlist",$graphicsLib, 'graphicsLib', '', 'value', 'text', $rsgConfig->graphicsLib );

    html_rsg2_config::showconfig( $lists );
}

/**
 * @param string $option
 * @throws Exception
 */
function cancelConfig( $option ) {
	$mainframe =& JFactory::getApplication();
	$mainframe->redirect("index.php?option=$option");
}
?>