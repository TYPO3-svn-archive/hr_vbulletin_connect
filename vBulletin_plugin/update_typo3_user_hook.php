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
////////////////////////////////////////////////////////////
//
//  config:
//
// Set here the base url to the typo3 site, without any filename such as index.php, but with a trailing slash:
$GLOBALS['hr_vbulletin_connect_config'] = array(
     //'typo3_url' => "http://eagle.intervis.org/copadata_2/",
     'typo3_url' => "http://dev.tvet-portal.net/",
      // Set here the pid of the register page:
     'typo3_register_pid' => 26,
     // Set here the pid of any page in your page tree, this page is called from vBulletin
     // with  url parameter like: index.php?id=20&type=450
     // this page must not be a shortcut.
     'typo3_vbulletin_sync_pid' => 20,
); 
//
// end config
//
/////////////////////////////////

function call_typo3_site($command, $vBulletin_userid, $vbulletin_user, &$errors){
   global $vbulletin;
   $ok = false;
   //If typo3 changed data from vBulletin this function must not take effect.
   if (defined ('TYPO3_MODE'))
          return true;
    if(!$vBulletin_userid){
        //error_log(__LINE__.", ".__FILE__." keine userid");
        //return false;
    }
   
   // get the user who change the values:
   $admin = $vbulletin->session->fetch_userinfo();
   //print_r($admin);
   if(! $admin['userid']){
       //error_log(__LINE__.", ".__FILE__." keine admin userid");
       //return false;
   }
   
   $typo3_url = $GLOBALS['hr_vbulletin_connect_config']['typo3_url']."index.php";
     
   $typo3_url .= "?type=450&no_cache=1";
   if(isset($GLOBALS['hr_vbulletin_connect_config']['typo3_vbulletin_sync_pid'])){
           $typo3_url .= '&id='.$GLOBALS['hr_vbulletin_connect_config']['typo3_vbulletin_sync_pid'];
   }
   
   
   //echo $typo3_url;
// erzeuge einen neuen cURL-Handle
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $typo3_url);
   //curl_setopt($ch, CURLOPT_HEADER, 0);
   $cookies = "";
   //echo $typo3_url."<br>";
   foreach($_COOKIE as $key => $value){
            $cookies.="$key=$value; ";
   }
   
   //echo "md5_password=".$md5_password;
   unset($vbulletin_user['salt']);// This is only set by creating a new user, there is no need to send this value.

   
    
   if(is_array($vbulletin_user)){ 
        foreach($vbulletin_user as $key => $value){
                if(!is_array($value)){
                    $postdata .= urlencode("vbulletin_user[".$key."]")."=".urlencode($value)."&";
                }
        }
    }
    $sessionhash = $_COOKIE[$vbulletin->config['Misc']['cookieprefix'].'sessionhash'];
    if(0 == strlen($sessionhash)){
         $sessionhash = $_COOKIE[$vbulletin->config['Misc']['cookieprefix'].'_sessionhash'];   
    }
    
    $postdata.='cmd='.urlencode($command).'&';
    $postdata.='vbuserid='.urlencode($vBulletin_userid).'&';
    $postdata.='sessionhash='.urlencode( $sessionhash).'&';
//print("prefix=".$vbulletin->config['Misc']['cookieprefix']);
//error_log(__LINE__.", ".__FILE__." postdata= $postdata");
    $safecode = md5($postdata.COOKIE_SALT.$admin['userid']);
    if($command === 'update'){
        //print("\n postdata vbulletin: ".$postdata.COOKIE_SALT.$admin['userid']);    
        //print("\n safecode vb: ".$safecode);
        //print("\n");
    }
    $postdata.='safecode='.urlencode($safecode);
    
    

   //echo $postdata;
   curl_setopt($ch, CURLOPT_COOKIE, $cookies);  
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($ch, CURLOPT_POST, 1); // set POST method 
   curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata); 
   curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
   //curl_setopt($ch, CURLOPT_HEADER, 1);
   
   //if(DOMXML_LOAD_PARSING){
   //     echo "DOMXML_LOAD_PARSING=".DOMXML_LOAD_PARSING;
   //}
   
  //echo "phpversion=".(int) PHP_VERSION."<br />";

   
    // führe die Aktion aus und gebe die Daten an den Browser weiter
    $xmlResponse = curl_exec ($ch);
    // schließe den cURL-Handle und gebe die Systemresourcen frei
    curl_close($ch);
    
    //error_log(__LINE__.", ".__FILE__." xmlResponse = ".$xmlResponse);
    //error_log("errorarray count=".count($errors));
    //echo "xmlResponse=".$xmlResponse;
    
    // php 4.x
    if ((int) PHP_VERSION === 4){
        //echo $xmlResponse;
        if (!$dom = @domxml_open_mem($xmlResponse)) {
            echo "no valid xml: $xmlResponse";
            $ok = false;
        }
        //echo $xmlResponse;
        $errortags = $dom->get_elements_by_tagname("error");
        //$error_arr = array();
        foreach($errortags as $node){
            $errors[] = $node->get_content();
            $ok = false;
        }
    }else{ // php 5.x
        $dom = new DOMDocument();
        //echo "xml: $xmlResponse";
        if (!$dom->loadXml($xmlResponse)) {
            echo "no valid xml: $xmlResponse";
            $ok = false;
        }
        //echo $xmlResponse;
        $errortags = $dom->getElementsByTagName("error");
        //$error_arr = array();
        foreach($errortags as $node){
            $errors[] = $node->nodeValue;
            $ok = false;
        }    
    }
    
