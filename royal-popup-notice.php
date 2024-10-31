<?php
/*
Plugin Name: Royal PopUp Notice
Plugin URI: http://wordpress.org/plugins/royal-popup-notice/
Description: Display a PopUp Notice that you can easily link to any page like feedback, twitter, facebook etc & customise as you like.
Version: 1.0.1
Author: Mehdi Akram
Author URI: http://profiles.wordpress.org/royaltechbd
License: GPLv2
*/

// Hook will fire upon activation - we are using it to set default option values
register_activation_hook( __FILE__, 'royal_popup_notice_activate_plugin' );



//Additional links on the plugin page
add_filter( 'plugin_row_meta', 'royal_popup_notice_register_plugin_links', 10, 2 );
function royal_popup_notice_register_plugin_links($links, $file) {
	$base = plugin_basename(__FILE__);
	if ($file == $base) {
		$links[] = '<a href="http://www.royaltechbd.com/" target="_blank">' . __( 'Royal Technologies', 'rpun' ) . '</a>';
		$links[] = '<a href="http://www.shamokaldarpon.com/" target="_blank">' . __( 'Shamokal Darpon', 'rpun' ) . '</a>';
	}
	return $links;
}







// Add options and populate default values on first load
function royal_popup_notice_activate_plugin() {

	// populate plugin options array
	$royal_popup_notice_plugin_options = array(
		'text_for_h1'      => 'Payment Due Notice',
		'text_for_tab'     => 'Your website development cost is partially or fully due. Please clear your dues and contact your web developer for avoid this notice.',
		'font_family'      => '"Righteous", cursive',
		'font_weight_bold' => '1',
		'button_position'  => '0',
		'tab_url'          => 'https://www.facebook.com/mehdiakram/',
		'bg_image_url'     => 'https://mehdiakram.files.wordpress.com/2014/09/overlay.png',
		'text_color'       => '#000',
		'tab_color'        => '#fff',
		'target_blank'     => '0'
		);

	// create field in WP_options to store all plugin data in one field
	add_option( 'royal_popup_notice_plugin_options', $royal_popup_notice_plugin_options );

}


// Fire off hooks depending on if the admin settings page is used or the public website
if ( is_admin() ){ // admin actions and filters

	// Hook for adding admin menu
	add_action( 'admin_menu', 'royal_popup_admin_menu' );

	// Hook for registering plugin option settings
	add_action( 'admin_init', 'royal_popup_notice_settings_api_init');

	// Hook to fire farbtastic includes for using built in WordPress color picker functionality
	add_action('admin_enqueue_scripts', 'royal_popup_farbtastic_script');

	// Display the 'Settings' link in the plugin row on the installed plugins list page
	add_filter( 'plugin_action_links_'.plugin_basename(__FILE__), 'royal_popup_admin_plugin_actions', -10);

} else { // non-admin enqueues, actions, and filters


	// get the current page url
	$royal_popup_notice_current_page_url 			= royal_popup_notice_get_full_url();


	// get the tab url from the plugin option variable array
	$royal_popup_notice_plugin_option_array	= get_option( 'royal_popup_plugin_options' );
	$royal_popup_notice_tab_url				= $royal_popup_notice_plugin_option_array[ 'tab_url' ];
	$royal_popup_notice_bg_image_url		= $royal_popup_notice_plugin_option_array[ 'bg_image_url' ];


	// compare the page url and the option tab - don't render the tab if the values are the same
	if ( $royal_popup_notice_tab_url != $royal_popup_notice_current_page_url ) {

		// hook to get option values and dynamically render css to support the tab classes
		add_action( 'wp_head', 'royal_popup_notice_custom_css_hook' );

		// hook to get option values and write the div for the Royal PopUp Notice to display
		add_action( 'wp_footer', 'royal_popup_notice_body_tag_html' );
	}
}



// get the complete url for the current page
function royal_popup_notice_get_full_url()
{
	$s 			= empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
	$sp 		= strtolower($_SERVER["SERVER_PROTOCOL"]);
	$protocol 	= substr($sp, 0, strpos($sp, "/")) . $s;
	$port 		= ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
	return $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
}



// Include WordPress color picker functionality
function royal_popup_farbtastic_script($hook) {

	// only enqueue farbtastic on the plugin settings page
	if( $hook != 'settings_page_royal_popup_notice' ) 
		return;


	// load the style and script for farbtastic
	wp_enqueue_style( 'farbtastic' );
	wp_enqueue_script( 'farbtastic' );

}



