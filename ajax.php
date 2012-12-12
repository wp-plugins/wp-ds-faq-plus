<?php
/**
** March 15, 2011. Kimiya Kitani (kitani@cseas.kyoto-u.ac.jp)
** - Fixed the security bug
** - Fixed Quote trouble
** July 19, 2011. 
** - Added the sort function and the fixied the security bug.
** August 22, 2011.
** - Fixed 'id' and 'mode' elements type check for WP DS FAQ 1.3.3
** August 25, 2011.
** - Correspond the settings and user permission
** August 29, 2011.
** - Add Custom Sort item in Database table with 1.0.12
** September 7, 2011
** - Add Visible options with 1.0.13
** September 22, 2011
** - Fixed bug: $this->settings_editor_permission --> $settings_editor_permission 
** October 5, 2012 (1.0.15)
** - Added the error explanations and translation.
** - Fixed and Arranged the translation file.
**/
require_once(preg_replace('|wp-content.*$|','', __FILE__) . 'wp-config.php');

header('Content-type: text/javascript; charset='.get_settings('blog_charset'), true);
header('Cache-control: max-age=2600000, must-revalidate', true);

//function error(){ die( "alert('Что-то не заработало :(')" ); }
// 英語に直しておく By Kitani.
// 2011.07.19: 1.0.9: Detail information
// 2012.10.05: 1.0.15: Added Traslation system.
function error($str=""){
  if($str != "") 
    die( "alert('" . _e($str, 'wp-ds-faq') . "')" ); 
  else
    die( "alert('" . _e('Something did not work.','wp-ds-faq') . "')" );
}

// 1.0.4: CSRF対策（2011.04.07) By Kitani.
if (isset($_POST['dsfaq_plus_mode_csrf_ticket']) && isset($_SESSION['csrf_ticket'])){
  $p_ticket = htmlspecialchars($_POST['dsfaq_plus_mode_csrf_ticket'], ENT_QUOTES);
  $s_ticket = htmlspecialchars($_SESSION['csrf_ticket'], ENT_QUOTES);
  if($p_ticket != $s_ticket)
     die( "alert('CSRF Security Error.')" );
}

/*
[Japanese]
エスケープの解除 2011.03.15
 $flag = mysql escape, $opt = 'を削除（addcslashes対策）
 $resultsで纏めてjavascriptコードが書かれており、最後にaddcslashes()が必要（主に'のエスケープ）。しかしDBの'じゃエスケープされてしまっている。よって'が二重にエスケープされてしまう問題がある。
 解決方法は、一旦エスケープを全部解除して、改めてmysql_real_escape_stringをする。SQL命令のときはこれでいくとして、$resultsのときには、'のみエスケープを解除しとくという面倒なことが必要だ。そのための関数を下記に作っておく。

[English]
Unescape funtion on March 15, 2011
  $flag = escape for mysql DB, $opt = remove the escape for ' (single quotation).
  - Issue: $result element includes Javascript code. Therefore, ' (single quotation) needs to be escaped. However, the DB data was already escaped, so $result element is escaped over again. 
  - Solution: all elements are unescaped and are escaped by using  mysql_real_escape_string. In case of the element for Javascript ($results), ' (single quotation) is unescaped in the element. Of course, the element is escaped by using addcslashes();

*/

function k_escape($str, $flag=false, $opt=false){ 
  $str = stripcslashes($str);
  $str = stripslashes($str);
  
  if ($flag){
	$str = mysql_real_escape_string($str);
  }
  if ($opt){
	$str = str_replace("\\'","\'",$str);
  }
  
   return $str;
}

if(!isset($_POST['action'])){ 
  error("There is not action.");  // 2011.07.19: 1.0.9: add error reason
}

global $wpdb, $dsfaq;
$escape      = "\\'\\\/\x08\x0C\n\r\x09"; // Kitani: \x09 = TAB, \x08 = BackScape, \x0C = \f：http://ja.wikipedia.org/wiki/ASCII
$table_name  = $wpdb->prefix."dsfaq_name";
$table_quest = $wpdb->prefix."dsfaq_quest";

// 2011.08.25 (1.0.11) get options
$settings = get_option('wp_ds_faq_array');
			
// 2011.08.25 (1.0.11)  Refresh information of current user permission 
if( current_user_can($settings['wp_dsfaq_plus_editor_permission']) ) $settings_editor_permission = true;
else	$settings_editor_permission = false;
if( current_user_can($settings['wp_dsfaq_plus_admin_permission']) ) $settings_admin_permission = true;
else	$settings_admin_permission = false;


// 2011.03.15 By Kitani. Security
foreach($_POST as $key=>$value){
  // エスケープされたものを一旦解除し、再度エスケープ
  if(!empty($_POST[$key]))
      $_POST[$key] = k_escape($_POST[$key], true, false);
}

