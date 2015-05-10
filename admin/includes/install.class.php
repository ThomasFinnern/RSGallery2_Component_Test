<?php
/**
* This file contains the install class for RSGallery2
* @version $Id: install.class.php 1088 2012-07-05 19:28:28Z mirjam $
* @package RSGallery2
* @copyright (C) 2003 - 2011 RSGallery2
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* RSGallery is Free Software
*/

// no direct access
defined( '_JEXEC' ) or die();

if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

// Include the JLog class.
jimport('joomla.log.log');

//require_once( $rsgClasses_path . 'file.utils.php' );

global $rsgConfig;
if( !isset( $rsgConfig )){
    
	JLog::add('require once: config /version', JLog::DEBUG);
	
    require_once( JPATH_SITE. DS . "administrator" . DS . "components" . DS . "com_rsgallery2" . DS . 'includes' .DS. "config.class.php" );
    require_once( JPATH_SITE. DS . "administrator" . DS . "components" . DS . "com_rsgallery2" . DS . 'includes' .DS. "version.rsgallery2.php" );

	JLog::add('create config /version', JLog::DEBUG);
		
    $rsgVersion = new rsgalleryVersion();
    $rsgConfig = new rsgConfig( false );

    // report all errors if in debug mode
    if($rsgConfig->get('debug'))
        error_reporting(E_ALL);
}

/**
* Install class
* @package RSGallery2
* @author Ronald Smit <webmaster@rsdev.nl>
*/
class rsgInstall {
    /** @var string RSGallery base directory */
    var $galleryDir;
    /** @var string Directory to hold original image */
    var $dirOriginal;
    /** @var string Directory to hold thumbnail */
    var $dirThumbs;
    /** @var string Directory to hold display image */
    var $dirDisplay;
	/** @var string Directory to hold watermarked image */
	var $dirWatermarked;
	/** @var array Table list of RSGallery2 */
    var $tablelistNew;
    /** @var array Table list of old RSGallery versions */
    var $tablelistOld;
    /** @var array List migrator class instances */
    var $galleryList;
    /** @var array List of allowed image formats */
    var $allowedExt;
    
    /** Constructor */
    function rsgInstall(){
		global $rsgConfig;
		
		JLog::add('Constructor rsgInstall class', JLog::DEBUG);
		
        $app =JFactory::getApplication();
		
		if (!defined("JURI_SITE")){
			define('JURI_SITE', $app->isSite() ? JUri::base() : JUri::root());
		}
		
        $this->galleryDir   = '/images/rsgallery';
        $this->dirThumbs    = '/images/rsgallery/thumb';
        $this->dirOriginal  = '/images/rsgallery/original';
        $this->dirDisplay   = '/images/rsgallery/display';
		$this->dirWatermarked  = '/images/rsgallery/watermarked';
		
        $this->tablelistNew = array('#__rsgallery2_galleries','#__rsgallery2_files','#__rsgallery2_comments','#__rsgallery2_config', '#__rsgallery2_acl');
        $this->tablelistOld = array('#__rsgallery','#__rsgalleryfiles','#__rsgallery_comments','');

		
        //TODO: this should use the master list in imgUtils
        $this->allowedExt   = array("jpg","gif","png");

        // initialize migrators here
        $this->galleryList  = array(
            new migrate_com_akogallery,
            new migrate_com_zoom_251_RC4,
            new migrate_com_ponygallery_ml_241,
            new migrate_com_easygallery_10B5
        );

		
        if( $rsgConfig->get( 'debug' )){
            $this->galleryList[] = new migrate_testMigrator;
            $this->galleryList[] = new migrate_testMigratorFail;
        }
        
		JLog::add('rsgInstall: exit constructor', JLog::DEBUG);

    }
    /** For debug purposes only */
    function echo_values(){
		echo JText::_('COM_RSGALLERY2_THUMBDIRECTORY_IS').$this->dirThumbs;
    }
	
    /**
     * Changes Menu icon in backend to RSGallery2 logo
	 * Deprecated in v3.0 for J!1.6
     */
    function changeMenuIcon() {
		$database = JFactory::getDBO();
		$database->setQuery("UPDATE #__extensions SET admin_menu_img='../administrator/components/com_rsgallery2/images/rsg2_menu.png' WHERE admin_menu_link='option=com_rsgallery2'");
		if ($database->execute())
			{
			$this->writeInstallMsg(JText::_('COM_RSGALLERY2_MENU_IMAGE_RSGALLERY2_SUCCESFULLY_CHANGED'), 'ok');
			}
		else
			{
			$this->writeInstallMsg(JText::_('COM_RSGALLERY2_MENU_IMAGE_COULD_NOT_BE_CHANGED'), 'error');
			}
    }
    
    /** 
     * Creates the default gallery directory structure
     */
    function createDirStructure() {
        
		JLog::add('rsgInstall: createDirStructure', JLog::DEBUG);
		
        $dirs = array($this->galleryDir, $this->dirOriginal, $this->dirThumbs, $this->dirDisplay, $this->dirWatermarked);
        $count = 0;
        
        foreach ($dirs as $dir) {
			if (file_exists(JPATH_SITE.$dir) && is_dir(JPATH_SITE.$dir)) {
				// Dir already exists, next
				$this->writeInstallMsg(JText::sprintf('COM_RSGALLERY2_ALREADY_EXISTS', $dir),"ok");
			}
			else {
				if(@mkdir(JPATH_SITE.$dir, 0777)) {
					$this->writeInstallMsg(JText::sprintf('COM_RSGALLERY2_FOLDER_IS_CREATED', $dir),"ok");
					$count++;
				}
				else {
					$this->writeInstallMsg(JText::sprintf('COM_RSGALLERY2_FOLDER_COULD_NOT_BE_CREATED', $dir),"error");
				}
			}
        }
    }

    /**
        Creates database table (needed for fresh install)
		DEPRECIATED (for migrator): use GenericMigrator:: instead [@todo: check usage and if indeed deprecated]
    **/
    function createTableStructure(){

		JLog::add('rsgInstall: createTableStructure', JLog::DEBUG);

		$result = $this->populate_db();

        if( count( $result ) == 0 ){
            $this->writeInstallMsg(JText::_('COM_RSGALLERY2_DATABASE_TABLES_CREATED_SUCCESFULLY'),"ok");
            return true;
        }
        else{
            foreach( $result as $e )
                $this->writeInstallMsg( $e, "error" );
            return true;
        }
    }

    /**
        ripped from joomla core: /installation/install2.php
        DEPRECIATED: use GenericMigrator:: instead
    * @param object database object
    * @param string File name
    * @return array containing errors
    * /
    static function populate_db( $sqlfile='rsgallery2.sql') {
        $database = JFactory::getDBO();
		
        $sqlDir = JPATH_SITE. DS . "administrator" . DS . "components" . DS . "com_rsgallery2" . DS . "sql/";
        $errors = array();
    
        $query = fread( fopen( $sqlDir . $sqlfile, 'r' ), filesize( $sqlDir . $sqlfile ) );
        $pieces  = GenericMigrator::split_sql($query);
    
        for ($i=0; $i<count($pieces); $i++) {
            $pieces[$i] = trim($pieces[$i]);
            if(!empty($pieces[$i]) && $pieces[$i] != "#") {
                $database->setQuery( $pieces[$i] );
                if (!$database->execute()) {
                    $errors[] = array ( $database->getErrorMsg(), $pieces[$i] );
                }
            }
        }
        return $errors;
    }
    /**/
	
    /**
     * Reads the content of the source directory and creates images in the specified directory
     * !!!!!!!!!!!!!!!! OBSOLETE, WILL BE REMOVED SHORTLY !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
     * @param string Source directory
     * @param string Type of image (display, thumbs)
     * @todo Do a check on allowed filetypes, so only gif, jpeg and png are fed to the image convertor
     */
    function createImages($dir, $type = "display") {

		JLog::add('rsgInstall: createImages', JLog::DEBUG);

		global $rsgConfig;
		/** 
		 * Set timelimit to avoid time out errors due to restaticions 
		 * in php.ini's 'max_execution_time' which defaults to 30 in
		 * most installations.
		 */
		switch ($type) {
		case "thumbs":
			$tdir    = $this->dirThumbs;
			$width  = $rsgConfig->get("thumb_width");
			break;
		default:
		case "display":
			$tdir    = $this->dirDisplay;
			$width  = $rsgConfig->get("image_width");
			break;
		}

		set_time_limit(120);
		$count = 0;
		if (is_dir($dir))
			{
			if ($handle = opendir($dir))
				{
				while (($filename = readdir($handle)) !== false)
					{
					if (!is_dir($dir.$filename) && $filename !== "." && $filename !== ".." && $filename !== "Thumbs.db")
						{
						if(imgUtils::resizeImage($dir."/".$filename, JPATH_SITE.$this->dirDisplay."/".$filename, $rsgConfig->get('image_width')))
							{
							continue;
							}
						else
							{
							$count++;
							}
						}
					}
				closedir($handle);
				}
			}
		if ($count > 0)
			return false;
		else
			return true;
    }
    
