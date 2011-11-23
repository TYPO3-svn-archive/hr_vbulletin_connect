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

/* vBulletin connector:
Achtung: die Klassenvariablen funktionieren nicht!!! irgendwas geht da nicht. konstruktor wird 
nur einmal aufgefufen, was ja passt, nur die Klassenvariablen haben immer nur den Wert, der im Konstruktor
initiiert wird, egal, wann man einen Wert in einer funktion überschreibt, in der nächsten hat sie wieder
den Wert vom Konstruktor.
*/
require_once(t3lib_extMgm::extPath('hr_vbulletin_connect').'class.user_feuser_vbulletinuser_sync.php');


class user_tcemain_datamap {
    //var $vBulletin_sync;// geht nicht
    var $typo3_user;
    //var $vbulletinuser_exists;
    var $test;
    var $extKey = 'hr_vbulletin_connect';
    
    /** Konstructor */
    function user_tcemain_datamap(){
        //echo "konstructor";
        //$this->test = "111";
    }
    function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, &$pObj){
        if($table != 'fe_users'){
            return;
        }
     }

    function processDatamap_postProcessFieldArray($status, &$table, $id, &$fieldArray, &$pObj){
        if($table != 'fe_users'){
            return;
        }
        // Es soll das Object immer wieder neu gemacht werden
//      if(! is_object($pObj->vBulletin_sync)){
        $pObj->vBulletin_sync = t3lib_div::makeInstance('user_feuser_vbulletinuser_sync');
//       }
        $pObj->vBulletin_sync->test = 1;
        
        if (!t3lib_div::testInt($id)) { 
            $typo3_user =  $fieldArray;    
        }else{
            //$typo3_user = $pObj->vBulletin_sync->get_typo3_user($id);
            $typo3_user_from_db = $pObj->vBulletin_sync->get_typo3_user($id);
            // if it doesn't exists a vBulletin user, but a typo3 user already exists, so it will be tried to create
            // a equivalent vBulletin user, and therefore
            if( ! $typo3_user_from_db['tx_hrvbulletinconnect_vbulletin_user_id']){
                unset($typo3_user_from_db['password']);// unset the password because it is a md5 hash and this must not converted to md5 again, only when the password is changed and is in the $fieldArray as plain text.
                $typo3_user = array_merge($typo3_user_from_db, $fieldArray);
                $pObj->log($table,$id,2,0,0,"no vBulletin exists already for this typo3 frontend user, so will try to create a vBulletin user",0,array($table));
            
            }else{
                $typo3_user = $fieldArray;
                $typo3_user['uid'] = $id;
                $typo3_user['tx_hrvbulletinconnect_vbulletin_user_id'] = $typo3_user_from_db['tx_hrvbulletinconnect_vbulletin_user_id'];
            }
        }

         // If the Record is not new, and if a vBulletin user is found, then set this user as existing for vBulletin
        $vbulletinuser_exists = false;
        if ($typo3_user['tx_hrvbulletinconnect_vbulletin_user_id']) {
                $vb_user = fetch_userinfo($typo3_user['tx_hrvbulletinconnect_vbulletin_user_id']);
                if(is_array($vb_user)){
                    $pObj->vBulletin_sync->vBulletin_userdata->set_existing($vb_user);
                    $vbulletinuser_exists = true;
                }else{
                    $pObj->log($table,$id,5,0,1,"There is no vBulletin user with userid=".$typo3_user['tx_hrvbulletinconnect_vbulletin_user_id'].", maybe this user was deleted in vBulletin, but the fe_user wasn't automatically deleted",0,array($table));
                    $table = '';
                    return;
                }
                
//                 else{
//                     /* There should be a equivalent vBulletin user already exists, but there is no one!!
//                     */
//                     $password_hash = md5(md5($typo3_user_from_db['password']).$typo3_user_from_db['tx_hrvbulletinconnect_vbulletin_user_salt']);
//                     // try if the old password match
//                     if(!strcmp($password_hash, $typo3_user_from_db['tx_hrvbulletinconnect_vbulletin_user_password'])){
//                         $current_password = $typo3_user_from_db['password'];
//                         $pObj->vBulletin_sync->user_create($typo3_user_from_db);
//                         if (!empty($pObj->vBulletin_sync->vBulletin_userdata->errors)){
//                             debug("fehler");
//                             $errormsg = "It should exists a equivalent vBulletin user, but there is no. I want to create a new one, but this does fail:<br>\n";
//                             foreach($pObj->vBulletin_sync->vBulletin_userdata->errors as $key => $value){
//                                 $errormsg .= "$key: $value | \n";
//                             }
//                             //debug($typo3_user);
//                             $pObj->log($table,$id,5,0,1,$errormsg,0,array($table));
//                             $fieldArray = array();
//                             $table = '';
//                             return;
//                          }
//                          $pObj->vBulletin_sync->post_user_create($typo3_user_from_db);
//                          if (!empty($pObj->vBulletin_sync->vBulletin_userdata->errors)){
//                             debug("fehler");
//                             $errormsg = "It should exists a equivalent vBulletin user, but there is no. I want to create a new one, but this does fail: (2):<br>\n";
//                             foreach($pObj->vBulletin_sync->vBulletin_userdata->errors as $key => $value){
//                                 $errormsg .= "$key: $value | \n";
//                             }
//                             //debug($typo3_user);
//                             $pObj->log($table,$id,5,0,1,$errormsg,0,array($table));
//                             $fieldArray = array();
//                             $table = '';
//                             return;
//                          }
//                          $pObj->vBulletin_sync->vBulletin_userdata->set_existing($pObj->vBulletin_sync->vBulletin_userdata->user);
//                          $vbulletinuser_exists = true;
//                     }else{
//                         $pObj->log($table,$id,5,0,1,"a equivalent vBulletin user should already exists, but there is no one. The old password doesn't match, so I cannot create a new one: ". $typo3_user_from_db['password']." salt=".$typo3_user_from_db['tx_hrvbulletinconnect_vbulletin_user_salt'] .", passwordhash=".$typo3_user_from_db['tx_hrvbulletinconnect_vbulletin_user_password'].", $password_hash: table '%s' ",0,array($table));
//                     }
//                 }
        
        
        
        }
        
        
        /*
        in this->dataArr sind jetzt alle Felder durch typo3 abgetestet und eventuell ganz gelöscht worden.
        Jetzt müssen noch die Tests von vBulletin gemacht werden.
        */
        $ok = true;
        foreach($typo3_user as $theField => $value){
            switch($theField){
            case 'email':
                if(!$pObj->vBulletin_sync->vBulletin_userdata->verify_useremail($typo3_user['email'])){
                    $pObj->log($table,$id,5,0,1,"vBulletin: $theField is not valid: table '%s' ",0,array($table));
                    $ok = false;
                }
                break;
            case 'username':
                if(!$pObj->vBulletin_sync->verify_username($typo3_user['username'])){
                    $pObj->log($table,$id,5,0,1,"vBulletin: $theField is not valid: table '%s' ",0,array($table));
                    $ok = false;
                }
                break;
            case 'www':
                // der Wert darf auch leer sein, oder sonst eine gültige Url.
                //echo "www";
                if(!$pObj->vBulletin_sync->vBulletin_userdata->verify_homepage($typo3_user['www'])){
                    $pObj->log($table,$id,5,0,1,"vBulletin: $theField is not valid: table '%s' ",0,array($table));
                    $ok = false;
                }
                // because vBulletin maybe modifies the field:
                $fieldArray['www'] = $typo3_user['www'];
                break;
            case 'password':
                if(!$pObj->vBulletin_sync->vBulletin_userdata->verify_md5($fieldArray['password'])){
                      $fieldArray['password'] = md5($fieldArray['password']);              
                }
                break;
//             case 'usergroup':
//             case 'disable':
//                 $current_vb_groups = array_merge(array($this->vBulletin_userdata->fetch_field('usergroupid')), explode(',', $this->vBulletin_userdata->fetch_field('membergroupids')));
//                 debug($this->vBulletin_userdata->user);
//                 debug("current vb_groups:");
//                 debug($current_vb_groups);
//                 $disable = $typo3_user_from_db['disable'];
//                 if(!empty($typo3_user['disable'])){
//                     $disable = $typo3_user['disable'];
//                 }
//                 $new_vb_groups = $this->get_vBulletin_usergroups($current_vb_groups, $typo3_user['usergroup'],$disable); 
//                 debug($new_vb_groups);
//                 $this->vBulletin_userdata->set('usergroupid', array_shift($new_vb_groups));
//                 $this->vBulletin_userdata->set('membergroupids', implode($new_vb_groups, ','));
// 
//                 break;
            }
        }
        // cancel if a field is not valid:
        if(! $ok){
            $table = '';
            $fieldArray = array();
            return;
        }
        
        // override the typo3_user with the new values:
        //$typo3_user = array_merge($typo3_user, $fieldArray);
        

        
        if(!$vbulletinuser_exists){
            //debug("user_create");

            $pObj->vBulletin_sync->user_create($typo3_user);
            if (!empty($pObj->vBulletin_sync->vBulletin_userdata->errors)){
                //debug("fehler");
                $errormsg = "vBulletin: (user_create) cannot create vBulletin user:<br>\n";
                foreach($pObj->vBulletin_sync->vBulletin_userdata->errors as $key => $value){
                    $errormsg .= "$key: $value | \n";
                }
                //debug($typo3_user);
                $pObj->log($table,$id,5,0,1,$errormsg,0,array($table));
                $fieldArray = array();
                $table = '';
                return;
            }
        }else{
            //debug("user_update");
            $pObj->vBulletin_sync->user_update($typo3_user);
            if (!empty($pObj->vBulletin_sync->vBulletin_userdata->errors)){
                //debug("fehler");
                $errormsg = "vBulletin: (user_update) cannot update vBulletin user:<br>\n";
                foreach($pObj->vBulletin_sync->vBulletin_userdata->errors as $key => $value){
                    $errormsg .= "$key: $value | \n";
                }
                $pObj->log($table,$id,5,0,1,$errormsg,0,array($table));
                $fieldArray = array();
                $table = '';
                return;
           }
           
           
           //debug($fieldArray);
        }
    
    }
    function processDatamap_afterDatabaseOperations($status, $table, $id, &$fieldArray, &$pObj){
        if($table != 'fe_users'){
            return;
        }
         if($status == 'new'){
            $id = $pObj->substNEWwithIDs[$id];
         }
         if(empty($fieldArray)){
            //debug("fieldarray is emty");
            return;
         }
         
         $typo3_user = $pObj->vBulletin_sync->get_typo3_user($id);
         if(!is_array($typo3_user)){
            if(TYPO3_DLOG){
                t3lib_div::devLog('critical error: no typo3 user is found: uid='.$id." in ".__LINE__.": ".__FUNCTION__.", ".__FILE__, $this->extKey, 2 );
            }
            return;
         }
  
        $vbulletinuser_exists = false;
        if ($typo3_user['tx_hrvbulletinconnect_vbulletin_user_id']) {
                $vb_user = fetch_userinfo($typo3_user['tx_hrvbulletinconnect_vbulletin_user_id']);
                if(is_array($vb_user)){
                    $pObj->vBulletin_sync->vBulletin_userdata->set_existing($vb_user);
                    $vbulletinuser_exists = true;
                }else{
                    if(TYPO3_DLOG){
                        t3lib_div::devLog('critical error: vbulletin_user_id is set, but no such vBulletin user is found: '.$typo3_user['username'].', uid='.$typo3_user['uid']." in ".__LINE__.": ".__FUNCTION__.", ".__FILE__, $this->extKey, 2 );
                    }
                    return;
                }
        }         
         
         if(!$vbulletinuser_exists){
            //debug("post_user_create");
            $pObj->vBulletin_sync->post_user_create($typo3_user);
            if (!empty($pObj->vBulletin_sync->vBulletin_userdata->errors)){
                //debug("fehler");
                $errormsg = "post_user_create vBulletin: cannot update vBulletin user:<br>\n";
                foreach($pObj->vBulletin_sync->vBulletin_userdata->errors as $key => $value){
                    $errormsg .= "$key: $value | \n";
                }
                $pObj->log($table,$id,1,0,1,$errormsg,0,array($table));
                return;
            }
            $vbulletinuser_exists = true;
         }else{
            //debug("post_user_update");
            $pObj->vBulletin_sync->post_user_update($typo3_user);
            if (!empty($pObj->vBulletin_sync->vBulletin_userdata->errors)){
                //debug("fehler");
                $errormsg = "post_user_create vBulletin: cannot update vBulletin user:<br>\n";
                foreach($pObj->vBulletin_sync->vBulletin_userdata->errors as $key => $value){
                    $errormsg .= "$key: $value | \n";
                }
                $pObj->log($table,$id,2,0,1,$errormsg,0,array($table));
                return;
           }
        }
        //debug($fieldArray);
        //$typo3_user = $pObj->vBulletin_sync->get_typo3_user($id);
     }


}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_vbulletin_connect/class.user_tcemain_datamap.php'])    {
     include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_vbulletin_connect/class.user_tcemain_datamap.php']);
}

?>