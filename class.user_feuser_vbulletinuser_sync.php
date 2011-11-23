<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Herbert Roider(herbert.roider@utanet.at)
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
 * @author	Herbert Roider <herbert.roider@utanet.at>
 
 
This Class provides an API to update, create and delete vBulletin users.
It is designed for use in your own scripts. If you have a typo3 user and want to create an equivalent
vBulletin user you can use this class.
 
 
 
 */
//       $handle_1 = fopen ("/home/herbert/htdocs/copadata_2/fileadmin/log_1.txt", "a+");
//       fwrite($handle_1, __FILE__." wird eigebunden: currentDir = \"".getcwd()."\"\n");
//       fclose($handle_1);


//require_once(t3lib_extMgm::extPath('hr_vbulletin_connect')."user_vBulletin_global.php");

//require_once("user_vBulletin_global.php");
//require_once(t3lib_extMgm::extPath('hr_vbulletin_connect').'user_vBulletin_global.php');
//echo PATH_site;

require_once(t3lib_extMgm::extPath('hr_vbulletin_connect').'class.user_hrvbulletinconnect_div.php');


class user_feuser_vbulletinuser_sync {
    
    var $cObj;
    var $conf;
    var $conf_default;
    var $extKey = 'hr_vbulletin_connect';
    //var $vbulletin_config;
    var $vBulletin_userdata;
    var $vbgrops_to_typo3groups;
    var $test;
    
    
    /** Constructor 
    
    
    */
    function user_feuser_vbulletinuser_sync(){
       global $vbulletin;
       
       //echo PATH_site;
       
       $this->test = 0;
       // Die Config-einstellungen aus der localconf.php holen:
       $this->conf_default = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
       // init user datamanager class
       $this->vBulletin_userdata =& datamanager_init('User', $vbulletin, ERRTYPE_ARRAY);
       //         // relation vBulletin usergroupid  -- TYPO3 FE usergroup uid
       //         // Default vBulletin Usergroups, the keys are the vBulletin usergroups 
       $this->vbgrops_to_typo3groups=array(
                1 => $this->conf_default['vB1feGroupsid'], // Unregistered / Not Logged In          not used
                2 => $this->conf_default['vB2feGroupsid'], // Registered Users                      1
                3 => $this->conf_default['vB3feGroupsid'], // Users Awaiting Email Confirmation     not used
                4 => $this->conf_default['vB4feGroupsid'], // (COPPA) Users Awaiting Moderation     not used
                5 => $this->conf_default['vB5feGroupsid'], // Super Moderators                      8
                6 => $this->conf_default['vB6feGroupsid'], // Administrators                        9
                7 => $this->conf_default['vB7feGroupsid'], // Moderators                            6
                // Custom vBulletin groups
                8 => $this->conf_default['vB8feGroupsid'], //Banned Users                          10
                9 => $this->conf_default['vB9feGroupsid'], 
                10 => $this->conf_default['vB10feGroupsid'], 
                11 => $this->conf_default['vB11feGroupsid'], 
                12 => $this->conf_default['vB12feGroupsid'], 
                13 => $this->conf_default['vB13feGroupsid'], 
                14 => $this->conf_default['vB14feGroupsid'], 
                15 => $this->conf_default['vB15feGroupsid'], 
        );
       // unset all emty values and invalid values:
       foreach($this->vbgrops_to_typo3groups as $key => $value){
            if(!intval($value) || $value <= 0){
                unset($this->vbgrops_to_typo3groups[$key]);
            }
            
        }
        //debug($this->vbgrops_to_typo3groups);

    
    }

