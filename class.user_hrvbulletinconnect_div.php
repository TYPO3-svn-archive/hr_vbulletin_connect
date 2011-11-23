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
 
 
 
 
 */

class user_hrvbulletinconnect_div {
 
    
    /** vBulletin change some special characters, but this is not acceptable in conjunction with typo3.
    Don't use the function $this->vBulletin_userdata->verify_username from the vBulletin vB_DataManager_User.
    \todo  only ascii characters are allowed!! and no space
    * 
    * @param   strint  username
    * @return   bool   true if success, otherwise false
    */     

    function verify_username($username){
        $errormsg = "username: \"$username\"  contains bad characters";
        
        if(! $this->test_allowed_char($username)){
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
    
    function get_vbulletin_charset(){
        global $vbulletin, $stylevar ; 
        return  $stylevar['charset'];
    }
    function get_typo3_charset(){
        $typo3_charset = 'iso-8859-1';
        if (TYPO3_MODE=="BE") { 
            // \see: /typo3/template.php :  function initCharset()
            $typo3_charset = $GLOBALS['LANG']->charSet ? $GLOBALS['LANG']->charSet : $typo3_charset;
        
        }else{
            $typo3_charset = $GLOBALS['TSFE']->renderCharset;
        }
        return $typo3_charset;
    }
   
    function convert_fe_user_charset(&$array, $from_charset, $to_charset){

        $csconvobj = t3lib_div::makeinstance('t3lib_cs');
        $from_charset = $csconvobj->parse_charset($from_charset);
        $to_charset = $csconvobj->parse_charset($to_charset);
        if(is_array($array)){
            foreach($array as $key => $value){
//                 switch($key){
//                 case 'email':
//                 case 'www':
//                 case 'homepage':
//                 case 'username':
//                     $converted =  $csconvobj->conv($value,$from_charset,$to_charset);
//                     if($converted == false){
//                         error_log("convertierung hat nicht funktioniert");
//                     }else{
//                         $array[$key] = $converted;
//                     }
//                     error_log( "$key= ($value)=".$converted);
//                     break;
//                 }
                    $converted =  $csconvobj->conv($value,$from_charset,$to_charset);
                    if($converted == false){
                        //error_log("convertierung hat nicht funktioniert, value=$value");
                    }else{
                        $array[$key] = $converted;
                    }
                    //error_log( "$key= ($value)=".$converted);
           
            }
        }
       
    }
   function convert_vbulletin_user_charset(&$array, $from_charset, $to_charset){
        $this->convert_fe_user_charset($array, $from_charset, $to_charset);
   }    
    /**
    * @param   string   
    * @return   bool   false, if not allowed characters are found. This is mainly to test the username
    
    */
    function test_allowed_char($str){
        $strLen = strlen($str);
                //echo "username = ".$vb_user['username'];
        for ($a=0;$a<$strLen;$a++)      {       // Traverse each char in string.
            $chr=substr($str,$a,1);
            $ord=ord($chr);
            if ($ord>=127)      { 
                return false;     
            }
            if($ord <= 32){
                return false;
            }
        }
        return true;
    }

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_vbulletin_connect/class.user_hrvbulletinconnect_div.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_vbulletin_connect/class.user_hrvbulletinconnect_div.php']);
}

?>