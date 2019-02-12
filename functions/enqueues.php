<?php

function doliconnect_enqueues() { 

if ( empty(get_theme_mod( 'ptibogxivtheme_css')) || get_theme_mod( 'ptibogxivtheme_css') == 'css' ) {
$type='bootstrap';
$css='css';
$version='4.3.0'; 
} else {
$type='bootswatch';
$css=get_theme_mod( 'ptibogxivtheme_css');
$version='4.2.1';  
}

	wp_enqueue_style( 'bootstrap', plugins_url( 'doliconnect/includes/css/'.$type.'/'.$css.'/bootstrap.min.css'), array(), $version);
  
	wp_enqueue_script( 'bootstrap-js', plugins_url( 'doliconnect/includes/js/scripts.min.js'), array('jquery'), ' ', true );

	wp_enqueue_script( 'font-awesome', '//use.fontawesome.com/releases/v5.7.1/js/all.js', array(), '5.7.1' );
  
  wp_enqueue_style( 'bootstrap-social', plugins_url( 'doliconnect/includes/css/bootstrap-social.css'), array(), $version); 

} 

?>