<?php

/**
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * RSGallery is Free Software
 * Header included for use of JED Checker
 * Textfile for "deprecated" tasks to do or done for RSGallery2
 * 
 */ 

 
/**
 * Project:     Securimage: A PHP class for creating and managing form CAPTCHA images<br />
 * File:        securimage_show.php<br />
 *
 * Copyright (c) 2011, Drew Phillips
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 * 
 *  - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * Any modifications to the library should be indicated clearly in the source code
 * to inform users that the changes are not a part of the original software.<br /><br />
 *
 * If you found this script useful, please take a quick moment to rate it.<br />
 * http://www.hotscripts.com/rate/49400.html  Thanks.
 *
 * @link http://www.phpcaptcha.org Securimage PHP CAPTCHA
 * @link http://www.phpcaptcha.org/latest.zip Download Latest Version
 * @link http://www.phpcaptcha.org/Securimage_Docs/ Online Documentation
 * @copyright 2011 Drew Phillips
 * @author Drew Phillips <drew@drew-phillips.com>
 * @version 3.0 (October 2011)
 * @package Securimage
 *
 */

//Modification to original script - start
//Access Joomla Session variables from this script by initiating the Joomla application
define( '_JEXEC', 1 );
define( 'JPATH_BASE', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)  ))))));
define( 'DS', DIRECTORY_SEPARATOR );
require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );

$mainframe = JFactory::getApplication('site');
$mainframe->initialise();

$session = JFactory::getSession();

// initialize the rsg config file
if (!defined('JPATH_RSGALLERY2_ADMIN')){	//might also be defined in router.php is SEF is used
	define('JPATH_RSGALLERY2_ADMIN', JPATH_ROOT. DS .'administrator' . DS . 'components' . DS . 'com_rsgallery2');
}
require_once(JPATH_RSGALLERY2_ADMIN . DS . 'includes' . DS . 'config.class.php');
$rsgConfig = new rsgConfig();
//Modification to original script - end

require_once dirname(__FILE__) . '/securimage.php';

$img = new securimage();

// You can customize the image by making changes below
//Customisation of script - start 
$img->case_sensitive  = $rsgConfig->get('captcha_case_sensitive'); // true to use case sensitive codes
$img->image_height    = $rsgConfig->get('captcha_image_height');   // width in pixels of the image
$img->image_width     = $img->image_height * 2.7;		           // a good formula for image size
$img->perturbation    = $rsgConfig->get('captcha_perturbation');   // 1.0 = high distortion, higher numbers = more distortion
$img->image_bg_color  = new Securimage_Color($rsgConfig->get('captcha_image_bg_color'));   // image background color
$img->text_color      = new Securimage_Color($rsgConfig->get('captcha_text_color'));   // captcha text color
$img->line_color      = new Securimage_Color($rsgConfig->get('captcha_line_color'));   // color of lines over the image
$img->charset         = $rsgConfig->get('captcha_charset');		   // Characters to be used
$img->code_length     = $rsgConfig->get('captcha_code_length');	   // Number of characters in the captcha
$img->num_lines       = $rsgConfig->get('captcha_num_lines');      // how many lines to draw over the image
$img->captcha_type    = ($rsgConfig->get('captcha_type') ? Securimage::SI_CAPTCHA_MATHEMATIC : Securimage::SI_CAPTCHA_STRING); 							  // captcha: alphanumeric or math 
// see securimage.php for more options that can be set
//Customisation of script - end

$img->show();  // outputs the image and content headers to the browser
// alternate use: 
// $img->show('/path/to/background_image.jpg');