    /**
     * Copies everything from directory $source to directory $target and sets up permissions
     * 
     * @param string Source directory
     * @param string Destination directory
     * @param int chmod wanted (e.g. 0777)
     * @param boolean Subdirectory copying yes or no
     * @return boolean true on success, false on failure
     */
    function copyFiles($source, $target, $chmod=0777, $subdir=false){
		$errorcount = 0;
		$exceptions = array('.','..');
		/** 
		 * Set timelimit to avoid time out errors due to restaticions 
		 * in php.ini's 'max_execution_time' which defaults to 30 in
		 * most installations.
		 */
		set_time_limit(0);
		//* Processing
		$handle = opendir($source);
		while (false!==($item=readdir($handle)))
			if (!in_array($item,$exceptions))
				{
				/** cleanup for trailing slashes in directories destinations */
				$from    = str_replace('//','/',$source.'/'.$item);
				$to      = str_replace('//','/',$target.'/'.$item);
				if (is_file($from))
					{
					if (@copy($from,$to))
						{
						chmod($to,$chmod);
						touch($to,filemtime($from)); // to track last modified time
							$messages[]=JText::_('COM_RSGALLERY2_INSTALL_FILE_COPIED_FROM').$from.JText::_('COM_RSGALLERY2_INSTALL_FILE_COPIED_TO').$to;
						}
					else
						{
						$errors[]=JText::_('COM_RSGALLERY2_CANNOT_COPY_FILE_FROM').$from.JText::_('COM_RSGALLERY2_INSTALL_FILE_COPIED_TO').$to;
						$errorcount++;
						}
					}
				if (is_dir($from))
					{
					if($subdir)
						{
						if (@mkdir($to))
							{
							chmod($to,$chmod);
							$messages[]=JText::_('COM_RSGALLERY2_DIRECTORY_CREATED').$to;
							}
						else
							{
							$errors[]=JText::_('COM_RSGALLERY2_CANNOT_CREATE_DIRECTORY').' '.$to;
							$errorcount++;
							}
						$this->copyFiles($from, $to, $chmod, $subdir);
						}
					}
				}
		closedir($handle);
		if ($errorcount > 0)
			return false;
		else
			return true;
    }
     
    /**
     * Function will recursively delete all directories and files in them, including subdirectories
     *
     * @param string $target Directory to delete
     * @param array $exceptions Array of files to exclude from the delete
     * @param boolean $output Status message for every file True or False
     * @return boolean True or False
     */
    function deleteGalleryDir($target, $exceptions, $output=false) {
    
		JLog::add('rsgInstall: deleteGalleryDir: ' + $target, JLog::DEBUG);
		
		if (file_exists($target) && is_dir($target))
		{
			$sourcedir = opendir($target);
			while(false !== ($filename = readdir($sourcedir)))
				{
				if(!in_array($filename, $exceptions))
					{
					if($output)
						{
						echo JText::_('COM_RSGALLERY2_PROCESSING').$target."/".$filename."<br>";
						}
					if(is_dir($target."/".$filename))
						{
						// recourse subdirectory; call of function recursive
						$this->deleteGalleryDir($target."/".$filename, $exceptions);
						}
					else if(is_file($target."/".$filename))
						{
						// unlink file
						unlink($target."/".$filename);
						}
					}
				}
			closedir($sourcedir);
			if(rmdir($target))
				{
				//return 0;
				$this->writeInstallMsg(JText::sprintf('COM_RSGALLERY2_DIRECTORY_STRUCTURE_DELETED', $target),"ok");
				}
			else
				{
				//return 1;
				$this->writeInstallMsg(JText::sprintf('COM_RSGALLERY2_DELETING_OLD_DIRECTORY_STRUCTURE_FAILED', $target), "error");
				}
			}
		else
			{
			//return 2;
			$this->writeInstallMsg(JText::_('COM_RSGALLERY2_NO_OLD_DIRECTORY_STRUCTURE_FOUND_CONTINUE'),"ok");
			}
    }
    
    /**
     * NEEDS A REWRITE, DOES NOT FUNCTION PROPERLY
     * Function tries to set the correct permissions on a directory
     * @param string Directory to change permissions on
     * @param int Warning number
     * @todo (Rewrite this to function properly. Error trapping is different in class now)
	 * @todo 150329 may not be needed any more as it is not used
     */
    static function setDirPermsOnGallery($dir, &$warning_num)
    {
		JLog::add('rsgInstall: setDirPermsOnGallery', JLog::DEBUG);
		
		// ToDo: May use JPath::setPermissions
		// Chmods files and directories recursively to given permissions.
		// setPermissions(string $path, string $filemode = '0644', string $foldermode = '0755') : boolean
		
        global $ftpIsAvailable, $ftpUse;
        if(file_exists($dir))
            {
            if(is_dir($dir))
                {
                // check for correct permissions on the dir
                @chmod($dir, 0777);
                if((fileperms($dir) & 0777) != 0777)
                    {
                    // can't change file perms, so see if
                    // safemode patch installed and try it thru
                    // ftp assist
                    if(isset($ftpIsAvailable) && $ftpIsAvailable && $ftpUse && function_exists('chmodDir'))
                        chmodDir($dir, '777');
                    }
                if(fileperms($dir) & 0777 != 0777)
                    {
                    // issue warning about not being able to change gallery dir perms to 777
                    // this may or may NOT be a problem.  Let user decide.
                    $warning_num = 2;
                    return false;
                    }
                else
                    return true;
                }
            else
                {
                // existing gallery is a file rather than a directory
                // needs to be corrected by user first
                $warning_num = 1;
                return false;
                }
            }
            return true;
        }

    /**
     * Functions checks permissions on directories and returns status messages
     * @param string Directoy path to checked dir
     * @return boolean
     * @todo Rewrite this. Does not make sense now
     */
    function checkDirPerms($dir)
        {
        global $warning;
        if(!is_dir($dir))
            {
            //
            $this->writeInstallMSg("<strong>$dir</strong>".JText::_('COM_RSGALLERY2_PERMS_NOT_EXIST'),"error");
            }
        elseif(is_dir($dir) && (fileperms($dir) & 0777) != 0777)
            {
            $this->writeInstallMsg("<strong>$dir</strong>".JText::_('COM_RSGALLERY2_PERMS_NOT_SET').decoct(fileperms($dir)).'.<br />'.JText::_('COM_RSGALLERY2_PLEASE_TRY_TO_CORRECT_THESE_PERMISSIONS_THOUGH_FTP'),"error");
            }
        else
            {
            $this->writeInstallMsg("<strong>$dir</strong> ".JText::_('COM_RSGALLERY2_WAS_FOUND_PERMISSIONS_ARE_OK'),"ok");
            }
        }
    
    /**
     * Checks if component is installed
     * @param Component name (eg 'com_rsgallery2')
     * @return True or False
     */
    static function componentInstalled($component){
		$database = JFactory::getDBO();
		
		JLog::add('rsgInstall: componentInstalled', JLog::DEBUG);
		
		$component = $database->quote($component);
		// $sql = "SELECT COUNT(1) FROM #__extensions as a WHERE a.element = '$component'";
        $query = $database->getQuery(true);
        $query->select('*')
            ->from('#__extensions')
            ->where('element='. $component)
            ->limit('1');
        $database->setQuery($query);
		$result = $database->loadResult();

		if ($result > 0) {
			return true;
		} else {
			return false;
		}
    }

    /**
     * Writes an installation status message 
     * @param string Message to write
     * @param string Type of message (ok,error)
     */
    static function writeInstallMsg($msg, $type = NULL) {
        if ($type == "ok") {
            $icon = "tick.png";
		} elseif ($type == "error") {
            $icon = "publish_x.png";
		} else {
            $icon = "downarrow.png";
		}
	?>
        <div align="center">
        <table width="500"><tr><td>
			<table class="adminlist" border="1">
			<tr>
				<td width="40">
					<img src="<?php echo JURI_SITE;?>/administrator/components/com_rsgallery2/images/<?php echo $icon;?>" alt="" border="0">
				</td>
				<?php if( $type=='error' ): ?>
				<td>
					<pre><?php print_r( $msg );?></pre>
				</td>
				<?php else: ?>
				<td>
					<?php echo $msg;?>
				</td>
				<?php endif; ?>
			</tr>
			</table>
        </td></tr>
		</table>
        </div>
	<?php
	}
        
     /**
      * Shows the "Installation complete" box with a link to the control panel
      */
     static function installComplete($msg = null){
		if($msg == null) $msg = JText::_('COM_RSGALLERY2_INSTALLATION_OF_RSGALLERY_IS_COMPLETED');
		?>
		<div align="center">
			<table width="500"><tr><td>
				<table class="adminlist" border="1">
				<tr>
					<td colspan="2">
						<div align="center">
							<h2><?php echo $msg; ?></h2> 
							<?php //echo JText::_('COM_RSGALLERY2_INSTALL_STATUS_MSGS')?>
							<br>
							<a href="index.php?option=com_rsgallery2">
								<img src="<?php echo JURI_SITE.'administrator/components/com_rsgallery2/images/icon-48-config.png';?>" alt=" <?php echo JText::_('COM_RSGALLERY2_CONTROL_PANEL') ?>" width="48" height="48" border="0">
								<h2>
									<?php echo JText::_('COM_RSGALLERY2_CONTROL_PANEL') ?>
								</h2>
							</a>
						</div>
					</td>
				</tr>
				</table>
			</td></tr></table>
		</div>
        <?php
     }
	 
    /**
     * Deletes table from database if it exists
     * 
     * @param string Tablename to delete
     */
    function deleteTable($table)
    {
		JLog::add('rsgInstall: deleteTable', JLog::DEBUG);
	
        $database = JFactory::getDBO();
        $sql = "DROP TABLE IF EXISTS `$table`";
        $database->setQuery($sql);
        if ($database->execute())
            {
            $this->writeInstallMsg(JText::sprintf('COM_RSGALLERY2_IS_DELETED_OR_TABLE_DOES_NOT_EXIST_YET', $table),"ok");
            }
        else
            {
            $this->writeInstallMsg(JText::sprintf('COM_RSGALLERY2_COULD_NOT_BE_DELETED_DELETE_MANUALLY', $table),"error");
            }
        }
        
    /**
     * Performs exactly the same as the PHP5 function array_combine()
     */
    static function array_combine_emulated($keys, $vals) {
        $keys = array_values( (array) $keys );
        $vals = array_values( (array) $vals );
        $n = max( count( $keys ), count( $vals ) );
        $r = array();
        for( $i=0; $i<$n; $i++ ) {
            $r[ $keys[ $i ] ] = $vals[ $i ];
            }
        return $r;
    }
    
