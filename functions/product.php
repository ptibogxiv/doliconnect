<?php

function doliproduct($object, $value) {
  if ( function_exists('pll_the_languages') ) { 
    $lang = pll_current_language('locale');
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
          $price = doliProductPrice($product, $qty2, false, true);
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
  $relatedproducts = callDoliApi("GET", $request, null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));  
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
      $related .= apply_filters( 'doliproductlist', $product, false, $fk_parent_line);
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
    if ( function_exists('pll_the_languages') ) { 
      $locale = pll_current_language('locale');
    } else {
      if ( $current_user->locale == null ) { 
        $locale = get_locale(); 
      } else { 
        $locale = $current_user->locale; 
      } 
    }
    $fmt = numfmt_create( $locale, NumberFormatter::CURRENCY );
    return numfmt_format_currency($fmt, $montant, $currency);//.$decimal
  }
}

function doliProductStock($product, $refresh = false, $nohtml = false, $array_options = array(), $fk_line = null) {
global $current_user;
  $mstock = array();
  $warehouse = doliconst('DOLICONNECT_ID_WAREHOUSE');
  $stock = callDoliApi("GET", "/products/".$product->id."/stock?selected_warehouse_id=".$warehouse, null, dolidelay('stock', $refresh));
  if (!empty($product->type) && empty(doliconst('STOCK_SUPPORTS_SERVICES'))) {
    $mstock['stock'] = 999999;
  } elseif (isset($stock->stock_warehouse) && !empty($stock->stock_warehouse) && !empty($warehouse) && $warehouse > 0) {
    if (isset($stock->stock_warehouse->$warehouse->real)) {
      $mstock['stock'] = min(array($stock->stock_reel,$stock->stock_warehouse->$warehouse->real,$stock->stock_theorique));
    } else {
      $mstock['stock'] = 0;
    }
  } elseif (isset($stock->stock_theorique) && isset($stock->stock_reel)) {
    $mstock['stock'] = min(array($stock->stock_theorique,$stock->stock_reel));
  } else {
    $mstock['stock'] = 999999;
  }
  if (isset($product->array_options->options_packaging) && !empty($product->array_options->options_packaging)) {
    $mstock['step'] = $product->array_options->options_packaging;
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

  if (isset($fk_line->id) && !empty($fk_line)) {
    $linearray_options = (array) $fk_line->array_options;
    $mstock['qty'] = $fk_line->qty;
    $mstock['line'] = $fk_line->id;
    $mstock['array_options'] = $linearray_options;
    $mstock['fk_parent_line'] = $fk_line->fk_parent_line;
  } elseif (doliconnector($current_user, 'fk_order') > 0) {
    $order = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order')."?contact_list=0", null, $refresh);
    if ( isset($order->lines) && $order->lines != null ) {
      foreach ($order->lines as $line) {
        $linearray_options = (array) $line->array_options;
        if (isset($product->id) && $line->fk_product == $product->id && isset($fk_line->id) && $line->id == $fk_line->id ) {
           $mstock['qty'] = $line->qty;
           $mstock['line'] = $line->id;
           $mstock['array_options'] = $linearray_options;
           $mstock['fk_parent_line'] = $line->fk_parent_line;
        } elseif (isset($product->id) && $line->fk_product == $product->id && $linearray_options == $array_options) {
          $mstock['qty'] = $line->qty;
          $mstock['line'] = $line->id;
          $mstock['array_options'] = $linearray_options;
          $mstock['fk_parent_line'] = $line->fk_parent_line;
        }
      }
    } 
  } else {
    $mstock['qty'] = 0;
    $mstock['line'] = 0;
    $mstock['array_options'] = $array_options;
    $mstock['fk_parent_line'] = null;
  }

  if (!isset($mstock['qty']) ) {
    $mstock['qty'] = 0;
    $mstock['line'] = 0;
    $mstock['array_options'] = $array_options;
    $mstock['fk_parent_line'] = null;
  }

  if (! isset($mstock['line'])) { $mstock['line'] = null; }
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
    if ( $mstock['stock'] <= 0 || (isset($product->array_options->options_packaging) && !empty($product->array_options->options_packaging) && $mstock['stock'] < $product->array_options->options_packaging) ) { 
      $stock .= "<a tabindex='0' id='popover-stock-".$product->id."' class='badge rounded-pill bg-dark text-white text-decoration-none' data-bs-container='body' data-bs-toggle='popover' data-bs-trigger='focus' title='".__( 'Not available', 'doliconnect')."' data-bs-content='".sprintf( __( 'This item is out of stock and can not be ordered or shipped. %s', 'doliconnect'), $shipping)."'><i class='fas fa-warehouse'></i> ".__( 'Not available', 'doliconnect')."</a>";
    } elseif ( ($mstock['stock'] <= 0 || (isset($product->array_options->options_packaging) && $mstock['stock'] < $product->array_options->options_packaging)) && $product->stock_theorique > $mstock['stock'] ) { 
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
  $orderid = doliconnector($current_user, 'fk_order', true);
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
  $thirdparty = callDoliApi("GET", "/thirdparties/".doliconnector($current_user, 'fk_soc'), null, dolidelay('thirdparty'));
  if ( empty($orderid) ) {
    $rdr = [
      'socid' => doliconnector($current_user, 'fk_soc'),
      'date' => time(),
      'demand_reason_id' => 1,
      'cond_reglement_id' => $thirdparty->cond_reglement_id,
      'shipping_method_id' => $thirdparty->shipping_method_id,
      'module_source' => 'doliconnect',
      'modelpdf' =>  doliconst("COMMANDE_ADDON_PDF"),
      'pos_source' => get_current_blog_id(),
	  ];                  
    $order = callDoliApi("POST", "/orders", $rdr, 0);
    $orderid = $order;
  }
  if (isset($thirdparty->tva_assuj) && empty($thirdparty->tva_assuj)) {
    if (isset($product->tva_tx))  $product->tva_tx = 0;
  }
  if ( doliCheckModules('adherent') && $product->id == doliconst("ADHERENT_PRODUCT_ID_FOR_SUBSCRIPTIONS", dolidelay('constante')) && !empty(doliconst("FACTURE_TVAOPTION", dolidelay('constante'))) && !empty(doliconst("ADHERENT_VAT_FOR_SUBSCRIPTIONS", dolidelay('constante')))) {
    $price_base_type = 'TTC';
  } else {
    $price_base_type = 'HT';
  }
  if (empty($product->status)) {
    if (!empty($mstock['line'])) $deleteline = callDoliApi("DELETE", "/orders/".doliconnector($current_user, 'fk_order')."/lines/".$mstock['line'], null, 0);
    $order = callDoliApi("GET", "/orders/".$orderid."?contact_list=0", null, dolidelay('order', true));
    //$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', true));
    //delete_transient( 'doliconnect_cartlinelink_'.$mstock['line'] );
    $response['message'] = __( 'This item has been deleted to basket', 'doliconnect');
    $response['items'] = doliconnect_countitems($order);
    $response['lines'] = doliline($order);
    if (empty($relatedproduct)) $response['newqty'] = $quantity;
    $response['total'] = doliprice($order, 'ttc', isset($order->multicurrency_code) ? $order->multicurrency_code : null);
    return $response;
  } elseif ( $orderid > 0 && $quantity > 0 && empty($mstock['line'])) {                                                                                  
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
    $addline = callDoliApi("POST", "/orders/".$orderid."/lines", $adln, 0);
    $order = callDoliApi("GET", "/orders/".$orderid."?contact_list=0", null, dolidelay('order', true));
    //$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', true));
    $product = callDoliApi("GET", "/products/stock/".$product->id, null, dolidelay('product', true));
    $response['message'] = __( 'This item has been added to basket', 'doliconnect');
    $response['items'] = doliconnect_countitems($order);
    $response['lines'] = doliline($order);
    if (empty($relatedproduct)) $response['newqty'] = $quantity;
    $response['total'] = doliprice($order, 'ttc', isset($order->multicurrency_code) ? $order->multicurrency_code : null);
    return $response;
  } elseif ( $orderid > 0 && $mstock['line'] > 0 ) {
    if ( $quantity < 1 ) {
      $deleteline = callDoliApi("DELETE", "/orders/".$orderid."/lines/".$mstock['line'], null, 0);
      $order = callDoliApi("GET", "/orders/".$orderid."?contact_list=0", null, dolidelay('order', true));
      //$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', true));
      $product = callDoliApi("GET", "/products/stock/".$product->id, null, dolidelay('product', true));
      //delete_transient( 'doliconnect_cartlinelink_'.$mstock['line'] );
      $response['message'] = __( 'This item has been deleted to basket', 'doliconnect');
      $response['items'] = doliconnect_countitems($order);
      $response['lines'] = doliline($order);
      if (empty($relatedproduct)) $response['newqty'] = 0;
      $response['total'] = doliprice($order, 'ttc', isset($order->multicurrency_code) ? $order->multicurrency_code : null);
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
      $updateline = callDoliApi("PUT", "/orders/".$orderid."/lines/".$mstock['line'], $uln, 0);
      $order = callDoliApi("GET", "/orders/".$orderid."?contact_list=0", null, dolidelay('order', true));
      //$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, dolidelay('doliconnector', true));
      $product = callDoliApi("GET", "/products/stock/".$product->id, null, dolidelay('product', true));
      $response['message'] = __( 'Quantities have been changed', 'doliconnect');
      $response['items'] = doliconnect_countitems($order);
      $response['lines'] = doliline($order);
      if (empty($relatedproduct)) $response['newqty'] = $quantity;
      $response['total'] = doliprice($order, 'ttc', isset($order->multicurrency_code) ? $order->multicurrency_code : null);
      return $response;
    }
  } elseif ( $orderid > 0 && is_null($mstock['line']) ) {
    $order = callDoliApi("GET", "/orders/".$orderid."?contact_list=0", null, dolidelay('order', true));
    $response['message'] = __( 'Quantities have been changed', 'doliconnect');
    $response['items'] = doliconnect_countitems($order);
    $response['lines'] = doliline($order);
    if (empty($relatedproduct)) $response['newqty'] = $quantity;
    $response['total'] = doliprice($order, 'ttc', isset($order->multicurrency_code) ? $order->multicurrency_code : null);
    return $response;
  } else {
    return false;
  }
}

function doliWishlist($thirdpartyid, $productid, $lineid, $refresh = false, $nohtml = false) {
  $request = "/wishlist?sortfield=p.label&sortorder=ASC&thirdparty_ids=".$thirdpartyid."&pagination_data=true&sqlfilters=(t.priv%3A%3D%3A0)";
  $object = callDoliApi("GET", $request, null, dolidelay('product', $refresh));
  if ( doliversion('19.0.0') && isset($object->data) ) { $wishlist = $object->data; } else { $wishlist = $object; }
  if (!$nohtml) {
    $wish = '<button class="btn btn-sm btn-light" id="wish-prod-'.$productid.'" value="wish" type="submit" onclick="doliJavaCartAction(\'updateLine\', '.$productid.', '.$lineid.', 1, \'wish\');"><i class="fa-regular fa-heart"></i></button>';
  } else {
    $wish = false;
  }
  foreach ($wishlist as $wsh) {
    if (isset($wsh->fk_product) && $productid == $wsh->fk_product) {
      if (!$nohtml) {
        $wish = '<button class="btn btn-sm btn-light" id="wish-prod-'.$productid.'" value="wish" type="submit" onclick="doliJavaCartAction(\'updateLine\', '.$productid.', '.$lineid.', 1, \'wish\');"><i class="fa-solid fa-heart" style="color:Fuchsia"></i></button>';
      } else {
        $wish = $wsh->id;
      }
    }
  }
  return $wish;
}

function doliProductCart($product, $line = null, $refresh = null, $wishlist = true, $array_options = array()) {
  global $current_user;
  if (isset($line->array_options)) { 
    $lineid = $line->id;
    $linearray_options = (array) $line->array_options;
  }  else {
    $lineid = 0;
    $linearray_options = $array_options;
  }
  $mstock = doliProductStock($product, $refresh, true, $linearray_options, $line);
  $button = '<div id="doliform-product-'.$product->id.'-'.$mstock['line'].'" class="d-grid gap-2">';
  if ( empty(doliconnectid('dolicart')) || empty(doliconnectid('dolicart')) ) {
    $button .= "<a class='btn btn-block btn-info' href='".doliconnecturl('dolicontact')."?type=COM' role='button' title='".__( 'Contact us', 'doliconnect')."'>".__( 'Contact us', 'doliconnect').'</a>';
  } elseif ( is_user_logged_in() && doliCheckModules('commande') && doliconnectid('dolicart') > 0 ) {
      if (!empty($line->fk_parent_line) && !empty($mstock['fk_parent_line'])) {
        $button .= '<input id="qty-prod-'.$product->id.'" type="text" class="form-control form-control-sm" value="'.__( 'Linked item', 'doliconnect').'" aria-label="'.__( 'Linked item', 'doliconnect').'" style="text-align:center;" disabled readonly>';
      } elseif ( $mstock['stock'] <= 0 || $mstock['m2'] < $mstock['step'] ) { 
        $button .= '<input id="qty-prod-'.$product->id.'" type="text" class="form-control form-control-sm" value="'.__( 'Unavailable', 'doliconnect').'" aria-label="'.__( 'Unavailable', 'doliconnect').'" style="text-align:center;" disabled readonly>';
      } elseif (doliCheckModules('adherent', $refresh) && $product->id == doliconst("ADHERENT_PRODUCT_ID_FOR_SUBSCRIPTIONS", dolidelay('constante'))) {
        $button .= '<div class="btn-group" role="group" aria-label="Basic example">';
        if (!empty($mstock['qty'])) {
          $button .= "<button class='btn btn-sm btn-dark' name='delete' value='delete' type='submit' onclick='doliJavaCartAction(\"updateLine\", ".$product->id.", ".$mstock['line'].", 0, ".json_encode($linearray_options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).", \"delete\");'><i class='fa-solid fa-trash-can'></i></button>";
        } else {
          $button .= "<button class='btn btn-sm btn-danger' name='plus' value='plus' type='submit' onclick='doliJavaCartAction(\"updateLine\", ".$product->id.", ".$mstock['line'].", 1, ".json_encode($linearray_options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).", \"membership\");'>".__('Pay my subscription', 'doliconnect')."</button>";
        }
        $button .= '</div>';
      } else {
        $button .= '<div class="mb-3"><div class="input-group">';
        if (!empty($mstock['qty'])) $button .= "<button class='btn btn-sm btn-dark' name='delete' value='delete' type='submit' onclick='doliJavaCartAction(\"updateLine\", ".$product->id.", ".$mstock['line'].", 0, ".json_encode($linearray_options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).", \"delete\");'><i class='fa-solid fa-trash-can'></i></button>";

        $button .= "<button class='btn btn-sm btn-warning";
        if (empty($mstock['qty'])) $button .= " disabled";
        $button .= "' name='minus' value='minus' type='submit' onclick='doliJavaCartAction(\"updateLine\", ".$product->id.", ".$mstock['line'].", document.getElementById(\"qty-prod-".$product->id."\").value, ".json_encode($linearray_options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).", \"minus\");'><i class='fa-solid fa-minus'></i></button>";
        $button .= "<input id='qty-prod-".$product->id."' type='tel' onchange='doliJavaCartAction(\"updateLine\", ".$product->id.", ".$mstock['line'].", document.getElementById(\"qty-prod-".$product->id."\").value, ".json_encode($linearray_options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).", \'modify\");' class='form-control form-control-sm' placeholder='' aria-label='Quantity' value='".$mstock['qty']."' style='text-align:center;'>";
        $button .= "<button class='btn btn-sm btn-warning' name='plus' value='plus' type='submit' onclick='doliJavaCartAction(\"updateLine\", ".$product->id.", ".$mstock['line'].", document.getElementById(\"qty-prod-".$product->id."\").value, ".json_encode($linearray_options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).", \"plus\");'><i class='fa-solid fa-plus'></i></button>"; 
        if ( !empty($wishlist) && doliCheckModules('wishlist')) {
          $button .= doliWishlist(doliconnector($current_user, 'fk_soc'), $product->id, $mstock['line'], $refresh);
        } 
        $button .= '</div>';
        if (isset($mstock['step']) && $mstock['step']>1) $button .= '<div class="form-text" id="basic-addon4"><small>'.sprintf(__( 'Sold by %s', 'doliconnect'), $mstock['step']).'</small></div>';  
      } 
    } else {
    if ( get_option('doliloginmodal') == '1' ) {       
      $button .= doliModalButton('login', 'doliloginproduct-'.$product->id, __('Sign in', 'doliconnect'), 'button', 'btn btn-sm btn-outline-secondary');
    } else {
      $button .= "<a href='".wp_login_url( get_permalink() )."?redirect_to=".get_permalink()."' class='btn btn-sm btn-outline-secondary' type='button'>".__( 'Sign in', 'doliconnect').'</a>';
    }
  }
  $button .= '</div>';
  return $button;
}

function doliProducPriceTaxAssuj($price_ht, $price_ttc, $vat) {
  if (!empty(get_option('dolibarr_b2bmode')) || empty($vat)) {
    return $price_ht;
  } else {
    return $price_ttc;
  }
}

function doliProductPrice($product, $quantity = null, $refresh = false, $nohtml = false) {
global $current_user;
  $button = null;
  $price = array();
  $thirdparty = callDoliApi("GET", "/thirdparties/".doliconnector($current_user, 'fk_soc'), null, dolidelay('thirdparty'));
  if (isset($thirdparty->tva_assuj) && empty($thirdparty->tva_assuj)) {
    if (isset($product->tva_tx))  $product->tva_tx = 0;
  }
  if (doliconnector($current_user, 'fk_order') > 0) {
    $orderfo = callDoliApi("GET", "/orders/".doliconnector($current_user, 'fk_order'), null, $refresh);
  }
  $currency=isset($orderfo->multicurrency_code)?$orderfo->multicurrency_code:strtoupper(doliconst("MAIN_MONNAIE"));
  if ( $product->type == '1' && !is_null($product->duration_unit) && '0' < ($product->duration_value)) {
    if ( $product->duration_unit == 'i' ) {
      $altdurvalue=60/$product->duration_value; 
    }
  }
  $price['discount'] = !empty(doliconnector($current_user, 'remise_percent'))?doliconnector($current_user, 'remise_percent'):'0';
  $customer_discount = $price['discount'];

  if ( !empty(doliconst("PRODUIT_MULTIPRICES")) && !empty($product->multiprices_ttc) ) {

    if (!empty(doliconnector($current_user, 'price_level'))) {
      $level=doliconnector($current_user, 'price_level');
    } else {
      $level=1;
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
    //$button .= '<table class="table table-sm table-striped table-bordered"><tbody>';
    $multiprix = doliProducPriceTaxAssuj($product->multiprices, $product->multiprices_ttc, $product->tva_tx);
  } else {
    if ( !empty(doliconst("PRODUIT_CUSTOMER_PRICES")) && doliconnector($current_user, 'fk_soc', $refresh) > 0 ) {
      $product2 = callDoliApi("GET", "/products/".$product->id."/selling_multiprices/per_customer", null, dolidelay('product', $refresh));
      if ( !isset($product2->error) && $product2 != null ) {
        $new_product2 = array_filter($product2, function($obj){
          global $current_user;
          $thirdparty_id = doliconnector($current_user, 'fk_soc');
          if (isset($obj->fk_soc)) {
            if ($obj->fk_soc != $thirdparty_id)  { return false; }
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
      $requestp = "/discountprice?productid=".$product->id."&sortfield=t.rowid&sortorder=ASC&sqlfilters=(t.date_begin%3A%3C%3D%3A'".$lastdate."')%20AND%20(t.date_end%3A%3E%3D%3A'".$lastdate."')%20AND%20(d.tosell%3A%3D%3A1)";
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
      $vat=$product->tva_tx;
      $refprice = doliProducPriceTaxAssuj($price_ht, $price_ttc, $product->tva_tx);
    }

    if ($price_min_ttc == $price_ttc) {
      $price['discount'] = 0;
      $price_ttc3 = $price_min_ttc;
      $price_ht3 = $price_min_ht;
    } elseif ($price_min_ttc > ($price_ttc-($price_ttc*$price['discount']/100))) {
      $price['discount'] = 100-(100*$price_min_ttc/$price_ttc);
      $price_ttc3 = $price_ttc-($price_ttc*$price['discount']/100);
      $price_ht3 = $price_ht-($price_ht*$price['discount']/100);
    }

  }
  $price['subprice'] = $price_ht;

  if ($nohtml) { 
    return $price;
  } else {
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
    $explication = doliProducPriceTaxAssuj(__( 'Displayed price is excluded VAT', 'doliconnect'), __( 'Displayed price is included VAT', 'doliconnect'), $product->tva_tx);
    $explication .= sprintf(__( 'VAT rate of %s', 'doliconnect'), $vat);
    //$explication .= "<ul>";
    $explication .= sprintf(__( 'Initial sale price: %s', 'doliconnect'), doliprice(doliProducPriceTaxAssuj($price_ht, $price_ttc, $product->tva_tx), $currency));
    if (isset($customer_discount) && !empty($customer_discount) && !empty($price['discount'])) $explication .= sprintf(__( 'Your customer discount is %s percent', 'doliconnect'), $customer_discount);
    if (isset($discountlabel) && !empty($discountlabel)) $explication .= $discountlabel;
    if ($price_ttc != $price_ttc3) $explication .= sprintf(__( 'Discounted price: %s', 'doliconnect'), doliprice( doliProducPriceTaxAssuj($price_ht3, $price_ttc3, $product->tva_tx), $currency));
    //$explication .= "</ul>";
    $button .= "<a tabindex='0' id='popover-price-".$product->id."' class='btn btn-light position-relative top-0 end-0";
    if (!empty($price['discount'])) $button .= " text-danger";
    $button .= "' data-bs-container='body' data-bs-toggle='popover' data-bs-trigger='focus' title='".__( 'About price', 'doliconnect')."' data-bs-content='".$explication."'>";
    $button .= doliprice(doliProducPriceTaxAssuj($price_ht3, $price_ttc3, $product->tva_tx), $currency);
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
      $unit = callDoliApi("GET", "/setup/dictionary/units?sortfield=rowid&sortorder=ASC&limit=1&active=1&sqlfilters=(t.rowid%3Alike%3A'".$product->net_measure_units."')", null, dolidelay('constante'));
      $button .= '<span class="position-absolute top-100 start-0 translate-middle badge rounded-pill bg-info"><small>'.doliprice( $refprice/$product->net_measure, null, $currency).'/'.$unit[0]->short_label.'<span class="visually-hidden">net measure price</span></small></span>';
    }
    if (!empty($price['discount'])) $button .= '<span class="position-absolute top-100 start-100 translate-middle badge bg-light text-dark"><small><s>'.doliprice(doliProducPriceTaxAssuj($price_ht, $price_ttc, $product->tva_tx), $currency).'</s><span class="visually-hidden">initial price</span></small></span>';
    $button .= '</a><br><br>';
    return $button;
  }
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
function doliproductlist($product, $refresh = false, $fk_parent_line = null) {
global $current_user;

$wish = 0;
if (isset($product->fk_product) && !empty($product->qty)) {
$wish = $product->qty;
$product = callDoliApi("GET", "/products/".$product->fk_product."?includesubproducts=true&includetrans=true", null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
} else {
$product = callDoliApi("GET", "/products/".$product->id."?includesubproducts=true&includetrans=true", null, dolidelay('product', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
}

$arr_params = array( 'search' => isset($_GET['search'])?$_GET['search']:null, 'category' => isset($_GET['category'])?$_GET['category']:null, 'subcategory' => isset($_GET['subcategory'])?$_GET['subcategory']:null, 'product' => $product->id);  
$producturl = esc_url( add_query_arg( $arr_params, doliconnecturl('dolishop')) );

$list = "<li class='list-group-item list-group-item-light list-group-item-action' id='prod-li-".$product->id."'><table width='100%' style='border:0px'><tr><td width='20%' style='border:0px'><center>";
$list .= '<a href="'.$producturl.'" class="text-decoration-none">'.doliconnect_image('product', $product->id, array('limit'=>1, 'entity'=>$product->entity, 'size'=>'150x150'), esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)).'</a>';
$list .= "</center></td>";
//$list .= var_dump($product);
$list .= '<td width="80%" style="border:0px"><a href="'.$producturl.'" class="text-body text-decoration-none"><b>'.doliproduct($product, 'label').'</b></a>';
$list .= "<div class='row'><div class='col'><p><small>";
if ( !doliconst('MAIN_GENERATE_DOCUMENTS_HIDE_REF') ) { $list .= "<i class='fas fa-toolbox fa-fw'></i> ".(!empty($product->ref)?$product->ref:'-'); }
if ( !empty($product->barcode) ) { 
if ( !doliconst('MAIN_GENERATE_DOCUMENTS_HIDE_REF') ) { $list .= " | "; }
$list .= "<i class='fas fa-barcode fa-fw'></i> ".$product->barcode; }
$list .= "</small>";
if ( ! empty(doliconnectid('dolicart')) ) { 
$list .= "<br>".doliProductStock($product);
}
if ( isset($product->country_id) && !empty($product->country_id) ) {  
if ( function_exists('pll_the_languages') ) { 
$lang = pll_current_language('locale');
} else {
$lang = $current_user->locale;
}
if ( isset($product->country_id) && !empty($product->country_id) ) { 
$country = callDoliApi("GET", "/setup/dictionary/countries/".$product->country_id."?lang=".$lang, null, dolidelay('constante'));
$list .= "<br><small><span class='fi fi-".strtolower($product->country_code)."'></span> ".$country->label;
}
if ( isset($product->state_id) && !empty($product->state_id) ) { 
$state = callDoliApi("GET", "/setup/dictionary/states/".$product->state_id."?lang=".$lang, null, dolidelay('constante')); 
$list .= " - ".$state->name; } 
$list .= "</small>"; }
if( has_filter('mydoliconnectproductdesc') ) {
$list .= apply_filters('mydoliconnectproductdesc', $product, 'list');
}

$list .= '<div class="d-grid gap-2"><a href="'.$producturl.'" class="btn btn-link text-body">'.__( 'Read more...', 'doliconnect').'</a></div>';
$list .= '</p></div>';

if ( ! empty(doliconnectid('dolicart')) ) { 
$list .= "<div class='col-12 col-md-4'><center>";
$list .= doliProductPrice($product, null, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
$list .= doliProductCart($product, null, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), true, array(), $fk_parent_line);
$list .= "</center></div>";
}
$list .= "</div></td></tr></table></li>";
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
    } elseif ($product->id > 0 && !empty($product->status)) {
      $card .= '<div class="card-header">'.doliproduct($product, 'label'); 
      if (strpos(esc_url($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']), 'product') !== false) {
        $arr_params = array( 'product');
        $return =  esc_url( remove_query_arg( $arr_params ), $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        $card .= '<a class="float-end text-decoration-none" href="'.esc_url( $return ).'"><i class="fas fa-arrow-left"></i>'.__( 'Back', 'doliconnect').'</a>';
      }
      $card .= '</div><div class="card-body"><div class="row">';
      $card .= "<div class='col-12 d-block d-sm-block d-xs-block d-md-none'><center>";
      $card .= doliconnect_image('product', $product->id, array('limit'=>1, 'entity'=>$product->entity, 'size'=>'200x200'), esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
      $card .= "</center>";
      //$card .= wp_get_attachment_image( $attributes['mediaID'], "ptibogxiv_large", "", array( "class" => "img-fluid" ) );
      $card .= '</div>';
      $card .= '<div class="col-md-4 d-none d-md-block"><center>';
      $card .= doliconnect_image('product', $product->id, array('limit'=>1, 'entity'=>$product->entity, 'size'=>'200x200'), esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
      $card .= '</center>';
      //$card .= wp_get_attachment_image( $attributes['mediaID'], "ptibogxiv_square", "", array( "class" => "img-fluid" ) );
      $card .= '</div>';
      $card .= "<div class='col-12 col-md-8'><small>";
      if ( !doliconst('MAIN_GENERATE_DOCUMENTS_HIDE_REF') ) { $card .= "<i class='fas fa-toolbox fa-fw'></i> <span itemprop='sku'>".(!empty($product->ref)?$product->ref:'-').'</span>'; }
      if ( !empty($product->barcode) ) { 
        if ( !doliconst('MAIN_GENERATE_DOCUMENTS_HIDE_REF') ) { $card .= " | "; }
        $card .= "<i class='fas fa-barcode fa-fw'></i> ".$product->barcode;
      }
      $card .= "</small>";
      if ( ! empty(doliconnectid('dolicart')) && !isset($attributes['hideStock']) ) { 
        $card .= '<br>'.doliProductStock($product);
      }
      if (!empty(doliconnect_supplier($product, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)))) {
        $card .= '<br>'.doliconnect_supplier($product, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
      }
      if (!empty(doliconnect_categories('product', $product, doliconnecturl('dolishop')))) $card .= '<br>'.doliconnect_categories('product', $product, doliconnecturl('dolishop'));
      if ( !empty($product->country_id) ) {  
        if ( function_exists('pll_the_languages') ) { 
          $lang = pll_current_language('locale');
        } else {
          $lang = $current_user->locale;
        }
        $country = callDoliApi("GET", "/setup/dictionary/countries/".$product->country_id."?lang=".$lang, null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
        $card .= "<br><small><i class='fas fa-globe-europe fa-fw'></i> ".__( 'Origin:', 'doliconnect')." ".$country->label;
        if ( isset($product->state_id) && !empty($product->state_id) ) { 
          $state = callDoliApi("GET", "/setup/dictionary/states/".$product->state_id."?lang=".$lang, null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null))); 
          $card .= " - ".$state->name;
        } 
        $card .= " <span class='fi fi-".strtolower($product->country_code)."'></span></small>";
      }
      if( has_filter('mydoliconnectproductdesc') ) {
        $card .= apply_filters('mydoliconnectproductdesc', $product, 'card');
      }
      if ( ! empty(doliconnectid('dolicart')) ) { 
        $card .= '<br><br><div class="jumbotron">';
        $card .= doliProductPrice($product, null, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
        $card .= doliProductCart($product, null, esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
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

      $card .= '</div></div></div>';
    } else {
      $card .= '<div class="col-12"><p><center>'.__( 'Item not in sale', 'doliconnect' ).'</center></p></div>';
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
