<?php

add_action( 'wp_enqueue_scripts', 'enqueue_scripts_gdrf_public' );
function enqueue_scripts_gdrf_public() {
	wp_register_script( 'gdrf-public-scripts', plugins_url( 'doliconnect/includes/js/gdrf-public.js'), array( 'jquery' ), '', false );
	$translations = array(
		'gdrf_ajax_url' => esc_url( admin_url( 'admin-ajax.php' ) ),
		'gdrf_success'  => __( 'Your enquiry have been submitted. Check your email to validate your data request.', 'doliconnect'),
		'gdrf_errors'   => __( 'Some errors occurred:', 'doliconnect'),
	);
	wp_localize_script( 'gdrf-public-scripts', 'gdrf_localize', $translations );
}

function doliconnect_enqueues() { 

/* Styles */
if ( empty(get_theme_mod( 'ptibogxivtheme_css')) || get_theme_mod( 'ptibogxivtheme_css') == 'css' ) {
$css='';
$version='4.4.1'; 
} else {
$css='bootswatch/'.get_theme_mod( 'ptibogxivtheme_css').'/';
$version='4.4.1';  
}

	wp_enqueue_style( 'bootstrap', plugins_url( 'doliconnect/includes/css/'.$css.'bootstrap.min.css'), array(), $version);
  
	wp_enqueue_script( 'bootstrap-js', plugins_url( 'doliconnect/includes/js/scripts.min.js'), array('jquery'), ' ', true );

	wp_enqueue_script( 'fontawesome', '//use.fontawesome.com/releases/v5.11.2/js/all.js', array(), '5.11.2' );
  
  wp_enqueue_style( 'bootstrap-social', plugins_url( 'doliconnect/includes/css/bootstrap-social.css'), array(), $version); 
  
  wp_enqueue_style( 'flag-icon-css', plugins_url( 'doliconnect/includes/flag-icon-css/css/flag-icon.css'), array(), '3.4.5'); 
}

?>