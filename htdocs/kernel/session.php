<?php
// $Id: session.php 1083 2007-10-16 16:42:51Z phppp $
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
// Author: Kazumi Ono (AKA onokazu)                                          //
// URL: http://www.myweb.ne.jp/, http://www.xoops.org/, http://jp.xoops.org/ //
// Project: The XOOPS Project                                                //
// ------------------------------------------------------------------------- //
/*
  Based on SecureSession class
  Written by Vagharshak Tozalakyan <vagh@armdex.com>
  Released under GNU Public License
*/
/**
* Handler for a session
* @package     kernel
*
* @author	    Kazumi Ono	<onokazu@xoops.org>
* @copyright	copyright (c) 2000-2003 XOOPS.org
*/
class XoopsSessionHandler
{
	/**
	* Database connection
	* @var	object
	* @access	private
	*/
	var $db;

	/**
	* Security checking level
	* Possible value: 
	*	0 - no check;
	*	1 - check browser characteristics (HTTP_USER_AGENT/HTTP_ACCEPT_LANGUAGE)
	*	2 - check browser and IP A.B;
	*	3 - check browser and IP A.B.C, recommended;
	*	4 - check browser and IP A.B.C.D;
	* 
	* @var	int
	* @access	public
	*/
	var $securityLevel = 3;
    
	/**
	* Enable regenerate_id
	* @var	bool
	* @access	public
	*/
	var $enableRegenerateId = false;
    
	/**
	* Constructor
	* @param object $db reference to the {@link XoopsDatabase} object
	* 
	*/
	function XoopsSessionHandler(&$db) {$this->db =& $db;}

	/**
	* Open a session
	* @param	string  $save_path
	* @param	string  $session_name
	* @return	bool
	*/
	function open($save_path, $session_name) {return true;}

	/**
	* Close a session
	* @return	bool
	*/
	function close()
	{
		$this->gc_force();
		return true;
	}

