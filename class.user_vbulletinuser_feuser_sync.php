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
 * @author      Herbert Roider <herbert.roider@utanet.at>
 */


/**
This class perform the updates of the fe_users table when a vBulletin user is changed by the vBulletin.
A vBulletin plugin call the typo3 site (typenum = 450) with some get-parameters and all the session cookies.
Therefore "curl" is necessary.

*/
if(isset($_GET['type']) && $_GET['type'] == 450){
    require_once(t3lib_extMgm::extPath('hr_vbulletin_connect')."user_vBulletin_global.php");
    require_once(t3lib_extMgm::extPath('hr_vbulletin_connect').'class.user_hrvbulletinconnect_div.php');

}

//require_once(t3lib_extMgm::extPath('hr_vbulletin_connect').'user_vBulletin_global.php');


class user_vbulletinuser_feuser_sync {
    
    var $cObj;
    var $conf;
    var $conf_default; // contains the configuration array generated from the localconf.php settings of this extention
    var $extKey = 'hr_vbulletin_connect';
 
 
 
    /**
    Es ist eine Page typeNum 450 konfiguriert. Bei der wird nur diese Funktion aufgefufen.
    Es wird versucht mit den Sessionhash von vBulletin (Cookie) den User aus der Datenbank auszulesen
    der dann der Funktion update_typo3user übergeben wird, die dann den fe_user von typo3 updatet.
    */
    function init($content, $conf){
        $this->conf = $conf;
        /** @todo should be the same encoding as in vBulletin
        */
        $content = '<?xml version="1.0" encoding="utf-8"  standalone="yes" ?>
        <errors>
        ';
//echo "hallo";
        //return "ein Test";
//echo "cookie_alt=".COOKIE_SALT."<br />";
        $errors = $this->process_vbulletin_call();
        $content.=$errors;
        $content.='</errors>';
        //echo $content;
        return $content;
    
    }
    function process_vbulletin_call(){
      global $vbulletin;
      
      if(strcmp($_SERVER['REMOTE_ADDR'],$_SERVER['SERVER_ADDR'])){
            if(TYPO3_DLOG){
                t3lib_div::devLog('the server ip and the client ip is not the same: REMOTE_ADDR='.$_SERVER['REMOTE_ADDR'].', SERVER_ADDR='.$_SERVER['SERVER_ADDR'].' in: '.__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2);
            }
            return $this->create_error_element("auth_failed");
      }
       
      /* fetch the user logged in (vbulletin), who want to change userdata.
      All vbulletin cookies are set, and so hope that vbulletin eat this.
      It seems necessary that the useragent is also set.
      @see file: update_typo3_user_hook.php
      */
      //$admin_vbuser = $vbulletin->session->fetch_userinfo(); 
//              foreach($admin_vbuser as $key => $value){ 
//                  $msg.="$key = $value\n"; 
//              } 
//              t3lib_div::devLog('user = '.$msg.__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2); 
       
        $div = t3lib_div::makeinstance('user_hrvbulletinconnect_div');
       
       $admin_vbuser = $this->fetch_session_user();
       if(false == $admin_vbuser){
            if(TYPO3_DLOG){
                t3lib_div::devLog('cannot fetch vbulletin user from session '.__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2);
            }
            return $this->create_error_element("auth_failed");
       }
       //print_r($admin_vbuser);
       
       
       
       $command = t3lib_div::_GP("cmd"); 
       $sessionhash = t3lib_div::_GP('sessionhash');
       $safecode = t3lib_div::_GP("safecode");
       $vBulletin_userid = intval(t3lib_div::_GP("vbuserid"));
       $GP_vbuser = t3lib_div::_GP("vbulletin_user");

       if(!$vBulletin_userid){
            if(TYPO3_DLOG){
                t3lib_div::devLog('no vbuserid in '.__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2);
            }
            return $this->create_error_element("no_vbulletin_userid_in_parameters");
       }
        
        // Testen, ob die Daten nicht verändert wurden:
        $postdata = "";
        if(is_array($GP_vbuser)){
            foreach($GP_vbuser as $key => $value){
                    if(!is_array($value)){
                        $postdata .= urlencode("vbulletin_user[".$key."]")."=".urlencode($value)."&";
                    }
            }
        }
        $postdata.='cmd='.urlencode($command).'&';
        $postdata.='vbuserid='.urlencode($vBulletin_userid).'&';
        $postdata.='sessionhash='.$sessionhash.'&';
        $safecode_1 = md5($postdata.COOKIE_SALT.$admin_vbuser['userid']);
        //print("<!-- \n postdata TYPO3: ".$postdata.COOKIE_SALT.$admin_vbuser['userid']);
        //print("\n safecode TYPO3:".$safecode_1);
        //print("\n -->");
        if(strcmp($safecode, $safecode_1)){
            if(TYPO3_DLOG){
                t3lib_div::devLog('checksum dont match: safecode_1 = '.$safecode_1.', '.$safecode.'  in '.__LINE__.": ".__FUNCTION__." file: ".__FILE__." postdata=".$postdata, $this->extKey, 2);
                //t3lib_div::devLog($postdata.$admin_vbuser['salt'].COOKIE_SALT.$admin_vbuser['userid'].'  in '.__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2);
                
                return $this->create_error_element("auth_failed");
            }
        }
        
        // ist nicht gesetzt:
        $GP_vbuser['userid'] = $vBulletin_userid;
      
      
        if($command == 'update'){     
            if(TYPO3_DLOG){
                 t3lib_div::devLog('try to updata user in '.__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2);
            }
            if($admin_vbuser['userid'] < 1){
                t3lib_div::devLog('cannot update user ('.$vBulletin_userid.') because admin userid < 0 in '.__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2);
                 return $this->create_error_element("cannot_update_typo3_user");
                
            }
                    
            $vb_user = fetch_userinfo($vBulletin_userid);
            if(!$vb_user){
                if(TYPO3_DLOG){
                    t3lib_div::devLog('want update the typo3 user, but no equivalent vBulletin user found in '.__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2);
                }
                return $this->create_error_element("want_update_the_typo3_user_but_no_equivalent_vbulletin_user_found");
            }
            
            // This field is not in the database, so get it from the POST data:
            $vb_user['md5_password'] = $GP_vbuser['md5_password'];
            
            if(! $this->update_typo3user($vb_user)){
                if(TYPO3_DLOG){
                    t3lib_div::devLog('cannot update typo3 user in '.__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2);
                }
                return $this->create_error_element("cannot_update_typo3_user");
  
            }
        }else if($command == 'delete'){
            if($admin['userid'] < 1){
                t3lib_div::devLog('cannot delete user ('.$vBulletin_userid.') because admin userid < 0 in '.__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2);
                 return $this->create_error_element("cannot_delete_typo3_user");
                
            }
            //t3lib_div::devLog('delete user in '.__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2);
            if(!$this->delete_typo3user($vBulletin_userid)){
                t3lib_div::devLog('cannot delete user ('.$vBulletin_userid.') in '.__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2);
                 return $this->create_error_element("cannot_delete_typo3_user");
            
            }
        }else if($command == 'verify'){
           //return "";
           if(TYPO3_DLOG){
                  t3lib_div::devLog('try to verify user in '.__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2);
            }
            //$GP_vbuser = t3lib_div::_GP("vbulletin_user");
  //echo "typo3 user=";
  //print_r($typo3_user);

            $ret = $this->verify_typo3user($GP_vbuser, $error);
            if($ret== false){
                if(TYPO3_DLOG){
                    t3lib_div::devLog('cannot verify user in '.__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2);
                }

                return $this->create_error_element("vbulletin_user_is_not_valid_for_typo3");
            }
        }else if($command == 'reset_password'){
            // this test is not necessary, because the automatic created password contains only numbers:
            if( ! $div->test_allowed_char($vb_user['password']))
            {
                $error = 'not_allowed_character_found_in_password';            
                if(TYPO3_DLOG){
                    t3lib_div::devLog($error." in ".__LINE__.": ".__FUNCTION__.", ".__FILE__, $this->extKey, 2 );
                }
                return false;
            }
            error_log(__LINE__.", ".__FILE__." reset password");

             // This field is not in the database, so get it from the POST data:
            $vb_user = array();
            $vb_user['md5_password'] = $GP_vbuser['md5_password'];
            $vb_user['userid'] = $vBulletin_userid;
            if(! $this->update_typo3user($vb_user)){
                if(TYPO3_DLOG){
                    t3lib_div::devLog('cannot update typo3 user in '.__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2);
                }
                return $this->create_error_element("cannot_update_typo3_user");
  
            }
        }else{
            t3lib_div::devLog('command is not valid: "'.$command.'"  in '.__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2);
            return $this->create_error_element("command_is_not_valid");
        }
        return "";
        
    
    
    }
    function create_error_element($content){
        return "<error>$content</error>\n";
    }
    /** Schreibt die Daten von der vBulletin usertabelle in den typo3-user datensatz.
    
    @todo: usergruppen
    */
    function update_typo3user($vb_user)
    {
         
         //echo "update<br />";
         //print_r($vb_user);
         $dbres = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users',
                        "tx_hrvbulletinconnect_vbulletin_user_id = '".$vb_user['userid']."' AND deleted=0 ");
         //echo "1";
         if (!$dbres)     {
            //echo "kein equivalent fe_user gefunden".__LINE__.", ".__FILE__."\n";
            if(TYPO3_DLOG){
                t3lib_div::devLog('want update the typo3 user, but no typo3 user found, vb_user.userid='.$vb_user['userid']." in ".__LINE__.": ".__FUNCTION__.", ".__FILE__, $this->extKey, 2 );
            }
            return false;
         }
         //echo "md5_password=".$vb_user['md5_password'];

