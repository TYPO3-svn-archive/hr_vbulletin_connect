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
 * Module extension (addition to function menu) 'vBulletin_user' for the 'hr_vbulletin_connect' extension.
 *
 * @author	Herbert Roider <herbert.roider@utanet.at>
 */

//       $handle_1 = fopen ("/home/herbert/htdocs/copadata_2/fileadmin/log_1.txt", "a+");
//       fwrite($handle_1, __FILE__." wird eigebunden: currentDir = \"".getcwd()."\"\n");
//       fclose($handle_1);

//echo "hallo";


require_once(t3lib_extMgm::extPath('hr_vbulletin_connect')."user_vBulletin_global.php");

require_once(PATH_t3lib."class.t3lib_extobjbase.php");

require_once(t3lib_extMgm::extPath('hr_vbulletin_connect').'class.user_feuser_vbulletinuser_sync.php');
require_once(t3lib_extMgm::extPath('hr_vbulletin_connect').'class.user_vbulletinuser_feuser_sync.php');

require_once(t3lib_extMgm::extPath('hr_vbulletin_connect').'class.user_hrvbulletinconnect_div.php');

class tx_hrvbulletinconnect_modfunc1 extends t3lib_extobjbase {
        var $vBulletin_sync;
        	
        var $filter = "";
        var $offset = 0;// number of the page (pagebrowser), first page is 0
        var $db_offset = 0;// offset x max_rows
        var $max_rows = 200;// max. rows on a page   
           
        function modMenu()	{
		global $LANG;

		return Array (
                        'type' => array(
                                'list_not_connected_user' => 'not connected typo3 users',
                                'list_connected_user' => 'connected users',
                                'list_not_connected_vbulletin_user' => 'not connected vBulletin users',
                        )
		
                
                
                
                );
	}

