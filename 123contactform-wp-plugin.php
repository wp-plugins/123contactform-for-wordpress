<?php
/*
Plugin Name: 123ContactForm for WordPress
Plugin URI: http://www.123contactform.com/wordpress-contact-form-plugin.html
Description: Contact Form plugin from 123ContactForm.com. Usage notes, tips and tricks, <a href="http://www.123contactform.com/wordpress-contact-form-plugin.html">here</a>.
Author: 123ContactForm.com
Version: 1.2.2
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
    while ($ready==false)
        {		
	    $i = strpos($tosearch, "[123-contact-form ");
	    if ($i !== false) 
	        {
	        $j = strpos($tosearch, "]");		
			if ($j===false) return $content; /* form code not closed correctly */
			
	        $id = substr($tosearch, $i+19, $j-$i-19);
	        if (is_numeric($id))
	            {		  
		        $toreplace=substr($tosearch,$i,$j-$i+1);
        
				$formcode="<script type=\"text/javascript\">var servicedomain=\"www.123contactform.com\"; var cfJsHost = ((\"https:\" == document.location.protocol) ? \"https://\" : \"http://\"); document.write(unescape(\"%3Cscript src='\" + cfJsHost + servicedomain + \"/includes/easyXDM.min.js' type='text/javascript'%3E%3C/script%3E\")); document.write(unescape(\"%3Cscript src='\" + cfJsHost + servicedomain + \"/jsform-$id.js' type='text/javascript'%3E%3C/script%3E\")); </script>";
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