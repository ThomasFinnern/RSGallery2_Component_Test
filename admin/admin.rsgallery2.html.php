<?php
/**
* This file handles the HTML processing for the Admin section of RSGallery.
*
* @version $Id: admin.rsgallery2.html.php 1090 2012-07-09 18:52:20Z mirjam $
* @package RSGallery2
* @copyright (C) 2003 - 2012 RSGallery2
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* RSGallery is Free Software
*/

defined( '_JEXEC' ) or die();


/**
* The HTML_RSGALLERY class is used to encapsulate the HTML processing for RSGallery.
* @package RSGallery2
* @todo Move this class to a separate class file and add loose functions to it
**/
class HTML_RSGALLERY{

    
    /**
     * use to print a message between HTML_RSGallery::RSGalleryHeader(); and a normal feature.
     * use for things like deleting an image, where a success message should be displayed and viewImages() called aferwards.
     * two css classes are used: rsg-admin-msg, rsg-admin-msg-important
     * this function replaces newlines with <br> for convienence.
     *  
     * @todo implement css classes in css file
     *  
     * @param string $msg message to print
     * @param bool $important optionally display the message as important, possibly changing the text to red or bold, etc.  as a general rule, expected results should be normal, unexpected results should be marked important.
     */
    static function printAdminMsg($msg, $important=false) {
        // replace newlines with html line breaks.
        str_replace('\n', '<br>', $msg);
        
        if($important)
            echo "<p class='rsg-admin-msg'>$msg</p>";
        else
            echo "<p class='rsg-admin-msg-important message'>$msg</p>";
    }
    
    /**
      * Used by showCP to generate buttons
      * @param string $link URL for button link
      * @param string $image Image name for button image
      * @param string $text Text to show in button
      */
    static function quickiconButton( $link, $image, $text ) {
		?>
        <div style="float:left;">
        <div class="icon">
            <a href="<?php echo $link; ?>">
                <div class="iconimage">
					<?php echo JHtml::image('administrator/components/com_rsgallery2/images/'.$image, $text); ?>
                </div>
                <?php echo $text; ?>
            </a>
        </div>
        </div>
        <?php
    }
    
    /**
      * Used by showCP to generate buttons
     * @param string $link URL for button link
     * @param string $image Image name for button image
     * @param string $text Text to show in button
      */
    static function quickiconDebugButton($Id, $link, $image, $text ) {
		?>
        <div style="float:left;">
            <div class="debugicon">
                <a href="<?php echo $link; ?>" class="<?php echo $Id; ?>">
                    <div class="iconimage">
                        <?php echo JHtml::image('administrator/components/com_rsgallery2/images/'.$image, $text); ?>
                    </div>
                    <?php echo $text; ?>
                </a>
            </div>
        </div>
        <?php
    }
    