// action function to get option values and write the div for the Royal PopUp Notice to display
function royal_popup_notice_body_tag_html() {

	// get plugin option array and store in a variable
	$royal_popup_notice_plugin_option_array	= get_option( 'royal_popup_plugin_options' );

	// fetch individual values from the plugin option variable array
	$royal_popup_notice_text_for_h1				= $royal_popup_notice_plugin_option_array[ 'text_for_h1' ];
	$royal_popup_notice_text_for_tab			= $royal_popup_notice_plugin_option_array[ 'text_for_tab' ];
	$royal_popup_notice_tab_url					= $royal_popup_notice_plugin_option_array[ 'tab_url' ];
	$royal_popup_notice_bg_image_url			= $royal_popup_notice_plugin_option_array[ 'bg_image_url' ];
	$royal_popup_notice_target_blank			= $royal_popup_notice_plugin_option_array[ 'target_blank' ];

	// set the page target
	if ($royal_popup_notice_target_blank == '1') {
		$royal_popup_notice_target_blank = ' target="_blank"';
	}

	// Write HTML to render tab
	echo '
	<div class="royal_popup_notice_bg"></div>
		<div class="royal_popup_notice_div">
			<div class="royal_popup_notice_content">
				<a href="' . esc_url( $royal_popup_notice_tab_url ) . '"' . $royal_popup_notice_target_blank . '>		
				<h1>' . esc_html( $royal_popup_notice_text_for_h1 ) . '</h1>
				</a>	
				<p>' . esc_html( $royal_popup_notice_text_for_tab ) . '</p>
			</div>	
		</div>	
	
	';
}



// action function to add a new submenu under Settings
function royal_popup_admin_menu() {

	// Add a new submenu under Settings
	add_options_page( 'Royal PopUp Notice Option Settings', 'Royal PopUp Notice', 'manage_options', 'royal_popup_notice', 'royal_popup_notice_options_page' );
}


