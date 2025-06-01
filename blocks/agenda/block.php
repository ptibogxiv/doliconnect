<?php
/**
 * Plugin Name: DoliConnect Agenda Block
 * Description: A custom block for displaying agenda items.
 * Version: 1.0.0
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Registers the agenda block.
 *
 * @package DoliConnect
 */

// Enqueue block editor assets
function agenda_block_render_callback( $attributes ) {
	$limit=5;
	$page=0;
    $request= "/agendaevents?sortfield=t.datep&sortorder=ASC&limit=".$limit."&page=".$page."&sqlfilters=(t.datep2%3A%3E%3D%3A'".date("Ymd")."')&pagination_data=true";
    $object = callDoliApi("GET", $request, null, dolidelay('agenda', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));  
    if ( doliversion('21.0.0') && isset($object->data) ) { $listagenda = $object->data; } else { $listagenda = $object; }

      $block ="<div class='card shadow-sm'><div class='card-header'>".sprintf(__( 'Next %s events', 'doliconnect'), $limit)."</div><ul class='list-group list-group-flush'>";

      if ( !isset($listagenda->error) && $listagenda != null ) {
        foreach ($listagenda as $postagenda) {
          $nonce = wp_create_nonce( 'doli-agenda-'.$postagenda->id);
          $arr_params = array( 'id' => $postagenda->id, 'security' => $nonce);  
          $return = esc_url( add_query_arg( $arr_params, doliconnecturl('doliagenda')) );

          $block .="<a href='".$return."' class='list-group-item d-flex justify-content-between lh-condensed list-group-item-light list-group-item-action'>";
          $block .="<div><i class='fa-solid fa-calendar-days fa-3x fa-fw'></i></div><div>";                                                                                
          $block .="<h6 class='my-0'>$postagenda->label</h6><small class='text-muted'>$postagenda->location ".date('d/m/Y',$postagenda->datep)." ".date('d/m/Y',$postagenda->datef)."</small>";
          $block .="</div></a>";
        }
      } else {
        $block .="<li class='list-group-item list-group-item-light'><center>".__( 'No event', 'doliconnect')."</center></li>";
      }
      $block .="</ul>";
      $block .=doliCardFooter($object, 'agenda');
      $block .="</div>";
    return $block;
}

// Register the block
function agenda_block_register() {
    // Register the block editor script
    wp_register_script(
        'agenda-block-script',
        plugins_url( 'block.js', __FILE__ ),
        array( 'wp-blocks', 'wp-element', 'wp-editor' ),
        '1.0.0',
        true
    );

    // Register the block
    register_block_type( 'doliconnect/agenda-block', array(
        'editor_script' => 'agenda-block-script',
        'render_callback' => 'agenda_block_render_callback', // Optional for dynamic blocks
    ) );
}
add_action( 'init', 'agenda_block_register' );
?>