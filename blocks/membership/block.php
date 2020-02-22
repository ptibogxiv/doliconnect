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


function doliconnect_membership_block() {

	// Scripts.
	wp_register_script(
		'doliconnect-membership-block-script', // Handle.
		plugins_url( 'block.js', __FILE__ ), // Block.js: We register the block here.
		array( 'wp-blocks', 'wp-element', 'wp-i18n' )
    );

function doliconnect_membership_render_block( $attributes ) {
global $current_user;

$delay=MONTH_IN_SECONDS;

doliconnect_enqueues(); 

$current_offset = get_option('gmt_offset');
$tzstring = get_option('timezone_string');
$check_zone_info = true;
// Remove old Etc mappings. Fallback to gmt_offset.
if ( false !== strpos($tzstring,'Etc/GMT') )
	$tzstring = '';

if ( empty($tzstring) ) { // Create a UTC+- zone if no timezone string exists
	$check_zone_info = false;
	if ( 0 == $current_offset )
		$tzstring = 'UTC+0';
	elseif ($current_offset < 0)
		$tzstring = 'UTC' . $current_offset;
	else
		$tzstring = 'UTC+' . $current_offset;
}
//define( 'MY_TIMEZONE', (get_option( 'timezone_string' ) ? get_option( 'timezone_string' ) : date_default_timezone_get() ) );
//date_default_timezone_set( MY_TIMEZONE );
date_default_timezone_set($tzstring);

$html = "";

if (is_user_logged_in() && doliconnector($current_user, 'fk_member') > 0){
$adherent = callDoliApi("GET", "/adherentsplus/".doliconnector($current_user, 'fk_member'), null, dolidelay('member', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), true));
}

$request = "/adherentsplus/type?sortfield=t.libelle&sortorder=ASC&sqlfilters=(t.morphy%3A=%3A'')%20or%20(t.morphy%3Ais%3Anull)%20or%20(t.morphy%3A%3D%3A'phy')";

$typeadhesion = callDoliApi("GET", $request, null, dolidelay('member', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), true));

$typeadhesionpro = callDoliApi("GET", "/adherentsplus/type?sortfield=t.libelle&sortorder=ASC&sqlfilters=(t.morphy%3A=%3A'')%20or%20(t.morphy%3Ais%3Anull)%20or%20(t.morphy%3A%3D%3A'mor')", null, dolidelay('member', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), true));

if ( isset($typeadhesionpro->error) && $typeadhesionpro->error->code != '404' ) {
$html .= '<center><ul class="nav nav-pills nav-justified" id="pills-tab" role="tablist">
<li class="nav-item"><a class="nav-link active" id="pills-home-tab" data-toggle="pill" href="#pills-home" role="tab" aria-controls="pills-home" aria-selected="true">'.__( 'Individual', 'doliconnect' ).'</a></li>
<li class="nav-item"><a class="nav-link" id="pills-group-tab" data-toggle="pill" href="#pills-group" role="tab" aria-controls="pills-group" aria-selected="false">'.__( 'Company', 'doliconnect' ).'</a></li>
</ul></center><br>';
}

$html .= '<div class="tab-content" id="pills-tabContent"><div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">';
      