	/**
	* Read a session from the database
	* @param	string  &sess_id    ID of the session
	* @return	array   Session data
	*/
	function read($sess_id)
	{
		$sql = sprintf('SELECT sess_data, sess_ip, sess_uagent, sess_fprint FROM %s WHERE sess_id = %s', $this->db->prefix('session'), $this->db->quoteString($sess_id));
		$serve_uagent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');
		$serve_aclang = filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE');
		$serve_remaddr = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP);
		if(false != $result = $this->db->query($sql))
		{
			if(list($sess_data, $sess_ip) = $this->db->fetchRow($result))
			{
				if($this->securityLevel > 1)
				{
					$pos = strpos($sess_ip, ".", $this->securityLevel - 1);
					if(strncmp($sess_ip, $serve_remaddr, $pos)) {$sess_data = '';}
				}
				return $sess_data;
			}
		}
		return '';
	}

	/**
	* Inserts a session into the database
	* @param   string  $sess_id
	* @param   string  $sess_data
	* @return  bool    
	**/
	function write($sess_id, $sess_data)
	{
		$sess_id = $this->db->quoteString($sess_id);
		$sql = sprintf("UPDATE %s SET sess_updated = '%u', sess_data = %s WHERE sess_id = %s", $this->db->prefix('session'), time(), $this->db->quoteString($sess_data), $sess_id);
		$this->db->queryF($sql);
		$serve_uagent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');
		$serve_aclang = filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE');
		$serve_remaddr = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP);
		if(!$this->db->getAffectedRows())
		{
			$sql = sprintf("INSERT INTO %s (sess_id, sess_updated, sess_ip, sess_data, sess_uagent, sess_aclang, sess_fprint) VALUES (%s, '%u', %s, %s, %s, %s, %s)", $this->db->prefix('session'), $sess_id, time(), $this->db->quoteString($serve_remaddr), $this->db->quoteString($sess_data), $this->db->quoteString($serve_uagent), $this->db->quoteString($serve_aclang), $this->db->quoteString($this->icms_sessionFingerprint()));
			return $this->db->queryF($sql);
		}
		return true;
	}

	/**
	* Destroy a session
	* @param   string  $sess_id
	* @return  bool
	**/
	function destroy($sess_id)
	{
		$sql = sprintf('DELETE FROM %s WHERE sess_id = %s', $this->db->prefix('session'), $this->db->quoteString($sess_id));
		if(!$result = $this->db->queryF($sql)) {return false;}
		return true;
	}

	/**
	* Garbage Collector
	* @param   int $expire Time in seconds until a session expires
	* @return  bool
	**/
	function gc($expire)
	{
		if(empty($expire)) {return true;}
		$mintime = time() - intval($expire);
		$sql = sprintf("DELETE FROM %s WHERE sess_updated < '%u'", $this->db->prefix('session'), $mintime);
		return $this->db->queryF($sql);
	}

	/**
	* Force gc for situations where gc is registered but not executed
	**/
	function gc_force()
	{
		if(rand(1, 100) < 11)
		{
			$expiration = empty($GLOBALS['xoopsConfig']['session_expire']) ? @ini_get('session.gc_maxlifetime') : $GLOBALS['xoopsConfig']['session_expire'] * 60;
			$this->gc($expiration);
		}
	}

	/**
	* Update the current session id with a newly generated one
	* To be refactored 
	* @param   bool $delete_old_session
	* @return  bool
	**/
	function icms_sessionRegenerateId($regenerate = false)
	{
		$old_session_id = session_id();
		if($regenerate)
		{
			$success = session_regenerate_id(true);
//			$this->destroy($old_session_id);
		}
		else {$success = session_regenerate_id();}
		// Force updating cookie for session cookie is not issued correctly in some IE versions or not automatically issued prior to PHP 4.3.3 for all browsers 
		if($success) {$this->update_cookie();}
		
		return $success;
	}

	/**
	* Update cookie status for current session
	* To be refactored 
	* FIXME: how about $xoopsConfig['use_ssl'] is enabled?
	* 
	* @param   string  $sess_id    session ID
	* @param   int     $expire     Time in seconds until a session expires
	* @return  bool
	**/
	function update_cookie($sess_id = null, $expire = null)
	{
		global $xoopsConfig;
		$session_name = ($xoopsConfig['use_mysession'] && $xoopsConfig['session_name'] != '') ? $xoopsConfig['session_name'] : session_name();
		$session_expire = !is_null($expire) ? intval($expire) : ( ($xoopsConfig['use_mysession'] && $xoopsConfig['session_name'] != '') ? $xoopsConfig['session_expire'] * 60 : ini_get('session.cookie_lifetime') );
		$session_id = empty($sess_id) ? session_id() : $sess_id;
		setcookie($session_name, $session_id, $session_expire ? time() + $session_expire : 0, '/',  '', 0, 0);
	}

	/**
	* Opens a session & creates a session fingerprint & unique session_id()
	* @param   string  $unique    Unique identifier to use in the hash algorhythm
	*						this should be unique to the user. ie. pass, uname, uid etc.
	* @param   bool  $regenerate	true = regenerate the session_id(), false = keep same session_id()
	**/
	function icms_sessionOpen($unique = '', $regenerate = false)
	{
		$_SESSION['icms_fprint'] = $this->icms_sessionFingerprint($unique);
		if($regenerate) {$this->icms_sessionRegenerateId(true);}
	}
	
	/**
	* Check the $_SESSION fingerprint against the cookie fingerprint
	* @param   string  $unique    Unique identifier to use in the hash algorhythm
	*						this should be unique to the user. ie. pass, uname, uid etc.
	* @return  bool.
	**/
	function icms_sessionCheck($unique = '')
	{
//		$this->icms_sessionRegenerateId();
		return (isset($_SESSION['icms_fprint']) && $_SESSION['icms_fprint'] == $this->icms_sessionFingerprint($unique));
	}

	/**
	* Create a Unique Fingerprint hash of session
	* @param   string  $unique    Unique identifier to use in the hash algorhythm
	*						this should be unique to the user. ie. pass, uname, uid etc.
	* @return  string	sha256 encrypted hash.
	**/
	function icms_sessionFingerprint($unique = '')
	{
		global $xoopsConfig;
		if(!isset($unique) || $unique = '') {$unique = XOOPS_DB_SALT;}
		$serve_uagent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');
		$serve_aclang = filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE');
		$serve_remaddr = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP);
		$securityLevel = $xoopsConfig['session_chk_level'];

		$fingerprint = $unique;
		if($securityLevel >= 1) {$fingerprint .= $serve_uagent.$serve_aclang;}
		if($securityLevel >= 2)
		{
			$num_blocks = abs(intval($securityLevel));
			if($num_blocks > 4) {$num_blocks = 4;}
			$blocks = explode('.', $serve_remaddr);
			for($i = 0; $i < $num_blocks; $i++) {$fingerprint .= $blocks[$i].'.';}
		}
		return hash('sha256',$fingerprint);
	}

}

/**
* Handler for admin session
* @package	kernel
*
* @author		Vaughan Montgomery <vaughan@impresscms.org>
* @copyright	copyright (c) 2007-2009 ImpressCMS.org
*/
class icmsAdminSessionHandler
{
	/**
	* Database connection
	* @var	object
	* @access	private
	*/
	var $db;