    /**
     * Returns the highest value for autoincrement id in table
     * @param string Tablename
     * @param integer Autoincrement ID for the table
     * @return integer Highest value for ID in table
     */
    static function maxId($tablename = "#__rsgallery2_cats", $id = "id") {
        $database = JFactory::getDBO();
        $sql = "SELECT MAX($id) FROM $tablename";
        $database->setQuery($sql);
        $max_id = $database->loadResult();
        return $max_id;
    }
    static function showInstallOptions(){
        ?>
        <table width="100%">
        <tr>
            <td width="300">&nbsp;</td>
            <td width="500">
                <table class="adminform" width="500">
                <tr>
                    <th><div style=font-size:14px;>Choose your option</div></th>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>
                    <div style=font-size:12px;font-weight:bold;>
                    <img style="float:left;margin:7px;" src="<?php echo JURI_SITE;?>/administrator/images/install.png" alt="" border="0">&nbsp;
                    <a href="index.php?option=com_rsgallery2&task=install&opt=fresh">Fresh install</a>
                    </div>
                    Installs a complete new RSGallery2. All original images, directories and database entries will be lost. Typical choice for a first install or if you want a completely fresh installation.
                    </td>
                </tr>
                <tr>
                    <td>
                    <div style=font-size:12px;font-weight:bold;>
                    <img style="float:left;margin:7px;" src="<?php echo JURI_SITE;?>/administrator/images/categories.png" alt="" border="0">&nbsp;
                    <a href="index.php?option=com_rsgallery2&task=install&opt=upgrade">Upgrade</a>
                    </div>
                    Upgrade from RSGallery 2.0 beta 5 only. This upgrade only works if the old database tables are still on the server and the '<strong>gallery</strong>' and '<strong>gallery/thumbs</strong>' directory still exist. If not, choose <a href="index.php?option=com_rsgallery2&task=install&opt=fresh">Fresh install</a>.
                    </td>
                </tr>
                <tr>
                    <td><div style=font-size:12px;font-weight:bold;>
                    <img style="float:left;margin:7px;" src="<?php echo JURI_SITE;?>/administrator/images/menu.png" alt="" border="0">&nbsp;
                    <a href="index.php?option=com_rsgallery2&task=install&opt=migration">Migration</a>
                    </div>
                    Migrate your other galleries to RSGallery2. This option will detect any other gallery component in your installation and will offer you the possibility to import the images into the new RSGallery2.<br>(Currently supported are: <strong>Zoom Media Gallery</strong>, <strong>Akogallery</strong> and .......)
                    </td>
                </tr>
                <tr>
                    <td>
                    <div style=font-size:12px;font-weight:bold;>
                    <img style="float:left;margin:15px;" src="<?php echo JURI_SITE;?>/administrator/images/next_f2.png" alt="" border="0">&nbsp;
                    <a href="index.php?option=com_rsgallery2">Do Nothing</a>
                    </div>
                    Choose this if you are upgrading from a recent RSGallery2 installation.  This option will preserve your existing RSGallery2 galleries and take you to the control panel.  Clicking "Continue" below does same but takes you back to Component Installers.
                    </td>
                </tr>      
                <tr>
                    <td>&nbsp;</td>
                </tr>
                <tr class="row1">
                    <td>&nbsp;</td>
                </tr>
                </table>
            </td>
            <td width="300">&nbsp;</td>
        </tr>
        </table>
        <?php
    }

	function RemoveAccidentallyLeftovers () {
       //Delete images and directories if exist
        $exceptions = array(".", "..");
        $this->deleteGalleryDir(JPATH_SITE.$this->galleryDir, $exceptions, false);

		/*
        //Delete database tables
        foreach ($this->tablelistNew as $table)
        {
            $this->deleteTable($table);
        } 
		*/		
 	}
	
    function freshInstall() {
        global $rsgConfig;
		$database = JFactory::getDBO();
		
        //echo '<h2>'.JText::_('COM_RSGALLERY2_FRESH_INSTALL').'</h2>';
        echo '<b>'.JText::_('COM_RSGALLERY2_FRESH_INSTALL').'</b>';

        //Delete images and directories if exist
//		$this->RemoveAccidentallyLeftovers ();

		/* ToDo: Too late here -> use preflight 
        //Delete database tables
        foreach ($this->tablelistNew as $table)
        {
            $this->deleteTable($table);
        }
		*/
		
        //Create new directories
        $this->createDirStructure();
        
		//Create RSGallery2 table structure
        //$this->createTableStructure();

        // save config to populate database with default config values
        $rsgConfig->saveConfig();
        
        //Now wish the user good luck and link to the control panel
        $this->installComplete();
    }
    
    /**
     * Migration scripts are called from here
     *
     */
    function showMigrationOptions() {
        
        $i = 0;

        foreach( $this->galleryList as $component ){
            if( $component->detect() ){
                ?>
                <div align="center">
                <table width="500"><tr><td>
                <table class="adminlist" border="1">
                <tr>
                    <td width="75%"><strong><?php echo $component->getName(); ?></strong> is installed</td>
                    <td><a href="index.php?option=com_rsgallery2&rsgOption=maintenance&task=doMigration&type=<? echo $component->getTechName(); ?>"><img src="<?php echo JURI_SITE;?>/administrator/images/install.png" alt="" width="24" height="24" border="0" align="middle">&nbsp;Migrate</a></td>
                </tr>
                </table>
                </td></tr></table>
                </div>
                <?php
                $i++;
            }
        }
        if ( $i == 0 ){
            //No migration possibilities
            $this->writeInstallMsg(JText::_('COM_RSGALLERY2_NO_OTHER_GALLERYSYSTEMS_INSTALLED'),"error");
        }
    }

    /**
     * actually does a migration
     * @param string type of migration
     */
    function doMigration( $type ){
        foreach( $this->galleryList as $gallery ){
            if( $type == $gallery->getTechName() ){
                return $gallery->migrate();
            }
        }
        return "$type".JText::_('COM_RSGALLERY2_IS_NOT_A_VALID_MIGRATION_TYPE');
    }
    
    /**
     * Checks if specified table exists in the system
     * 
     * @param string Tablename
     * @return True or False
     */
    static function tableExists($table) {
		global $mosConfig_dbprefix;
		$database = JFactory::getDBO();
			
		$table = substr($table, 3);
		$sql = "SHOW TABLES LIKE '$mosConfig_dbprefix$table'";
		$database->setQuery($sql);
		if ($database->execute())
			$result = $database->getNumRows();
		if ($result > 0)
			{
			return true;
			}
		else
			{
			return false;
			}
    }
    /**
     * Returns the extension of a file
     *
     * @param integer Filename
     * @return Extension of filename
     */
    static function getExtension($filename){
        $parts = array_reverse(explode(".", $filename));
        $ext = $parts[0];
        return strtolower($ext);
    }
}	//End class rsgInstall

/**
* abstract parent class for migrators
* @package RSGallery2
* @author Jonah Braun <Jonah@WhaleHosting.ca>
*/
class GenericMigrator{
    /* public functions - should be overridden */
    
    /**
     * Function will return string containing the technical name.  no spaces, special characters, etc allowed as this will be used in GET/POST.
     * It would be advisable to use the class name.  we would just use get_class(), but it's implementation is differs in PHP 4 and 5.
     * @return string Technical name
     */
    static function getTechName(){
        return 'GenericMigrator';
    }

    /**
     * @return string containing user friendly name and version(s) of which gallery this class migrates
     */
    static function getName(){
        return 'GenericMigrator';
    }

    /**
     * detect if the gallery version this class handles is installed
     * @return true or false
     */
    static function detect(){
        return false;
    }

    /**
     * do the migration thing
     * @return true on success, anything else a failure
     */
    function migrate(){
        return false;
    }

    /* utility functions */

    /**
    * @param string File name
    * @return bool true if success
    **/
    function handleSqlFile( $sqlfile ){
        $result = $this->runSqlFile( $sqlfile );

        if( count( $result ) == 0 ){
            rsgInstall::writeInstallMsg(JText::_('COM_RSGALLERY2_DATABASE_TABLES_CREATED_SUCCESFULLY'),"ok");
            return true;
        }
        else{
            foreach( $result as $e )
                rsgInstall::writeInstallMsg( $e, "error" );
            return false;
        }
    }

    /**
     * ripped from joomla core: /installation/install2.php:populate_db()
     * @param string File name
     * @return array containing errors
     */
    function runSqlFile( $sqlfile ) {
		$database = JFactory::getDBO();
        $sqlDir =  JPATH_SITE. DS . "administrator" . DS . "components" . DS . "com_rsgallery2" . DS . "sql/";

        $errors = array();
    
        $query = fread( fopen( $sqlDir . $sqlfile, 'r' ), filesize( $sqlDir . $sqlfile ) );
        $pieces  = $this->split_sql($query);
    
        for ($i=0; $i<count($pieces); $i++) {
            $pieces[$i] = trim($pieces[$i]);
            if(!empty($pieces[$i]) && $pieces[$i] != "#") {
                $database->setQuery( $pieces[$i] );
                if (!$database->execute()) {
                    $errors[] = array ( $database->getErrorMsg(), $pieces[$i] );
                }
            }
        }
        return $errors;
    }
    
    /**
     * ripped from joomla core: /installation/install2.php
     * @param string
     */
    static function split_sql($sql) {
        $sql = trim($sql);
        //$sql = ereg_replace("\n#[^\n]*\n", "\n", $sql);
        // ereg_replace deprecated
        $sql = preg_replace("/\n#[^\n]*\n/", "\n", $sql);

        $buffer = array();
        $ret = array();
        $in_string = false;
    
        for($i=0; $i<strlen($sql)-1; $i++) {
            if($sql[$i] == ";" && !$in_string) {
                $ret[] = substr($sql, 0, $i);
                $sql = substr($sql, $i + 1);
                $i = 0;
            }
    
            if($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\") {
                $in_string = false;
            }
            elseif(!$in_string && ($sql[$i] == '"' || $sql[$i] == "'") && (!isset($buffer[0]) || $buffer[0] != "\\")) {
                $in_string = $sql[$i];
            }
            if(isset($buffer[1])) {
                $buffer[0] = $buffer[1];
            }
            $buffer[1] = $sql[$i];
        }
    
        if(!empty($sql)) {
            $ret[] = $sql;
        }
        return($ret);
    }
    
