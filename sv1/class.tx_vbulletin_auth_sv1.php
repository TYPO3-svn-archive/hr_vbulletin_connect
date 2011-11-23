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
  
  */

//require_once(t3lib_extMgm::extPath('hr_vbulletin_connect').'user_vBulletin_global.php');



class tx_vbulletin_auth_sv1 extends tx_sv_authbase {
	var $prefixId = 'tx_vbulletin_auth_sv1';		// Same as class name
	var $scriptRelPath = 'sv1/class.tx_vbulletin_auth_sv1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'hr_vbulletin_connect';	// The extension key.

	/**
	 * authenticate a user
	 *
	 * @param	array		Data of user.
	 * @return	boolean
	 */
	function authUser($user)	{
               global $vbulletin;
               

                
                $OK = 100;
	//echo "tx_vbulletin_auth_sv1";
        t3lib_div::devLog('authUser: tx_vbulletin_auth_sv1', 2);     
		if ($this->login['uname'] )	{
			//$OK = false;

                        // get the vBulletin user
                        $vb_user = $vbulletin->userinfo;
                        //print_r($vb_user);
                        if(!$vb_user['userid']){
                            if(TYPO3_DLOG){
                                t3lib_div::devLog('No vBulletin user is logged in:'.$this->login['uident'].', username='.$this->login['uname'].' in '.__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2);
                            }
                            return $OK;
                        }
                        //echo "vbuser gefunden:";
                        
                        // check if the pseudopassword match (only to improve security):
                        $pseudopass = md5(COOKIE_SALT.$this->login['uname'].$vb_user['salt']);
                        //echo "pseudopw=$pseudopass, ".$this->login['uident']." <br>"; 
                        if(! ($this->login['uident']==$pseudopass) ){
                           if(TYPO3_DLOG){
                                t3lib_div::devLog('pseudopassword doesn\'t match:'.$pseudopass.', '.$this->login['uident'].' in '.__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2);
                            }
                            return $OK;
                        }
                        
                        if($vb_user['username'] === $this->login['uname']){
                            //echo "vbulletin user wird akzeptiert";
                            $OK = 200;
                        }
                        


		}
	    // bei 100 werden andere auth-services auch getestet,
            // bei 0 wird nicht mehr weitergetestet 
            // bei 200 ist man eingeloggt             
            // echo "ret (cc_svauthdemo)=".$OK;       
                //return 100;
		return $OK;
	}

}



if (defined("TYPO3_MODE") && $TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/hr_vbulletin_connect/sv1/class.tx_vbulletin_auth_sv1.php"])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]["XCLASS"]["ext/hr_vbulletin_connect/sv1/class.tx_vbulletin_auth_sv1.php"]);
}

?>