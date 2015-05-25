<?php
/**
* Galleries option for RSGallery2
* @version $Id: galleries.php 1084 2012-06-17 15:25:18Z mirjam $
* @package RSGallery2
* @copyright (C) 2003 - 2012 RSGallery2
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* RSGallery is Free Software
*/

defined( '_JEXEC' ) or die();

require_once( $rsgOptions_path . 'galleries.html.php' );
require_once( $rsgOptions_path . 'galleries.class.php' );
require_once( $rsgOptions_path . 'images.class.php' );

//$cid = JRequest::getVar( 'cid' , array(), 'default', 'array' );
$input =JFactory::getApplication()->input;
$cid = $input->get( 'cid', array(), 'ARRAY');

switch( $task ){
    case 'new':
	case 'add':
		edit( $option, 0 );
        break;

    case 'edit':
        edit( $option, $cid[0] );
        break;

    case 'editA':
        edit( $option, $id );
        break;
	
	case 'apply':
    case 'save':
        save( $option );
        break;

    case 'remove':
        removeWarn( $cid, $option );
        break;

    case 'removeReal':
        removeReal( $cid, $option );
        break;

    case 'publish':
        publish( $cid, 1, $option );
        break;

    case 'unpublish':
        publish( $cid, 0, $option );
        break;

    case 'cancel':
        cancel( $option );
        break;

    case 'orderup':
        order( $cid[0], -1, $option );
        break;

    case 'orderdown':
        order( $cid[0], 1, $option );
        break;

	case 'saveorder':
		saveOrder( $cid );
		break;
		
    case 'show':
    default:
        show();
    break;
}

/**
 * show galleries
 * @throws Exception
 */
function show(){
    global $mosConfig_list_limit;	//Todo: $app = &JFactory::getApplication();$limit = $app->getCfg('list_limit'); replaces $mosConfig_list_limit
	$mainframe = JFactory::getApplication();
	//$option = JRequest::getCmd('option');
	$input =JFactory::getApplication()->input;
	$option = $input->get( 'option', '', 'CMD');	
	$database = JFactory::getDBO();
    $limit      = $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
    $limitstart = $mainframe->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );
    $levellimit = $mainframe->getUserStateFromRequest( "view{$option}limit", 'levellimit', 10 );
    $search     = $mainframe->getUserStateFromRequest( "search{$option}", 'search', '' );
    $search     = $database->escape( trim( strtolower( $search ) ) );

    // select the records
    // note, since this is a tree we have to do the limits code-side
    if ($search) {
        $query = "SELECT id"
        . " FROM #__rsgallery2_galleries"
        . " WHERE LOWER( name ) LIKE '%" . strtolower( $search ) . "%'"
        ;
        $database->setQuery( $query );
        $search_rows = $database->loadColumn();
    }

//  $query = "SELECT a.*, u.name AS editor"	//J!1.6 has parent_id instead of parent and title instead of name
    $query = "SELECT a.*, u.name AS editor, a.parent AS parent_id, a.name AS title, vl.title as access_level" //MK// [change] [J!1.6 has parent_id instead of parent and title instead of name]	
    . " FROM #__rsgallery2_galleries AS a"
    . " LEFT JOIN #__users AS u ON u.id = a.checked_out"
	. " LEFT JOIN #__viewlevels AS vl ON vl.id = a.access"
    . " ORDER BY a.ordering"
    ;
	try
	{
		$database->setQuery( $query );
		$rows = $database->loadObjectList();
	}
	catch (RuntimeException $e)
	{
		echo $e->getMessage(); 
		return false;
	}
		
    // establish the hierarchy of the menu
    $children = array();
    // first pass - collect children
    foreach ($rows as $v ) {
        $pt = $v->parent;
        $list = @$children[$pt] ? $children[$pt] : array();
        array_push( $list, $v );
        $children[$pt] = $list;
    }
    // second pass - get an indent list of the items
    $list = JHtml::_('menu.treerecurse',  0, '', array(), $children, max( 0, $levellimit-1 ) );
    // eventually only pick out the searched items.
    if ($search) {
        $list1 = array();

        foreach ($search_rows as $sid ) {
            foreach ($list as $item) {
                if ($item->id == $sid) {
                    $list1[] = $item;
                }
            }
        }
        // replace full list with found items
        $list = $list1;
    }

    $total = count( $list );
	jimport("joomla.html.pagination");
    $pageNav = new JPagination( $total, $limitstart, $limit  );

	//@todo: is this $lists['levellist'] unused?
    $lists['levellist'] = JHtml::_("Select.integerlist", 1, 20, 1, 'levellimit', 'size="1" onchange="document.adminForm.submit();"', $levellimit );

    // slice out elements based on limits
    $list = array_slice( $list, $pageNav->limitstart, $pageNav->limit );

    html_rsg2_galleries::show( $list, $lists, $search, $pageNav );

    return true;
}


