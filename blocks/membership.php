<?php
/**
 * BLOCK: admin
 *
 * Gutenberg Custom admin Block assets.
 *
 * @since   1.0.0
 * @package OPB
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function doliconnect_register_membership_blocks() {
    register_block_type(
        'doliconnect/membership-block',
        array(
            'title'          => __( 'Membership', 'doliconnect'),
			'description'	 => __( 'A block for displaying membership.', 'doliconnect'),
			'icon'			 => 'tickets-alt',
			'category'		 => 'widgets',
            'attributes'      => array(
                'title'   => array(
                    'label'   => __( 'Title', 'myplugin' ),
                    'type'    => 'string',
                    'default' => 'Hello World',
                ),
                'count'   => array(
                    'label'   => __( 'Count', 'myplugin' ),
                    'type'    => 'integer',
                    'default' => 5,
                ),
                'enabled' => array(
                    'label'   => __( 'Enabled?', 'myplugin' ),
                    'type'    => 'boolean',
                    'default' => true,
                ),
                'size'    => array(
                    'label'   => __( 'Size', 'myplugin' ),
                    'type'    => 'string',
                    'enum'    => array( 'small', 'medium', 'large' ),
                    'default' => 'medium',
                ),
            ),
            'render_callback' => function ( $attributes ) {
                return sprintf(
                    __( '<p>%s: %d items (%s)</p>', 'myplugin' ),
                    esc_html( $attributes['title'] ),
                    $attributes['count'],
                    $attributes['size']
                );
            },
            'supports'        => array(
                'autoRegister' => true,
            ),
        )
    );
}

add_action( 'init', 'doliconnect_register_membership_blocks' );

/*
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

$adherent = doliConnect('member', $current_user, false, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));

if (isset($adherent->id) && $adherent->id > 0) {
	$member_id = "member_id=".$adherent->id;
} else {
	$member_id = '';
}

if (!empty($current_user->billing_type)) {
	$morphy = "&sqlfilters=(t.morphy:=:'')or(t.morphy:is:null)or(t.morphy:=:'".$current_user->billing_type."')";
} else {
	$morphy = '';
}
$request = "/adherentsplus/type?sortfield=t.libelle&sortorder=ASC&".$member_id.$morphy;
$typeadhesion = callDoliApi("GET", $request, null, dolidelay('member', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), true));

if ( !isset($typeadhesion->error) ) {
	$html .= '<div class="card"><div class="card-header">'.__( 'Prices', 'doliconnect').' '.$typeadhesion[0]->season.'</div>';
	$html .= dolimembertypelist($typeadhesion, $adherent);
	$html .= '<div class="card-body"><small>'.__( 'Note: the admins reserve the right to change your membership in relation to your personal situation. A validation of the membership may be necessary depending on the cases.', 'doliconnect').'</small></div>';
}

$html .= doliCardFooter($typeadhesion , 'member', $request);
$html .= "</div>";
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
*/
