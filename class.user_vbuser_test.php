<?php
/* 
http://eagle.intervis.org/copadata/typo3conf/ext/hr_vbulletin_connect/class.user_vbuser_test.php
*/

error_reporting(E_ALL & ~E_NOTICE);

//define('VERSION', '3.6.1');
//define('THIS_SCRIPT', 'tools.php');
//define('VB_AREA', 'tools');
//define('CUSTOMER_NUMBER', trim(strtoupper('A906FF737170')));
//echo getcwd()."\n";
$currentDir = getcwd();
echo $currentDir."   ";;
echo basename($currentDir."/");
chdir('/home/herbert/htdocs/copadata/forums/');
require_once('./global.php');
//echo DIR;



//print_r($vbulletin->session->vars);
//exit();

//require_once('./install/init.php');
//require_once(DIR . '/includes/adminfunctions.php');

//$specialtemplates = array();
//$datastore_class = (!empty($vbulletin->config['Datastore']['class'])) ? $vbulletin->config['Datastore']['class'] : 'vB_Datastore';

// if ($datastore_class != 'vB_Datastore')
// {
//         require_once(DIR . '/includes/class_datastore.php');
// }
//$vbulletin->datastore =& new $datastore_class($vbulletin, $db);
//$vbulletin->datastore->fetch($specialtemplates);

// $type = $vbulletin->input->clean_gpc('r', 'type', TYPE_STR);
// $customerid = $vbulletin->input->clean_gpc('p', 'customerid', TYPE_STR);
// $bbcustomerid = $vbulletin->input->clean_gpc('c', 'bbcustomerid', TYPE_STR);


        if (!$vbulletin->options['allowregistration'])
        {
                eval(standard_error(fetch_error('noregister')));
        }

        // check for multireg
        if ($vbulletin->userinfo['userid'] AND !$vbulletin->options['allowmultiregs'])
        {
                //eval(standard_error(fetch_error('alreadyregistered', $vbulletin->userinfo['username'], $vbulletin->session->vars['sessionurl'])));
        }

        // init user datamanager class
        $userdata =& datamanager_init('User', $vbulletin, ERRTYPE_ARRAY);

