<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Herbert Roider (herbert.roider@utanet.at)
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
   This class contains functions and code from the extensions: 
        'sr_language_menu' 
                by   Kasper Skaarhoej <kasper@typo3.com>
                and  Stanislas Rolland <stanislas.rolland@fructifor.com>
            
        'rlmp_language_detection'
                by  robert lemke medienprojekte <rl@robertlemke.de>

   It creates the redirect page if a user comes from the vBulletin page.
   The redirect page provides a link to try login the user, and try to set the same language. 
    
   
  * @author      Herbert Roider <herbert.roider@utanet.at>
  * 

 */
//       $handle_1 = fopen ("/home/herbert/htdocs/copadata_2/fileadmin/log_1.txt", "a+");
//       fwrite($handle_1, __FILE__." wird eigebunden: currentDir = \"".getcwd()."\"\n");
//       fclose($handle_1);

require_once(PATH_tslib.'class.tslib_pibase.php');


$tx_usercheck_pi1_conf_default = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['hr_vbulletin_connect']);
$tx_usercheck_pi1_referer = t3lib_div::getIndpEnv('HTTP_REFERER');

// Referer is the forum:
if (  (strlen($tx_usercheck_pi1_referer) && stristr ($tx_usercheck_pi1_referer, $tx_usercheck_pi1_conf_default['vBurl'])) ){
     // Referer is _not_ the typo3 site:
     //if ( ! (strlen($tx_usercheck_pi1_referer) 
     //        && stristr ($tx_usercheck_pi1_referer, t3lib_div::getIndpEnv('TYPO3_SITE_URL')) ) ){
         require_once(t3lib_extMgm::extPath('hr_vbulletin_connect')."user_vBulletin_global.php");
     //}
}           

class tx_hrvbulletinconnect_pi1 extends tslib_pibase {
        var $prefixId = 'tx_hrvbulletinconnect_pi1';            // Same as class name
        var $scriptRelPath = 'pi1/class.tx_hrvbulletinconnect_pi1.php'; // Path to this script relative to the extension dir.
        var $extKey = 'hr_vbulletin_connect';   // The extension key.
        var $conf = array();
        var $cObj;
        /** 
         * @param       string          $content: HTML content
         * @param       array           $conf: The mandatory configuration array
         * @return      void            
         */
        function main($content,$conf)   {
            global $TSFE, $vbulletin;           
            $conf_default = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['hr_vbulletin_connect']);
            $this->conf['defaultLang'] = $conf_default['typo3_default_lang'];
            $this->conf = array_merge($this->conf,$conf);
            
            $this->pi_loadLL();
            //echo "hallo";
            
            // Break out if the GET-Parameter "redirect" is set to avoid endless loops:
            if($this->piVars['redirect']){
                //echo "avoid endless redirects";
                return;
            }
            //echo "sprachauswahl";
            

           // Break out, if language already selected
           //if (t3lib_div::GPvar ('L') !== NULL) return;
            
            // Break ouf if the last page visited was also on our site:
            
            $referer = t3lib_div::getIndpEnv('HTTP_REFERER');
            
            /** 
            Possible Folderstructures:
            
            root
              |
              +-TYPO3
              |
              +-vB
                
            root
              |
              +--TYPO3
                  |
                  +-vB
                  
             But not! because the referer match also on every TYPO3 page:
             root
               |
               +--vB
                   |
                   +-TYPO3 
            
            This don't work if vB is installed inside TYPO3
            */
//             if (strlen($referer) && stristr ($referer, t3lib_div::getIndpEnv('TYPO3_SITE_URL'))){
//                 //echo "gleiche Seite";
//                 //echo "typpo3-url=".t3lib_div::getIndpEnv('TYPO3_SITE_URL');
//                 return;
//             }           
            
            if ( ! (strlen($referer) && stristr ($referer, $conf_default['vBurl'])) ){
                //echo "referer ist nicht das forum";
                //echo $conf_default['vBurl'].",".$referer;
                return;
            }           
                                        
            
 
 
 
 
            // This part of code build the redirect url.
            // It is taken from class.tx_srlanguagemenu_pi1.php, Extension: 'sr_language_menu'
            $localTempl = new t3lib_TStemplate;
            $removeParams = array();
            $forwardParams = $this->local_add_vars($GLOBALS['HTTP_GET_VARS'],$removeParams);
            $forwardParams .= $this->local_add_vars($GLOBALS['HTTP_POST_VARS'],$removeParams);