/**
 * Compiles information to add or edit
 * @param string $option
 * @param int $id The unique id of the record to edit (0 if new)
 */
function edit( $option, $id ) {
	global $rsgOptions_path;
	$mainframe = JFactory::getApplication();
	$database = JFactory::getDBO();
	$my = JFactory::getUser();
	
    $lists = array();

    $row = new rsgGalleriesItem( $database );
    // load the row from the db table
    $row->load( $id );

    // fail if checked out not by 'me'
    if ($row->isCheckedOut( $my->id )) {
		// ToDo: Translate 
		$msg = JText::_( 'The module $row->title is currently being edited by another administrator' );
	    $mainframe->enqueueMessage($msg);
        $mainframe->redirect( 'index.php?option='. $option );
    }

	$canAdmin	= $my->authorise('core.admin', 'com_rsgallery2');
	$canEditStateGallery = $my->authorise('core.edit.state','com_rsgallery2.gallery.'.$row->id);
	
    if ($id) {
        $row->checkout( $my->id );
    } else {
        // initialise new record
        $row->published = 1;
        $row->order     = 0;
        $row->uid		= $my->id;
    }

    // build the html select list for ordering
    $query = "SELECT ordering AS value, name AS text"
    . "\n FROM #__rsgallery2_galleries"
    . "\n ORDER BY ordering"
    ;

	// build list of users when user has core.admin, else give owners name
	if ($canAdmin) {
		$lists['uid'] 			= JHtml::_('list.users', 'uid', $row->uid, 1, NULL, 'name', 0 );
	} else {
		$lists['uid'] 			= JFactory::getUser($row->uid)->name;
	}
    // build the html select list for ordering
    $lists['ordering']          = JHtml::_('list.ordering', 'ordering', $query, Null, $id, 0 );
    // build the html select list for parent item
    $lists['parent']        = galleryParentSelectList( $row );
    // build the html select list for published if allowed to change state
	if ($canEditStateGallery) {
		$lists['published'] = JHtml::_("select.booleanlist", 'published', 'class="inputbox"', $row->published ); 
	} else {
		$lists['published'] = ($row->published ? JText::_('JYES') : JText::_('JNO'));
	}
	
	//--- Add info / text as form fields to be edited -----------------------
	// Commented out (voting_view, voting_vote, gallery_sort_order) 
	$file 	= JPATH_SITE .'/administrator/components/com_rsgallery2/options/galleries.item.xml';

	// ToDo: Debug / Test to check if following replacement is working 
	//$params = new JParameter( $row->params, $file );
	$jparams = new JRegistry();
	$params = $jparams->get($row->params, $file);
/// ToDo: Jparameter ... Try this for J3:
/*
$params2 = new JForm('params');
$params2->loadFile($file);///var_dump($row);
$params2->bind( $row->params );

$fields = $params2->getFieldset('params');
foreach( $fields AS $field => $obj ){
  echo $params2->getLabel( $field, null );
  echo $params2->getInput( $field, null, null );	
}


I have been working hard on this and come up with a solution, its the "proper" way to do it that other modules do but are hidden behind multiple layers of objects (so a bit hard to figure out).

First I thought that the problem was in JFormField but it really was not. It has no need to access those properties since they only "parse" the fields were not made to give any direct control.

So here is a bit of the other code I have.

if($form->loadFile($path.'/fields.xml')){
        $fieldset = $form->getFieldset();
        //SQL STUFF HERE TO GET $result
        $result = $db->loadObject();
        if(isset($result->params)){
            $moduleParams = json_decode($result->params);
        }else{
            $moduleParams = new stdClass;
        }
        foreach($fieldset as $index=>$field){
            $field->name = 'plg_form_settings['.$field->name.']';
            $content .= '<div class="control-group">';
                $content .= '<div class="control-label">';
                    $content .= $field->label;
                $content .= '</div>';
                $content .= '<div class="controls">';
                    $content .= $field->input;
                $content .= '</div>';
            $content .= '</div>';
        }
    }

This loops each field in the fieldset and returns an instance of JFormField a more or less read only class. What should be done is any edits you want to do is done via JForm itself.

if($form->loadFile($path.'/fields.xml')){
    $fieldset = $form->getFieldset();
    //SQL STUFF HERE TO GET $result
    $result = $db->loadObject();
    if(isset($result->params)){
        $moduleParams = json_decode($result->params);
    }else{
        $moduleParams = new stdClass;
    }
    $dataArray = array();
    //split the loop into 2, this way the data can be bound
    foreach($fieldset as $index=>$field){
        if($id!=0&&isset($moduleParams->{$index})){
            $dataArray[$index] = $moduleParams->{$index};
        }
    }
    //bind and reset to ensure it worked
    $form->bind($dataArray);
    $fieldset = $form->getFieldset();
    foreach($fieldset as $index=>$field){
        $output = '<div class="control-group">';
            $output .= '<div class="control-label">';
                $output .= $field->label;
            $output .= '</div>';
            $output .= '<div class="controls">';
                $output .= $field->input;
            $output .= '</div>';
        $output .= '</div>';
        $content .= $output;
    }
}

As you can see I use 2 loops, one is just to match a parameter to a field (I could probably improve it by looping the actual $moduleParams object). While the other is the same as the one I had before. In between I bind the data to form and "reset" the fieldset variable (not sure if its needed but it does not hurt in the debugging process. This however will only correctly change the $field->value as you are binding a forms value, so the other variables are still protected.

So I came up with a solution for name, which this question was mainly about. JForm is fairly strict on the name as it uses that as a basis for its interaction with other objects, so its best not to touch it while JForm is parsing the form, but after.



*/
///JForm has no render method as used in images.html.php line  343

    html_rsg2_galleries::edit( $row, $lists, $params, $option );
}


