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
$versionbase = '5.0.0-beta1'; 
$version=$versionbase; 
} else {
$css='bootswatch/'.get_theme_mod( 'ptibogxivtheme_css').'/';
$version='4.5.2';  
}

if (!empty(get_theme_mod( 'ptibogxivtheme_css')) && $version != $versionbase && empty(get_option('doliconnectbeta'))) {
$css='';
$version=$versionbase;
}

	wp_register_style( 'bootstrap.min.css', get_stylesheet_directory_uri() . '/theme/css/'.$css.'bootstrap.min.css', array(), $version);
  //wp_register_style( 'bootstrap.min.css', 'https://cdn.jsdelivr.net/npm/bootstrap@'.$version.'/dist/css/bootstrap.min.css', array(), $version);
	wp_enqueue_style( 'bootstrap.min.css');
	wp_register_script( 'bootstrap.bundle.min.js', get_template_directory_uri() . '/theme/js/bootstrap.bundle.min.js', array('jquery'), $version, true);
  //wp_register_script( 'bootstrap.bundle.min.js', 'https://cdn.jsdelivr.net/npm/bootstrap@'.$version.'/dist/js/bootstrap.bundle.min.js', array(), $version);
  wp_enqueue_script( 'bootstrap.bundle.min.js');
  if (empty(get_option('doliconnectfontawesome'))) {
  wp_register_script( 'font-awesome', '//use.fontawesome.com/releases/v5.15.1/js/all.js', array(), '5.15.1' );
	wp_enqueue_script( 'font-awesome');
  }
  wp_register_style( 'bootstrap-social', plugins_url( 'doliconnect/includes/bootstrap/css/bootstrap-social.css'), array(), $version);
	wp_enqueue_style( 'bootstrap-social');
  wp_register_style( 'flag-icon-css', plugins_url( 'doliconnect/includes/flag-icon-css/css/flag-icon.css'), array(), '3.4.5'); 
	wp_enqueue_style( 'flag-icon-css');
}

?>