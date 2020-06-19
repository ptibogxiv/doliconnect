<?php

add_action( 'wp_enqueue_scripts', 'enqueue_scripts_doli_gdrf_public' );
function enqueue_scripts_doli_gdrf_public() {
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
$version='4.5.0'; 
} else {
$css='bootswatch/'.get_theme_mod( 'ptibogxivtheme_css').'/';
$version='4.5.0';  
}

	wp_register_style( 'bootstrap.min.css', plugins_url( 'doliconnect/includes/bootstrap/css/'.$css.'bootstrap.min.css'), array(), $version);
	wp_enqueue_style( 'bootstrap.min.css');
	wp_register_script( 'bootstrap.bundle.min.js', plugins_url( 'doliconnect/includes/bootstrap/js/bootstrap.bundle.min.js'), array('jquery'), $version, true);
  wp_enqueue_script( 'bootstrap.bundle.min.js');
  wp_register_script( 'font-awesome', '//use.fontawesome.com/releases/v5.13.1/js/all.js', array(), '5.13.1' );
	wp_enqueue_script( 'font-awesome');
  wp_register_style( 'bootstrap-social', plugins_url( 'doliconnect/includes/bootstrap/css/bootstrap-social.css'), array(), $version);
	wp_enqueue_style( 'bootstrap-social');
  wp_register_style( 'flag-icon-css', plugins_url( 'doliconnect/includes/flag-icon-css/css/flag-icon.css'), array(), '3.4.5'); 
	wp_enqueue_style( 'flag-icon-css');
}

?>