	/**
     * Function migrates gallery information of other gallery systems to RSGallery2
     *
     * @param string Old gallery tablename
     * @param string Old ID field name
     * @param string Old Category field name
     * @param string Old Parent ID field name
     * @param string Old Description field name
     */
	static function migrateGalleries($old_table, $old_catid = "id", $old_catname = "catname", $old_parent_id = "parent_id", $old_descr_name = "description", $max_id) {
		$database = JFactory::getDBO();
	    //Set variables
	    $error = 0;
	    $file = 0;
	    
	    //Select all category details from other gallery system
	    $sql = "SELECT $old_catid, $old_catname, $old_parent_id, $old_descr_name FROM $old_table ORDER BY $old_catname ASC";
	    $database->setQuery($sql);
	    $old = $database->loadObjectList();
	    
	    foreach ($old as $row) {
			//Create new category ID
	        $id             = $row->$old_catid + $max_id;
			$id				= (int) $id;
	        $catname        = $database->quote($row->$old_catname);
			// $parent_id		= (int) $parent_id;
	        $description    = $database->quote($row->$old_descr_name);
	        $alias			= $database->quote(JFilterOutput::stringURLSafe($catname));
	        if ($row->$old_parent_id == 0) {
	            $parent_id  = 0;
	        } else {
	            $parent_id  = $row->$old_parent_id + $max_id;
	        }
	        
	        //Insert values into RSGallery2 gallery table
	        $sql2 = "INSERT INTO #__rsgallery2_galleries ".
	                "(id, name, parent, description, published, alias) VALUES ".
	                "('$id','$catname','$parent_id','$description', '1', '$alias')";
	        $database->setQuery($sql2);
			//Count errors and migrated files
	        if (!$database->execute()) {
	            $error++;
	        } else {
	            $file++;
	        }
		}
		
	    $total = $error + $file;
	    if ($error > 0) {
	        rsgInstall::writeInstallMsg(JText::_('COM_RSGALLERY2__MIGRATE_NOT_ALL_GAL')."<strong>$file</strong>".JText::_('COM_RSGALLERY2_OUT_OF')."<strong>$processed</strong>".JText::_('COM_RSGALLERY2_ENTRIES_PROCESSED'),"error");
		} else {
	        rsgInstall::writeInstallMsg(JText::_('COM_RSGALLERY2_ALL_GALLERY_INFORMATION_MIGRATED_TO_RSGALLERY2_DATABASE')."<strong>$file</strong>".JText::_('COM_RSGALLERY2_ENTRIES_PROCESSED'),"ok");
	    }
    }
    
	/**
     * Migrates item information of other gallery systems to RSGallery2
     * 
     * @param string Old files tablename
     * @param string Old image name
     * @param string Old image filename
     * @param timestamp Old image date
     * @param string Old description
     * @param integer Old User ID
     * @param integer Old category ID
     * @param integer Highest value in new table
     */
    static function migrateItems($old_table, $old_image_name, $old_image_filename, $old_image_date, $old_description, $old_uid, $old_catid, $max_id, $prefix) {
		$database = JFactory::getDBO();
	    //Set variables
	    $error = 0;
	    $file = 0;
	    
	    //GEt all information from images table
	    $sql = "SELECT * FROM $old_table";
	    $database->setQuery($sql);
	    $old = $database->loadObjectList();
	    
	    foreach ($old as $row) {
	        $filename   = $database->quote($prefix.$row->$old_image_filename);
			$descr      = $database->quote($row->$old_description);
	        $imagename  = $database->quote($row->$old_image_name);
	        $date       = $database->quote($row->$old_image_date);
	        $uid        = (int) $row->$old_uid;
	        $catid      = $row->$old_catid + $max_id;
			$catid		= (int) $catid;
			$alias		= $database->quote(JFilterOutput::stringURLSafe($imagename));
	        
	        //Insert data into RSGallery2 files table
	        $sql2 = "INSERT INTO #__rsgallery2_files ".
	                "(name, descr, title, date, userid, gallery_id, alias) VALUES ".
	                "('$filename', '$descr', '$imagename', '$date', '$uid', '$catid', '$alias')";
	        $database->setQuery($sql2);
	        
	        //Error and file counting
	        if (!$database->execute()) {
	            $error++;
	        } else {
	            $file++;
	        }
		}
	    $total = $error + $file;
	    if ($error > 0) {
	        rsgInstall::writeInstallMsg(JText::_('COM_RSGALLERY2_MIGRATE_NOT_ALL')."<strong>$file</strong>".JText::_('COM_RSGALLERY2_OUT_OF')."<strong>$total</strong>".JText::_('COM_RSGALLERY2_ENTRIES_PROCESSED'),"error");
		} else{
	        rsgInstall::writeInstallMsg(JText::_('COM_RSGALLERY2_ALL_FILE_INFORMATION_MIGRATED_TO_RSGALLERY2_DATABASE')."<strong>$file</strong>".JText::_('COM_RSGALLERY2_ENTRIES_PROCESSED'),"ok");
		}
	}

	/**
     * Migrates comment information of other gallery systems to RSGallery2
     * 
     * @param string Old commments tablename
     * @param string Old comment text
     * @todo Make this work. As images get new Image ID's this means the comments don't match when migrated.
     */	
	static function migrateComments($old_table = "#__zoom_comments", $old_comment = "cmtcontent", $old_img_id = "imgid") {
		$database = JFactory::getDBO();
    	return true;
    }
}//end class

/**
 * test migrator - always succesfull
 * @package RSGallery2
 */
class migrate_testMigrator extends GenericMigrator{
    static function getTechName(){
        return 'testMigrator';
    }
    static function getName(){
        return 'test migrator for debug mode';
    }
    static function detect(){
        return true;
    }
	/**
     * do the migration thing
     * @return true on success, anything else a failure
     */
    function migrate(){
        return true;
    }
}
/**
 * test migrator - always fails
 * @package RSGallery2
 */
class migrate_testMigratorFail extends GenericMigrator{
    static function getTechName(){
        return 'testMigratorFail';
    }
    static function getName(){
        return 'test migrator for debug mode - always fails';
    }
    static function detect(){
        return true;
    }
	/**
     * do the migration thing
     * @return true on success, anything else a failure
     */
    function migrate(){
        return "this test migrator always fails.  :-p";
    }
}


/**
* akogallery migrator
* @package RSGallery2
*/
class migrate_com_akogallery extends GenericMigrator{

    var $imgTable =         '#__akogallery';
    var $commentTable =     '#__akogallery_comments';
    var $categoryTable =    '#__categories';
    
    /**
     * @return string containing the technical name.  no spaces, special characters, etc allowed as this will be used in GET/POST.  advisable to use the class name.  we would just use get_class(), but it's implementation differs in PHP 4 and 5.
     */
    static function getTechName(){
        return 'com_akogallery';
    }


    /**
     * @return string containing a user friendly name and version(s) of which gallery this class migrates
     */
    static function getName(){
        return 'AKO Gallery - any version';
    }

    /**
     * detect if the gallery version this class handles is installed
     * @return true or false
     */
    static function detect(){
        // if AKO has changed it's storage format over time, we should also check for version
        return rsgInstall::componentInstalled( 'com_akogallery' );
    }

    /**
     * do the migration thing
     * @return true on success, anything else a failure
     */
    function migrate() {
        
        $comconfig =  JPATH_SITE. DS . "administrator" . DS . "components" . DS . "com_akogallery" . DS . "config.akogallery.php";

        if (! file_exists($comconfig))
            return ( "Config file for AKOGallery does not exist" );
        else
            include_once( $comconfig );

        $imgDir = JPATH_SITE . $ag_pathimages;

        if( !is_dir( $imgDir )) {
            return 'Image Directory does not exist.';
        }

        // set_magic_quotes_runtime is deprecated since PHP 5.3.0
        // minimum version for J!3 is PHP 3.10
        // example how to check it
        //if(get_magic_quotes_runtime()) // Checks if lower then PHP 5.3
        //    set_magic_quotes_runtime(1);
        // -> all further functions are
        // Deprecated: set_magic_quotes_runtime(1);
        $oldnewcats = $this->migrateCategories();
        if( $oldnewcats === false ){
            // Deprecated: set_magic_quotes_runtime(0);
            return 'Error migrating Categories';
        }

        if( !$this->migrateImages( $imgDir, $oldnewcats )){
            // Deprecated: set_magic_quotes_runtime(0);
            return 'Error migrating images';
        }

        if( !$this->migrateComments() ){
            // Deprecated: set_magic_quotes_runtime(0);
            return 'Error migrating Comments';
        }

        // Deprecated: set_magic_quotes_runtime(0);
            
        return 'Successful migration';
    }
	/**
	 * add a new category for every category in #__categories that has section set to com_akogallery
     */
// #__rsgallery2_cat does not exist in v3.1.0, so comment entire function
/*    function migrateCategories(){
        
        $database = JFactory::getDBO();
        $objects = 0;
        $error = 0;
        
        $stringId = "id";
        $stringCatName = "name";
        $stringDesc = "description";
        $stringParentId = "parent_id";
        
        $id = 0; 
        $catname = ''; 
        $desc = ''; 
        $parent_id = 0; 
        $insertSQL= '';
        
        $selectSQL = "SELECT $stringId, $stringCatName, $stringParentId, $stringDesc FROM " . $this->categoryTable . " WHERE section = '" . $this->getTechName() .
            "' ORDER BY $stringCatName ASC";
        $database->setQuery( $selectSQL );
        $AKOCat = $database->loadObjectList();

        // We want to make sure everything works or nothing works... makes it easier to fix and retry
        $database->setQuery( "BEGIN" );

        // contains ids: oldcat => newcat
        $oldnewcats = array();
        
        foreach ( $AKOCat as $oldCat ) {
            $oldnewcats[ $oldCat->$stringId ] = rsgInstall::maxId() + 1;
            $id         = $oldnewcats[ $oldCat->$stringId ];
            $catname    = $oldCat->$stringCatName;
            $desc       = $oldCat->$stringDesc;
    
            if( $oldCat->$stringParentId == 0 )
                $parent_id = 0;
            else
                $parent_id = $oldnewcats[ $oldCat->$stringParentId ];

            $insertSQL = 'INSERT INTO #__rsgallery2_cats ' .	// #__rsgallery2_cat does not exist in v3.1.0, so comment entire function
            '( id, catname, parent, description ) VALUES ' .
            "( $id, '$catname', $parent_id, '$desc' )";
            $database->setQuery( $insertSQL );
            
            if( ! $database->execute() )
            {
                $error++;
                rsgInstall::writeInstallMsg( "Error importing AKOGallery categories into RSG2 category table. Category Migration rolled back. Please post a bug about this so we can help you with it.
                <br>id = $id
                <br>catname = $catname
                <br>parent = $parent_id
                <br>description = $desc
                <br><br>insertSQL Statement = $insertSQL
                <br><Br>selectSQL statement = $selectSQL
                <br><br>error:" . $database->getErrorMsg(), "error" );
            }
            else
            $objects++;
        }
        if( $error <> 0 )
        {
            $database->setQuery( "ROLLBACK" );
            rsgInstall::writeInstallMsg( "Error importing AKOGallery categories into RSG2 category table. Category Migration rolled back. Please post a bug about this so we can help you with it.", "error" );
        }
        else {
            $database->setQuery( "COMMIT" );
            rsgInstall::writeInstallMsg( "All Category entries successfully imported into RSG2 table. " . $objects . " objects imported", "ok" );
            return $oldnewcats;
        }
    }/**/