            $isocode = "";
            $set_lang = $this->set_lang_link_vars($isocode);
            
            //$set_login_vars = $this->login_vbulletin_user();
            $set_login_vars = $this->set_login_link_vars();
            
            if(! ($set_lang ||  $set_login_vars  )){
                //echo "weder einloggen noch Sprache verändern";
                return;
            }
            if($set_lang){
                $this->LLkey = $isocode;
            }
            //echo $this->LLkey ;
            $GLOBALS['TSFE']->linkVars .= '&'.$this->prefixId.'[redirect]=1';
            
            $LD = $localTempl->linkData($GLOBALS['TSFE']->page,'','','','',$forwardParams,'0');
            $uri = $LD['totalURL'];        
            
            $url = $uri;
            //$url = $_SERVER['REQUEST_URI'];
            
                                        
            //echo $isocode;
            // set this, because the language isn't set correctly because the user comes from the
            // vbulletin board.
            $Template = $this->cObj->fileResource($this->conf['templateFile']);
            $content_templ = $this->cObj->getSubpart($Template, '###TEMPLATE_REDIRECT###');
            $markerArray['###MESSAGE###'] = $this->pi_getLL('message');
            $markerArray['###LINKTEXT###'] = $this->pi_getLL('linktext');
            $markerArray['###LINK_URL###'] = $url;
            $content = $this->cObj->substituteMarkerArray($content_templ, $markerArray);
            
             echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset='.$GLOBALS['TSFE']->renderCharset.'" />
<meta http-equiv="refresh" content="20; URL='.$url.'">
<base href="'.t3lib_div::getIndpEnv('TYPO3_SITE_URL').'" />
</head>
<body>'.$content.'
</body>
</html>
';
            exit();
        }


        /** This function is taken from class.tx_srlanguagemenu_pi1.php, Extension: 'sr_language_menu'
         */        
        function local_add_vars2($vars,$path) {
                $res='';
                reset ($vars);
                while (list ($key, $val) = each ($vars)) {
                        if (!is_array($val)) {
                                $res .= '&'.$path.'['.rawurlencode($key).']'.'='.rawurlencode($val);
                        } else {
                                $res .= $this->local_add_vars2($val, $path.'['.rawurlencode($key).']');
                        }
                }
                return $res;
        }
        /** This function is taken from class.tx_srlanguagemenu_pi1.php, Extension: 'sr_language_menu'
         */        
        function local_add_vars($vars,$varNames) {
                $res='';
                reset ($vars);
                while (list ($key, $val) = each ($vars)) {
                        if (is_array($val)) {
                                $res .= $this->local_add_vars2($val, rawurlencode($key));
                        } else {
                                if (($key != 'id') && ($key != 'type') && ($key != 'L') && !in_array($key,$varNames)) {
                                        $res .= '&'.rawurlencode($key).'='.rawurlencode($val);
                                }
                        }
                }
                return $res;
        }
         /** This function is taken from Extension: 'rlmp_language_detection' by robert lemke medienprojekte <rl@robertlemke.de>
         */        
        /**
         * Returns an array of sys_language records containing the ISO code as the key and the record's uid as the value
         * 
         * @return      array   sys_language records: ISO code => uid of sys_language record
         * @access      private
         */
        function getSysLanguages () {
                global $TYPO3_DB;

                if (strlen($this->conf['defaultLang'])) $availableLanguages [trim(strtolower($this->conf['defaultLang']))] = 0;
        
                        // Two options: prior TYPO3 3.6.0 the title of the sys_language entry must be one of the two-letter iso codes in order
                        // to detect the language. But if the static_languages is installed and the sys_language record points to the correct
                        // language, we can use that information instead.

                $res = $TYPO3_DB->exec_SELECTquery (
                        'lg_iso_2',
                        'static_languages',
                        '1'
                );
                if ($res) {
                               // debug("test");
                                // Table and field exist so create query for the new approach:
                        $res = $TYPO3_DB->exec_SELECTquery (
                                'sys_language.uid, static_languages.lg_iso_2 as isocode',
                                'sys_language LEFT JOIN static_languages ON sys_language.static_lang_isocode = static_languages.uid',
                                '1' . $this->cObj->enableFields ('sys_language') . $this->cObj->enableFields ('static_languages')
                        );
                } else {
                        $res = $TYPO3_DB->exec_SELECTquery (
                                'sys_language.uid, sys_language.title as isocode',
                                'sys_language',
                                '1' . $this->cObj->enableFields ('sys_language')
                        );
                }
                while ($row = $TYPO3_DB->sql_fetch_assoc($res)) {
                        $availableLanguages [trim(strtolower($row['isocode']))] = $row['uid'];
                }
                return $availableLanguages;
        }
        
        /**
         * Returns an array of sys_language records containing the ISO code as the key and the record's uid as the value
         * 
         * @return      array   ISO code (first 2 letters) => languageid of the vbulletin language table
         */
        function get_vbulletin_languages () {
            global $vbulletin;
            require_once(t3lib_extMgm::extPath('hr_vbulletin_connect').'user_vBulletin_global.php');
           $res = $vbulletin->db->query_read_slave("
                SELECT languageid, title, languagecode
                FROM " . TABLE_PREFIX . "language
                " );
            $out = array();
            while ($row = $vbulletin->db->fetch_array($res))
            {
                    // only the first 2 letters from the code in the case of "de_DE"
                    $out[substr(trim(strtolower($row['languagecode'])),0,2)] = $row['languageid'];
            }
            return $out;
        }
        /**
         * Set the: $GLOBALS['TSFE']->linkVars to set the same language as vBulletin
         * @param       string  The function set the isocode of the vbulletin site. ISO code (first 2 letters)
         * @return      boolean   true if succed, otherwise false
         */
        function set_lang_link_vars(&$isocode){
            global $TSFE, $vbulletin;           

            $vBLang_id = $vbulletin->userinfo['languageid'];
            
            $vb_languages = $this->get_vbulletin_languages();
            //debug($vb_languages);
            
            /** see function fetch_phrase in file: functions_misc.php
            */
            if ($vBLang_id == -1)
            {
                    $vBLang_id = LANGUAGEID;
            }
            else if ($vBLang_id == 0)
            {
                    $vBLang_id = $vbulletin->options['languageid'];
            }

            //debug ("vbLang_id=".$vBLang_id);
           
            // Get the typo3 language id by compare the isocodes
            $typo3_languages = $this->getSysLanguages ();
            //debug($typo3_languages);
            $isocode = array_search($vBLang_id, $vb_languages);
            //debug( "isocode von vb=".$isocode );
            $Typo3Lang_id = $typo3_languages[$isocode];
            //debug($typo3_languages);
            
            $current_typo3_language = 0;
            if ($GLOBALS['TSFE']->sys_language_content) {
                    $current_typo3_language = $GLOBALS['TSFE']->sys_language_content; 
            }
            //debug("current TYPO3 lang=".$current_typo3_language." Soll-language wegen vBulletin=".$Typo3Lang_id);
            // vbulletin and typo3 have the same language:
            if($Typo3Lang_id == $current_typo3_language){
                //echo "gleiche Sprache";
                return false;
            }

            
            
            if(strstr($GLOBALS['TSFE']->linkVars, '&L=')) {
                    $GLOBALS['TSFE']->linkVars = ereg_replace('&L=[0-9]*' , '&L='.$Typo3Lang_id, $GLOBALS['TSFE']->linkVars);
            } else {
                    $GLOBALS['TSFE']->linkVars .= '&L='.$Typo3Lang_id;
            }
            if(!$this->rlmp_language_detectionLoaded) {
                 $GLOBALS['TSFE']->linkVars = ereg_replace('&L=0' , '', $GLOBALS['TSFE']->linkVars);
            }
            //echo "Sprache muß gesetzt werden ";
            return true;

        
        }

        /**
         * Set the: $GLOBALS['TSFE']->linkVars with the required Parameter to log in.
         * \see tx_vbulletin_auth_sv1
         * 
         * @return     bool   true if succeed, otherwise false
         */
        function set_login_link_vars(){
             global $TSFE, $vbulletin;           
            //echo "usercheck";
           
            // Die Config-einstellungen aus der localconf.php holen:
            $conf_default = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);


            if(t3lib_div::_GP('tx_usercheck_pi1')){
                //echo "tx_usercheck_pi1 is already set";
                return false;
            }
            
            // user ist schon eingeloggt:
            if(is_array($GLOBALS['TSFE']->fe_user->user)){
                //echo "user ist schon eingeloggt";
                return false;
            }
            
            //require_once(t3lib_extMgm::extPath('hr_vbulletin_connect').'user_vBulletin_global.php');
          
            // get the vBulletin user
            $vb_user = $vbulletin->userinfo;
            if(!$vb_user['userid']){
                if(TYPO3_DLOG){
                    t3lib_div::devLog('No vBulletin user found password:'.$loginData['uident'].', username='.$loginData['uname'].' in '.__LINE__.": ".__FUNCTION__." file: ".__FILE__, $this->extKey, 2);
                }
                //echo "kein vbuser eingeloggt";
                return false;
            }
            
            $pseudopass = md5(COOKIE_SALT.$vb_user['username'].$vb_user['salt']);
            
            $GLOBALS['TSFE']->linkVars .= '&logintype=login&pid='.$conf_default['fe_users_store_pid'].'&user='.$vb_user['username'].'&pass='.$pseudopass.'&redirect_url='.$_SERVER['REQUEST_URI'];

            //echo "login parameters werden gesetzt";
            return true;
        
        }
    // NOt used!!!!
    function redirect_to_register_page(){
            
           // This part of code build the redirect url.
           // It is taken from class.tx_srlanguagemenu_pi1.php, Extension: 'sr_language_menu'
            $localTempl = new t3lib_TStemplate;
            $removeParams = array('tx_hrvbulletinconnect_pi1');
            $forwardParams = $this->local_add_vars($GLOBALS['HTTP_GET_VARS'],$removeParams);
            $forwardParams .= $this->local_add_vars($GLOBALS['HTTP_POST_VARS'],$removeParams);

            $isocode = "";
            $set_lang = $this->set_lang_link_vars($isocode);
            
            if($set_lang){
                $this->LLkey = $isocode;
            }
            
            $GLOBALS['TSFE']->linkVars .= '&'.$this->prefixId.'[redirect]=1';
            
            $LD = $localTempl->linkData($GLOBALS['TSFE']->page,'','','','',$forwardParams,'0');
            $uri = $LD['totalURL'];        
            
            $url = $uri;
             
                                        
            //echo $isocode;
            // set this, because the language isn't set correctly because the user comes from the
            // vbulletin board.
            $Template = $this->cObj->fileResource($this->conf['templateFile']);
            $content_templ = $this->cObj->getSubpart($Template, '###TEMPLATE_REDIRECT###');
            $markerArray['###MESSAGE###'] = $this->pi_getLL('message');
            $markerArray['###LINKTEXT###'] = $this->pi_getLL('linktext');
            $markerArray['###LINK_URL###'] = $url;
            $content = $this->cObj->substituteMarkerArray($content_templ, $markerArray); 
            return $content;   
    
    }
