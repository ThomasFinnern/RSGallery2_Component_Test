<?php
/**
* This file contains the class representing a gallery.
* @version $Id: gallery.class.php 1085 2012-06-24 13:44:29Z mirjam $
* @package RSGallery2
* @copyright (C) 2005 - 2012 RSGallery2
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* RSGallery2 is Free Software
*/
defined( '_JEXEC' ) or die();

/**
* Class representing a gallery.
* Don't access variables directly, use get(), kids() or items()
* @package RSGallery2
* @author Jonah Braun <Jonah@WhaleHosting.ca>
*/
class rsgGallery extends JObject{
//     variables from the db table
	/** @var array the entire table row */
	var $row = null; /* rsgGallery parameters as associatian array */
	
	/** @var int Primary key */
	var $id = null;
	/** @var int id of parent */
	var $parent = null;
	/** @var string name of gallery*/
	var $name = null;
	/** @var string alias of gallery*/
	var $alias = null;
	/** @var string */
	var $description = null;
	/** @var boolean */
	var $published = null;
	/** @var int */
	var $checked_out        = null;
	/** @var datetime */
	var $checked_out_time   = null;
	/** @var int */
	var $ordering = null;
	/** @var datetime */
	var $date = null;
	/** @var int */
	var $hits = null;
	/** @var string */
	var $params = null;
	/** @var int */
	var $user = null;
	/** @var int */
	var $uid = null;
	/** @var string */
	var $allowed = null;
	/** @var int */
	var $thumb_id = null;
	/** @var int */
	var $asset_id = null;
	/** @var int */
	var $access = null;

//     variables for sub galleries and image items
	/** @var array representing child galleries.  generated on demand!  use kids() */
	var $kids = null;
	/** @var array representing images.  generated on demand!  use itemRows() */
	var $_itemRows = null;
	/** @var array representing images.  generated on demand!  use items() */
	var $items = null;

//     misc other generated variables
	/** @var the thumbnail object representing the gallery.  generated on demand!  use thumb() */
	var $thumb = null;
	/** @var string containing the html image code */
	var $thumbHTML = null;
	/** @var url to go to this gallery from the frontend */
	var $url = null;
	var $status = null;

	var $_itemCount = null;

    /**
     * @param mixed|null $row (rsgGallery parameters as associatian array)
     */
    function __construct( $row ){
		$this->row = $row;

		// bind db row to this object
		foreach ( $row as $k=>$v ){
			$this->$k = $row[$k];
		}

		$this->params = $this->explode_assoc("=", "\n", $this->params);
		
		$this->thumb();

		//Write status icons
		$this->status = galleryUtils::writeGalleryStatus( $this );
		//Write owner name
		$this->owner = galleryUtils::genericGetUserName( $this->get('uid') );

		//Write gallery name
		$this->url = JRoute::_("index.php?option=com_rsgallery2&gid=".$this->get('id'));
		$this->galleryName = htmlspecialchars( stripslashes( $this->get( 'name' )));
		
		//Write HTML for thumbnail
		$this->thumbHTML = "<div class=\"img-shadow\"><a href=\"".$this->url."\">".galleryUtils::getThumb( $this->get('id'),0,0,"" )."</a></div>";
		
		//Write description
		jimport('joomla.filter.output');
		$this->description = JFilterOutput::ampReplace($this->get('description'));
	}
	
	/**
     *
	 * @param int  $days amount of days to the past
     * @return true if there is new images within the given time span
	 * @todo rewrite the sql to use better date features
	 */
	function hasNewImages($days = 7){
		$database = JFactory::getDBO();
		$lastweek  = mktime (0, 0, 0, date("m"),    date("d") - $days, date("Y"));
		$lastweek = date("Y-m-d H:m:s",$lastweek);
		$query = 'SELECT * FROM `#__rsgallery2_files` WHERE `date` >= '. $database->quote($lastweek). ' AND `gallery_id` = '. (int) $this->id .' AND `published` = 1';
		$database->setQuery($query);
		$database->execute();

		return (bool) $database->getNumRows();
	}
	
	/**
     * returns the total number of items in this gallery.
     * @return int
     */
	function itemCount(){
		if( $this->_itemCount === null ){
			$db = JFactory::getDBO();
			
			$gid = $this->id;

			$query = $db->getQuery(true);
			$query->select('count(1)');
			$query->from('#__rsgallery2_files');
			$query->where('gallery_id='. (int) $gid);
			// Only for superadministrators this includes the unpublished items
			if (!JFactory::getUser()->authorise('core.admin','com_rsgallery2'))
				$query->where('published = 1');
			$db->setQuery($query);
			
			$this->_itemCount = $db->loadResult();
		}
		return $this->_itemCount;
	}
	