if ( !isset($typeadhesion->error) ) {
if ( count($typeadhesion) < 4 ) {
$html .= '<div class="card-deck mb-3 text-center">';
} else {
$html .= '<table class="table table-striped"><tbody>';
}
foreach ( $typeadhesion as $postadh ) {
if ($postadh->subscription == '1'){

if ( (! is_user_logged_in() && $postadh->automatic == '1') or ($postadh->automatic == '1' && $postadh->id == $adherent->typeid or $postadh->id == $adherent->typeid )) {
$color="-success";
} elseif ($postadh->automatic != '1') {
$color="-danger";
} else { $color="-warning"; }

$tx=1;
$montantdata=($tx*$postadh->price)+$postadh->welcome;
$montant1 = $postadh->price;
$montant2 = $tx*$postadh->price;

if ( count($typeadhesion) < 4 ) {

$html .= '<DIV class="card border'.$color.' mb-4 box-shadow"><div class="card-header"><h4 class="my-0 font-weight-normal">'.doliproduct($postadh, 'label').'</h4></div><div class="card-body">'; 
$html .= '<h1 class="card-title pricing-card-title">'.doliprice($postadh->price).'<small class="text-muted">/';
if (! empty ($postadh->duration_value)) { $html .= doliduration($postadh); }
else {
$html .= __( 'year', 'doliconnect');
}
$html .= '</small></h1>';

if ( !isset($adherent) or (($postadh->welcome > '0') && isset($adherent) && ($adherent->datefin == null )) or (($postadh->welcome > '0') && isset($renewadherent) && (current_time( 'timestamp',1) > $renewadherent) && isset($adherent) && (current_time( 'timestamp',1) > $adherent->datefin)) ) {          
$html .= "<small>".__( 'First subscription at', 'doliconnect' )." ".doliprice($montantdata)."</small>"; 
}   
$html .= doliproduct($postadh, 'note').'</div>';

if ( function_exists('dolimembership_modal') ) {
$html .= '<div class="card-footer"><a href="'.doliconnecturl('doliaccount').'?module=members" role="button" class="btn btn-block btn'.$color.'">'.__( 'Subscribe', 'doliconnect' ).'</a></div>';
}

$html .= '</div>';

} else {
 
$html .= "<tr><td><div class='row'><div class='col-md-8'><b>";
if ( $postadh->family == '1' ) {
$html .= "<i class='fas fa-users fa-fw'></i> ";
}else{$html .= "<i class='fas fa-user fa-fw'></i> ";}
$html .= doliproduct($postadh, 'label');
if (! empty ($postadh->duration_value)) $html .= " - ".doliduration($postadh);
$html .= " <small>";
if ((($postadh->welcome > '0') && ($adherent->datefin == null )) or (($postadh->welcome > '0') && (current_time( 'timestamp',1) > $renewadherent) && (current_time( 'timestamp',1) > $adherent->datefin))) { 
$html .= "(";
$html .= doliprice($montantdata)." ";
$html .=  __( 'then', 'doliconnect' )." ".doliprice($montant1)." ".__( 'yearly', 'doliconnect' ); 
} else {
$html .= "(".doliprice($montant1);
$html .= " ".__( 'yearly', 'doliconnect' );
} 
$html .= ")</small>";
if (!empty(doliproduct($postadh, 'note'))) $html .= "<br><small class='text-justify text-muted '>".doliproduct($postadh, 'note')."</small>";
if (!empty(number_format($postadh->federal))) $html .= "<br><small class='text-justify text-muted '>".__( 'Including a federal part of', 'doliconnect-pro')." ".doliprice($postadh->federal)."</small>";
$html .= "</div>";
if ( function_exists('dolimembership_modal') ) {
$html .= '<div class="col-md-4"><a href="'.doliconnecturl('doliaccount').'?module=members" role="button" class="btn btn-block btn'.$color.'">'.__( 'Subscribe', 'doliconnect' ).'</a></div>';
}

$html .= "</div></td></tr>"; 
}

}}
if ( count($typeadhesion) < 4 ) {
$html .= '</div>';
} else {
$html .= '</tbody></table>';
}
}

$html .= '</div><div class="tab-pane fade" id="pills-group" role="tabpanel" aria-labelledby="pills-group-tab">';

if ( !isset($typeadhesionpro->error) ) {
$html .= '<div class="card-deck mb-3 text-center">';
foreach ( $typeadhesionpro as $postadh ) {
$html .= '<div class="card border-info mb-4 box-shadow"><div class="card-header"><h4 class="my-0 font-weight-normal">'.doliproduct($postadh, 'label').'</h4></div><div class="card-body">
<h1 class="card-title pricing-card-title">'.doliprice($postadh->price).'<small class="text-muted">/';
if (! empty ($postadh->duration_value)) { $html .= doliduration($postadh); }
else {
$html .= __( 'year', 'doliconnect' );
}
$html .= '</small></h1>';

if ( !isset($adherent) or (($postadh->welcome > '0') && isset($adherent) && ($adherent->datefin == null )) or (($postadh->welcome > '0') && isset($renewadherent) && (current_time( 'timestamp',1) > $renewadherent) && isset($adherent) && (current_time( 'timestamp',1) > $adherent->datefin)) ) {          
$html .= "<small>".__( 'First subscription at', 'doliconnect' )." ".doliprice($montantdata)."</small>"; 
}   
$html .= doliproduct($postadh, 'note').'</div>';

if ( function_exists('dolimembership_modal') ) {
$html .= '<div class="card-footer"><a href="'.doliconnecturl('dolicontact').'?type=COM" role="button" class="btn btn-lg btn-block btn-info">'.__( 'Contact us', 'doliconnect' ).'</a></div>';
}

$html .= '</div>';
}
$html .= '</div>';
}

$html .= '</div></div>';

$html .= "<small><div class='float-left'>";
$html .= dolirefresh($request, get_permalink(), dolidelay('thirdparty'), $typeadhesion);
$html .= "</div><div class='float-right'>";
$html .= dolihelp('ISSUE');
$html .= "</div></small>";
 
$html .= '<br>';

return $html;
}

	// We also specify the editor script to be used in the Gutenberg interface
	register_block_type( 'doliconnect/membership-block', array(
				'render_callback' => 'doliconnect_membership_render_block',
				'editor_script'   => 'doliconnect-membership-block-script',
				'attributes'      => array(
					'request_type' => array(
						'type' => 'string',
					),
				),
	) );

} // End function organic_profile_block().

// Hook: Editor assets.
add_action( 'init', 'doliconnect_membership_block' );