<?php

add_action( 'doliconnect_cron_hook', 'doliconnect_cron_process' );

function doliconnect_cron_process($refresh = false) {

    $cronjob = !empty(get_site_option('doliconnect_cronjob_multisite'))?get_site_option('doliconnect_cronjob_multisite'):get_option('doliconnect_cronjob');

    if (!empty($cronjob)) {
        if ($cronjob == '2') $refresh = true;
        $products = array();
        $categories = array();

        if ( is_numeric(doliconst('MAIN_MODULE_DISCOUNTPRICE')) ) {
            $date = new DateTime(); 
            $date->modify('NOW');
            $lastdate = $date->format('Y-m-d');
            $requestp = "/discountprice?sortfield=t.rowid&sortorder=DESC&sqlfilters=(t.date_begin%3A%3C%3D%3A'".$lastdate."')%20AND%20(t.date_end%3A%3E%3D%3A'".$lastdate."')%20AND%20(d.tosell%3A%3D%3A1)";
            $listproduct = callDoliApi("GET", $requestp, null, dolidelay('category', $refresh));
            if ( !isset($listproduct->error) && $listproduct != null ) {
                foreach ($listproduct as $product) {
                    $products[$product->fk_product]['id'] = $product->fk_product;
                    $products[$product->fk_product]['entity'] = $product->entity;
                }
            }
        }

        $shop = doliconst("DOLICONNECT_CATSHOP");
        if ( $shop != null && $shop > 0 ) {
            $request = "/categories?sortfield=t.label&sortorder=ASC&limit=100&type=product&sqlfilters=(t.fk_parent%3A%3D%3A".esc_attr($shop).")";
        } else {
            $request = "/categories?sortfield=t.label&sortorder=ASC&limit=100&type=product&sqlfilters=(t.fk_parent%3A%3D%3A0)";
        }
        $resultatsc = callDoliApi("GET", $request, null, dolidelay('category', $refresh));
        if ( !isset($resultatsc->error) && $resultatsc != null ) {
            foreach ($resultatsc->childs as $category) {
                $categories[$category->id] = $category->id;
                doliconnect_image('category', $category->id, 1, $refresh, $category->entity);
            }
        }

        foreach ($categories as $id => $categorie) {
            $request = "/categories/".$id."?include_childs=true";
            $resultatsc = callDoliApi("GET", $request, null, dolidelay('category', $refresh));
            if ( !isset($resultatsc->error) && $resultatsc != null ) {
                foreach ($resultatsc->childs as $category) {
                    $categories[$category->id] = $category->id;
                    $subcategories[$category->id] = $category->id;
                    doliconnect_image('category', $category->id, 1, $refresh, $category->entity);
                }
            }
        }

        if (isset($subcategories)) {
            foreach ($subcategories as $id => $categorie) {
                $request = "/categories/".$id."?include_childs=true";
                $resultatsc = callDoliApi("GET", $request, null, dolidelay('category', $refresh));
                if ( !isset($resultatsc->error) && $resultatsc != null ) {
                    foreach ($resultatsc->childs as $category) {
                        $categories[$category->id] = $category->id;
                        doliconnect_image('category', $category->id, 1, $refresh, $category->entity);
                    }
                }
            }
        }

        foreach ($categories as $id => $categorie) {
            $requestp = "/products?sortfield=t.rowid&sortorder=DESC&category=".$id."&sqlfilters=(t.tosell%3A%3D%3A1)&limit=1000";
            $listproduct = callDoliApi("GET", $requestp, null, dolidelay('product', $refresh));
            if ( !isset($listproduct->error) && $listproduct != null ) {
                foreach ($listproduct as $product) {
                    $products[$product->id]['id'] = $product->id;
                    $products[$product->id]['entity'] = $product->entity;
                }
            }
        }
 
        foreach ($products as $id => $product) {
            $product1 = callDoliApi("GET", "/products/".$product['id']."?includestockdata=1&includesubproducts=true&includetrans=true", null, dolidelay('product', $refresh));
            doliconnect_image('product', $product['id'], array('limit'=>1, 'entity'=>$product['entity'], 'size'=>'200x200'), $refresh);
            if ( ! empty(doliconnectid('dolicart')) ) {
                if ( is_numeric(doliconst('MAIN_MODULE_DISCOUNTPRICE')) ) {
                    $date = new DateTime(); 
                    $date->modify('NOW');
                    $lastdate = $date->format('Y-m-d');
                    $product2 = callDoliApi("GET", "/discountprice?productid=".$product['id']."&sortfield=t.rowid&sortorder=ASC&sqlfilters=(t.date_begin%3A%3C%3D%3A'".$lastdate."')%20AND%20(t.date_end%3A%3E%3D%3A'".$lastdate."')", null, dolidelay('product', $refresh));
                }
                if ( is_numeric(doliconst("PRODUIT_CUSTOMER_PRICES"))) {
                    $product3 = callDoliApi("GET", "/products/".$product['id']."/selling_multiprices/per_customer", null, dolidelay('product', $refresh));
                }
                if ( is_numeric(doliconst("PRODUIT_CUSTOMER_PRICES", $refresh))) {
                    $product4 = callDoliApi("GET", "/products/".$product['id']."/selling_multiprices/per_customer", null, dolidelay('product', $refresh));
                }
            }
        }
    }
}
?>