<?php
/**
* category class
* @version $Id: galleries.class.php 1049 2011-11-08 13:57:16Z mirjam $
* @package RSGallery2
* @copyright (C) 2005 - 2011 RSGallery2
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* RSGallery2 is Free Software
**/

// no direct access
defined( '_JEXEC' ) or die();

/**
* Category database table class
* @package RSGallery2
* @author Jonah Braun <Jonah@WhaleHosting.ca>
*/
class rsgGalleriesItem extends JTable {
    /** @var int Primary key */
    var $id = null;
	/** @var int */
    var $parent = 0;
	/** @var string */
    var $name = null;
	/** @var string */
	var $alias = null;
	/** @var string */
    var $description = null;
	/** @var boolean */
    var $published = null;
	/** @var boolean */
    var $checked_out        = null;
	/** @var time */
    var $checked_out_time   = null;
	/** @var boolean */
    var $ordering = null;
	/** @var int */
    var $hits = null;
	/** @var datetime */
    var $date = null;
	/** @var string in form text;value; ... */
    var $params = null;
	/** @var int */
    var $user = null;
	/** @var int */
    var $uid = null;
	/** @var boolean */
    var $allowed = null;
	/** @var int */
    var $thumb_id = null;
	/** @var int */
	var $asset_id = null;
	/** @var int */
	var $access = null;

    /**
    * @param database $db  A database connector object
    */
    function rsgGalleriesItem( &$db ) {
		parent::__construct('#__rsgallery2_galleries', 'id', $db );
    }
    /** 
     * overloaded check function 
     * @return bool
     */
    function check() {
		$db = JFactory::getDBO();
        // filter malicious code
        $ignoreList = array( 'params','description' );
		
		$ignore = is_array( $ignoreList );
		
		$filter = & JFilterInput::getInstance();
		foreach ($this->getProperties() as $k => $v)
		{
			if ($ignore && in_array( $k, $ignoreList ) ) {
				continue;
			}
			$this->$k = $filter->clean( $this->$k );
		}
		
        /** check for valid name */
        if (trim( $this->name ) == '') {
            //$this->_errors = JText::_('COM_RSGALLERY2_GALLERY_NAME');
            $this->setError (JText::_('COM_RSGALLERY2_GALLERY_NAME'));
            return false;
        }

        /** check for existing name */
        $query = "SELECT id"
        . " FROM #__rsgallery2_galleries"
        . " WHERE name = ".$db->quote($this->name)
        . " AND parent = ".(int) $this->parent
        ;
        $this->_db->setQuery( $query );

        $xid = intval( $this->_db->loadResult() );
        if ($xid && $xid != intval( $this->id )) {
            $this->setError (JText::_('COM_RSGALLERY2_THERE_IS_A_GALLERY_ALREADY_WITH_THAT_NAME_PLEASE_TRY_AGAIN'));
            return false;
        }
        return true;
    }
	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form `com_rsgallery2.gallery.id`
	 * where id is the value of the primary key of the table.
	 *
	 * @return      string
	 */
	protected function _getAssetName() {
		$k = $this->_tbl_key;
		return 'com_rsgallery2.gallery.'.(int) $this->$k;
	}
	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return      string
	 */
	protected function _getAssetTitle() {
		return $this->name;
	}
	/**
	 * Get the parent asset id for the gallery
	 *
     * @param JTable $table
     * @param null $id
     * @return int|null
     */
	protected function _getAssetParentId(JTable $table = null, $id = null) {
		// Initialise variables
		$assetId = null;
		$db		= $this->getDbo();	//$this is the rsgGalleriesItem object

		//If the current gallery has the Top gallery as a parent, the parent asset is the one of the extension
		if ($this->parent == 0){
			$asset = JTable::getInstance('Asset');
			$asset->loadByName('com_rsgallery2');
			$assetId = $asset->id;
		}
		//else the parent asset is the asset of the parent gallery 
		else {
			// Build the query to get the asset id for the parent category.
			$query	= $db->getQuery(true);
			$query->select('asset_id');
			$query->from('#__rsgallery2_galleries');
			$query->where('id = '.(int) $this->parent);

			// Get the asset id from the database.
			$db->setQuery($query);
			if ($result = $db->loadResult()) {
				$assetId = (int) $result;
			}
		}
	
		// Return the asset id.
		if ($assetId) {
			return $assetId;
		} else {
			return parent::_getAssetParentId($table, $id);
		}
	}
}

/**
 * build the select list for parent item
 * ripped from joomla.php: mosAdminMenus::Parent()
 * @param $row current gallery
 * @return string HTML Selectlist
 */
function galleryParentSelectList( &$row ) {
    $database = JFactory::getDBO();

    $id = '';
    if ( $row->id ) {
        $id = " AND id != $row->id";
    }

    // Get a list of the items
    // [J!1.6 has parent_id instead of parent and title instead of name in menu.treerecurse]
    $query = "SELECT *, parent AS parent_id, name AS title"	
    . " FROM #__rsgallery2_galleries"
    . " WHERE published != -2"
    . $id
    . " ORDER BY parent, ordering"
    ;
    $database->setQuery( $query );
    
    $mitems = $database->loadObjectList();

    // establish the hierarchy of the menu
    $children = array();

    if ( $mitems ) {
        // first pass - collect children
        foreach ( $mitems as $v ) {
            $pt     = $v->parent;
            $list   = @$children[$pt] ? $children[$pt] : array();
            array_push( $list, $v );
            $children[$pt] = $list;
        }
    }

    // second pass - get an indent list of the items
    $list = JHtml::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0 );

    // assemble menu items to the array
    $mitems     = array();
	//Only add Top gallery as a choice is galleries may be created there or if the current parent is the Top gallery
	if ((JFactory::getUser()->authorise('core.create', 'com_rsgallery2')) OR ($row->parent == 0)) {
		$mitems[]   = JHtml::_('select.option', '0', JText::_('COM_RSGALLERY2_TOP_GALLERY') );
	}

    foreach ( $list as $item ) {
		//[hack] [the original treename holds &#160; as a non breacking space for subgalleries, but JHtmlSelect::option cannot handle that, nor &nbsp;] 
    	$item->treename = str_replace  ( '&#160;&#160;'  ,  '...' ,  $item->treename  ); 
		//Check create permission for each possible parent
		$canCreateInParentGallery = JFactory::getUser()->authorise('core.create', 'com_rsgallery2.gallery.'.$item->id);
		//Get the allowed parents and the current parent
		if (($canCreateInParentGallery) OR ($row->parent == $item->id)) {
			$mitems[] = JHtml::_('select.option', $item->id, '...'. $item->treename);
		}
    }
    
	//genericlist(array of objects, value of HMTL name attribute, additional HTML attributes for <select> tag, name of objectvarialbe for the option value, name of objectvariable for option text, key that is selected,???,???)
    $output = JHtml::_("select.genericlist", $mitems, 'parent', 'class="inputbox" size="10"', 'value', 'text', $row->parent );

    return $output;
}
/* ACL functions from here */
/**
 * Returns an array with the gallery ID's from the children of the parent
 * @param int $gallery_id Gallery ID from the parent ID to check
 * @return array Array with Gallery ID's from children
 */
function subList( $gallery_id ) {
	$database = JFactory::getDBO();
	$sql = "SELECT id FROM #__rsgallery2_galleries WHERE parent = '$gallery_id'";
	$database->setQuery( $sql );
	$result = $database->loadColumn();
	if (count($result) > 0)
		return result;
	else
		return 0;
}
?>