// Display and fill the form fields for the plugin admin page
function royal_popup_notice_options_page() {


?>

	<div class="wrap">
	<?php screen_icon( 'plugins' ); ?>
	<h2>Royal PopUp Notice</h2>
	<p>Royal PopUp Notice was created to give you an easy option for adding a link like contact/feedback/facebook page.
	<strong>NOTE: This plugin requires the WP_footer() hook to be fired from your theme.</strong></p>
	<form method="post" action="options.php">


<?php

	settings_fields( 'royal_popup_notice_option_group' );
	do_settings_sections( 'royal_popup_notice' );

	// get plugin option array and store in a variable
	$royal_popup_notice_plugin_option_array	= get_option( 'royal_popup_plugin_options' );

	// fetch individual values from the plugin option variable array
	$royal_popup_notice_text_for_h1				= $royal_popup_notice_plugin_option_array[ 'text_for_h1' ];
	$royal_popup_notice_text_for_tab			= $royal_popup_notice_plugin_option_array[ 'text_for_tab' ];
	$royal_popup_notice_font_family				= $royal_popup_notice_plugin_option_array[ 'font_family' ];
	$royal_popup_notice_font_weight_bold		= $royal_popup_notice_plugin_option_array[ 'font_weight_bold' ];
	$royal_popup_notice_tab_url					= $royal_popup_notice_plugin_option_array[ 'tab_url' ];
	$royal_popup_notice_bg_image_url			= $royal_popup_notice_plugin_option_array[ 'bg_image_url' ];
	$royal_popup_notice_text_color				= $royal_popup_notice_plugin_option_array[ 'text_color' ];
	$royal_popup_notice_tab_color				= $royal_popup_notice_plugin_option_array[ 'tab_color' ];
	$royal_popup_notice_target_blank			= $royal_popup_notice_plugin_option_array[ 'target_blank' ];

?>



	<script type="text/javascript">

		jQuery(document).ready(function() {
			jQuery('#colorpicker1').hide();
			jQuery('#colorpicker1').farbtastic("#color1");
			jQuery("#color1").click(function(){jQuery('#colorpicker1').slideToggle()});
		});

		jQuery(document).ready(function() {
			jQuery('#colorpicker2').hide();
			jQuery('#colorpicker2').farbtastic("#color2");
			jQuery("#color2").click(function(){jQuery('#colorpicker2').slideToggle()});
		});

		jQuery(document).ready(function() {
			jQuery('#colorpicker3').hide();
			jQuery('#colorpicker3').farbtastic("#color3");
			jQuery("#color3").click(function(){jQuery('#colorpicker3').slideToggle()});
		});

	</script>


	<table class="widefat royalpopupwarpper">

		<tr valign="top">
		<th scope="row" width="230"><label for="royal_popup_notice_text_for_h1">PopUp Notice Header</label></th>
		<td width="525"><input maxlength="1025" size="100%" type="text" name="royal_popup_plugin_options[text_for_h1]" value="<?php echo esc_html( $royal_popup_notice_text_for_h1 ); ?>" /></td>
		</tr>		
		
		<tr valign="top">
		<th scope="row" width="230"><label for="royal_popup_notice_text_for_tab">PopUp Notice Text</label></th>
		<td width="525"><input maxlength="1025" size="100%" type="text" name="royal_popup_plugin_options[text_for_tab]" value="<?php echo esc_html( $royal_popup_notice_text_for_tab ); ?>" /></td>
		</tr>


		<tr valign="top">
		<th scope="row"><label for="royal_popup_tab_font">Select Font</label></th>
		<td>
			<select name="royal_popup_plugin_options[font_family]">	
				<option value='"Arial", sans-serif'								<?php selected( $royal_popup_notice_font_family, '"arial", sans-serif' );							?>	>Arial</option>
				<option value='"Open Sans",sans-serif'							<?php selected( $royal_popup_notice_font_family, '"Open Sans",sans-serif' );						?>	>Open Sans</option>
				<option value='"Righteous", cursive'							<?php selected( $royal_popup_notice_font_family, '"Righteous", cursive' );							?>	>Righteous (Google Font)</option>
				<option value='"Titan One", cursive'							<?php selected( $royal_popup_notice_font_family, '"Titan One", cursive' );							?>	>Titan One (Google Font)</option>
				<option value='"Finger Paint", cursive'							<?php selected( $royal_popup_notice_font_family, '"Finger Paint", cursive' );							?>	>Finger Paint (Google Font)</option>
				<option value='"Londrina Shadow", cursive'						<?php selected( $royal_popup_notice_font_family, '"Londrina Shadow", cursive' );						?>	>Londrina Shadow (Google Font)</option>
				<option value='"Autour One", cursive'							<?php selected( $royal_popup_notice_font_family, '"Autour One", cursive' );							?>	>Autour One (Google Font)</option>
				<option value='"Meie Script", cursive'							<?php selected( $royal_popup_notice_font_family, '"Meie Script", cursive' );							?>	>Meie Script (Google Font)</option>
				<option value='"Sonsie One", cursive'							<?php selected( $royal_popup_notice_font_family, '"Sonsie One", cursive' );							?>	>Sonsie One (Google Font)</option>
				<option value='"Kavoon", cursive'								<?php selected( $royal_popup_notice_font_family, '"Kavoon", cursive' );								?>	>Kavoon (Google Font)</option>
				<option value='"Racing Sans One", cursive'						<?php selected( $royal_popup_notice_font_family, '"Racing Sans One", cursive' );						?>	>Racing Sans One (Google Font)</option>
				<option value='"Gravitas One", cursive'							<?php selected( $royal_popup_notice_font_family, '"Gravitas One", cursive' );							?>	>Gravitas One (Google Font)</option>
				<option value='"Nosifer", cursive'								<?php selected( $royal_popup_notice_font_family, '"Nosifer", cursive' );								?>	>Nosifer (Google Font)</option>
				<option value='"Offside", cursive'								<?php selected( $royal_popup_notice_font_family, '"Offside", cursive' );								?>	>Offside (Google Font)</option>
				<option value='"Audiowide", cursive'							<?php selected( $royal_popup_notice_font_family, '"Audiowide", cursive' );							?>	>Audiowide (Google Font)</option>
				<option value='"Faster One", cursive'							<?php selected( $royal_popup_notice_font_family, '"Faster One", cursive' );							?>	>Faster One (Google Font)</option>
				<option value='"Germania One", cursive'							<?php selected( $royal_popup_notice_font_family, '"Germania One", cursive' );							?>	>Germania One (Google Font)</option>
				<option value='"Emblema One", cursive'							<?php selected( $royal_popup_notice_font_family, '"Emblema One", cursive' );							?>	>Emblema One (Google Font)</option>
				<option value='"Sansita One", cursive'							<?php selected( $royal_popup_notice_font_family, '"Sansita One", cursive' );							?>	>Sansita One (Google Font)</option>
				<option value='"Creepster", cursive'							<?php selected( $royal_popup_notice_font_family, '"Creepster", cursive' );							?>	>Creepster (Google Font)</option>
				<option value='"Delius Unicase", cursive'						<?php selected( $royal_popup_notice_font_family, '"Delius Unicase", cursive' );						?>	>Delius Unicase (Google Font)</option>
				<option value='"Wallpoet", cursive'								<?php selected( $royal_popup_notice_font_family, '"Wallpoet", cursive' );								?>	>Wallpoet (Google Font)</option>
				<option value='"Monoton", cursive'								<?php selected( $royal_popup_notice_font_family, '"Monoton", cursive' );								?>	>Monoton (Google Font)</option>
				<option value='"Kenia", cursive'								<?php selected( $royal_popup_notice_font_family, '"Kenia", cursive' );								?>	>Kenia (Google Font)</option>
				<option value='"Monofett", cursive'								<?php selected( $royal_popup_notice_font_family, '"Monofett", cursive' );								?>	>Monofett (Google Font)</option>
				<option value='"Denk One", sans-serif'							<?php selected( $royal_popup_notice_font_family, '"Denk One", sans-serif' );							?>	>Denk One (Google Font)</option>
				<option value='"Ropa Sans", sans-serif'							<?php selected( $royal_popup_notice_font_family, '"Ropa Sans", sans-serif' );							?>	>Ropa Sans (Google Font)</option>
				<option value='"Paytone One", sans-serif'						<?php selected( $royal_popup_notice_font_family, '"Paytone One", sans-serif' );						?>	>Paytone One (Google Font)</option>
				<option value='"Russo One", sans-serif'							<?php selected( $royal_popup_notice_font_family, '"Russo One", sans-serif' );							?>	>Russo One (Google Font)</option>
				<option value='"Krona One", sans-serif'							<?php selected( $royal_popup_notice_font_family, '"Krona One", sans-serif' );							?>	>Krona One (Google Font)</option>
				<option value='"Rum Raisin", sans-serif'						<?php selected( $royal_popup_notice_font_family, '"Rum Raisin", sans-serif' );						?>	>Rum Raisin (Google Font)</option>
				
			</select>
		</td>
		</tr>


		<tr valign="top">
		<th scope="row"><label for="royal_popup_notice_font_weight_bold">Bold text</label></th>
		<td><input name="royal_popup_plugin_options[font_weight_bold]" type="checkbox" value="1" <?php checked( '1', $royal_popup_notice_font_weight_bold ); ?> /></td>
		</tr>
		

		<tr valign="top">
		<th scope="row"><label for="royal_popup_notice_target_blank">Open link in new window</label></th>
		<td><input name="royal_popup_plugin_options[target_blank]" type="checkbox" value="1" <?php checked( '1', $royal_popup_notice_target_blank ); ?> /></td>
		</tr>


		<tr valign="top">
		<th scope="row"><label for="royal_popup_notice_tab_url">Header URL</label></th>
		<td><input size="100%" type="text" name="royal_popup_plugin_options[tab_url]" value="<?php echo esc_url( $royal_popup_notice_tab_url ); ?>" /></td>
		</tr>		
		
	
	</table>

<BR>

	<table class="widefat royalpopupwarpper" border="1">

		<tr valign="top">
			<th scope="row" colspan="2" width="33%"><strong>Colors:</strong> Click on each field to display the color picker. Click again to close it.</th>
			<td width="33%" rowspan="4">
				<div id="colorpicker1"></div>
				<div id="colorpicker2"></div>
				<div id="colorpicker3"></div>
			</td>
		</tr>


		<tr valign="top">
			<th scope="row"><label for="royal_popup_notice_text_color">Text color</label></th>
			<td width="33%"><input type="text" maxlength="7" size="6" value="<?php echo esc_attr( $royal_popup_notice_text_color ); ?>" name="royal_popup_plugin_options[text_color]" id="color1" /></td>
		</tr>


		<tr valign="top">
			<th scope="row"><label for="royal_popup_notice_tab_color">Background color</label></th>
			<td width="33%"><input type="text" maxlength="7" size="6" value="<?php echo esc_attr( $royal_popup_notice_tab_color ); ?>" name="royal_popup_plugin_options[tab_color]" id="color2" /></td>
		</tr>
		
		<tr valign="top">
		<th scope="row"><label for="royal_popup_notice_bg_image_url">Background Image URL</label></th>
		<td><input size="33%" type="text" name="royal_popup_plugin_options[bg_image_url]" value="<?php echo esc_url( $royal_popup_notice_bg_image_url); ?>" /></td>
		</tr>
	</table>

	<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>



<?php
	echo '</form>';
	echo '</div>';
}