    function migrateImages( $imgDir, $oldnewcats ){
        /*
        for every entry in $this->imgTable call imgUtils::importImage() with the info from $this->imgTable, $this->$commentTable and full path to image using $imgDir
        */
        $database = JFactory::getDBO();
        
        $selectSQL = "SELECT imgfilename, imgtitle, catid FROM $this->imgTable";
        $database->setQuery( $selectSQL );
        $AKOFile = $database->loadObjectList();

        $finalResult = true;
        
        foreach ( $AKOFile as $file ) {
            // Deprecated: set_magic_quotes_runtime(0);
            $filePath   = $imgDir . "/" . $file->imgfilename;
            $imgTitle   = $file->imgtitle;
            $catId      = $oldnewcats[ $file->catid ];
            $fileName   = $file->imgfilename;

            $result = imgUtils::importImage( $filePath, $fileName, $catId, $imgTitle );

            if( $result !== true ){
                rsgInstall::writeInstallMsg( $result->toString(), 'error' );
                $finalResult = false;
                return $finalResult;
            }
        }
        return $finalResult;
    }

//picid and name do not exist in #__rsgallery2_comments in v3.1.0, so comment entire function?!
/*	function migrateComments() {
        $database = JFactory::getDBO();
        $error = 0;
        $objects = 0;

        $selectSQL = "SELECT cmtpic, cmtname, cmttext FROM $this->commentTable";
        $database->setQuery( $selectSQL );
        $AKOComment = $database->loadObjectList();
        // Again - We want everything or nothing to work.
        $database->setQuery( "BEGIN" );

        foreach ( $AKOComment as $comment ) {
            $picId      = $comment->cmtpic;
            $name       = $comment->cmtname;
            $commentText= $comment->cmttext;

            $insertSQL = "INSERT INTO #__rsgallery2_comments " .
            "( picid, name, comment ) VALUES " .
            "( $picId, '$name', '$commentText' )";	//picid and name do not exist in #__rsgallery2_comments in v3.1.0, so comment entire function?!
            $database->setQuery( $insertSQL );

            if( !$database->execute() )
            $error++;
            else
            $objects++;

            if( $error <> 0 ) {
                $database->setQuery( "ROLLBACK" );
                rsgInstall::writeInstallMsg( "Error inserting comments. Transaction Cancelled. Please post an error so we can help you with it.", "error" );
            }
            else {
                $database->setQuery( "COMMIT" );
                rsgInstall::writeInstallMsg( "Comments Migrated Successfully. " . $objects . " imported into RSGallery2 comments table", "ok" );
                return true;
            }
        }
    }/**/
}
/**
 * Pony Gallery ML version 2.4.1 migrator
 * @package RSGallery2
 * @author Ronald Smit <ronald.smit@rsdev.nl>
 */
class migrate_com_ponygallery_ml_241 extends genericMigrator {
	/**
     * @return string containing the technical name.  no spaces, special characters, etc allowed as this will be used in GET/POST.  advisable to use the class name.  we would just use get_class(), but it's implementation is differs in PHP 4 and 5.
     */
    static function getTechName(){
        return 'com_ponygallery_ml_241';
    }

    
    /**
     * @return string containing a user friendly name and version(s) of which gallery this class migrates
     */
    static function getName(){
        return 'Pony Gallery ML 2.4.1';
    }

    /**
     * detect if the gallery version this class handles is installed
     * @return true or false
    **/
    static function detect(){
        
        if( rsgInstall::componentInstalled( "com_ponygallery" )){
            include_once(JPATH_SITE . DS . "components" . DS . "com_ponygallery" . DS . "language" . DS . "english.php");
			$version = explode(",", _PONYGALLERY_VERSION);
            if ( $version[0] == "Version 2.4.1" )
            	return true;
        }

        // component not installed or wrong version.
        return false;
    }
/**
 * Copies original images from Pony Gallery to the RSGallery2 file structure
 * and then creates display and thumb images.
 * @param string full path to the original Pony Images
 * @return True id succesfull, false if not
 */
function copyImages($basedir, $prefix = "pony_"){
        global $database, $rsgConfig;
        
        $sql = "SELECT * FROM #__ponygallery";
        $database->setQuery( $sql );
        $result = $database->loadObjectList();
        $i = 0;
        foreach ($result as $image) {
        	$source 		= $basedir . $image->imgfilename;
        	$destination	= JPATH_ORIGINAL . DS . $prefix.$image->imgfilename;

			//First move image to original folder
        	$newpath = fileUtils::move_uploadedFile_to_orignalDir($source, $destination);
        	if ($newpath) {
        		imgUtils::makeDisplayImage($newpath, '', $rsgConfig->get('image_width'));
        		imgUtils::makeThumbImage($newpath);
        	} else {
        		$i++;
        	}
        }
		if ($i > 0) {
			return false;
		} else {
			return true;
		}
    }
    
    function migrate() {

    	//Set basedir to original images
	    include_once(JPATH_SITE. DS . "administrator" . DS . "components" . DS . "com_ponygallery" . DS ."config.ponygallery.php");
        // ToDo: 1505010 $ag_pathoriginalimages  is undefined
	    $basedir = JPATH_SITE . $ag_pathoriginalimages . DS;
	    
	    //Set prefix
	    $prefix = "pony_";
	    
	    //Show start message
	    rsgInstall::writeInstallMsg("Start migrating ".$this->getName(),"ok");

	    //Define Max ID in #__rsgallery2_galleries
	    $max_id = rsgInstall::maxId();

	    //Migrate categories to RSGallery2 DB
	    $this->migrateGalleries("#__ponygallery_catg", "cid", "name", "parent", "description", $max_id);
	    
	    //Migrate files into RSGallery2 DB
	    $this->migrateItems("#__ponygallery", "imgtitle", "imgfilename", "imgdate", "imgtext", "imgauthor", "catid", $max_id, $prefix);

	    //Migrate comments into RSGallery2 DB
	    //$this->migrateComments("#__ponygallery_comments", "cmttext", "cmtid");
		
	    if ($this->copyImages($basedir)) {
	        rsgInstall::writeInstallMsg(JText::_('COM_RSGALLERY2_FILES_SUCCESFULLY_COPIED_TO_NEW_STRUCTURE'),"ok");
	    } else {
	        rsgInstall::writeInstallMsg(JText::_('COM_RSGALLERY2_THERE_WERE_ERRORS_COPYING_FILES_TO_THE_NEW_STRUCTURE'),"error");
	    }
	    rsgInstall::installComplete("Migration of ".$this->getName()." completed");
	    
    }
}

/**
* Zoom Gallery 2.5.1 RC4 migrator
* @package RSGallery2
* @author Ronald Smit <ronald.smit@rsdev.nl>
*/
class migrate_com_zoom_251_RC4 extends GenericMigrator{

    /**
     * @return string containing the technical name.  no spaces, special characters, etc allowed as this will be used in GET/POST.  advisable to use the class name.  we would just use get_class(), but it's implementation is differs in PHP 4 and 5.
     */
    static function getTechName(){
        return 'com_zoom_251_RC4';
    }

    
    /**
     * @return string containing a user friendly name and version(s) of which gallery this class migrates
     */
    static function getName(){
        return 'ZOOM Gallery 2.5.1 RC4';
    }

    /**
     * detect if the gallery version this class handles is installed
     * @return true or false
    **/
    static function detect(){
        
        $comdir =  JPATH_SITE. DS . "administrator" . DS . "components" . DS . "com_zoom";
        
        if( rsgInstall::componentInstalled( "com_zoom" )){
            include_once(JPATH_SITE. DS . "administrator" . DS . "components" . DS . "com_zoom" . DS ."etc" . DS ."zoom_config.php");

            if ( $zoomConfig['version'] == "2.5.1 RC4" ) {
            	return true;
            }
        }

        // component not installed or wrong version.
        return false;
    }

