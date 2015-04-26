<?php
/**
* This file handles image manipulation functions RSGallery2
* @version $Id: file.utils.php 1088 2012-07-05 19:28:28Z mirjam $
* @package RSGallery2
* @copyright (C) 2005 - 2011 RSGallery2
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* RSGallery2 is Free Software
*/

defined( '_JEXEC' ) or die();

require_once(JPATH_RSGALLERY2_ADMIN.'/includes/mimetype.php');
//require_once(JPATH_ROOT.'/includes/PEAR/PEAR.php');				//Mirjam: no longer used since SVN 975
//require_once( $rsgClasses_path . 'file.utils.php' );

//Load Joomla filesystem class
jimport('joomla.base.tree');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.*');


/**
 * simple error class
 * built to make migration to php5 easier (hopefully)
 * @package RSGallery2
 */
class imageUploadError{
    var $filename;
    var $error;
    /**
     * Contructor for imageUploadError
     * @param string $f Filename for which the error was found
     * @param string $e Error message
     */
    function ImageUploadError($f, $e){
        $this->filename=$f;
        $this->error=$e;
    }

    /**
     * @return string
     */
    function getFilename(){
        return $this->filename;
    }

    /**
     * @return string
     */
    function getError(){
        return $this->error;
    }

    /**
     * @return string
     */
    function toString(){
        return JText::_('COM_RSGALLERY2_ERROR_IMAGE_UPLOAD') . $this->filename . ":<p> " . $this->error . "<br>";
    }
}

/**
* file utilities class, super class for specific file type handlers
* @package RSGallery2
* @author Jonah Braun <Jonah@WhaleHosting.ca>
*/
class fileUtils{
	
	/** Constructor */
	function fileUtils() {
		//$this->allowedFiles = $this->allowedFileTypes();
	}
	
	/**
	 * Retrieves the allowed file types list from the Control Panel.
	 * @return array with allowed file types
	 */
    static function allowedFileTypes(){
    	global $rsgConfig;
        $allowed = explode( ",", strtolower( $rsgConfig->get('allowedFileTypes') ) );
        return $allowed;
    }
    
    /**
     * Takes an image file, moves the file and adds database entry
     * @param $imgTmpName the verified REAL name of the local file including path
     * @param $imgName name of file according to user/browser or just the name excluding path
     * @param $imgCat desired category
     * @param string $imgTitle title of image, if empty will be created from $imgName
     * @param string $imgDesc description of image, if empty will remain empty
     * @return bool|imageUploadError|returns|string returns true if successful otherwise returns an ImageUploadError
     */
    static function importImage($imgTmpName, $imgName, $imgCat, $imgTitle='', $imgDesc='') {
        $handle = fileUtils::determineHandle( $imgName );
        
        switch( $handle ){
            case 'imgUtils':
                return imgUtils::importImage( $imgTmpName, $imgName, $imgCat, $imgTitle, $imgDesc );
            break;
            case 'videoUtils':
                return videoUtils::importImage( $imgTmpName, $imgName, $imgCat, $imgTitle, $imgDesc );
            break;
            case 'audioUtils':
                return audioUtils::importImage( $imgTmpName, $imgName, $imgCat, $imgTitle, $imgDesc );
            break;
            default:
                return new imageUploadError( $imgName, "$imgName ".JText::_('COM_RSGALLERY2_NOT_A_SUPPORTED_FILE_TYPE') );
        }
    }

    /**
     * Moves file to the original folder.
     * It checks whether a filename already exists and renames when necessary
     * 
     * @todo Check filenames against database instead of filesystem
     * @param string $tmpName Temporary upload location as provided by $_FILES['tmp_name'] or from filename array
     * @param string $name Destination location path
     * @return imageUploadError|string Path to the file where the image was saved to
     */
    static function move_uploadedFile_to_orignalDir( $tmpName, $name ){
        $parts = pathinfo( $name );
        
        // Clean filename
        $basename = JFile::makeSafe($parts['basename']);
        // make sure we don't use the old name
        unset( $parts );
        unset( $name );

		//Get extension
        $ext = JFile::getExt($basename);
        
        if ( JFile::exists( JPATH_DISPLAY . DS . $basename ) || JFile::exists( JPATH_ORIGINAL . DS . $basename )){
            $stub = substr( $basename, 0, (strlen( $ext )+1)*-1 );
    
            // if file exists, add a number, test, increment, test...  similar to what filemanagers will do
            $i=0;
            do {
                $basename=$stub . "-" . ++$i . "." . $ext;
            } while( JFile::exists( JPATH_DISPLAY . DS . $basename ) || JFile::exists( JPATH_ORIGINAL . DS . $basename ));
        }
        
        $destination = JPATH_ORIGINAL . DS . $basename;
        if ( !JFile::copy( $tmpName, $destination )) {
            if( !JFile::upload( $tmpName, $destination )){
            	return new imageUploadError( $basename, JText::_('COM_RSGALLERY2_COULD_NOT_COPY')."$tmpName ".JText::_('COM_RSGALLERY2_IMAGE_TO')." $destination" );
            }
        }

        return $destination;
    }