    /**
     * Shows the RSGallery control panel in backend.
     * @todo complete translation tags
     * @todo use div in stead of tables (LOW PRIORITY)
     * @todo Move CSS to stylesheet
     */
    static function showCP(){
        //global  $rows, $rows2, $rsgConfig, $rsgVersion;
        global  $rsgConfig, $rsgVersion;

        // Get the current JUser object
		$user = JFactory::getUser();
		$canDo	= Rsgallery2Helper::getActions();
        $doc = JFactory::getDocument();

		JToolBarHelper::title( JText::_('COM_RSGALLERY2_RSG2_CONTROL_PANEL'), 'generic.png' );

        //Show Warning box if some preconditions are not met
        galleryUtils::writeWarningBox();

        // Purge / delete of database variables should be confirmed
        $script = "
            jQuery(document).ready(function($){
                $('.purgeEverything').on('click', function () {
                    return confirm('" . JText::_('COM_RSGALLERY2_CONFIRM_PURGE_TABLES_DEL_IMAGES') . "');
                });

                $('.reallyUninstall').on('click', function () {
                    return confirm('" . JText::_('COM_RSGALLERY2_CONFIRM_DELETE_TABLES_IMAGES')  . "');
                });
            });
        ";
        $doc->addScriptDeclaration($script);

        ?>

			<?php if (count(JHtmlSidebar::getEntries()) > 0) : ?>
                <div id="j-sidebar-container" class="span2">
                    <?php echo JHtmlSidebar::render( ); ?>
                </div>
                <div id="j-main-container" class="span12">
            <?php else : ?>
                <div id="j-main-container">
            <?php endif;?>
		
            <div class="clearfix"> </div>
				<!-- -->
		
        <div id="rsg2-thisform">
            <div id='rsg2-infoTabs'>
                <table width="100%">
                    <tr>
                        <td>
                    <?php
						jimport( 'joomla.html.html.tabs' );
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
						echo JHtml::_('tabs.start', 'recent', $options);
						//echo JHtml::_('tabs.panel', JText::_('Categories'), 'panel_1_id');
						echo JHtml::_('tabs.panel', JText::_('JCATEGORIES'), 'panel_1_id');					
                        ?>
                        <table class="adminlist" width="500">
                            <tr>
                                <th colspan="3"><?php echo JText::_('COM_RSGALLERY2_MOST_RECENTLY_ADDED_GALLERIES'); ?></th>
                            </tr>
                            <tr>
                                <td><strong><?php echo JText::_('COM_RSGALLERY2_GALLERY'); ?></strong></td>
                                <td><strong><?php echo JText::_('COM_RSGALLERY2_USER'); ?></strong></td>
                                <td><strong><?php echo JText::_('COM_RSGALLERY2_ID'); ?></strong></td>
                            </tr>
                            <?php // echo galleryUtils::latestCats();
                                galleryUtils::latestCats();
                            ?>
                            <tr>
                                <th colspan="3">&nbsp;</th>
                            </tr>
                        </table>
                        <?php
							echo JHtml::_('tabs.panel', JText::_('COM_RSGALLERY2_ITEMS'), 'Images');
						?>
                        <table class="adminlist" width="500">
                            <tr>
                                <th colspan="4"><?php echo JText::_('COM_RSGALLERY2_MOST_RECENTLY_ADDED_ITEMS'); ?></th>
                            </tr>
                            <tr>
                                <td><strong><?php echo JText::_('COM_RSGALLERY2_FILENAME');?></strong></td>
                                <td><strong><?php echo JText::_('COM_RSGALLERY2_GALLERY');?></strong></td>
                                <td><strong><?php echo JText::_('COM_RSGALLERY2_DATE'); ?></strong></td>
                                <td><strong><?php echo JText::_('COM_RSGALLERY2_USER'); ?></strong></td>
                            </tr>
                            <?php galleryUtils::latestImages();?>
                            <tr>
                                <th colspan="4">&nbsp;</th>
                            </tr>
                        </table>
                        <?php
						echo JHtml::_('tabs.panel', JText::_('COM_RSGALLERY2_CREDITS'), 'Credits');
					?>

                        
<div id='rsg2-credits'>
    <h3>Core Team - RSGallery2 4.x</h3>
	<h4>(Joomla 3.x)</h4>
    <dl>
		<dt>2015 - </dt>
			<dd><b>Johan Ravenzwaaij</b></dd>
			<dd><b>Mirjam Kaizer</b></dd>
			<dd><b>Thomas Finnern</b></dd>
    </dl>
    
	<h3>RSGallery2 3.x (Joomla 1.6/1.7/2.5)</h3>
    <dl>
		<dt>2011-2014</dt>
        	<dd><b>Johan Ravenzwaaij</b></dd>
            <dd><b>Mirjam Kaizer</b></dd>
    </dl>
    
    <h3>Translations</h3>	
    <dl>
        <dt>Brazilian Portuguese</dt> 
			<dd><b>Helio Wakasugui</b></dd>
		<dt>Croatian</dt> 
			<dd><b>Tanja</b></dd>
        <dt>Czech</dt> 
			<dd><b>David Zirhut</b> <a href='http://www.joomlaportal.cz/'>joomlaportal.za</a></dd>
			<dd><b>Felix 'eFix' Lauda</b></dd>
        <dt>Dutch</dt>
			<dd><b>Tomislav Ribicic</b></dd>
        	<dd><b>Dani&#235;l Tulp</b> <a href='http://design.danieltulp.nl' target='_blank'></a></dd>
			<dd><b>Bas</b><a href='http://www.fantasea.nl' target='_blank'>http://www.fantasea.nl</a></dd>
        	<dd><b>Mirjam Kaizer</b></dd>
		<dt>Finnish</dt>
			<dd><b>Antti</b></dd>
			<dd><b>Ripley</b></dd>
		<dt>French</dt>
			<dd><b>Fabien de Silvestre</b></dd>
        <dt>German</dt> 
			<dd><b>woelzen</b><a href='http://conseil.silvestre.fr' target='_blank'>http://conseil.silvestre.fr</a></dd>
			<dd><b>chfrey</b></dd>
		<dt>Greek</dt>
			<dd><b>Charis Argyropoulos</b><a href='http://www.symenox.gr' target='_blank'>http://www.symenox.gr</a></dd>
			<dd><b>George Fakas</b></dd>
		<dt>Hebrew</dt>
			<dd><b>Kobi</b></dd>
			<dd><b>theNoam</b><a href='http://www.theNoam.com/' target='_blank'>http://www.theNoam.com/</a></dd>
        <dt>Hungarian</dt> 
			<dd><b>Jozsef Tamas Herczeg</b> <a href='http://www.soft-trans.hu' target='_blank'>SOFT-TRANS</a></dd>
		<dt>Italian</dt>
			<dd><b>Michele Monaco</b><a href='http://www.mayavoyage.com' target='_blank'>Maya Voyages</a></dd>
			<dd><b>Marco Galimberti</b>
			</dd>
        <dt>Norwegian</dt> 
			<dd><b>Ronny Tjelle</b></dd>
            <dd><b>Steinar Vikholt</b></dd>
        <dt>Persian</dt> 
			<dd><b>Joomir</b> <a href='http://www.joomir.com/' target='_blank'>http://www.joomir.com</a></dd>
		<dt>Polish</dt> 
			<dd><b>Zbyszek Rosiek</b></dd>
        <dt>Russian</dt>
			<dd><b>Ragnaar</b></dd>
        <dt>Slovenian</dt>
			<dd><b>Iztok Osredkar</b></dd>
		<dt>Spanish</dt> 
			<dd><b>Eb&auml;vs</b> <a href='http://www.ebavs.net/' target='_blank'>eb&auml;vs.net</a></dd>
        <dt>Traditional Chinese</dt>
			<dd><b>Sun Yu</b><a href='http://www.meto.com.tw' target='_blank'>Meto</a></dd>
			<dd><b>Mike Ho</b> <a href='http://www.dogneighbor.com' target='_blank'>http://www.dogneighbor.com</a></dd>
		<dt>Turkish</dt>
			<dd><b>Pheadrus</b></dd>
    </dl>

    <h3>Logo</h3>
    <dl>
        <dt>Designer</dt> <dd><b>Cory "ccdog" Webb</b> <a href='http://www.corywebb.com/' target='_blank'>CoryWebb.com</a></dd>
    </dl>

	
	<h3>RSGallery2 2.x (Joomla 1.5.x)</h3>
    <dl>
		<dt>2010 (alphabetically)</dt>
        	<dd>Johan Ravenzwaaij</dd>
			<dd>Jonah Braun</dd>
			<dd>Mihir Chhatre <a href="http://www.thoughtfulviewfinder.com">Thoughtfulviewfinder Services </a></dd>
            <dd>Mirjam Kaizer</dd>
        <dt>2008-2009</dt>
		<dt>Project Architect</dt>
        	<dd>Jonah Braun <a href='http://whalehosting.ca/' target='_blank'>Whale Hosting Inc.</a></dd>
        <dt>Developers</dt>
        	<dd>John Caprez</dd>
        <dt>Community Liaison</dt>
        	<dd>Dani&#235;l Tulp <a href='http://design.danieltulp.nl/' target='_blank'>DT^2</a></dd>
    </dl>
	
    <h3>RSGallery2 1.x (Joomla 1.0.x, legacy)</h3>
    <dl>
        <dt>Creator</dt>
        	<dd>Ronald Smit</dd>
        <dt>RSGallery 1.x</dt>
        	<dd>Andy "Troozers" Stewart</dd>
            <dd>Richard Foster</dd>
		<dt>RSGallery2 2005</dt>
			<dd>Dani&#235;l Tulp</dd>
			<dd>Jonah Braun</dd>
			<dd>Tomislav Ribicic</dd>
		<dt>RSGallery2 2006</dt>
			<dd>Dani&#235;l Tulp</dd>
			<dd>Jonah Braun</dd>
			<dd>Ronald Smit</dd>
			<dd>Tomislav Ribicic</dd>
		<dt>RSGallery2 2007</dt>
			<dd>Dani&#235;l Tulp</dd>
			<dd>John Caprez</dd>
			<dd>Jonah Braun</dd>
			<dd>Jonathan DeLaigle</dd>
			<dd>Margo Adams</dd>
			<dd>Ronald Smit</dd>
		<dt>RSGallery2 2008</dt>
			<dd>Dan Shaffer 'chefgroovy'</dd>
		<dt>&nbsp;</dt>
    </dl>

</div>
                        <?php
						echo JHtml::_('tabs.end');
                        ?>                        </td>
                            </tr>
                    <tr>
                      <td>&nbsp;</td>
                    </tr>
                        </table>
            <br />
                <table border="0" width="100%" cellspacing="1" cellpadding="1" style=background-color:#CCCCCC;>
                    <tr>
                        <td bgcolor="#FFFFFF" colspan="2">
                            <img src="<?php echo JURI_SITE;?>/administrator/components/com_rsgallery2/images/rsg2-logo.png" align="middle" alt="RSGallery logo"/>
                        </td>
                    </tr>
                    <tr>
                        <td width="120" bgcolor="#FFFFFF"><strong><?php echo JText::_('COM_RSGALLERY2_INSTALLED_VERSION');?></strong></td>
                        <td bgcolor="#FFFFFF"><a href='index.php?option=com_rsgallery2&task=viewChangelog' title='view changelog'><?php echo $rsgVersion->getVersionOnly(); echo " (".$rsgVersion->getSVNonly().")";?></a></td>
                    </tr>
                    <tr>
                        <td bgcolor="#FFFFFF"><strong><?php echo JText::_('COM_RSGALLERY2_LICENSE')?>:</strong></td>
                        <td bgcolor="#FFFFFF"><a href="http://www.gnu.org/copyleft/gpl.html" target="_blank">GNU GPL</a></td>
                    </tr>
                </table>
            </div>

            <div id='cpanel'>
                <?php
                if ( $canDo->get('core.admin') ){
                    $link = 'index.php?option=com_rsgallery2&rsgOption=config&task=showConfig';
                    HTML_RSGALLERY::quickiconButton( $link, 'config.png',  JText::_('COM_RSGALLERY2_CONFIGURATION') );
                }

                $link = 'index.php?option=com_rsgallery2&rsgOption=galleries';
                HTML_RSGALLERY::quickiconButton( $link, 'categories.png', JText::_('COM_RSGALLERY2_MANAGE_GALLERIES') );

                $link = 'index.php?option=com_rsgallery2&rsgOption=images&task=batchupload';
                HTML_RSGALLERY::quickiconButton( $link, 'upload_zip.png', JText::_('COM_RSGALLERY2_BATCH_UPLOAD') );
                
                $link = 'index.php?option=com_rsgallery2&rsgOption=images&task=upload';
                HTML_RSGALLERY::quickiconButton( $link, 'upload.png', JText::_('COM_RSGALLERY2_UPLOAD') );

				//Java Uploader: not implemented at this point, so removed
                //$link = 'index.php?option=com_rsgallery2&rsgOption=jumploader';
                //HTML_RSGALLERY::quickiconButton( $link, 'upload_zip.png', JText::_('COM_RSGALLERY2_JAVA_UPLOADER') );
                
                $link = 'index.php?option=com_rsgallery2&rsgOption=images&task=view_images';
                HTML_RSGALLERY::quickiconButton( $link, 'mediamanager.png', JText::_('COM_RSGALLERY2_MANAGE_ITEMS') );

                if ( $canDo->get('core.admin') ){
                    /*
                    $link = 'index.php?option=com_rsgallery2&task=consolidate_db';
                    HTML_RSGALLERY::quickiconButton( $link, 'dbrestore.png', JText::_('COM_RSGALLERY2_CONSOLIDATE_DATABASE') );
    				*/
    				$link = 'index.php?option=com_rsgallery2&rsgOption=maintenance';
					HTML_RSGALLERY::quickiconButton( $link, 'maintenance.png', JText::_('COM_RSGALLERY2_MAINTENANCE'));
					
					$link = 'index.php?option=com_rsgallery2&rsgOption=installer';
					HTML_RSGALLERY::quickiconButton( $link, 'template.png', JText::_('COM_RSGALLERY2_TEMPLATE_MANAGER'));
    			}
				
				// Temporary not for v3.2.0: permissions in front end need to be implemented in next release and this message can then be removed.
				if ($user->authorise('core.admin', 	'com_rsgallery2')){
					echo '<div style="clear:left;">';
					echo '<div class="CtrlPanelInfo">';
						echo '<p>';
						echo '<b>Note for the site administrator about RSGallery2 permissions</b><br />';
						echo 'In version 3.2.0 some permissions were added so that the Site Administrator can allow <b>frontend</b> users via My Galleries to delete images/galleries <i>they own</i>, edit the state of images/galleries <i>they own</i> and create images/galleries <i>in galleries they own</i>. Descriptions of these new permissions:
						<ul>
							<li>Create Own: set at component and gallery level. This allows the user to upload images to galleries that he owns and create galleries in parent galleries that he owns.</li>
							<li>Delete Own: set at component, gallery and item level. This allows the user to delete images or galleries that he owns.</li>
							<li>Edit State Own: set at component, gallery and item level. This allows the user to edit the state (published or not) of images or galleries that he owns.</li>
						</ul>
						Note that these three "own" permissions are not implemented in the backend, users logged in via the backend need Create permission, Delete permission or Edit State permission, they will be implemented in the backend in the next release. ("Edit Own" is already implemented in the backend.)';
						echo '</p>';
					echo '</div>';
					echo '<div style="clear:left;">';
				}			
                // if debug is on, display advanced options
                if( ($rsgConfig->get( 'debug' )) AND ( $canDo->get('core.admin') ) ){ ?>
                <div id='rsg2-cpanelDebug'>
                    <div id='DangerZone'>
                        <h3>
                            <?php echo JText::_('COM_RSGALLERY2_DANGER_ZONE');?>
                        </h3>
                    </div>
                    <br/>
					<div id='rsg2-DebugHeader'>
						<strong>
							<?php echo JText::_('COM_RSGALLERY2_C_DEBUG_ON');?>
						</strong>
					</div>
                    <?php
                    $link = 'index.php?option=com_rsgallery2&task=purgeEverything';
					HTML_RSGALLERY::quickiconDebugButton( 'purgeEverything', $link, 'menu.png', JText::_('COM_RSGALLERY2_PURGEDELETE_EVERYTHING') );

                    $link = 'index.php?option=com_rsgallery2&task=reallyUninstall';
					HTML_RSGALLERY::quickiconDebugButton( 'reallyUninstall', $link, 'menu.png', JText::_('COM_RSGALLERY2_C_REALLY_UNINSTALL') );
	
					$link = 'index.php?option=com_rsgallery2&task=config_rawEdit';
					HTML_RSGALLERY::quickiconDebugButton( 'config_rawEdit', $link, 'menu.png', JText::_('COM_RSGALLERY2_CONFIG_-_RAW_EDIT') );
					
					//Moved Migration Options: only show when debug is on since there are only test migration options and four Joomla 1.0.x options.
                    /*
					$link = 'index.php?option=com_rsgallery2&rsgOption=maintenance&task=showMigration';
					HTML_RSGALLERY::quickiconDebugButton( 'showMigration', $link, 'dbrestore.png', JText::_('COM_RSGALLERY2_MIGRATION_OPTIONS') );
                    */
                    $link = 'index.php?option=com_rsgallery2&task=config_dumpVars';
                    HTML_RSGALLERY::quickiconDebugButton( 'config_dumpVars', $link, 'menu.png', JText::_('COM_RSGALLERY2_CONFIG_-_VIEW') );
                    ?>
                    <div class='rsg2-clr'>&nbsp;</div>
                </div>
                <?php } ?>
                <div class='rsg2-clr'>&nbsp;</div>
            </div>
        </div>
        <div class='rsg2-clr'>&nbsp;</div>

		</div> <!-- j-main-container -->

        <?php
    }

