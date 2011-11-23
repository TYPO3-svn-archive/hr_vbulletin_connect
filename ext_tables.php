<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$tempColumns = Array (
	"tx_hrvbulletinconnect_vbulletin_user_id" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:hr_vbulletin_connect/locallang_db.php:fe_users.tx_hrvbulletinconnect_vbulletin_user_id",		
		"config" => Array (
			"type" => "none",
		)
	),
	"tx_hrvbulletinconnect_vbulletin_user_salt" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:hr_vbulletin_connect/locallang_db.php:fe_users.tx_hrvbulletinconnect_vbulletin_user_salt",		
		"config" => Array (
			"type" => "none",
		)
	),
	"tx_hrvbulletinconnect_vbulletin_user_password" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:hr_vbulletin_connect/locallang_db.php:fe_users.tx_hrvbulletinconnect_vbulletin_user_password",		
		"config" => Array (
			"type" => "none",
		)
	),
);


t3lib_div::loadTCA("fe_users");
t3lib_extMgm::addTCAcolumns("fe_users",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("fe_users","tx_hrvbulletinconnect_vbulletin_user_id;;;;1-1-1, tx_hrvbulletinconnect_vbulletin_user_salt, tx_hrvbulletinconnect_vbulletin_user_password");


if (TYPO3_MODE=="BE")	{
	t3lib_extMgm::insertModuleFunction(
		"web_info",		
		"tx_hrvbulletinconnect_modfunc1",
		t3lib_extMgm::extPath($_EXTKEY)."modfunc1/class.tx_hrvbulletinconnect_modfunc1.php",
		"LLL:EXT:hr_vbulletin_connect/locallang_db.php:moduleFunction.tx_hrvbulletinconnect_modfunc1"
	);
}

/** @todo dieser Codeteil kann wahrscheinlich auskommentiert werden: 
*/
// t3lib_extMgm::addService($_EXTKEY,  'auth' /* sv type */,  'tx_hrvbulletinconnect_sv1' /* sv key */,
// 		array(
// 
// 			'title' => 'vBulletin auth service',
// 			'description' => 'Authenticate vBulletin User as FE',
// 
// 			'subtype' => 'getUserFE,authUserFE',
// 
// 			'available' => TRUE,
// 			'priority' => 100,
// 			'quality' => 50,
// 
// 			'os' => '',
// 			'exec' => 'vBulletin',
// 
// 			'classFile' => t3lib_extMgm::extPath($_EXTKEY).'sv1/class.tx_hrvbulletinconnect_sv1.php',
// 			'className' => 'tx_hrvbulletinconnect_sv1',
// 		)
// 	);

t3lib_extMgm::addStaticFile($_EXTKEY,'static/','fe_admin');
t3lib_extMgm::addStaticFile($_EXTKEY,'static/sr_feuser_register/','sr_feuser_register');

//
$TCA['fe_users']['feInterface']['fe_admin_fieldList'] .= ',disable';

?>