//         // coppa option
//         //$userdata->set_info('coppauser', $vbulletin->GPC['coppauser']);
//         //$userdata->set_info('coppapassword', $vbulletin->GPC['password']);
//         //$userdata->set_bitfield('options', 'coppauser', $vbulletin->GPC['coppauser']);
//         //$userdata->set('parentemail', $vbulletin->GPC['parentemail']);
// 
//         // check for missing fields
//         if (empty($vbulletin->GPC['username'])
//                 OR empty($vbulletin->GPC['email'])
//                 OR empty($vbulletin->GPC['emailconfirm'])
//                 OR ($vbulletin->GPC['coppauser'] AND empty($vbulletin->GPC['parentemail']))
//                 OR (empty($vbulletin->GPC['password']) AND empty($vbulletin->GPC['password_md5']))
//                 OR (empty($vbulletin->GPC['passwordconfirm']) AND empty($vbulletin->GPC['passwordconfirm_md5']))
//         )
//         {
//                 //$userdata->error('fieldmissing');
//                 echo "error 1";
//         }
// 
//         // check for matching passwords
//         if ($vbulletin->GPC['password'] != $vbulletin->GPC['passwordconfirm'] OR (strlen($vbulletin->GPC['password_md5']) == 32 AND $vbulletin->GPC['password_md5'] != $vbulletin->GPC['passwordconfirm_md5']))
//         {
//                 //$userdata->error('passwordmismatch');
//         }
//         // set password
//         $userdata->set('password', ($vbulletin->GPC['password_md5'] ? $vbulletin->GPC['password_md5'] : $vbulletin->GPC['password']));
//         $userdata->set('password', 'e7fbfbeb0ec8a09a1bbd7dbc56d8a294'); // pass5
//         // check for matching email addresses
//         if ($vbulletin->GPC['email'] != $vbulletin->GPC['emailconfirm'])
//         {
//                 //$userdata->error('emailmismatch');
//         }
//         $userdata->set('email', 'herbert1@sofablau.com');
//         // set email
//         //$userdata->set('email', $vbulletin->GPC['email']);
// 
//         //$userdata->set('username', $vbulletin->GPC['username']);
//         $userdata->set('username', 'bertl1');
//         // check referrer
//         if ($vbulletin->GPC['referrername'] AND !$vbulletin->userinfo['userid'])
//         {
//                 //$userdata->set('referrerid', $vbulletin->GPC['referrername']);
//         }
//         
//         //$userdata->set('referrerid', 0);
// //         // Check Reg Image
// //         if ($vbulletin->options['regimagecheck'] AND $vbulletin->options['regimagetype'])
// //         {
// //                 require_once(DIR . '/includes/functions_regimage.php');
// //                 if (!verify_regimage_hash($vbulletin->GPC['imagehash'], $vbulletin->GPC['imagestamp']))
// //                 {
// //                         $userdata->error('register_imagecheck');
// //                 }
// //         }
// 
//         // Set specified options
// //         if (!empty($vbulletin->GPC['options']))
// //         {
// //                 foreach ($vbulletin->GPC['options'] AS $optionname => $onoff)
// //                 {
// //                         $userdata->set_bitfield('options', $optionname, $onoff);
// //                 }
// //         }
// 
//         // assign user to usergroup 3 if email needs verification
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
//         // set usergroupid
//         $userdata->set('usergroupid', 3);
// 
//         // set languageid
//         $userdata->set('languageid', $vbulletin->userinfo['languageid']);
// 
//         // set user title
//         $userdata->set_usertitle('', false, $vbulletin->usergroupcache["$newusergroupid"], false, false);
// 
//         // set profile fields
//         //$customfields = $userdata->set_userfields($vbulletin->GPC['userfield'], true, 'register');
//         
//         // set birthday
// //         $userdata->set('showbirthday', $vbulletin->GPC['showbirthday']);
// //         $userdata->set('birthday', array(
// //                 'day'   => 29,
// //                 'month' => 7,
// //                 'year'  => 1970
// //         ));
// 
//         // set time options
//         //$userdata->set_dst($vbulletin->GPC['dst']);
//         //$userdata->set('timezoneoffset', $vbulletin->GPC['timezoneoffset']);
// 
//         // register IP address
//         $userdata->set('ipaddress', IPADDRESS);
// 
//         //($hook = vBulletinHook::fetch_hook('register_addmember_process')) ? eval($hook) : false;
// echo "presave\n";
//         $userdata->pre_save();
// print_r($userdata->errors);
//         // check for errors
//         if (!empty($userdata->errors))
//         {
//                 $_REQUEST['do'] = 'register';
// 
//                 $errorlist = '';
//                 foreach ($userdata->errors AS $index => $error)
//                 {
//                         $errorlist .= "<li>$error</li>";
//                 }
// 
//                 $username = htmlspecialchars_uni($vbulletin->GPC['username']);
//                 $email = htmlspecialchars_uni($vbulletin->GPC['email']);
//                 $emailconfirm = htmlspecialchars_uni($vbulletin->GPC['emailconfirm']);
//                 $parentemail = htmlspecialchars_uni($vbulletin->GPC['parentemail']);
//                 $selectdst = array($vbulletin->GPC['dst'] => 'selected="selected"');
//                 $sbselected = array($vbulletin->GPC['showbirthday'] => 'selected="selected"');
//                 $show['errors'] = true;
//         }
//         else
//         {
//                 echo "keine Fehler";
//                 $show['errors'] = false;
// 
//                 // save the data
//                 //$vbulletin->userinfo['userid']
//                 //        = $userid
//                 //        = $userdata->save();
// 
//                 //if ($userid)
//         }
        $existing = array('userid'=>4);
        $userdata->set_existing($existing);
        $userdata->set('email', 'test@sofablau.com');
        //$userdata->pre_save();
print_r($userdata->errors);
        //$userdata->save();
        $userid = 4;
        //$userid = 345;
        $user = fetch_userinfo($userid);
        if($user){
            echo "ok";
        }
        echo "<pre>";
        print_r($user);
        echo "</pre>";


?>

