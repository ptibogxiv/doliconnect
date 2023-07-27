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
	$css = 'css';
	$versionbase = '5.3.1'; 
	$version = $versionbase; 
} else {
	$css = 'bootswatch/'.get_theme_mod( 'ptibogxivtheme_css');
	$version = '5.3.0'; 
	$versionbase = $version;
}

if (!empty(get_theme_mod( 'ptibogxivtheme_css')) && $version != $versionbase && empty(get_option('doliconnectbeta'))) {
	$css = 'css';
	$version = $versionbase;
}

	wp_register_style( 'bootstrap.min', plugins_url( 'doliconnect/includes/bootstrap/'.$css.'/bootstrap.min.css' ), array(), $version );
	wp_enqueue_style( 'bootstrap.min');
	wp_register_script( 'bootstrap.bundle.min', plugins_url( 'doliconnect/includes/bootstrap/js/bootstrap.bundle.min.js' ), array( 'jquery' ), $version, true );
  	wp_enqueue_script( 'bootstrap.bundle.min');
	wp_enqueue_script( 'jquery-masonry', array( 'jquery' ) );
	if (empty(get_option('doliconnectfontawesome'))) {
  		wp_register_script( 'font-awesome', '//use.fontawesome.com/releases/v6.4.0/js/all.js', array(), '6.4.0' );
		wp_enqueue_script( 'font-awesome');
	}
	//wp_register_script( 'doliconnect-dolicart', plugins_url( 'doliconnect/includes/js/dolicart.js'), array( 'jquery' ), '', false );
	//wp_enqueue_script( 'doliconnect-dolicart' );
  	wp_register_style( 'bootstrap-social', plugins_url( 'doliconnect/includes/bootstrap/css/bootstrap-social.css' ), array(), $version );
	wp_enqueue_style( 'bootstrap-social');
  	wp_register_style( 'flag-icon', plugins_url( 'doliconnect/includes/flag-icon-css/css/flag-icons.css' ), array(), '6.9.2' ); 
	wp_enqueue_style( 'flag-icon');

}

?>