    /** This function create an equivalent vBulletin user. It is necessary to call post_user_create after this function.
    

    *
    * @param       array  array with the fields for the new fe_user
    * @return      userdatamanager of vBulletin. In case of an error the array $this->vBulletin_userdata->errors 
                contains the errormessages, otherwise this array is emty. Refer the vBulletin documentation
                for Data Managers and the vBulletin file: class_dm_user.php for details.
    */     
    function user_create($typo3_user)
    {
        global $vbulletin; 

        $div = t3lib_div::makeinstance('user_hrvbulletinconnect_div');
        $div->convert_fe_user_charset($typo3_user,$div->get_typo3_charset(), $div->get_vbulletin_charset() );



        // check for missing fields
        if (empty($typo3_user['username'])
                OR empty($typo3_user['email'])
                OR empty($typo3_user['password'])
                
        )
        {
                $this->vBulletin_userdata->error('fieldmissing');
                //echo "error 1";
        }
        // set password ( the password can be plain text or a md5 hash)
        //$this->vBulletin_userdata->set('password', ($vbulletin->GPC['password_md5'] ? $vbulletin->GPC['password_md5'] : $vbulletin->GPC['password']));
        $this->vBulletin_userdata->set('password', $typo3_user['password']); // pass5
        
        if(!$this->verify_username($typo3_user['username'])){
            return $this->vBulletin_userdata;
        }
        
        $this->vBulletin_userdata->set('email', $typo3_user['email']);
        $this->vBulletin_userdata->set('username', $typo3_user['username']);
        $this->vBulletin_userdata->set('homepage', $typo3_user['www']);
        

        // Set the default timezoneoffset of vBulletin:
        $this->vBulletin_userdata->set('timezoneoffset', $vbulletin->options['timeoffset']);
        //debug("timezoneoffset=".$vbulletin->options['timeoffset']);
        
        
        // assign user to usergroup 3 if email needs verification
//         if ($vbulletin->options['verifyemail'])
//         {
//                 $newusergroupid = 3;
//         }
//         else if ($vbulletin->options['moderatenewmembers'] OR $vbulletin->GPC['coppauser'])
//         {
//                 $newusergroupid = 4;
//         }
//         else
//         {
//                 $newusergroupid = 2;
//         }
        
        // Default usergruppe if no usergroup is set for the TYPO3 user:
        $newusergroupid = 1;
        $newmembergroupids = array();
        // set usergroupid
        $this->vBulletin_userdata->set('usergroupid', $newusergroupid);
         
        //set the usergroups:
        if(isset($typo3_user['usergroup']) || isset($typo3_user['disable'])  ){
            if(isset($typo3_user['usergroup']) ){
                $current_typo3_usergroup = $typo3_user['usergroup'];
            }else{
                $current_typo3_usergroup = $typo3_user_from_db['usergroup'];
            }
            if(isset($typo3_user['disable']) ){
                $disable = $typo3_user['disable'];
            }else{
                $disable = $typo3_user_from_db['disable'];
            }
            $current_vb_groups = array_merge(array($vb_user['usergroupid']), explode(',', $vb_user['membergroupids']));
            $vb_groups = $this->get_vBulletin_usergroups($current_vb_groups, $current_typo3_usergroup,$disable); 
            // first element in the array is the main group:
            $this->vBulletin_userdata->set('usergroupid', array_shift($vb_groups));
            $this->vBulletin_userdata->set('membergroupids', implode($vb_groups, ','));
        }



        /* typo3 set the language cookie for vBulletin */
        // set languageid
        $this->vBulletin_userdata->set('languageid', $vbulletin->userinfo['languageid']);
                if(TYPO3_DLOG){
                    t3lib_div::devLog('languageid='.$vbulletin->userinfo['languageid']." in ".__LINE__.": ".__FUNCTION__.", ".__FILE__, $this->extKey, 2 );
                }

        // set user title
        $this->vBulletin_userdata->set_usertitle('', false, $vbulletin->usergroupcache["$newusergroupid"], false, false);

        // register IP address
        $this->vBulletin_userdata->set('ipaddress', IPADDRESS);

        // vBulletin offers a function to test, if the data are valid.
        $this->vBulletin_userdata->pre_save();
        
        // check for errors
        if (!empty($this->vBulletin_userdata->errors))
        {
                return $this->vBulletin_userdata;
        }
        $userid = $this->vBulletin_userdata->save();
        return $this->vBulletin_userdata;
    
    }    
    /** 
    this function must called after the database operation, and after "user_create"
    *
    * @param       array  fe_user, only the field "uid" is requiered. 
    * @return      userdatamanager of vBulletin. In case of an error the array $this->vBulletin_userdata->errors 
                contains the errormessages, otherwise this array is emty. Refer the vBulletin documentation
                for Data Managers and the vBulletin file: class_dm_user.php for details.
    */     
   function post_user_create($typo3_user){
        global $vbulletin;
        //debug($this->vBulletin_userdata->user); 
         
         /* vBulletin create a salt after create a user, therefore a second step is necessary
         to write this value in the fe_users. This is done here.
         */
         $GLOBALS['TYPO3_DB']->exec_UPDATEquery("fe_users", "uid='".$typo3_user['uid']."' AND deleted=0 ",
                 array(
                            //"password"=>$vb_user['password'],
                        "tx_hrvbulletinconnect_vbulletin_user_salt"=>$this->vBulletin_userdata->user['salt'],
                        "tx_hrvbulletinconnect_vbulletin_user_password"=>$this->vBulletin_userdata->user['password'],
                        "tx_hrvbulletinconnect_vbulletin_user_id"=>$this->vBulletin_userdata->user['userid'],
                    
                    )
             );
         
         return $this->vBulletin_userdata;
            
    
    }    
    
    
    /** 
    @todo
    not full tested, but works !!! 
         * try to delete the equivalent vBulletin user, but not the typo3 frontenduser.
         *
         * @param       int  the uid of the typo3 fe_user 
         * @return      userdatamanager of vBulletin. In case of an error the array $this->vBulletin_userdata->errors 
                        contains the errormessages, otherwise this array is emty. Refer the vBulletin documentation
                        for Data Managers and the vBulletin file: class_dm_user.php for details.
    
    */
    function user_delete($typo3_user_uid )
    {
        if(! $typo3_user_uid){
            $this->vBulletin_userdata->errors[] = "typo3 user uid is zero!";
            return $this->vBulletin_userdata;
         }
        $dbres = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', 'uid=\''.$typo3_user_uid.'\' AND deleted=0 ');
        if(!$dbres){
                if(TYPO3_DLOG){
                    t3lib_div::devLog('no valid database resource: uid='.$typo3_user_uid." in ".__LINE__.": ".__FUNCTION__.", ".__FILE__, $this->extKey, 2 );
                }
                $this->vBulletin_userdata->errors[] ="no valid database resource";
                return $this->vBulletin_userdata;
        }
        $typo3_user_from_db = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbres);
       if(!$typo3_user_from_db['tx_hrvbulletinconnect_vbulletin_user_id']){
            $this->vBulletin_userdata->errors[] = "typo3 user has no equivalent vBulletin user";
            return $this->vBulletin_userdata;
       }
        $vb_user = fetch_userinfo($typo3_user_from_db['tx_hrvbulletinconnect_vbulletin_user_id']);
        if(!is_array($vb_user) ){
            if(TYPO3_DLOG){
                t3lib_div::devLog('typo3 user has no equivalent vBulletin user: userid='.$typo3_user_from_db['tx_hrvbulletinconnect_vbulletin_user_id']." (".$typo3_user_from_db['username'].'), fe_user uid='.$typo3_user_from_db['uid']." in ".__LINE__.": ".__FUNCTION__.", ".__FILE__, $this->extKey, 2 );
            }
            $this->vBulletin_userdata->errors[] = "typo3 user has no equivalent vBulletin user";
            return $this->vBulletin_userdata;
        }
        $this->vBulletin_userdata->set_existing($vb_user);
        $this->vBulletin_userdata->delete();
        
        $GLOBALS['TYPO3_DB']->exec_UPDATEquery("fe_users", "uid='".$typo3_user_from_db['uid']."'",
                 array(
                            //"password"=>$vb_user['password'],
                        "tx_hrvbulletinconnect_vbulletin_user_salt"=>"",
                        "tx_hrvbulletinconnect_vbulletin_user_password"=>"",
                        "tx_hrvbulletinconnect_vbulletin_user_id"=>"0",
                    
                    )
             );

        
        return $this->vBulletin_userdata;
    }
    
    
    /**
    
    * write the userdate from fe_users to the vBulletin user.
    *
    * @param       array  with the new data of a user and the field "uid"! 
    * @return      userdatamanager of vBulletin. In case of an error the array $this->vBulletin_userdata->errors 
                contains the errormessages, otherwise this array is emty. Refer the vBulletin documentation
                for Data Managers and the vBulletin file: class_dm_user.php for details.
    */     
    function user_update($typo3_user){
        global $vbulletin; 
        
        $div = t3lib_div::makeinstance('user_hrvbulletinconnect_div');
        $div->convert_fe_user_charset($typo3_user,$div->get_typo3_charset(), $div->get_vbulletin_charset() );

        //$this->array_convert2vbulletin_charset($typo3_user);
        
        //$typo3_user_from_db = $GLOBALS['TSFE']->sys_page->getRawRecord("fe_users",$typo3_user['uid']);
        $dbres = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', 'uid=\''.$typo3_user['uid'].'\' AND deleted=0 ');
        if(!$dbres){
            if(TYPO3_DLOG){
                t3lib_div::devLog('no valid resource (typo3 user not found): '.$typo3_user['username'].', uid='.$typo3_user['uid']." in ".__LINE__.": ".__FUNCTION__.", ".__FILE__, $this->extKey, 2 );
            }
            $this->vBulletin_userdata->errors[] ='no valid resource (typo3 user not found): '.$typo3_user['username'].', uid='.$typo3_user['uid'];
            return  $this->vBulletin_userdata;;
        }
        $typo3_user_from_db = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbres);
        
        // a vBulletin user does not exists:
        if(!$typo3_user_from_db['tx_hrvbulletinconnect_vbulletin_user_id']){
            if(TYPO3_DLOG){
                t3lib_div::devLog('"tx_hrvbulletinconnect_vbulletin_user_id" is not set for typo3 user: '.$typo3_user['username'].', uid='.$typo3_user['uid']." in ".__LINE__.": ".__FUNCTION__.", ".__FILE__, $this->extKey, 2 );
            }
            $this->vBulletin_userdata->errors[] = '"tx_hrvbulletinconnect_vbulletin_user_id" is not set for typo3 user: '.$typo3_user['username'].', uid='.$typo3_user['uid'];
            return  $this->vBulletin_userdata;;
        }
        
        $vb_user = fetch_userinfo($typo3_user_from_db['tx_hrvbulletinconnect_vbulletin_user_id']);
        if(!$vb_user){
            if(TYPO3_DLOG){
                t3lib_div::devLog('cannot fetch vBulletinuser for typo3-user:: '.$typo3_user['username'].', uid='.$typo3_user['uid']." in ".__LINE__.": ".__FUNCTION__.", ".__FILE__, $this->extKey, 2 );
            }
            $this->vBulletin_userdata->errors[] = 'cannot fetch vBulletinuser for typo3-user:: '.$typo3_user['username'].', uid='.$typo3_user['uid'];
            return  $this->vBulletin_userdata;;
        }
        
        $this->vBulletin_userdata->set_existing($vb_user);
        if(isset($typo3_user['email']) ){
            $this->vBulletin_userdata->set('email',$typo3_user['email'] );
        }
        if(isset($typo3_user['www']) ){
            $this->vBulletin_userdata->set('homepage',$typo3_user['www'] );
        }
        if(isset($typo3_user['username']) ){
            $username = $typo3_user['username'];
            if(!$this->verify_username($typo3_user['username'])){
                 return $this->vBulletin_userdata;
            }
            $this->vBulletin_userdata->set('username',$typo3_user['username']);

        }
        
        
        
        
        // vBulletin accept passwords in plain text or md5 hash
        // vBulletin convert the password in md5(md5(<plaintextpassword>).salt)
        // The function post_user_update take the password from vBulletin database and write it in the
        // fe_users table.
        //debug("vb-password=".$this->vBulletin_userdata->user['password']);
        if(isset($typo3_user['password']) ){
            $this->vBulletin_userdata->set('password',$typo3_user['password'] );
        }
        
        //set the usergroups:
        if(isset($typo3_user['usergroup']) || isset($typo3_user['disable'])  ){
            if(isset($typo3_user['usergroup']) ){
                $current_typo3_usergroup = $typo3_user['usergroup'];
            }else{
                $current_typo3_usergroup = $typo3_user_from_db['usergroup'];
            }
            if(isset($typo3_user['disable']) ){
                $disable = $typo3_user['disable'];
            }else{
                $disable = $typo3_user_from_db['disable'];
            }
            $current_vb_groups = array_merge(array($vb_user['usergroupid']), explode(',', $vb_user['membergroupids']));
            $vb_groups = $this->get_vBulletin_usergroups($current_vb_groups, $current_typo3_usergroup,$disable); 
            // first element in the array is the main group:
            $usergroup = array_shift($vb_groups);
            $membergroups = implode($vb_groups, ',');
            $this->vBulletin_userdata->set('usergroupid', $usergroup);
            $this->vBulletin_userdata->set('membergroupids', $membergroups);
        }

        
        //debug($typo3_user);
        
         $this->vBulletin_userdata->save();
         return $this->vBulletin_userdata;
    }
    
    /**
    * write the userdate from fe_users to the vBulletin user.
    This function must called after the typo3 fe_users is updated, because this function
    takes the new data from the database.
    *
    * @param       array   Data of user. Only the uid of the typo3_usr is needed.
    * @return      userdatamanager of vBulletin. In case of an error the array $this->vBulletin_userdata->errors 
                contains the errormessages, otherwise this array is emty. Refer the vBulletin documentation
                for Data Managers and the vBulletin file: class_dm_user.php for details.
    */     
    function post_user_update($typo3_user){
        global $vbulletin; 
        //debug("vb-password=".$this->vBulletin_userdata->user['password']);
        
        /* if the password is changed, the vBulletin field "password" is not emty.
        write this passwordhash in the typo3 userfield:
        */
        if(isset($this->vBulletin_userdata->user['password'])){
            $GLOBALS['TYPO3_DB']->exec_UPDATEquery("fe_users", "uid='".$typo3_user['uid']."' AND deleted=0 ",
                    array(
                            "tx_hrvbulletinconnect_vbulletin_user_password"=>$this->vBulletin_userdata->user['password'],
                        )
                );
        }
        return   $this->vBulletin_userdata;   
    }

    /** @todo testen!! not ready implemented!!!!
    * returns the equivalent vBulletin usergroup from the map table of the configuration
    *
    * @param       int   typo3 usergroup uid
    * @return      allways a valid vBulletin usergroup
    */     
    function typo3_usergroup_to_vBulletin_usergroup($typo3_usergroup_uid){
          $vBulletin_group = 0;
          foreach($this->vbgrops_to_typo3groups as $vb_group => $typo3_group){
            if($typo3_usergroup_uid == $typo3_group){
                $vBulletin_group = $vb_group ;          
            }
          }
          /* return allways true */
          $this->vBulletin_userdata->verify_username($vBulletin_group);
          return $vBulletin_group;    
    }
         
    /** @todo testen!! not implemented yet
        @todo: mann soll eine defaultgruppe einstellen können , wenn keine passt
        ausserdem soll abgefragt werden, ob eine gruppe überhaupt angegeben ist.
        die Membergroupids müssen auch noch irgendwie gesetzt werden

    * returns the equivalent typo3 usergroup from the map table of the configuration
    *
    * @param       int   vBulletin usergroup uid
    * @return      the text of the configuration (an empty string is also possible)
    */     
    function vBulletin_usergroup_to_typo3_usergroup($vBulletin_usergroup_uid){
          
          return $this->vbgrops_to_typo3groups[$vBulletin_usergroup_uid];
    
    }


    /**

    Translate the typo3 usergroups to the vBulletin usergroups. If there 
    are existing usergroups, which are not in the translations table, 
    then these groups are append to the membergroup's. Each group id is unique in the array, so
    vBulletin have no troubles to take these groups.
    The sequence of the translatable groups are not changed. 
   
    *
    * @param       array   vBulletin usergroupid and membergroupid's. The first element is the
                           usergroupid
    * @param       string  field "usergroup", usergroupid's split by comma
    * @param       bool    field "disable", if this is true, then the first group (usergroupid) 
                           is overriden to the unregistered group "1"   
    * @return      array of the translated usergroups. The first element is the usergroupid, the rest are
                   the membergroupid's. 
    */     
    function get_vBulletin_usergroups($current_vb_usergroup, $typo3_usergroup, $typo3_disable){
       // unset all emty groups
       foreach($current_vb_usergroup as $key => $value){
            if(!intval($value)){
                unset($current_vb_usergroup[$key]);
            }
        }
        // get only the custom groups:
        $translateable_vbgroups = array_keys($this->vbgrops_to_typo3groups);
        //debug($current_vb_usergroup);
        // get the usergroups, which cannot be translated
        $vb_custom_groups = array_diff($current_vb_usergroup, $translateable_vbgroups);
        //debug($this->vbgrops_to_typo3groups);
        //debug($vb_custom_groups);
        $vb_groups = array();
        $arr_typo3_usergroup = t3lib_div::trimExplode(',',$typo3_usergroup,1);
        // translate the typo3 groups if it is possible:
        foreach($arr_typo3_usergroup as $value){
            $group = $this->get_vBulletin_usergroup($value);
            if($group){
                $vb_groups[] =  $group; 
            }
        }
        $vb_groups = array_merge($vb_groups,$vb_custom_groups); 
        // if $vb_groups is empty, so set groupid 1 (unregistered):
        if(empty($vb_groups)){
            return array(1);// unregistered group
        }
        // override first group as unregistrered group if disabled, and delete all membergroup ids:
        if($typo3_disable){
            //debug("disable");
            $vb_groups = array();
            $vb_groups[] = intval($this->conf_default['vb_group_for_disable']);
        }
        //debug($vb_groups);
        // remove duplicate entries and re-indexing with key starting at 0:
        $temp=array_unique($vb_groups);
        $vb_groups=array_values($temp);
        //debug($vb_groups);
        return $vb_groups;
    }
    
    
    /** @todo testing
    * Returns the equivalent typo3 usergroup
    * @param   int   The id of the vBulletin usergroup
    * @return   The id of the typo3 usergroup if success, otherwise false, if there is no
                equivalent typo3 usergroup in the translations table "vbgrops_to_typo3groups"
    */     
    function get_typo3_usergroup($vb_usergroup){
        foreach($this->vbgrops_to_typo3groups as $vbgroup => $typo3group){
            //debug("$vbgroup => $typo3group : ".$typo3_usergroups[0]);
            if($vbgroup == $vb_usergroup){
                return $typo3group;
            }
        }
        return false; // 
    }
    
    /** 
    * Returns the equivalent vBulletin usergroup
    * @param   int   The id of the typo3 usergroup
    * @return   The id of the vBulletin usergroup id success, otherwise false, if there is no
                equivalent vBulletin usergroup in the translations table "vbgrops_to_typo3groups"
    */     
    function get_vBulletin_usergroup($typo3_usergroup){
        foreach($this->vbgrops_to_typo3groups as $vbgroup => $typo3group){
            //debug("$vbgroup => $typo3group : ".$typo3_usergroups[0]);
            if($typo3group == $typo3_usergroup){
                return $vbgroup;
            }
        }
        return false; // 
    }
    
    /** 
    * 
    * @param   int   The uid of the typo3 user
    * @return   array of the fe_users record, if no user is found than false
    */     
    function get_typo3_user($uid){
        $dbres = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', 'uid=\''.$uid.'\' AND deleted=0 ');
        if(!$dbres){
            if(TYPO3_DLOG){
                 t3lib_div::devLog('no valid database resource (typo3 user not found): '.$uid." in ".__LINE__.": ".__FUNCTION__.", ".__FILE__, $this->extKey, 2 );
            }
            return false;       
        }
        $typo3_user_from_db = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbres);
        if(!is_array($typo3_user_from_db)){
             if(TYPO3_DLOG){
               t3lib_div::devLog('no typo3 user found: '.$uid." in ".__LINE__.": ".__FUNCTION__.",".__FILE__, $this->extKey, 2 );
            }
            return false;
        }
        return $typo3_user_from_db;
    }
    
    /** vBulletin change some special characters, but this is not acceptable in conjunction with typo3.
    Don't use the function $this->vBulletin_userdata->verify_username from the vBulletin vB_DataManager_User.
    \todo  only ascii characters are allowed!! and no space
    * 
    * @param   strint  username
    * @return   bool   true if success, otherwise false
    */     

    function verify_username($username){
        $errormsg = "username: \"$username\"  contains bad characters";
        $div = t3lib_div::makeinstance('user_hrvbulletinconnect_div');

        if(! $div->test_allowed_char($username)){
            $this->vBulletin_userdata->errors[] = $errormsg;
            return false;
        }         
        $vBulletin_username = $username;
        if(!$this->vBulletin_userdata->verify_username($vBulletin_username)){
            return false;
        }
        // vBulletin change some special characters, but this is not acceptable in conjunction with typo3
        if(strcmp($username, $vBulletin_username)){
            $this->vBulletin_userdata->errors[] = $errormsg;
            return false;
        }
        return true;
    }
    
    
    
