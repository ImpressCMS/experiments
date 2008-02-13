<?php
// $Id$
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //

defined("XOOPS_MAINFILE_INCLUDED") or die();

set_magic_quotes_runtime(0);

/**
 * Hack by marcan <InBox Solutions> for SmartX
 * Including some debuging functions from the SmartObject Framework
 */
include_once(XOOPS_ROOT_PATH . "/modules/smartobject/include/xoops_core_common_functions.php");
/**
 * End of Hack by marcan <InBox Solutions> for SmartX
 * Including some debuging functions from the SmartObject Framework
 */


/**
 * Extremely reduced kernel class
 * This class should not really be defined in this file, but it wasn't worth including an entire
 * file for those two functions.
 * Few notes:
 * - modules should use this class methods to generate physical paths/URIs (the ones which do not conform
 * will perform badly when true URL rewriting is implemented)
 */
class xos_kernel_Xoops2 {
	var $paths = array(
		'www' => array(), 'modules' => array(), 'themes' => array(),
	);
	function xos_kernel_Xoops2() {
		$this->paths['www'] = array( XOOPS_ROOT_PATH, XOOPS_URL );
		$this->paths['modules'] = array( XOOPS_ROOT_PATH . '/modules', XOOPS_URL . '/modules' );
		$this->paths['themes'] = array( XOOPS_ROOT_PATH . '/themes', XOOPS_URL . '/themes' );
	}
	/**
	 * Convert a XOOPS path to a physical one
	 */
	function path( $url, $virtual = false ) {
		$path = '';
		@list( $root, $path ) = explode( '/', $url, 2 );
		if ( !isset( $this->paths[$root] ) ) {
			list( $root, $path ) = array( 'www', $url );
		}
		if ( !$virtual ) {		// Returns a physical path
			return $this->paths[$root][0] . '/' . $path;
		}
		return !isset( $this->paths[$root][1] ) ? '' : ( $this->paths[$root][1] . '/' . $path );
	}
	/**
	* Convert a XOOPS path to an URL
	*/
	function url( $url ) {
		return ( false !== strpos( $url, '://' ) ? $url : $this->path( $url, true ) );
	}
	/**
	* Build an URL with the specified request params
	*/
	function buildUrl( $url, $params = array() ) {
		if ( $url == '.' ) {
			$url = $_SERVER['REQUEST_URI'];
		}
		$split = explode( '?', $url );
		if ( count($split) > 1 ) {
			list( $url, $query ) = $split;
			parse_str( $query, $query );
			$params = array_merge( $query, $params );
		}
		if ( !empty( $params ) ) {
			foreach ( $params as $k => $v ) {
				$params[$k] = $k . '=' . rawurlencode($v);
			}
			$url .= '?' . implode( '&', $params );
		}
		return $url;
	}




}
global $xoops;
$xoops =& new xos_kernel_Xoops2();

    // Instantiate security object
    require_once XOOPS_ROOT_PATH."/class/xoopssecurity.php";
    global $xoopsSecurity;
    $xoopsSecurity = new XoopsSecurity();
    //Check super globals
    $xoopsSecurity->checkSuperglobals();

    // ############## Activate error handler / logger class ##############
    global $xoopsLogger, $xoopsErrorHandler;

    include_once XOOPS_ROOT_PATH . '/class/logger.php';
    $xoopsLogger =& XoopsLogger::instance();
	$xoopsErrorHandler =& $xoopsLogger;
    $xoopsLogger->startTime();
    $xoopsLogger->startTime( 'XOOPS Boot' );


    define("XOOPS_SIDEBLOCK_LEFT",0);
    define("XOOPS_SIDEBLOCK_RIGHT",1);
    define("XOOPS_SIDEBLOCK_BOTH",2);
    define("XOOPS_CENTERBLOCK_LEFT",3);
    define("XOOPS_CENTERBLOCK_RIGHT",4);
    define("XOOPS_CENTERBLOCK_CENTER",5);
    define("XOOPS_CENTERBLOCK_ALL",6);
    define("XOOPS_CENTERBLOCK_BOTTOMLEFT",7);
    define("XOOPS_CENTERBLOCK_BOTTOMRIGHT",8);
    define("XOOPS_CENTERBLOCK_BOTTOM",9);
    define("XOOPS_BLOCK_INVISIBLE",0);
    define("XOOPS_BLOCK_VISIBLE",1);
    define("XOOPS_MATCH_START",0);
    define("XOOPS_MATCH_END",1);
    define("XOOPS_MATCH_EQUAL",2);
    define("XOOPS_MATCH_CONTAIN",3);
    define("SMARTY_DIR", XOOPS_ROOT_PATH."/class/smarty/");
    define("XOOPS_CACHE_PATH", XOOPS_ROOT_PATH."/cache");
    define("XOOPS_UPLOAD_PATH", XOOPS_ROOT_PATH."/uploads");
    define("XOOPS_THEME_PATH", XOOPS_ROOT_PATH."/themes");
    define("XOOPS_COMPILE_PATH", XOOPS_ROOT_PATH."/templates_c");
    define("XOOPS_THEME_URL", XOOPS_URL."/themes");
    define("XOOPS_UPLOAD_URL", XOOPS_URL."/uploads");

    if (!defined('XOOPS_XMLRPC')) {
        define('XOOPS_DB_CHKREF', 1);
    } else {
        define('XOOPS_DB_CHKREF', 0);
    }

    // ############## Include common functions file ##############
    include_once XOOPS_ROOT_PATH.'/include/functions.php';

    // #################### Connect to DB ##################
    require_once XOOPS_ROOT_PATH.'/class/database/databasefactory.php';
    if ($_SERVER['REQUEST_METHOD'] != 'POST' || !$xoopsSecurity->checkReferer(XOOPS_DB_CHKREF)) {
        define('XOOPS_DB_PROXY', 1);
    }
    $xoopsDB =& XoopsDatabaseFactory::getDatabaseConnection();

    // ################# Include required files ##############
    require_once XOOPS_ROOT_PATH.'/kernel/object.php';
    require_once XOOPS_ROOT_PATH.'/class/criteria.php';

	/**
	 * Hack by marcan <INBOX> for SmartX
	 * Including the SmartObject classes
	 */
    include_once(XOOPS_ROOT_PATH . "/modules/smartobject/class/smartloader.php");
	/**
	 * End of hack by marcan <INBOX> for SmartX
	 * Including the SmartObject classes
	 */

    // #################### Include text sanitizer ##################
    include_once XOOPS_ROOT_PATH."/class/module.textsanitizer.php";

	/**
	 * Hack by marcan <INBOX>
	 * Adding SmartObject Adsense Feature
	 */
    //include_once(XOOPS_ROOT_PATH . "/modules/smartobject/include/adsense.php");
	/**
	 * End of Hack by marcan <INBOX>
	 * Adding SmartObject Adsense Feature
	 */

	// ML Hack by marcan
	// ############## Initializing ML functions ##############

	include_once(XOOPS_ROOT_PATH . "/include/multilanguage.php");

	// End of ML Hack by marcan
    // ################# Load Config Settings ##############
    $config_handler =& xoops_gethandler('config');
    $xoopsConfig =& $config_handler->getConfigsByCat(XOOPS_CONF);

	$myts =& MyTextSanitizer::getInstance();

    // #################### Error reporting settings ##################
    if ( $xoopsConfig['debug_mode'] == 1 || $xoopsConfig['debug_mode'] == 2 ) {
        error_reporting(E_ALL);
        $xoopsLogger->enableRendering();
        $xoopsLogger->usePopup = ( $xoopsConfig['debug_mode'] == 2 );
    } else {
	    error_reporting(0);
        $xoopsLogger->activated = false;
    }
	$xoopsSecurity->checkBadips();

    // ################# Include version info file ##############
    include_once XOOPS_ROOT_PATH."/include/version.php";

    // for older versions...will be DEPRECATED!
    $xoopsConfig['xoops_url'] = XOOPS_URL;
    $xoopsConfig['root_path'] = XOOPS_ROOT_PATH."/";

	// ML Hack by marcan
	check_language();
	// End of ML Hack by marcan

    // #################### Include site-wide lang file ##################
    if ( file_exists(XOOPS_ROOT_PATH."/language/".$xoopsConfig['language']."/global.php") ) {
        include_once XOOPS_ROOT_PATH."/language/".$xoopsConfig['language']."/global.php";
    } else {
        include_once XOOPS_ROOT_PATH."/language/english/global.php";
    }

    // ################ Include page-specific lang file ################
    if (isset($xoopsOption['pagetype']) && false === strpos($xoopsOption['pagetype'], '.')) {
        if ( file_exists(XOOPS_ROOT_PATH."/language/".$xoopsConfig['language']."/".$xoopsOption['pagetype'].".php") ) {
            include_once XOOPS_ROOT_PATH."/language/".$xoopsConfig['language']."/".$xoopsOption['pagetype'].".php";
        } else {
            include_once XOOPS_ROOT_PATH."/language/english/".$xoopsOption['pagetype'].".php";
        }
    }
    $xoopsOption = array();

    if ( !defined("XOOPS_USE_MULTIBYTES") ) {
        define("XOOPS_USE_MULTIBYTES",0);
    }

	/**#@+
	* Host abstraction layer
	*/
	if ( !isset($_SERVER['PATH_TRANSLATED']) && isset($_SERVER['SCRIPT_FILENAME']) ) {
		$_SERVER['PATH_TRANSLATED'] =& $_SERVER['SCRIPT_FILENAME'];		// For Apache CGI
	} elseif ( isset($_SERVER['PATH_TRANSLATED']) && !isset($_SERVER['SCRIPT_FILENAME']) ) {
		$_SERVER['SCRIPT_FILENAME'] =& $_SERVER['PATH_TRANSLATED'];		// For IIS/2K now I think :-(
	}

    if ( empty( $_SERVER[ 'REQUEST_URI' ] ) ) {         // Not defined by IIS
	// Under some configs, IIS makes SCRIPT_NAME point to php.exe :-(
	if ( !( $_SERVER[ 'REQUEST_URI' ] = @$_SERVER['PHP_SELF'] ) ) {
		$_SERVER[ 'REQUEST_URI' ] = $_SERVER['SCRIPT_NAME'];
	}
	if ( isset( $_SERVER[ 'QUERY_STRING' ] ) ) {
		$_SERVER[ 'REQUEST_URI' ] .= '?' . $_SERVER[ 'QUERY_STRING' ];
	}
	}
	$xoopsRequestUri = $_SERVER[ 'REQUEST_URI' ];		// Deprecated (use the corrected $_SERVER variable now)
	/**#@-*/

	// ############## Login a user with a valid session ##############
	$xoopsUser = '';
	$xoopsUserIsAdmin = false;
	$member_handler =& xoops_gethandler('member');
	$sess_handler =& xoops_gethandler('session');
	if ($xoopsConfig['use_ssl'] && isset($_POST[$xoopsConfig['sslpost_name']]) && $_POST[$xoopsConfig['sslpost_name']] != '') {
		session_id($_POST[$xoopsConfig['sslpost_name']]);
	} elseif ($xoopsConfig['use_mysession'] && $xoopsConfig['session_name'] != '') {
		if (isset($_COOKIE[$xoopsConfig['session_name']])) {
			session_id($_COOKIE[$xoopsConfig['session_name']]);
		} else {
            // no custom session cookie set, destroy session if any
            $_SESSION = array();
            //session_destroy();
        }
        if (function_exists('session_cache_expire')) {
            session_cache_expire($xoopsConfig['session_expire']);
        }
        @ini_set('session.gc_maxlifetime', $xoopsConfig['session_expire'] * 60);
    }
    session_set_save_handler(array(&$sess_handler, 'open'), array(&$sess_handler, 'close'), array(&$sess_handler, 'read'), array(&$sess_handler, 'write'), array(&$sess_handler, 'destroy'), array(&$sess_handler, 'gc'));
    session_start();

	/**
	 * Hack by marcan <InBox Solutions> for SmartX
	 * Automatically display ALL themes in the themes folder whatever $xoopsConfig['theme_set_allowed'] says
	 */

	// ############## Allow all themes in theme folders to be selected ##############
	include_once(XOOPS_ROOT_PATH . "/class/xoopslists.php");
	$themesList = XoopsLists::getThemesList();
	$themes_array = array();
	foreach ($themesList as $theme) {
		if (file_exists(XOOPS_THEME_PATH . "/" . $theme . "/theme.html")) {
			$themes_array[$theme] = $theme;
		}
	}

	$xoopsConfig['theme_set_allowed'] = $themes_array;
	/**
	 * End of Hack by marcan <InBox Solutions> for SmartX
	 * Automatically display ALL themes in the themes folder whatever $xoopsConfig['theme_set_allowed'] says
	 */

	// autologin hack GIJ
	if(empty($_SESSION['xoopsUserId']) && isset($_COOKIE['autologin_uname']) && isset($_COOKIE['autologin_pass'])) {

		// autologin V2 GIJ
		if( ! empty( $_POST ) ) {
			$_SESSION['AUTOLOGIN_POST'] = $_POST ;
			$_SESSION['AUTOLOGIN_REQUEST_URI'] = $_SERVER['REQUEST_URI'] ;
			redirect_header( XOOPS_URL . '/session_confirm.php' , 0 , _AUTOMATIC_LOGIN) ;
		} else if( ! empty( $_SERVER['QUERY_STRING'] ) && substr( $_SERVER['SCRIPT_NAME'] , -19 ) != 'session_confirm.php') {
			$_SESSION['AUTOLOGIN_REQUEST_URI'] = $_SERVER['REQUEST_URI'] ;
			redirect_header( XOOPS_URL . '/session_confirm.php' , 0 , _AUTOMATIC_LOGIN ) ;
		}
		// end of autologin V2

		// redirect to XOOPS_URL/ when query string exists (anti-CSRF) V1 code
		/* if( ! empty( $_SERVER['QUERY_STRING'] ) ) {
			redirect_header( XOOPS_URL . '/' , 0 , 'Now, logging in automatically' ) ;
			exit ;
		}*/

		$myts =& MyTextSanitizer::getInstance();
		$uname = $myts->stripSlashesGPC($_COOKIE['autologin_uname']);
		$pass = $myts->stripSlashesGPC($_COOKIE['autologin_pass']);
		if( empty( $uname ) || is_numeric( $pass ) ) $user = false ;
		else {
			// V3
			$uname4sql = addslashes( $uname ) ;
			$criteria = new CriteriaCompo(new Criteria('uname', $uname4sql ));
			$user_handler =& xoops_gethandler('user');
			$users =& $user_handler->getObjects($criteria, false);
			if( empty( $users ) || count( $users ) != 1 ) $user = false ;
			else {
				// V3.1 begin
				$user = $users[0] ;
				$old_limit = time() - ( defined('XOOPS_AUTOLOGIN_LIFETIME') ? XOOPS_AUTOLOGIN_LIFETIME : 604800 ) ; // 1 week default
				list( $old_Ynj , $old_encpass ) = explode( ':' , $pass ) ;
				if( strtotime( $old_Ynj ) < $old_limit || md5( $user->getVar('pass') . XOOPS_DB_PASS . XOOPS_DB_PREFIX . $old_Ynj ) != $old_encpass ) $user = false ;
				// V3.1 end
			}
			unset( $users ) ;
		}
		$xoops_cookie_path = defined('XOOPS_COOKIE_PATH') ? XOOPS_COOKIE_PATH : preg_replace( '?http://[^/]+(/.*)$?' , "$1" , XOOPS_URL ) ;
		if( $xoops_cookie_path == XOOPS_URL ) $xoops_cookie_path = '/' ;
		if (false != $user && $user->getVar('level') > 0) {
			// update time of last login
			$user->setVar('last_login', time());
			if (!$member_handler->insertUser($user, true)) {
			}
			//$_SESSION = array();
			$_SESSION['xoopsUserId'] = $user->getVar('uid');

			$_SESSION['xoopsUserGroups'] = $user->getGroups();
			// begin newly added in 2004-11-30
			$user_theme = $user->getVar('theme');
			if (in_array($user_theme, $xoopsConfig['theme_set_allowed'])) {
				$_SESSION['xoopsUserTheme'] = $user_theme;
			}
			// end newly added in 2004-11-30
			// update autologin cookies
			$expire = time() + ( defined('XOOPS_AUTOLOGIN_LIFETIME') ? XOOPS_AUTOLOGIN_LIFETIME : 604800 ) ; // 1 week default
			setcookie('autologin_uname', $uname, $expire, $xoops_cookie_path, '', 0);
			// V3.1
			$Ynj = date( 'Y-n-j' ) ;
			setcookie('autologin_pass', $Ynj . ':' . md5( $user->getVar('pass') . XOOPS_DB_PASS . XOOPS_DB_PREFIX . $Ynj ) , $expire, $xoops_cookie_path, '', 0);
		} else {
			setcookie('autologin_uname', '', time() - 3600, $xoops_cookie_path, '', 0);
			setcookie('autologin_pass', '', time() - 3600, $xoops_cookie_path, '', 0);
		}
	}
	// end of autologin hack GIJ

	if (!empty($_SESSION['xoopsUserId'])) {
		$xoopsUser =& $member_handler->getUser($_SESSION['xoopsUserId']);
		if (!is_object($xoopsUser)) {
			$xoopsUser = '';
			$_SESSION = array();
		} else {
			if ($xoopsConfig['use_mysession'] && $xoopsConfig['session_name'] != '') {
				setcookie($xoopsConfig['session_name'], session_id(), time()+(60*$xoopsConfig['session_expire']), '/',  '', 0);
			}
			$xoopsUser->setGroups($_SESSION['xoopsUserGroups']);
			$xoopsUserIsAdmin = $xoopsUser->isAdmin();
		}
	}
	if (!empty($_POST['xoops_theme_select']) && in_array($_POST['xoops_theme_select'], $xoopsConfig['theme_set_allowed'])) {
		$xoopsConfig['theme_set'] = $_POST['xoops_theme_select'];
		$_SESSION['xoopsUserTheme'] = $_POST['xoops_theme_select'];
	} elseif (!empty($_SESSION['xoopsUserTheme']) && in_array($_SESSION['xoopsUserTheme'], $xoopsConfig['theme_set_allowed'])) {

		$xoopsConfig['theme_set'] = $_SESSION['xoopsUserTheme'];
	}