    /**
     * do the migration thing
     * @return true on success, anything else a failure
     */
    function migrate(){
	    global $mosConfig_absolute_path;
	    
	    //Set basedir from config file
	    include_once(JPATH_SITE. DS . "components" . DS . "com_zoom" . DS . "zoom_config.php");
	    $basedir = JPATH_SITE . "/" .$zoomConfig['imagepath'];
	    
	    //Set prefix
	    $prefix = "zoom_";
	    
	    //Write version is OK
	    rsgInstall::writeInstallMsg("OK, right version (".$zoomConfig['version'].") is installed. Let's migrate!","ok");
			    
	    //Determine max ID for proper ID transfer to database
	    $max_id = rsgInstall::maxId();
	    
	    //Create RSGallery2 table structure, WHY do this!!!!
	    //$this->createTableStructure();
	    
	    //Migrate categories to RSGallery2 DB
	    $this->migrateGalleries("#__zoom", "catid", "catname", "subcat_id", "catdescr", $max_id);
	    
	    //Migrate files into RSGallery2 DB
	    $this->migrateItems("#__zoomfiles", "imgname", "imgfilename", "imgdate", "imgdescr", "uid", "catid", $max_id, $prefix);
	    
	    //Migrate comments into RSGallery2 DB
	    //$this->migrateComments();//Obsolete for now
	    
		
	    if ($this->copyImages($basedir, $prefix)) {
	        rsgInstall::writeInstallMsg(JText::_('COM_RSGALLERY2_FILES_SUCCESFULLY_COPIED_TO_NEW_STRUCTURE'),"ok");
	    } else {
	        rsgInstall::writeInstallMsg(JText::_('COM_RSGALLERY2_THERE_WERE_ERRORS_COPYING_FILES_TO_THE_NEW_STRUCTURE'),"error");
	    }
	    rsgInstall::installComplete("Migration of ".$this->getName()." completed");
	}
	
	static function copyImages($basedir, $prefix = "zoom_") {
		global $rsgConfig;
		$database = JFactory::getDBO();
		
		//Set error count
		$i = 0;
		
		//Retrieve image names and folder from database
		$sql = "SELECT * FROM #__zoomfiles as a, #__zoom as b " .
				"WHERE a.catid = b.catid " .
				"ORDER BY a.catid ASC";
		$database->setQuery( $sql );
		$result = $database->loadObjectList();

		//Copy images and create display and thumb
		foreach ($result as $image) {
			$source 		= $basedir . $image->catdir . "/" . $image->imgfilename;
			$destination 	= JPATH_ORIGINAL . "/" . $prefix.$image->imgfilename;

			//First move image to original folder
        	$newpath = fileUtils::move_uploadedFile_to_orignalDir($source, $destination);
        	if ($newpath) {
        		imgUtils::makeDisplayImage($newpath, '', $rsgConfig->get('image_width'));
        		imgUtils::makeThumbImage($newpath);
        	} else {
        		$i++;
        	}
		}
		//Handle errors
		if ($i > 0) {
			return false;
		} else {
			return true;
		}
	}
}

/**
* Easy Gallery migrator
* @package RSGallery2
* @author Ronald Smit <ronald.smit@rsdev.nl>
*/
class migrate_com_easygallery_10B5 extends GenericMigrator{

    /**
     * @return string containing the technical name.  no spaces, special characters, etc allowed as this will be used in GET/POST.  advisable to use the class name.  we would just use get_class(), but it's implementation is differs in PHP 4 and 5.
     */
    static function getTechName(){
        return 'com_easygallery_10B5';
    }

    
    /**
     * @return string containing a user friendly name and version(s) of which gallery this class migrates
     */
    static function getName(){
        return 'Easy Gallery 1.0 beta 5';
    }

    /**
     * detect if the gallery version this class handles is installed
     * @return true or false
    **/
    static function detect(){
        global $mosConfig_absolute_path;
        
        if( rsgInstall::componentInstalled( "com_easygallery" )){
			return true;
        } else {
        	// component not installed or wrong version.
        	return false;
        }
    }

	function migrate() {
		$database = JFactory::getDBO();
		//Set basedir from config file
	    include_once(JPATH_SITE. DS . "administrator" . DS . "components" . DS . "com_easygallery" . DS . "configuration.php");
	    $basedir = JPATH_SITE .$eg_original_path;

	    //Set prefix
	    $prefix = "easy_";

	    //Write version is OK
	    rsgInstall::writeInstallMsg("OK, right version is installed. Let's migrate!","ok");
	    
	    //Determine max ID for proper ID transfer to database
	    $max_id = rsgInstall::maxId();
	    
	    //Create RSGallery2 table structure, WHY do this!!!!
	    //$this->createTableStructure();
	    
	    //Migrate categories to RSGallery2 DB
	    $this->migrateGalleries("#__categories", "id", "title", "parent_id", "description", $max_id);
	    
	    //Migrate files into RSGallery2 DB
	    $this->migrateItems("#__easygallery", "name", "path", "0000-00-00 00:00:00", "description", 0, "cid", $max_id, $prefix);
	    
	    //Migrate comments into RSGallery2 DB
	    //$this->migrateComments();//Obsolete for now
	    
	    if ($this->copyImages($basedir, $prefix)) {
	        rsgInstall::writeInstallMsg(JText::_('COM_RSGALLERY2_FILES_SUCCESFULLY_COPIED_TO_NEW_STRUCTURE'),"ok");
	    } else {
	        rsgInstall::writeInstallMsg(JText::_('COM_RSGALLERY2_THERE_WERE_ERRORS_COPYING_FILES_TO_THE_NEW_STRUCTURE'),"error");
	    }
	    rsgInstall::installComplete("Migration of ".$this->getName()." completed");
	}
	
	/**
     * Function migrates gallery information of Easy Gallery to RSGallery2
     * Easy Gallery uses Joomla #__categories table for the storage of category information,
     * so a custom migrateGalleries() function is necessary here.
     * 
     * @param string Old gallery tablename
     * @param string Old ID field name
     * @param string Old Category field name
     * @param string Old Parent ID field name
     * @param string Old Description field name
     */
	static function migrateGalleries($old_table, $old_catid = "id", $old_catname = "catname", $old_parent_id = "parent_id", $old_descr_name = "description", $max_id) {
		$database = JFactory::getDBO();
	    //Set variables
	    $error = 0;
	    $file = 0;
	    
		$old_catname = $database->quote($old_catname);
		
	    //Select all category details from other gallery system
	    $sql = "SELECT $old_catid, $old_catname, $old_parent_id, $old_descr_name " .
	    		"FROM $old_table " .
	    		"WHERE section = 'com_easygallery'" .
	    		"ORDER BY $old_catname ASC";
	    $database->setQuery($sql);
	    $old = $database->loadObjectList();
	    
	    foreach ($old as $row) {
			//Create new category ID
	        $id             = (int) $row->$old_catid + $max_id;
	        $catname        = $database->quote($row->$old_catname);
			$parent_id		= (int) $parent_id;
	        $description    = $database->quote($row->$old_descr_name);
	        $alias			= $database->quote(JFilterOutput::stringURLSafe($catname));
	        if ($row->$old_parent_id == 0) {
	            $parent_id  = 0;
	        } else {
	            $parent_id  = $row->$old_parent_id + $max_id;
	        }
	        
	        //Insert values into RSGallery2 gallery table
	        $sql2 = "INSERT INTO #__rsgallery2_galleries ".
	                "(id, name, parent, description, published, alias) VALUES ".
	                "('$id','$catname','$parent_id','$description', '1', '$alias')";
	        $database->setQuery($sql2);
			//Count errors and migrated files
	        if (!$database->execute()) {
	            $error++;
	        } else {
	            $file++;
	        }
		}
		
	    $total = $error + $file;
	    if ($error > 0) {
	        rsgInstall::writeInstallMsg(JText::_('COM_RSGALLERY2_MIGRATE_NOT_ALL_GAL')."<strong>$file</strong>".JText::_('COM_RSGALLERY2_OUT_OF')."<strong>$processed</strong>".JText::_('COM_RSGALLERY2_ENTRIES_PROCESSED'),"error");
		} else {
	        rsgInstall::writeInstallMsg(JText::_('COM_RSGALLERY2_ALL_GALLERY_INFORMATION_MIGRATED_TO_RSGALLERY2_DATABASE')."<strong>$file</strong>".JText::_('COM_RSGALLERY2_ENTRIES_PROCESSED'),"ok");
	    }
    }
    
    /**
     * Migrates item information of Easy Gallery to RSGallery2
     * Easy Gallery stores the filename, including the path in one field.
     * We need to retrieve the filename, without the path to be able to
     * store the filename in the DB
     * 
     * @param string Old files tablename
     * @param string Old image name
     * @param string Old image filename
     * @param timestamp Old image date
     * @param string Old description
     * @param integer Old User ID
     * @param integer Old category ID
     * @param integer Highest value in new table
     */
    static function migrateItems($old_table, $old_image_name, $old_image_filename, $old_image_date, $old_description, $old_uid, $old_catid, $max_id, $prefix) {
		$database = JFactory::getDBO();
	    //Set variables
	    $error = 0;
	    $file = 0;
	    
	    //Get all information from images table
	    $sql = "SELECT * FROM `$old_table`";
	    $database->setQuery($sql);
	    $old = $database->loadObjectList();
	    
	    foreach ($old as $row) {
	        //Retrieve correct filename, without path information
	        $filename 	= array_reverse( explode("/", $row->$old_image_filename) );
	        $filename   = $database->quote($prefix.$filename[0]);
	        $imagename  = $database->quote($row->$old_image_name);
	        $date       = $database->quote($row->$old_image_date);
	        $descr      = $database->quote($row->$old_description);
	        $uid        = (int) $row->$old_uid;
	        $catid      = (int) $row->$old_catid + $max_id;
			$alias		= $database->quote(JFilterOutput::stringURLSafe($imagename));
	        
	        //Insert data into RSGallery2 files table
	        $sql2 = "INSERT INTO #__rsgallery2_files ".
	                "(name, descr, title, date, userid, gallery_id, alias) VALUES ".
	                "('$filename', '$descr', '$imagename', '$date', '$uid', '$catid', '$alias')";
	        $database->setQuery($sql2);
	        
	        //Error and file counting
	        if (!$database->execute()) {
	            $error++;
	        } else {
	            $file++;
	        }
		}
	    $total = $error + $file;
	    if ($error > 0) {
	        rsgInstall::writeInstallMsg(JText::_('COM_RSGALLERY2_MIGRATE_NOT_ALL')."<strong>$file</strong>".JText::_('COM_RSGALLERY2_OUT_OF')."<strong>$total</strong>".JText::_('COM_RSGALLERY2_ENTRIES_PROCESSED'),"error");
		} else{
	        rsgInstall::writeInstallMsg(JText::_('COM_RSGALLERY2_ALL_FILE_INFORMATION_MIGRATED_TO_RSGALLERY2_DATABASE')."<strong>$file</strong>".JText::_('COM_RSGALLERY2_ENTRIES_PROCESSED'),"ok");
		}
	}
	
