<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Herbert Roider (herbert.roider@utanet.at)
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

require_once(t3lib_extMgm::extPath('hr_vbulletin_connect').'class.user_feuser_vbulletinuser_sync.php');


function user_forgotPassword(&$params, &$ref) {
        
        $extKey = "hr_vbulletin_connect";
        //echo "herberts::forgotPassword";
        //exit();
        $d=$GLOBALS['TSFE']->getStorageSiterootPids();
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid, username, password', 'fe_users', 'email='.$GLOBALS['TYPO3_DB']->fullQuoteStr(trim($ref->piVars['DATA']['forgot_email']), 'fe_users').' AND pid='.intval($d['_STORAGE_PID']).' '.$GLOBALS['TSFE']->cObj->enableFields('fe_users'));
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
        return true;
}



?>
