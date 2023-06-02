<?php
function doliconnect_membership($current_user, $statut, $type, $delay) {
if ($statut=='1') {
$statut='-1';
$action='POST';
} elseif ($statut=='2') {
$statut='0';
$action='PUT';
} elseif ($statut=='3') {
$statut='-1';
$action='PUT';
} elseif ($statut=='4') {
$statut='1';
$action='PUT';
} elseif ($statut=='5') {
$statut='1';
$action='POST';
} 

list($year, $month, $day) = explode("-", $current_user->billing_birth);
$birth = mktime(0, 0, 0, $month, $day, $year); // debug si non conforme

$thirdparty = callDoliApi("GET", "/thirdparties/".doliconnector($current_user, 'fk_soc'), null, dolidelay('thirdparty'));  

$data = [
    'login' => $current_user->user_login,
    'company'  => $current_user->billing_company,
    'morphy' => $current_user->billing_type,
    'civility_id' => $current_user->civility_id,    
    'lastname' => $current_user->user_lastname,
    'firstname' => $current_user->user_firstname,
    'address' => $thirdparty->address,    
    'zip' => $thirdparty->zip,
    'town' => $thirdparty->town,
    'country_id' => $thirdparty->country_id,
    'email' => $thirdparty->email,
    'phone' => $thirdparty->phone,
    'birth' => $birth,
    'typeid' => $type,
    'socid' => doliconnector($current_user, 'fk_soc'),
    'array_options' => $thirdparty->array_options,
		'statut'	=> $statut,
	];
  
if ($action=='POST') {
$mbr = callDoliApi("POST", "/members", $data, 0);
$adhesion = callDoliApi("GET", "/adherentsplus/".doliconnector($current_user, 'fk_member', true), null, dolidelay('member', true));
} else {
$adhesion = callDoliApi("PUT", "/adherentsplus/".doliconnector($current_user, 'fk_member', true), $data, 0);
}

return $adhesion;
}