	/**
     * returns an array of sub galleries in this gallery
     * @return array rsgGallery|bool
     */
	function kids(){
		// check if we need to generate the list
		if( $this->kids == null ){
			$this->kids = rsgGalleryManager::getList( $this->get('id') );
		}
		
		return $this->kids;
	}
	
	/**
	 * returns the parent gallery item.
     * @return null|rsgGallery
     */
	function parent(){
		return rsgGalleryManager::get( $this->parent );
	}
	
	/**
	*  returns an array of item db rows
	* @todo image listing should be based on what the current visitor can see (owner, administrator, un/published, etc.)
     * @return array rsgGallery|mixed
     * @throws Exception
     */
	function itemRows( ){
		
		if( $this->_itemRows === null ){

			global $rsgConfig;
			$my = JFactory::getUser();
			$database = JFactory::getDBO();
		
			//$filter_order = JRequest::getWord( 'filter_order',  $rsgConfig->get("filter_order") );
			$input =JFactory::getApplication()->input;
			$filter_order = $input->get( 'filter_order',  $rsgConfig->get("filter_order"), 'WORD');					
			//$filter_order_Dir = JRequest::getWord( 'filter_order_Dir', $rsgConfig->get("filter_order_Dir"));
			$filter_order_Dir = $input->get( 'filter_order_Dir',  $rsgConfig->get("filter_order_Dir"), 'WORD');					
	
			$where = ' WHERE `gallery_id` = '. (int) $this->get('id');

			//Show only published items except for users with core.admin
			//MK// [todo] Show user with core.admin that which item is unpublished
			if (!JFactory::getUser()->authorise('core.admin','com_rsgallery2'))
				$where .= ' AND `published` = 1 ';
			
			$orderby 	= ' ORDER BY `'.$filter_order.'` '.$filter_order_Dir;
	
			$query = ' SELECT * FROM `#__rsgallery2_files` '
				. $where
				. $orderby;

			// limit handling was borked but I had this fixed.  we can use it again....
			$database->setQuery( $query);//, $limitstart, $limit );

			$this->_itemRows = $database->loadAssocList( 'id' );
		}
		return $this->_itemRows;
	}

	/**
	*  returns an array of all item objects (imagess) n? strings ?
     * @return array rsgGallery
     */
	function items( ){
		if( $this->items === null ){
			$this->items = array();
			$rows = $this->itemRows( );

			foreach( $rows as $row ){
				$this->items[$row['id']] = rsgItem::getCorrectItemObject( $this, $row );
			}
		}
		return $this->items;
	}


	/**
	* returns an array of item objects viewable with the current pagination
     * @return array rsgGallery
     * @throws Exception
     */
	function currentItems(){
		global $rsgConfig;
		if( $this->items === null )
			$this->items();
		
		$length = $rsgConfig->get("display_thumbs_maxPerPage");
		if( $length == 0 )
			return $this->items; // 0 means display all

		$input =JFactory::getApplication()->input;
		//$current = $this->indexOfItem(JRequest::getInt( 'id', 0 ));
		$current = $input->get( 'id', 0, 'INT');		
		//$current = JRequest::getInt( 'limitstart', $current );
		$current = $input->get( 'limitstart', $current, 'INT');		
		
		// calculate page from current position
		$start =  floor($current  / $length) * $length;
		return $this->array_slice_preserve_keys($this->items, $start, $length);
		
	}

	/**
 	 *  returns basic information for this gallery
	 * @param string $key
     * @param string $default
     * @return mixed|null
     */
	function get( $key , $default = null){
		
		if(!isset($this->$key))
			return $default;
		else
			return $this->$key;
	}

	
	/**
	*  returns item by it's db id (images)
	*/
    /**
     * @param int $id
     * @return mixed
     * @throws Exception
     */
	function getItem( $id = null ){

		if( $this->items === null )
			$this->items();
		
		if( $id !== null )
			return $this->items[$id];

		//$id = JRequest::getInt( 'id', null );
		$input =JFactory::getApplication()->input;
		$id = $input->get( 'id', null, 'INT');		
		if( $id !== null )
			return $this->items[$id];
			
		//$id = JRequest::getInt( 'limitstart', 0 );
		$id = $input->get( 'limitstart', 0, 'INT');
        $arr = array_slice($this->items, $id, 1);
		return array_pop($arr);
	}

