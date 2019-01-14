<?php
/**
 * BLOCK: admin
 *
 * Gutenberg Custom admin Block assets.
 *
 * @since   1.0.0
 * @package OPB
 */

// Exit if accessed directly.
// if ( ! defined( 'ABSPATH' ) ) {
// 	exit;
// }

/**
 * Enqueue the block's assets for the editor.
 *
 * `wp-blocks`: Includes block type registration and related functions.
 * `wp-element`: Includes the WordPress Element abstraction for describing the structure of your blocks.
 * `wp-i18n`: To internationalize the block's text.
 *
 * @since 1.0.0
 */


function doliconnect_admin_block() {

	// Scripts.
	wp_register_script(
		'doliconnect-admin-block-script', // Handle.
		plugins_url( 'block.js', __FILE__ ), // Block.js: We register the block here.
		array( 'wp-blocks', 'wp-element', 'wp-i18n' ), // Dependencies, defined above.
    'beta5'
	);

	// Styles.
	wp_register_style(
		'doliconnect-admin-block-editor-style', // Handle.
		plugins_url( 'editor.css', __FILE__ ), // Block editor CSS.
		array( 'wp-edit-blocks' ), // Dependencies, defined above.
    'beta5'
	);
  
	wp_register_style(
		'doliconnect-admin-block-frontend-style', // Handle.
		plugins_url( 'style.css', __FILE__ ), // Block editor CSS.
		array( 'wp-edit-blocks' ) // Dependency to include the CSS after it.
	);

function doliconnect_admin_render_block( $attributes ) {
$a = shortcode_atts( array(
'col' => 'col'
), $atts );
$args = array( 
'blog_id'      => $GLOBALS['blog_id'],
'role'         => 'administrator',
'meta_key' => 'doliboard_'.get_current_blog_id(),
'orderby' => 'meta_value',
'order'        => 'ASC',
);

$user_query = new WP_User_Query( $args );
$html = "<DIV class='row'>";
if ( ! empty( $user_query->results ) ) {
foreach ( $user_query->results as $user ) {
$html .= "<DIV class='";
if($a[col]=='3'){
$html .= "col-12 col-md-6 col-lg-4";
}else{$html .= "col-12 col-md-6 col-lg-6";}


$order1="doliboard_".get_current_blog_id();
$order=$user->$order1;
$html .= "'><DIV class='card ".$attributes['adminCardStyle']." mb-3 shadow-sm'>
<DIV class='card-body'>
<DIV class='row'><DIV class='col-4'>".get_avatar($user->ID, 100)."</DIV><DIV class='col-8 text-justify'><H6>" . esc_html( $user->user_firstname ) . ' ' . esc_html( $user->user_lastname ) . "</H6>".get_option('doliboard_title_'.$order)."<br/>".substr( get_the_author_meta('description',$user->ID) , 0 , 100) . "</DIV></DIV></DIV><DIV class='card-footer'>";
if ($user->facebook) { 
$html .= '<A href="https://www.facebook.com/'.$user->facebook.'" target="_blank"><I class="fab fa-facebook-square fa-2x fa-fw"></I></A> ';}
if ($user->twitter) { 
$html .= '<A href="https://www.twitter.com/'.$user->twitter.'" target="_blank"><I class="fab fa-twitter-square fa-2x fa-fw"></I></A> ';}
if ($user->linkedin) { 
$html .= '<A href="https://www.linkedin.com/'.$user->linkedin.'" target="_blank"><I class="fab fa-linkedin fa-2x fa-fw"></I></A>';}
$html .= "</DIV>
</DIV></DIV>";
}
}else{
$html .= 'No admins found!';
}
$args = array( 
'blog_id'      => $GLOBALS['blog_id'],
'role'         => 'editor',
'meta_key' => 'doliboard_'.get_current_blog_id(),
'orderby' => 'meta_value',
'order'        => 'ASC',
);

$user_query = new WP_User_Query( $args );

if ( ! empty( $user_query->results ) ) {
foreach ( $user_query->results as $user ) {
$html .= "<DIV class='";
if(is_active_sidebar('sidebar-widget-area')){
$html .= "col-12 col-md-6 col-lg-6";
}else{
$html .= "col-12 col-md-6 col-lg-4";
}
$order1="doliboard_".get_current_blog_id();
$order=$user->$order1;
$html .= "'><DIV class='card card ".$attributes['adminCardStyle']." mb-3 shadow-sm mb-3'>
<DIV class='card-body'>
<DIV class='row'><DIV class='col-4'>".get_avatar($user->ID, 100)."</DIV><DIV class='col-8 text-justify'>".get_option('doliboard_title_'.$order)."<br/>".substr( get_the_author_meta('description',$user->ID) , 0 , 100) . "";
if ($user->facebook) { 
$html .= '<A href="https://www.facebook.com/'.$user->facebook.'" target="_blank"><I class="fab fa-facebook-square fa-2x fa-fw"></I></A> ';}
if ($user->twitter) { 
$html .= '<A href="https://www.twitter.com/'.$user->twitter.'" target="_blank"><I class="fab fa-twitter-square fa-2x fa-fw"></I></A> ';}
if ($user->linkedin) { 
$html .= '<A href="https://www.linkedin.com/'.$user->linkedin.'" target="_blank"><I class="fab fa-linkedin fa-2x fa-fw"></I></A>';}
$html .= "</DIV></DIV></DIV><DIV class='card-footer'><H6>" . esc_html( $user->user_firstname ) . ' ' . esc_html( $user->user_lastname ) . "</H6></DIV>
</DIV></DIV>";
}
}
$html .= "</DIV>";
return $html;
}

	// Here we actually register the block with WP, again using our namespacing
	// We also specify the editor script to be used in the Gutenberg interface
	register_block_type( 'doliconnect/admin-block', array(
    'render_callback' => 'doliconnect_admin_render_block',
		'editor_script' => 'doliconnect-admin-block-script',
		'editor_style' => 'doliconnect-admin-block-editor-style',
		'style' => 'doliconnect-admin-block-frontend-style',
	) );

} // End function organic_profile_block().

// Hook: Editor assets.
add_action( 'init', 'doliconnect_admin_block' );