//     function array_convert2vbulletin_charset(&$array){
//         global $vbulletin, $stylevar ; 
//         //error_log("vbulletn charset=".$vbulletin->config['Mysqli']['charset']);
//         //print("vBulletin_charset=". $stylevar['charset']); 
//         $typo3_charset = 'iso-8859-1';
//         $vbulletin_charset =  $stylevar['charset'];
//         
//         if (TYPO3_MODE=="BE") { 
//             // \see: /typo3/template.php :  function initCharset()
//             $typo3_charset = $GLOBALS['LANG']->charSet ? $GLOBALS['LANG']->charSet : 'iso-8859-1';
//         
//         }else{
//             $typo3_charset = $GLOBALS['TSFE']->renderCharset;
//         }
// 
//         $csconvobj = t3lib_div::makeinstance('t3lib_cs');
//         $typo3_charset = $csconvobj->parse_charset($typo3_charset);
//         $vbulletin_charset = $csconvobj->parse_charset($vbulletin_charset);
//         if(is_array($array)){
//             foreach($array as $key => $value){
//                 switch($key){
//                 case 'email':
//                 case 'www':
//                 case 'username':
//                     $converted =  $csconvobj->conv($value,$typo3_charset,$vbulletin_charset);
//                     if($converted == false){
//                         error_log("convertierung hat nicht funktioniert");
//                     }else{
//                         $array[$key] = $converted;
//                     }
//                     error_log( "$key= ($value)=".$converted);
//                     break;
//                 }
//             
//             }
//         }
//        
//     }
//     /**
//     * @param   string   
//     * @return   bool   false, if not allowed characters are found. This is mainly to test the username
//     
//     */
//     function test_allowed_char($str){
//         $strLen = strlen($str);
//                 //echo "username = ".$vb_user['username'];
//         for ($a=0;$a<$strLen;$a++)      {       // Traverse each char in string.
//             $chr=substr($str,$a,1);
//             $ord=ord($chr);
//             if ($ord>127)      { 
//                 return false;     
//             }
//             if($ord <= 32){
//                 return false;
//             }
//         }
//         return true;
//     }

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_vbulletin_connect/class.user_feuser_vbulletinuser_sync.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_vbulletin_connect/class.user_feuser_vbulletinuser_sync.php']);
}

?>