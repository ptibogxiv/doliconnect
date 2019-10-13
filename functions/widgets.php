<?php
// ******************WIDGET********************************

class My_doliconnect extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array( 
			'classname' => 'my_doliconnect',                               
			'description' => 'Soumission de bug',
      'customize_selective_refresh' => true,
		);
		parent::__construct( 'my_doliconnect', 'SOS Bug (Doliconnect)', $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
public function widget( $args, $instance ) {
global $wpdb;
		
print $args['before_widget'];
if ( ! empty( $instance['title'] ) ) {
  print $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
}

$time=current_time('timestamp');

if (is_user_logged_in()){ 
  print "<a class='btn btn-block btn-warning' href='".doliconnecturl('doliaccount') . "?module=ticket&type=ISSUE&create' ><span class='fa fa-bug fa-fw'></span> ".__( 'Report a Bug', 'doliconnect' )."</a>";
} else {
  print "<a class='btn btn-block btn-warning' href='".esc_url( add_query_arg( 'type', 'issue', doliconnecturl('dolicontact')) ) . "' ><span class='fa fa-bug fa-fw'></span> ".__( 'Report a Bug', 'doliconnect' )."</a>";
} 
  print $args['after_widget'];  
}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Offre d\'emploi', 'text_domain' );
		?>
		<p>
		<label for="<?php print esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'text_domain' ); ?></label> 
		<input class="widefat" id="<?php print esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php print esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php print esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 *
	 * @return array
	 */
/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

}

add_action( 'widgets_init', function(){
	register_widget( 'My_doliconnect' );
});

class My_doliconnect_Membership extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array( 
			'classname' => 'my_doliconnect_membership',                               
			'description' => 'lightbox adhesion',
      'customize_selective_refresh' => true,
		);
		parent::__construct( 'my_doliconnect_membership', 'Adhesion (Doliconnect)', $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
public function widget( $args, $instance ) {
global $current_user, $wpdb;
		// outputs the content of the widget
    
  		print $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
print $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

if (doliconnector($current_user, 'fk_member') > 0) {
$adherent = callDoliApi("GET", "/adherentsplus/".doliconnector($current_user, 'fk_member'), null);
}
 
if ($adherent->statut == '1' && $adherent->datefin < current_time('timestamp')) {
print "<A class='btn btn-block btn-success' href='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' >".__( 'Pay my subscription', 'doliconnect' )."</a>"; 
}
elseif ($adherent->statut == '0') {
print "<a class='btn btn-block btn-info' href='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' >".__( 'Subscribe', 'doliconnect' )."</a>"; 
}
elseif ($adherent->statut == '-1') {
print "<a class='btn btn-block btn-warning disabled' href='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' >".__( 'Membership', 'doliconnect' )."</a>";//requested 
}
elseif (!$adherent->id > 0) {
print "<a class='btn btn-block btn-success' href='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' >".__( 'Subscribe', 'doliconnect' )."</a>"; 
}


print $args['after_widget'];  
    
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		$title = '';
		?>
		<p>
		<label for="<?php print esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'text_domain' ); ?></label> 
		<input class="widefat" id="<?php print esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php print esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php print esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 *
	 * @return array
	 */
/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

}

add_action( 'widgets_init', function(){
	register_widget( 'My_doliconnect_Membership' );
});

class Doliconnect_Changelang extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array( 
			'classname' => 'Doliconnect_Changelang',                               
			'description' => 'Modal for change lang',
      'customize_selective_refresh' => true,
		);
		parent::__construct( 'Doliconnect_Changelang', __('Change language', 'doliconnect').' (Doliconnect)', $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
public function widget( $args, $instance ) {
global $current_user, $wpdb;
		// outputs the content of the widget
    
print $args['before_widget'];
if ( ! empty( $instance['title'] ) ) {
print $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
}

if ( function_exists('pll_the_languages') && function_exists('doliconnect_langs') ) {      
print '<a href="#" data-toggle="modal" data-target="#DoliconnectSelectLang" data-dismiss="modal" title="'.__('Choose language', 'doliconnect').'">'.pll_current_language('flag').' '.pll_current_language('name').'</a>';
}

print $args['after_widget'];  
    
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		$title = '';
		?>
		<p>
		<label for="<?php print esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'text_domain' ); ?></label> 
		<input class="widefat" id="<?php print esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php print esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php print esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 *
	 * @return array
	 */
/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}

}

add_action( 'widgets_init', function(){
	register_widget( 'Doliconnect_Changelang' );
});
?>