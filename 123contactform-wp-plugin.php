<?php
/*
Plugin Name: 123ContactForm for WordPress
Plugin URI: http://www.123contactform.com/wordpress-contact-form-plugin.html
Description: Contact Form plugin from 123ContactForm.com. Usage notes, tips and tricks, <a href="http://www.123contactform.com/wordpress-contact-form-plugin.html">here</a>.
Author: 123ContactForm.com
Version: 1.0.0
Author URI: http://www.123contactform.com/
*/

/*  Copyright 2010 123ContactForm.com (email: use contact form on http://www.123contactform.com/)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

define( 'W123CF_VERSION', '1.0.0' );
define( 'W123CF_IFRAME_HEIGHT', '600' );
define( 'W123CF_IFRAME_SCROLLING', 'no' );
define( 'W123CF_IFRAME_BORDER', '0' );

add_filter('the_content', 'w123cf_widget_text_filter', 9 );
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
			    if ($tosearch[$i+18]=="f")
			       {
				   /* embedded form */
				   $formcode=file_get_contents("http://www.123contactform.com/embedded/".$id.".txt");
				   $tosearch=str_replace($toreplace, $formcode, $tosearch);
				   }
				else    
				   {
				   /* embedded iframe */
				   $formlink="http://www.123contactform.com/embedded/".$id.".txt";
				   $formcode="<iframe height=\"".W123CF_IFRAME_HEIGHT."\" width=\"100%\" id=\"contactform123\" name=\"contactform123\" scrolling=\"".W123CF_IFRAME_SCROLLING."\" marginwidth=\"0\" marginheight=\"0\" frameborder=\"".W123CF_IFRAME_BORDER."\" src=\"$formlink\">\n";
                   $formcode.="<p>Your browser does not support iframes. The contact form cannot be displaied. Please use another contact method (phone, fax etc)</p>\n";
                   $formcode.="</iframe>\n";
				   $tosearch=str_replace($toreplace, $formcode, $tosearch);				  
				   }		        
		        }
	        }
		else $ready=true;	
		}
	return $tosearch;	
}

?>