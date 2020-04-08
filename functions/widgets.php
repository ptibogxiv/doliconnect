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
  print "<a class='btn btn-block btn-warning' href='".doliconnecturl('doliaccount') . "?module=ticket&type=ISSUE&create' ><span class='fa fa-bug fa-fw'></span> ".__( 'Report a Bug', 'doliconnect')."</a>";
} else {
  print "<a class='btn btn-block btn-warning' href='".esc_url( add_query_arg( 'type', 'issue', doliconnecturl('dolicontact')) ) . "' ><span class='fa fa-bug fa-fw'></span> ".__( 'Report a Bug', 'doliconnect')."</a>";
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
		<label for="<?php print esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'doliconnect'); ?></label> 
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
print "<A class='btn btn-block btn-success' href='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' >".__( 'Pay my subscription', 'doliconnect')."</a>"; 
}
elseif ($adherent->statut == '0') {
print "<a class='btn btn-block btn-info' href='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' >".__( 'Subscribe', 'doliconnect')."</a>"; 
}
elseif ($adherent->statut == '-1') {
print "<a class='btn btn-block btn-warning disabled' href='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' >".__( 'Membership', 'doliconnect')."</a>";//requested 
}
elseif (!$adherent->id > 0) {
print "<a class='btn btn-block btn-success' href='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' >".__( 'Subscribe', 'doliconnect')."</a>"; 
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
		<label for="<?php print esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'doliconnect'); ?></label> 
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

class Doliconnect_DoliMenu extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array( 
			'classname' => 'Doliconnect_DoliMenu',                               
			'description' => 'Links to account and cart',
      'customize_selective_refresh' => true,
		);
		parent::__construct( 'Doliconnect_DoliMenu', __('Account & Cart', 'doliconnect').' (Doliconnect)', $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
public function widget( $args, $instance ) {
    
print $args['before_widget'];
if ( ! empty( $instance['title'] ) ) {
print $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
}

if ( doliconnectid('doliaccount') > 0 ) { 
print '<a href="'.doliconnecturl('doliaccount').'" title="'.__('My account', 'doliconnect').'"><i class="fas fa-user-circle fa-fw fa-2x"></i></a>';
} 

if ( doliconnectid('dolicart') > 0 ) { 
print '<a href="'.doliconnecturl('dolicart').'" title="'.__('Basket', 'doliconnect').'"><span class="fa-layers fa-fw fa-2x">
<i class="fas fa-shopping-bag"></i><span class="fa-layers-counter fa-lg" id="DoliWidgetCarItems" style="background:Tomato">'.(!empty(doliconnector( null, 'fk_order_nb_item'))?doliconnector( null, 'fk_order_nb_item'):'0').'</span></span></a>';  
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
		<label for="<?php print esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'doliconnect'); ?></label> 
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
	register_widget( 'Doliconnect_DoliMenu' );
});

class Doliconnect_DoliShop extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array( 
			'classname' => 'Doliconnect_DoliShop',                               
			'description' => 'List of product\'s categories',
      'customize_selective_refresh' => true,
		);
		parent::__construct( 'Doliconnect_DoliShop', __('Category of products', 'doliconnect').' (Doliconnect)', $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
public function widget( $args, $instance ) {

if ( !empty($instance['display']) || (empty($instance['display']) && is_page(doliconnectid('dolishop')) && !empty(doliconnectid('dolishop'))) ) { 
  
print $args['before_widget'];
if ( ! empty( $instance['title'] ) ) {
print $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
}

$shop = doliconst("DOLICONNECT_CATSHOP", esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
//print $shop;

if ( $shop != null ) {

print '<form role="search" method="get" id="shopform" action="' . doliconnecturl('dolishop') . '" ><div class="input-group"><input type="text" class="form-control" name="search" id="search" placeholder="' . esc_attr__('Name, Ref. or barcode', 'doliconnect') . '" aria-label="Search for..." aria-describedby="search-widget">
<div class="input-group-append"><button class="btn btn-primary" type="submit" id="searchsubmit" >Go!</button></div>
</div></form><br>';

$request = "/categories/".esc_attr($shop)."?include_childs=true";
$resultatsc = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if ( !isset($resultatsc->error) && $resultatsc != null ) {
print "<div class='list-group'>";
foreach ($resultatsc->childs as $categorie) {

print "<a href='".esc_url( add_query_arg( 'category', $categorie->id, doliconnecturl('dolishop')) )."' class='list-group-item list-group-item-action";
if ( $categorie->id == $_GET['category'] && !isset($_GET['subcategory']) ) { print " active"; }
print "'>".doliproduct($categorie, 'label')."</a>"; //."<br />".doliproduct($categorie, 'description')

if ( isset($_GET['category']) && $categorie->id == $_GET['category'] ) {
$request = "/categories/".esc_attr(isset($_GET["category"]) ? $_GET["category"] : $_GET["subcategory"])."?include_childs=true";

$resultatsc = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if ( !isset($resultatsc->error) && $resultatsc != null ) {
foreach ($resultatsc->childs as $categorie) {

$arr_params = array( 'category' => $_GET['category'], 'subcategory' => $categorie->id);  
$return = esc_url( add_query_arg( $arr_params, doliconnecturl('dolishop')) );

print "<a href='".$return."' class='list-group-item list-group-item-action";
if ( $categorie->id == $_GET['subcategory'] ) { print " active"; }
print "'>>".doliproduct($categorie, 'label')."</a>"; 
}

}}

}
print "</div>";
}
} 

print $args['after_widget'];  
}    
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
    // Si le titre n'est pas vide, alors on met le titre, sinon un nouveau titre
    $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Categories', 'doliconenct' );
    // Si l'utilisateur n'est pas vide, alors on met l'utilisateur, sinon un nouveau utilisateur
    $display = ! empty( $instance['display'] ) ? $instance['display'] : null;
 
		?>
		<p>
		<label for="<?php print esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'doliconnect'); ?></label> 
		<input class="widefat" id="<?php print esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php print esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php print esc_attr( $title ); ?>">
		</p>
    <p>
    <label for="<?php echo $this->get_field_id( 'display' ); ?>"><?php esc_attr_e( 'Display:', 'doliconnect'); ?></label>
    <input class="widefat" id="<?php print esc_attr( $this->get_field_id( 'display' ) ); ?>"  name="<?php print esc_attr( $this->get_field_name( 'display' ) ); ?>" type="text" value="<?php print esc_attr( $display ); ?>">
    </p>
		<?php 
	}

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
    $instance['display'] = ( ! empty( $new_instance['display'] ) ) ? strip_tags( $new_instance['display'] ) : $old_instance['display'];
		return $instance;
	}

}

add_action( 'widgets_init', function(){
	register_widget( 'Doliconnect_DoliShop' );
});

class Doliconnect_Changelang extends WP_Widget {

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
  
print $args['before_widget'];
if ( ! empty( $instance['title'] ) ) {
print $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
}

if ( function_exists('pll_the_languages') && function_exists('doliconnect_langs') ) {      
print '<a href="#" class="text-decoration-none" data-toggle="modal" data-target="#DoliconnectSelectLang" data-dismiss="modal" title="'.__('Choose language', 'doliconnect').'"><span class="flag-icon flag-icon-'.strtolower(substr(pll_current_language('slug'), -2)).'"></span> '.pll_current_language('name').'</a>';
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
		<label for="<?php print esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'doliconnect'); ?></label> 
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

class DOLIGDRF_Widget extends WP_Widget {

	function __construct() {

		parent::__construct(
			'doligdrf-widget',
			'GDPR Data Request (Doliconnect)'
		);

		add_action(
			'widgets_init',
			function() {
				register_widget( 'DOLIGDRF_Widget' );
			}
		);

	}

	public function widget( $args, $instance ) {

		if ( ! empty( $instance['title'] ) ) {
			echo '<h3>' . esc_html( $instance['title'] ) . '</h3>';
		}
		if ( ! empty( $instance['text'] ) ) {
			echo '<p>' . esc_html( $instance['text'] ) . '</p>';
		}
		$params = array();
		if ( isset( $instance['request_type'] ) ) {
			if ( 'export' === $instance['request_type'] ) {
				$params['request_type'] = 'export';
			} elseif ( 'remove' === $instance['request_type'] ) {
				$params['request_type'] = 'remove';
			}
		}
    		$params['widget'] = true;
		echo gdrf_data_request_form( $params );

	}

	public function form( $instance ) {
		$title        = ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';
		$text         = ( ! empty( $instance['text'] ) ) ? $instance['text'] : '';
		$request_type = ( ! empty( $instance['request_type'] ) ) ? $instance['request_type'] : '';

		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Optional widget title:', 'doliconnect'); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>"><?php esc_html_e( 'Optional widget description:', 'doliconnect'); ?></label>
			<textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'text' ) ); ?>" type="text" cols="30" rows="10"><?php echo esc_attr( $text ); ?></textarea>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'request_type' ) ); ?>"><?php echo esc_attr( 'Request type:', 'doliconnect'); ?></label>
			<select name="<?php echo esc_attr( $this->get_field_name( 'request_type' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'request_type' ) ); ?>">
				<option value="both" <?php selected( $request_type, 'both' ); ?>><?php esc_attr_e( 'Both Export and Remove', 'doliconnect'); ?></option>
				<option value="export" <?php selected( $request_type, 'export' ); ?>><?php esc_attr_e( 'Data Export form only', 'doliconnect'); ?></option>
				<option value="remove" <?php selected( $request_type, 'remove' ); ?>><?php esc_attr_e( 'Data Remove form only', 'doliconnect'); ?></option>
			</select>
		</p>

		<?php
	}

	public function update( $new_instance, $old_instance ) {

		$instance = array();

		$instance['title']        = ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['text']         = ( ! empty( $new_instance['text'] ) ) ? $new_instance['text'] : '';
		$instance['request_type'] = ( ! empty( $new_instance['request_type'] ) ) ? $new_instance['request_type'] : '';

		return $instance;

	}
}
$gdrf_widget = new DOLIGDRF_Widget();

?>