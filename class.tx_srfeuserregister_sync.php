<?php
    /***************************************************************
    *  Copyright notice
    *
    *  (c) 2008  Herbert Roider (herbert.roider@utanet.at)
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
    *
    * @author  Herbert Roider (herbert.roider@utanet.at)
     
    */
    /* vBulletin connector:
    */
    // This file ist included with typoscript in the static Template:
    //require_once(t3lib_extMgm::extPath('hr_vbulletin_connect')."user_vBulletin_global.php");
    require_once(t3lib_extMgm::extPath('hr_vbulletin_connect').'class.user_feuser_vbulletinuser_sync.php');
     
     
    class tx_srfeuserregister_sync {
        /* this is our vBulletin Datamanager
        see: http://www.vbulletin.com/docs/html?manualversion=30601500
        */
        var $vBulletin_sync;
         
        // Constructor:
        function tx_srfeuserregister_sync() {
           t3lib_div::devLog(__FILE__." ".__LINE__ , 'hr_vbulletin_connect', 2 );
 
           ///////////////////////////////////////////////////////////////
            // invoke vBulletin:
            $this->vBulletin_sync = t3lib_div::makeInstance('user_feuser_vbulletinuser_sync');
            ///////////////////////////////////////////////////////////////////////
        }
         
         
         
         
        function registrationProcess_beforeConfirmCreate(&$recordArray, &$controlDataObj) {
//             //var_dump($controlDataObj);
//             print($controlDataObj->getCmd());
//             print($controlDataObj->getCmdKey() );
//             print($controlDataObj->getMode() );
            //$controlDataObj->failure = "username,password";
            
            if($controlDataObj->getMode() == MODE_NORMAL){
                return;
            }
            // in the case of this hook, the record array is passed by reference
            // in this example hook, we generate a username based on the first and last names of the user
             
            //echo "registrationProcess_beforeConfirmCreate";
            //print_r($recordArray);
            /*
            Let vBulletin verify the values if set:
            */
            $tempArr = array();
            $vBulletin_fields = array("username", "email", "www");
            foreach($vBulletin_fields as $theField) {
                if (! isset($recordArray[$theField])) {
                    continue;
                }
                //$errormsg = "vBulletin Error Message:<br />";
                switch($theField) {
                    case 'email':
                    if (!$this->vBulletin_sync->vBulletin_userdata->verify_useremail($recordArray[$theField])) {
                        //print("vbulletin: email fails, maybe already existing");
                    }
                    break;
                    case 'username':
                     
                    if (!$this->vBulletin_sync->verify_username($recordArray[$theField])) {
                        //print("vBulletin: username fails");
                    }
                    break;
                    case 'www':
                    // This value must be empty or a valid url:
                    if (!$this->vBulletin_sync->vBulletin_userdata->verify_homepage($recordArray[$theField])) {
                        //print("vBulletin: www fails");
                         
                    }
                    break;
                     
                     
                }
                 
            }
             
        }
         
        function registrationProcess_afterSaveEdit($recordArray, &$invokingObj) {
           // echo " registrationProcess_afterSaveEdit";
           // print_r($recordArray);
            
            // fetch the userdate from db because of the field: tx_hrvbulletinconnect_vbulletin_user_id
            $typo3_user_from_db = $GLOBALS['TSFE']->sys_page->getRawRecord("fe_users", $this->dataArr['uid']);
            $vb_user = fetch_userinfo($typo3_user_from_db['tx_hrvbulletinconnect_vbulletin_user_id']);
            if (is_array($vb_user)) {
                //echo "existing";
                $this->vBulletin_sync->vBulletin_userdata->set_existing($vb_user);
                
            }
            $vBulletin_userdata = $this->vBulletin_sync->user_update($recordArray);
            if (!empty($vBulletin_userdata->errors)) {
                $errorlist = '';
                foreach ($vBulletin_userdata->errors AS $index => $error) {
                    $errorlist .= "<li>$error</li>";
                }
                //print($errorlist);
                return;
            }
            $this->vBulletin_sync->post_user_update($recordArray);

        }
         
        function registrationProcess_beforeSaveDelete($recordArray, &$invokingObj) {
            //echo "  registrationProcess_beforeSaveDelete";
            //print_r($recordArray);
            if (!$vBulletin_userdata = $this->vBulletin_sync->user_delete($recordArray['uid'])) {
                
               //print("error: cannot delete vbulletin user ");
            }
             
        }
         
        function registrationProcess_afterSaveCreate($recordArray, &$invokingObj) {
            //echo "  registrationProcess_afterSaveCreate";
            //print_r($recordArray);
            $vBulletin_userdata = $this->vBulletin_sync->user_create($recordArray);
            if (!empty($vBulletin_userdata->errors)) {
                $errorlist = '';
                foreach ($vBulletin_userdata->errors AS $index => $error) {
                    $errorlist .= "<li>".$error."</li>";
                }
                
                //print($errorlist);
                return;
            }
             
            $this->vBulletin_sync->post_user_create($recordArray);
             
             
        }
         
        function confirmRegistrationClass_preProcess(&$recordArray, &$invokingObj) {
            // in the case of this hook, the record array is passed by reference
            // you may not see this echo if the page is redirected to auto-login
            //echo "confirmRegistrationClass_preProcess";
            //print_r($recordArray);
             
             
             
             
        }
         
        function confirmRegistrationClass_postProcess($recordArray, &$invokingObj) {
            // you may not see this echo if the page is redirected to auto-login
            //echo "confirmRegistrationClass_postProcess";
            //print_r($recordArray);
             
            $vBulletin_userdata = $this->vBulletin_sync->user_update($recordArray);
            $this->vBulletin_sync->post_user_update($recordArray);
             
             
             
             
        }
         
        function addGlobalMarkers(&$markerArray, &$invokingObj) {
//             echo "addGlobalMarkers";
//             $invokingObj->controlData->setFailure(true);
//             $invokingObj->data->failureArr[] = 'email';
//             $invokingObj->data->inError['email'] = TRUE;
//             $invokingObj->data->failureMsg['email'][] = "hallo ein Test";
//             $invokingObj->data->setError('###TEMPLATE_NO_PERMISSIONSdddd###');
             
             
        }
    
        /** Check the data against vBulletin
         *
         * @param type $theTable
         * @param type $dataArray     the fields of the form
         * @param type $origArray     userrecord, if the cmdKey is "edit" or an empty array if the cmdKey is "create"
         * @param type $markContentArray
         * @param type $cmdKey
         * @param type $requiredArray
         * @param type $theField      name of the table field
         * @param type $cmdParts
         * @param type $invokingObj   tx_srfeuserregister_data
         * @return string   if no error it return '', and in case of an error, the $theField 
         */
        function evalValues($theTable, $dataArray, $origArray, $markContentArray, $cmdKey, $requiredArray, $theField, $cmdParts, &$invokingObj) {
                
                /* if the user already exists, set the existing vbulletin user for the datamanager 
                 */
                if (is_array($origArray) && key_exists('tx_hrvbulletinconnect_vbulletin_user_id', $origArray)) {
                        $vb_user = fetch_userinfo($origArray['tx_hrvbulletinconnect_vbulletin_user_id']);
                        if (is_array($vb_user)) {
                                $this->vBulletin_sync->vBulletin_userdata->set_existing($vb_user);
                        }
                }
                /*
                  Let vBulletin verify the values if set:
                 */

                if ($theField === 'username') {
                        if (!$this->vBulletin_sync->vBulletin_userdata->verify_username($dataArray[$theField])) {
                                foreach ($this->vBulletin_sync->vBulletin_userdata->errors as $key => $value) {
                                        $errormsg .= "vBulletin: $value <br>";
                                }
                                $invokingObj->inError[$theField] = TRUE;
                                $invokingObj->failureMsg[$theField][] = $errormsg;
                                return $theField;
                        }
                } else if ($theField === 'email') {
                        if (!$this->vBulletin_sync->vBulletin_userdata->verify_useremail($dataArray[$theField])) {
                                foreach ($this->vBulletin_sync->vBulletin_userdata->errors as $key => $value) {
                                        $errormsg .= "vBulletin: $value <br>";
                                }
                                $invokingObj->inError[$theField] = TRUE;
                                $invokingObj->failureMsg[$theField][] = $errormsg;
                                return $theField;
                        }
                } else if ($theField === 'www') {
                        if (!$this->vBulletin_sync->vBulletin_userdata->verify_homepage($dataArray[$theField])) {
                                foreach ($this->vBulletin_sync->vBulletin_userdata->errors as $key => $value) {
                                        $errormsg .= "vBulletin: $value <br>";
                                }
                                $invokingObj->inError[$theField] = TRUE;
                                $invokingObj->failureMsg[$theField][] = $errormsg;
                                return $theField;
                        }
                } else if ($theField === 'password') {
                        if (!$this->vBulletin_sync->vBulletin_userdata->verify_password($dataArray[$theField])) {
                                foreach ($this->vBulletin_sync->vBulletin_userdata->errors as $key => $value) {
                                        $errormsg .= "vBulletin: $value <br>";
                                }
                                $invokingObj->inError[$theField] = TRUE;
                                $invokingObj->failureMsg[$theField][] = $errormsg;
                                return $theField;
                        }
                }
                return '';
        }

}
     
if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/hr_vbulletin_connect/class.tx_srfeuserregister_sync.php']) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/hr_vbulletin_connect/class.tx_srfeuserregister_sync.php']);
}     
     
?>
