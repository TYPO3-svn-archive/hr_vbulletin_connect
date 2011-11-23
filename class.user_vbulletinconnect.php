<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Herbert Roider (herbert.roider@utanet.at)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
  * This class contains 2 hook functions which are called from the t3lib_userAuth object. 
    See the ext_localconf.php
 * @author	Herbert Roider <herbert.roider@utanet.at>
  * 

 */
// Prefix and names for the database tables
class user_vbulletinconnect {
    var $prefixId = 'user_vbulletinconnect';		// Same as class name
    var $scriptRelPath = 'class.user_vbulletinconnect.php';	// Path to this script relative to the extension dir.
    var $extKey = 'hr_vbulletin_connect';	// The extension key.
    
        /** unset the vBulletin cookies only when a user click the logout.
         *
         * @param       array           empty array
         * @param       t3lib_userAuth 
         * @return      void
         */
    function postprocess_logout($params, $auth_obj)	{
        //var_dump($auth_obj);
        global $vbulletin;
        if( $auth_obj->loginType == 'FE'){
                $loginData = $auth_obj->getLoginFormData();
                // active logout (eg. with "logout" button)
                if ($loginData['status']=='logout') {
                    //require_once(t3lib_extMgm::extPath('hr_vbulletin_connect').'user_vBulletin_global.php');

                    if(TYPO3_DLOG){
                        t3lib_div::devLog('process vBulletin logout in '.__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2);
                    }
                    // The vBulletin function.
                    process_logout();
                }
         }
    }
        /** look if a vBulletin user is logged in and find the equivalent typo3 user and authenticate this.
        This function is called every session start and try to find a vBulletin user, if no typo3 user is logged in.
         *
         * @param       array           contains the pObj which contains a reference to the t3lib_userAuth object.
         * @param       t3lib_userAuth 
         * @return      void
         */
    
    function postprocess_user($uOb, $auth_obj)	{
        global $vbulletin;
        
        if ($uOb['pObj']->loginType == 'FE'){
            
            // Die Config-einstellungen aus der localconf.php holen:
            $conf_default = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
            
            

            // A user is logged in in typo3 so set the cookies for vbulletin.
            // Set the cookies only at login
            if( $auth_obj->loginType == 'FE'){
                $loginData = $auth_obj->getLoginFormData();
                // active login (eg. with "login" button)
                if ($loginData['status']=='login') {
                    //require_once(t3lib_extMgm::extPath('hr_vbulletin_connect').'user_vBulletin_global.php');

                    if(TYPO3_DLOG){
                        t3lib_div::devLog('try to create a valid vBulletin session for typo3 user='.$uOb['pObj']->user ['uid'].','.$uOb['pObj']->user ['username'].' in '.__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2);
                    }
                    // Create a vBulletin session for this typo3 user:
                    $vbulletin->userinfo = fetch_userinfo($uOb['pObj']->user ['tx_hrvbulletinconnect_vbulletin_user_id']);
                    //print_r($vbulletin->userinfo);
                    
                    process_new_login('', 0, '');
                    $vbulletin->session->set('loggedin', 2);
                    $vbulletin->session->save();
                }
            }
            
        }
    }
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_vbulletin_connect/class.user_vbulletinconnect.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_vbulletin_connect/class.user_vbulletinconnect.php']);
}

?>