    /**
     * @param string $message
     * @param string $title
     * @param string $url
     */
	function showInstallMessage( $message, $title, $url ) {
		// global $PHP_SELF;
		?>
		<table class="adminheading">
		<tr>
			<th class="install">
			<?php echo $title; ?>
			</th>
		</tr>
		</table>

		<table class="adminform">
		<tr>
			<td align="left">
			<strong><?php echo $message; ?></strong>
			</td>
		</tr>
		<tr>
			<td colspan="2" align="center">
			[&nbsp;<a href="<?php echo $url;?>" style="font-size: 16px; font-weight: bold"><?php echo JText::_('COM_RSGALLERY2_CONTINUE')?></a>&nbsp;]
			</td>
		</tr>
		</table>
		<?php
	}


    /**
     * If there are no categories and a user has requested an action that
     * requires a category, this is the error message to display
     */
    static function requestCatCreation(){
		?>
		<script>
	        Joomla.submitbutton = function(pressbutton) {
            if (pressbutton != 'cancel'){
                Joomla.submitform( pressbutton );
                // return;
            } else {
                window.history.go(-1);
                // return;
            }
        }
		</script>

        <table width="100%">
        	<tr>
        		<td width="40%">&nbsp;</td>
        		<td align="center">
			        <table width="100%">
			        	<tr>
			        		<td><h3><?php echo JText::_('COM_RSGALLERY2_CREATE_A_CATEGORY_FIRST');?></h3></td>
			        	<tr>
			        		<td>
                                <div id='cpanel'>
                                    <?php
                                    $link = 'index.php?option=com_rsgallery2&rsgOption=galleries';
                                    HTML_RSGALLERY::quickiconButton( $link, 'categories.png', JText::_('COM_RSGALLERY2_MANAGE_GALLERIES') );
                                    ?>
                                </div>
			        		</td>
			        	</tr>
			        </table>
			    </td>
			    <td width="40%">&nbsp;</td>
			</tr>
		</table>
        <div class='rsg2-clr'>&nbsp;</div>
        <?php
    }

