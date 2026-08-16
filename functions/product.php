<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

  function doliconnect_dolibarrproduct_init() {
      $labels = array(
          'name'                  => _x( 'Items', 'Post type general name', 'doliconnect' ),
          'singular_name'         => _x( 'Item', 'Post type singular name', 'doliconnect' ),
          'menu_name'             => _x( 'Items', 'Admin Menu text', 'doliconnect' ),
          'name_admin_bar'        => _x( 'Item', 'Add New on Toolbar', 'doliconnect' ),
          'add_new'               => __( 'Add New', 'doliconnect' ),
          'add_new_item'          => __( 'Add New item', 'doliconnect' ),
          'new_item'              => __( 'New item', 'doliconnect' ),
          'edit_item'             => __( 'Edit item', 'doliconnect' ),
          'view_item'             => __( 'View item', 'doliconnect' ),
          'all_items'             => __( 'All items', 'doliconnect' ),
          'search_items'          => __( 'Search recipes', 'doliconnect' ),
          'parent_item_colon'     => __( 'Parent recipes:', 'doliconnect' ),
          'not_found'             => __( 'No recipes found.', 'doliconnect' ),
          'not_found_in_trash'    => __( 'No recipes found in Trash.', 'doliconnect' ),
          'featured_image'        => _x( 'Recipe Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'doliconnect' ),
          'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'doliconnect' ),
          'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'doliconnect' ),
          'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'doliconnect' ),
          'archives'              => _x( 'Recipe archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'doliconnect' ),
          'insert_into_item'      => _x( 'Insert into recipe', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'rdoliconnect' ),
          'uploaded_to_this_item' => _x( 'Uploaded to this recipe', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'doliconnect' ),
          'filter_items_list'     => _x( 'Filter recipes list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'doliconnect' ),
          'items_list_navigation' => _x( 'Recipes list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'doliconnect' ),
          'items_list'            => _x( 'Recipes list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'doliconnect' ),
      ); 
      $post_slug = get_post_field( 'post_name', get_option('dolishop') );    
      $args = array(
          'labels'             => $labels,
          'description'        => __( 'Item custom post type.', 'doliconnect' ), 
          'menu_icon'          => 'dashicons-products',
          'public' => true,
          'exclude_from_search' => false,
          'publicly_queryable' => true,
          'show_ui'            => true,
          'show_in_menu'       => true,
          'query_var'          => true,
          'rewrite'            => array( 'slug' => $post_slug ),
          'capability_type'    => 'page',
          'has_archive'        => true,
          'hierarchical'       => false,
          'menu_position'      => 80,
          'supports'           => array( 'title', 'author', 'editor', 'thumbnail'), //,'comments'
          'taxonomies'         => array( 'doliproduct_category' ),
          'show_in_rest'       => true
      );
      
      register_post_type( 'doliproduct', $args );
  }
  add_action( 'init', 'doliconnect_dolibarrproduct_init' );

  // Limiter le filtre à la page admin des custom post_type doliproduct
  add_filter('use_block_editor_for_post', function($use_block_editor, $post) {
    if ($post && $post->post_type === 'doliproduct') {
        return false;
    }
    return $use_block_editor;
}, 10, 2);

add_action('save_post_doliproduct', 'doliproduct_update_action', 10, 3);
function doliproduct_update_action($post_ID, $post, $update) {
    // Avoid running during autosave or revisions
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (wp_is_post_revision($post_ID)) {
        return;
    }
    if (!current_user_can('edit_post', $post_ID)) {
        return;
    }

    // Only run on updates, not on first creation
    if ($update) {
        // Your custom logic here
        error_log("doliproduct Type '{$post->post_type}' with ID {$post_ID} was updated.");
        
        // Example: Send an email
        update_post_meta($post_ID, 'doliproduct_productid', sanitize_text_field($_POST['doliproduct_productid']));
    
        $uln = [
          'label' => $post->post_title,
          'description' => $post->post_content,
          'url' => $post->guid,
          //'array_options' => $array_options
	      ];                  
        $updateproduct = callDoliApi("PUT", "/products/".sanitize_text_field($_POST['doliproduct_productid']), $uln, 0);
        
    }
}

  add_action( 'init', 'doliproduct_taxonomies', 0 );  
    function doliproduct_taxonomies() { 
      $taxonomy_slug = get_post_field( 'post_name', get_option('dolishop') ).'/'.__( 'category', 'doliconnect' );  
        register_taxonomy(  
        'doliproduct_category',  
        'doliproduct',
        array(  
            'hierarchical' => true,  
            'labels' => __( 'Categories of items', 'doliconnect' ), 
            'description' => __( 'Categories of items for Dolibarr', 'doliconnect' ), 
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menu' => true,
            'query_var' => true,  
            'rewrite' => array( 'slug' => $taxonomy_slug ),
            'show_in_rest' => true
        )  
    );  
  }

  function doliproduct_category_add_custom_field($term) {
    $custom_field_value = isset($term->term_id) ? get_term_meta($term->term_id, 'doliproduct_category_id', true) : '';
    ?>
    <tr class="form-field">
        <th scope="row">
            <label for="doliproduct_category_id"><?php _e('Category ID', 'doliconnect'); ?></label>
        </th>
        <td>
            <input type="text" name="doliproduct_category_id" id="doliproduct_category_id" value="<?php echo esc_attr($custom_field_value); ?>" />
            <p class="description"><?php _e('Enter a custom value for this category.', 'doliconnect'); ?></p>
        </td>
    </tr>
    <?php
}
add_action('doliproduct_category_edit_form_fields', 'doliproduct_category_add_custom_field');
add_action('doliproduct_category_add_form_fields', 'doliproduct_category_add_custom_field');

  // Ajout d'un champ personnalisé au type de post doliproduct
  function doliproduct_add_custom_meta_box() {
      add_meta_box(
          'doliproduct_productid_callback',
          __('N° of item', 'doliconnect'),
          'doliproduct_productid_callback',
          'doliproduct',
          'side',
          'default'
      );
  }
  add_action('add_meta_boxes', 'doliproduct_add_custom_meta_box');

  function doliproduct_productid_callback($post) {
      // Récupérer la valeur actuelle du champ personnalisé
      $custom_field_value = get_post_meta($post->ID, 'doliproduct_productid', true);
      echo '<label for="doliproduct_productid">' . __('Enter value:', 'doliconnect') . '</label>';
      echo '<input type="text" id="doliproduct_productid" name="doliproduct_productid" value="' . esc_attr($custom_field_value) . '" size="25" />';
  }

  // Vérification des capacités utilisateur avant d'enregistrer les métadonnées
  function doliproduct_save_meta_box($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    if (isset($_POST['doliproduct_productid'])) {
        update_post_meta($post_id, 'doliproduct_productid', sanitize_text_field($_POST['doliproduct_productid']));
    }
  }
  add_action('save_post', 'doliproduct_save_meta_box');

  function doliproduct_conditional_display( $content) {
    global $post;
      if ( is_singular( 'doliproduct' ) && in_the_loop() && is_main_query() ) {
          $custom_field_value = get_post_meta( $post->ID, 'doliproduct_productid', true );
          $custom_message = '';//<div>' . sprintf( __( 'This is a doliproduct post. Item N°: %s', 'doliconnect' ), esc_html( $custom_field_value ) ) . '</div>';
          $request = "/products/".esc_attr($custom_field_value)."?includesubproducts=true&includetrans=true";
          $product = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

          $custom_message .= apply_filters( 'doliproductcard', $product);
          return $custom_message . $content;
      }
      return $content;
  }
  add_filter( 'the_content', 'doliproduct_conditional_display', 10);

  //add_filter( 'template_include', 'doliproduct_list_template', 99 );
  function doliproduct_list_template( $template ) {
      if ( get_query_var('post_type') == 'doliproduct' ) {
          $file_name = 'page.php';
          if ( locate_template( $file_name ) ) {
              $template = locate_template( $file_name );
          } else {
              $template = plugin_dir_path( __DIR__ ) . 'templates/'. $file_name;
          }
      }
      return $template;
  }

  /**
   * Callback to change the single post template for doliproduct post type.
   *
   * @param string $single Path to the default single template.
   * @return string Path to the custom template.
   */

  function doliproduct_single_template($single) {
      // Check if we're viewing a specific post type
      if ( is_singular( 'doliproduct' ) && in_the_loop() && is_main_query() ) {
          // Path to your custom template file inside your theme
          $custom_template = get_stylesheet_directory() . '/content-item.php';

          // If the file exists, use it
          if (file_exists($custom_template)) {
              return $custom_template;
          }
      }
      // Fallback to the default template
      return $single;
  }
  //add_filter('single_template', 'doliproduct_single_template');

  /**
   * Callback to add the custom post type to search results.
   *
   * @param WP_Query $query The current query object.
   */
  
  function doliproduct_include_in_search_results( $query ) {
    if ( $query->is_main_query() && $query->is_search() && ! is_admin() ) {
        $query->set( 'post_type', array( 'post', 'page', 'doliproduct' ) );
    }
  }
  add_action( 'pre_get_posts', 'doliproduct_include_in_search_results' );


function getDoliProductUrl($productid, $refresh = false) {
  // Vérifier que l'ID du produit est valide
  if (empty($productid)) {
    return 'Invalid product ID';
  }
  $product = callDoliApi("GET", "/products/".$productid."?includesubproducts=true&includetrans=true", null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
  if (!isset($product->id) || empty($product->id)) {
    return 'Title and Product ID are required';
  }

  $args = array(
      'post_type'  => 'doliproduct',
      'meta_query' => array(
        array(
          'key'     => 'doliproduct_productid',
          'value'   => sanitize_text_field($productid),
          'compare' => '='
        )
      ),
    'posts_per_page' => 1
  );

  $query = new WP_Query($args);

  if ($query->have_posts()) {
    if (!empty($refresh)) {
      $categories =  callDoliApi("GET", "/categories/object/product/".$product->id."?sortfield=s.rowid&sortorder=ASC", null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
      // Ajouter les catégories de produits au post
      if (isset($categories) && !empty($categories)) {
        $category_ids = array();
        foreach ($categories as $category) {
          $term = getDoliProductCategory($category);
          if ($term) {
            $category_ids[] = $term;
          }
        }
        if (!empty($category_ids)) {
          wp_set_post_terms($query->posts[0]->ID, $category_ids, 'doliproduct_category');
        }
      }
    }     

    $url = get_permalink($query->posts[0]->ID);
    wp_reset_postdata(); // Réinitialiser la requête globale
    return $url;
  } else {
    // Si aucun post n'a été trouvé, créer un nouveau post doliproduct
    $product = callDoliApi("GET", "/products/".$productid."?includesubproducts=true&includetrans=true", null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
    if (!isset($product->id) || empty($product->id)) {
      return 'Title and Product ID are required';
    }
      
    $title = doliproduct($product, 'label');
    $content = doliproduct($product, 'description');

    $post_data = array(
      'post_title'   => sanitize_text_field($title),
      'post_content' => wp_kses_post($content),
      'post_status'  => 'publish',
      'post_type'    => 'doliproduct',
      'meta_input'   => array(
        'doliproduct_productid' => sanitize_text_field($productid),
      ),
    );

    $post_id = wp_insert_post($post_data);

    if (is_wp_error($post_id)) {
      return 'Error creating post: ' . $post_id->get_error_message();
    }

    $url = get_permalink($post_id);
    if (!empty($refresh)) {
      $categories =  callDoliApi("GET", "/categories/object/product/".$product->id."?sortfield=s.rowid&sortorder=ASC", null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
      // Ajouter les catégories de produits au post
      if (isset($categories) && !empty($categories)) {
        $category_ids = array();
        foreach ($categories as $category) {
          $term = getDoliProductCategory($category);
          if ($term) {
            $category_ids[] = $term;
          }
        }
        if (!empty($category_ids)) {
          wp_set_post_terms($post_id, $category_ids, 'doliproduct_category');
        }
      }
    }  
    wp_reset_postdata(); // Réinitialiser la requête globale
    return $url;
  }

  return 'No URL found';
}

function getDoliProductCategory($category) {
  if (isset($category->id) && !empty($category->id)) {
  $args = array(
      'taxonomy'   => 'doliproduct_category',
      'hide_empty' => false,              
      'meta_query' => array(
          array(
              'key'     => 'doliproduct_category_id',   
              'value'   => $category->id,        
              'compare' => '=',      
          ),
      ),
  );
  $terms = get_terms($args);
  if (is_wp_error($terms)) {
    return 'Error: ' . $terms->get_error_message();
  }
  if (!empty($terms)) {
    return $terms[0]->term_id;
  } else {
    $term = $category->label;
    $taxonomy = 'doliproduct_category';
    $args = array(
      'description' => $category->description,
      //'slug' => 'football-blogs',
    );

    $result = wp_insert_term($term, $taxonomy, $args);

    if (is_wp_error($result)) {
      return 'Error: ' . $result->get_error_message();
    } else {
      update_term_meta($result['term_id'], 'doliproduct_category_id', sanitize_text_field($category->id));
      
      return $result['term_id'];
    }
  }
  }
}

function doliproduct($object, $value) {
global $current_user;

  $lang = doliUserLang($current_user, 'locale');
  if ( isset($object->multilangs->$lang) ) { 
    return !empty($object->multilangs->$lang->$value) ? $object->multilangs->$lang->$value : $object->$value;
  } else {
    if (isset($object->$value)) return $object->$value;
  }
}

function doliRequiredRelatedProducts($id, $qty = null, $valid = false) {
  $request = "/relatedproducts/".$id."?required=true";
  $relatedproducts = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));  
  if ( !isset( $relatedproducts->error ) && $relatedproducts != null ) {
      if (empty($valid)) { 
        return true;
      } else {
        foreach ( $relatedproducts as $product ) {
          $qty2 = $qty*$product->qty;
          $product = callDoliApi("GET", "/products/".$product->id."?includesubproducts=true&includetrans=true", null, dolidelay('product', true));
          $mstock = doliProductStock($product, false, true, array(), $id);
          $price = doliProductPrice($product, $qty2, false);
          $related = doliaddtocart($product, $mstock, $qty2, $price, null, null, $id);
        }
        return $related;
      }
  } else {
      return false;
  }
}

function doliCheckRelatedProducts($id) {
  $request = "/relatedproducts/".$id;
  $relatedproducts = callDoliApi("GET", $request, null, dolidelay('product'));  
  if ( !isset( $relatedproducts->error ) && $relatedproducts != null ) {
      return true;
  } else {
      return false;
  }
}

function doliRelatedProducts($fk_parent_line, $refresh = false) {
  $request = "/relatedproducts/".$fk_parent_line;
  $relatedproducts = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));  
  if ( !isset( $relatedproducts->error ) && $relatedproducts != null ) {
    $related = null;
    foreach ( $relatedproducts as $product ) { 
      $related .= apply_filters( 'doliproductlist', $product->id, false, $fk_parent_line, true);
    }
  return $related;
  } else {
      return false;
  }
}

function doliprice($object = null, $mode = "ttc", $currency = null) {
global $current_user;

  if ( is_object($object) ) {
    $total='multicurrency_total_'.$mode;
    if ( isset($object->$mode) ) { 
      $montant=$object->$mode;
    } else {
      $total = 'total_'.$mode;
      $montant = $object->$total;
    } 
  } elseif (!empty($object)) {
    $montant = $object;
  } else {
    $montant = 0;
  }
  if ($montant == 0) {
    return __( 'Free', 'doliconnect');
  } else {
    //$objet->multicurrency_code
    if ( is_null($currency) ) { 
      $currency = strtoupper(doliconst("MAIN_MONNAIE"));
    }
    $fmt = numfmt_create( doliUserLang($current_user, 'locale'), NumberFormatter::CURRENCY );
    return numfmt_format_currency($fmt, $montant, $currency).'<sup> '.$mode.'</sup>';
  }
}

function doliProductStock($product, $refresh = false, $nohtml = false, $array_options = array(), $fk_line = null) {
global $current_user;
  $mstock = array();
  $order = doliConnect('order', $current_user, false, $refresh);
  $warehouse = doliconst('DOLICONNECT_ID_WAREHOUSE');
  $stock = callDoliApi("GET", "/products/".$product->id."/stock?selected_warehouse_id=".$warehouse, null, dolidelay('stock', $refresh));
  $mstock['orderid'] = $order->id;

  if (empty($product->status)) {   
    $mstock['stock'] = 0;
  } elseif (!empty($product->type) && empty(doliconst('STOCK_SUPPORTS_SERVICES'))) {
    $mstock['stock'] = 999999999;
  } elseif (isset($stock->stock_warehouse) && !empty($stock->stock_warehouse) && !empty($warehouse) && $warehouse > 0) {
    if (isset($stock->stock_warehouse->$warehouse->real)) {
      $mstock['stock'] = min(array($stock->stock_reel,$stock->stock_warehouse->$warehouse->real,$stock->stock_theorique));
    } else {
      $mstock['stock'] = 0;
    }
  } elseif (isset($stock->stock_theorique) && isset($stock->stock_reel)) {
    $mstock['stock'] = min(array($stock->stock_theorique,$stock->stock_reel));
  } else {
    $mstock['stock'] = 999999999;
  }
  if (!empty(doliconst('PRODUCT_USE_CUSTOMER_PACKAGING')) && isset($product->packaging) && !empty($product->packaging)) {
    $mstock['step'] = $product->packaging;
  } else {
    $mstock['step'] = 1;
  }
  if (!empty(doliconnectid('dolishipping'))) {
    $shipping = '<a href="'.doliconnecturl('dolishipping').'" class="btn btn-link btn-block btn-sm">'.__( 'Shipping', 'doliconnect').'</a>';
  } else {
    $shipping = null;
  }  
  $array_options2 = array();
  $array = callDoliApi("GET", "/setup/extrafields?sortfield=t.pos&sortorder=ASC&elementtype=commandedet", null, dolidelay('constante'));  
  if ( isset($array->commandedet) && $array->commandedet != null ) {
      foreach ($array->commandedet as $name => $value) {
        $name = 'options_'.$name;
        if (is_array($array_options) && !isset($array_options[$name])) {
          $array_options2[$name] = $value->default;
        }
      }
  }
  if (isset($array_options) && is_array($array_options)) $array_options = array_merge($array_options2, $array_options);
  
  if (isset($fk_line->id) && !empty($fk_line->id)) {
    $linearray_options = (array) $fk_line->array_options;
    $mstock['qty'] = $fk_line->qty;
    $mstock['lineid'] = $fk_line->id;
    $mstock['line'] = $fk_line;
    $mstock['array_options'] = $linearray_options;
    $mstock['fk_parent_line'] = $fk_line->fk_parent_line;
  } elseif (isset($order->id) && $order->id > 0) {
    if ( isset($order->lines) && $order->lines != null ) {
      foreach ($order->lines as $line) {
        $linearray_options = (array) $line->array_options;
        if (isset($product->id) && $line->fk_product == $product->id && isset($fk_line->id) && $line->id == $fk_line->id) {
           $mstock['qty'] = $line->qty;
           $mstock['lineid'] = $line->id;
           $mstock['line'] = $line;
           $mstock['array_options'] = $linearray_options;
           $mstock['fk_parent_line'] = $line->fk_parent_line;
        } elseif (isset($product->id) && $line->fk_product == $product->id && $linearray_options == $array_options) {
          $mstock['qty'] = $line->qty;
          $mstock['lineid'] = $line->id;
          $mstock['line'] = $line;
          $mstock['array_options'] = $linearray_options;
          $mstock['fk_parent_line'] = $line->fk_parent_line;
         }
        }
    } 
  } else {
    $mstock['qty'] = 0;
    $mstock['lineid'] = 0;
    $mstock['line'] = null;
    $mstock['array_options'] = $array_options;
    $mstock['fk_parent_line'] = null;
  }

  if (!isset($mstock['qty']) ) {
    $mstock['qty'] = 0;
    $mstock['lineid'] = 0;
    $mstock['line'] = null;
    $mstock['array_options'] = $array_options;
    $mstock['fk_parent_line'] = null;
  }

  if (! isset($mstock['lineid'])) { $mstock['lineid'] = null; }
  if (doliconst('CUSTOMER_ORDER_DRAFT_FOR_VIRTUAL_STOCK')) $mstock['stock']=$mstock['stock']+$mstock['qty'];
  if ( $mstock['stock']-$mstock['qty'] > 0 && (empty($product->type) || (!empty($product->type) && doliconst('STOCK_SUPPORTS_SERVICES')) ) ) {
    $mstock['m0'] = 1*$mstock['step'];
    $mstock['m1'] = get_option('dolicartlist')*$mstock['step'];
    if ( $mstock['stock']-$mstock['qty'] >= $mstock['m1'] || !doliCheckModules('stock') ) {
      $mstock['m2'] = $mstock['m1'];
    } elseif ( $mstock['stock'] > $mstock['qty'] ) {
      $mstock['m2'] = $mstock['stock'];
    } else { $mstock['m2'] = $mstock['qty']; }
  } else {
    $mstock['m0'] = 1;
    if ( isset($line) && $line->qty > 1 ) { $mstock['m2'] = $mstock['qty']; }
    else { $mstock['m2'] = 1; }
  } 
  if (!$nohtml) {
    $stock = '<script type="text/javascript">';//.var_dump($array_options).var_dump($mstock['array_options']);
    $stock .= '
    (function ($) {
     $(document).ready(function () {
       $("#popover-stock-'.$product->id.'").popover({
         placement : "auto",
          delay: { "show": 150, "hide": 150 },
          trigger : "focus",
          html : true
        })
      });
    })(jQuery);';
    $stock .= '</script>';
  }
  if ( ! is_object($product) || !doliCheckModules('stock') || (!empty($product->type) && empty(doliconst('STOCK_SUPPORTS_SERVICES')) ) || (empty($product->type) && !empty(doliconst('STOCK_ALLOW_NEGATIVE_TRANSFER')) && empty(doliconst('STOCK_MUST_BE_ENOUGH_FOR_ORDER')) )) {
    if (!$nohtml) $stock .= "<a tabindex='0' id='popover-stock-".$product->id."' class='badge rounded-pill bg-success text-white text-decoration-none' data-bs-container='body' data-bs-toggle='popover' data-bs-trigger='focus' title='".__( 'Available', 'doliconnect')."' data-bs-content='".__( 'This item is available and can be order', 'doliconnect')."'><i class='fas fa-warehouse'></i> ".__( 'Available', 'doliconnect').'</a>';
    $mstock['m0'] = 1*$mstock['step'];
    $mstock['m1'] = get_option('dolicartlist')*$mstock['step'];
    $mstock['m2'] = $mstock['m1'];
  } elseif (empty($product->type) && empty(doliconst('STOCK_ALLOW_NEGATIVE_TRANSFER')) && empty(doliconst('STOCK_MUST_BE_ENOUGH_FOR_ORDER')) && isset($product->array_options->options_unlimitedsale) && !empty($product->array_options->options_unlimitedsale)) {
    if (!$nohtml) $stock .= "<a tabindex='0' id='popover-stock-".$product->id."' class='badge rounded-pill bg-info text-white text-decoration-none' data-bs-container='body' data-bs-toggle='popover' data-bs-trigger='focus' title='".__( 'Available', 'doliconnect')."' data-bs-content='".__( 'This item is available and can be order but it can sometimes be briefly unavailable', 'doliconnect')."'><i class='fas fa-warehouse'></i> ".__( 'Available', 'doliconnect').'</a>';
    $mstock['m0'] = 1*$mstock['step'];
    $mstock['m1'] = get_option('dolicartlist')*$mstock['step'];
    $mstock['m2'] = $mstock['m1'];
    $mstock['stock'] = $mstock['m2'];
  } elseif (!$nohtml) {
    if ( $mstock['stock'] <= 0 || (!empty(doliconst('PRODUCT_USE_CUSTOMER_PACKAGING')) && isset($product->packaging) && !empty($product->packaging) && $mstock['stock'] < $product->packaging) ) { 
      $stock .= "<a tabindex='0' id='popover-stock-".$product->id."' class='badge rounded-pill bg-dark text-white text-decoration-none' data-bs-container='body' data-bs-toggle='popover' data-bs-trigger='focus' title='".__( 'Not available', 'doliconnect')."' data-bs-content='".sprintf( __( 'This item is out of stock and can not be ordered or shipped. %s', 'doliconnect'), $shipping)."'><i class='fas fa-warehouse'></i> ".__( 'Not available', 'doliconnect')."</a>";
    } elseif ( ($mstock['stock'] <= 0 || (!empty(doliconst('PRODUCT_USE_CUSTOMER_PACKAGING')) && isset($product->packaging) && $mstock['stock'] < $product->packaging)) && $product->stock_theorique > $mstock['stock'] ) { 
        $next = null;
      $stock .= "<a tabindex='0' id='popover-stock-".$product->id."' class='badge rounded-pill bg-danger text-white text-decoration-none' title='".__( 'Available soon', 'doliconnect')."' data-bs-container='body' data-bs-toggle='popover' data-bs-trigger='focus' data-bs-content='".sprintf( __( 'This item is not in stock but should be available soon within %s days. %s %s', 'doliconnect'), $delay, $next, $shipping)."'><i class='fas fa-warehouse'></i> ".__( 'Available soon', 'doliconnect')."</a>"; 
    } elseif ( $mstock['stock'] >= 0 && $mstock['stock'] <= $product->seuil_stock_alerte ) { 
      $stock .= "<a tabindex='0' id='popover-stock-".$product->id."' class='badge rounded-pill bg-warning text-white text-decoration-none' data-bs-container='body' data-bs-toggle='popover' data-bs-trigger='focus' title='".__( 'Limited availability', 'doliconnect')."' data-bs-content='".sprintf( __( 'This item is in stock and can be shipped immediately but only in limited quantities. %s', 'doliconnect'), $shipping)."'><i class='fas fa-warehouse'></i> ".__( 'Available', 'doliconnect')."</a>";
    } else {
      $stock .= "<a tabindex='0' id='popover-stock-".$product->id."' class='badge rounded-pill bg-success text-white text-decoration-none' data-bs-container='body' data-bs-toggle='popover' data-bs-trigger='focus' title='".__( 'Available immediately', 'doliconnect')."' data-bs-content='".sprintf( __( 'This item is in stock and can be shipped immediately. %s', 'doliconnect'), $shipping)."'><i class='fas fa-warehouse'></i> ".__( 'Available', 'doliconnect').'</a>';
    }
  } 
  if ($nohtml) { 
    return $mstock;
  } else {
    return $stock;
  }
}

function doliconnect_countitems($object){
  $qty=0;
  if ( is_object($object) && isset($object->lines) && $object->lines != null ) {
    foreach ($object->lines as $line) {
      $qty+=$line->qty;
    }
  }
  return $qty;
}

function doliaddtocart($product, $mstock, $quantity, $price, $timestart = null, $timeend = null, $relatedproduct = null, $array_options = array()) {
global $current_user;

  $response = array();
  $order = doliConnect('order', $current_user, false, true);
  if ( isset($order->id) && $order->id > 0 ) {
    $orderid = $order->id;
  } else {
    $orderid = null;
  }
  if (!is_null($timestart) && $timestart > 0 ) {
   $date_start=strftime('%Y-%m-%d 00:00:00', $timestart);
  } else {
   $date_start = null;
  }
  if ( !is_null($timeend) && $timeend > 0 ) {
   $date_end=strftime('%Y-%m-%d 00:00:00', $timeend);
  } else {
    $date_end = null;
  }
  if ($quantity < 0) {
    $quantity = 0;
  } elseif ($quantity > $mstock['m2']) {
    $oldquantity = $quantity;
    $quantity = $mstock['m2'];
  }
  $thirdparty = doliConnect('thirdparty', $current_user, false);
  
  if ( empty($orderid) ) {
    $rdr = [
      'socid' => $thirdparty->id,
      'date' => time(),
      'demand_reason_id' => 1,
      'cond_reglement_id' => $thirdparty->cond_reglement_id,
      'shipping_method_id' => $thirdparty->shipping_method_id,
      'module_source' => 'doliconnect',
      'modelpdf' =>  doliconst("COMMANDE_ADDON_PDF"),
      'pos_source' => get_current_blog_id(),
	  ];                  
    $order = callDoliApi("POST", "/orders", $rdr, 0);
    $order = doliConnect('order', $current_user, false, true);
  }
  if (isset($thirdparty->tva_assuj) && empty($thirdparty->tva_assuj)) {
    if (isset($product->tva_tx)) $product->tva_tx = 0;
  }
  if ( doliCheckModules('adherent') && $product->id == doliconst("ADHERENT_PRODUCT_ID_FOR_SUBSCRIPTIONS") && !empty(doliconst("FACTURE_TVAOPTION")) && !empty(doliconst("ADHERENT_VAT_FOR_SUBSCRIPTIONS"))) {
    $price_base_type = 'TTC';
  } else {
    $price_base_type = 'HT';
  }
  
  if (empty($product->status)) {
    if (!empty($mstock['lineid'])) $deleteline = callDoliApi("DELETE", "/orders/".$order->id."/lines/".$mstock['lineid'], null, 0);
    $order = doliConnect('order', $current_user, false, true);
    $response['message'] = __( 'This item has been deleted to basket', 'doliconnect');
    $response['items'] = doliconnect_countitems($order);
    $response['lines'] = doliline($order);
    $response['dolicart'] = doliOffcanvasCart( $current_user );
    $response['line'] = null;
    if (empty($relatedproduct)) $response['newqty'] = $quantity;
    $response['total'] = doliprice($order, 'ttc', isset($order->multicurrency_code) ? $order->multicurrency_code : null);
    return $response;
  } elseif ( $order->id > 0 && $quantity > 0 && empty($mstock['lineid'])) {                                                                                  
    $adln = [
      'fk_product' => $product->id,
      'desc' => $product->description,
      'date_start' => $date_start,
      'date_end' => $date_end,
      'qty' => $quantity,
      'tva_tx' => $product->tva_tx, 
      'price_base_type' => $price_base_type, 
      'remise_percent' => $price['discount'],
      'subprice' => $price['subprice'],
      'localtax1_tx'=> (isset($mstock['localtax1_tx'])?$mstock['localtax1_tx']: null),
      'localtax2_tx' => (isset($mstock['localtax2_tx'])?$mstock['localtax2_tx']: null),
      'info_bits' => (isset($mstock['info_bits'])?$mstock['info_bits']: null),
      'product_type' => (isset($mstock['product_type'])?$mstock['product_type']: null),
      'fk_parent_line' => $relatedproduct,
      'fk_fournprice' => (isset($mstock['fk_fournprice'])?$mstock['fk_fournprice']: null),
      'pa_ht'=> (isset($mstock['pa_ht'])?$mstock['pa_ht']: null),
      'label' => (isset($mstock['label'])?$mstock['label']: null),
      'special_code' => (isset($mstock['special_code'])?$mstock['special_code']: null),
      'fk_unit' => (isset($mstock['fk_unit'])?$mstock['fk_unit']: null),
      'multicurrency_subprice' => (isset($mstock['multicurrency_subprice'])?$mstock['multicurrency_subprice']: null),
      'ref_ext' => (isset($mstock['ref_ext'])?$mstock['ref_ext']: null),
      'rang' => (isset($mstock['rang'])?$mstock['rang']: null),
      'array_options' => $array_options
	  ];                 
    $addline = callDoliApi("POST", "/orders/".$order->id."/lines", $adln, 0);
    
    $addline = callDoliApi("GET", "/orders/".$order->id."/lines/".$addline, null, 0);
    $order = doliConnect('order', $current_user, false, true);
    $mstock = doliProductStock($product, true, true, $array_options, $addline);
    $response['message'] = __( 'This item has been added to basket', 'doliconnect');
    $response['items'] = doliconnect_countitems($order);
    $response['lines'] = doliline($order);
    $response['dolicart'] = doliOffcanvasCart( $current_user );
    $response['line'] = $mstock['lineid'];
    if (empty($relatedproduct)) $response['newqty'] = $quantity;
    $response['total'] = doliprice($order, 'ttc', isset($order->multicurrency_code) ? $order->multicurrency_code : null);
    $response['doliproductbutton'] = doliProductCart($product, $price, $mstock['line'], true, true, $array_options);
    return $response;
  } elseif ( $order->id > 0 && $mstock['lineid'] > 0 ) {
    if ( $quantity < 1 ) {
      $deleteline = callDoliApi("DELETE", "/orders/".$order->id."/lines/".$mstock['lineid'], null, 0);
      $order = doliConnect('order', $current_user, false, true);
      $mstock = doliProductStock($product, true, true, $array_options, null);
      $response['message'] = __( 'This item has been deleted to basket', 'doliconnect');
      $response['items'] = doliconnect_countitems($order);
      $response['lines'] = doliline($order);
      $response['dolicart'] = doliOffcanvasCart( $current_user );
      $response['line'] = null;
      if (empty($relatedproduct)) $response['newqty'] = 0;
      $response['total'] = doliprice($order, 'ttc', isset($order->multicurrency_code) ? $order->multicurrency_code : null);
      $response['doliproductbutton'] = doliProductCart($product, $price, $mstock['line'], true, true, $array_options);
      return $response;
    } else {
      $uln = [
        'desc' => $product->description,
        'date_start' => $date_start,
        'date_end' => $date_end,
        'qty' => $quantity,
        'tva_tx' => $product->tva_tx, 
        'price_base_type' => $price_base_type, 
        'remise_percent' => $price['discount'],
        'subprice' => $price['subprice'],
        'localtax1_tx'=> (isset($mstock['localtax1_tx'])?$mstock['localtax1_tx']: null),
        'localtax2_tx' => (isset($mstock['localtax2_tx'])?$mstock['localtax2_tx']: null),
        'info_bits' => (isset($mstock['info_bits'])?$mstock['info_bits']: null),
        'product_type' => (isset($mstock['product_type'])?$mstock['product_type']: null),
        'fk_parent_line' => (isset($mstock['fk_parent_line'])?$mstock['fk_parent_line']: null),
        'fk_fournprice' => (isset($mstock['fk_fournprice'])?$mstock['fk_fournprice']: null),
        'pa_ht'=> (isset($mstock['pa_ht'])?$mstock['pa_ht']: null),
        'label' => (isset($mstock['label'])?$mstock['label']: null),
        'special_code' => (isset($mstock['special_code'])?$mstock['special_code']: null),
        'fk_unit' => (isset($mstock['fk_unit'])?$mstock['fk_unit']: null),
        'multicurrency_subprice' => (isset($mstock['multicurrency_subprice'])?$mstock['multicurrency_subprice']: null),
        'ref_ext' => (isset($mstock['ref_ext'])?$mstock['ref_ext']: null),
        'array_options' => $array_options
	    ];                  
      $updateline = callDoliApi("PUT", "/orders/".$order->id."/lines/".$mstock['lineid'], $uln, 0);
      $order = doliConnect('order', $current_user, false, true);
      $warehouse = doliconst('DOLICONNECT_ID_WAREHOUSE');
      $response['message'] = __( 'Quantities have been changed', 'doliconnect');
      $response['items'] = doliconnect_countitems($order);
      $response['lines'] = doliline($order);
      $mstock = doliProductStock($product, true, true, $array_options, $mstock['line']);
      $response['line'] = $mstock['lineid'];
      $response['dolicart'] = doliOffcanvasCart( $current_user );
      if (empty($relatedproduct)) $response['newqty'] = $quantity;
      $response['total'] = doliprice($order, 'ttc', isset($order->multicurrency_code) ? $order->multicurrency_code : null);
			$response['doliproductbutton'] = doliProductCart($product, $price, $mstock['line'], true, true, $array_options);
      return $response;
    }
  } elseif ( $order->id > 0 && is_null($mstock['lineid']) ) {
    $order = doliConnect('order', $current_user, false, true);
    $response['message'] = __( 'Quantities have been changed', 'doliconnect');
    $response['items'] = doliconnect_countitems($order);
    $response['lines'] = doliline($order);
    $mstock = doliProductStock($product, true, true, $array_options, $mstock['line']);
    $response['line'] = $mstock['lineid'];
    $response['dolicart'] = doliOffcanvasCart( $current_user );
    if (empty($relatedproduct)) $response['newqty'] = $quantity;
    $response['total'] = doliprice($order, 'ttc', isset($order->multicurrency_code) ? $order->multicurrency_code : null);
    $response['doliproductbutton'] = doliProductCart($product, $price, $mstock['line'], true, true, $array_options);
    return $response;
  } else {
    return false;
  }
}

function doliWishlist($thirdparty, $productid, $lineid, $refresh = false, $nohtml = false) {
  if (isset($thirdparty->id)) {
    $thirdpartyid = $thirdparty->id;
  } else {
    $thirdpartyid = null;
  }
  $request = "/wishlist?sortfield=p.label&sortorder=ASC&thirdparty_ids=".$thirdpartyid."&pagination_data=true&sqlfilters=(t.priv:=:0)";
  $object = callDoliApi("GET", $request, null, dolidelay('product', $refresh));
  if ( doliversion('19.0.0') && isset($object->data) ) { $wishlist = $object->data; } else { $wishlist = $object; }
  if (!$nohtml) {
    $wish = '<button class="btn btn-sm btn-light border border-light-subtle" id="wish-prod-'.$productid.'" value="wish" type="submit" onclick="doliCartButton(\'updateLine\', '.$productid.', '.$lineid.', 1, null, \'wish\');"><i class="fa-regular fa-heart"></i></button>';
  } else {
    $wish = false;
  }
  foreach ($wishlist as $wsh) {
    if (isset($wsh->fk_product) && $productid == $wsh->fk_product) {
      if (!$nohtml) {
        $wish = '<button class="btn btn-sm btn-light border border-light-subtle" id="wish-prod-'.$productid.'" value="wish" type="submit" onclick="doliCartButton(\'updateLine\', '.$productid.', '.$lineid.', 1, null, \'wish\');"><i class="fa-solid fa-heart" style="color:Fuchsia"></i></button>';
      } else {
        $wish = $wsh->id;
      }
    }
  }
  return $wish;
}

function doliProductCart($product, $price, $line = null, $refresh = null, $context = null, $array_options = array()) {
global $current_user;

  if (is_object($line) && isset($line->array_options)) { 
    $lineid = $line->id;
    $linearray_options = (array) $line->array_options;
  }  else {
    $lineid = 0;
    $linearray_options = $array_options;
  }
  $thirdparty = doliConnect('thirdparty', $current_user, false, $refresh);
  $mstock = doliProductStock($product, $refresh, true, $linearray_options, $line);
  //var_dump($mstock);
  wp_enqueue_script( 'dolicart');
  $button = '<div id="doliform-product-'.$product->id.'-'.$mstock['lineid'].'" name="doliform-product-'.$product->id.'" class="d-grid gap-2">';
  if ($context =='dolioffcanvascart' && !empty($lineid) && $lineid > 0) {
    $button .= '<div class="input-group input-group-sm m-0 p-0">';
    $button .= '<label class="input-group-text border border-0 bg-transparent text-dark" for="qty-prod-'.$product->id.'">'.__( 'Qty :', 'doliconnect').'</label>';
    if (doliconst("ADHERENT_PRODUCT_ID_FOR_SUBSCRIPTIONS") == $product->id) {
      $button .= '<input id="qty-prod-'.$product->id.'" type="text" class="form-control form-control-sm border border-0 bg-transparent text-dark" value="'.$mstock['qty'].'" aria-label="'.$mstock['qty'].'" style="text-align:center;" disabled readonly>';
    } else {
      $button .= "<select class='form-select form-select-sm border border-0 bg-transparent text-dark' id='qty-prod-".$product->id."' onchange='doliCartButton(\"updateLine\", ".$product->id.", ".$line->id.", document.getElementById(\"qty-prod-".$product->id."\").value, ".json_encode($linearray_options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).", \"modify\");'>";
      $loop = $mstock['m2']/$mstock['step'];
      for ($i = 1; $i <= $loop; $i++) {
        $qty = $i*$mstock['step'];
        $button .= '<option value="'.$qty.'" ';
        if ($qty == $mstock['qty']) $button .= ' selected';
        $button .= '>'.$qty.'</option>';
      }
      $button .= '</select>';
    }
    $button .= '</div>';    
  } elseif ($context =='dolioffcanvascart_delete' && !empty($lineid) && $lineid > 0) {
    $button .= "<button class='btn btn-link btn-sm m-0 p-0 border border-0 bg-transparent text-dark' id='button-addon2' name='delete' value='delete' type='submit' onclick='doliCartButton(\"updateLine\", ".$product->id.", ".$line->id.", 0, ".json_encode($linearray_options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).", \"delete\");'><i class='fa-solid fa-xmark'></i></button>";
  } else {
    if (empty($product->status)) {
        $button .= '<div class="btn-group" role="group" aria-label="Basic example">';
        $button .= '<input id="qty-prod-'.$product->id.'" type="text" class="form-control form-control-sm" value="'.__( 'Item not in sale', 'doliconnect').'" aria-label="'.__( 'Item not in sale', 'doliconnect').'" style="text-align:center;" disabled readonly>';
        if ( !empty($context) && doliCheckModules('wishlist')) {
          $button .= doliWishlist($thirdparty, $product->id, $mstock['lineid'], $refresh);
        }
        $button .= '</div>';
      } elseif (isset($product->required) && !empty($product->required)) {
        $button .= '<div class="btn-group" role="group" aria-label="Basic example">';
        $button .= '<input id="qty-prod-'.$product->id.'" type="text" class="form-control form-control-sm" value="'.$mstock['qty'].'" aria-label="'.$mstock['qty'].'" style="text-align:center;" disabled readonly>';
        $button .= '</div>';
      } elseif ( empty(doliconnectid('dolicart')) || empty(doliconnectid('dolicart')) ) {
        $button .= "<a class='btn btn-block btn-info' href='".doliconnecturl('dolicontact')."?type=COM' role='button' title='".__( 'Contact us', 'doliconnect')."'>".__( 'Contact us', 'doliconnect').'</a>';
      } elseif ( isset($thirdparty->status) && $thirdparty->status != '1' ) {
        $button .= "<a class='btn btn-block btn-outline-secondary disabled' href='".doliconnecturl('dolicontact')."?type=COM' role='button' title='".__( 'Account closed', 'doliconnect')."' disabled>".__( 'Account closed', 'doliconnect').'</a>';
      } elseif ( isset($thirdparty->client) && ( ($thirdparty->client == '2' && get_option('doliProductclient') != '2' ) ) ) {
        $button .= '<input id="qty-prod-'.$product->id.'" type="text" class="form-control form-control-sm" value="'.__( 'Only for our customers', 'doliconnect').'" aria-label="'.__( 'Only for our customers', 'doliconnect').'" style="text-align:center;" disabled readonly>';
      } elseif ( $price['refprice'] < 0 ) {
        $button .= '<input id="qty-prod-'.$product->id.'" type="text" class="form-control form-control-sm" value="'.__( 'Please contact us', 'doliconnect').'" aria-label="'.__( 'Please contact us', 'doliconnect').'" style="text-align:center;" disabled readonly>';
      } elseif ( is_user_logged_in() && doliCheckModules('commande') && doliconnectid('dolicart') > 0 && isset($thirdparty->status) && $thirdparty->status == '1' ) {
        if (!empty($line->fk_parent_line) && !empty($mstock['fk_parent_line'])) {
          $button .= '<input id="qty-prod-'.$product->id.'" type="text" class="form-control form-control-sm" value="'.__( 'Linked item', 'doliconnect').'" aria-label="'.__( 'Linked item', 'doliconnect').'" style="text-align:center;" disabled readonly>';
        } elseif ( $mstock['stock'] <= 0 || $mstock['m2'] < $mstock['step'] ) { 
          $button .= '<input id="qty-prod-'.$product->id.'" type="text" class="form-control form-control-sm" value="'.__( 'Unavailable', 'doliconnect').'" aria-label="'.__( 'Unavailable', 'doliconnect').'" style="text-align:center;" disabled readonly>';
        } elseif (doliCheckModules('adherent') && $product->id == doliconst("ADHERENT_PRODUCT_ID_FOR_SUBSCRIPTIONS")) {
          $adherent = doliConnect('member', $current_user, false, $refresh);
          $button .= '<div class="btn-group" role="group" aria-label="Basic example">';
          if (!empty($mstock['qty'])) {
            $button .= "<button class='btn btn-sm btn-dark border border-dark' name='delete' value='delete' type='submit' onclick='doliCartButton(\"updateLine\", ".$product->id.", ".$mstock['lineid'].", 0, ".json_encode($linearray_options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).", \"delete\");'><i class='fa-solid fa-trash-can'></i></button>";
          } elseif (isset($adherent->status) && $adherent->status == '1') {
            $button .= "<button class='btn btn-sm btn-danger' name='plus' value='plus' type='submit' onclick='doliCartButton(\"updateLine\", ".$product->id.", ".$mstock['lineid'].", 1, ".json_encode($linearray_options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).", \"membership\");'>".__('Pay my subscription', 'doliconnect')."</button>";
          } else {
            $button .= "<a class='btn btn-sm btn-block btn-info' href='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' role='button' title='".__( 'Subscribe', 'doliconnect')."'>".__( 'Subscribe', 'doliconnect').'</a>';
          }
          $button .= '</div>';
        } else {
          $button .= '<div class="mb-3"><div class="input-group btn-outline-secondary">';
          if (!empty($mstock['qty'])) $button .= "<button class='btn btn-sm btn-dark border border-dark' name='delete' value='delete' type='submit' onclick='doliCartButton(\"updateLine\", ".$product->id.", ".$mstock['lineid'].", 0, ".json_encode($linearray_options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).", \"delete\");'><i class='fa-solid fa-trash-can'></i></button>";
          $button .= "<button class='btn btn-sm btn-light border border-light-subtle";
          if (empty($mstock['qty'])) $button .= " disabled";
          $button .= "' name='minus' value='minus' type='submit' onclick='doliCartButton(\"updateLine\", ".$product->id.", ".$mstock['lineid'].", document.getElementById(\"qty-prod-".$product->id."\").value, ".json_encode($linearray_options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).", \"minus\");'><i class='fa-solid fa-minus'></i></button>";
          $button .= "<input class='form-control form-control-sm btn-light border border-light-subtle' id='qty-prod-".$product->id."' type='tel' onchange='doliCartButton(\"updateLine\", ".$product->id.", ".$mstock['lineid'].", document.getElementById(\"qty-prod-".$product->id."\").value, ".json_encode($linearray_options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).", \"modify\");' placeholder='' aria-label='Quantity' value='".$mstock['qty']."' style='text-align:center;'>";
          $button .= "<button class='btn btn-sm btn-light border border-light-subtle' name='plus' value='plus' type='submit' onclick='doliCartButton(\"updateLine\", ".$product->id.", ".$mstock['lineid'].", document.getElementById(\"qty-prod-".$product->id."\").value, ".json_encode($linearray_options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).", \"plus\");'><i class='fa-solid fa-plus'></i></button>"; 
          if ( !empty($context) && doliCheckModules('wishlist')) {
            $button .= doliWishlist($thirdparty, $product->id, $mstock['lineid'], $refresh);
          }
          $button .= '</div>';
          if (isset($mstock['step']) && $mstock['step']>1) {
            $button .= '<div class="form-text" id="basic-addon4">'.sprintf(__( 'Sold by %s', 'doliconnect'), $mstock['step']).'</div>';
          }
          $button .= '</div>';
        } 
      } else {   
        $button .= "<a href='".wp_login_url( get_permalink() )."?redirect_to=".get_permalink()."' class='btn btn-sm btn-outline-secondary' type='button'>".__( 'Sign in', 'doliconnect').'</a>';
      }
  }
  $button .= '</div>';
  return $button;
}

function doliProducPriceTaxAssuj( $price_ht, $price_ttc, $vat) {
  if (!empty(get_option('dolibarr_b2bmode')) || empty($vat)) {
    return $price_ht;
  } else {
    return $price_ttc;
  }
}

function doliOffcanvasCart( $current_user, $object = null) {
  if (empty($object)) {
    $object = doliConnect('order', $current_user, false);
  }
  $offcanvas = '<div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasDoliCartLabel">'.sprintf(__( 'My cart (%s items)', 'doliconnect'), doliconnect_countitems($object)).'</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>';
  $offcanvas .= '<div class="offcanvas-body"><ul class="list-group list-group-flush">';
  $offcanvas .= doliline($object, false, 'dolioffcanvascart');
  $offcanvas .= '</ul></div>';
  if ($object->id > 0 && isset($object->lines) && !empty($object->lines)) {
    $offcanvas .= '<div class="offcanvas-footer m-3">';
    $offcanvas .= '<div class="d-grid gap-2">';
    $offcanvas .= "<a type='button' class='btn btn-outline-secondary btn-sm' href='#' type='submit' onclick='doliCartButton(\"updateCart\", 0, 0, 0, null, \"delete\");'>".__('Empty the basket', 'doliconnect').'</a>';
      $arr_params = array( 'checkout' => wp_create_nonce( 'dolicart-'. $object->id.'-'.$current_user->ID));  
      $return = esc_url( add_query_arg( $arr_params, doliconnecturl('dolicart')) );
    $offcanvas .= '<a type="button" class="btn btn-primary btn-lg" href="'.$return.'">'.sprintf(__( ' Order - %s', 'doliconnect'), doliprice($object, 'ttc', isset($object->multicurrency_code) ? $object->multicurrency_code : null)).'</a>';
    $offcanvas .= '</div>';
    $offcanvas .= '</div>';
  }
  return $offcanvas;
}

function doliProductDisplayPrice( $product, $price, $refresh = false) {
  global $current_user;

  $thirdparty = doliConnect('thirdparty', $current_user, false);
  if (isset($thirdparty->tva_assuj) && empty($thirdparty->tva_assuj)) {
    if (isset($product->tva_tx))  $product->tva_tx = 0;
  }
  $orderfo = doliConnect('order', $current_user, false, $refresh);
  $currency=isset($orderfo->multicurrency_code)?$orderfo->multicurrency_code:strtoupper(doliconst("MAIN_MONNAIE"));

  $button = '<script type="text/javascript">';
  $button .= 'jQuery(document).ready(function($) {
  $("#popover-price-'.$product->id.'").popover({
    placement : "auto",
    delay: { "show": 150, "hide": 150 },
    trigger : "focus",
    html : true
  })
  });';
  $button .= '</script>';
  if ( $price['refprice'] < 0 ) {
    $button .= '';
  } elseif (empty($product->status)) {
    $button .= '';
  } else {
    $explication = doliProducPriceTaxAssuj(__( 'Displayed price is excluded VAT', 'doliconnect'), __( 'Displayed price is included VAT', 'doliconnect'), $product->tva_tx);
    $explication .= sprintf(__( 'VAT rate of %s', 'doliconnect'), $price['vat']);
    $explication .= "<ul>";
    $explication .= sprintf(__( 'Initial sale price: %s', 'doliconnect'), doliprice(doliProducPriceTaxAssuj($price['ht'], $price['ttc'], $product->tva_tx), $currency));
    if (isset($customer_discount) && !empty($customer_discount) && !empty($price['discount'])) $explication .= sprintf(__( 'Your customer discount is %s percent', 'doliconnect'), $customer_discount);
    if (isset($discountlabel) && !empty($discountlabel)) $explication .= $discountlabel;
    if ($price['ttc'] != $price['ttc3']) $explication .= sprintf(__( 'Discounted price: %s', 'doliconnect'), doliprice( doliProducPriceTaxAssuj($price['ht3'], $price['ttc3'], $product->tva_tx), $currency));
    $explication .= "</ul>";
    $button .= '<div class="card"><div class="card-body">';
    $button .= "<a tabindex='0' id='popover-price-".$product->id."' class='btn btn-light position-relative top-0 end-0";
    if (!empty($price['discount'])) $button .= " text-danger";
    $button .= "' data-bs-container='body' data-bs-toggle='popover' data-bs-trigger='focus' title='".__( 'About price', 'doliconnect')."' data-bs-content=''>";//".$explication."
    $button .= doliprice(doliProducPriceTaxAssuj($price['ht3'], $price['ttc3'], $product->tva_tx), (empty(get_option('dolibarr_b2bmode'))?'ttc':'ht'), $currency);
    $date = new DateTime(); 
    $date->modify('NOW');
    if (!empty(get_option('dolicartnewlist')) && get_option('dolicartnewlist') != 'none') { 
      $date->modify('FIRST DAY OF LAST '.get_option('dolicartnewlist').' MIDNIGHT');
      $lastdate = $date->format('Y-m-d');
    } elseif (empty(get_option('dolicartnewlist'))) {
      $date->modify('FIRST DAY OF LAST MONTH MIDNIGHT');
      $lastdate = $date->format('Y-m-d');
    } else {
      $lastdate = $date->format('Y-m-d');
    }
    if ($product->date_creation >= $lastdate) $button .= '<span class="position-absolute top-0 start-0 translate-middle badge rounded-pill bg-warning">'.__( 'Novelty', 'doliconnect').'<span class="visually-hidden">Novelty</span></span>';
    if (!empty($price['discount'])) $button .= '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">-'.round($price['discount']).'%<span class="visually-hidden">discount</span></span>';
    if (!empty($product->net_measure) && !empty($product->net_measure_units)) { 
      $unit = callDoliApi("GET", "/setup/dictionary/units?sortfield=rowid&sortorder=ASC&limit=1&active=1&sqlfilters=(t.rowid:like:'".$product->net_measure_units."')", null, dolidelay('constante'));
      $button .= '<span class="position-absolute top-100 start-0 translate-middle badge rounded-pill bg-info"><small>'.doliprice( $price['refprice']/$product->net_measure, null, $currency).'/'.$unit[0]->short_label.'<span class="visually-hidden">net measure price</span></small></span>';
    }
    if (!empty($price['discount'])) $button .= '<span class="position-absolute top-100 start-100 translate-middle badge bg-light text-dark"><small><s>'.doliprice(doliProducPriceTaxAssuj($price['ht'], $price['ttc'], $product->tva_tx), (empty(get_option('dolibarr_b2bmode'))?'ttc':'ht'), $currency).'</s><span class="visually-hidden">initial price</span></small></span>';
    $button .= '</a>';
    $button .= '</div></div>';
  }
  return $button;
}

function doliProductPrice($product, $quantity = null, $refresh = false) {
global $current_user;
  $button = null;
  $price = array();
  $thirdparty = doliConnect('thirdparty', $current_user, false);
  if (isset($thirdparty->tva_assuj) && empty($thirdparty->tva_assuj)) {
    if (isset($product->tva_tx))  $product->tva_tx = 0;
  }
  $orderfo = doliConnect('order', $current_user, false, $refresh);
  $currency=isset($orderfo->multicurrency_code)?$orderfo->multicurrency_code:strtoupper(doliconst("MAIN_MONNAIE"));
  if ( $product->type == '1' && !is_null($product->duration_unit) && '0' < ($product->duration_value)) {
    if ( $product->duration_unit == 'i' ) {
      $altdurvalue=60/$product->duration_value; 
    }
  }
  $price['discount'] = isset($thirdparty->remise_percent)?$thirdparty->remise_percent:'0';
  $customer_discount = $price['discount'];

  if ( !empty(doliconst("PRODUIT_MULTIPRICES")) && !empty($product->multiprices_ttc) ) {

    if (isset($thirdparty->price_level) && !empty($thirdparty->price_level)) {
      $level = $thirdparty->price_level;
    } else {
      $level = 1;
    }
    $price_min_ttc = $product->multiprices_min_ttc->$level; 
    $price_min_ht = $product->multiprices_min->$level;  
    $price_ttc = $product->multiprices_ttc->$level;
    $price_ht = $product->multiprices->$level; 
    $price_min_ttc3 = $product->multiprices_min_ttc->$level;
    $price_min_ht3 = $product->multiprices_min->$level;  
    $price_ttc3 = $product->multiprices_ttc->$level;
    $price_ht3 = $product->multiprices->$level;
    $vat = $product->tva_tx;
    $refprice=doliProducPriceTaxAssuj($price_ht, $price_ttc, $product->tva_tx);
    $multiprix = doliProducPriceTaxAssuj($product->multiprices, $product->multiprices_ttc, $product->tva_tx);
  } else {
    if ( !empty(doliconst("PRODUIT_CUSTOMER_PRICES")) && isset($thirdparty->id) && !empty($thirdparty->id) ) {
      $product2 = callDoliApi("GET", "/products/".$product->id."/selling_multiprices/per_customer", null, dolidelay('product', $refresh));
      if ( !isset($product2->error) && $product2 != null ) {
        $new_product2 = array_filter($product2, function($obj){
          global $current_user;
          $thirdparty = doliConnect('thirdparty', $current_user, false);
          if (isset($obj->fk_soc)) {
            if ($obj->fk_soc != $thirdparty->id)  { return false; }
          }
          return true;
          });
          $product2 = null;
          foreach ($new_product2 as $array) {
            $product2 = $array;
          }
        }
    }
    if ( doliCheckModules('discountprice') ) {
      $date = new DateTime(); 
      $date->modify('NOW');
      $lastdate = $date->format('Y-m-d');
      $requestp = "/discountprice?productid=".$product->id."&sortfield=t.rowid&sortorder=ASC&sqlfilters=(t.date_begin%3A%3C%3D%3A'".$lastdate."')and(t.date_end%3A%3E%3D%3A'".$lastdate."')and(d.tosell:=:1)";
      $object = callDoliApi("GET", $requestp, null, dolidelay('product', $refresh));
      if ( doliversion('19.0.0') && isset($object->data) ) { $product3 = $object->data; } else { $product3 = $object; }
    }
    if ( doliCheckModules('discountprice') && isset($product3) && !isset($product3->error) && isset($product3[0])) {
      if (!empty($product3[0]->discount)) {
        $price_ttc3=$product->price_ttc-($product->price_ttc*$product3[0]->discount/100);
        $price_ht3=$product->price-($product->price*$product3[0]->discount/100);
        $price_ttc=$product->price_ttc;
        $price_ht=$product->price;
        $vat = $product->tva_tx;
        $price['discount'] = $product3[0]->discount;
      } elseif (!empty($product3[0]->price)) {
        $price_ht3=$product3[0]->price; 
        $price_ht=$product->price; 
        $price['discount'] = 100-(100*$price_ht3/$price_ht);
        $price_ttc3=$product->price_ttc-($product->price_ttc*$price['discount']/100);
        $price_ttc=$product->price_ttc;
        $vat = $product->tva_tx;
      } elseif (!empty($product3[0]->price_ttc)) {
        $price_ttc3=$product3[0]->price_ttc;
        $price_ttc=$product->price_ttc; 
        $price['discount'] = 100-(100*$price_ttc3/$price_ttc);
        $price_ht3=$product->price-($product->price*$price['discount']/100);
        $price_ht=$product->price;
        $vat = $product->tva_tx;
      }
      $price_min_ttc=$product->price_min_ttc;
      $price_min_ht=$product->price_min;
      $refprice=doliProducPriceTaxAssuj($price_ht3, $price_ttc3, $product->tva_tx);

      if (!empty($product3[0]->label)) {
        $discountlabel = $product3[0]->label;
      }

    } elseif ( !empty(doliconst("PRODUIT_CUSTOMER_PRICES", $refresh)) && isset($product2) && !empty($product2) && !isset($product2->error) ) {
      $price_min_ttc3=$product->price_min_ttc-($product2->price_min_ttc*$price['discount']/100);
      $price_ttc3=$product->price_ttc-($product2->price_ttc*$price['discount']/100);
      $price_ht3=$product->price-($product2->price*$price['discount']/100);
      $price_min_ttc=$product2->price_min_ttc;
      $price_min_ht=$product2->price_min;
      $price_ttc=$product2->price_ttc;
      $price_ht=$product2->price;
      $vat = $product2->tva_tx;
      $refprice = doliProducPriceTaxAssuj($price_ht, $price_ttc, $product->tva_tx);
    } else {
      $price_min_ttc3=$product->price_min_ttc-($product->price_min_ttc*$price['discount']/100);
      $price_ttc3=$product->price_ttc-($product->price_ttc*$price['discount']/100);
      $price_ht3=$product->price-($product->price*$price['discount']/100);
      $price_min_ttc=$product->price_min_ttc;
      $price_min_ht=$product->price_min;
      $price_ttc=$product->price_ttc;
      $price_ht=$product->price;
      $vat = $product->tva_tx;
      $refprice = doliProducPriceTaxAssuj($price_ht, $price_ttc, $product->tva_tx);
    }


    if ($price_min_ttc == $price_ttc) {
      $price['discount'] = 0;
      $price_ttc3 = $price_min_ttc;
      $price_ht3 = $price_min_ht;
    } elseif ($price_ttc < 0) {
      $price['discount'] = 0;
      $price_ttc3 = -1;
      $price_ht3 = -1;
    } elseif ($price_min_ttc > ($price_ttc-($price_ttc*$price['discount']/100))) {
      $price['discount'] = 100-(100*$price_min_ttc/$price_ttc);
      $price_ttc3 = $price_ttc-($price_ttc*$price['discount']/100);
      $price_ht3 = $price_ht-($price_ht*$price['discount']/100);
    }

  }
  $price['vat'] = $vat;
  $price['refprice'] = $refprice;
  $price['ttc'] = $price_ttc;
  $price['ht'] = $price_ht;
  $price['ttc3'] = $price_ttc3;
  $price['ht3'] = $price_ht3;
  $price['subprice'] = $price_ht;

  return $price;
}

function doliconnect_supplier($product, $refresh = false){
  $brands =  callDoliApi("GET", "/products/".$product->id."/purchase_prices", null, dolidelay('product', $refresh));
  $supplier = "";
  if ( !isset($brands->error) && $brands != null ) {
    $supplier .= "<small><i class='fas fa-industry fa-fw'></i> ";
    $supplier .= _n( 'Supplier:', 'Supplier:', count($brands), 'doliconnect' );
    $i = 0;
    foreach ($brands as $brand) {
      if ($i > 0) $supplier .= ",";
      $thirdparty =  callDoliApi("GET", "/thirdparties/".$brand->fourn_id, null, dolidelay('product', $refresh));
      $supplier .= " ";
      if (!empty(doliconnectid('dolisupplier'))) {
        $supplier .= "<a href='".doliconnecturl('dolisupplier')."?supplier=".$thirdparty->id."'>";
      }
      $supplier .= (!empty($thirdparty->name_alias)?$thirdparty->name_alias:$thirdparty->name);
      if (!empty(doliconnectid('dolisupplier'))) {
        $supplier .= "</a>";
      }
      $i++;
    }
    $supplier .= "</small>";
  }
  return $supplier;
}

// list of products filter
function doliproductlist($productid, $refresh = false, $fk_parent_line = null, $required = null) {
global $current_user;

if (isset($productid) && is_numeric($productid) && $productid > 0) {
  $product = callDoliApi("GET", "/products/".$productid."?includesubproducts=true&includetrans=true", null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
  $product->required = $required;

  $arr_params = array( 'search' => isset($_GET['search'])?$_GET['search']:null, 'category' => isset($_GET['category'])?$_GET['category']:null, 'subcategory' => isset($_GET['subcategory'])?$_GET['subcategory']:null);  
  $producturl = esc_url( add_query_arg( $arr_params, getDoliProductUrl($product->id)) );

  $list = "<li class='list-group-item list-group-item-light list-group-item-action' id='prod-li-".$product->id."'><table width='100%' style='border:0px'><tr><td width='20%' style='border:0px'><center>";
  $list .= '<a href="'.$producturl.'" class="text-decoration-none">'.doliconnect_image('product', $product->id, array('limit'=>1, 'entity'=>$product->entity, 'size'=>'150x150'), esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)).'</a>';
  $list .= "</center></td>";
  $list .= '<td width="80%" style="border:0px"><a href="'.$producturl.'" class="text-body text-decoration-none"><b>'.doliproduct($product, 'label').'</b></a>';
  $list .= "<div class='row'><div class='col position-relative'><p><small>";
  if ( !doliconst('MAIN_GENERATE_DOCUMENTS_HIDE_REF') ) { $list .= "<i class='fas fa-toolbox fa-fw'></i> ".(!empty($product->ref)?$product->ref:'-'); }
  if ( !empty($product->barcode) ) { 
  if ( !doliconst('MAIN_GENERATE_DOCUMENTS_HIDE_REF') ) { $list .= " | "; }
  $list .= "<i class='fas fa-barcode fa-fw'></i> ".$product->barcode; }
  $list .= "</small>";
  if ( ! empty(doliconnectid('dolicart')) ) { 
  $list .= "<br>".doliProductStock($product);
  }
  if ( isset($product->country_id) && !empty($product->country_id) ) {  
    if ( isset($product->country_id) && !empty($product->country_id) ) { 
      $country = callDoliApi("GET", "/setup/dictionary/countries/".$product->country_id."?lang=".doliUserLang($current_user), null, dolidelay('constante'));
      $list .= "<br><small><span class='fi fi-".strtolower($product->country_code)."'></span> ".$country->label;
    }
    if ( isset($product->state_id) && !empty($product->state_id) ) { 
      $state = callDoliApi("GET", "/setup/dictionary/states/".$product->state_id."?lang=".doliUserLang($current_user), null, dolidelay('constante')); 
      $list .= " - ".$state->name; 
    } 
    $list .= "</small>"; 
  }
  if( has_filter('mydoliconnectproductdesc') ) {
    $list .= apply_filters('mydoliconnectproductdesc', $product, 'list');
  }
  $list .= '</p>';
  $list .= '<p>'.substr(sanitize_text_field(doliproduct($product, 'description')), 0, 172).'... <a href="'.$producturl.'" class="stretched-link">['.__( 'Read more...', 'doliconnect').']</a></p>';
  $list .= '</div>';

  if ( ! empty(doliconnectid('dolicart')) ) { 
    $list .= "<div class='col-12 col-md-4'><center>";
    $price = doliProductPrice($product, null, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
    $list .= doliProductDisplayPrice($product ,$price, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
    $list .= doliProductCart($product, $price, null, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), true, array(), $fk_parent_line);
    $list .= "</center></div>";
  }
  $list .= "</div></td></tr></table></li>";
} else {
  $list = "<li class='list-group-item list-group-item-light list-group-item-action'>".__( 'Product not found', 'doliconnect')."</li>";
}
return $list;
}
add_filter( 'doliproductlist', 'doliproductlist', 10, 3);

// list of products filter
function doliproductcard($product, $attributes= null) {
global $current_user;

  if (isset($product->id) && $product->id > 0) {

    $card = '';
    if (defined("DOLIBUG")) {
      $card = dolibug();
    } elseif ($product->id > 0) {
      $card .= '<div class="card-header">'.doliproduct($product, 'label'); 
      if (strpos(esc_url($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']), 'product') !== false) {
        $arr_params = array( 'product');
        $return =  esc_url( remove_query_arg( $arr_params ), $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        $card .= '<a class="btn btn-sm btn-outline-secondary border border-0 float-end" href="'.esc_url( $return ).'"><i class="fas fa-arrow-left"></i>'.__( 'Back', 'doliconnect').'</a>';
      }
      $card .= '</div><div class="card-body"><div class="row">';
      $card .= "<div class='col-12 d-block d-sm-block d-xs-block d-md-none'><center>";
      $card .= doliconnect_image('product', $product->id, array('limit'=>1, 'entity'=>$product->entity, 'size'=>'200x200'), esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
      $card .= "</center>";
      //$card .= wp_get_attachment_image( $attributes['mediaID'], "ptibogxiv_large", "", array( "class" => "img-fluid" ) );
      $card .= '</div>';
      $card .= '<div class="col-md-6 d-none d-md-block"><center>';
      $card .= doliconnect_image('product', $product->id, array('limit'=>1, 'entity'=>$product->entity, 'size'=>'200x200'), esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
      $card .= '</center>';
      //$card .= wp_get_attachment_image( $attributes['mediaID'], "ptibogxiv_square", "", array( "class" => "img-fluid" ) );
      $card .= '</div>';
      $card .= "<div class='col-12 col-md-6'><small>";
      if ( !doliconst('MAIN_GENERATE_DOCUMENTS_HIDE_REF') ) { $card .= "<i class='fas fa-toolbox fa-fw'></i> <span itemprop='sku'>".(!empty($product->ref)?$product->ref:'-').'</span>'; }
      if ( !empty($product->barcode) ) { 
        if ( !doliconst('MAIN_GENERATE_DOCUMENTS_HIDE_REF') ) { $card .= " | "; }
        $card .= "<i class='fas fa-barcode fa-fw'></i> ".$product->barcode;
      }
      $card .= "</small>";
      if ( ! empty(doliconnectid('dolicart')) && !isset($attributes['hideStock']) ) { 
        $card .= '<br>'.doliProductStock($product);
      }
      if (!empty(doliconnectid('dolisupplier')) && !empty(doliconnect_supplier($product, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)))) {
        $card .= '<br>'.doliconnect_supplier($product);
      }
      if (!empty(doliconnect_categories('product', $product, doliconnecturl('dolishop')))) $card .= '<br>'.doliconnect_categories('product', $product, doliconnecturl('dolishop'));
      if ( !empty($product->country_id) ) {  
        $country = callDoliApi("GET", "/setup/dictionary/countries/".$product->country_id."?lang=".doliUserLang($current_user), null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
        $card .= "<br><small><i class='fas fa-globe-europe fa-fw'></i> ".__( 'Origin:', 'doliconnect')." ".$country->label;
        if ( isset($product->state_id) && !empty($product->state_id) ) { 
          $state = callDoliApi("GET", "/setup/dictionary/states/".$product->state_id."?lang=".doliUserLang($current_user), null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null))); 
          $card .= " - ".$state->name;
        } 
        $card .= " <span class='fi fi-".strtolower($product->country_code)."'></span></small>";
      }
      if( has_filter('mydoliconnectproductdesc') ) {
        $card .= apply_filters('mydoliconnectproductdesc', $product, 'card');
      }
      if ( ! empty(doliconnectid('dolicart')) ) { 
        $card .= '<br><br>';
        $price = doliProductPrice($product, null, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
        $card .= doliProductDisplayPrice($product, $price, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
        $card .= doliProductCart($product, $price, null, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
        $card .= '</div>';
      }
      $card .= '</div><div class="col-12"><h6>'.__( 'Description', 'doliconnect' ).'</h6><p>'.doliproduct($product, 'description').'</p>';

      if (!empty(doliconnect_supplier($product))) {
        $brands =  callDoliApi("GET", "/products/".$product->id."/purchase_prices", null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
        if ( !isset($brands->error) && $brands != null ) {
          $i = 0;
          foreach ($brands as $brand) {
            if (!empty($brand->desc_supplier)) {
              $thirdparty =  callDoliApi("GET", "/thirdparties/".$brand->fourn_id, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
              $card .= '<p>';
              $card .= (!empty($thirdparty->name_alias)?$thirdparty->name_alias:$thirdparty->name).'<br>';
              $card .= $brand->desc_supplier;
              $card .= '</p>';
            }
            $i++;
          }
        }
      }

      if (!empty($product->sousprods)) {
      $card .= '</div><div class="col-12"><h6>'.__( 'This item contains:', 'doliconnect' ).'</h6>';
        foreach ($product->sousprods as $subprod) {
          $card .= '<li>'.$subprod->qty.'x '.$subprod->label.'</li>';
        }
      }

      $card .= '</div>';
    } else {
      $card .= '<div class="col-12"><p><center>'.__( 'Item not in sale', 'doliconnect' ).'</center></p>';
    }

    if ( doliversion('23.0.0') && !empty(get_option('doliconnectbeta')) ) {
      $request= "/documents?modulepart=product&id=".$product->id."&limit=100&content_type=application/pdf&pagination_data=true";
      $downloads = callDoliApi("GET", $request, null, dolidelay('document', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));   
      if ( doliversion('22.0.0') && isset($downloads->data) ) { $downloads = $downloads->data; } else { $downloads = $downloads; }

      if ( !isset($downloads->error) && $downloads != null ) {
        $card .= '<div class="col-12"><h6>'.__( 'Download documents', 'doliconnect' ).'</h6>';
        foreach ($downloads as $download) {                                                                                 
          $card .= $download->relativename;
        }
        $card .= '</div>';
      } else {
        $card .= 'non pdf';
      }
    }

    if( has_filter('mydoliconnectproductcard') ) {
      $card .= apply_filters('mydoliconnectproductcard', $product, 'card');
    }

    $card .= '</div>';
  } else {
    $card = "<center><br><br><br><br><div class='align-middle'><i class='fas fa-bomb fa-7x fa-fw'></i><h4>".__( 'Oops! This item does not appear to exist', 'doliconnect' )."</h4></div><br>";
    $card .= '<button type="button" class="btn btn-link" onclick="window.history.back()">'.__( 'Return', 'doliconnect').'</button>';
    $card .= "<br><br><br></center>";
  }

return $card;
}
add_filter( 'doliproductcard', 'doliproductcard', 10, 2);
?>
