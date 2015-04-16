<?php
/**
* This file contains xxxxxxxxxxxxxxxxxxxxxxxxxxx.
* @version xxx
* @package RSGallery2
* @copyright (C) 2003 - 2006 RSGallery2
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* RSGallery is Free Software
*/

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

global $rsgOptions_path;
require_once( $rsgOptions_path . 'search.html.php' );

$input =JFactory::getApplication()->input;
//$cid = JRequest::getVar( 'cid' , array(), 'default', 'array' );
$cid = $input->get( 'cid', array(), 'ARRAY');
//$task = JRequest::getCmd( 'task', null);
$task = $input->get( 'task', '', 'CMD');

//Load stylesheet from current template
global  $rsgConfig;
$template_dir = JURI_SITE . "/components/com_rsgallery2/templates/" . $rsgConfig->get('template');
$doc = JFactory::getDocument();
$doc->addStyleSheet($template_dir."/css/template.css","text/css");

switch ($task) {
	case 'showResults':
		showResults();
		break;
}

function showResults() {
	$database = JFactory::getDBO();
	//Retrieve search string
	//$searchtext = JRequest::getVar( 'searchtext'  , '');
	$searchtext = $input->get( 'searchtext', '', 'STRING');

	//Check searchtext against database
	// See http://docs.joomla.org/Secure_coding_guidelines. Example given (2012 June 23):
	/* Special attention should be paid to LIKE clauses which contain the % wild card
		character as these require special escaping in order to avoid possible denial of
		service attacks. LIKE clauses can be handled like this:
		Construct the search term by escaping the user-supplied string and, if
		required, adding the % wild card characters manually.
			$search = '%' . $db->escape( $search, true ) . '%' );
		Construct the SQL query, being careful to suppress the default behaviour of
		Quote so as to prevent double-escaping.
			$query = 'SELECT * FROM #__table WHERE `field` LIKE ' . $db->quote( $search, false );
	*/
	$escapedSearchtext = '%' . $database->escape( $searchtext, true) . '%';
	$safeSearchtext = $database->quote( $escapedSearchtext, false);
	$sql = 'SELECT *, a.name as itemname, a.id as item_id FROM #__rsgallery2_files as a, #__rsgallery2_galleries as b ' .
			' WHERE a.gallery_id = b.id ' .
			' AND (' .
			' a.title LIKE '. $safeSearchtext .' OR ' .	
			' a.descr LIKE '. $safeSearchtext .
			' ) ' .
			' AND a.published = 1 ' .
			' AND b.published = 1 ' .
			' GROUP BY a.id ' .
			' ORDER BY a.id DESC ';
	$database->setQuery($sql);
	$result = $database->loadObjectList();
	
	//show results
	html_rsg2_search::showResults($result, $searchtext);		
}


function showExtendedSearch() {
	echo "Extended search possibilities later!";
}
?>