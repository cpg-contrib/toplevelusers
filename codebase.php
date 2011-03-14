<?php
/*************************
  Coppermine Photo Gallery
  ************************
  Copyright (c) 2003-2006 Coppermine Dev Team
  v1.1 originally written by Gregory DEMAR

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.
  ********************************************
  Coppermine version: 1.4.11
  $Source: /cvsroot/cpg-contrib/toplevelusers/codebase.php,v $
  $Revision: 1.1 $
  $Author: donnoman $
  $Date: 2006/11/29 05:19:49 $
**********************************************/

if (!defined('IN_COPPERMINE')) die('Not in Coppermine...');

$thisplugin->add_filter('plugin_block','tlu_plugin_block');

function tlu_list_users()
{
    global $CONFIG, $PAGE, $FORBIDDEN_SET;
    global $lang_list_users, $lang_errors, $template_user_list_info_box, $cpg_show_private_album, $cpg_udb;

    $rowset = $cpg_udb->list_users_query($user_count);

    $user_list = array();
    foreach ($rowset as $user) {
        $cpg_nopic_data = cpg_get_system_thumb('nopic.jpg', $user['user_id']);
        $user_thumb = '<img src="' . $cpg_nopic_data['thumb'] . '" ' . $cpg_nopic_data['whole'] . ' class="image" border="0" alt="" />';
        $user_pic_count = $user['pic_count'];
        $user_thumb_pid = ($user['gallery_pid']) ? $user['gallery_pid'] : $user['thumb_pid'];
        $user_album_count = $user['alb_count'];
        $cat_array=array();
        if ($user_pic_count) {
            $sql = "SELECT filepath, filename, url_prefix, pwidth, pheight " . "FROM {$CONFIG['TABLE_PICTURES']} " . "WHERE pid='$user_thumb_pid' AND approved='YES'";
            $result = cpg_db_query($sql);
            if (mysql_num_rows($result)) {
                $picture = mysql_fetch_array($result);
                mysql_free_result($result);
                $pic_url = get_pic_url($picture, 'thumb');
                if (!is_image($picture['filename'])) {
                    $image_info = getimagesize(urldecode($pic_url));
                    $picture['pwidth'] = $image_info[0];
                    $picture['pheight'] = $image_info[1];
                }
                $image_size = compute_img_size($picture['pwidth'], $picture['pheight'], $CONFIG['alb_list_thumb_size']);
                $user_thumb = "<img src=\"" . $pic_url . "\" class=\"image\" {$image_size['geom']} border=\"0\" alt=\"\" />";
            }
        }

        $cat_array[0]='<a href="index.php?cat='.($user['user_id']+FIRST_USER_CAT).'">'.ucfirst($user['user_name']).'</a>';
        $cat_array[1]='';
        $cat_array[2]=$user_album_count;
        $cat_array[3]=$user_pic_count;
        if (1 <= $CONFIG['subcat_level']) {
            $cat_array['cat_albums']=list_cat_albums($user['user_id']+FIRST_USER_CAT);
        } else {
            $cat_array['cat_albums']='';
        }
        $cat_array['cat_thumb']='<a href="index.php?cat='.($user['user_id']+FIRST_USER_CAT).'">'.$user_thumb.'</a>';

        $user_list[]=$cat_array;

    }
    return $user_list;
}

function tlu_plugin_block($var)
{
	global $cat_data;
	static $run;
	if (!isset($run)) {
        $run=true;
        $splice_key=-1;
        if ($cat==0) {
            foreach ($cat_data as $data) {
                $splice_key++;
                if (stristr($data[0],'User galleries')) {
                    break;
                }
            }

            if ($splice_key > -1)   {
                 array_splice($cat_data,$splice_key,1,tlu_list_users());
            }
       }
    }
    return $var;
}


?>