/**
 * Hack by marcan <INBOX>
 * Adding SmartObject Custom tag Feature
 */
include_once(XOOPS_ROOT_PATH . "/modules/smartobject/include/customtag.php");
/**
 * End of Hack by marcan <INBOX>
 * Adding SmartObject Custom tag Feature
 */

/**
 * Hack by marcan >INBOX> for SCSPRO
 * Including SmartCron features
 */
if (file_exists(XOOPS_ROOT_PATH . '/modules/smartcron/include/smartcron.inc.php')) {
	include_once(XOOPS_ROOT_PATH . '/modules/smartcron/include/smartcron.inc.php');
}
/**
 * End of Hack by marcan >INBOX> for SCSPRO
 * Including SmartCron features
 */
	if ($xoopsConfig['closesite'] == 1) {
		$allowed = false;
		if (is_object($xoopsUser)) {
			foreach ($xoopsUser->getGroups() as $group) {
				if (in_array($group, $xoopsConfig['closesite_okgrp']) || XOOPS_GROUP_ADMIN == $group) {
					$allowed = true;
					break;
				}
			}
		} elseif (!empty($_POST['xoops_login'])) {
			include_once XOOPS_ROOT_PATH.'/include/checklogin.php';
			exit();
		}
		if (!$allowed) {
            include_once XOOPS_ROOT_PATH.'/class/template.php';
            $xoopsTpl = new XoopsTpl();
            $xoopsTpl->assign( array(
            	'sitename' => $xoopsConfig['sitename'],
            	'xoops_themecss' => xoops_getcss(),
            	'xoops_imageurl' => XOOPS_THEME_URL.'/'.$xoopsConfig['theme_set'].'/',
            	'lang_login' => _LOGIN,
            	'lang_username' => _USERNAME,
            	'lang_password' => _PASSWORD,
            	'lang_siteclosemsg' => $xoopsConfig['closesite_text'],
            	'xoops_requesturi' => $_SERVER['REQUEST_URI'],
            ) );

			// Hack by marcan : Adding more smarty variable when the site is closed
			$myts =& MyTextSanitizer::getInstance();
			$config_handler =& xoops_gethandler('config');
			$xoopsConfigMetaFooter =& $config_handler->getConfigsByCat(XOOPS_CONF_METAFOOTER);
			$xoopsTpl->assign(array('xoops_meta_robots' => $xoopsConfigMetaFooter['meta_robots'],
									'xoops_meta_keywords' => $xoopsConfigMetaFooter['meta_keywords'],
									'xoops_meta_description' => $xoopsConfigMetaFooter['meta_description'],
									'xoops_meta_rating' => $xoopsConfigMetaFooter['meta_rating'],
									'xoops_meta_author' => $xoopsConfigMetaFooter['meta_author'],
									'xoops_meta_copyright' => $xoopsConfigMetaFooter['meta_copyright'],
									'xoops_sitename' => $myts->formatForML($xoopsConfig['sitename']),
									'xoops_pagetitle' => $myts->formatForML($xoopsConfig['slogan']),
									'xoops_footer' => $xoopsConfigMetaFooter['footer']
									));
			// End of hack by marcan

			$xoopsTpl->assign(array('sitename' => $xoopsConfig['sitename'], 'xoops_themecss' => xoops_getcss(), 'xoops_imageurl' => XOOPS_THEME_URL.'/'.$xoopsConfig['theme_set'].'/', 'lang_login' => _LOGIN, 'lang_username' => _USERNAME, 'lang_password' => _PASSWORD, 'lang_siteclosemsg' => $xoopsConfig['closesite_text']));
            $xoopsTpl->caching = 0;
			$xoopsTpl->display('db:system_siteclosed.html');
			exit();
		}
		unset($allowed, $group);
	}

	if (file_exists('./xoops_version.php')) {
        $url_arr = explode( '/', strstr( $_SERVER['PHP_SELF'],'/modules/') );
		$module_handler =& xoops_gethandler('module');
		$xoopsModule =& $module_handler->getByDirname($url_arr[2]);
		unset($url_arr);
		if (!$xoopsModule || !$xoopsModule->getVar('isactive')) {
			include_once XOOPS_ROOT_PATH."/header.php";
			echo "<h4>"._MODULENOEXIST."</h4>";
			include_once XOOPS_ROOT_PATH."/footer.php";
			exit();
		}
		$moduleperm_handler =& xoops_gethandler('groupperm');
		if ($xoopsUser) {
			if (!$moduleperm_handler->checkRight('module_read', $xoopsModule->getVar('mid'), $xoopsUser->getGroups())) {
				redirect_header(XOOPS_URL."/user.php",1,_NOPERM);
				exit();
			}
			$xoopsUserIsAdmin = $xoopsUser->isAdmin($xoopsModule->getVar('mid'));
		} else {
			if (!$moduleperm_handler->checkRight('module_read', $xoopsModule->getVar('mid'), XOOPS_GROUP_ANONYMOUS)) {
				redirect_header(XOOPS_URL."/user.php",1,_NOPERM);
				exit();
			}
		}
		if ( file_exists(XOOPS_ROOT_PATH."/modules/".$xoopsModule->getVar('dirname')."/language/".$xoopsConfig['language']."/main.php") ) {
			include_once XOOPS_ROOT_PATH."/modules/".$xoopsModule->getVar('dirname')."/language/".$xoopsConfig['language']."/main.php";
		} else {
			if ( file_exists(XOOPS_ROOT_PATH."/modules/".$xoopsModule->getVar('dirname')."/language/english/main.php") ) {
				include_once XOOPS_ROOT_PATH."/modules/".$xoopsModule->getVar('dirname')."/language/english/main.php";
			}
		}
		if ($xoopsModule->getVar('hasconfig') == 1 || $xoopsModule->getVar('hascomments') == 1 || $xoopsModule->getVar( 'hasnotification' ) == 1) {
			$xoopsModuleConfig =& $config_handler->getConfigsByCat(0, $xoopsModule->getVar('mid'));
		}
	} elseif($xoopsUser) {
		$xoopsUserIsAdmin = $xoopsUser->isAdmin(1);
	}
    $xoopsLogger->stopTime( 'XOOPS Boot' );
    $xoopsLogger->startTime( 'Module init' );

    /**
     * ML HAck by marcan
     */
     $xoopsConfig['sitename'] = $myts->FormatForML($xoopsConfig['sitename']);
     $xoopsConfig['slogan'] = $myts->FormatForML($xoopsConfig['slogan']);
    /**
     * end of ML HAck by marcan
     */
/**
 * Hack by marcan <INBOX> for SCSPRO
 * Setting mastop as the main editor
 */
if (!isset($_GET['basiceditor'])) {
	define('XOOPS_EDITOR_IS_HTML', true);
}
/**
 * End of Hack by marcan <INBOX> for SCSPRO
 * Setting mastop as the main editor
 */
?>
