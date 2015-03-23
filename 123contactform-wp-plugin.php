<?php
/*
Plugin Name: 123ContactForm for WordPress
Plugin URI: http://www.123contactform.com/wordpress-contact-form-plugin.html
Description: Contact Form plugin from 123ContactForm.com. Usage notes, tips and tricks, <a href="http://www.123contactform.com/wordpress-contact-form-plugin.html">here</a>.
Author: 123ContactForm.com
Version: 1.3.1
Author URI: http://www.123contactform.com/
*/

add_filter('mce_external_plugins', "contact_123_register");
add_filter('mce_buttons', 'contact_123_add_button', 0);
add_filter('the_content', 'w123cf_widget_text_filter', 9 );

function contact_123_add_button($buttons)
{
    array_push($buttons, "separator", "123contactform");
    return $buttons;
}

function contact_123_register($plugin_array)
{
    $url = trim(get_bloginfo('url'), "/")."/wp-content/plugins/123contactform-for-wordpress/editor_plugin.js";
    $plugin_array['contact_123'] = $url;
    return $plugin_array;
}

function w123cf_widget_text_filter( $content ) {
    $tosearch = $content;
	$ready=false;
	$customVars2="";
    while ($ready==false)
        {		
	    $i = strpos($tosearch, "[123-contact-form ");
	    if ($i !== false) 
	        {
	        $j = strpos($tosearch, "]", $i);		
			if ($j===false) return $content; /* form code not closed correctly */
		$pos_custom_controls=strpos($tosearch,"control",$i+19);
	        if(($pos_custom_controls > $i) &&($pos_custom_controls < $j))
				{//If we face some custom vars inside the WP-content page
				 $pos1=stripos($tosearch,"i",$i);
				 $pos2=stripos($tosearch,"control",$i);
				 $id=substr($tosearch,$pos1,$pos2-$pos1);
				 $id=str_replace("i","",$id);
				 $id=str_replace(" ","",$id);
				 $id=intval($id);
				 $customVars2=substr($tosearch,$pos2,$j-$pos2);
				 $customVars2=str_replace("'","",$customVars2);
				 $customVars2=str_replace('"','',$customVars2);
				}
			else
				$id = substr($tosearch, $i+19, $j-$i-19);
		
		
	        if (is_numeric($id))
	            {		  
		        $toreplace=substr($tosearch,$i,$j-$i+1);

				$formcode="<script type=\"text/javascript\">var customVars123='$customVars2';var servicedomain=\"www.123contactform.com\"; var cfJsHost = ((\"https:\" == document.location.protocol) ? \"https://\" : \"http://\"); document.write(unescape(\"%3Cscript src='\" + cfJsHost + servicedomain + \"/includes/easyXDM.min.js' type='text/javascript'%3E%3C/script%3E\")); document.write(unescape(\"%3Cscript src='\" + cfJsHost + servicedomain + \"/jsform-$id.js?\"+customVars123+\"' type='text/javascript'%3E%3C/script%3E\")); </script>";				
				$tosearch=str_replace($toreplace, $formcode, $tosearch);
				 				
				if ( is_callable('curl_init') ) {					
					$curl = curl_init("http://www.123contactform.com/embedded-link/".$id.".txt");
					curl_setopt($curl, CURLOPT_HEADER, 0);
					ob_start();  
					curl_exec($curl);  
					curl_close($curl);
					$linkcode = ob_get_contents();  
					ob_end_clean(); 				
					}
				elseif ( is_callable('file_get_contents') ) {
					$linkcode=file_get_contents("http://www.123contactform.com/embedded-link/".$id.".txt");   
					}
					
 
  
				$tosearch.=$linkcode;
		        }
	        }
		else $ready=true;	
		}
	return $tosearch;	
}
/* Form submision to new wp post */
add_action( 'wp_ajax_cfp-connect', 'cfp_connect' );
add_action( 'wp_ajax_nopriv_cfp-connect', 'cfp_connect' );
function cfp_connect() {
        $cfp_pub_key = $_POST["pk"];
        $message = $_POST["message"];
        $signature = base64_decode(str_replace(" ", "+", $_POST["signature"]));
        if(!isset($cfp_pub_key) || $cfp_pub_key=="") { echo cfp_message("Key is not sent",0);exit(); } // Key is not sent
        $verify = openssl_verify($message, $signature, base64_decode($cfp_pub_key), OPENSSL_ALGO_SHA1);
        if ($verify == 1) {
            if(!get_option("123cf_post_public_key")) {
                add_option("123cf_post_public_key",$cfp_pub_key);  
            } else {
                update_option("123cf_post_public_key",$cfp_pub_key);
            }
            echo cfp_message("WordPress connected",1);exit();
        } elseif ($verify == 0) {
            echo cfp_message("Signature not verified",0);exit();
        } else {
           echo cfp_message("error: ".openssl_error_string(),0);exit();
        }
        exit();
}
function cfp_upload_image($post_id,$post_image, $post_image_name = null) {
                 $upload_dir=wp_upload_dir();
                 $upload_path=str_replace( '/', DIRECTORY_SEPARATOR, $upload_dir['path'] ) . DIRECTORY_SEPARATOR;
                 $decoded_img=base64_decode($post_image);
                 if(!$post_image_name) { $filename='image.png'; } else { $filename=$post_image_name; };
                 $hashed_filename=md5( $filename . microtime() ) . '_' . $filename;
                 $image_upload=file_put_contents( $upload_path . $hashed_filename, $decoded_img );
                 if( !function_exists( 'wp_handle_sideload' ) ) {
                   require_once( ABSPATH . 'wp-admin/includes/file.php' );
                 }
                 if( !function_exists( 'wp_get_current_user' ) ) {
                   require_once( ABSPATH . 'wp-includes/pluggable.php' );
                 }
                 $file             = array();
                 $file['error']    = '';
                 $file['tmp_name'] = $upload_path . $hashed_filename;
                 $file['name']     = $hashed_filename;
                 $file['type']     = 'image/jpg';
                 $file['size']     = filesize( $upload_path . $hashed_filename );
                 $file_return = wp_handle_sideload( $file, array( 'test_form' => false ) ); 
                 $file_url = $file_return["file"];
                $filetype = wp_check_filetype( basename( $file_url ), null );
                $attachment = array(
                        'guid'           =>  $upload_dir['url'] . '/' . basename( $file_url ), 
                        'post_mime_type' => $filetype['type'],
                        'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $file_url ) ),
                        'post_content'   => '',
                        'post_status'    => 'inherit'
                );
                $attach_id = wp_insert_attachment( $attachment, $file_url, $post_id );
                require_once( ABSPATH . 'wp-admin/includes/image.php' );
                $attach_data = wp_generate_attachment_metadata( $attach_id, $file_url );
                wp_update_attachment_metadata( $attach_id, $attach_data );
                update_post_meta( $post_id, '_thumbnail_id', $attach_id );
}
add_action( 'wp_ajax_cfp-new-post', 'cfp_new_post' );
add_action( 'wp_ajax_nopriv_cfp-new-post', 'cfp_new_post' );
function cfp_new_post() {
    if(!cfp_authenticate()) { echo cfp_message("There was an error while trying to authenticate with wordpress",0); exit(); }
    $post_title = strip_tags(rawurldecode($_POST["post_title"]));
    $post_title = preg_replace("/&nbsp;/",' ',$post_title);
    $post_title = stripslashes($post_title);
    $post_content = rawurldecode($_POST["post_content"]);
    $post_content = stripslashes($post_content);
    $post_status = $_POST["post_status"];
    $post_category = urldecode($_POST["post_category"]);
    $post_author = $_POST["post_author"];
    $post_format = $_POST["post_format"];
    $comments = $_POST["comment_status"];
    $comments == "1" ? $comment_status = "open" : $comment_status = "closed";
    $post_excerpt = rawurldecode($_POST["post_excerpt"]);
    $post_excerpt = preg_replace("/&nbsp;/",' ',$post_excerpt);
    $post_excerpt = stripslashes($post_excerpt);
    $post_tags = explode(",",rawurldecode($_POST["post_tags"]));
    $post_image = str_replace(" ", "+",$_POST["post_image"]);
    $post_image_name = $_POST["post_image_name"];    
    $custom_fields_keys = cfp_get_custom_post_fields();
    $custom_fields_values = array();
    foreach($custom_fields_keys as $key) {  
        if($_POST[$key]) {
           $custom_fields_values[rawurldecode($key)] =  $_POST[$key];
        }
    }
    $post_categories = explode(",",$post_category);
    $cat_id_arr = array();
    if(is_array($post_categories)) {
        foreach($post_categories as $category_name) {
            $category_id = get_cat_ID( $category_name );
            if($category_id) { 
               $cat_id_arr[] = $category_id; 
            }
        }
    }
    $new_post = array(
    'post_author'    => $post_author,    
    'post_title'     => $post_title,
    'post_content'   => $post_content,
    'post_status'    => $post_status,
    'comment_status' => $comment_status,
    'post_excerpt'   => $post_excerpt,
    'post_category'  => $cat_id_arr
    );
    $post_id = wp_insert_post( $new_post );
    if($post_id) {
       foreach($custom_fields_values as $meta_key=>$meta_value) {
          add_post_meta($post_id,str_replace("|***|"," ",$meta_key), $meta_value);  
       } 
         set_post_format($post_id, $post_format);
         wp_set_post_tags($post_id, $post_tags);
         if(isset($post_image)) {
              cfp_upload_image($post_id,$post_image,$post_image_name);   
        }
        echo cfp_message("New post created",1); exit();
    }
    echo cfp_message("There was an error while trying to create new post",0); exit();
}
function cfp_get_custom_post_fields() {    
    global $wpdb;
    $custom_fields = array();
    $fields = $wpdb->get_results( 'SELECT DISTINCT meta_key FROM wp_postmeta', OBJECT );
    foreach ($fields as $field) {
        if (substr($field->meta_key,0,1) != "_"){
            $meta_key = str_replace(" ","|***|",$field->meta_key);
            $custom_fields[] = $meta_key;
        }
    }
    return $custom_fields;
}
function insert_child_category($category,$wp_categories) {
    if($category->parent == 0) {
        $wp_categories[] = $category;
        $args = array('hierarchical'=>true,'hide_empty'=>0,'child_of'=>$category->cat_ID );
        $child_categories = get_categories( $args );
        foreach($child_categories as $child_cat) {
           $wp_categories[] = $child_cat; 
        }
    }
    return $wp_categories;
} 
add_action( 'wp_ajax_cfp-get-wp-data', 'cfp_get_wp_data' );
add_action( 'wp_ajax_nopriv_cfp-get-wp-data', 'cfp_get_wp_data' );
function cfp_get_wp_data() { 
    if(!cfp_authenticate()) { echo cfp_message("There was an error while trying to authenticate with wordpress",0); exit(); }
    global $wpdb;
    $data = array();
    $custom_fields = array();
    $fields = $wpdb->get_results( 'SELECT DISTINCT meta_key FROM wp_postmeta', OBJECT );
    foreach ($fields as $field) {
        if (substr($field->meta_key,0,1) != "_"){
            $custom_fields[] = $field->meta_key;
        }
    }
    $data["custom_fields"] = $custom_fields;
   $args = array('orderby' => 'name','hierarchical'=>true,'hide_empty'=>0,'parent'=>0 ); 
   $categories = get_categories( $args );
   $wp_categories = array();
   foreach($categories as $category) {
       $wp_categories = insert_child_category($category,$wp_categories);
   }
   $data["categories"] = $wp_categories; 
   $args_authors = array('who' => 'author');
   $users = get_users($args_authors);
   $authors = array();
   foreach($users as $user) {
       $authors[] = array("id"=>$user->data->ID,"username"=>$user->data->user_login);
   }
   $data["authors"] = $authors;
    echo json_encode($data);exit();
}
add_action( 'wp_ajax_cfp-check-connection', 'cfp_check_connection' );
add_action( 'wp_ajax_nopriv_cfp-check-connection', 'cfp_check_connection' );
function cfp_check_connection() { 
    if(!cfp_authenticate()) { echo cfp_message("There was an error while trying to authenticate with wordpress",0); exit(); }
    echo cfp_message("Connection OK",1); exit();
}
function cfp_authenticate() {
        if(!get_option( "123cf_post_public_key")) { return false; }
        $cfp_pub_key = get_option( "123cf_post_public_key");
        $message = $_POST["message"];
        $signature = base64_decode(str_replace(" ", "+", $_POST["signature"]));
        $verify = openssl_verify($message, $signature, base64_decode($cfp_pub_key), OPENSSL_ALGO_SHA1);       
        return $verify;
}
function cfp_message($message,$status) {
    $return_message = array("message"=>$message,"status"=>$status);
    return json_encode($return_message);
}