	/**
	 * Copies original images from Pony Gallery to the RSGallery2 file structure
	 * and then creates display and thumb images.
	 * @param string full path to the original Pony Images
	 * @return True id successful, false if not
	 */
	static function copyImages($basedir, $prefix = "easy_"){
        global $rsgConfig;
        $database = JFactory::getDBO();
        
        $sql = "SELECT * FROM #__easygallery";
        $database->setQuery( $sql );
        $result = $database->loadObjectList();
        $i = 0;
        foreach ($result as $image) {
        	$source 		= $basedir ."/" . $image->path;
        	$filename = array_reverse( explode("/", $image->path) );
        	$destination	= JPATH_ORIGINAL . "/" . $prefix.$filename[0];

			//First move image to original folder
			
        	$newpath = fileUtils::move_uploadedFile_to_orignalDir($source, $destination);
        	if ($newpath) {
        		imgUtils::makeDisplayImage($newpath, '', $rsgConfig->get('image_width'));
        		imgUtils::makeThumbImage($newpath);
        	} else {
        		$i++;
        	}
        }
		if ($i > 0) {
			return false;
		} else {
			return true;
		}
    }
}

/**
 * rsgallery migrator
 * @package RSGallery2
 */
class migrate_com_rsgallery extends GenericMigrator {
    /* public functions */
    
    /**
     * @return string containing the technical name.  no spaces, special characters, etc allowed as this will be used in GET/POST.  advisable to use the class name.  we would just use get_class(), but it's implementation is differs in PHP 4 and 5.
     */
    static function getTechName(){
        return 'com_rsgallery';
    }

    /**
     * @return String containing name and version(s) of which gallery this class migrates
     */
    static function getName(){
        return 'RSGallery2 1.10.2+';
    }

	/**
     * detect if the gallery version this class handles is installed
     * @return true or false
     */
	static function detect(){
		$database = JFactory::getDBO();
		
		if( in_array( $database->getPrefix().'rsgallery2_config', $database->getTableList() ) === false ){ 
            // rsgallery2_config table does not exist
            return false;
        } else {
        	// if #__rsgallery2_config exists, then we can handle the upgrade
        	return true;
        }
    }
	/**
	* do the migration thing
	* @return true on success, anything else a failure
	*/
	function migrate(){
		global $rsgConfig;
		$database = JFactory::getDBO();
		
		// in versions prior to 1.11.0, if the config had never been saved, no variables (including the version) would exist
		// if this is the case, we set the version to something appropiate
		$database->setQuery( "SELECT * FROM #__rsgallery2_config" );
		$database->execute();
		if( $database->getNumRows() == 0 )
			$rsgConfig->set( 'version', '1.10.?' );

		// match version numbers.  each update is applied successively until finished.
		// this will happen because there are no break statements

		switch( true ){
			case $this->beforeVersion( '1.11.0' ):
				$this->handleSqlFile( 'upgrade_1.10.14_to_1.11.0.sql' );

			case $this->beforeVersion( '1.11.1' ):
				$this->handleSqlFile( 'upgrade_1.11.0_to_1.11.1.sql' );

			case $this->beforeVersion( '1.11.8' ):
				$this->handleSqlFile( 'upgrade_1.11.7_to_1.11.8.sql' );

			case $this->beforeVersion( '1.11.11' ):
				$this->handleSqlFile( 'upgrade_1.11.10_to_1.11.11.sql' );

			case $this->beforeVersion( '1.12.0' ):
				$this->handleSqlFile( 'upgrade_1.11.11_to_1.12.0.sql' );

			case $this->beforeVersion( '1.12.2' ):
				$this->upgradeTo_1_12_2();

			case $this->beforeVersion( '1.13.2' ):
				$this->handleSqlFile( 'upgrade_1.12.2_to_1.13.2.sql' );

			case $this->beforeVersion( '1.14.0' ):
				$this->handleSqlFile( 'upgrade_1.13.2_to_1.14.0.sql' );
			
			case $this->beforeVersion( '1.14.1' ):
				$this->handleSqlFile( 'upgrade_1.14.0_to_1.14.1.sql' );
			
			case $this->beforeVersion( '2.2.1' ):
				$this->handleSqlFile( 'upgrade_2.2.0_to_2.2.1.sql' );
				$this->upgradeTo_2_2_1();
			
			case $this->beforeVersion( '3.0.0' ):
				$this->handleSqlFile( 'upgrade_2.x.x_to_3.0.0.sql' );
				
			case $this->beforeVersion( '3.0.2' ):
				$this->handleSqlFile( 'upgrade_3.0.0_to_3.0.2.sql' );
				rsgInstall::writeInstallMsg( JText::_('COM_RSGALLERY2_UPDATEINFO_302'), 'ok');
			
			case $this->beforeVersion( '3.1.1' ):
				$this->handleSqlFile( 'upgrade_3.1.0_to_3.1.1.sql' );
				
			case $this->beforeVersion( '3.1.1' ):
				$this->handleSqlFile( 'upgrade_3.1.0_to_3.1.1.sql' );
				
			case $this->beforeVersion( '3.2.0' ):
				$this->upgradeTo_3_2_0();
			
			default:
				// if we reach this point then everything was a success, update the version number and exit.
				$this->updateVersionNumber();
				return true;
		}
	}

    /**
     * check if installed version is less than (before) $ver
     * @param string version to check against
     * @return true if installed version is less than $ver otherwise false
     */
    static function beforeVersion( $ver ){
        global $rsgConfig;

        // version in existing database (the version we are migrating from)
        // get version number as an array, with major, minor and revision numbers being keyed 0, 1, 2 respectively.
        $installedVer = explode( '.', $rsgConfig->get( 'version' ) );

        $ver = explode( '.', $ver );

        // check major versions
        if( $installedVer[0] < $ver[0] )
            return true;
        else if( $installedVer[0] > $ver[0] )
            return false;

        // major versions match, check minor versions
        if( $installedVer[1] < $ver[1] )
            return true;
        else if( $installedVer[1] > $ver[1] )
            return false;

        // minor versions match, check revision
        if( $installedVer[2] < $ver[2] )
            return true;
        else if( $installedVer[2] > $ver[2] )
            return false;

        // version numbers match exactly
        return false;
    }

    /**
     * updates the version number in database to the hardcoded version number
     */
    static function updateVersionNumber(){
        global $rsgConfig;

        $rsgConfig->set( 'version', $rsgConfig->getDefault( 'version' ));
        $rsgConfig->saveConfig();
    }

    /** special upgrade handling for various versions is below **/

