<?php
//*****************************************************************************************

class My_classifieds extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array( 
			'classname' => 'my_widget',                               
			'description' => 'Publication d\'offres d\'emploi',
      'customize_selective_refresh' => true,
		);
		parent::__construct( 'my_widget', 'Offres d\'emploi', $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
global $wpdb;
		// outputs the content of the widget
    
  		print $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
print $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

$time=time();
$delay = DAY_IN_SECONDS;
 
$listclassi = callDoliApi("GET", "/classifieds?sortfield=t.date_start&sortorder=DESC&limit=5&sqlfilters=(t.approved='2')", null, dolidelay($delay, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));   
//print $listclassi;

$counti=count($listclassi);
print '<div class="list-group">'; 
if ($counti>'0') {
foreach ($listclassi as $postticket) {                                                                                 
$return = esc_url( get_permalink(get_option('doliclassifieds') ))."?id=$postticket->rowid";
print "<a class='list-group-item list-group-item-action' href='$return'>#$postticket->rowid - $postticket->label</a>";
} 
}
else {
print "<a href='#' class='list-group-item list-group-item-action disabled'><center>pas d'annonce</center></a>";}

print "<a class='list-group-item list-group-item-action list-group-item-info' href='".doliconnecturl('doliaccount')."?module=classifieds' >Gérer/Déposer une annonce</a>"; 
print "</div>";
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
	register_widget( 'My_classifieds' );
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
global $current_user;
    
  	print $args['before_widget'];
	if ( ! empty( $instance['title'] ) ) {
		print $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
	}
	$thirdparty = doliConnect('member', $current_user, false, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
 
	if (isset($adherent->statut) && $adherent->statut == '1' && $adherent->datefin < current_time('timestamp')) {
		print "<A class='btn btn-block btn-success' href='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' >".__( 'Pay my subscription', 'doliconnect')."</a>"; 
	}
	elseif (isset($adherent->statut) && $adherent->statut == '0') {
		print "<a class='btn btn-block btn-info' href='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' >".__( 'Subscribe', 'doliconnect')."</a>"; 
	}
	elseif (isset($adherent->statut) && $adherent->statut == '-1') {
		print "<a class='btn btn-block btn-warning disabled' href='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' >".__( 'Membership', 'doliconnect')."</a>";//requested 
	}
	elseif (!isset($adherent->id)) {
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
print '<a href="'.doliconnecturl('dolicart').'" title="'.__('Basket', 'doliconnect').'"><span class="fa-layers fa-fw fa-2x"><i class="fas fa-shopping-bag"></i><span class="fa-layers-counter" id="DoliWidgetCartItems" style="background:Tomato">'.doliconnect_countitems(doliConnect('order', wp_get_current_user())).'</span></span></a>';  
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

if ( !empty($instance['display']) || (is_singular('doliproduct')) || (is_tax('doliproduct_category')) || (empty($instance['display']) && is_page(doliconnectid('dolishop')) && !empty(doliconnectid('dolishop'))) ) { 
  
print $args['before_widget'];
if ( ! empty( $instance['title'] ) ) {
	print $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
}

$shop = doliconst("DOLICONNECT_CATSHOP", esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));

$limit=20;
if ( isset($_GET['pg']) && is_numeric(esc_attr($_GET['pg'])) && esc_attr($_GET['pg']) > 0 ) { $page = esc_attr($_GET['pg']); } else { $page = 0; }
if ( isset($_GET['field']) ) { $field = esc_attr($_GET['field']); } else { $field = 'label'; }
if ( isset($_GET['order']) ) { $order = esc_attr($_GET['order']); } else { $order = 'ASC'; }

if ( $shop != null && $shop > 0 ) {
	$request = "/categories?sortfield=t.label&sortorder=ASC&limit=100&type=product&sqlfilters=(t.fk_parent:=:".esc_attr($shop).")";
} else {
	$request = "/categories?sortfield=t.label&sortorder=ASC&limit=100&type=product&sqlfilters=(t.fk_parent:=:0)";
}

$resultatsc = callDoliApi("GET", $request, null, dolidelay('category', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if ( !isset($resultatsc->error) && $resultatsc != null ) {
		print "<div class='list-group'>";
		if (doliconst("CATEGORIE_RECURSIV_ADD", esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null))) { 
			print "<a href='".esc_url( add_query_arg( 'category', 'all', doliconnecturl('dolishop')) )."' class='list-group-item list-group-item-light list-group-item-action d-flex justify-content-between";
			if (is_page(doliconnectid('dolishop')) || (isset($_GET['category']) && $_GET['category'] == 'all')) { print " active"; }
			if ( doliversion('19.0.0') ) { 
				$requestp = "/products?sortfield=t.".$field."&sortorder=".$order."&limit=".$limit."&page=".$page."&category=".esc_attr($shop)."&ids_only=true&pagination_data=true&sqlfilters=(t.tosell:=:1)";
				$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));  
				$count = $listproduct->pagination->total;
			} else { 
				$requestp = "/products?sortfield=t.".$field."&sortorder=".$order."&limit=1000&category=".esc_attr($shop)."&ids_only=true&sqlfilters=(t.tosell:=:1)";
				$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
				if (empty($listproduct) || isset($listproduct->error)) {
				$count = 0;
				} else {
				$count = count($listproduct);
				}
			}
			print "'>".__(  'All items', 'doliconnect')." <span class='badge bg-secondary rounded-pill'>".$count."</span></a>";
		}

	if (get_option('dolicartnewlist') != 'none') {
		print "<a href='".esc_url( add_query_arg( 'category', 'new', doliconnecturl('dolishop')) )."' class='list-group-item list-group-item-light list-group-item-action d-flex justify-content-between";
		if (isset($_GET['category']) && $_GET['category'] == 'new') { print " active"; }
		$date = new DateTime(); 
		$date->modify('NOW');
		$duration = (!empty(get_option('dolicartnewlist'))?get_option('dolicartnewlist'):'month');
		$date->modify('FIRST DAY OF LAST '.$duration.' MIDNIGHT');
		$lastdate = $date->format('Y-m-d');
		if ( doliversion('19.0.0') ) { 
			$requestp = "/products?sortfield=t.".$field."&sortorder=".$order."&limit=".$limit."&page=".$page."&category=".esc_attr($shop)."&ids_only=true&pagination_data=true&sqlfilters=(t.datec%3A%3E%3D%3A'".$lastdate."')and(t.tosell:=:1)";
			$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));  
			$count = $listproduct->pagination->total;
		} else { 
			$requestp = "/products?sortfield=t.".$field."&sortorder=".$order."&limit=1000&category=".esc_attr($shop)."&ids_only=true&sqlfilters=(t.datec%3A%3E%3D%3A'".$lastdate."')and(t.tosell:=:1)";
			$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
			if (empty($listproduct) || isset($listproduct->error)) {
			$count = 0;
			} else {
			$count = count($listproduct);
			}
		}
		print "'>".__(  'Novelties', 'doliconnect')." <span class='badge bg-secondary rounded-pill'>".$count."</span></a>";
	}

	if ( doliCheckModules('discountprice', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)) ) {
		print "<a href='".esc_url( add_query_arg( 'category', 'discount', doliconnecturl('dolishop')) )."' class='list-group-item list-group-item-light list-group-item-action d-flex justify-content-between";
		if (isset($_GET['category']) && $_GET['category'] == 'discount') { print " active"; }
		$date = new DateTime(); 
		$date->modify('NOW');
		$lastdate = $date->format('Y-m-d');
		if ( doliversion('19.0.0') ) { 
			$requestp = "/discountprice?sortfield=t.".$field."&sortorder=".$order."&limit=".$limit."&page=".$page."&pagination_data=true&sqlfilters=(t.date_begin%3A%3C%3D%3A'".$lastdate."')and(t.date_end%3A%3E%3D%3A'".$lastdate."')and(d.tosell:=:1)";
			$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));  
			$count = $listproduct->pagination->total;
		} else { 
			$requestp = "/discountprice?sortfield=t.rowid&sortorder=DESC&sqlfilters=(t.date_begin%3A%3C%3D%3A'".$lastdate."')and(t.date_end%3A%3E%3D%3A'".$lastdate."')and(d.tosell:=:1)";
			$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
			if (empty($listproduct) || isset($listproduct->error)) {
				$count = 0;
			} else {
				$count = count($listproduct);
			}
		}
		print "'>".__(  'Discounted items', 'doliconnect')." <span class='badge bg-secondary rounded-pill'>".$count."</span></a>";
	}

	foreach ($resultatsc as $categorie) {
		getDoliProductCategory($categorie);
		if ( doliversion('19.0.0') ) { 
			$requestp = "/products?sortfield=t.".$field."&sortorder=".$order."&limit=".$limit."&page=".$page."&category=".$categorie->id."&ids_only=true&pagination_data=true&sqlfilters=(t.tosell:=:1)";
			$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));  
			$count = $listproduct->pagination->total;
		} else { 
			$requestp = "/products?sortfield=t.".$field."&sortorder=".$order."&limit=1000&category=".$categorie->id."&ids_only=true&sqlfilters=(t.tosell:=:1)";
			$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
			if (empty($listproduct) || isset($listproduct->error)) {
			$count = 0;
			} else {
			$count = count($listproduct);
			}
		}

		print "<a href='".get_term_link(getDoliProductCategory($categorie))."' class='list-group-item list-group-item-light list-group-item-action d-flex justify-content-between";
		if ( is_tax('doliproduct_category', getDoliProductCategory($categorie)) || (isset($_GET['category']) && !isset($_GET['subcategory']) && $categorie->id == $_GET['category']) ) { print " active"; }
		print "'>".doliproduct($categorie, 'label')." <span class='badge bg-secondary rounded-pill'>".$count."</span></a>";

		if ( is_tax('doliproduct_category', getDoliProductCategory($categorie)) || (isset($_GET['category']) && $categorie->id == $_GET['category']) ) {
			$request = "/categories/".esc_attr(isset($_GET["category"]) ? $_GET["category"] : $shop)."?include_childs=true";
			$resultatsc = callDoliApi("GET", $request, null, dolidelay('category', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
			if ( !isset($resultatsc->error) && $resultatsc != null ) {
				foreach ($resultatsc->childs as $scategorie) {
				if ( doliversion('19.0.0') ) { 
					$requestp = "/products?sortfield=t.".$field."&sortorder=".$order."&limit=".$limit."&page=".$page."&category=".$scategorie->id."&ids_only=true&pagination_data=true&sqlfilters=(t.tosell:=:1)";
					$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));  
					$count = $listproduct->pagination->total;
				} else { 
					$requestp = "/products?sortfield=t.".$field."&sortorder=".$order."&limit=1000&category=".$scategorie->id."&ids_only=true&sqlfilters=(t.tosell:=:1)";
					$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
					if (empty($listproduct) || isset($listproduct->error)) {
					$count = 0;
					} else {
					$count = count($listproduct);
					}
				}
				print "<a href='".get_term_link(getDoliProductCategory($scategorie))."' class='list-group-item list-group-item-light list-group-item-action d-flex justify-content-between";
				if ( is_tax('doliproduct_category', getDoliProductCategory($scategorie)) || (isset($_GET['subcategory']) && $scategorie->id == $_GET['subcategory'] ) ) { print " active"; }
				print "'>>".doliproduct($scategorie, 'label')." <span class='badge bg-secondary rounded-pill'>".$count."</span></a>";
				}

				if ( (isset($scategorie) && is_tax('doliproduct_category', getDoliProductCategory($scategorie))) || (isset($_GET['subcategory']) && isset($scategorie) && $scategorie->id == $_GET['subcategory'] ) ) {

					$request = "/categories/".esc_attr(isset($_GET["subcategory"]) ? $_GET["subcategory"] : $_GET["category"])."?include_childs=true";
					$resultatsc = callDoliApi("GET", $request, null, dolidelay('category', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
					if ( !isset($resultatsc->error) && $resultatsc != null) {
						foreach ($resultatsc->childs as $sscategorie) {
							getDoliProductCategory($sscategorie);
								if ( doliversion('19.0.0') ) { 
									$requestp = "/products?sortfield=t.".$field."&sortorder=".$order."&limit=".$limit."&page=".$page."&category=".$categorie->id."&ids_only=true&pagination_data=true&sqlfilters=(t.tosell:=:1)";
									$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));  
									$count = $listproduct->pagination->total;
								} else { 
									$requestp = "/products?sortfield=t.".$field."&sortorder=".$order."&limit=1000&category=".$categorie->id."&ids_only=true&sqlfilters=(t.tosell:=:1)";
									$listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
									if (empty($listproduct) || isset($listproduct->error)) {
									$count = 0;
									} else {
									$count = count($listproduct);
									}
								}
							print "<a href='".get_term_link(getDoliProductCategory($sscategorie))."' class='list-group-item list-group-item-light list-group-item-action d-flex justify-content-between";
							if ( is_tax('doliproduct_category', getDoliProductCategory($sscategorie)) || (isset($_GET['subsubcategory']) && $sscategorie->id == $_GET['subsubcategory'])) { print " active"; }
							print "'>>> ".doliproduct($sscategorie, 'label')." <span class='badge bg-secondary rounded-pill'>".$count."</span></a>";
						} 
					}
				}
			}	
		}

	}
	print "</div>";
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