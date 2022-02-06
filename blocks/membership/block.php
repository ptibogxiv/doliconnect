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
} else {
$adherent = (object) 0;
$adherent->typeid = 0;
}

$member_id = '';
if (isset($adherent) && $adherent->id > 0) $member_id = "member_id=".$adherent->id;
$morphy = '';

$request = "/adherentsplus/type?sortfield=t.libelle&sortorder=ASC&nature=all&".$member_id.$morphy;
$typeadhesion = callDoliApi("GET", $request, null, dolidelay('member', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), true));

if ( !isset($typeadhesion->error) ) {

$html .= '<div class="card"><div class="card-header">'.__( 'Prices', 'doliconnect').' '.$typeadhesion[0]->season.'</div>';
$html .= dolimembertypelist($typeadhesion, $adherent);
$html .= '<div class="card-body"><small>'.__( 'Note: the admins reserve the right to change your membership in relation to your personal situation. A validation of the membership may be necessary depending on the cases.', 'doliconnect').'</small></div>';

}

$html .= "<div class='card-footer text-muted'>";
$html .= "<small><div class='float-start'>";
$html .= dolirefresh($request, get_permalink(), dolidelay('thirdparty'), $typeadhesion);
$html .= "</div><div class='float-end'>";
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