	function main()	{
			// Initializes the module. Done in this function because we may need to re-initialize if data is submitted!
		global $SOBE,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
                global $vbulletin, $vbphrase;
                // invoke vBulletin:
                $this->vBulletin_sync = t3lib_div::makeInstance('user_feuser_vbulletinuser_sync');
                
                $this->filter = t3lib_div::_GP('filter');
                $this->offset = intval(t3lib_div::_GP('offset') );
                $this->db_offset = $this->offset * $this->max_rows;

		//$theOutput.=$this->pObj->doc->spacer(5);
		//$theOutput.=$this->pObj->doc->section($LANG->getLL("title"),"Dummy content here...",0,1);
                
                $cmd = t3lib_div::_GP('cmd');
                $feuser = t3lib_div::_GP('feuser');
                $error = false;
                if($cmd){
                    switch($cmd){
                    case 'create':
                        $typo3_user = t3lib_BEfunc::getRecord("fe_users",$feuser);
                        //debug($typo3_user);
                        $this->vBulletin_sync->user_create($typo3_user);
                        if (!empty($this->vBulletin_sync->vBulletin_userdata->errors)){
                            $error = true;
                            $errormsg = "vBulletin returns some errors:<br>\n";
                            foreach($this->vBulletin_sync->vBulletin_userdata->errors as $key => $value){
                                $errormsg .= "$key: $value <br> \n";
                            }
                            break;
                         }
                         $this->vBulletin_sync->post_user_create($typo3_user);
                         if (!empty($this->vBulletin_sync->vBulletin_userdata->errors)){
                            $error = true;
                            $errormsg = "vBulletin returns some errors:<br>\n";
                            foreach($this->vBulletin_sync->vBulletin_userdata->errors as $key => $value){
                                $errormsg .= "$key: $value <br> \n";
                            }
                            break;
                        }
                        $message.= "vBulletin user: ". $this->vBulletin_sync->vBulletin_userdata->user['username']."<br>userid: ".$this->vBulletin_sync->vBulletin_userdata->user['userid']."<br>is successful created";
                        break;
                    case 'delete':
                        $typo3_user = t3lib_BEfunc::getRecord("fe_users",$feuser);
                        //debug($typo3_user);
                        $this->vBulletin_sync->user_delete($typo3_user['uid']);
                        if (!empty($this->vBulletin_sync->vBulletin_userdata->errors)){
                            $error = true;
                            $errormsg = "vBulletin returns some errors:<br>\n";
                            foreach($this->vBulletin_sync->vBulletin_userdata->errors as $key => $value){
                                $errormsg .= "$key: $value <br> \n";
                            }
                            break;
                         }
                        $message.= "vBulletin user is deleted";

                        break;
                    case 'create_all':
                        $dbres = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', 'pid=\''.$this->pObj->id.'\' AND tx_hrvbulletinconnect_vbulletin_user_id = 0'.t3lib_BEfunc::deleteClause('fe_users'),'username');
                        if(!$dbres){
                            $errormsg.= "cannot access the db";
                            $error = true;
                            break;
                        }
                        $failed_users = array();
                        $i=0;
                        while($fe_user = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbres) && $i<200){
                            $datastore = t3lib_div::makeInstance('user_feuser_vbulletinuser_sync');
                            $datastore->user_create($fe_user);
                            //debug($fe_user['uid']);
                            if (!empty($datastore->vBulletin_userdata->errors)){
                                $failed_users[] = array('uid'=>$fe_user['uid'], 'username'=>$fe_user['username']);
                                continue;
                            }
                            $datastore->post_user_create($fe_user);
                            if (!empty($datastore->vBulletin_userdata->errors)){
                                $failed_users[] = array($fe_user['uid'], $fe_user['username']);
                                continue;
                            }
                            $i++;
                        }
                        $message.="$i TYPO3 users successful imported in vBulletin (max. 200)";
                        if(count($failed_users)){
                            $errormsg.= "<br>Some users failed:<br>";
                            foreach($failed_users as $user){
                                $errormsg.=$user['uid']." ".$user['username']."<br>\n";
                            }
                        }
                        
                    
                        break;
                    
                       case 'unlink':
                            $typo3_user = t3lib_BEfunc::getRecord("fe_users",$feuser);
                            //debug($typo3_user);
                            $GLOBALS['TYPO3_DB']->exec_UPDATEquery("fe_users", "uid='".$typo3_user['uid']."'  AND pid=".$this->pObj->id.t3lib_BEfunc::deleteClause('fe_users'),
                                    array(
                                            "tx_hrvbulletinconnect_vbulletin_user_salt"=>'',
                                            "tx_hrvbulletinconnect_vbulletin_user_password"=>'',
                                            "tx_hrvbulletinconnect_vbulletin_user_id"=>'',
                                        )
                                );
                            $message.= "vBulletin user is unlinked";
                            break;
                       
                       case 'unlink_all':
                            $GLOBALS['TYPO3_DB']->exec_UPDATEquery("fe_users", " pid=".$this->pObj->id.t3lib_BEfunc::deleteClause('fe_users'),
                                    array(
                                            "tx_hrvbulletinconnect_vbulletin_user_salt"=>'',
                                            "tx_hrvbulletinconnect_vbulletin_user_password"=>'',
                                            "tx_hrvbulletinconnect_vbulletin_user_id"=>'',
                                        )
                                );
                            $message.= "vBulletin user is unlinked";
                            break;
                       
                       case 'import_all_vbulletin_users':
                            $dbres = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', 'tx_hrvbulletinconnect_vbulletin_user_id > 0'.t3lib_BEfunc::deleteClause('fe_users'));
                            if(!$dbres){
                                return "error";
                            }
                            $connected_fe_users = "0";
                            while($fe_user = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbres)){
                                $connected_fe_users.=",".$fe_user['tx_hrvbulletinconnect_vbulletin_user_id'];
                            }
                            $users = $vbulletin->db->query_read("
                            SELECT DISTINCT *
                            FROM " . TABLE_PREFIX . "user 
                            WHERE NOT (userid IN ($connected_fe_users) )
                            ORDER BY username
                            ");
                            $i=0;
                            while ($vb_user = $vbulletin->db->fetch_array($users) && $i<200)
                            {
                                $sync = t3lib_div::makeInstance('user_vbulletinuser_feuser_sync');
                                if(!$sync->create_typo3user($vb_user, $error, $this->pObj->id)){
                                    $errormsg .= $error."<br>\n";
                                    continue;
                                }
                                $i++;
                                //$message.= "vBulletin user successful imported";
                            }
                            $message.= "$i vBulletin user successful imported (max. 200)";
                        break;

                       case 'import_vbulletin_user':
                            $vb_userid = t3lib_div::_GP('vbuser');
                            if(!$vb_userid){
                                $errormsg .= "cannot import vBulletin user, userid is not set";
                            }
                            $vb_user = fetch_userinfo($vb_userid);
                            
                            $sync = t3lib_div::makeInstance('user_vbulletinuser_feuser_sync');
                            if(!$sync->create_typo3user($vb_user, $error, $this->pObj->id)){
                                $errormsg .= $error;
                                break;
                            }
                            $message.= "vBulletin user successful imported";
                            
                            
                           break;
                    }
                }
                
                //$message .= __PHPSELF__;
                
                
                //$theOutput.=$this->pObj->doc->spacer(5);
                $theOutput.=$this->pObj->doc->section("Messages","<font color=\"red\">$errormsg</font><br>$message",0,1);

		$menu=array();