    /**
     * Inserts the HTML placed at the top of all RSGallery Admin pages.
     * @param string $type
     * @param string $text
     */
    static function RSGalleryHeader($type='', $text=''){
        ?>
        <table class="adminheading">
          <tr>
            <td><!--<img src="<?php echo JURI_SITE?>administrator/components/com_rsgallery2/images/rsg2-logo.png" border=0 />--></td>
            <th class='<?php echo $type; ?>'>RSGallery2 <?php echo $text; ?></th>
          </tr>
        </table>
        <?php
    }

    /**
     * Inserts the HTML placed at the bottom of all RSGallery Admin pages.
     */
    static function RSGalleryFooter(){
        global $rsgVersion;
        ?>
        <div class= "rsg2-footer" align="center"><br /><br /><?php echo $rsgVersion->getShortVersion();?></div>
        <div class='rsg2-clr'>&nbsp;</div>
        <?php
    }

    /**
     * Inserts the HTML placed at the bottom of all RSGallery Admin pages.
     */
    static function RSGallerySidebar(){
        if (count(JHtmlSidebar::getEntries()) > 0) : ?>
            <div id="j-sidebar-container" class="span2">
                <?php echo JHtmlSidebar::render( ); ?>
            </div>
            <div id="j-main-container" class="span10">
        <?php else : ?>
            <div id="j-main-container">
        <?php endif;?>
            <div class="clearfix"> </div>
         <?php
    }