// Use Settings API to whitelist options
function royal_popup_notice_settings_api_init() {
	register_setting( 'royal_popup_notice_option_group', 'royal_popup_plugin_options' );
}



// Build array of links for rendering in installed plugins list
function royal_popup_admin_plugin_actions($links) {

	$links[] = '<a href="options-general.php?page=royal_popup_notice">'.__('Settings').'</a>';
	return $links;

}



// This function runs all the css and dynamic css elements for displaying the Royal PopUp Notice
function royal_popup_notice_custom_css_hook() {

	// get plugin option array and store in a variable
	$royal_popup_notice_plugin_option_array	= get_option( 'royal_popup_plugin_options' );

	// fetch individual values from the plugin option variable array
	$royal_popup_notice_text_for_h1				= $royal_popup_notice_plugin_option_array[ 'text_for_h1' ];
	$royal_popup_notice_text_for_tab			= $royal_popup_notice_plugin_option_array[ 'text_for_tab' ];
	$royal_popup_notice_font_family				= $royal_popup_notice_plugin_option_array[ 'font_family' ];
	$royal_popup_notice_font_weight_bold		= $royal_popup_notice_plugin_option_array[ 'font_weight_bold' ];
	$royal_popup_notice_text_shadow				= $royal_popup_notice_plugin_option_array[ 'text_shadow' ];
	$royal_popup_notice_button_position			= $royal_popup_notice_plugin_option_array[ 'button_position' ];
	$royal_popup_notice_tab_url					= $royal_popup_notice_plugin_option_array[ 'tab_url' ];
	$royal_popup_notice_bg_image_url			= $royal_popup_notice_plugin_option_array[ 'bg_image_url' ];
	$royal_popup_notice_text_color				= $royal_popup_notice_plugin_option_array[ 'text_color' ];
	$royal_popup_notice_tab_color				= $royal_popup_notice_plugin_option_array[ 'tab_color' ];

?>

<style type='text/css'>
@import url(http://fonts.googleapis.com/css?family=Autour+One|Meie+Script|Armata|Rum+Raisin|Sonsie+One|Kavoon|Denk+One|Gravitas+One|Racing+Sans+One|Nosifer|Ropa+Sans|Offside|Titan+One|Paytone+One|Audiowide|Righteous|Faster+One|Russo+One|Germania+One|Krona+One|Emblema+One|Creepster|Delius+Unicase|Wallpoet|Sansita+One|Monoton|Kenia|Monofett);


/* Begin Royal PopUp Notice Styles*/


.royalpopupwarpper input {line-height: 110%;  padding: 10px!important;}


.royal_popup_notice_bg
{
top: 0;
left: 0; 
height: 100%;
position: fixed;
width: 100%; 
background: url("<?php echo $royal_popup_notice_bg_image_url; ?>") repeat scroll 0 0 #000; 
opacity: 0.9;
z-index: 2147483646;
}


.royal_popup_notice_div{
left: 0;
top: 0;
height: 100%;
width: 100%;
position: fixed;
z-index: 2147483647
}
.royal_popup_notice_content {
background-color:<?php echo $royal_popup_notice_tab_color; ?>;
max-width: 500px;
width: 80%;
min-height: 200px;
margin:10% auto;
text-decoration:none;
text-align:center;
border: 10px solid rgba(0, 0, 0, .8);
border-radius: 5px;
}


.royal_popup_notice_content a:link,
.royal_popup_notice_content a:visited,
.royal_popup_notice_content a:hover,
.royal_popup_notice_content a:active
{text-decoration: none; color: #fff;}

.royal_popup_notice_content h1{background: #000;padding: 5px; font-size: 25px; font-weight: bold;}

.royal_popup_notice_content p{
padding: 5px;
font-family:<?php echo $royal_popup_notice_font_family; ?>;
color:<?php echo $royal_popup_notice_text_color; ?>;
font-size:18px;
line-height: 130%;
<?php
if ( $royal_popup_notice_font_weight_bold =='1' ) :
  echo 'font-weight:bold;' . "\n";
else :
  echo 'font-weight:normal;' . "\n";
endif;
?>
}

/* End Royal PopUp Notice Styles*/
</style>

<?php
}