/**
 * Saves the record on an edit form submit
 * @param string $option
 * @throws Exception
 */
function save( $option ) {
    global $rsgOption, $rsgConfig;
	$mainframe = JFactory::getApplication();

	$my = JFactory::getUser();
	$database = JFactory::getDBO();

	$input =JFactory::getApplication()->input;
	//$task = JRequest::getCmd('task');
	$task = $input->get( 'task', '', 'CMD');		
	//$id = JRequest::getInt('id');
	$id = $input->get( 'id', 0, 'INT');					
	
    $row = new rsgGalleriesItem( $database );
	$row->load($id);
    //if (!$row->bind( JRequest::get('post') )) {	//here we get id, parent, ... from the user's input
    //if (!$row->bind( $input->post)) {	//here we get id, parent, ... from the user's input
    // ToDo: Revisit a) check if $input->post->getArray(); is proper replacement for above b) Remove debug code below
    //$OrgOldPost = JRequest::get('post');
    //$Test7Post = $input->post->getArray();
    if (!$row->bind( $input->post->getArray() )) {	//here we get id, parent, ... from the user's input
        echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
        exit();
    }

	//$row->description = JRequest::getVar( 'description', '', 'post', 'string', JREQUEST_ALLOWRAW );
	$input =JFactory::getApplication()->input;
	$row->description = $input->post->get('description', '', 'RAW');
	
	//Make the alias for SEF
	if(empty($row->alias)) {
            $row->alias = $row->name;
    }
    $row->alias = JFilterOutput::stringURLSafe($row->alias);
	
    // save params
    //$params = JRequest::getVar( 'params', array() );
	$input =JFactory::getApplication()->input;
	$params = $input->get( 'params', array(), 'ARRAY');		
    if (is_array( $params )) {
        $txt = array();
        foreach ( $params as $k=>$v) {
            $txt[] = "$k=$v";
        }
        $row->params = implode( "\n", $txt );
    }

	// Get the rules which are in the form … with the name 'rules' with type array (default value array())
	//$data['rules']	= JRequest::getVar('rules', array(), 'post', 'array');
	$input =JFactory::getApplication()->input;
	$data['rules']		= $input->post->get( 'rules', array(), 'ARRAY');
	
	//Only save rules when there are rules (which were only shown to those with core.admin)
	if (!empty($data['rules'])) {
		// Get the form library, add a path for the form XML and get the form instantiated
		jimport( 'joomla.form.form' );
		JForm::addFormPath(JPATH_ADMINISTRATOR.'/components/com_rsgallery2/models/forms/');
		$form = JForm::getInstance('com_rsgallery2.params','gallery',array( 'load_data' => false ));
		// Filter $data which means that for $data['rules'] the Null values are removed
		$data = $form->filter($data);
		if (isset($data['rules']) && is_array($data['rules'])) {
			// Instantiate a JAccessRules object with the rules posted in the form
			jimport( 'joomla.access.rules' );
			$rules = new JAccessRules($data['rules']);
			// $row is an rsgGalleriesItem object that extends JTable with method setRules
			// this binds the JAccessRules object to $row->_rules
			$row->setRules($rules);
		}
	}
   	
	// code cleaner for xhtml transitional compliance 
	$row->description = str_replace( '<br>', '<br />', $row->description );

    $row->date = date( 'Y-m-d H:i:s' );
    if (!$row->check()) {
        echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
        exit();
    }
    if (!$row->store()) {
        echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
        exit();
    }
    $row->checkin();
    $row->reorder( );
    
	//Redirect based on save or apply task
	if ($task == 'save') {
		$mainframe->redirect( "index.php?option=$option&rsgOption=$rsgOption" );
	} else { //apply
		$mainframe->redirect("index.php?option=$option&rsgOption=$rsgOption&task=editA&hidemainmenu=1&id=$row->id");
	}
}


