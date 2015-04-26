<?php
/**
* @version $Id: instance.class.php 1088 2012-07-05 19:28:28Z mirjam $
* @package RSGallery2
* @copyright (C) 2005 - 2006 RSGallery2
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* RSGallery2 is Free Software
*/

// no direct access
defined( '_JEXEC' ) or die();

/**
 * Create the request global object
 */
$GLOBALS['_RSGINSTANCE'] = null;

/**
* Represents an instance of RSGallery2
* @package RSGallery2
* @author Jonah Braun <Jonah@WhaleHosting.ca>
*/
class rsgInstance{//as of v2.1.0 SVN 975 no longer extending JRequest
	
	/**
	 * Creates a new RSG2 instance and executes it.
	 *
	 * @static
	 * @param string $newInstance What parameters to use for the new instance.  Your options are:
	 *                            'request'	Use the request array (default).
	 * @param bool $showTemplate show a template or not.
	 */
	static function instance( $newInstance = 'request', $showTemplate = true ){
		static $instanceStack = array();
		$stacked = false;

		// if rsg2 is already instanced then push the current instance to be pop'd later
		if( $GLOBALS['_RSGINSTANCE'] ){
			$stacked = true;

			if( count( $instanceStack ) > 9 )
				JError::raiseError( '9', 'RSGallery2 instance stack exceeds 9.  Probable endless recursion, $instanceStack:<pre>'.print_r( $instanceStack,1).'</pre>' );

			// push current instance on stack
			array_push( $instanceStack, $GLOBALS['_RSGINSTANCE'] );
		}

		$GLOBALS['_RSGINSTANCE'] = $newInstance;

		if( $showTemplate ){
			// execute a frontend template based instance
			require_once( JPATH_RSGALLERY2_SITE . DS . 'main.rsgallery2.php' );
			rsgInstance::mainSwitch();
		}

		if( $stacked )
			$GLOBALS['_RSGINSTANCE'] = array_pop( $instanceStack );
	}
	
	/**
	* This is the main task switch where we decide what to do.
	 * @throws Exception
	 */
	static function mainSwitch(){
		// 140701 original: switch( JRequest::getCmd( 'rsgOption', '' )) {
		$input =JFactory::getApplication()->input;		
		$cmd = $input->get( 'rsgOption', '', 'CMD');		
		switch( $cmd ) {
			case 'rsgComments':
				require_once(JPATH_RSGALLERY2_SITE . DS . 'lib' . DS . 'rsgcomments' . DS . 'rsgcomments.php');
				break;
			case 'rsgVoting':
				require_once(JPATH_RSGALLERY2_SITE . DS . 'lib' . DS . 'rsgvoting' . DS . 'rsgvoting.php');
				break;
			case 'myGalleries':
				require_once(JPATH_RSGALLERY2_SITE . DS . 'lib' . DS . 'mygalleries' . DS . 'mygalleries.php');
				break;
			case'search':
				require_once(JPATH_RSGALLERY2_SITE . DS . 'lib' . DS . 'rsgsearch' . DS . 'search.php');
				break;
			default:
				// 140701 original: switch( JRequest::getCmd( 'task', '' ) ){
				$task = $input->get( 'task', '', 'CMD');		
				switch( $task ){
					case 'xml':
						xmlFile();
						break;
					case "downloadfile":
                        // Todo Fix: downloadFile (id) Id is missing
						downloadFile();
						break;
					default:
						// require the base class rsgDisplay
						require_once( JPATH_RSGALLERY2_SITE . DS . 'templates' . DS . 'meta' . DS . 'display.class.php' );
						// show the template
						template();
				}
		}
	}

}