    /**
     * @param int $id
     * @return bool|int|mixed|string
     * @throws Exception
     */
	function indexOfItem($id = null){
	
		if( $id === null ){
			// $id = JRequest::getInt( 'id', null );
			$input =JFactory::getApplication()->input;
			$id = $input->get( 'id', null, 'INT');		
			if( $id === null ){
				return 0;
			}
		}
		
		if (!array_key_exists($id, $this->items))
			return 0;

		$keys = array_keys($this->items);
		$index = array_search($id, $keys);
		return $index;
		
	}
	
	/**
	*  returns the thumbnail representing this gallery
     * @return string | null|the thump image ? path
     */
	function thumb( ){
		// check if we need to find out what it is first
		if( $this->thumb == null ){
			if( $this->thumb_id == 0 ){
				// thumbnail not set, use random
				$items = $this->items();
				if( count( $items ) == 0 )
					return null;

				shuffle( $items );
				$this->thumb = $items[0];
			}
			else{
				$this->thumb = $this->getItem( $this->thumb_id );
			}
		}
		return $this->thumb;
	}
	
	/**
	 * increases the hit counter for this object
	 * @todo doesn't work right now
     * @return bool
     */
	function hit(){
		$query = 'UPDATE `#__rsgallery2_galleries` SET `hits` = hits + 1 WHERE `id` = '. (int) $this->id;

		$database = JFactory::getDBO();
		$database->setQuery( $query );
		
		if( !$database->execute() ) {
// 			$this->setError( $db->getErrorMsg() );
			return false;
		}
		
		$this->hits++;
	}

	/**
	 * Method to get a pagination object for the the gallery items
	 *
	 * @access public
     * @return JPagination
     * @throws Exception
     */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$input =JFactory::getApplication()->input;
			//$limitstart = JRequest::getInt( 'limitstart', 0 );
			$limitstart = $input->get( 'limitstart', 0, 'INT');					
			//$limit = JRequest::getInt( 'limit', 1 ) ;
			$limit = $input->get( 'limit', 1, 'INT');					
			$this->_pagination = new JPagination( $this->itemCount(), $limitstart, $limit);
		}

		return $this->_pagination;
	}
	
	/** get local path to gallery
	 * @param string $path_separator char to separate path with (default = DS)
	 * @return string path to gallery
	 **/
	function getPath($path_separator = DS){

		global $rsgConfig;
		
		static $path = null;
		
		// return cached path if it is available
		if ($path != null) return $path;
				
		// check if the galleries are stored in separate folders
		if ( $rsgConfig->get('gallery_folders') ){
			
			// if the gallery is in the root, return empty string
			if ($this->parent == null){
				$path = '' ; 	
			}
			else
			{
				// if gallery is a sub gallery the get the path ftrom the parent
				$parent_gallery = rsgGalleryManager::get($this->parent);
				$path = $parent_gallery->getPath($path_separator) . $path_separator . $this->id;
			} 
			
		}
		else{
			$path = $path_separator;
		}
		
		return $path;
		
	}
	
	/**
	 * array_slice with preserve_keys for every php version (taken form http://www.php.net/array_slice )
	 *
	 * @param array $array Input array
	 * @param int $offset Start offset
	 * @param int $length Length
	 * @return array
	 */
	static function array_slice_preserve_keys($array, $offset, $length = null)
	{
		// PHP >= 5.0.2 is able to do this itself
		if(version_compare(phpversion(),"5.0.2",">="))
			return(array_slice($array, $offset, $length, true));

		// prepare input variables
		$result = array();
		$i = 0;
		if($offset < 0)
			$offset = count($array) + $offset;
		if($length > 0)
			$endOffset = $offset + $length;
		else if($length < 0)
			$endOffset = count($array) + $length;
		else
			$endOffset = count($array);
	   
		// collect elements
		foreach($array as $key=>$value)
		{
			if($i >= $offset && $i < $endOffset)
				$result[$key] = $value;
			$i++;
		}
	   
		// return
		return($result);
	}

    /**
     * @param $glue1
     * @param $glue2
     * @param $array
     * @return mixed
     */
	static function explode_assoc($glue1, $glue2, $array)
	{
        //$array3 = []; // 141031 thomas
        //$array3 = (); // 141031 thomas
		//$array3 = array (); // 150316 finnern may be needed empty ?
        $array2=explode($glue2, $array);
        foreach($array2 as  $val)
        {
            $pos=strpos($val,$glue1);
            $key=substr($val,0,$pos);
            $array3[$key] =substr($val,$pos+1,strlen($val));
        }

	    return $array3;
	}

}
