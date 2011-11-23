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
 * @author    Herbert Roider <herbert.roider@utanet.at>
  
 */

    
// store the get vars and unset the arrays for vBulletin, because some parameter are the same
// and vBulletin modify some Parameter or delete it. Specially the parameter "forgot"
$save_get = $_GET;
$save_post = $_POST ;
unset($GLOBALS['HTTP_GET_VARS'] );
unset($_GET);
unset($GLOBALS['HTTP_POST_VARS'] );
unset($_POST);

$GLOBALS['HTTP_GET_VARS'] = array();
$_GET = array();
$GLOBALS['HTTP_POST_VARS'] = array();
$_POST = array();
     
// This is a hack need for vBulletin 4.x  @see includes/init.php line 623
$_POST['hrvbulletindummy'] = "";
     
error_log(__FILE__." wird eigebunden: currentDir = \"".getcwd());
//t3lib_div::devLog(__FILE__." wird eigebunden: currentDir = \"".getcwd(), 'hr_vbulletin_connect', 2 );
 

//    $cookies = "";
//    foreach($_COOKIE as $key => $value){
//         $cookies.="$key=$value; ";
//    }
// 
      if (TYPO3_MODE=="FE"){ 
          //echo "<pre>";
          //print_r(get_included_files());
          //echo "</pre>";
     } 

// include the main script of vBulletin
$user_feuser_vbulletinuser_sync_conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['hr_vbulletin_connect']);
$currentDir = getcwd();



chdir(PATH_site); // if forumDir is a relative path change to the root of the typo3 site

if(@chdir($user_feuser_vbulletinuser_sync_conf['forumDir'])){
     require_once('./global.php');
     require_once(DIR . '/includes/functions_login.php');
}
chdir($currentDir);       
    
// restore the GET-vars, reason see above
$GLOBALS['HTTP_GET_VARS']  = $save_get;
$_GET = $save_get;
$GLOBALS['HTTP_POST_VARS']  = $save_post;
$_POST = $save_post;

//    $cookies = "";
//    foreach($_COOKIE as $key => $value){
//         $cookies.="$key=$value; ";
//    }


?>