    /**
     *
     */
    static function showUploadStep1(){
        ?>
        <script type="text/javascript">
	        Joomla.submitbutton = function(pressbutton) {
            var form = document.form;
            if ( pressbutton == 'controlPanel' ) {
                location = "index.php?option=com_rsgallery2";
                return;
            }

            if ( pressbutton == 'upload' ) {
                // do field validation
                if (form.catid.value == "0")
                    alert( "<?php echo JText::_('COM_RSGALLERY2_YOU_MUST_SELECT_A_GALLERY'); ?>" );
                else
                    form.submit();
            }
        }
        </script>
        
        <table width="100%">
        <tr>
            <td width="300">&nbsp;</td>
            <td>
                <form name="form" action="index.php?option=com_rsgallery2&task=upload" method="post">
                <input type="hidden" name="uploadStep" value="2" />
                <table class="adminform">
                <tr>
                    <th colspan="2"><font size="4"><?php echo JText::_('COM_RSGALLERY2_STEP_1');?></font></th>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td width="200">

                    <?php echo JText::_('COM_RSGALLERY2_PICK_A_GALLERY'); ?>
                    </td>
                    <td>
                        <?php echo galleryUtils::galleriesSelectList(NULL, 'catid', false) ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr class="row1">
                    <td colspan="2"><div style=text-align:center;></div></td>
                </tr>
                </table>
                </form>
               </td>
            <td width="300">&nbsp;</td>
       </tr>
       </table>
        <?php
    }