switch($_POST['action']) {
    case 'add_faq':
        if(!isset($_POST['input_faq']) || $_POST['input_faq'] == ""){ error("No input a FAQ name"); }
        $input_faq = $_POST['input_faq'];
	   
        // Надо будет избавиться от двух одинаковых обращений к БД. Хватит и одного.
        if(!$dsfaq->get_faq_book($input_faq, false, false)){
			// 2011.08.29 (1.0.12)
           $sql = "INSERT INTO `".$table_name."` ( `id` , `name_faq` , `mode`, `custom_mode` ) VALUES ('', '".$input_faq."', '0', '0');";
//           $sql = "INSERT INTO `".$table_name."` ( `id` , `name_faq` , `mode` ) VALUES ('', '".$input_faq."', '0');";
            $results = $wpdb->query( $sql );
            if($results){
                $results = $dsfaq->get_faq_book(k_escape($input_faq,true,true), false, false);

                $results = addcslashes($results, "\\'"); 
                die( "document.getElementById('faqbook').innerHTML += '$results';\n
                document.getElementById(\"s1\").innerHTML = '';" );
            }
        }else{
            die( "alert('".__('The FAQ with such name already exists!', 'wp-ds-faq')."');\n
            document.getElementById(\"s1\").innerHTML = '';" );
        }
        break;

    case 'delete_faqbook':
        if(!isset($_POST['id']) || $_POST['id'] == ""){ error("There is not id number"); }
//        $id = $_POST['id'];
          $id = (int) $_POST['id']; if(!is_int($id)){ break; }   // fixed by WP DS FAQ 1.3.3 (1.0.10: 2011.08.22)
      
        $quest_from_faq = $dsfaq->get_quest_from_faq($id, false, false, true);
        
        if($quest_from_faq != false){
            $count = count($quest_from_faq);
            $i = 0; $wr = '';
            foreach ($quest_from_faq as $s) {
                $i = $i + 1;
                if($i == $count){$or = "";}else{$or = ' OR ';}
                $wr .= "`id` = '".k_escape($s['id'],true,false)."'".$or;
            }
            $sql = "DELETE FROM `".$table_quest."` WHERE ".$wr;
            $results = $wpdb->query( $sql );
        }

//        $sql = "DELETE FROM `".$table_name."` WHERE `id` = ".$id;
        $sql = "DELETE FROM `".$table_name."` WHERE `id` = '".$id."'";  // SQL injection
        $results = $wpdb->query( $sql );
        if($results){ die( "idElem = document.getElementById('dsfaq_id_".$id."');\n idElem.parentNode.removeChild(idElem);"); }
        break;
    
    case 'save_quest':
        if(!isset($_POST['id']) || $_POST['id'] == ""){ error("There is not id number"); }
//        $id = $_POST['id'];
        $id = (int) $_POST['id']; if(!is_int($id)){ break; }   // fixed by WP DS FAQ 1.3.3 (1.0.10: 2011.08.22)


        if(!isset($_POST['dsfaq_quest']) || $_POST['dsfaq_quest'] == "" ){ error(); }
        $dsfaq_quest = $_POST['dsfaq_quest']; 

        if(!isset($_POST['dsfaq_answer']) || $_POST['dsfaq_answer'] == "" ){ error(); }
        $dsfaq_answer = $_POST['dsfaq_answer']; 
        
        $sql = "SELECT * FROM `".$table_quest."` WHERE `id_book` = '".$id."' ORDER BY `sort` DESC LIMIT 1";
        $results = $wpdb->get_results($sql, ARRAY_A);
        if($results){
            foreach ($results as $s) {
                (int)$sortnum = $s['sort']+1;
            }
        }else{
            (int)$sortnum = 1;
        }
        
        
        $sql = "INSERT INTO `".$table_quest."` ( `id` , `id_book` , `date` ,             `quest` ,           `answer` ,          `sort` )
                                        VALUES ( ''   , '".$id."' , '".date("Y-m-d-H-i-s")."', '".$dsfaq_quest."', '".$dsfaq_answer."', '".$sortnum."');";
        $results = $wpdb->query( $sql );
        
        if($results){
            $results = $dsfaq->get_quest_from_faq($id, false, k_escape($dsfaq_quest,true,true), false);

            $results = addcslashes($results, "\\'");
            die( "idElem = document.getElementById('dsfaq_add_q_".$id."');\n
            idElem.parentNode.removeChild(idElem);\n
            document.getElementById('dsfaq_id_".$id."').innerHTML += '".$results."';" );
        }        
        break;

    case 'delete_quest':
        if(!isset($_POST['id']) || $_POST['id'] == ""){ error(); }
//        $id = $_POST['id'];
        $id = (int) $_POST['id']; if(!is_int($id)){ break; }   // fixed by WP DS FAQ 1.3.3 (1.0.10: 2011.08.22)

        if(isset($_POST['front'])){
//            (int)$front = $_POST['front'];
            $front = (int) $_POST['front']; // fixed by WP DS FAQ 1.3.3 (1.0.10: 2011.08.22)

        }

//        $sql = "DELETE FROM `".$table_quest."` WHERE `id` = ".$id;
        $sql = "DELETE FROM `".$table_quest."` WHERE `id` = '".$id."'";
        $results = $wpdb->query( $sql );
        
//        if(isset($front) and $front == 1){
        if(isset($front) and is_int($front) and $front == 1){ // fixed by WP DS FAQ 1.3.3 (1.0.10: 2011.08.22)
            $die = "if(document.getElementById('dsfaq_qa_block_".$id."')){
                        dsfaq_front_bg_color('dsfaq_qa_block_".$id."', function (){
                            idElem = document.getElementById('dsfaq_qa_block_".$id."');
                            idElem.parentNode.removeChild(idElem);
                            idElem = document.getElementById('dsfaq_li_".$id."');
                            idElem.parentNode.removeChild(idElem); })
                    }else{
                        dsfaq_front_bg_color('dsfaq_answer_".$id."', function (){ 
                            idElem = document.getElementById('dsfaq_quest_".$id."');
                            idElem.parentNode.removeChild(idElem);
                            idElem = document.getElementById('dsfaq_tools_".$id."');
                            idElem.parentNode.removeChild(idElem);
                            idElem = document.getElementById('dsfaq_answer_".$id."');
                            idElem.parentNode.removeChild(idElem); })
                    }";
        }else{
            $die = "idElem = document.getElementById('dsfaq_idquest_".$id."');
                    idElem.parentNode.removeChild(idElem);";
        }

        if($results){ die($die); }
        break;
        
    case 'edit_quest':
        if(!isset($_POST['id']) || $_POST['id'] == ""){ error(); }
//        (int)$id = $_POST['id'];
        $id = (int) $_POST['id']; if(!is_int($id)){ break; }   // fixed by WP DS FAQ 1.3.3 (1.0.10: 2011.08.22)
       
        $sql = "SELECT * FROM `".$table_quest."` WHERE `id` = '".$id."'";
        $select = $wpdb->get_results($sql, ARRAY_A);
        
        $results = '';
        foreach ($select as $s) {
            $results .= '<div id="dsfaq_idquest_edit_'.$id.'" class="dsfaq_idquest_edit">';
            $results .= '<br>';
            $results .= '<p>'.__('Question:', 'wp-ds-faq').'</p>';
            $results .= '<input id="dsfaq_quest" type="text" value="'.str_replace('"', '&quot;', k_escape($s['quest'],false,true)).'" />';
            $results .= '<p>'.__('Answer:', 'wp-ds-faq').'</p>';
            $results .= '<textarea id="dsfaq_answer" rows="10" cols="45" name="text">'.k_escape($s['answer'],false,true).'</textarea><br>';
            $results .= '<p class="dsfaq_drv">';
            $results .= '<a href="#_" onclick="this.innerHTML=\'<img src='.$dsfaq->plugurl.'img/ajax-loader.gif>\'; update_quest('.k_escape($s['id'],false,true).', '.$s['id_book'].');"><span class="button">'.__('Save', 'wp-ds-faq').'</span></a>';
            $results .= ' &nbsp; ';
            $results .= '<a href="#_" onclick="cancel_edit(\''.$id.'\', \'dsfaq_idquest_edit_'.$id.'\');" class="button">'.__('Cancel', 'wp-ds-faq').'</a>';
            $results .= '</p>';
            $results .= '</div>';
        }
        
        $results = addcslashes($results, $escape);
        die( "document.getElementById('dsfaq_edit_link_".$id."').innerHTML = '';\n
        document.getElementById('dsfaq_idquest_".$id."').style.backgroundColor = '#fdfdef';\n
        document.getElementById('dsfaq_idquest_".$id."').innerHTML += '".$results."';" );
        break;
        
    case 'front_edit_quest':
        if(!isset($_POST['id']) || $_POST['id'] == "" ){ error(); }
//        (int)$id = $_POST['id'];
        $id = (int) $_POST['id']; if(!is_int($id)){ break; }   // fixed by WP DS FAQ 1.3.3 (1.0.10: 2011.08.22)
      
        $sql = "SELECT * FROM `".$table_quest."` WHERE `id` = '".$id."'";
        $select = $wpdb->get_results($sql, ARRAY_A);

        foreach ($select as $s) {          
            $front_input_quest = '<input style="width: 100%;" id="dsfaq_inp_quest_'.$id.'" type="text" value="'.str_replace('"', '&quot;', k_escape($s['quest'],false,true)).'" />';
            $front_textarea_answer = '<textarea style="width: 100%;" id="dsfaq_txt_answer_'.$id.'" rows="10" cols="45">'.k_escape($s['answer'],false,true).'</textarea>';
            $front_tools  = '[ <a href="#_" onclick="this.innerHTML=\'<img src='.$dsfaq->plugurl.'img/ajax-loader.gif>\'; dsfaq_front_update_quest('.k_escape($s['id'],false,true).');">'.__('Save', 'wp-ds-faq').'</a> ]';
            $front_tools .= '[ <a href="#_" onclick="dsfaq_front_cancel_edit(\''.$id.'\');">'.__('Cancel', 'wp-ds-faq').'</a> ]';
        }
        
        $front_input_quest     = addcslashes($front_input_quest, $escape);
        $front_textarea_answer = addcslashes($front_textarea_answer, $escape);
        $front_tools           = addcslashes($front_tools, $escape);
      
        die( "document.getElementById('dsfaq_quest_".$id."').innerHTML = '".$front_input_quest."';\n
        document.getElementById('dsfaq_answer_".$id."').innerHTML = '".$front_textarea_answer."';\n
        document.getElementById('dsfaq_tools_".$id."').innerHTML = '".$front_tools."';" );
        break;
        
    case 'front_cancel_edit':
        if(!isset($_POST['id']) || $_POST['id'] == ""){ error(); }
//        (int)$id = $_POST['id'];
        $id = (int) $_POST['id']; if(!is_int($id)){ break; }   // fixed by WP DS FAQ 1.3.3 (1.0.10: 2011.08.22)
      
        $sql = "SELECT * FROM `".$table_quest."` WHERE `id` = '".$id."'";
        $select = $wpdb->get_results($sql, ARRAY_A);
        
        $results = '';
        foreach ($select as $s) {
            $front_input_quest = k_escape($s['quest'],false,true);
            $front_textarea_answer = '<div class="dsfaq_answer">'.k_escape($s['answer'],false,true);
			// 2011.08.25 (1.0.11) Limitation by settings and current user permission
//            if(current_user_can('level_10')){
			if($settings_editor_permission){
                $front_tools  = '[ <a href="#_" onclick="dsfaq_front_edit_quest('.k_escape($s['id'],false,true).');">'.__('Edit', 'wp-ds-faq').'</a> ]';
//	2011.08.22: 1.0.10: ログイン時のFAQ表示ページにおいて、「編集」→「キャンセル」を押した後に「削除」になる問題を修正（管理画面以外での削除を禁止するという1.0.3のポリシーに準拠）

				// 2011.08.25 (1.0.11) Limitation by settings and current user permission
				if(! $settings['wp_dsfaq_plus_disable_all_delete'] && ! $settings['wp_dsfaq_plus_disable_frontedit_delete']){	
             	   $front_tools .= '[ <a href="#_" onclick="this.innerHTML=\'<img src='.$dsfaq->plugurl.'img/ajax-loader.gif>\'; dsfaq_front_delete_quest('.k_escape($s['id'],false,true).');">'.__('Delete&nbsp;question', 'wp-ds-faq').'</a> ]';             	   
          		}else if($settings_admin_permissions && !$settings['wp_dsfaq_plus_apply_safetyoptions_to_admin']){
             	   $front_tools .= '[ <a href="#_" onclick="this.innerHTML=\'<img src='.$dsfaq->plugurl.'img/ajax-loader.gif>\'; dsfaq_front_delete_quest('.k_escape($s['id'],false,true).');">'.__('Delete&nbsp;question', 'wp-ds-faq').'</a> ]';             	   
				}          		
          	}
        }
        $front_input_quest     = addcslashes($front_input_quest, $escape);
        $front_textarea_answer = addcslashes($front_textarea_answer, $escape);
        $front_tools           = addcslashes($front_tools, $escape);
        die( "if(document.getElementById('dsfaq_qa_block_".$id."')){
                  document.getElementById('dsfaq_quest_".$id."').innerHTML = '".$front_input_quest."';\n
                  document.getElementById('dsfaq_answer_".$id."').innerHTML = '".$front_textarea_answer."</div>';\n
              }else{
                  document.getElementById('dsfaq_quest_".$id."').innerHTML = '<a href=\"#_\" onclick=\"dsfaq_open_quest(".$id.");\">".$front_input_quest."</a>';\n
                  document.getElementById('dsfaq_answer_".$id."').innerHTML = '".$front_textarea_answer."<br><span class=\"dsfaq_tools\">[&nbsp;<a href=\"#_\" onclick=\"dsfaq_close_quest(".$id.");\">".__('Close', 'wp-ds-faq')."</a>&nbsp;]</span></div>';\n
              }
        document.getElementById('dsfaq_tools_".$id."').innerHTML = '".$front_tools."';" );
        break;

    case 'update_quest':
        if(!isset($_POST['id']) || $_POST['id'] == ""){ error(); }
//        $id = $_POST['id'];
        $id = (int) $_POST['id']; if(!is_int($id)){ break; }   // fixed by WP DS FAQ 1.3.3 (1.0.10: 2011.08.22)
        
        if(!isset($_POST['id_book']) || $_POST['id_book'] == ""){ error(); }
//        $id_book = $_POST['id_book'];
        $id_book = (int) $_POST['id_book']; if(!is_int($id_book)){ break; }  // fixed by WP DS FAQ 1.3.3 (1.0.10: 2011.08.22)
        
        if(!isset($_POST['dsfaq_quest']) || $_POST['dsfaq_quest'] == ""){ error(); }
        $dsfaq_quest = $_POST['dsfaq_quest'];

        if(!isset($_POST['dsfaq_answer']) || $_POST['dsfaq_answer'] == ""){ error(); }
        $dsfaq_answer = $_POST['dsfaq_answer'];
        
        $sql = "UPDATE ".$table_quest." SET date='".date("Y-m-d-H-i-s")."', quest='".$dsfaq_quest."', answer='".$dsfaq_answer."' WHERE id='".$id."'";
        $results = $wpdb->query( $sql );

        if($results){
            $res = $dsfaq->get_quest_from_faq(k_escape($id_book,true,true), $id, false, true);

            $results = '';
            
           foreach ($res as $s) {
                $results .= '<table border="0" width="690"><tr><td width="12">';
                $results .= '<a href="#_" onclick="dsfaq_q_change(\'up\', \''.k_escape($s['id_book'],false,true).'\', \''.k_escape($s['id'],false,true).'\');"><img src="'.$dsfaq->plugurl.'img/up.gif" width="8" height="8"></a>';
                $results .= '<br><img src="'.$dsfaq->plugurl.'img/1x1.gif" width="1" height="6"><br>';
                $results .= '<a href="#_" onclick="dsfaq_q_change(\'down\', \''.k_escape($s['id_book'],false,true).'\', \''.k_escape($s['id'],false,true).'\');"><img src="'.$dsfaq->plugurl.'img/down.gif" width="8" height="8"></a>';
                $results .= '</td>';
                $results .= '<td>';
                $results .= k_escape($s['quest'],false,true);
                $results .= '</td><td width="120" align="center" id="dsfaq_edit_link_'.k_escape($s['id'],false,true).'">';
                $results .= '<a href="#_" onclick="this.innerHTML=\'<img src='.$dsfaq->plugurl.'img/ajax-loader.gif>\'; edit_quest('.k_escape($s['id'],false,true).');"><span class="button">'.__('Edit', 'wp-ds-faq').'</span></a>';
                $results .= '</td><td width="120" align="center">';
                $results .= '<a href="#_" onclick="this.innerHTML=\'<img src='.$dsfaq->plugurl.'img/ajax-loader.gif>\'; delete_quest('.k_escape($s['id'],false,true).');"><span class="button">'.__('Delete&nbsp;question', 'wp-ds-faq').'</span></a>';
                $results .= '</td></tr></table>';
            }

            $results = addcslashes($results, "\\'");
            die( "idElem = document.getElementById('dsfaq_idquest_edit_".$id."');\n
            idElem.parentNode.removeChild(idElem);\n
            document.getElementById('dsfaq_idquest_".$id."').style.backgroundColor = '#FFFFFF';\n
            document.getElementById('dsfaq_idquest_".$id."').innerHTML = '".$results."';" );
        }        
        
        break;
        
    case 'front_update_quest':
        if(!isset($_POST['id']) || $_POST['id'] == ""){ error(); }
//        $id = $_POST['id'];
        $id = (int) $_POST['id']; if(!is_int($id)){ break; }   // fixed by WP DS FAQ 1.3.3 (1.0.10: 2011.08.22)
      
        if(!isset($_POST['dsfaq_quest']) || $_POST['dsfaq_quest'] == ""){ error(); }
        $dsfaq_quest = $_POST['dsfaq_quest'];

        if(!isset($_POST['dsfaq_answer']) || $_POST['dsfaq_answer'] == "") error();
        $dsfaq_answer = $_POST['dsfaq_answer'];
        
        $sql = "UPDATE ".$table_quest." SET date='".date("Y-m-d-H-i-s")."', quest='".$dsfaq_quest."', answer='".$dsfaq_answer."' WHERE id='".$id."'";
        $results = $wpdb->query( $sql );
        
        if($results){
            $sql = "SELECT * FROM `".$table_quest."` WHERE `id` = '".$id."'";
            $select = $wpdb->get_results($sql, ARRAY_A);
            
            $results = '';
            foreach ($select as $s) {
                $front_input_quest = k_escape($s['quest'],false,true);
                $front_textarea_answer = '<div class="dsfaq_answer">'.k_escape($s['answer'],false,true);
			// 2011.08.25 (1.0.11) Limitation by settings and current user permission
//            if(current_user_can('level_10')){
				if($settings_editor_permission){
                    $front_tools  = '[ <a href="#_" onclick="dsfaq_front_edit_quest('.k_escape($s['id'],false,true).');">'.__('Edit', 'wp-ds-faq').'</a> ]';
				// 2011.08.25 (1.0.11) Limitation by settings and current user permission
					if(! $settings['wp_dsfaq_plus_disable_all_delete'] && ! $settings['wp_dsfaq_plus_disable_frontedit_delete']){
                   		$front_tools .= '[ <a href="#_" onclick="this.innerHTML=\'<img src='.$dsfaq->plugurl.'img/ajax-loader.gif>\'; dsfaq_front_delete_quest('.k_escape($s['id'],false,true).');">'.__('Delete&nbsp;question', 'wp-ds-faq').'</a> ]';
               		}else if($settings_admin_permission && ! $settings['wp_dsfaq_plus_apply_safetyoptions_to_admin']){
                   		$front_tools .= '[ <a href="#_" onclick="this.innerHTML=\'<img src='.$dsfaq->plugurl.'img/ajax-loader.gif>\'; dsfaq_front_delete_quest('.k_escape($s['id'],false,true).');">'.__('Delete&nbsp;question', 'wp-ds-faq').'</a> ]';
               		}
               	}
            }
        
            $front_input_quest     = addcslashes($front_input_quest, $escape);
            $front_textarea_answer = addcslashes($front_textarea_answer, $escape);
            $front_tools           = addcslashes($front_tools, $escape);
            die( "if(document.getElementById('dsfaq_qa_block_".$id."')){
                  document.getElementById('dsfaq_quest_".$id."').innerHTML = '".$front_input_quest."';\n
                  document.getElementById('dsfaq_answer_".$id."').innerHTML = '".$front_textarea_answer."</div>';\n
                  document.getElementById('dsfaq_li_".$id."').innerHTML = '<a href=\"#".$id."\">".$front_input_quest."</a>';
           }else{
                  document.getElementById('dsfaq_quest_".$id."').innerHTML = '<a href=\"#_\" onclick=\"dsfaq_open_quest(".$id.");\">".$front_input_quest."</a>';\n
                  document.getElementById('dsfaq_answer_".$id."').innerHTML = '".$front_textarea_answer."<br><span class=\"dsfaq_tools\">[&nbsp;<a href=\"#_\" onclick=\"dsfaq_close_quest(".$id.");\">".__('Close', 'wp-ds-faq')."</a>&nbsp;]</span></div>';\n
              }
            document.getElementById('dsfaq_tools_".$id."').innerHTML = '".$front_tools."';" );
        }        
        
        break;
    
    case 'q_change':
        if(!isset($_POST['to']) || $_POST['to'] == ""){ error(); }
        $to = $_POST['to'];
        if(!isset($_POST['id_book']) || $_POST['id_book'] == ""){ error(); }
//      $id_book = $_POST['id_book'];
        $id_book = (int) $_POST['id_book']; if(!is_int($id_book)){ break; }  // fixed by WP DS FAQ 1.3.3 (1.0.10: 2011.08.22)
        if(!isset($_POST['id']) || $_POST['id'] == ""){ error(); }
//        $id = $_POST['id'];
        $id = (int) $_POST['id']; if(!is_int($id)){ break; }   // fixed by WP DS FAQ 1.3.3 (1.0.10: 2011.08.22)
        
        $sql = "SELECT `sort` FROM `".$table_quest."` WHERE `id` = '".$id."'";
        $select = $wpdb->get_results($sql, ARRAY_A);
        $sort = $select['0']['sort'];
        
        if($to == "up"){  $sql = "SELECT * FROM `".$table_quest."` WHERE `id_book` = '".$id_book."' AND `sort` < ".$sort." ORDER BY `sort` DESC LIMIT 1";}
        if($to == "down"){$sql = "SELECT * FROM `".$table_quest."` WHERE `id_book` = '".$id_book."' AND `sort` > ".$sort." ORDER BY `sort` ASC  LIMIT 1";}
        
        $results = $wpdb->get_results($sql, ARRAY_A);
        
        if($results){
            $q_id_curent   = $id;
            $q_sort_curent = $sort;
            $q_id_change   = $results['0']['id'];
            $q_sort_change = $results['0']['sort'];
            
            $sql = "UPDATE ".$table_quest." SET sort='".$q_sort_change."' WHERE id='".$q_id_curent."'";
            $results = $wpdb->query( $sql );
            $sql = "UPDATE ".$table_quest." SET sort='".$q_sort_curent."' WHERE id='".$q_id_change."'";
            $results = $wpdb->query( $sql );
            
            if($to == 'up'){
                die( "da = document.getElementById('dsfaq_idquest_".$q_id_curent."');\n
                db = document.getElementById('dsfaq_idquest_".$q_id_change."');\n
                da.parentNode.insertBefore(da, db);\n
                dsfaq_bg_color ('dsfaq_idquest_".$q_id_curent."', 'dsfaq_idquest_".$q_id_change."');" );
            }
            if($to == 'down'){
                die( "da = document.getElementById('dsfaq_idquest_".$q_id_curent."');\n
                db = document.getElementById('dsfaq_idquest_".$q_id_change."');\n
                db.parentNode.insertBefore(db, da);\n
                dsfaq_bg_color ('dsfaq_idquest_".$q_id_curent."', 'dsfaq_idquest_".$q_id_change."');" );
            }
            
        }else{
            die("dsfaq_nahStep('dsfaq_idquest_".$id."');");
        }
        break;
        
    case 'save_settings':
        if(!isset($_POST['dsfaq_h1']) || $_POST['dsfaq_h1'] == ""){ error(); }
        $dsfaq_h1 = $_POST['dsfaq_h1'];
        
        if(!isset($_POST['dsfaq_h2']) || $_POST['dsfaq_h2'] == ""){ error(); }
        $dsfaq_h2 = $_POST['dsfaq_h2']; 
        
        if(!isset($_POST['dsfaq_css']) || $_POST['dsfaq_css'] == ""){ error(); }
        $dsfaq_css = $_POST['dsfaq_css'];
        
        if(!isset($_POST['dsfaq_copyr']) || $_POST['dsfaq_copyr'] == ""){ error(); }
        $dsfaq_copyr = $_POST['dsfaq_copyr'];
        
        if($dsfaq_copyr == 'true'){
            $wp_ds_faq_array['wp_ds_faq_showcopyright'] = true;
        }else{
            $wp_ds_faq_array['wp_ds_faq_showcopyright'] = false;
        }
        $wp_ds_faq_array['wp_ds_faq_h1']  = $dsfaq_h1;
        $wp_ds_faq_array['wp_ds_faq_h2']  = $dsfaq_h2;

        $wp_ds_faq_array['wp_ds_faq_css'] = $dsfaq_css;
        
        update_option('wp_ds_faq_array', $wp_ds_faq_array);

        die("document.getElementById('dsfaq_progress').innerHTML = '<span style=\'color:green;\'>".__('Settings&nbsp;have&nbsp;been&nbsp;saved:', 'wp-ds-faq')."&nbsp;".date("Y-m-d H:i:s")."</span>';");
        
        break;
    
    case 'edit_name_book':
        if(!isset($_POST['id']) || $_POST['id'] == ""){ error(); }
//        $id = $_POST['id'];
        $id = (int) $_POST['id']; if(!is_int($id)){ break; }   // fixed by WP DS FAQ 1.3.3 (1.0.10: 2011.08.22)
        
        $select = $dsfaq->get_faq_book(false, $id, true);
        
        $name_faq = $select[0]['name_faq'];
        $name_faq = addcslashes($name_faq, $escape);
        $name_faq = str_replace('"', '&quot;', $name_faq);
	            
        die("document.getElementById('dsfaq_namebook_".$id."').innerHTML = '<input style=\"width:415px;\" id=\"dsfaq_input_bookname_".$id."\" value=\"".$name_faq."\"/>';\n
        document.getElementById('dsfaq_toolnamebook_".$id."').innerHTML = '<a href=\"#_\" onclick=\"this.innerHTML=\'<img src=".$dsfaq->plugurl."img/ajax-loader.gif>\'; dsfaq_save_name_book(".$id.");\"><span class=\"button\">".__('Save', 'wp-ds-faq')."</span></a>';");
        
        break;
        
    case 'save_name_book':
        if(!isset($_POST['id']) || $_POST['id'] == ""){ error(); }
//        $id = $_POST['id'];
        $id = (int) $_POST['id']; if(!is_int($id)){ break; }   // fixed by WP DS FAQ 1.3.3 (1.0.10: 2011.08.22)

        if(!isset($_POST['name_book']) || $_POST['name_book'] == ""){ error(); }
        $name_book = $_POST['name_book'];
        
        $sql = "UPDATE ".$table_name." SET name_faq='".$name_book."' WHERE id='".$id."'";
        $results = $wpdb->query( $sql );
        
        die("document.getElementById('dsfaq_namebook_".$id."').innerHTML = '<span class=\"dsfaq_title\">".k_escape($name_book,false,true)."</span>';\n
        document.getElementById('dsfaq_toolnamebook_".$id."').innerHTML = '<a href=\"#_\" onclick=\"this.innerHTML=\'<img src=".$dsfaq->plugurl."img/ajax-loader.gif>\'; dsfaq_edit_name_book(".$id.");\"><span class=\"button\">".__('Change&nbsp;title', 'wp-ds-faq')."</span></a>';");
        
        break;
        
    case 'change_faqdisplay':
        if(!isset($_POST['id']) || $_POST['id'] == ""){ error(); }
//        $id = $_POST['id'];
        $id = (int) $_POST['id']; if(!is_int($id)){ break; }   // fixed by WP DS FAQ 1.3.3 (1.0.12: 2011.08.29)

        if(!isset($_POST['mode']) || $_POST['mode'] == ""){ error(); }
//        $mode = $_POST['mode'];
        $mode = (int) $_POST['mode']; if(!is_int($mode)){ break; }   // fixed by WP DS FAQ 1.3.3 (1.0.10: 2011.08.22)

        // 2011.07.19: 1.0.9 for security
        if(!is_numeric($mode) || $mode < 0) $mode = 0;      

		// 2011.07.19: 1.0.9: modeをDBから取得
        $sql = "SELECT * FROM ".$table_name." WHERE  id = '".$id."'";
        $result = $wpdb->get_row($sql);
        if(isset($result->custom_mode)) // 一つしか人しないはず。一つ目からmodeを取り出す
            $set_mode = $result->custom_mode;
        else $set_mode = 0;
        if(!is_numeric($set_mode) || $set_mode < 0) $set_mode = 0;
        unset($result); // 取得したDBデータの破棄
             
        // 2011.07.19: 1.0.9: DBのmodeからsort, orderを分離   
	    $sort = (($set_mode - ($set_mode % 10)) / 10) % 10; 
	    if($set_mode >= 100) // 2011.08.29 (1.0.12) fix
	       $order = (( $set_mode - ($sort * 10) - ($set_mode % 10) ) / 100 ) % 100;
	    else $order = 0;
              
        $mode_with_sort = $mode + $sort*10 + $order*100;

		// 2011.08.29 (1.0.12)        
        $sql = "UPDATE ".$table_name." SET mode='".$mode."', custom_mode='".$mode_with_sort."' WHERE id='".$id."'";
//        $sql = "UPDATE ".$table_name." SET mode='".$mode_with_sort."' WHERE id='".$id."'";
        $results = $wpdb->query( $sql );

        k_escape($id, false, true);
        k_escape($mode, false, true);

        if($results){
            $results = '';
            $results .= '<input type="radio" name="dsfaq_mode_'.$id.'" onclick="dsfaq_change_faqdisplay(\''.$s['id'].'\', \'0\');" '.(($mode == 0)?"checked":"").'> '.(($mode == 0)?"<b>":"").__('deployed', 'wp-ds-faq').(($mode == 0)?"</b>":"");
            $results .= ' &nbsp; ';
            $results .= '<input type="radio" name="dsfaq_mode_'.$id.'" onclick="dsfaq_change_faqdisplay(\''.$s['id'].'\', \'1\');" '.(($mode == 1)?"checked":"").'> '.(($mode == 1)?"<b>":"").__('minimized', 'wp-ds-faq').(($mode == 1)?"</b>":"");
        }
        
        $results = addcslashes($results, $escape);
        die("document.getElementById('dsfaq_display_mode_".$id."').innerHTML = '".$results."';");
        break;
        
    case 'change_faqdisplaysort':
// 2011.07.19: 1.0.9 for Sort

        if(!isset($_POST['id']) || $_POST['id'] == ""){ error("There is not id number"); }
//        $id = $_POST['id'];
        $id = (int) $_POST['id']; if(!is_int($id)){ break; }   // fixed by WP DS FAQ 1.3.3 (1.0.10: 2011.08.22)

        if(!isset($_POST['sort']) || $_POST['sort'] == ""){ error("Not setting of sort"); }
        $sort = $_POST['sort'];

        // 2011.07.19: 1.0.9 for security
        if(!is_numeric($sort) || $sort < 0) $sort = 0;      

		// 2011.07.19: 1.0.9: modeをDBから取得
        $sql = "SELECT * FROM ".$table_name." WHERE  id = '".$id."'";
        $result = $wpdb->get_row($sql);
        if(isset($result->custom_mode)) // 一つしか人しないはず。一つ目からmodeを取り出す
            $set_mode = $result->custom_mode;
        else $set_mode = 0;
        if(!is_numeric($set_mode) || $set_mode < 0) $set_mode = 0;
        unset($result); // 取得したDBデータの破棄

        // 2011.07.19: 1.0.9: DBのmodeからmode, orderを分離   
	    $mode = $set_mode % 10;
	    $set_sort = (($set_mode - ($set_mode % 10)) / 10) % 10; 
	    if($set_mode >= 100) // 2011.08.29 (1.0.12) fix
	       $order = (( $set_mode - ($set_sort * 10) - $mode ) / 100 ) % 100;
	    else $order = 0;
    
        $mode_with_sort = $mode + $sort*10 + $order*100;
 
 		// 2011.08.29 (1.0.12)        
       $sql = "UPDATE ".$table_name." SET mode='".$mode."', custom_mode='".$mode_with_sort."' WHERE id='".$id."'";
 //       $sql = "UPDATE ".$table_name." SET mode='".$mode_with_sort."' WHERE id='".$id."'";
        $results = $wpdb->query( $sql );

        k_escape($id, false, true);
        k_escape($sort, false, true);
        
        if($results){
            $results = '';
            $results .= '<input type="radio" name="dsfaq_sort_'.$id.'" onclick="dsfaq_change_faqdisplaysort(\''.$s['id'].'\', \'0\');" '.(($sort == 0)?"checked":"").'> '.(($sort == 0)?"<b>":"").__('Custom', 'wp-ds-faq').(($sort == 0)?"</b>":"");
            $results .= ' &nbsp; ';
            $results .= '<input type="radio" name="dsfaq_sort_'.$id.'" onclick="dsfaq_change_faqdisplaysort(\''.$s['id'].'\', \'1\');" '.(($sort == 1)?"checked":"").'> '.(($sort == 1)?"<b>":"").__('Last modified', 'wp-ds-faq').(($sort == 1)?"</b>":"");
            $results .= ' &nbsp; ';
            $results .= '<input type="radio" name="dsfaq_sort_'.$id.'" onclick="dsfaq_change_faqdisplaysort(\''.$s['id'].'\', \'2\');" '.(($sort == 2)?"checked":"").'> '.(($sort == 2)?"<b>":"").__('Answer Name', 'wp-ds-faq').(($sort == 2)?"</b>":"");

        }
        
        $results = addcslashes($results, $escape);
        die("document.getElementById('dsfaq_display_sort_".$id."').innerHTML = '".$results."';");
        break;

    case 'change_faqdisplayorder':
// 2011.07.19: 1.0.9 for Sort2

        if(!isset($_POST['id']) || $_POST['id'] == ""){ error("There is not id number"); }
//        $id = $_POST['id'];
        $id = (int) $_POST['id']; if(!is_int($id)){ break; }   // fixed by WP DS FAQ 1.3.3 (1.0.10: 2011.08.22)

        if(!isset($_POST['order']) || $_POST['order'] == ""){ error("Not setting of order"); }
        $order = $_POST['order'];

        // 2011.07.19: 1.0.9 for security
        if(!is_numeric($order) || $order < 0) $order = 0;      

		// 2011.07.19: 1.0.9: modeをDBから取得
        $sql = "SELECT * FROM ".$table_name." WHERE  id = '".$id."'";
        $result = $wpdb->get_row($sql);
        if(isset($result->custom_mode)) // 一つしか人しないはず。一つ目からmodeを取り出す
            $set_mode = $result->custom_mode;		
        else $set_mode = 0;
        if(!is_numeric($set_mode) || $set_mode < 0) $set_mode = 0;
        unset($result); // 取得したDBデータの破棄
     
        // 2011.07.19: 1.0.9: DBのmodeからmode, orderを分離   
	    $mode = $set_mode % 10;
	    $sort = (($set_mode - $mode) / 10) % 10; 
      
        $mode_with_sort = $mode + $sort*10 + $order*100;
		
		// 2011.0.29 (1.0.12) 
        $sql = "UPDATE ".$table_name." SET mode='".$mode."', custom_mode='".$mode_with_sort."' WHERE id='".$id."'";
//        $sql = "UPDATE ".$table_name." SET mode='".$mode_with_sort."' WHERE id='".$id."'";
        $results = $wpdb->query( $sql );

        k_escape($id, false, true);
        k_escape($sort, false, true);
        
        if($results){
            $results = '';
            $results .= '<input type="radio" name="dsfaq_order_'.$id.'" onclick="dsfaq_change_faqdisplayorder(\''.$s['id'].'\', \'0\');" '.(($order == 0)?"checked":"").'> '.(($order == 0)?"<b>":"").__('Ascending', 'wp-ds-faq').(($order == 0)?"</b>":"");
            $results .= ' &nbsp; ';
            $results .= '<input type="radio" name="dsfaq_order_'.$id.'" onclick="dsfaq_change_faqdisplayorder(\''.$s['id'].'\', \'1\');" '.(($order == 1)?"checked":"").'> '.(($order == 1)?"<b>":"").__('Descending', 'wp-ds-faq').(($order == 1)?"</b>":"");
        }
                
        $results = addcslashes($results, $escape);
        die("document.getElementById('dsfaq_display_order_".$id."').innerHTML = '".$results."';");
        break;

    case 'dsfaq_faqdisplay_visible':
// 2011.09.07: 1.0.13 for visible

        if(!isset($_POST['id']) || $_POST['id'] == ""){ error("There is not id number"); }
        $id = (int) $_POST['id']; if(!is_int($id)){ break; }   

        if(!isset($_POST['visible']) || $_POST['visible'] == ""){ error("Not setting of visible"); }
        $visible = (int) $_POST['visible']; 
        if($visible != 0) $visible = 1;

        $sql = "UPDATE ".$table_name." SET visible='".$visible."' WHERE id='".$id."'";
        $results = $wpdb->query( $sql );

        if($results){
            $results = '';
            $results .= '<input type="radio" name="dsfaq_faqdisplay_visible_'.$id.'" onclick="dsfaq_faqdisplay_visible(\''.$s['id'].'\', \'1\');" '.(($visible == 1)?"checked":"").'> '.(($visible == 1)?"<b>":"").__('Publish', 'wp-ds-faq').(($visible == 1)?"</b>":"");
            $results .= ' &nbsp; ';
            $results .= '<input type="radio" name="dsfaq_faqdisplay_visible_'.$id.'" onclick="dsfaq_faqdisplay_visible(\''.$s['id'].'\', \'0\');" '.(($visible == 0)?"checked":"").'> '.(($visible == 0)?"<b>":"").__('Not publish', 'wp-ds-faq').(($visible == 0)?"</b>":"");
        }
                
        $results = addcslashes($results, $escape);
        die("document.getElementById('dsfaq_faqdisplay_visible_".$id."').innerHTML = '".$results."';");
        break;


    case 'open_quest':
        if(!isset($_POST['id']) || $_POST['id'] == ""){ error(); }
//        $id = $_POST['id'];
        $id = (int) $_POST['id']; if(!is_int($id)){ break; }   // fixed by WP DS FAQ 1.3.3 (1.0.10: 2011.08.22)

        $sql = "SELECT `answer` FROM `".$table_quest."` WHERE `id` = '".$id."'";
        $select = $wpdb->get_results($sql, ARRAY_A);
        $answer = k_escape($select[0]['answer'],false, true);
        
        $results = '';
        $results .= '<div class="dsfaq_answer">';
        $results .= apply_filters('the_content', $answer, 'dsfaq_filters');
        $results .= '<br><span class="dsfaq_tools">[&nbsp;<a href="#_" onclick="dsfaq_close_quest('.$id.');">'.__('Close', 'wp-ds-faq').'</a>&nbsp;]</span>';
        $results .= '</div>';
        
			// 2011.08.25 (1.0.11) Limitation by settings and current user permission
//            if(current_user_can('level_10')){
		if($settings_editor_permission){
         	  $tools .= '[ <a href="#_" onclick="dsfaq_front_edit_quest('.$id.');">'.__('Edit', 'wp-ds-faq').'</a> ]';

			  // 2011.08.25 (1.0.11) Limitation by settings and current user permission
			  if(! $settings['wp_dsfaq_plus_disable_all_delete'] && ! $settings['wp_dsfaq_plus_disable_frontedit_delete']){
	           		 $tools .= '[ <a href="#_" onclick="this.innerHTML=\'<img src='.$dsfaq->plugurl.'img/ajax-loader.gif>\'; dsfaq_front_delete_quest('.$id.');">'.__('Delete&nbsp;question', 'wp-ds-faq').'</a> ]';
	          }else if($settings_admin_permission && ! $settings['wp_dsfaq_plus_apply_safetyoptions_to_admin']){
	           		 $tools .= '[ <a href="#_" onclick="this.innerHTML=\'<img src='.$dsfaq->plugurl.'img/ajax-loader.gif>\'; dsfaq_front_delete_quest('.$id.');">'.__('Delete&nbsp;question', 'wp-ds-faq').'</a> ]';
	          }
        }
        
        $results = addcslashes($results, $escape);
        $tools   = addcslashes($tools, $escape);
        $results = str_replace('\"','', $results);
        $results = str_replace('\'','', $results);
		
        die("document.getElementById('dsfaq_answer_".$id."').innerHTML = '".$results."';\n
        document.getElementById('dsfaq_tools_".$id."').innerHTML = '".$tools."';");
        
        break;
        
    case 'restore_settings':
        update_option('wp_ds_faq_array', $dsfaq->wp_ds_faq_default_array);
        
        die("document.getElementById('dsfaq_h1').value = '".addcslashes($dsfaq->wp_ds_faq_default_array['wp_ds_faq_h1'], $escape)."';\n
        document.getElementById('dsfaq_h2').value = '".addcslashes($dsfaq->wp_ds_faq_default_array['wp_ds_faq_h2'], $escape)."';\n
        document.getElementById('dsfaq_css').value = '".addcslashes($dsfaq->wp_ds_faq_default_array['wp_ds_faq_css'], $escape)."';\n
        document.getElementById('dsfaq_copyr').checked = 'true';\n
        document.getElementById('dsfaq_progress').innerHTML = '<span style=\'color:green;\'>".__('Settings&nbsp;have&nbsp;been&nbsp;restore:', 'wp-ds-faq')."&nbsp;".date("Y-m-d H:i:s")."</span>';");
        break;

    default:
        error();
        break;
}

die( "document.getElementById(\"s1\").innerHTML = '<span style=\"color:red;\">[Error id: 1000]</span>';" );

?>