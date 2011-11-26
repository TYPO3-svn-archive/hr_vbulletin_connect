<?php

########################################################################
# Extension Manager/Repository config file for ext "hr_vbulletin_connect".
#
# Auto generated 26-11-2011 11:24
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'vBulletin user sync',
	'description' => 'Sync vBulletin user and Typo3 fe_users. It allows Single sign on.',
	'category' => 'misc',
	'shy' => 0,
	'version' => '1.4.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'alpha',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 1,
	'lockType' => '',
	'author' => 'Herbert Roider',
	'author_email' => 'herbert.roider@utanet.at',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:39:{s:9:"ChangeLog";s:4:"09cf";s:10:"README.txt";s:4:"ee2d";s:34:"class.tx_srfeuserregister_sync.php";s:4:"642d";s:40:"class.user_feuser_vbulletinuser_sync.php";s:4:"03a6";s:37:"class.user_hrvbulletinconnect_div.php";s:4:"b357";s:29:"class.user_tcemain_cmdmap.php";s:4:"2028";s:30:"class.user_tcemain_datamap.php";s:4:"3e08";s:32:"class.user_vbulletin_feAdmin.php";s:4:"8d14";s:31:"class.user_vbulletinconnect.php";s:4:"9b38";s:40:"class.user_vbulletinuser_feuser_sync.php";s:4:"71de";s:26:"class.user_vbuser_test.php";s:4:"59fb";s:27:"class.ux_tx_felogin_pi1.php";s:4:"9e48";s:21:"ext_conf_template.txt";s:4:"40c1";s:12:"ext_icon.gif";s:4:"4879";s:17:"ext_localconf.php";s:4:"fa6c";s:14:"ext_tables.php";s:4:"66e6";s:14:"ext_tables.sql";s:4:"ef6a";s:24:"ext_typoscript_setup.txt";s:4:"dc8c";s:16:"locallang_db.php";s:4:"cbf4";s:35:"user_forgotPassword_newloginbox.php";s:4:"f967";s:25:"user_vBulletin_global.php";s:4:"5cdd";s:16:"doc/ext_icon.xcf";s:4:"2605";s:14:"doc/manual.sxw";s:4:"60cd";s:19:"doc/wizard_form.dat";s:4:"b41e";s:20:"doc/wizard_form.html";s:4:"1ebc";s:49:"modfunc1/class.tx_hrvbulletinconnect_modfunc1.php";s:4:"8a19";s:22:"modfunc1/locallang.php";s:4:"11e8";s:39:"pi1/class.tx_hrvbulletinconnect_pi1.php";s:4:"b7c2";s:17:"pi1/locallang.php";s:4:"b35b";s:22:"pi1/redirect_page.tmpl";s:4:"a0d4";s:26:"res/fe_admin_fe_users.tmpl";s:4:"5019";s:30:"res/fe_admin_fe_users_old.tmpl";s:4:"def0";s:20:"static/constants.txt";s:4:"5341";s:16:"static/setup.txt";s:4:"f277";s:39:"static/sr_feuser_register/constants.txt";s:4:"d41d";s:35:"static/sr_feuser_register/setup.txt";s:4:"c847";s:35:"sv1/class.tx_vbulletin_auth_sv1.php";s:4:"064c";s:44:"vBulletin_plugin/product-typo3_user_sync.xml";s:4:"b308";s:43:"vBulletin_plugin/update_typo3_user_hook.php";s:4:"59ae";}',
	'suggests' => array(
	),
);

?>