        $typo3_user_from_db = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbres);
        if(! $typo3_user_from_db){
            return false;
        }
        $typo3_user = array();
        $typo3_user['uid'] = $typo3_user_from_db['uid'];
        if(strlen($vb_user['homepage'])){
            $typo3_user['www'] = $vb_user['homepage'];
        }
        if(strlen($vb_user['email'])){
            $typo3_user['email'] = $vb_user['email'];
        }
        if(strlen($vb_user['username'])){
            $typo3_user['username'] = $vb_user['username'];
        }
        if(strlen($vb_user['md5_password'])){
            error_log(__LINE__.",".__FILE__." vb_user password=".$vb_user['md5_password']);

            $typo3_user['password'] = $vb_user['md5_password'];
        }
        // There is no need to set this value, and the vbulletin plugin don't send this value to prevent the read.
        if(strlen($vb_user['salt'])){
            $typo3_user['tx_hrvbulletinconnect_vbulletin_user_salt'] = $vb_user['salt'];
        }
        // There is no need to set this value
        if(strlen($vb_user['password'])){
            $typo3_user['tx_hrvbulletinconnect_vbulletin_user_password'] = $vb_user['password'];
        }
        
        $div = t3lib_div::makeinstance('user_hrvbulletinconnect_div');
        $div->convert_fe_user_charset($typo3_user, $div->get_vbulletin_charset() ,$div->get_typo3_charset() );

        //$this->array_convert2typo3_charset($typo3_user);
       
        //print_r($typo3_user);
        
        
        $GLOBALS['TYPO3_DB']->exec_UPDATEquery("fe_users", "uid='".$typo3_user['uid']."'",
            $typo3_user
         );
         return true; 
    }
    /** delete the equivalent typo3 user
    *
    * @param       int     userid of the vBulletin user
    * @return              true or false
    */     
    function delete_typo3user($vb_user_id){
         
         //echo "update";
         //print_r($vb_user);
         
         /* $vb_user_id must be greater then 0 otherwise all
          typo3 user where the tx_hrvbulletinconnect_vbulletin_user_id is not set will be deleted
         */
         if(! $vb_user_id){
            if(TYPO3_DLOG){
                t3lib_div::devLog('cannot delete typo3 user, because the userid is not valid, vb_user.userid='.$vb_user_id." in ".__LINE__.": ".__FUNCTION__.", ".__FILE__, $this->extKey, 2 );
            }
            return false;
         }
         $dbres = $GLOBALS['TYPO3_DB']->exec_DELETEquery('fe_users',
                        "tx_hrvbulletinconnect_vbulletin_user_id = '".$vb_user_id."' AND deleted=0 ");
         //echo "1";
         if (!$dbres)     {
            //echo "kein equivalent fe_user gefunden".__LINE__.", ".__FILE__."\n";
            if(TYPO3_DLOG){
                t3lib_div::devLog('cannot delete typo3 user, vb_user.userid='.$vb_user_id." in ".__LINE__.": ".__FUNCTION__.", ".__FILE__, $this->extKey, 2 );
            }
            return false;
         }
         return true;
       
    }
    /** Test if a vBulletin user is valid for TYPO3
    *
    * @param       array  array with the fields for the vBulletin user
    * @param       string  errormessage
    * @return              true or false
    */     
    function verify_typo3user($vb_user, &$error){
        //$GLOBALS['TYPO3_DB']->debugOutput = TRUE;               // Set "TRUE" if you want database errors outputted.
        //$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = TRUE;      // Set "TRUE" if you want the last built query to be 
        $div = t3lib_div::makeinstance('user_hrvbulletinconnect_div');

         
        
         
         if(! is_array($vb_user)){
            $error = 'vbuser_is_not_an_array';
            if(TYPO3_DLOG){
                t3lib_div::devLog($errorin." in:".__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2);
            }  
            return false;
         }
         if(!$vb_user['userid']){
            $error = 'vbuser_userid_is_not_set';
            if(TYPO3_DLOG){
                t3lib_div::devLog($error." in:".__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2);
            }  
            return false;
         }
                  
         //$common_fields = array("homepage", "username", "email");
         foreach($vb_user as $theField => $value){
                switch($theField){
                case 'username':
                    // only ascii is allowed and no spaces:
                    if( ! $div->test_allowed_char($vb_user['username']))
                    {
                        $error = 'not_allowed_character_found_in_username';            
                        if(TYPO3_DLOG){
                            t3lib_div::devLog($error." in ".__LINE__.": ".__FUNCTION__.", ".__FILE__, $this->extKey, 2 );
                        }
                        return false;
                    }
                    
                    // Think there is no need to test if uppercase:
//                     if (ereg ("[A-Z]+", $vb_user['username'])) {
//                         $error = 'want to verify a typo3 user, upper case character found, vb_user.userid='.$vb_user['userid']." in ".__LINE__.": ".__FUNCTION__.", ".__FILE__;            
//                         if(TYPO3_DLOG){
//                             t3lib_div::devLog($error." in ".__LINE__.": ".__FUNCTION__.", ".__FILE__, $this->extKey, 2 );
//                         }
//                         return false;
//                     
//                     }
                    
                    /* Test if username is unique.
                    no case sensitive check: if a user "herbert" exists already, a new username "Herbert" is not allowed.
                    */
                    $dbres = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users',
                    "username = '".$vb_user['username']."' AND deleted=0 ");
                    //print("not exists=".$GLOBALS['TYPO3_DB']->debug_lastBuiltQuery );

 
                    if (!$dbres)     {
                        $error = 'database_error_at_verify_typo3_user';            
                        if(TYPO3_DLOG){
                            t3lib_div::devLog($error." in ".__LINE__.": ".__FUNCTION__.", ".__FILE__, $this->extKey, 2 );
                        }
                        return false;
                    }
                    while($typo3_user_not_unique = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbres)){
                        //print_r($typo3_user_not_unique);
                        if($typo3_user_not_unique['tx_hrvbulletinconnect_vbulletin_user_id'] != $vb_user['userid']){
                            $error = 'username_already_exists_in_typo3';
                            if(TYPO3_DLOG){
                                t3lib_div::devLog($error." in ".__LINE__.": ".__FUNCTION__.", ".__FILE__, $this->extKey, 2 );
                            }
                            return false;
                        }
                     }
                    
                    break;
                }
         }

        //t3lib_div::devLog('verify user is ok in '.__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2);
        $error = "";
        return true;
    
    
    }
    
    function create_typo3user($vb_user, &$error, $store_pid){
        //echo "create_typo3_user";
        if(! $this->verify_typo3user($vb_user, $error)){
            return false;
        }
                 
         /* look if a typo3 frontenduser already exists
         
         */
         $dbres = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users',
                        "tx_hrvbulletinconnect_vbulletin_user_id = '".$vb_user['userid']."' AND deleted=0 ");
         if (!$dbres)     {
            $error = 'cannot_verify_the_typo3_user_because_database_error';            
            if(TYPO3_DLOG){
                t3lib_div::devLog($error." in ".__LINE__.": ".__FUNCTION__.", ".__FILE__, $this->extKey, 2 );
            }
            return false;
         }
         if($typo3_user = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbres)){
            $error = 'cannot_import_vBulletin_user_because_a_equivalent_typo3_user_already_exists';            
            if(TYPO3_DLOG){
                t3lib_div::devLog($error." in ".__LINE__.": ".__FUNCTION__.", ".__FILE__, $this->extKey, 2 );
            }
            return false;
         }
    

         
        $typo3user['username'] = $vb_user['username'];
        $typo3user['tx_hrvbulletinconnect_vbulletin_user_salt'] = $vb_user['salt'];
        $typo3user['tx_hrvbulletinconnect_vbulletin_user_password'] = $vb_user['password'];
        $typo3user['tx_hrvbulletinconnect_vbulletin_user_id'] = $vb_user['userid'];
        $typo3user['www'] = $vb_user['homepage'];
        $typo3user['email'] = $vb_user['email'];
        $typo3user['usergroup'] = 1;
        $typo3user['pid'] = $store_pid;
        
        $div = t3lib_div::makeinstance('user_hrvbulletinconnect_div');
        $div->convert_fe_user_charset($typo3user, $div->get_vbulletin_charset(), $div->get_typo3_charset());
        //$this->array_convert2typo3_charset($typo3user);
       
        
        //$GLOBALS['TYPO3_DB']->debugOutput = TRUE;               // Set "TRUE" if you want database errors outputted.
        //$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = TRUE;      // Set "TRUE" if you want the last built query to be 
        $res = $GLOBALS['TYPO3_DB']->exec_INSERTquery("fe_users", $typo3user);
        //print("debug=".$GLOBALS['TYPO3_DB']->debug_lastBuiltQuery );

        if(!$res){
            $error = "import_a_vBulletin_user_fails";
            return false;
        }
        $error = "";
        return true;
    }
    /** 
    Try to fetch the user who is logged in at vBulletin from the cookie: sessionhash.
    The vbulletin function: $vbulletin->session->fetch_userinfo() work only if the ip of the session is not compared.
    Therefore this function look for the user with the sessioncookie.
    * @return            success:  vbulletin user array, if no user is logged in the userid is set to 0 , otherwise: false
    
    */
    function fetch_session_user(){
        global $vbulletin;
        $cookies = "";
        $admin = array();
        
        foreach($_COOKIE as $key => $value){
           $cookies.= " $key => $value\n";
       }
        //bbsessionhash
        $sessionhash = $_COOKIE[$vbulletin->config['Misc']['cookieprefix'].'sessionhash'];
        if(0 == strlen($sessionhash)){
             $sessionhash = $_COOKIE[$vbulletin->config['Misc']['cookieprefix'].'_sessionhash'];   
        }
        
        //$sessionhash = $vbulletin->session->vars['sessionhash'];
        //$sessionhash=$_GET['sessionhash'];
        //error_log(__LINE__.", ".__FILE__." cookies=".$cookies);

        // keine Session, so set the userid to zero:
        if(0 == strlen($sessionhash)){
            $admin['userid'] = 0;
            $admin['salt'] = "";
            return $admin;
        }


        $dbres = mysql_connect( $vbulletin->config['MasterServer']['servername'].":".$vbulletin->config['MasterServer']['port'], $vbulletin->config['MasterServer']['username'], $vbulletin->config['MasterServer']['password']);
        if (!$dbres) {
             if(TYPO3_DLOG){
                t3lib_div::devLog('cannot connect to mysql: ' . mysql_error().', in '.__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2 );
            }
            return false;
        }
        if (!mysql_select_db($vbulletin->config['Database']['dbname'])) {
             if(TYPO3_DLOG){
                t3lib_div::devLog('cannot select db (mysql): ' . mysql_error().', in '.__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2 );
            }
            return false;
        }
 
        
        $sql = "SELECT * "
            ." FROM ".$vbulletin->config['Database']['tableprefix']."user "
            ." LEFT JOIN ".$vbulletin->config['Database']['tableprefix']."session "
            ." ON ".$vbulletin->config['Database']['tableprefix']."user.userid = "
            .$vbulletin->config['Database']['tableprefix']."session.userid "
            ." WHERE ".$vbulletin->config['Database']['tableprefix']."session.sessionhash = '$sessionhash'";  
        
//         $sql = "SELECT * "
//             ." FROM ".$vbulletin->config['Database']['tableprefix']."session "
//             ." WHERE ".$vbulletin->config['Database']['tableprefix']."session.sessionhash = '$sessionhash'";          
        
        
        $result = mysql_query($sql);
        if(!$result) {
            if(TYPO3_DLOG){
                t3lib_div::devLog('query fails: mysql: ' . mysql_error().', in '.__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2 );
            }
            return false;
         }
        
        if(! ($admin = mysql_fetch_assoc($result))) {
            if(TYPO3_DLOG){
                t3lib_div::devLog('cannot fetch result (sessionhash = '.$sessionhash.'): mysql: ' . mysql_error().' therefore admin[userid] is set to 0, in '.__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2 );
            }
            $admin['userid'] = 0;
            $admin['salt'] = "";
            return $admin;
        }
        return $admin;
 
    
    }


}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_vbulletin_connect/class.user_vbulletinuser_feuser_sync.php'])       {
        include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_vbulletin_connect/class.user_vbulletinuser_feuser_sync.php']);
}

?>