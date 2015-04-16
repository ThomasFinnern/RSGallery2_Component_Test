<?php
/**
* This file contains Voting in RSG2
* @version $Id: rsgvoting.php 1085 2012-06-24 13:44:29Z mirjam $
* @package RSGallery2
* @copyright (C) 2003 - 2012 RSGallery2
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* RSGallery is Free Software
*/
defined( '_JEXEC' ) or die();

require_once( JPATH_RSGALLERY2_SITE . DS . 'lib' . DS . 'rsgvoting' . DS . 'rsgvoting.class.php' );

$input =JFactory::getApplication()->input;
// 140503 $cid not used ?
//$cid   = JRequest::getInt('cid', array(0) );
$cid = $input->get( 'cid', 0, 'INT');	
//$task  = JRequest::getCmd('task', '' );
$task = $input->get( 'task', '', 'CMD');		
//$id    = JRequest::getInt('id','' );
$id = $input->get( 'id', 0, 'INT');	

switch( $task ){
    case 'save':
        saveVote( $option );
        break;
}

function test( $id ) {
		echo "<pre>";
		print_r($_COOKIE);
		echo "</pre>";
		$cookie_prefix = strval("rsgvoting_".$id);
		echo $cookie_prefix;
	if (!isset($_COOKIE[$cookie_prefix])) {
		//Cookie valid for 1 year!
		setcookie($cookie_prefix ,$id ,time()+60*60*24*365, "/");
	}

}
function saveVote( $option ) {
	global $rsgConfig;
	$mainframe = JFactory::getApplication();
	$database = JFactory::getDBO();
	$my = JFactory::getUser();
	$input =JFactory::getApplication()->input;
	//$Itemid 	= JRequest::getInt('Itemid', '');	
	$Itemid = $input->get( 'Itemid', 0, 'INT');		
	//$rating 	= JRequest::getInt('rating', '');
	$rating = $input->get( 'rating', 0, 'INT');		
	//$id 		= JRequest::getInt('id', '');
	$id = $input->get( 'id', 0, 'INT');		
	$vote 		= new rsgVoting();

	//Check if user can vote
	if (!$vote->voteAllowed()) {
		$mainframe->redirect(JRoute::_("index.php?option=com_rsgallery2&Itemid=$Itemid&page=inline&id=$id", false), JText::_('COM_RSGALLERY2_YOU_ARE_NOT_AUTHORIZED_TO_VOTE'));
	}
	
	//Check if user has already voted for this image
	if ($vote->alreadyVoted( (int) $id)) {
		$mainframe->redirect(JRoute::_("index.php?option=com_rsgallery2&Itemid=$Itemid&page=inline&id=$id", false), JText::_('COM_RSGALLERY2_YOU_ALREADY_VOTED_FOR_THIS_ITEM'));
	}
	
	//All checks OK, store vote in DB
	$total 		= $vote->getTotal($id ) + $rating;
	$votecount 	= $vote->getVoteCount( $id ) + 1;
	
	$sql = 'UPDATE `#__rsgallery2_files` SET `rating` = '. (int) $total .', `votes` = '. (int) $votecount .' WHERE `id` = '. (int) $id;
	$database->setQuery( $sql );
	if ( !$database->execute() ) {
		$msg = JText::_('COM_RSGALLERY2_VOTE_COULD_NOT_BE_ADDED_TO_THE_DATABASE');
	} else {
		$msg = JText::_('COM_RSGALLERY2_VOTE_ADDED_TO_DATABASE');
		//Store cookie on system
		setcookie($rsgConfig->get('cookie_prefix').$id, $my->id, time()+60*60*24*365, "/");
	}
	$mainframe->redirect(JRoute::_("index.php?option=com_rsgallery2&Itemid=$Itemid&page=inline&id=$id", false), $msg);
}
?>