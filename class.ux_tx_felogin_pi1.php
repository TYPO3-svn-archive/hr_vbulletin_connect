<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Herbert Roider (herbert.roider@utanet.at)
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
needed for the extension felogin when the user reset the password .
Since 4.2.0 the newloginbox is obsolete and no more requiered for hr_vbulletin_connect. 
 */

require_once(t3lib_extMgm::extPath('hr_vbulletin_connect').'class.user_feuser_vbulletinuser_sync.php');

class ux_tx_felogin_pi1 extends tx_felogin_pi1
{
    function showForgot() {
        //error_log(__FILE__.", ".__FUNCTION__." ");

        $ret = parent::showForgot();
        if (t3lib_div::validEmail($this->piVars['forgot_email'])) {
            $extKey = "hr_vbulletin_connect";
            
            $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, username, password', 'fe_users', 'email='.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->piVars['forgot_email'], 'fe_users').' AND pid IN ('.$GLOBALS['TYPO3_DB']->cleanIntList($this->spid).') '.$this->cObj->enableFields('fe_users'));
            
            while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
                $vBulletin_sync = t3lib_div::makeInstance('user_feuser_vbulletinuser_sync');
                $vBulletin_userdata = $vBulletin_sync->user_update($row);
                if (!empty($vBulletin_userdata->errors))
                {
                    $errorlist = '';
                    foreach ($vBulletin_userdata->errors AS $index => $error)
                    {
                            $errorlist .= "$error<br>\n";
                    }
                    if(TYPO3_DLOG){
                        t3lib_div::devLog('cannot update typo3 user: '.$row['uid'].', error='.$errorlist.' in '.__LINE__.": ".__FUNCTION__.", ".__FILE__, $extKey, 2 );
                    }
                } else{
                    if(TYPO3_DLOG){
                        t3lib_div::devLog('update typo3 user: '.$row['uid'].$row['username'].',  in '.__LINE__.": ".__FUNCTION__.", ".__FILE__, $extKey, 2 );
                    }
            
                
                }
                $vBulletin_sync->post_user_update($row); 
            } 
         }
        return $ret;
    }
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_vbulletin_connect/class.ux_tx_felogin_pi1.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_vbulletin_connect/class.ux_tx_felogin_pi1.php']);
}

?>
