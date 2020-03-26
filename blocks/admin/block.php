<?php

function doliconnect_admin_block_render( $attributes, $content ) {

$args = array( 
'blog_id'      => $GLOBALS['blog_id'],
'role'         => 'administrator',
'meta_key' => 'doliboard_'.get_current_blog_id(),
'orderby' => 'meta_value',
'order'        => 'ASC',
);

$user_query = new WP_User_Query( $args );
$html = "<div class='row'>";
if ( ! empty( $user_query->results ) ) {
foreach ( $user_query->results as $user ) {
$html .= "<div class='";
if( !empty($attributes['col']) && $attributes['col'] == '3' ) {
$html .= "col-12 col-md-6 col-lg-4";
} else { $html .= "col-12 col-md-6 col-lg-6"; }


$order1="doliboard_".get_current_blog_id();
$order=$user->$order1;

$style = !empty($attributes['adminCardStyle']) ? $attributes['adminCardStyle'] : '';

$html .= "'><div class='card ".$style." mb-3 shadow-sm'>
<div class='card-body'>
<div class='row'><div class='col-4'>".get_avatar($user->ID, 100)."</div><div class='col-8 text-justify'><h6>" . esc_html( $user->user_firstname ) . ' ' . esc_html( $user->user_lastname ) . "</h6>".get_option('doliboard_title_'.$order)."<br/>".substr( get_the_author_meta('description',$user->ID) , 0 , 100) . "</div></div></div><div class='card-footer'>";
if ($user->facebook) { 
$html .= '<a href="https://www.facebook.com/'.$user->facebook.'" target="_blank"><i class="fab fa-facebook-square fa-2x fa-fw"></i></a> ';}
if ($user->twitter) { 
$html .= '<a href="https://www.twitter.com/'.$user->twitter.'" target="_blank"><i class="fab fa-twitter-square fa-2x fa-fw"></i></a> ';}
if ($user->linkedin) { 
$html .= '<a href="https://www.linkedin.com/'.$user->linkedin.'" target="_blank"><i class="fab fa-linkedin fa-2x fa-fw"></i></a>';}
$html .= "</div>
</div></div>";
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
$html .= "<div class='";
if(is_active_sidebar('sidebar-widget-area')){
$html .= "col-12 col-md-6 col-lg-6";
}else{
$html .= "col-12 col-md-6 col-lg-4";
}
$order1="doliboard_".get_current_blog_id();
$order=$user->$order1;
$html .= "'><div class='card card ".$style." mb-3 shadow-sm mb-3'>
<div class='card-body'>
<div class='row'><div class='col-4'>".get_avatar($user->ID, 100)."</div><div class='col-8 text-justify'>".get_option('doliboard_title_'.$order)."<br/>".substr( get_the_author_meta('description',$user->ID) , 0 , 100) . "";
if ($user->facebook) { 
$html .= '<a href="https://www.facebook.com/'.$user->facebook.'" target="_blank"><i class="fab fa-facebook-square fa-2x fa-fw"></i></a> ';}
if ($user->twitter) { 
$html .= '<a href="https://www.twitter.com/'.$user->twitter.'" target="_blank"><i class="fab fa-twitter-square fa-2x fa-fw"></i></a> ';}
if ($user->linkedin) { 
$html .= '<a href="https://www.linkedin.com/'.$user->linkedin.'" target="_blank"><i class="fab fa-linkedin fa-2x fa-fw"></i></a>';}
$html .= "</div></div></div><div class='card-footer'><h6>" . esc_html( $user->user_firstname ) . ' ' . esc_html( $user->user_lastname ) . "</h6></div>
</div></div>";
}
}
$html .= "</div>";
$html .= '<!-- Button trigger modal -->
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
  Launch demo modal
</button>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>';
return $html;
}
function doliconnect_admin_block_init() {
	if ( function_exists( 'register_block_type' ) ) {
		wp_register_script(
			'admin-block',
			plugins_url( 'block.js', __FILE__ ),
			array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-components' ),
      'beta3'
		);
		register_block_type(
			'doliconnect/admin-block',
			array(
				'editor_script'   => 'admin-block',
				'render_callback' => 'doliconnect_admin_block_render',
			)
		);
	}
}
add_action( 'init', 'doliconnect_admin_block_init' );
