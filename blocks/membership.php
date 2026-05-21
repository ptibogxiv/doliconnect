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
                global $current_user;
                doliconnect_enqueues();
                $html = "";
                $adherent = doliConnect('member', $current_user, false);

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
                $typeadhesion = callDoliApi("GET", $request, null, dolidelay('member'));

                if ( !isset($typeadhesion->error) ) {
                    $html .= '<div class="card"><div class="card-header">'.__( 'Prices', 'doliconnect').' '.$typeadhesion[0]->season.'</div>';
                    $html .= dolimembertypelist($typeadhesion, $adherent);
                    $html .= '<div class="card-body"><small>'.__( 'Note: the admins reserve the right to change your membership in relation to your personal situation. A validation of the membership may be necessary depending on the cases.', 'doliconnect').'</small></div>';
                }

                $html .= doliCardFooter($typeadhesion , 'member', $request);
                $html .= "</div>";
                return $html;
                //return sprintf(
                //    __( '<p>%s: %d items (%s)</p>', 'myplugin' ),
                //    esc_html( $attributes['title'] ),
                //    $attributes['count'],
                //    $attributes['size']
                //);
            },
            'supports'        => array(
                'autoRegister' => true,
            ),
        )
    );
}

add_action( 'init', 'doliconnect_register_membership_blocks' );