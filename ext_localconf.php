<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

// include vBulletin only in backend on every site. Otherwise it makes troubles when delete the cache. But in frontend it
// works well when include the vbulletin only when it is used. 
// This is mostly to improve the speed.
if (TYPO3_MODE=="BE"){ 
    //require_once(t3lib_extMgm::extPath('hr_vbulletin_connect')."user_vBulletin_global.php");
    if(isset($_GET['edit']['fe_users'])){
        require_once(t3lib_extMgm::extPath('hr_vbulletin_connect')."user_vBulletin_global.php");
        
    }
    if(isset($_GET['cmd']['fe_users'])){
        require_once(t3lib_extMgm::extPath('hr_vbulletin_connect')."user_vBulletin_global.php");
        
    }
}

if (TYPO3_MODE=="FE"){ 
    if(isset($_GET['tx_usercheck_pi1'])){
        require_once(t3lib_extMgm::extPath('hr_vbulletin_connect')."user_vBulletin_global.php");
    }
    else if(isset($_GET['logintype']) || isset($_POST['logintype'])){
        require_once(t3lib_extMgm::extPath('hr_vbulletin_connect')."user_vBulletin_global.php");
    }
    // when the user click the forgot Password link:
    else if(isset($_GET['tx_newloginbox_pi1']) ){
        require_once(t3lib_extMgm::extPath('hr_vbulletin_connect')."user_vBulletin_global.php");
    }
    else if(isset($_GET['type']) && $_GET['type'] == 450){
        require_once(t3lib_extMgm::extPath('hr_vbulletin_connect')."user_vBulletin_global.php");
    }
    // when the user click the forgot Password link:
    else if(isset($_GET['tx_felogin_pi1']) ){
        require_once(t3lib_extMgm::extPath('hr_vbulletin_connect')."user_vBulletin_global.php");
    }

}


if (TYPO3_MODE=="BE"){ 

    //Hooks for the tcemain (Backend editing):
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['hr_vbulletin_connect'] = 'EXT:hr_vbulletin_connect/class.user_tcemain_datamap.php:user_tcemain_datamap';
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['hr_vbulletin_connect'] = 'EXT:hr_vbulletin_connect/class.user_tcemain_cmdmap.php:user_tcemain_cmdmap';
}
if (TYPO3_MODE=="FE"){ 
    // typo3 3.8.x
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_feuserauth.php']['logoff_post_processing'][] = 'EXT:hr_vbulletin_connect/class.user_vbulletinconnect.php:user_vbulletinconnect->postprocess_logout';
    // typo3 4.x.x
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['logoff_post_processing'][] = 'EXT:hr_vbulletin_connect/class.user_vbulletinconnect.php:user_vbulletinconnect->postprocess_logout';
    

    $TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_userauth.php']['postUserLookUp'][] = 'EXT:hr_vbulletin_connect/class.user_vbulletinconnect.php:user_vbulletinconnect->postprocess_user';

    
    
    $GLOBALS ['TYPO3_CONF_VARS']['EXTCONF']['newloginbox']['forgotEmail'][] = 'user_forgotPassword';
    require_once(t3lib_extMgm::extPath('hr_vbulletin_connect').'user_forgotPassword_newloginbox.php');

    $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/felogin/pi1/class.tx_felogin_pi1.php'] = t3lib_extMgm::extPath('hr_vbulletin_connect').'class.ux_tx_felogin_pi1.php';        
        
    // register Service 
    t3lib_extMgm::addService($_EXTKEY,  'auth' /* sv type */,  'tx_vbulletin_auth_sv1' /* sv key */,
                array(

                        'title' => 'Auth vbulletin user',
                        'description' => 'Authentication of VBulletin users',

                        'subtype' => 'authUserFE',
                        'available' => true,
                        'priority' => 91,
                        'quality' => 50,

                        'os' => '',
                        'exec' => '',

                        'classFile' => t3lib_extMgm::extPath($_EXTKEY).'sv1/class.tx_vbulletin_auth_sv1.php',
                        'className' => 'tx_vbulletin_auth_sv1',
                )
        );

    // Achtung: nur ein Versuch, um vbulletin user einzuloggen, wo das Passwort nocht nicht übereinstimmt.
    // Das geht aber nicht weil der hashcode unbrauchbar ist.
//     t3lib_extMgm::addService($_EXTKEY,  'auth' /* sv type */,  'tx_vbulletinuser_auth_sv2' /* sv key */,
//                 array(
// 
//                         'title' => 'Auth vbulletin user',
//                         'description' => 'Authentication of VBulletin users, who are imported from vbulletin',
// 
//                         'subtype' => 'authUserFE',
//                         'available' => false,
//                         'priority' => 91,
//                         'quality' => 50,
// 
//                         'os' => '',
//                         'exec' => '',
// 
//                         'classFile' => t3lib_extMgm::extPath($_EXTKEY).'sv2/class.tx_vbulletinuser_auth_sv2.php',
//                         'className' => 'tx_vbulletinuser_auth_sv2',
//                 )
//         );

}


$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['sr_feuser_register']['tx_srfeuserregister_pi1']['confirmRegistrationClass'][] = 'EXT:hr_vbulletin_connect/class.tx_srfeuserregister_sync.php:&tx_srfeuserregister_sync';


$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['sr_feuser_register']['tx_srfeuserregister_pi1']['registrationProcess'][] = 'EXT:hr_vbulletin_connect/class.tx_srfeuserregister_sync.php:&tx_srfeuserregister_sync';

?>