    /**
     * asks user to choose how many files to upload
     */
    static function showUploadStep2( ){
        //$catid = JRequest::getInt('catid', null); 
		$input =JFactory::getApplication()->input;
		$catid = $input->get( 'catid', null, 'INT');		
        ?>
        <table width="100%">
        <tr>
            <td width="300">&nbsp;</td>
            <td>
                <form name="form" action="index.php?option=com_rsgallery2&task=upload" method="post">
                <input type="hidden" name="uploadStep" value="3" />
                <input type="hidden" name="catid" value="<?php echo $catid; ?>" />
                <table class="adminform">
                <tr>
                    <th colspan="2"><font size="4"><?php echo JText::_('COM_RSGALLERY2_STEP_2');?></font></th>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td width="200">
                    <?php echo JText::_('COM_RSGALLERY2_NUMBER_OF_UPLOADS') ;?>
                    </td>
                    <td>
                    <?php echo JHtml::_("select.integerlist", 1, 25, 1, 'numberOfUploads', 'onChange="form.submit()"', 1 ); ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                </tr>
                <tr class="row1">
                    <td colspan="2"><div style=text-align:center;><input type="submit" value="<?php echo JText::_('COM_RSGALLERY2_NEXT')?>"/></div></td>
                </tr>
                </table>
                </form>
               </td>
            <td width="300">&nbsp;</td>
       </tr>
       </table>
        <?php
    }