//     $errorlog = " Fehlermeldungen:\n";
//     foreach($errors as $value){
//         $errorlog .= $value."\n";
//     }
//     $errorlog.="Ende\n";
//     error_log(__LINE__.", ".__FILE__.$errorlog);
    if(0 == count($errors)){
        //error_log(__LINE__.", ".__FILE__." keine Fehler");
        $ok = true;
    }
 
    
   return $ok;         

}

/** Diese Funktion wird aus vBulletin aufgerufen von den gleichnamigen hook,
wenn sich userdaten verändert haben.
Es wird mit curl die Typo3-Seite (type=450, Siehe templatecode) aufgerufen. Wichtig ist, daß die Cookies von vBulletin auch mitgeschickt werden, zur identifikation vom User.
*/
function userdata_postsave($vBulletin_userid)
{
    global $vbulletin;
    // get the user who change the values:
    $admin = $vbulletin->session->fetch_userinfo();
    //sometimes vbulletin changes userdata which are not relevant for typo3.
    // The problem is, that often no user is logged in f.E. when a user ask for a new password.
    // In such a case the errormessages from typo3 should not be shown, so return true (no error):
    if( $admin['userid'] < 1){
       //error_log(__LINE__.", ".__FILE__." cannot update user because no admin user is logged in");

       return true;;
    }
    //print_r($vbulletin->GPC);

    $vbulletin_user = array();
    /** Try to get the md5 password if not set:
    */
    if(! isset($vbulletin_user['md5_password'])){
    
        //print_r($vbulletin->GPC);
        $md5_password = $vbulletin->GPC['newpassword_md5'] ? $vbulletin->GPC['newpassword_md5'] : $vbulletin->GPC['newpassword'];
        if(!$md5_password){
            $clear_password = $_POST['password'];
            if($clear_password){
                $md5_password = md5($clear_password);
            }
        }
        // neu Registrieren vom Frontend:
        if(!$md5_password){
            $md5_password = $_POST['password_md5'];
        
        }
        $vbulletin_user['md5_password'] = $md5_password;
        //error_log(__LINE__.", ".__FILE__." md5_password = $md5_password");
        //print("md5password=".$md5_password);


   }    
    
    
    $error = array();
    $ret = call_typo3_site("update", $vBulletin_userid, $vbulletin_user, $error);
    return $ret;

    
}


function userdata_delete($vBulletin_userid){
    // get the user who change the values:
    $admin = $vbulletin->session->fetch_userinfo();
    //sometimes vbulletin changes userdata which are not relevant for typo3.
    // The problem is, that often no user is logged in f.E. when a user ask for a new password.
    // In such a case the errormessages from typo3 should not be shown, so return true (no error):
    if( $admin['userid'] < 1){
       //error_log(__LINE__.", ".__FILE__." cannot delete user because no admin user is logged in");
       return true;;
    }
    $error = array();
    return call_typo3_site("delete", $vBulletin_userid, array(), $error);
}
    
/** Test if a vBulletin user is valid for TYPO3
*
* @param       string  command
* @param       int     userid of the vBulletin user
* @param       array   data of the vBulletin user
* @param       array   errormessage
* @return              true or false
*/     
function userdata_verify($vBulletin_userid, $vBulletin_user,  &$error)
{
    global $vbulletin;
    // get the user who change the values:
    $admin = $vbulletin->session->fetch_userinfo();
    //sometimes vbulletin changes userdata which are not relevant for typo3.
    // The problem is, that often no user is logged in f.E. when a user ask for a new password.
    // In such a case the errormessages from typo3 should not be shown, so return true (no error):
    if( $admin['userid'] < 1){
       //error_log(__LINE__.", ".__FILE__." cannot verify user because no admin user is logged in");
       return true;;
    }
    $ret= call_typo3_site("verify", $vBulletin_userid, $vBulletin_user, $error);
    //error_log(__FILE__." Errorcode in userdata_verify: \"".$ret."\", error: $error");
    
    return $ret;

}

/** Reset FPassword
*
* @param       string  command
* @param       int     userid of the vBulletin user
* @param       array   the new password in plain text (not md5)
* @param       array   errormessage
* @return              true or false
*/     
function userdata_reset_password($vBulletin_userid, $newpassword,  &$error)
{
    //global $vbulletin;
    //error_log(__FILE__." resetpassword, vbulletin userid=$vBulletin_userid");
    $vBulletin_user = array('md5_password'=> md5($newpassword), 'userid' => $vBulletin_userid);
    return call_typo3_site("reset_password", $vBulletin_userid, $vBulletin_user, $error);
}
function call_typo3_register_page(){
    global $vbulletin;
    //echo "call_typo3_register_page";
    $vbulletin->url = $GLOBALS['hr_vbulletin_connect_config']['typo3_url']."index.php?id=".$GLOBALS['hr_vbulletin_connect_config']['typo3_register_pid'];
    standard_redirect();    
}


?>