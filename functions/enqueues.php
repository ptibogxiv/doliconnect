<?php

function doliconnect_enqueues() { 

/* Styles */
if ( empty(get_theme_mod( 'ptibogxivtheme_css')) || get_theme_mod( 'ptibogxivtheme_css') == 'css' ) {
$css='';
$version='4.3.1'; 
} else {
$css='bootswatch/'.get_theme_mod( 'ptibogxivtheme_css').'/';
$version='4.3.1';  
}

	wp_enqueue_style( 'bootstrap', plugins_url( 'doliconnect/includes/css/'.$css.'bootstrap.min.css'), array(), $version);
  
	wp_enqueue_script( 'bootstrap-js', plugins_url( 'doliconnect/includes/js/scripts.min.js'), array('jquery'), ' ', true );

	wp_enqueue_script( 'font-awesome', '//use.fontawesome.com/releases/v5.9.0/js/all.js', array(), '5.9.0' );
  
  wp_enqueue_style( 'bootstrap-social', plugins_url( 'doliconnect/includes/css/bootstrap-social.css'), array(), $version); 

} 

?>