/**
 * Deletes one or more records
 * @param array $cid An array of unique category id numbers
 * @param string $option The current url option
 */
function removeWarn( $cid, $option ) {
    if (!is_array( $cid ) || count( $cid ) < 1) {
        echo "<script> alert('Select an item to delete'); window.history.go(-1);</script>\n";
        exit;
    }

    $galleries = rsgGalleryManager::getArray( $cid );

    html_rsg2_galleries::removeWarn( $galleries );
}

/**
 * Deletes one or more records
 * @param int $cid array An array of unique category id numbers
 * @param string $option The current url option
 * @throws Exception
 */
function removeReal( $cid, $option ) {
	global $rsgOption, $rsgConfig;
	$mainframe =& JFactory::getApplication();

    $result = rsgGalleryManager::deleteArray( $cid );

	$mainframe->redirect( "index.php?option=$option&rsgOption=$rsgOption" );
}

/**
 * Publishes or Unpublishes one or more records
 * @param array int $cid An array of unique category id numbers
 * @param int $publish 0 if unpublishing, 1 if publishing
 * @param string $option The current url option
 * @throws Exception
 */

function publish( $cid=null, $publish=1,  $option ) {
	global $rsgOption;
	$mainframe =& JFactory::getApplication();
	$database = JFactory::getDBO();
	$my =& JFactory::getUser();

	// 140503 $catid not used
    // $catid = JRequest::getInt( 'catid', array(0) );

    if (!is_array( $cid ) || count( $cid ) < 1) {
        $action = $publish ? 'publish' : 'unpublish';
        echo "<script> alert('Select an item to $action'); window.history.go(-1);</script>\n";
        exit;
    }

    $cids = implode( ',', $cid );

    $query = "UPDATE #__rsgallery2_galleries"
    . "\n SET published = " . intval( $publish )
    . "\n WHERE id IN ( $cids )"
    . "\n AND ( checked_out = 0 OR ( checked_out = $my->id ) )"
    ;
    $database->setQuery( $query );
    if (!$database->execute()) {
        echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
        exit();
    }

    if (count( $cid ) == 1) {
        $row = new rsgGalleriesItem( $database );
        $row->checkin( $cid[0] );
    }
    $mainframe->redirect( "index.php?option=$option&rsgOption=$rsgOption" );
}
/**
* Moves the order of a record
 * @param $uid
 * @param int $inc The increment to reorder by
 * @param string $option
 * @throws Exception
 */
