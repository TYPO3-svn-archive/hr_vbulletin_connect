<?php
    /***************************************************************
    *  Copyright notice
    *
    *  (c) 2006  Herbert Roider (herbert.roider@utanet.at)
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
     
     
     
    This class extend the user_feAdmin and is made for use with hr_vbulletin_connect instead of user_feAdmin.
    This libary verify the date for vBulletin and can handle the hashed password.
     
    It should also be an example for programmers how to invoke the
    class user_feuser_vbulletinuser_sync in your own scripts. It shows to verify the data to vBulletin,
    make a instance of the class user_feuser_vbulletinuser_sync, create user, update user and delete user.
     
    */
     
    //       $handle_1 = fopen ("/home/herbert/htdocs/copadata_2/fileadmin/log_1.txt", "a+");
    //       fwrite($handle_1, __FILE__." wird eigebunden: currentDir = \"".getcwd()."\"\n");
    //       fclose($handle_1);
     
     
    if (t3lib_div::int_from_ver($GLOBALS['TYPO_VERSION']) < t3lib_div::int_from_ver("4.0")) {
        require_once ('media/scripts/fe_adminLib.inc');
    } else {
        require_once ('typo3/sysext/cms/tslib/media/scripts/fe_adminLib.inc');
    }
     
    require_once(t3lib_extMgm::extPath('kb_md5fepw').'ux_feadminLib.php');
     
    require_once(PATH_tslib.'class.tslib_pibase.php');
     
     
    /* vBulletin connector:
    */
    // This file ist included with typoscript in the static Template:
    //require_once(t3lib_extMgm::extPath('hr_vbulletin_connect')."user_vBulletin_global.php");
    require_once(t3lib_extMgm::extPath('hr_vbulletin_connect').'class.user_feuser_vbulletinuser_sync.php');
     
    class user_vbulletin_feAdmin extends ux_user_feAdmin {
        var $prefixId = 'user_vbulletin_feAdmin';
        // Same as class name
        var $scriptRelPath = 'class.user_vbulletin_feAdmin.php'; // Path to this script relative to the extension dir.
        var $extKey = 'hr_vbulletin_connect'; // The extension key.
         
        var $csConvObj; // Irgendwas fÃ¼r die Spracheinstellungen und Zeichensatz
         
        var $pibase;// contains an object from tslib_pibase only for translation (pi_loadLL and pi_getLL)
        var $cObj;
         
        /* this is our vBulletin Datamanager
        see: http://www.vbulletin.com/docs/html?manualversion=30601500
        */
        var $vBulletin_sync;
         
         
        function init($content, $conf) {
            ///////////////////////////////////////////////////////////////
            // invoke vBulletin:
            $this->vBulletin_sync = t3lib_div::makeInstance('user_feuser_vbulletinuser_sync');
            ///////////////////////////////////////////////////////////////////////
             
             
            $content = parent::init($content, $conf);
            return $content;
             
        }
         
         
         
        /** verifying some field for vBulletin
        */
        function evalValues() {
            //echo "evalVlues";
            //debug($this->dataArr);
             
             
            parent::evalValues();
            //debug($this->dataArr);
             
             
            /* if feAdmin has found some errors, there is no need to look for another errors
            */
            if ($this->failure) {
                return;
            }
            /* if cmdKey=edit, an existing user will be updated, so tell vBulletin which user to use
            (function "set_existing") */
            if ($this->cmdKey == 'edit') {
                $typo3_user_from_db = $GLOBALS['TSFE']->sys_page->getRawRecord("fe_users", $this->dataArr['uid']);
                $vb_user = fetch_userinfo($typo3_user_from_db['tx_hrvbulletinconnect_vbulletin_user_id']);
                if (is_array($vb_user)) {
                    //echo "existing";
                    $this->vBulletin_sync->vBulletin_userdata->set_existing($vb_user);
                }
                 
            }
            //debug("vberrors".$this->vBulletin_sync->vBulletin_userdata->errors);
             
            /*
            Let vBulletin verify the values if set:
            */
            $tempArr = array();
            $vBulletin_fields = array("username", "email", "www");
            foreach($vBulletin_fields as $theField) {
                if (! isset($this->dataArr[$theField])) {
                    continue;
                }
                //$errormsg = "vBulletin Error Message:<br />";
                switch($theField) {
                    case 'email':
                    if (!$this->vBulletin_sync->vBulletin_userdata->verify_useremail($this->dataArr[$theField])) {
                        //debug("vBulletin: email fails");
                        $this->failureMsg[$theField][] = $this->getFailure($theField, 'vbulletin_verify', "vBulletin: email is not valid");
                        $tempArr[] = $theField;
                    }
                    break;
                    case 'username':
                     
                    if (!$this->vBulletin_sync->verify_username($this->dataArr[$theField])) {
                        //debug("vBulletin: username fails");
                        $this->failureMsg[$theField][] = $this->getFailure($theField, 'vbulletin_verify', "vBulletin: username is not valid");
                        $tempArr[] = $theField;
                    }
                    break;
                    case 'www':
                    // This value must be empty or a valid url:
                    if (!$this->vBulletin_sync->vBulletin_userdata->verify_homepage($this->dataArr[$theField])) {
                        //debug("vBulletin: www fails");
                        $this->failureMsg[$theField][] = $this->getFailure($theField, 'vbulletin_verify', "vBulletin: url is not valid");
                        $tempArr[] = $theField;
                         
                    }
                    break;
                     
                     
                }
                //debug($theField);
                $this->markerArray['###EVAL_ERROR_FIELD_'.$theField.'###'] = is_array($this->failureMsg[$theField]) ? implode('<br />', $this->failureMsg[$theField]) :
                 '';
                 
                 
            }
            //debug($this->failure);
            $this->failure = implode(',', $tempArr);
            //$failure will show which fields were not OK
             
             
             
        }
         
        /*
         
        */
        function save() {
            //echo "hallo save";
            //debug($this->currentArr);
             
            /* A new user should be created, so let us create also a equivalent vBulletin user:
            */
            if ($this->cmdKey == 'create') {
                $vBulletin_userdata = $this->vBulletin_sync->user_create($this->dataArr);
                if (!empty($vBulletin_userdata->errors)) {
                    $errorlist = '';
                    foreach ($vBulletin_userdata->errors AS $index => $error) {
                        $errorlist .= "<li>".$error."</li>";
                    }
                    $this->error = $errorlist;
                    return;
                }
                 
                parent::save();
                if (!$this->saved) {
                    /* A vBulletinuser is created, but no equivalent typo3 user, so
                    delete the vBulletin user.
                    */
                    $vBulletin_userdata->delete();
                } else {
                    $this->vBulletin_sync->post_user_create($this->currentArr);
                }
                return;
            }
            // it is necessary to call parent::save() before, because the form is filled with the new values
            //debug($this->currentArr);
             
            parent::save();
            if (!$this->saved) {
                return;
            }
            //debug($this->currentArr);
            //////////////////////////////////////////////////////////////
            $vBulletin_userdata = $this->vBulletin_sync->user_update($this->currentArr);
            if (!empty($vBulletin_userdata->errors)) {
                $errorlist = '';
                foreach ($vBulletin_userdata->errors AS $index => $error) {
                    $errorlist .= "<li>$error</li>";
                }
                $this->error = $errorlist;
                return;
            }
            $this->vBulletin_sync->post_user_update($this->currentArr);
            return;
        }
         
        /* Tries to delete the equivalent vBulletin user, and if not successful, a errormesage "no permissions" is shown
        */
        function deleteRecord() {
            if (!$vBulletin_userdata = $this->vBulletin_sync->user_delete($this->recUid)) {
                $this->error = '###TEMPLATE_NO_PERMISSIONS###';
                return;
            }
            return parent::deleteRecord();
        }
         
         
         
        /** This function is equal as the parent function with some additional code to handle the equivalent vBulletin user. see the comments in the code.
        *
        * @return string  HTML content displaying the status of the action
        */
        function procesSetFixed() {
            if ($this->conf['setfixed']) {
                $theUid = intval($this->recUid);
                $origArr = $GLOBALS['TSFE']->sys_page->getRawRecord($this->theTable, $theUid);
                $fD = t3lib_div::_GP('fD');
                $sFK = t3lib_div::_GP('sFK');
                 
                $fieldArr = array();
                if (is_array($fD) || $sFK == 'DELETE') {
                    if (is_array($fD)) {
                        reset($fD);
                        while (list($field, $value) = each($fD)) {
                            $origArr[$field] = $value;
                            $fieldArr[] = $field;
                        }
                    }
                    $theCode = $this->setfixedHash($origArr, $origArr['_FIELDLIST']);
                    #error_log("curr code=$theCode, authcode=".$this->authCode."ende");
                    if (!strcmp($this->authCode, $theCode)) {
                        /////////////////////////////////////////////////////// VBulletin
                        if ($sFK == 'DELETE') {
                            $user = $GLOBALS['TSFE']->sys_page->getRawRecord($this->theTable, $uid);
                            $vBulletin_userdata = $this->vBulletin_sync->user_delete($user);
                        } else {
                        }
                        //////////////////////////////////////////////////////////////
                        if ($sFK == 'DELETE') {
                            $this->cObj->DBgetDelete($this->theTable, $theUid, TRUE);
                        } else {
                            $newFieldList = implode(',', array_intersect(t3lib_div::trimExplode(',', $this->fieldList), t3lib_div::trimExplode(',', implode($fieldArr, ','), 1)));
                            $this->cObj->DBgetUpdate($this->theTable, $theUid, $fD, $newFieldList, TRUE);
                            $this->currentArr = $GLOBALS['TSFE']->sys_page->getRawRecord($this->theTable, $theUid);
                            $this->userProcess_alt($this->conf['setfixed.']['userFunc_afterSave'], $this->conf['setfixed.']['userFunc_afterSave.'], array('rec' => $this->currentArr, 'origRec' => $origArr));
                        }
                        /////////////////////////////////////////////////////// VBulletin
                        if ($sFK == 'DELETE') {
                        } else {
                            $vBulletin_userdata = $this->vBulletin_sync->user_update($this->currentArr);
                            $this->vBulletin_sync->post_user_update($this->currentArr);
                        }
                        //////////////////////////////////////////////////////////////
                         
                        // Outputting template
                        $this->markerArray = $this->cObj->fillInMarkerArray($this->markerArray, $origArr, '', TRUE, 'FIELD_', $this->recInMarkersHSC);
                        $content = $this->getPlainTemplate('###TEMPLATE_SETFIXED_OK_'.$sFK.'###');
                        if (!$content) {
                            $content = $this->getPlainTemplate('###TEMPLATE_SETFIXED_OK###');
                        }
                        // Clearing cache if set:
                        $this->clearCacheIfSet();
                    }
                     else $content = $this->getPlainTemplate('###TEMPLATE_SETFIXED_FAILED###');
                }
                 else $content = $this->getPlainTemplate('###TEMPLATE_SETFIXED_FAILED###');
            }
            return $content;
        }
        /**
        * It is necessary that the field password don't contain the current database field "password", because
        this value is not really used anymore, only to store the plain text password.
        *
        * @param       array           The data array
        * @return      array           The processed input array
        * @see displayCreateScreen(), displayEditForm(), tslib_cObj::getUpdateJS()
        */
        function modifyDataArrForFormUpdate($inputArr) {
            $inputArr['tx_hrvbulletinconnect_vbulletin_user_id'] = "";
            $inputArr['tx_hrvbulletinconnect_vbulletin_user_salt'] = "";
            $inputArr['tx_hrvbulletinconnect_vbulletin_user_password'] = "";
            //debug($inputArr);
            return parent::modifyDataArrForFormUpdate($inputArr);
             
        }
         
        /**
        Remove also the subparts for the common fields www, username, password.
        The Marker:  ###SUB_REQUIRED_FIELD_xxx### is also used for Messages, if vBulletin fails.
         
        Remove required parts from template code string
        *       Works like this:
        *               - You insert subparts like this ###SUB_REQUIRED_FIELD_'.$theField.'### in the template that tells what is required for the field, if it's not correct filled in.
        *               - These subparts are all removed, except if the field is listed in $failure string!
        *
        *              Only fields that are found in $this->requiredArr is processed.
        *
        * @param       string          The template HTML code
        * @param       string          Comma list of fields which has errors (and therefore should not be removed)
        * @return      string          The processed template HTML code
        */
        function removeRequired($templateCode, $failure) {
            $templateCode_new = parent::removeRequired($templateCode, $failure);
            //debug($failure);
            $common_fields = array("www", "username", "password", "email");
             
            while (list(, $theField) = each($common_fields)) {
                if (!t3lib_div::inList($failure, $theField)) {
                    $templateCode_new = $this->cObj->substituteSubpart($templateCode_new, '###SUB_REQUIRED_FIELD_'.$theField.'###', '');
                }
            }
            return $templateCode_new;
        }
         
        /**
        * This function is mostly the same as from  Kraft Bernhard <kraftb@gmx.net>
        only additional code to update the vBulletin user
        *
        * @return      string          HTML content message
        * @see init(),compileMail(), sendMail()
        */
        function sendInfoMail() {
            if ($this->conf['infomail'] && $this->conf['email.']['field']) {
                $fetch = t3lib_div::_GP('fetch');
                if ($fetch) {
                    // Getting infomail config.
                    $key = trim(t3lib_div::_GP('key'));
                    if (is_array($this->conf['infomail.'][$key.'.'])) {
                        $config = $this->conf['infomail.'][$key.'.'];
                    } else {
                        $config = $this->conf['infomail.']['default.'];
                    }
                    $pidLock = '';
                    if (!$config['dontLockPid']) {
                        $pidLock = 'AND pid IN ('.$this->thePid.') ';
                    }
                     
                    // Getting records
                    if (t3lib_div::testInt($fetch)) {
                        $DBrows = $GLOBALS['TSFE']->sys_page->getRecordsByField($this->theTable, 'uid', $fetch, $pidLock, '', '', '1');
                    } elseif ($fetch) {
                        // $this->conf['email.']['field'] must be a valid field in the table!
                        $DBrows = $GLOBALS['TSFE']->sys_page->getRecordsByField($this->theTable, $this->conf['email.']['field'], $fetch, $pidLock, '', '', '100');
                    }
                     
                    // Processing records
                    if (is_array($DBrows)) {
                        $GLOBALS['TSFE']->includeTCA();
                        t3lib_div::loadTCA($this->theTable);
                        $recipient = $DBrows[0][$this->conf['email.']['field']];
                        foreach ($DBrows as $key => $row) {
                            if ($DBrows[$key]['password']) {
                                // to avoid emty passwords in the case that 
                                // kb_md5fepw is installed before feuser_admin.
                                if(! isset($this->conf['defaultPasswordLength'])){
                                     $this->conf['defaultPasswordLength'] = 5;
                                }
                                $new_pw = tx_kbmd5fepw_funcs::generatePassword(intval($this->conf['defaultPasswordLength']));
                                $DBrows[$key]['password'] = $new_pw;
                                $res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->theTable, 'uid='.$row['uid'], array('password' => md5($new_pw)));
                                //error_log("new password=".$new_pw."ende, defaultPasswordLength=".$this->conf['defaultPasswordLength']);
                                //////////////////////////////////////////////////////////////
                                // a hook would be cool
                                $row_1 = array("uid" => $row['uid'], 'password' => $new_pw);
                                $vBulletin_sync = t3lib_div::makeInstance('user_feuser_vbulletinuser_sync');
                                $vBulletin_userdata = $vBulletin_sync->user_update($row_1);
                                if (!empty($vBulletin_userdata->errors)) {
                                    $errorlist = '';
                                    foreach ($vBulletin_userdata->errors AS $index => $error) {
                                        $errorlist .= "$error<br>\n";
                                    }
                                    if (TYPO3_DLOG) {
                                        t3lib_div::devLog('cannot update typo3 user: '.$row_1['uid'].', error='.$errorlist.' in '.__LINE__.": ".__FUNCTION__.", ".__FILE__, $this->extKey, 2 );
                                    }
                                } else {
                                    if (TYPO3_DLOG) {
                                        t3lib_div::devLog('update typo3 user: '.$row_1['uid'].$row_1['username'].',  in '.__LINE__.": ".__FUNCTION__.", ".__FILE__, $this->extKey, 2 );
                                    }
                                     
                                     
                                }
                                $vBulletin_sync->post_user_update($row_1);
                                ///////////////////////////////////////////////////////////
                            }
                        }
                        $this->compileMail($config['label'], $DBrows, $recipient, $this->conf['setfixed.']);
                    } elseif ($this->cObj->checkEmail($fetch)) {
                        $this->sendMail($fetch, '', trim($this->cObj->getSubpart($this->templateCode, '###'.$this->emailMarkPrefix.'NORECORD###')));
                    }
                     
                    $content = $this->getPlainTemplate('###TEMPLATE_INFOMAIL_SENT###');
                } else {
                    $content = $this->getPlainTemplate('###TEMPLATE_INFOMAIL###');
                }
            }
             else $content = 'Error: infomail option is not available or emailField is not setup in TypoScript';
            return $content;
        }
         
    }
     
     
    if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_vbulletin_connect/class.user_vbulletin_feAdmin.php']) {
        include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_vbulletin_connect/class.user_vbulletin_feAdmin.php']);
    }
     
?>
