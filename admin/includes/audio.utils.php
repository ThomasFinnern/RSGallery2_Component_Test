<?php
/**
* This file handles image manipulation functions RSGallery2
* @version $Id: audio.utils.php 1085 2012-06-24 13:44:29Z mirjam $
* @package RSGallery2
* @copyright (C) 2005 - 2010 RSGallery2
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* RSGallery2 is Free Software
*/

defined( '_JEXEC' ) or die();

require_once( $rsgClasses_path . 'file.utils.php' );

/**
* Image utilities class
* @package RSGallery2
* @author Jonah Braun <Jonah@WhaleHosting.ca>
*/
class audioUtils extends fileUtils{
    static function allowedFileTypes(){
        return array('mp3');
    }

    /**
     * Takes an image file, moves the file and adds database entry
     * @param the verified REAL name of the local file including path
     * @param name of file according to user/browser or just the name excluding path
     * @param desired category
     * @param title of image, if empty will be created from $name
     * @param description of image, if empty will remain empty
     * @return returns true if successfull otherwise returns an ImageUploadError
     */
    static function importImage($tmpName, $name, $cat, $title='', $desc='') {
        global $rsgConfig;
		$database = JFactory::getDBO();
		$my = JFactory::getUser();

        $destination = fileUtils::move_uploadedFile_to_orignalDir( $tmpName, $name );
        
        if( is_a( $destination, imageUploadError ) )
            return $destination;

        $parts = pathinfo( $destination );
        $newName = $parts['basename'];

        // fill $title if empty
        if( $title == '' ) 
            $title = substr( $parts['basename'], 0, -( strlen( $parts['extension'] ) + ( $parts['extension'] == '' ? 0 : 1 )));

        // determine ordering
		$query = 'SELECT COUNT(1) FROM `#__rsgallery2_files` WHERE `gallery_id` = '. (int) $cat;
        $database->setQuery($query);
        $ordering = $database->loadResult() + 1;
        
        //Store image details in database
		$alias = $database->quote(JFilterOutput::stringURLSafe($title));
        $desc = $database->quote($desc);
        $title = $database->quote($title);
		$newName = $database->quote($newName);
		$dateNow = $database->quote(date('Y-m-d H:i:s'));
		$query = 'INSERT INTO `#__rsgallery2_files` '.
                ' (`title`, `name`, `descr`, `gallery_id`, `date`, `ordering`, `userid`, `alias`) VALUES '.
                ' ('.$title.', '.$newName.', '.$desc.', '. (int) $cat.', '.$dateNow.', '. (int) $ordering.', '. (int) $my->id.', '.$alias.')';
        $database->setQuery($query);
        
        if (!$database->execute()){
			audioUtils::deleteAudio( $parts['basename'] );
            return new imageUploadError( $parts['basename'], $database->stderr(true) );
        }

        return true;
    }
    
    static function getAudio($name, $local=false){
        global  $rsgConfig;
        
        $locale = $local? JPATH_ROOT : JURI_SITE;
        
        // if thumb image exists return that, otherwise the original image width <= $thumb_width so we return the original image instead.
        if( file_exists( JPATH_ROOT.$rsgConfig->get('imgPath_original') . '/' . audioUtils::getAudioName( $name ))){
            return $locale  . $rsgConfig->get('imgPath_original') . '/' . audioUtils::getAudioName( $name );
        }else {
            return;
        }
    }
    
    /**
    * deletes all elements of image on disk and in database
    * @param string name of image
    * @return true if success or notice and false if error
    */
	function deleteAudio($name){
        global $rsgConfig;
        $database = JFactory::getDBO();
		
        $original   = JPATH_ORIGINAL . DS . $name;
        
        if( file_exists( $original )){
            if( !unlink( $original )){
				JError::raiseNotice('ERROR_CODE', JText::_('COM_RSGALLERY2_ERROR_DELETING_ORIGINAL_IMAGE').": ".$original);
				return false;
			}
		}
		$query = 'SELECT `gallery_id` FROM `#__rsgallery2_files` WHERE `name` = '. $database->quote($name);
        $database->setQuery($query);
        $gallery_id = $database->loadResult();

		$query = 'DELETE FROM `#__rsgallery2_files` WHERE `name` = '. $database->quote($name);
        $database->setQuery($query);
        if( !$database->execute()){
            JError::raiseNotice('ERROR_CODE', JText::_('COM_RSGALLERY2_ERROR_DELETING_DATABASE_ENTRY_FOR_IMAGE').": ".$name);
			return false;
		}
		
        galleryUtils::reorderRSGallery('`#__rsgallery2_files`', '`gallery_id` = '. (int) $gallery_id);
        
        return true;
    }
      
    static function getAudioName($name){
        return $name;
    }
}