//		$menu[]=t3lib_BEfunc::getFuncCheck($this->pObj->id,"SET[tx_hrvbulletinconnect_modfunc1_check]",$this->pObj->MOD_SETTINGS["tx_hrvbulletinconnect_modfunc1_check"]).$LANG->getLL("checklabel");
                
                $menu[] = t3lib_BEfunc::getFuncMenu($this->pObj->id,'SET[type]',$this->pObj->MOD_SETTINGS['type'],$this->pObj->MOD_MENU['type'],'index.php').'<br/>';
		
                
                
                
                //$theOutput.=$this->pObj->doc->spacer(5);
		$theOutput.=$this->pObj->doc->section("Frontendusers",implode(" - ",$menu),0,1);
        $theOutput.=$this->renderSearchForm() ; 
                                // Branching:
                switch($this->pObj->MOD_SETTINGS['type'])       {
                        case 'list_connected_user':
                            $theOutput.=$this->list_connected_user();
                            break;
                        case 'list_not_connected_user':
                            $theOutput.=$this->list_not_connected_user();
                            break;
                                
                        case 'list_not_connected_vbulletin_user':
                            $theOutput.=$this->list_not_connected_vbulletin_user();
                            break;
                }
        

		return $theOutput;
	}

        function list_connected_user(){
            $div = t3lib_div::makeinstance('user_hrvbulletinconnect_div');
            //echo "filter=".$this->filter;
            
            $content .= "<table cellspacing=\"1\" cellpadding=\"0\" border=\"0\" class=\"lrPadding c-list\">\n";
            
            $whereclause =  'pid=\''.$this->pObj->id
                .'\' AND tx_hrvbulletinconnect_vbulletin_user_id > 0'.t3lib_BEfunc::deleteClause('fe_users')
                .' AND fe_users.username LIKE ('
                .$GLOBALS['TYPO3_DB']->fullQuoteStr('%'.$this->filter.'%',"fe_users")
                .')';
            
            $dbres = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', $whereclause,'', 'username', $this->db_offset.",50000");
            if(!$dbres){
                 return "error";
            }
        
 
        $maxrows = $this->max_rows;
        //$maxrows++;
        $row_count = mysql_num_rows($dbres);
        //echo "rowcount= $row_count<br>\n";
        $row_count+= $this->db_offset;
        $pagelist = $this->rederPagebrowser($row_count);
        

            
            
            $content.="<tr  class=\"bgColor5 tableheader\">\n";
            $content.="<td>&nbsp;</td><td>&nbsp;</td>";
            
            $content.='<td><a href="'.$this->linkSelf('&cmd=unlink_all').'" onclick="return confirm(\'Are you sure to unlink (not delete) all vBulletin user ?\');"><img'.t3lib_iconWorks::skinImg($this->pObj->doc->backPath,'gfx/delete_record.gif','width="11" height="12"').' title="unlink all (max. 200) equivalent vBulletin user" alt="unlink" /></a></td>';
            
            
            $content.="<td>uid</td><td>username</td><td>vBulletin<br>userid</td><td>errors</td>";
            $content.="</tr>";
            
            while( ($fe_user = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbres))   && $maxrows--  ){
//echo "maxrows=".$maxrows;
                $tCells = array();
                $errors = array();
                
                $vb_user = fetch_userinfo($fe_user['tx_hrvbulletinconnect_vbulletin_user_id']);
                $div = t3lib_div::makeinstance('user_hrvbulletinconnect_div');
                $div->convert_vbulletin_user_charset($vb_user, $div->get_vbulletin_charset(), $div->get_typo3_charset() );
 
                
                $has_equiv_vbuser = true;
                $single_sign_on = true;
                if(is_array($vb_user)){
                     if(strcmp($vb_user['username'], $fe_user['username'])){
                        $errors[]= "username don't match: ".$vb_user['username'].", ".$fe_user['username'];
                        $single_sign_on = false;
                    }
                    if(strcmp($vb_user['homepage'], $fe_user['www'])){
                        $errors[]= "www don't match";
                    }
                    if(strcmp($vb_user['email'], $fe_user['email'])){
                        $errors[]= "email don't match: ".$vb_user['email'].", ".$fe_user['email'];
                    }
                    if(strcmp($vb_user['password'], md5($fe_user['password'].$vb_user['salt']))){
                        $errors[]= "md5-password don't match";
                        $single_sign_on = false;
                    }
                   
                }else{
                    $has_equiv_vbuser = false;
                    $errors[]="no equivalent vBulletin user found";
                    $single_sign_on = false;
                    
                }
                
                $onClick = t3lib_BEfunc::editOnClick('&edit[fe_users]['.$fe_user['uid'].']=edit',$this->pObj->doc->backPath);
                $editIcon = '<a href="#" onclick="'.htmlspecialchars($onClick).'">'.
                             '<img'.t3lib_iconWorks::skinImg($this->pObj->doc->backPath,'gfx/edit2.gif','width="11" height="12"').' title="edit the typo3 frontend user" alt="edit" /></a>';
                $tCells[]="<td>".$editIcon."</td>";
                 
                
                if($single_sign_on){
                    $tCells[]="<td>".'<img'.t3lib_iconWorks::skinImg($this->pObj->doc->backPath,'gfx/icon_ok2.gif','width="11" height="11"').' title="Single Sign On is Ok" alt="Single Sign On" />'."</td>";
                }else{
                     $tCells[]="<td>".'<img'.t3lib_iconWorks::skinImg($this->pObj->doc->backPath,'gfx/icon_fatalerror.gif','width="11" height="11"').' title="Single Sign On would failed" alt="Single Sign On failed" />'."</td>";
                }
                 
                 if($has_equiv_vbuser){
                     $tCells[]='<td><a href="'.$this->linkSelf('&cmd=unlink&feuser='.$fe_user['uid']).'" onclick="return confirm(\'Are you sure to unlink (not delete) the vBulletin user ?\');"><img'.t3lib_iconWorks::skinImg($this->pObj->doc->backPath,'gfx/delete_record.gif','width="11" height="12"').' title="unlink the linked equivalent vBulletin user" alt="unlink" /></a></td>';
                     
                 }else{
                     $tCells[]='<td>&nbsp;</td>';
                 }
                
                
                $tCells[]="<td>".$fe_user['uid']."</td><td>".htmlspecialchars($fe_user['username'])."</td>";
                $tCells[]="<td>".$fe_user['tx_hrvbulletinconnect_vbulletin_user_id']."</td>";

                
//  
                
                
                $cell="<td>";
                foreach($errors as $err){
                    $cell.= $err." | ";
                }
                $cell.="</td>";
                $tCells[] = $cell;
                $content.= '
                                                <tr  class="bgColor-10">
                                                        '.implode('
                                                        ',$tCells).'
                                                </tr>';

            }
            $content.="</table>";
            $content .= $pagelist;
            return $content;
 
         
         
         
         
        }

        function list_not_connected_user(){
                    
            $content = "<table  cellspacing=\"1\" cellpadding=\"0\" border=\"0\" class=\"lrPadding c-list\">\n";
            
           $whereclause =  'pid=\''.$this->pObj->id
                .'\' AND tx_hrvbulletinconnect_vbulletin_user_id = 0'.t3lib_BEfunc::deleteClause('fe_users')
                .' AND fe_users.username LIKE ('
                .$GLOBALS['TYPO3_DB']->fullQuoteStr('%'.$this->filter.'%',"fe_users")
                .')';
           
            
            $dbres = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', $whereclause,'', 'username', $this->db_offset.",50000");
            if(!$dbres){
                debug("mysqleror");
                return "error";
            }
        
            $maxrows = $this->max_rows;
            //$maxrows++;
            $row_count = mysql_num_rows($dbres);
            //echo "rowcount= $row_count<br>\n";
            $row_count+= $this->db_offset;
            $pagelist = $this->rederPagebrowser($row_count);
        
            
            
            
            
            $content.="<tr  class=\"bgColor5 tableheader\">\n";
            $content.="<td>&nbsp;</td>";
            $content.="<td>";
            if(! $fe_user['tx_hrvbulletinconnect_vbulletin_user_id']){
                      $content.='<a href="'.$this->linkSelf('&cmd=create_all').'"  onclick="return confirm(\'Are you sure to create all vBulletin users?\');"><img'.t3lib_iconWorks::skinImg($this->pObj->doc->backPath,'gfx/new_el.gif','width="11" height="12"').' title="Create all (max. 200) vBulletin user to allow Single Sign On" alt="Create all connected vBulletin users" /></a></td>';
            }

            
            $content.="<td>uid</td><td>username</td><td>&nbsp;</td><td>&nbsp;</td>";
            
            
            $content.="</tr>";

            while($fe_user = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbres)){
                $content.="<tr    class=\"bgColor-10\">\n";
                
                $content.="<td>";
                $onClick = t3lib_BEfunc::editOnClick('&edit[fe_users]['.$fe_user['uid'].']=edit',$this->pObj->doc->backPath);
                $editIcon = '<a href="#" onclick="'.htmlspecialchars($onClick).'">'.
                                                                '<img'.t3lib_iconWorks::skinImg($this->pObj->doc->backPath,'gfx/edit2.gif','width="11" height="12"').' title="" alt="" />'.
                                                                '</a>';
                $content.=$editIcon;
                
                $content.="<td>";
                if(! $fe_user['tx_hrvbulletinconnect_vbulletin_user_id']){
                      $content.='<a href="'.$this->linkSelf('&cmd=create&feuser='.$fe_user['uid']).'" ><img'.t3lib_iconWorks::skinImg($this->pObj->doc->backPath,'gfx/new_el.gif','width="11" height="12"').' title="Create a connected vBulletin user to allow Single Sign On" alt="Create connected vBulletin user" /></a>';

                }else{
                    $content.='&nbsp;';
                }
                $content.="</td>";
                $content.="<td>".$fe_user['uid']."</td><td>".htmlspecialchars($fe_user['username'])."</td>";

                $content.="</tr>";
             }
             $content.="</table>";
             $content .= $pagelist;
            return $content;
       
        }
        function list_not_connected_vbulletin_user(){
            global $vbulletin, $vbphrase;
            $dbres = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'fe_users', 'tx_hrvbulletinconnect_vbulletin_user_id > 0'.t3lib_BEfunc::deleteClause('fe_users'));
            if(!$dbres){
                 return "error";
            }
            $connected_fe_users = "0";
            while($fe_user = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbres)){
                $connected_fe_users.=",".$fe_user['tx_hrvbulletinconnect_vbulletin_user_id'];
            }
            //$connected_fe_users = "0";
            
            $whereclause = " NOT (userid IN ($connected_fe_users) ) AND username LIKE ('".$vbulletin->db->escape_string('%'.$this->filter.'%')."')";
            //$whereclause = " 1=1 ";
            //echo "whereclause=".$whereclause;
            $users = $vbulletin->db->query_read("
            SELECT DISTINCT *
            FROM " . TABLE_PREFIX . "user 
            WHERE  $whereclause
            ORDER BY username
            LIMIT ".$this->db_offset.",50000
            ");
           
            
            $maxrows = $this->max_rows;
            //$maxrows++;
            $row_count = $vbulletin->db->num_rows($users);
            //echo "rowcount= $row_count<br>\n";
            $row_count+= $this->db_offset;
            $pagelist = $this->rederPagebrowser($row_count);
            
            $content.="<table cellspacing=\"1\" cellpadding=\"0\" border=\"0\" class=\"lrPadding c-list\"><tr  class=\"bgColor5 tableheader\">\n";
                $cell="<td>";
                $cell.='<a href="'.$this->linkSelf('&cmd=import_all_vbulletin_users&vbuser='.$vb_user['userid']).'"   onclick="return confirm(\'Are you sure to import all vBulletin users?\');" ><img'.t3lib_iconWorks::skinImg($this->pObj->doc->backPath,'gfx/new_el.gif','width="11" height="12"').' title="Import all (max. 200) vBulletin user to allow Single Sign On" alt="Import all vBulletin user" /></a>';
                $cell.="</td>";

            
            $content.="$cell<td>vBulletin<br>userid</td><td>username</td></tr>";
            while ( ($vb_user = $vbulletin->db->fetch_array($users)) && $maxrows--)
            {
                $tCells = array();
                $errors = array();
                
                $cell="<td>";
                $cell.='<a href="'.$this->linkSelf('&cmd=import_vbulletin_user&vbuser='.$vb_user['userid']).'" ><img'.t3lib_iconWorks::skinImg($this->pObj->doc->backPath,'gfx/new_el.gif','width="11" height="12"').' title="Import a vBulletin user to allow Single Sign On" alt="Import a vBulletin user" /></a>';
                $cell.="</td>";
                $tCells[] = $cell;
                
                
                
                $tCells[] = "<td>".htmlspecialchars($vb_user['userid'])."</td>";
                $tCells[] = "<td>".htmlspecialchars($vb_user['username'])."</td>";
                           
 
                
                
                $content.= '
                                                <tr    class="bgColor-10">
                                                        '.implode('
                                                        ',$tCells).'
                                                </tr>';

            }
            $content .= "</table>";
            $content .= $pagelist;
            return $content;            
        
        }
        
        /** From realurl:
         * Links to the module script and sets necessary parameters (only for pathcache display)
         *
         * @param       string          Additional GET vars
         * @return      string          script + query
         */
        function linkSelf($addParams)   {
                return htmlspecialchars('index.php?id='.$this->pObj->id.$addParams);
        }
        function renderSearchForm(){
            $content = '<input name="filter" type="text" size="30" value="'.$this->filter.'"><input type="submit" value="filter"><input type="hidden" name="id" value="'.htmlspecialchars($this->pObj->id).'" />
            <input type="hidden" name="offset" value="0" />';
            return $content;
        
        
        }
        function rederPagebrowser($row_count){
            //////////////////////////////
            // zum Bl�tern:
            //echo "offset=".$this->offset."<br>\n";
            $pagelist = '<div class="pagebrowser">';
            $pagelist .= 'Page:&nbsp;';
            $maxrows = $this->max_rows;
            //echo "Ergebnisse:$row_count<br>\n";
            //echo "Seite:&nbsp;";
            $page_count = ceil($row_count / $this->max_rows);// Aufrunden
            //debug("pagecount = ".$page_count);        
            for($i=0;$i< ( $row_count / $this->max_rows) ;$i++){
                    
                    if($this->offset  == $i){
                            $pagelist.= ($i+1)."&nbsp;";
                    }else{
                            $pagelist.= '<a href="'.$this->linkSelf('&offset='.$i.'&filter='.$this->filter).'">'.($i+1).'</a>&nbsp;';
                            //$pagelist .= $this->pi_linkTP(($i+1), array($this->prefixId=>array($conf["embed_index"]=> array($this->parent_id=> array("offset" => $i)))))." ";
                    
                    
                    }
            }
            $pagelist .= '<br />Results = '.$row_count;
            $pagelist .= '</div>';
            return $pagelist;       
        
        
        }


}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_vbulletin_connect/modfunc1/class.tx_hrvbulletinconnect_modfunc1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/hr_vbulletin_connect/modfunc1/class.tx_hrvbulletinconnect_modfunc1.php']);
}

?>