function dolimembertypelist($typeadhesion, $adherent = null) {

  $list = '<ul class="list-group list-group-flush">';
  
  if ( !isset($typeadhesion->error) ) {
    foreach ($typeadhesion as $postadh) {
      if ( !doliversion('14.0.0') || (!isset($postadh->amount)) ) {
        $postadh->amount = $postadh->price;
      } 
      if ( ( $postadh->subscription == '1' || ( $postadh->subscription != '1' && $adherent->typeid == $postadh->id ) ) && $postadh->statut == '1' || ( $postadh->statut == '0' && isset($adherent->typeid) && $postadh->id == $adherent->typeid && $adherent->statut == '1' ) ) {
        $list .= '<li class="list-group-item list-group-item-light list-group-item-action"><div class="row"><div class="col-md-8"><b>';
        if ($postadh->morphy == 'mor') {
          $list .= "<i class='fas fa-user-tie fa-fw'></i> "; 
        } elseif ($postadh->morphy == 'phy') {
          $list .= "<i class='fas fa-user fa-fw'></i> "; 
        } else { $list .= "<i class='fas fa-user-friends fa-fw'></i> ";
        }
        $list .= doliproduct($postadh, 'label');
        if (! empty ($postadh->duration_value)) $list .= " - ".doliduration($postadh);
        $list .= " <small>";
        if ( !empty($postadh->subscription) ) {
          if ($postadh->date_renew < $postadh->date_welcomefee) { 
            $list .= "(";
            $list .= doliprice($postadh->amount);
          } elseif ($postadh->price_prorata != $postadh->amount) { 
            $list .= "(";
            $list .= doliprice($postadh->price_prorata)." ";
            $list .= __( 'then', 'doliconnect')." ".doliprice($postadh->amount);
          } else {
            $list .= "(".doliprice($postadh->price_prorata);
          } 
          $list .= ")"; 
        } else { 
          $list .= "<span class='badge badge-pill badge-primary'>".__( 'Free', 'doliconnect')."</span>";
        }
        $list .= "</small></b>";
        if (isset($postadh->note) && !empty($postadh->note)) $list .= "<br><small class='text-justify text-muted '>".doliproduct($postadh, 'note')."</small>";
        if (isset($postadh->description) && !empty($postadh->description)) $list .= "<br><small class='text-justify text-muted '>".doliproduct($postadh, 'description')."</small>";
        if (!empty(number_format($postadh->federal))) $list .= "<br><small class='text-justify text-muted '>".__( 'Including a federal part of', 'doliconnect')." ".doliprice($postadh->federal)."</small>";
        $list .= "<br><small class='text-justify text-muted '>".__( 'From', 'doliconnect')." ".wp_date('d/m/Y', $postadh->date_begin)." ".__( 'until', 'doliconnect')." ".wp_date('d/m/Y', $postadh->date_end)."</small>";
        $list .= "</div><div class='col-md-4'>";
        if (!isset($adherent->next_subscription_renew)) $adherent->next_subscription_renew = null;
        if ( isset($adherent) && isset($adherent->datefin) && $adherent->datefin != null && $adherent->statut == 1 && $adherent->datefin > $adherent->next_subscription_renew && $adherent->next_subscription_renew > current_time( 'timestamp',1) ) {
          $list .= "<div class='d-grid gap-2'><button class='btn btn-light' disabled>".sprintf(__('From %s', 'doliconnect'), wp_date('d/m/Y', $adherent->next_subscription_renew))."</button></div>";
        } elseif ( $postadh->family == '1' ) {
          $list .= "<div class='d-grid gap-2'><a href='".doliconnecturl('doliaccount')."?module=ticket&type=COM&create' class='btn btn-info' role='button'>".__( 'Contact us', 'doliconnect')."</a></div>";
        } elseif ( $postadh->statut == '0' && isset($adherent) && $postadh->id == $adherent->typeid ) { 
          $list .= "<div class='d-grid gap-2'><button class='btn btn-secondary' disabled>".__( 'Non-renewable', 'doliconnect')."</div></div>";
        } elseif ( $postadh->automatic_renew != '1' && isset($adherent) && $postadh->id == $adherent->typeid ) { //to do add security for avoid loop  in revali
          $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-warning btn-block' type='submit'>".__( 'Validate', 'doliconnect')."</button></div></form>";
        } elseif ( $postadh->automatic == '1' && isset($adherent) && $postadh->id == $adherent->typeid ) {
          if ( isset($adherent) && $adherent->statut == '1' ) {
            if ( $adherent->datefin == null ) { 
              $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Validate', 'doliconnect')."</button></div></form>";
            } else {
              if ( $adherent->datefin>current_time( 'timestamp',1) ) { 
                $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Validate', 'doliconnect')."</button></div></form>";
              } else {
                $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Validate', 'doliconnect')."</button></div></form>";
              }
            }
          } elseif ( isset($adherent) && isset($adherent->statut) && $adherent->statut == '0' ) {
            $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Validate', 'doliconnect')."</button></div></form>";
          } else { 
            $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Validate', 'doliconnect')."</button></div></form>";
          }
        } elseif ( $postadh->automatic == '1' && ( (isset($adherent) && $postadh->id != $adherent->typeid) || !isset($adherent)) ) {
          if ( isset($adherent) && isset($adherent->statut) && $adherent->statut == '1' ) {
            if ( $adherent->datefin == null ) { 
              $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-warning btn-block' type='submit'>".__( 'Update', 'doliconnect')."</button></div></form>";
            } else {
              if ( $adherent->datefin>current_time( 'timestamp',1) ) { 
                $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-warning btn-block' type='submit'>".__( 'Update', 'doliconnect')."</button></div></form>";
              } else {
                $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><INPUT type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-warning btn-block' type='submit'>".__( 'Update', 'doliconnect')."</button></div></form>";
              }
            }
          } elseif ( isset($adherent) && isset($adherent->statut) && $adherent->statut == '0' ) {
            $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-warning btn-block' type='submit'>".__( 'Update', 'doliconnect')."</button></div></form>";
          } else { 
            $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='5'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-warning btn-block' type='submit'>".__( 'Validate', 'doliconnect')."</button></div></form>";
          }
        } elseif ( $postadh->automatic != '1' && isset($adherent) && $postadh->id == $adherent->typeid ) {
          if ( isset($adherent) && isset($adherent->statut) && $adherent->statut == '1' ) {
            if ($adherent->datefin == null ) { 
              $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Validate', 'doliconnect')."</button></div></form>";
            } else {
              if ($adherent->datefin>current_time( 'timestamp',1)) { 
                $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Validate', 'doliconnect')."</button></div></form>";
              } else {
                $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Validate', 'doliconnect')."</button></div></form>";
              }
            }
          } elseif ( isset($adherent) && isset($adherent->statut) && $adherent->statut == '0' ) {
            $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect')."</button></div></form>";
          } elseif ( isset($adherent) && isset($adherent->statut) && $adherent->statut == '-1' ) {
            $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='5'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-info btn-block' type='submit' disabled>".__( 'Request submitted', 'doliconnect')."</button></div></form>";
          } else { 
            $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='5'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect')."</button></div></form>";
          }
        } elseif ( $postadh->automatic != '1' && ( (isset($adherent) && $postadh->id != $adherent->typeid) || !isset($adherent)) ) {
          if (isset($adherent) && isset($adherent->statut) && $adherent->statut == '1') {
            if ($adherent->datefin == null ) { 
              $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect')."</button></div></form>";
            } else {
              if ( $adherent->datefin>current_time( 'timestamp',1) ) { 
                $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect')."</button></div></form>";
              } else {
                $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect')."</button></div></form>";
              }
            }
          } elseif ( isset($adherent) && isset($adherent->statut) && $adherent->statut == '0' ) {
            $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect')."</button></div></form>";
          } else {
            $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='1'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect')."</button></div></form>";
          } 
        }
        $list .= "</div></div></li>"; 
      }
    }
  } else { 
    $list .= "<li class='list-group-item list-group-item-light'><center>".__( 'No available membership type', 'doliconnect')."</center></li>";
  }
    $list .= "</ul>"; 
return $list; 
}

?>