    /**
     * @param $filename
     * @return bool|string
     */
	static function determineHandle( $filename ){
		require_once( JPATH_RSGALLERY2_ADMIN.'/includes/audio.utils.php' );
		require_once( JPATH_RSGALLERY2_ADMIN.'/includes/video.utils.php' );
	
		$ext = strtolower(JFile::getExt($filename));
		if( in_array( $ext, imgUtils::allowedFileTypes() ))
			return 'imgUtils';
		else if( in_array( $ext, videoUtils::allowedFileTypes() ))
			return 'videoUtils';
		else if( in_array( $ext, audioUtils::allowedFileTypes() ))
			return 'audioUtils';
		else
			return false;
	}
}


/**
* Filehandling class
* @package RSGallery2
* @author Ronald Smit <webmaster@rsdev.nl>
*/
class fileHandler {
    /** @var array List of protected files */
    var $protectedFiles;
    /** @var ar ray List of allowed image formats */
    var $allowedFiles;
    /** @var array List of all used folders */
    var $usedFolders;
    /** @var string Name of dir in which files are extracted */
    var $extractDir;
    
    /** Constructor */
    function fileHandler() {
        global $rsgConfig;
        $this->protectedFiles = array('.','..','index.html','Helvetica.afm', 'original_temp.jpg', 'display_temp.jpg');
        $this->allowedFiles = array('jpg','gif','png','avi','flv','mpg');
        $this->usedFolders = array(
            JPATH_THUMB,
            JPATH_DISPLAY,
            JPATH_ORIGINAL,
            JPATH_ROOT.'/media'
            );
        $this->extractDir = "";
    }
    /**
     * Check if OS is Windows
     * @return bool
     */
    static function is_win() {
        if ( substr(PHP_OS, 0, 3) == 'WIN' )
            return true;
        else
            return false;
    }
    
    /**
     * Function returns the permissions in a 4 digit format (e.g: 0777)
     * @param string $folder full path to folder to check
     * @return string from int 4 digit folder permissions
     */
    static function getPerms($folder) {
        $perms = substr(sprintf('%o', fileperms($folder)), -4);
        return $perms;
    }
    
    /**
     * Check routine to see is all prerequisites are met to start handling the upload process
     * @return bool|string True if all is well, false if something is missing
     */
    function preHandlerCheck() {
        /* Check if media gallery exists and is writable */
        /* Check if RSGallery directories exist and are writable */
        $error = "";
        foreach ($this->usedFolders as $folder) {
            if ( file_exists($folder) ) {
                if ( is_writable($folder) )
                    continue;
                else
                    $error .= "<p>".$folder.JText::_('COM_RSGALLERY2_EXISTS_BUT_IS_NOT_WRITABLE')."</p>";
            } else {
                $error .= "<p>".$folder.' '.JText::_('COM_RSGALLERY2_DOES_NOT_EXIST')."</p>";
            }
        }
        //Error handling
        if ($error != "")
            return $error;
        else
            return true;
        }
    