// not used, but it works
// Ein vbulletin user wird eingeloggt, wenn noch kein fe_user eingeloggt ist.
// Zum tragen kommt die Authentifizierung erst beim nächsten Seitenaufruf.
    function login_vbulletin_user()
    {
        global $vbulletin;
        //echo "loginUser=".$GLOBALS['TSFE']->loginUser;
        // a user is already logged in:
        if( $GLOBALS['TSFE']->loginUser ){
            return false;
        }
        
        // get the vbulletin user
        $vb_user = $vbulletin->userinfo;
        //print_r($vb_user);
        if(!$vb_user['userid']){
            if(typo3_dlog){
                t3lib_div::devlog('no vbulletin user is logged in:'.$this->login['uident'].', username='.$this->login['uname'].' in '.__line__.": ".__function__." file: ".__file__, $this->extkey, 2);
            }
            return false;
        }
        //echo "vbuser gefunden:";

        $loginData=array(
                'uname' => $vb_user['username'],
                'uident'=> 'dummypassword',
                'status' =>'login'
        );
        // Login user
        $GLOBALS['TSFE']->fe_user->checkPid = FALSE;
        $info = $GLOBALS['TSFE']->fe_user->getAuthInfoArray();
       // print_r($info);
        $user = $GLOBALS['TSFE']->fe_user->fetchUserRecord( $info['db_user'],
        $loginData['uname'] );
        
        //print_r($user);
        
        //$ok=$GLOBALS['TSFE']->fe_user->compareUident( $user, $loginData );
        $GLOBALS['TSFE']->fe_user->createUserSession( $user );
        $GLOBALS['TSFE']->loginUser = 1;
        $GLOBALS['TSFE']->fe_user->start();
        return true;          
                    
 
    }


}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_vbulletin_connect/pi1/class.tx_hrvbulletinconnect_pi1.php'])        {
        include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_vbulletin_connect/pi1/class.tx_hrvbulletinconnect_pi1.php']);
}

?>