function order( $uid, $inc, $option ) {
	global $rsgOption;
	$mainframe =& JFactory::getApplication();
	$database = JFactory::getDBO();
	
	$row = new rsgGalleriesItem( $database );
    $row->load( $uid );
	$row->move( $inc, "parent = $row->parent" );//2nd arg: restrict to set with same parent

    $mainframe->redirect( "index.php?option=$option&rsgOption=$rsgOption" );
}

/**
* Cancels an edit operation
* @param string $option The current url option
 */
function cancel( $option ) {
	global $rsgOption;
	$mainframe =& JFactory::getApplication();
	$database = JFactory::getDBO();

	$row = new rsgGalleriesItem( $database );
    //$row->bind( $_POST );
	$input =JFactory::getApplication()->input;
    // ToDo: Revisit check if $input->post->getArray(); is proper replacement for above
    $row->bind( $input->post->getArray() );

	$row->checkin();
    $mainframe->redirect( "index.php?option=$option&rsgOption=$rsgOption" );
}

/**
 * @param $cid
 * @throws Exception
 */
function saveOrder( &$cid ) {
	$mainframe =& JFactory::getApplication();
	$database = JFactory::getDBO();

	$total		= count( $cid );
	// $order 		= JRequest::getVar( 'order', array(0), 'post', 'array' );
	$input =JFactory::getApplication()->input;
	$order = $input->post->get( 'order', array(), 'ARRAY');
	JArrayHelper::toInteger($order, array(0));

	$row 		= new rsgGalleriesItem( $database );
	
	$conditions = array();

	// update ordering values
	for ( $i=0; $i < $total; $i++ ) {
		$row->load( (int) $cid[$i] );
		$groupings[] = $row->parent;
		if ($row->ordering != $order[$i]) {
			$row->ordering = $order[$i];
			if (!$row->store()) {
				JError::raiseError(500, $mainframe->getErrorMsg());
			} // if
		} // if
	} // for

	// reorder each group
	$groupings = array_unique( $groupings );
	foreach ( $groupings as $group ) {
		$row->reorder('parent = '.$database->Quote($group));
	} // foreach

	// clean any existing cache files
	$cache =& JFactory::getCache('com_rsgallery2');
	$cache->clean( 'com_rsgallery2' );

	$msg 	= JText::_( 'COM_RSGALLERY2_NEW_ORDERING_SAVED' );
	$mainframe->enqueueMessage( $msg );
	$mainframe->redirect( 'index.php?option=com_rsgallery2&rsgOption=galleries');
} // saveOrder
?>