    /**
     * Checks the size of an uploaded ZIP-file and checks it against the upload_max_filesize in php.ini
     * @param $zip_file array File array from form post method
     * @return boolean True if size is within the upload limit, false if not
     */
    static function checkSize($zip_file) {
        //Check if file does not exceed upload_max_filesize in php.ini
        $max_size = ini_get('upload_max_filesize')*1024 * 1024;
        $real_size = $zip_file['size'];
        if ($real_size > $max_size || $real_size == 0) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * Checks if uploaded file is a zipfile or a single file
     * @param string $filename filename
     * @return string 'zip' if zip-file, 'image' if image file, 'error' if illegal file type
     */
    function checkFileType($filename) {
        //Retrieve extension    
        $file_ext = strtolower( JFile::getExt($filename) );

        if ($file_ext == 'zip') {
            $imagetype = 'zip';
        } else {
            if ( in_array($file_ext, $this->allowedFiles) ) {
                $imagetype = 'image';
            } else {
                $imagetype = 'error';
            }
        }
        return $imagetype;
    }
    /**
     * Returns the correct imagetype
     * @param string $filename Full path to image
     * @return string
     */
    static function getImageType( $filename ) {
		if(!file_exists($filename)) { return ""; }
		
        $image = getimagesize( $filename );
		if($image == false){
			//it's not an image, but might be a video
			$info = pathinfo($filename);
			return $info['extension'];
		}
		else{
		
			$type = $image[2];
			switch ( $type ) {
				case 1:
					$imagetype = "gif";
					break;
				case 2:
					$imagetype = "jpg";
					break;
				case 3:
					$imagetype = "png";
					break;
				case 4:
					$imagetype = "swf";
					break;
				case 5:
					$imagetype = "psd";
					break;
				default:
					$imagetype = "";
	                                                            
			}
	        return $imagetype;
		}
    }
    
    /**
     * Checks the number of images against the number of images to upload.
     * @todo Check if user is Super Administrator. Limits do not count for him
	 * Does not seem to be used anywhere in 3.1.0
     * @param bool $zip
     * @param string $zip_count
     * @return bool  True if number is within boundaries, false if number exceeds maximum
     */
    static function checkMaxImages($zip = false, $zip_count = '' ) {
		global $my, $database, $rsgConfig;
        $maxImages = $rsgConfig->get('uu_maxImages');
        
        //Check if maximum number of images is exceeded
        $database->setQuery('SELECT COUNT(1) FROM `#__rsgallery2_files` WHERE `userid` = '. (int) $my->id);
        $count = $database->loadResult();
        
        if ($zip == true) {
            $total = $count + $zip_count['nb'];
            if ( $total > $maxImages ) {
                return false;
            } else {
                return true;
            }
        } else {
            if ( $count >= $maxImages ) {
                return false;
            } else {
                return true;
            }
        }
    }
    
    /**
     * Cleans out any last remains out of /media directory, except files that belong there
     * returns boolean True upon completion, false if some files remain in media
     * @param $extractDir
     */
    static function cleanMediaDir( $extractDir ) {
        $mediadir = JPATH_ROOT. DS ."media". DS. $extractDir;

        if (file_exists( $mediadir )) {
            fileHandler::deldir( JPath::clean($mediadir) );
        } else {
            echo JText::_('COM_RSGALLERY2_APPARENTLY')."<strong>$mediadir </strong>".JText::_('COM_RSGALLERY2_DOES_NOT_EXIST');
        }
    }
    
    /**
     * Deletes complete directories, including contents. 
     * Idea from Joomla installer class
     * @Deprecated, use JFolder::delete() instead
     * @param string $dir
     * @return bool
     */
    static function deldir( $dir ) {
        $current_dir = opendir( $dir );
        $old_umask = umask(0);
        while ($entryname = readdir( $current_dir )) {
            if ($entryname != '.' and $entryname != '..') {
                if (is_dir( $dir .DS. $entryname )) {
                    fileHandler::deldir( JPath::clean( $dir .DS. $entryname ) );
                } else {
                    @chmod($dir .DS. $entryname, 0777);
                    unlink( $dir .DS. $entryname );
                }
            }
        }
        umask($old_umask);
        closedir( $current_dir );
        return rmdir( $dir );
    }
	
    /**
     * Uploads archive (with original name) and extracts archive to designated folder.
     * (This function replaces handleZIP used in J!1.5 and allows for all archive formats.)
     * 
     * @param	array $archive	Archive tmp path from upload form
     * @param 	string $destination	Absolute path to destination folder, defaults to joomla /media folder
     * @return	array|bool	Array with filenames
     * @throws Exception
     */
    function extractArchive($archive, $destination = '') {
    	//This is what is given to this function: $archive = JRequest::getVar('uploadFile', null, 'FILES', 'ARRAY');

		global $rsgConfig;
    	$mainframe = JFactory::getApplication();
		$uploadError = 0;

    	//Make sure that a file was uploaded, so check that $uploadFile is an array, and verify that the upload (form) was indeed successfull.
		if (!is_array($archive)) {
			$uploadError	= 1;
			$mainframe->enqueueMessage(JText::_('COM_RSGALLERY2_NO_FILE_TO_UPLOAD_PRESENT'), 'error');
		}
		if (($archive['error']) || $archive['size'] < 1) {
			$uploadError	= 1;
			$mainframe->enqueueMessage(JText::_('COM_RSGALLERY2_ERROR_IN_UPLOAD'), 'error');
		}

		//Before extracting upload the archive to /JOOMLAROOT/images/rsgallery/tmp/ with JFile::upload(). It transfers a file from the source file path to the destination path. Filename is made safe.
		$fileDestination = JPATH_ROOT.DS.'images'.DS.'rsgallery'.DS.'tmp'.DS.JFile::makeSafe(basename($archive['name']));
		// Move uploaded file (this is truely uploading the file)
		if (!JFile::upload($archive['tmp_name'], $fileDestination)){
			$uploadError	= 1;
			$mainframe->enqueueMessage(JText::_('COM_RSGALLERY2_UNABLE_TO_TRANSFER_FILE_TO_UPLOAD_TO_SERVER').'destination: '.$fileDestination, 'error');
		}

		//If there was an upload error: return, else get ready to exctract the archive
		if ($uploadError){
			return false;			
		} else {
			$archive['tmp_name'] = $fileDestination;
		}

    	//Create unique install directory and store it for cleanup at the end.
        $tmpdir         = uniqid( 'rsginstall_' );
        $this->extractDir = $tmpdir;
        //Clean paths for archive extraction and create extractdir
    	$archivename 	= JPath::clean( $archive['tmp_name'] );
    	if (!$destination) {
            $extractdir = JPath::clean( JPATH_ROOT.DS . 'media' . DS . $tmpdir . DS );
    	} else {
            $extractdir = JPath::clean($destination . DS . $tmpdir . DS);
		}

		//Unpack archive
		jimport( 'joomla.filesystem.archive' );
		$result = JArchive::extract($archivename, $extractdir);
		if ( $result === false ) {
			//Extraction went wrong
			return false;
		} else {
			//Remove uploaded file on successfull extract
			JFile::delete($archive['tmp_name']);
		}
		
		/*
		 * Try to find the correct directory.  In case the files are inside a
		 * subdirectory detect this and set the directory to the correct path.
		 *
		 * List all the items in the directory.  If there is only one, and
		 * it is a folder, then we will set that folder to be the folder.
		 */
		
		$archivelist = array_merge(JFolder::files($extractdir, ''), JFolder::folders($extractdir, ''));

		if (count($archivelist) == 1)
		{
			if (JFolder::exists($extractdir.DS.$archivelist[0]))
			{
				$extractdir = JPath::clean($extractdir.DS.$archivelist[0]);
			}
		}
		return $archivelist;
    }
    
    /**
     * Picks up a ZIP-file from a form and extracts it to a designated directory
     * @param array $zip_file File array from form post method
     * @param string  $destination Absolute path to destination folder, defaults to Joomla /media folder
     * @return array|int with filenames
     */
    function handleZIP($zip_file, $destination = '' ) {
        global $rsgConfig;
        include_once(JPATH_ROOT.'/administrator/includes/pcl/pclzip.lib.php');
        
        $maxImages = $rsgConfig->get('uu_maxImages');
        
        //Create unique install directory
        $tmpdir         = uniqid( 'rsginstall_' );
        
        //Store dirname for cleanup at the end.
        $this->extractDir = $tmpdir;
        
        if (!$destination)
            $extractDir = JPath::clean( JPATH_ROOT.DS . 'media' . DS . $tmpdir . DS );
        else
            $extractDir = JPath::clean($destination . DS . $tmpdir . DS);

        // Create new zipfile
        $tzipfile = new PclZip($zip_file['tmp_name']);
        
        // Unzip to ftp directory, removing all path info
        $zip_list = $tzipfile->extract(  PCLZIP_OPT_PATH, $extractDir, PCLZIP_OPT_REMOVE_ALL_PATH);

        $list[] = array (); // = [];

        // Create image array from $ziplist
        $ziplist = JFolder::files( $extractDir );
        foreach($ziplist as $file) {
            if ( is_dir($extractDir . $file) ) {
                continue;
            } else {
                if ( !in_array( fileHandler::getImageType($extractDir . $file), $this->allowedFiles ) ) {
                    continue;
                } else {
                    $list[] = $file;
                }
            }
        }
        
        if ($zip_list == 0){
            return 0;
            // die ("- Error message :".$tzipfile->errorInfo(true));
        } else {
            return $list;
        }
    }
    
    /**
     * Copies all files from a folder to the /media folder.
     * It will NOT delete the media from the FTP-location
     * @param string $source Absolute path to the sourcefolder
     * @param string $destination Absolute path to destination folder, defaults to Joomla /media folder
     * @return array
     * @throws Exception
     */
    function handleFTP($source, $destination = '') {
		$mainframe = JFactory::getApplication();
        
        //Create unique install directory
        $tmpdir = uniqid( 'rsginstall_' );
        
        //Set destinatiopn
        if (!$destination)
            $copyDir = JPath::clean( JPATH_ROOT.DS . 'media' . DS . $tmpdir . DS );
        else
            $copyDir = JPath::clean($destination . DS . $tmpdir . DS);
            
        mkdir( $copyDir );
        
        //Store dirname for cleanup at the end.
        $this->extractDir = $tmpdir;

        //Add trailing slash to source path, clean function will remove it when unnecessary
        $source = JPath::clean($source.DS);

        //Check source directory
        if (!file_exists( $source ) OR !is_dir ( $source )) {
	        $mainframe->enqueueMessage( $source.JText::_('COM_RSGALLERY2_FU_FTP_DIR_NOT_EXIST') );
			$mainframe->redirect('index.php?option=com_rsgallery2&rsgOption=images&task=batchupload' );
        }
		
        //Read (all) files from FTP-directory
        $files = JFolder::files($source, '');
        if (!$files) {
	        $mainframe->enqueueMessage( JText::_('COM_RSGALLERY2_NO_VALID_IMAGES_FOUND_IN')
				.' '.JText::_('COM_RSGALLERY2_FTP_PATH').' '.$source."<br>"
				.JText::_('COM_RSGALLERY2_PLEASE_CHECK_THE_PATH') );
            $mainframe->redirect('index.php?option=com_rsgallery2&rsgOption=images&task=batchupload' );
        }
        
        //Create imagelist from FTP-directory
		$list = array();	// This array will hold all files to process
        foreach($files as $file) {
            if ( is_dir($source . $file) ) {
                continue;
            } else {
                if ( !in_array( fileHandler::getImageType($source . $file), $this->allowedFiles ) ) {
                    continue;
                } else {
                    //Add filename to list and copy to "/media/rsginstall_subdir" 
                    $list[] = $file;
                    copy( $source.$file, $copyDir.$file);
                }
            }
        }
		
		//Return imagelist only when there are images in it, else redirect
        if (count($list) == 0) {
	        $mainframe->enqueueMessage( JText::_('COM_RSGALLERY2_NO_FILES_FOUND_TO_PROCESS')
	        .JText::_('COM_RSGALLERY2_PLEASE_CHECK_THE_PATH').'<br>'
	        .JText::_('COM_RSGALLERY2_FTP_PATH').' "'.$source.'"' );
			$mainframe->redirect( 'index.php?option=com_rsgallery2&rsgOption=images&task=batchupload' );
        } else {
			return $list;            
        }

        return [];
    }
    
    /**
     * Reads the error code from the upload routine and generates corresponding message.
     * @param int $error Error code, from $_FILES['i_file']['error']
     * @return int|string 0 if upload is OK, $msg with error message if error has occured
     */
    static function returnUploadError( $error ) {
        if ( $error == UPLOAD_ERR_OK ) {
            return 0;
        } else {
            switch ( $error ) {
                case UPLOAD_ERR_INI_SIZE:
                    $msg = JText::_('COM_RSGALLERY2_THE_UPLOADED_FILE_EXCEEDS_THE_UPLOAD_MAX_FILESIZE_DIRECTIVE')." (".ini_get("upload_max_filesize").")".JText::_('COM_RSGALLERY2_IN_PHPINI');
                    break;
                case UPLOAD_ERR_FORM_SIZE:
					$msg = JText::_('COM_RSGALLERY2_FU_MAX_FILESIZE_FORM');
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $msg = JText::_('COM_RSGALLERY2_THE_UPLOADED_FILE_WAS_ONLY_PARTIALLY_UPLOADED');
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $msg = JText::_('COM_RSGALLERY2_NO_FILE_WAS_UPLOADED');
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $msg = JText::_('COM_RSGALLERY2_MISSING_A_TEMPORARY_FOLDER');
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $msg = JText::_('COM_RSGALLERY2_FAILED_TO_WRITE_FILE_TO_DISK');
                    break;
                case UPLOAD_ERR_EXTENSION;
                    $msg = JText::_('COM_RSGALLERY2_FILE_UPLOAD_STOPPED_BY_EXTENSION');
                    break;
                default:
                    $msg = JText::_('COM_RSGALLERY2_UNKNOWN_FILE_ERROR');
            }
        return $msg;
        }
    }   
}//End class FileHandler
