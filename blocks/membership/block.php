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

doliconnect_enqueues(); 

$html = "";

if (is_user_logged_in() && doliconnector($current_user, 'fk_member') > 0){
$adherent = callDoliApi("GET", "/adherentsplus/".doliconnector($current_user, 'fk_member'), null, dolidelay('member', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), true));
}

$request = "/adherentsplus/type?sortfield=t.libelle&sortorder=ASC"; //&sqlfilters=(t.morphy%3A=%3A'')%20or%20(t.morphy%3Ais%3Anull)%20or%20(t.morphy%3A%3D%3A'phy')
$typeadhesion = callDoliApi("GET", $request, null, dolidelay('member', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), true));

if ( !isset($typeadhesion->error) ) {
if ( count($typeadhesion) < 4 ) {
$html .= '<div class="card-deck mb-3 text-center">';
} else {
$html .= '<div class="card"><div class="card-header">'.__( 'Prices', 'doliconnect').' '.$typeadhesion[0]->season.'</div><table class="table table-striped"><tbody>';
}
foreach ( $typeadhesion as $postadh ) {
if ($postadh->subscription == '1'){

if ( (! is_user_logged_in() && $postadh->automatic == '1') or ($postadh->automatic == '1' && $postadh->id == $adherent->typeid or $postadh->id == $adherent->typeid )) {
$color="-success";
} elseif ($postadh->automatic != '1') {
$color="-danger";
} else { $color="-warning"; }

if ( count($typeadhesion) < 4 ) {

$html .= '<div class="card border'.$color.' mb-4 box-shadow"><div class="card-header"><h4 class="my-0 font-weight-normal">'.doliproduct($postadh, 'label').'</h4></div><div class="card-body">'; 
$html .= '<h1 class="card-title pricing-card-title">'.doliprice($postadh->price_prorata).'<small class="text-muted">/';
$html .= doliduration($postadh);
$html .= '</small></h1>';

if ( !isset($adherent) or (($postadh->welcome > '0') && isset($adherent) && ($adherent->datefin == null )) or (($postadh->welcome > '0') && (current_time( 'timestamp',1) > $adherent->next_subscription_renew) && isset($adherent) && (current_time( 'timestamp',1) > $adherent->datefin)) ) {          
$html .= "<small>".__( 'First subscription at', 'doliconnect' )." ".doliprice($postadh->price_prorata)."</small>"; 
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
if ($postadh->price_prorata != $postadh->price) { 
$html .= "(";
$html .= doliprice($postadh->price_prorata)." ";
$html .=  __( 'then', 'doliconnect' )." ".doliprice($postadh->price);
} else {
$html .= "(".doliprice($postadh->price_prorata);
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

$html .= '</tbody></table>';

}
$html .= "<div class='card-footer text-muted'>";
$html .= "<small><div class='float-left'>";
$html .= dolirefresh($request, get_permalink(), dolidelay('thirdparty'), $typeadhesion);
$html .= "</div><div class='float-right'>";
$html .= dolihelp('ISSUE');
$html .= "</div></small>";
$html .= "</div></div>";
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