	/**
	* Security checking level
	* Possible value: 
	*	0 - no check;
	*	1 - check browser characteristics (HTTP_USER_AGENT/HTTP_ACCEPT_LANGUAGE)
	*	2 - check browser and IP A.B;
	*	3 - check browser and IP A.B.C, recommended;
	*	4 - check browser and IP A.B.C.D;
	* @var	int
	* @access	public
	*/
	var $securityLevel = 3;
    
	/**
	* Enable adm_regenerate_id
	* @var	bool
	* @access	public
	*/
	var $enableRegenerateId = false;
    
	/**
	* Constructor
	* @param object $db reference to the {@link XoopsDatabase} object
	*/
	function icmsAdminSessionHandler(&$db) {$this->db =& $db;}

	/**
	* Open an admin session
	* @param	string  $adm_save_path
	* @param	string  $adm_session_name
	* @return	bool
	*/
	function open($adm_save_path, $adm_session_name) {return true;}

	/**
	* Close an admin session
	* @return	bool
	*/
	function close()
	{
		$this->gc_force();
		return true;
	}

	/**
	* Read an admin session from the database
	* @param	string  &adm_sess_id    ID of the admin session
	* @return	array   adm_Session data
	*/
	function read($adm_sess_id)
	{
		$sql = sprintf('SELECT adm_sess_data, adm_sess_ip, adm_sess_uagent, adm_sess_aclang, adm_sess_fprint FROM %s WHERE adm_sess_id = %s', $this->db->prefix('admin_session'), $this->db->quoteString($adm_sess_id));
		$serve_uagent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');
		$serve_aclang = filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE');
		$serve_remaddr = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP);
		if(false != $result = $this->db->query($sql))
		{
			if(list($adm_sess_data, $adm_sess_ip, $adm_sess_uagent, $adm_sess_aclang) = $this->db->fetchRow($result))
			{
				if($this->securityLevel >= 1)
				{
					$pos = strpos($adm_sess_ip, ".", $this->securityLevel - 1);
					if(strncmp($adm_sess_ip, $serve_remaddr, $pos)) {$adm_sess_data = '';}
				}
				return $adm_sess_data;
			}
		}
		return '';
	}

	/**
	* Inserts an admin session into the database
	* @param   string  $adm_sess_id
	* @param   string  $adm_sess_data
	* @return  bool
	**/
	function write($adm_sess_id, $adm_sess_data)
	{
		$adm_sess_id = $this->db->quoteString($adm_sess_id);
		$sql = sprintf("UPDATE %s SET adm_sess_updated = '%u', adm_sess_data = %s WHERE adm_sess_id = %s", $this->db->prefix('admin_session'), time(), $this->db->quoteString($adm_sess_data), $adm_sess_id);
		$this->db->queryF($sql);
		$serve_uagent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');
		$serve_aclang = filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE');
		$serve_remaddr = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP);
		if(!$this->db->getAffectedRows())
		{
			$sql = sprintf("INSERT INTO %s (adm_sess_id, adm_sess_updated, adm_sess_ip, adm_sess_data, adm_sess_uagent, adm_sess_aclang, adm_sess_fprint) VALUES (%s, '%u', %s, %s, %s, %s, %s)", $this->db->prefix('admin_session'), $adm_sess_id, time(), $this->db->quoteString($serve_remaddr), $this->db->quoteString($adm_sess_data), $this->db->quoteString($serve_uagent), $this->db->quoteString($serve_aclang), $this->db->quoteString($this->icms_sessionFingerprint()));
			return $this->db->queryF($sql);
		}
		return true;
	}

	/**
	* Destroy a session
	* @param   string  $adm_sess_id
	* @return  bool
	**/
	function destroy($adm_sess_id)
	{
		$sql = sprintf('DELETE FROM %s WHERE adm_sess_id = %s', $this->db->prefix('admin_session'), $this->db->quoteString($adm_sess_id));
		if(!$result = $this->db->queryF($sql)) {return false;}
		return true;
	}

	/**
	* Garbage Collector
	* @param   int $expire Time in seconds until an admin session expires
	* @return  bool
	**/
	function gc($expire)
	{
		if(empty($expire)) {return true;}
		$mintime = time() - intval($expire);
		$sql = sprintf("DELETE FROM %s WHERE adm_sess_updated < '%u'", $this->db->prefix('admin_session'), $mintime);
		return $this->db->queryF($sql);
	}

	/**
	* Force gc for situations where gc is registered but not executed
	**/
	function gc_force()
	{
		if(rand(1, 100) < 11)
		{
			$expiration = empty($GLOBALS['xoopsConfig']['admin_session_expire']) ? @ini_get('session.gc_maxlifetime') : $GLOBALS['xoopsConfig']['admin_session_expire'] * 60;
			$this->gc($expiration);
		}
	}

	/**
	* Update the current session id with a newly generated one
	* To be refactored 
	* @param   bool $delete_old_session
	* @return  bool
	**/
	function icms_sessionRegenerateId($regenerate = false)
	{
		$old_session_id = session_id();
		if($regenerate)
		{
			$success = session_regenerate_id(true);
//			$this->destroy($old_session_id);
		}
		else {$success = session_regenerate_id();}
		// Force updating cookie for session cookie is not issued correctly in some IE versions or not automatically issued prior to PHP 4.3.3 for all browsers 
		if($success) {$this->update_cookie();}
		
		return $success;
	}

	/**
	* Update cookie status for current session
	* To be refactored 
	* FIXME: how about $xoopsConfig['use_ssl'] is enabled?
	* 
	* @param   string  $sess_id    session ID
	* @param   int     $expire     Time in seconds until a session expires
	* @return  bool
	**/
	function update_cookie($adm_sess_id = null, $expire = null)
	{
		global $xoopsConfig;
		if($xoopsConfig['use_ssl'] && isset($_POST[$xoopsConfig['sslpost_name']]) && $_POST[$xoopsConfig['sslpost_name']] != '')
		{
			$adm_session_id($_POST[$xoopsConfig['sslpost_name']]);
			$cookie_secure = 1;
		}
		else
		{
			$adm_session_id = empty($adm_sess_id) ? session_id() : $adm_sess_id;
			$cookie_secure = 0;
		}
		$adm_session_name = ($xoopsConfig['admin_use_mysession'] && $xoopsConfig['admin_session_name'] != '') ? $xoopsConfig['admin_session_name'] : 'ICMSADSESSION';
		$adm_session_expire = !is_null($expire) ? intval($expire) : ( ($xoopsConfig['admin_use_mysession'] && $xoopsConfig['admin_session_name'] != '') ? $xoopsConfig['admin_session_expire'] * 60 : ini_get('session.cookie_lifetime') );
		setcookie($adm_session_name, $adm_session_id, $adm_session_expire ? time() + $adm_session_expire : 0, '/',  '', $cookie_secure, 1);
	}

	/**
	* Opens a session & creates a session fingerprint & unique session_id()
	* @param   string  $unique    Unique identifier to use in the hash algorhythm
	*						this should be unique to the user. ie. pass, uname, uid etc.
	* @param   bool  $regenerate	true = regenerate the session_id(), false = keep same session_id()
	**/
	function icms_sessionOpen($unique = '', $regenerate = false)
	{
		$_SESSION['icms_admin_fprint'] = $this->icms_sessionFingerprint($unique);
		if($regenerate) {$this->icms_sessionRegenerateId(true);}
	}
	
	/**
	* Check the $_SESSION fingerprint against the cookie fingerprint
	* @param   string  $unique    Unique identifier to use in the hash algorhythm
	*						this should be unique to the user. ie. pass, uname, uid etc.
	* @return  bool.
	**/
	function icms_sessionCheck($unique = '')
	{
//		$this->icms_sessionRegenerateId();
		return (isset($_SESSION['icms_admin_fprint']) && $_SESSION['icms_admin_fprint'] == $this->icms_sessionFingerprint($unique));
	}

	/**
	* Create a Unique Fingerprint hash of admin session
	* @param   string  $unique    Unique identifier to use in the hash algorhythm
	*						this should be unique to the user. ie. pass, uname, uid etc.
	* @return  string	sha256 encrypted hash.
	**/
	function icms_sessionFingerprint($unique = '')
	{
		global $xoopsConfig;
		if(!isset($unique) || $unique = '') {$unique = XOOPS_DB_SALT;}
		$serve_uagent = filter_input(INPUT_SERVER, 'HTTP_USER_AGENT');
		$serve_aclang = filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE');
		$serve_remaddr = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP);
		$securityLevel = $xoopsConfig['admin_session_chk_level'];

		$fingerprint = $unique;
		if($securityLevel >= 1) {$fingerprint .= $serve_uagent.$serve_aclang;}
		if($securityLevel >= 2)
		{
			$num_blocks = abs(intval($securityLevel));
			if($num_blocks > 4) {$num_blocks = 4;}
			$blocks = explode('.', $serve_remaddr);
			for($i = 0; $i < $num_blocks; $i++) {$fingerprint .= $blocks[$i].'.';}
		}
		return hash('sha256',$fingerprint);
	}

}

?>