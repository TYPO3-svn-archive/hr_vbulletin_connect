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


require_once(t3lib_extMgm::extPath('hr_vbulletin_connect').'class.user_feuser_vbulletinuser_sync.php');


class user_tcemain_cmdmap {
    var $extKey = 'hr_vbulletin_connect';
    

/**
 * Funktion is called before a typo3 user is deleted. The function try to delete the
 equivalent vBulletin user, if any exists.

 * @param       string          $command: The TCEmain command, fx. 'delete'
 * @param       string          $table: The table TCEmain is currently processing
 * @param       string          $id: The records id (if any)
 * @param       array           $value: The new value of the field which has been changed
 * @param       object          $pObj: Reference to the parent object (TCEmain)
 * @return      void
 * @access public
 */
    function processCmdmap_preProcess($command, &$table, $id, $value, &$pObj){
        //debug($command);
        if($table != 'fe_users'){
            return;
        }
        if($command != 'delete'){
            return;
        }
        $vBulletin_sync = t3lib_div::makeInstance('user_feuser_vbulletinuser_sync');
        //debug($id);
        $vBulletin_sync->user_delete($id);
        if (!empty($vBulletin_sync->vBulletin_userdata->errors)){
                $errormsg = "cannot delete equivalent vBulletin user but typo3 user will be deleted (maybe a vBulletin user don't exists already): \n";
                foreach($vBulletin_sync->vBulletin_userdata->errors as $key => $value){
                    $errormsg .= "$key: $value | \n";
                }
                $pObj->log($table,$id,2,0,1,$errormsg,0,array($table));
                //$table = '';
                return;
        }
     }
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_vbulletin_connect/class.user_tcemain_cmdmap.php'])    {
     include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_vbulletin_connect/class.user_tcemain_cmdmap.php']);
}

?>