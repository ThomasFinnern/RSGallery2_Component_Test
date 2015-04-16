<?php
/**
* Textfile for "deprecated" tasks to do or done for RSGallery2
* @version $Id: Deprecated.txt.php 1085 2014-03-22 13:44:29Z whazzup $
* @package RSGallery2
* @copyright (C) 2003 - 2006 RSGallery2
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* RSGallery is Free Software
*/
defined( '_JEXEC' ) or die();

/**
 * Header included for use of JED Checker
 * Textfile for "deprecated" tasks to do or done for RSGallery2
 * 
 */ 

 
 
 

In Joomla 2.5 Backend > Extensions > Plug-in Manager > Plugin: System - Debug > Setting: Logging: Log deprecated API = Yes, gives us a file with notices about functions used that are deprecated in J2.5 (and thus will not be available in J3).
- Googling notices might give info on what to change where and how to change it
- Finding out which function triggers such notice ("find in files" of (part of) the text on Joomla's code) might give some insight in what to change
=> A start is made here with a TODO and a DONE list, including information on what to change

TODO
? JDatabase::getErrorMsg() is deprecated, use exception handling instead. ==> J3 is still using this?
? JError::getError() is deprecated. ==> J3 is still using this?
! JAccess::getActions is deprecated. Use JAccess::getActionsFromFile or JAcces::getActionsFromData instead. ==> check this first in Eclipse (some info already below) (log entry is created in this file: /libraries/joomla/access/access.php)
Remove Template Manager button (since it hasn't worked since J15)
Remove index2 references
JParameter::render is deprecated. & JParameter::getParams is deprecated.
Strict warnings...
Is this in RSG2? JFactory::getXMLParser() is deprecated.
JElement::fetchTooltip is deprecated.
JElementText::_fetchElement() is deprecated.
JSimpleXML::__construct() is deprecated. (gallery/item edit view)
	JSimpleXML::loadfile() is deprecated.
	JSimpleXML::_parse() is deprecated.
	JSimpleXML::startElement() is deprecated.
	JSimpleXMLElement::__construct() is deprecated.
	JSimpleXML::_characterData() is deprecated.
	JSimpleXML::_getStackLocation() is deprecated.
	JSimpleXML::startElement() is deprecated.
	JSimpleXML::_getStackLocation() is deprecated.
	JSimpleXMLElement::addChild() is deprecated.
	JSimpleXMLElement::__construct() is deprecated.


DONE
* Files in the format admin.COMPONENTNAME.php are considered deprecated and will not be loaded in Joomla 3.0. -- just renamed it to COMPONENTNAME.php
* Use of constant DS is removed in J3
<?php	if(!defined('DS')){
			define('DS',DIRECTORY_SEPARATOR);
		}?>
* Use JHtmlTabs in J3 instead of JPane:
JPane::getInstance is deprecated.
JPanelSliders::__construct is deprecated.
JPaneSliders::_loadBehavior is deprecated.
JPaneSliders::startPane is deprecated.
JPaneSliders::startPanel is deprecated.
JPaneSliders::endPanel is deprecated.
JPaneTabs is deprecated.
JPaneTabs::_loadBehavior is deprecated.
JPane::startPane is deprecated.
JPaneTabs::startPanel is deprecated.
JPaneTabs::endPanel is deprecated.
JPaneTabs::startPanel is deprecated.
JPaneTabs::endPane is deprecated.
<?php 
	$options = array(
		'onActive' => 'function(title, description){
			description.setStyle("display", "block");
			title.addClass("open").removeClass("closed");
		}',
		'onBackground' => 'function(title, description){
			description.setStyle("display", "none");
			title.addClass("closed").removeClass("open");
		}',
		'startOffset' => 0,  // 0 starts on the first tab, 1 starts the second, etc...
		'useCookie' => true, // this must not be a string. Don't use quotes.
	);
	 
	echo JHtml::_('tabs.start', 'tab_group_id', $options);
	 
	echo JHtml::_('tabs.panel', JText::_('PANEL_1_TITLE'), 'panel_1_id');
	echo 'Panel 1 content can go here.';
	 
	echo JHtml::_('tabs.panel', JText::_('PANEL_2_TITLE'), 'panel_2_id');
	echo 'Panel 2 content can go here.';
	 
	echo JHtml::_('tabs.end');
?>
*JDatabase::getEscaped() is deprecated. Use JDatabase::escape().
*JBehavior::mootools is deprecated. == JHtml::_("behavior.mootools");
In Joomla 1.5, if you needed load MooTools libraries, you needed add a line in your code something like this:
<?php JHtml::_('behavior.mootools');?>
And for backward compatibility purpose in Joomla 1.6, 1.7 and 2.5 this line works, too.
But in Joomla 3.0, it’s been deprecated and you need to use the new standard. For adding MooTools Core libraries you need to add:
<?php JHtml::_('behavior.framework');?>
and if you want to add both MooTools Core and MooTools More libraries, you need to add:
<?php JHtml::_('behavior.framework', true);?>
*JApplicationHelper::parseXMLInstallFile is deprecated. Use JInstaller::parseXMLInstallFile instead.
*JAccess::getActions is deprecated. Use JAccess::getActionsFromFile or JAcces::getActionsFromData instead.
function getActions does this: (where <?php $section = 'component'?>)
<?php	$actions = self::getActionsFromFile(
            JPATH_ADMINISTRATOR . '/components/' . $component . '/access.xml',
            "/access/section[@name='" . $section . "']"
        ); ?>
so use (where <?php $component = 'com_rsgallery2'?> which has sections 'component', 'gallery' and 'item'!
<?php	$actions = self::getActionsFromFile(
            JPATH_ADMINISTRATOR . '/components/' . $component . '/access.xml'); ?>
RSGallery2 has "public static function getActions($galleryId = 0)" where 
<?php	if (empty($galleryId)) {
			$assetName = 'com_rsgallery2';
		} else {
			$assetName = 'com_rsgallery2.gallery.'.(int) $galleryId;
		}?>
so here the $section is not always $component ($assetName here).
Assets are com_rsgallery2, com_rsgallery2.gallery.1 (etc) and com_rsgallery2.item.1 (etc).
*JImage::site is deprecated. (funcion, filename, path, ?, ?, alt)
<?php echo JHtml::_('image.site', $image, '/components/com_rsgallery2/images/', NULL , NULL , $text);?>
becomes (path including filename, alt)
<?php echo JHtml::image('administrator/components/com_rsgallery2/images/'.$image, $text); ?>
*Files in the format toolbar.COMPONENTNAME.php are considered deprecated and will not be loaded in Joomla 3.0. ==> not autoloaded, so do it ourselves
not <?php require_once( JApplicationHelper::getPath('toolbar') );?>
<?php require_once( '/components/com_rsgallery2/toolbar.rsgallery2.php');?>
and JApplicationHelper::getPath no longer exists so
<?php require_once( JApplicationHelper::getPath('toolbar_html') );///J25?>
becomes
<?php require_once( '/components/com_rsgallery2/toolbar.rsgallery2.html.php');///J3?>
*JDatabase::loadResultArray() is deprecated. Use JDatabase::loadColumn(). (find and replace :-)
*Optimise database: Cannot redeclare class rsgComments in D:\website_spul\xampp177\htdocs\joomla25\administrator\components\com_rsgallery2\includes\install.class.php on line 2354
*Added administrator section
	<install folder="admin">
		<sql>
			<file driver="mysql" charset="utf8">sql/rsgallery2.sql</file>
		</sql>
	</install> 	
to the manifest