    /**
     * in some version prior to 1.12.2 #__rsgallery2_acl was hardcoded with the prefix jos.
     * if Joomla! was installed using a different prefix then #__rsgallery2_acl will be missing.
     * @todo this needs to be tested
     */
    function upgradeTo_1_12_2(){
        global $mosConfig_dbprefix;
		$database = JFactory::getDBO();
		
        if( $mosConfig_dbprefix == 'jos_' )
            return;  // prefix is jos, so it doesn't matter.

        if( in_array( $mosConfig_dbprefix.'rsgallery2_acl', $database->getTableList() ) === false ){
            // #__rsgallery2_acl does not exist

            // first we create the table
            $this->handleSqlFile( 'upgrade_1.12.1_to_1.12.2.sql' );

            // now remove jos_rsgallery2_acl if it does not belong
            // we only want to do this if it is empty and there is no other joomla installed using jos_
            $database->setQuery( "SHOW TABLES LIKE 'jos_content'" );
            $database->execute();
            if( $database->getNumRows() == 1 ) return; // joomla using jos_ exists

            $database->setQuery( "SELECT * FROM `jos_rsgallery2_acl`" );
            $database->execute();
            if( $database->getNumRows() > 0 ) return; // table not empty, leave it alone

            $database->setQuery( "DROP TABLE `jos_rsgallery2_acl`" );
            $database->execute();
        }
    }
	static function upgradeTo_2_2_1(){
		//There is a new field 'alias in tables #__rsgallery2_galleries and 
		// #__rsgallery2_files and it needs to be filled as our SEF router uses it
		$error = false;
		$db = JFactory::getDBO();
		
		//Get id, name for the galleries
		$query = 'SELECT id, name FROM #__rsgallery2_galleries';
		$db->setQuery($query);
		$result = $db->loadAssocList();
		//...and make alias from name
		foreach ($result as $key => $value) {
			jimport( 'joomla.filter.filteroutput' );
			$result[$key][alias] = JFilterOutput::stringURLSafe($value[name]);
		}
		//save the alias
		foreach ($result as $key => $value) {
			$query = 'UPDATE #__rsgallery2_galleries '
					.' SET `alias` = '. $db->quote($value[alias])
					.' WHERE `id` = '. (int) $value[id];
			$db->setQuery($query);
			$result = $db->execute();
			if (!$result) {
				$msg = JText::_('COM_RSGALLERY2_MIGRATE_ERROR_FILLING_ALIAS_GALLERY',$value[id], $value[name]);
				JError::raiseNotice( 100, $msg);
				$error = true;
			}
		}
	
		//Get id, title for the items
		$query = 'SELECT id, title FROM #__rsgallery2_files';
		$db->setQuery($query);
		$result = $db->loadAssocList();
		//...and make alias from title
		foreach ($result as $key => $value) {
			jimport( 'joomla.filter.filteroutput' );
			$result[$key][alias] = JFilterOutput::stringURLSafe($value[title]);
		}
		//save the alias
		foreach ($result as $key => $value) {
			$query = 'UPDATE #__rsgallery2_files '
					.' SET `alias` = '. $db->quote($value[alias])
					.' WHERE `id` = '. (int) $value[id];
			$db->setQuery($query);
			$result = $db->execute();
			if (!$result) {
				$msg = JText::_('COM_RSGALLERY2_MIGRATE_ERROR_FILLING_ALIAS_ITEM',$value[id], $value[title]);
				JError::raiseNotice( 100, $msg);
				$error = true;
			}
		}
		if ($error) {
			rsgInstall::writeInstallMsg(JText::_('COM_RSGALLERY2_FINISHED_CREATING_ALIASES'), 'error');
		} else {		
			rsgInstall::writeInstallMsg(JText::_('COM_RSGALLERY2_FINISHED_CREATING_ALIASES'), 'ok');
		}
	}
	static function upgradeTo_3_2_0(){
		// Change comments in table from BB Code to HTML
		$database = JFactory::getDBO();

		$query = 'SELECT id, comment FROM #__rsgallery2_comments';
		$database->setQuery( $query );
		$comments = $database->loadAssocList();

		$rsgComment = new rsgComments();

		foreach($comments as $comment) {
			//Parse BBCode comment to HTML comment
			$comment['comment'] = $rsgComment->parse( $comment['comment']);
			//Strip HTML tags with the exception of these allowed tags: line break, paragraph, bold, italic, underline, link image (for smileys) and allowed attribute: link, src; then clean comment.
			$allowedTags = array('strong','em','a','img','b','i','u');
			$allowedAttribs = array('href','src');
			$filter = & JFilterInput::getInstance($allowedTags,$allowedAttribs);
			@$comment['comment'] = $filter->clean($comment['comment']);
			// Update comment in table
			$query = 'UPDATE #__rsgallery2_comments SET comment  = '.$database->Quote($comment['comment']).' where id ='. (int) $comment['id'];
			$database->setQuery( $query );
			$result = $database->execute();
		}
	} // end of function upgradeTo_3_2_0
}	//end class migrate_com_rsgallery

/**
 * convert from flat gallery to hierarchical folder sturcture
 * @author John Caprez (john@swizzysoft.com)
 **/
class migrate_com_rsgallery_flat_structure extends GenericMigrator {
// TODO:implement	
}

/**
 * convert from hierarchical gallery to flat folder sturcture
 * @author John Caprez (john@swizzysoft.com)
 **/
class migrate_com_rsgallery_hierarchical_structure extends GenericMigrator {
	// TODO:implement	
}

/**
 * (Stripped) Class for the comments plugin - only here for converting comments from 2.2.1 to 2.3.0
 * @author Ronald Smit <ronald.smit@rsdev.nl>
 */
if (!class_exists('rsgComments')) {
class rsgComments {
	var $_buttons;
	var $_emoticons;
	/**
	 * Constructor
	 */
	function rsgComments() {
		global $mainframe;
		$this->_buttons = array(
		"b" 	=> "ubb_bold.gif",
		"i" 	=> "ubb_italicize.gif",
		"u" 	=> "ubb_underline.gif",
		"url" 	=> "ubb_url.gif",
		"quote" => "ubb_quote.gif",
		"code" 	=> "ubb_code.gif",
		"img" 	=> "ubb_image.gif"
		);
		$this->_emoticons = array(
		":D" 			=> "icon_biggrin.gif",
		":)" 			=> "icon_smile.gif",
		":(" 			=> "icon_sad.gif",	
		":O" 			=> "icon_surprised.gif",
		":shock:" 		=> "icon_eek.gif",
		":confused:" 	=> "icon_confused.gif",
		"8)" 			=> "icon_cool.gif",
		":lol:" 		=> "icon_lol.gif",
		":x" 			=> "icon_mad.gif",
		":P" 			=> "icon_razz.gif",
		":oops:" 		=> "icon_redface.gif",
		":cry:" 		=> "icon_cry.gif",
		":evil:" 		=> "icon_evil.gif",
		":twisted:" 	=> "icon_twisted.gif",
		":roll:" 		=> "icon_rolleyes.gif",
		":wink:" 		=> "icon_wink.gif",
		":!:" 			=> "icon_exclaim.gif",
		":?:" 			=> "icon_question.gif",
		":idea:" 		=> "icon_idea.gif",
		":arrow:" 		=> "icon_arrow.gif"
		);	
		$this->_emoticons_path 		= JURI_SITE."/components/com_rsgallery2/lib/rsgcomments/emoticons/default/";
	}
	
	/**
	 * Retrieves raw text and converts bbcode to HTML
	 */
	static function parseUBB($html, $hide = 0) {	//needed
		$html = str_replace(']www.', ']http://www.', $html);
		$html = str_replace('=www.', '=http://www.', $html);
		$patterns = array('/\[b\](.*?)\[\/b\]/i',
			'/\[u\](.*?)\[\/u\]/i',
			'/\[i\](.*?)\[\/i\]/i',
			'/\[url=(.*?)\](.*?)\[\/url\]/i',
			'/\[url\](.*?)\[\/url\]/i',
			'#\[email\]([a-z0-9\-_.]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]+)\[/email\]#',
			'#\[email=([a-z0-9\-_.]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]+)\](.*?)\[/email\]#',
			'/\[font=(.*?)\](.*?)\[\/font\]/i',
			'/\[size=(.*?)\](.*?)\[\/size\]/i',
			'/\[color=(.*?)\](.*?)\[\/color\]/i');
		$replacements = array('<b>\\1</b>',
			'<u>\\1</u>',
			'<i>\\1</i>',
			'<a href=\'\\1\' title=\'Visit \\1\'>\\2</a>',
			'<a href=\'\\1\' title=\'Visit \\1\'>\\1</a>',
			'<a href=\'mailto:\\1\'>\\1</a>',
			'<a href=\'mailto:\\1\'>\\3</a>',
			'<span style=\'font-family: \\1\'>\\2</span>',
			'<span style=\'font-size: \\1\'>\\2</span>');
		if ($hide) 
			$replacements[] = '\\2';
		else 
			$replacements[] = '<span style=\'color: \\1\'>\\2</span>';
		$html = preg_replace($patterns, $replacements, $html);
		return $html;
    }

	/**
	 * Replaces emoticons code with emoticons 
	 */
	function parseEmoticons($html) { //needed
		foreach ($this->_emoticons as $ubb => $icon) {
			$html = str_replace($ubb, "<img src='" . $this->_emoticons_path . $icon . "' border='0' alt='' />", $html);
		}
		return $html;
	}

	/**
	 * Parses an image element to HTML
	 */
	static function parseImgElement($html) {	//needed
			return preg_replace('/\[img\](.*?)\[\/img\]/i', '<img src=\'\\1\' alt=\'Posted image\' />', $html);
	}

	/**
	 * Parse a quote element to HTML
	 */
	static function parseQuoteElement($html) {	//needed
        $q1 = substr_count($html, "[/quote]");
        $q2 = substr_count($html, "[quote=");
        if ($q1 > $q2) $quotes = $q1;
        else $quotes = $q2;
        $patterns = array("/\[quote\](.+?)\[\/quote\]/is",
            "/\[quote=(.+?)\](.+?)\[\/quote\]/is");
        $replacements = array(
						"<div class='quote'><div class='genmed'><b>".JText::_('Quote')."</b></div><div class='quotebody'>\\1</div></div>",
            			"<div class='quote'><div class='genmed'><b>\\1".JText::_('Wrote')."</b></div><div class='quotebody'>\\2</div></div>"
            			);
        while ($quotes > 0) {
            $html = preg_replace($patterns, $replacements, $html);
            $quotes--;
        }
        return $html;
    }

	function parseCodeElement($html) {	//needed
		if (preg_match_all('/\[code\](.+?)\[\/code\]/is', $html, $replacementI)) {
			foreach($replacementI[0] as $val) $html = str_replace($val, $this->code_unprotect($val), $html);
		}
		$pattern = array();
		$replacement = array();
		$pattern[] = "/\[code\](.+?)\[\/code\]/is";
		$replacement[] = "<div class='code'><div class='genmed'><b>".JText::_('Code')."</b></div><div class='codebody'><pre>\\1</pre></div></div>";
		return preg_replace($pattern, $replacement, $html);
    }

	/**
	 * Parse a BB-encoded message to HTML
	 */
	function parse( $html ) {	//needed
		$html = $this->parseEmoticons($html);
        $html = $this->parseImgElement($html);
		$html = $this->parseUBB($html, 0);
		$html = $this->parseCodeElement($html);
		$html = $this->parseQuoteElement($html);
		$html = stripslashes($html);
		return str_replace('&#13;', "\r", nl2br($html));
    }
	
	static function code_unprotect($val) {	//needed
		$val = str_replace("{ : }", ":", $val);
		$val = str_replace("{ ; }", ";", $val);
		$val = str_replace("{ [ }", "[", $val);
		$val = str_replace("{ ] }", "]", $val);
		$val = str_replace(array("\n\r", "\r\n"), "\r", $val);
		$val = str_replace("\r", '&#13;', $val);
		return $val;
    }
}	//end class rsgComments
}

?>