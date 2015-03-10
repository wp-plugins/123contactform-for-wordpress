<?php
require_once dirname(__FILE__). '/config.php';
require_once dirname(__FILE__). '/jsonwrapper_helper.php';
function request($api_key) {
    
        $url = "http://www.123contactform.com/wp_dispatcher.php";
        
        $res = wp_remote_fopen("{$url}?api_key=".$api_key);
     
        if($res === false)
            return false;

        return json_decode($res);                
    }
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        
    <title>123ContactForm</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo site_url(); ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>

          
    <style type="text/css">        
        body, input, select {
			font-family:Arial;
            font-size:14px;
        }
    </style>

	<base target="_self" />
	<script>
    function fs_init() {
        
    }
    function contact123(shortcode) {
   		top.window.tinyMCE.execCommand('mceInsertContent',false,'[123-contact-form i'+shortcode+']');
  		tinyMCEPopup.editor.execCommand('mceRepaint');
  		tinyMCEPopup.close();
    	
    }
    </script>
    </head>
    
    <body onload="tinyMCEPopup.executeOnLoad('fs_init();');">
    <?php 
	if ($_POST["apikey"]) {    
		$response = request($_POST["apikey"]);
		if ($response->status == "ok")
			{
			?>
			Select the form you want to embed:
			<br>
			<select name="123_form_toembed" id="123_form_toembed">
			<?php
			foreach ($response->forms as $form)
				{
					?><option value="<?php echo $form->f_id;?>"><?php echo $form->f_name;?></option><?php
				} 
			?>
			</select>
			<input type="button" value="embed" onClick="contact123(document.getElementById('123_form_toembed').options[document.getElementById('123_form_toembed').selectedIndex].value)"/>
			<?php
			}
    	else 
			{
    		echo "Fatal error - ".$response->status;
			}
		}
	else{
		?>
		<p>Please enter your 123ContactForm API Key.<br/> If you don't know it, <a href="http://www.123contactform.com/index.php?p=login" target="_new">login</a> to 123ContactForm, then go to My Account.</p>
		<form method="post" action="">
       		API Key: <input type="text" name="apikey"/ style="width:300px;">
       		<input type="submit" value="Connect">
		</form>
		<?php
		}
	?>	
    </body>           
</html>