    /**
     * asks user to choose what files to upload
     */
    static function showUploadStep3( ){
		$input =JFactory::getApplication()->input;
        //$catid = JRequest::getInt('catid', null); 
		$catid = $input->get( 'catid', null, 'INT');		
        //$uploadstep = JRequest::getInt('uploadstep', null); 
		$uploadstep = $input->get( 'uploadstep', null, 'INT');		
        //$numberOfUploads = JRequest::getInt('numberOfUploads', null); 
		$numberOfUploads = $input->get( 'numberOfUploads', null, 'INT');		

        ?>
        <script language="javascript" type="text/javascript">
	        Joomla.submitbutton = function(pressbutton) {
			var form = document.form3;
                form.submit();
        }
        </script>
        <form name="form3" action="index.php?option=com_rsgallery2&task=upload" method="post" enctype="multipart/form-data">
        <input type="hidden" name="uploadStep" value="4" />
        <input type="hidden" name="catid" value="<?php echo $catid; ?>" />
        <input type="hidden" name="numberOfUploads" value="<?php echo $numberOfUploads; ?>" />
        <table width="100%">
        <tr>
            <td width="300">&nbsp;</td>
            <td>
            <table class="adminform">
            <tr>
                <th colspan="2"><font size="4"><?php echo JText::_('COM_RSGALLERY2_STEP_3');?></font></th>
            </tr>
            <?php for( $t=1; $t < ($numberOfUploads+1); $t++ ): ?>
            <tr>
                <td colspan="2">
                    <table width="100%" cellpadding="1" cellspacing="1">
                    <tr>
                        <td colspan="2"><strong><?php echo JText::_('COM_RSGALLERY2_IMAGE');?><?php echo "&nbsp;".$t;?></strong></td>
                    </tr>
                    <tr>
                        <td><?php echo JText::_('COM_RSGALLERY2_GALLERY_NAME');?>:</td>
                        <td><strong><?php echo galleryUtils::getCatnameFromId($catid);?></strong></td>
                    </tr>
                    <tr>
                        <td valign="top" width="100"><?php echo JText::_('COM_RSGALLERY2_TITLE')." ".$t; ?>:</td>
                        <td>
                        <input name="imgTitle[]" type="text" class="inputbox" size="40" />
                        </td>
                    </tr>
                    <tr>
                        <td valign="top"><?php echo JText::_('COM_RSGALLERY2_FILE')." ".$t; ?>:</td>
                        <td>
                        <input class="inputbox" name="images[]" type="file" size="30" />
                        </td>
                    </tr>
                    <tr>
                        <td valign="top"><?php echo JText::_('COM_RSGALLERY2_DESCRIPTION')." ".$t; ?></td>
                        <td>
                        <textarea class="inputbox" cols="35" rows="3" name="descr[]"></textarea>
                        </td>
                    </tr>
                    <tr class="row1">
                    <th colspan="2">&nbsp;</th>
                    </tr>
                    </table>
                    </td>
                    </tr>
              <?php endfor; ?>
              </table>
              </td>
                <td width="300">&nbsp;</td>
            </tr>
            </table>
</form>
        <?php
    }
}//end class
?>
