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

function dolimembertypelist($typeadhesion, $adherent) {
 
  /*
  print '<ul class="list-group list-group-flush">
    <li class="list-group-item d-flex justify-content-between align-items-center">
      <b>Cras justo odio</b><br><small class="text-justify text-muted">test test</small>
      <div class="d-grid gap-2 col-4"><button class="btn btn-primary" type="button">Button</button></div>
    </li>
    <li class="list-group-item list-group-item-light list-group-item-action">
      <div class="d-flex w-100 justify-content-between">
        <b>Hebdomadaire - 1 semaine</b>
        <div class="d-grid gap-2 col-4"><button class="btn btn-primary btn-sm" type="button">Button</button></div>
      </div>
      <p class="mb-1">8,29 E puis 15,00 E</p>
      <small>A partir du 21/11/2020 jusquau 22/11/2020</small>
    </li>
  </ul>';
  */
  $list = "<table class='table table-striped' id ='subscription-table'>";
  
  if ( !isset($typeadhesion->error) ) {
  foreach ($typeadhesion as $postadh) {
  if ( !doliversion('14.0.0') || (!isset($postadh->amount)) ) {
  $postadh->amount = $postadh->price;
  } 
  if ( ( $postadh->subscription == '1' || ( $postadh->subscription != '1' && $adherent->typeid == $postadh->id ) ) && $postadh->statut == '1' || ( $postadh->statut == '0' && isset($adherent->typeid) && $postadh->id == $adherent->typeid && $adherent->statut == '1' ) ) {
  $list .= "<tr><td><div class='row'><div class='col-md-8'><b>";
  if ($postadh->morphy == 'mor') {
    $list .= "<i class='fas fa-user-tie fa-fw'></i> "; 
  } elseif ($postadh->morphy == 'phy') {
    $list .= "<i class='fas fa-user fa-fw'></i> "; 
  } else { $list .= "<i class='fas fa-user-friends fa-fw'></i> ";}
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
  $list .= ")"; } else { $list .= "<span class='badge badge-pill badge-primary'>".__( 'Free', 'doliconnect')."</span>"; }
  $list .= "</small></b>";
  if (isset($postadh->note) && !empty($postadh->note)) $list .= "<br><small class='text-justify text-muted '>".doliproduct($postadh, 'note')."</small>";
  if (isset($postadh->description) && !empty($postadh->description)) $list .= "<br><small class='text-justify text-muted '>".doliproduct($postadh, 'description')."</small>";
  if (!empty(number_format($postadh->federal))) $list .= "<br><small class='text-justify text-muted '>".__( 'Including a federal part of', 'doliconnect')." ".doliprice($postadh->federal)."</small>";
  $list .= "<br><small class='text-justify text-muted '>".__( 'From', 'doliconnect')." ".wp_date('d/m/Y', $postadh->date_begin)." ".__( 'until', 'doliconnect')." ".wp_date('d/m/Y', $postadh->date_end)."</small>";
  $list .= "</div><div class='col-md-4'>";
  if ( isset($adherent) && isset($adherent->datefin) && $adherent->datefin != null && $adherent->statut == 1 && $adherent->datefin > $adherent->next_subscription_renew && $adherent->next_subscription_renew > current_time( 'timestamp',1) ) {
    $list .= "<div class='d-grid gap-2'><button class='btn btn-light' disabled>".sprintf(__('From %s', 'doliconnect'), wp_date('d/m/Y', $adherent->next_subscription_renew))."</button></div>";
  } elseif ( $postadh->family == '1' ) {
    $list .= "<div class='d-grid gap-2'><a href='".doliconnecturl('doliaccount')."?module=ticket&type=COM&create' class='btn btn-info' role='button'>".__( 'Contact us', 'doliconnect')."</a></div>";
  } 
  elseif ( $postadh->statut == '0' && isset($adherent) && $postadh->id == $adherent->typeid ) { 
    $list .= "<div class='d-grid gap-2'><button class='btn btn-secondary' disabled>".__( 'Non-renewable', 'doliconnect')."</div></div>";
  } 
  elseif ( $postadh->automatic_renew != '1' && isset($adherent) && $postadh->id == $adherent->typeid ) { //to do add security for avoid loop  in revali
    $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-warning btn-block' type='submit'>".__( 'Validate', 'doliconnect')."</button></div></form>";
  } 
  elseif ( $postadh->automatic == '1' && isset($adherent) && $postadh->id == $adherent->typeid ) {
  if ( isset($adherent) && $adherent->statut == '1' ) {
  if ( $adherent->datefin == null ) { $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Pay', 'doliconnect')."</button></div></form>";}
  
  else {
  if ( $adherent->datefin>current_time( 'timestamp',1) ) { $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Validate', 'doliconnect')."</button></div></form>";}else {
    $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Validate', 'doliconnect')."</button></div></form>";}
  }
  } elseif ( isset($adherent) && isset($adherent->statut) && $adherent->statut == '0' ) {
    $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Validate', 'doliconnect')."</button></div></form>";
  } else { $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Validate', 'doliconnect')."</button></div></form>";
  }
  
  } elseif ( $postadh->automatic == '1' && ( (isset($adherent) && $postadh->id != $adherent->typeid) || !isset($adherent)) ) {
  
  if ( isset($adherent) && isset($adherent->statut) && $adherent->statut == '1' ) {
  
  if ( $adherent->datefin == null ) { $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-warning btn-block' type='submit'>".__( 'Update', 'doliconnect')."</button></div></form>";
  } else {
  if ( $adherent->datefin>current_time( 'timestamp',1) ) { $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-warning btn-block' type='submit'>".__( 'Update', 'doliconnect')."</button></div></form>";
  } else {
    $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><INPUT type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-warning btn-block' type='submit'>".__( 'Update', 'doliconnect')."</button></div></form>";}
  }
  
  } elseif ( isset($adherent) && isset($adherent->statut) && $adherent->statut == '0' ) {
  
    $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-warning btn-block' type='submit'>".__( 'Update', 'doliconnect')."</button></div></form>";
  
  } else { $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='5'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-warning btn-block' type='submit'>".__( 'Validate', 'doliconnect')."</button></div></form>";
  }
  
  } elseif ( $postadh->automatic != '1' && isset($adherent) && $postadh->id == $adherent->typeid ) {
  
  if ( isset($adherent) && isset($adherent->statut) && $adherent->statut == '1' ) {
  
  if ($adherent->datefin == null ) { $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Pay', 'doliconnect')."</button></div></form>";
  } else {
  if ($adherent->datefin>current_time( 'timestamp',1)) { $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Validate', 'doliconnect')."</button></div></form>";}else {
    $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Validate', 'doliconnect')."</button></div></form>";}
  }
  
  } elseif ( isset($adherent) && isset($adherent->statut) && $adherent->statut == '0' ) {
    $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect')."</button></div></form>";
  }
  elseif ( isset($adherent) && isset($adherent->statut) && $adherent->statut == '-1' ) {
    $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='5'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-info btn-block' type='submit' disabled>".__( 'Request submitted', 'doliconnect')."</button></div></form>";
  } else { $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='5'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect')."</button></div></form>";
  }
  }
  elseif ( $postadh->automatic != '1' && ( (isset($adherent) && $postadh->id != $adherent->typeid) || !isset($adherent)) ) {
  if (isset($adherent) && isset($adherent->statut) && $adherent->statut == '1') {
  if ($adherent->datefin == null ){ $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect')."</button></div></form>";}
  
  else {
  if ( $adherent->datefin>current_time( 'timestamp',1) ) { $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect')."</button></div></form>";}else {
    $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect')."</button></div></form>";}
  }
  }
  elseif ( isset($adherent) && isset($adherent->statut) && $adherent->statut == '0' ) {
    $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect')."</button></div></form>";
  }
  else {
    $list .= "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='1'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect')."</button></div></form>";
  } 
  }
  }
    $list .= "</div></div></td></tr>"; 
  }
  } else { 
    $list .= "<li class='list-group-item list-group-item-light'><center>".__( 'No available membership type', 'doliconnect')."</center></li>";
  }
    $list .= "</table>"; 
return $list; 
}

function doliconnect_membership_modal() {
if ( !empty(doliconst('MAIN_MODULE_ADHERENTSPLUS')) && (is_user_logged_in() && is_page(doliconnectid('doliaccount')) && !empty(doliconnectid('doliaccount')) ) ) {
global $current_user;
doliconnect_enqueues();

$delay = dolidelay('member', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
$request = "/adherentsplus/".doliconnector($current_user, 'fk_member', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)); 
if ( !empty(doliconnector($current_user, 'fk_member')) && doliconnector($current_user, 'fk_member') > 0 && doliconnector($current_user, 'fk_soc') > 0 ) {
  $adherent = callDoliApi("GET", $request, null, $delay);
} else {
  $adherent = null;
}

print "<div class='modal fade' id='activatemember' tabindex='-1' aria-labelledby='activatememberLabel' aria-hidden='true' data-bs-keyboard='false'>
<div class='modal-dialog modal-lg modal-fullscreen-md-down modal-dialog-centered modal-dialog-scrollable'><div class='modal-content'><div class='modal-header'>";
if ( !isset($adherent->datefin) || ( $adherent->datefin>current_time( 'timestamp',1)) || ( $adherent->datefin < current_time( 'timestamp',1)) ) {
$member_id = '';
if (isset($adherent) && $adherent->id > 0) $member_id = "member_id=".$adherent->id;
$morphy = '';
if (!empty($current_user->billing_type)) $morphy = "&sqlfilters=(t.morphy%3A=%3A'')%20or%20(t.morphy%3Ais%3Anull)%20or%20(t.morphy%3A%3D%3A'".$current_user->billing_type."')";
$typeadhesion = callDoliApi("GET", "/adherentsplus/type?sortfield=t.libelle&sortorder=ASC&nature=all&".$member_id.$morphy, null, $delay);
//print $typeadhesion;
print '<h4 class="modal-title" id="myModalLabel">'.__( 'Prices', 'doliconnect').' '.$typeadhesion[0]->season.'</h4><button id="subscription-close" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>';

print '<div class="modal-body">';

print dolimembertypelist($typeadhesion, $adherent);

print "</div><div id='subscription-footer' class='modal-footer'><small class='text-justify'>".__( 'Note: the admins reserve the right to change your membership in relation to your personal situation. A validation of the membership may be necessary depending on the cases.', 'doliconnect')."</small></div></div></div></div>";

if (isset($adherent) && !empty($adherent->typeid)) {
$request= "/adherentsplus/type/".$adherent->typeid;
$adherenttype = callDoliApi("GET", $request, null, dolidelay('member', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//print var_dump($adherenttype);

if ( !doliversion('14.0.0') || !isset($adherenttype->amount)) {
$adherenttype->amount = $adherenttype->price;
} 

print '<div class="modal fade" id="PaySubscriptionModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"><div class="modal-content"><div class="modal-header">
<h5 class="modal-title" id="staticBackdropLabel">'.__( 'Subscription', 'doliconnect').'</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div><div class="modal-body">
<h6>'.__( 'This subscription', 'doliconnect').'</h6>
'.__( 'Price:', 'doliconnect').' '.doliprice($adherenttype->price_prorata).'<br>
'.__( 'From', 'doliconnect').' '.wp_date('d/m/Y', $adherenttype->date_begin).' '.__( 'until', 'doliconnect').' '.wp_date('d/m/Y', $adherenttype->date_end).'
<hr>
<h6>'.__( 'Next subscription', 'doliconnect').'</h6>
'.__( 'Price:', 'doliconnect').' '.doliprice($adherenttype->amount).'<br>
'.__( 'From', 'doliconnect').' '.wp_date('d/m/Y', $adherenttype->date_nextbegin).' '.__( 'until', 'doliconnect').' '.wp_date('d/m/Y', $adherenttype->date_nextend).'
</div><div class="modal-footer"><form id="subscribe-form" action="'.admin_url('admin-ajax.php').'" method="post">';
print "<input type='hidden' name='action' value='dolimember_request'>";
print "<input type='hidden' name='dolimember-nonce' value='".wp_create_nonce( 'dolimember-nonce')."'>";
print "<script>";
print 'jQuery(document).ready(function($) {
	
	jQuery("#subscribe-form").on("submit", function(e) { 
  jQuery("#PaySubscriptionModal").modal("hide");
  jQuery("#DoliconnectLoadingModal").modal("show");
	e.preventDefault();
    
	var $form = $(this);
  var url = "'.esc_url(doliconnecturl('dolicart')).'";  
jQuery("#DoliconnectLoadingModal").on("shown.bs.modal", function (e) {
      document.getElementById("message-dolicart").innerHTML = "";  
		$.post($form.attr("action"), $form.serialize(), function(response) {
      if (response.success) { 
        //console.log(response.data.message);
        if (document.getElementById("DoliHeaderCartItems")) {
          document.getElementById("DoliHeaderCartItems").innerHTML = response.data.items;
        }
        if (document.getElementById("DoliFooterCartItems")) {  
          document.getElementById("DoliFooterCartItems").innerHTML = response.data.items;
        }
        if (document.getElementById("DoliCartItemsList")) {  
          document.getElementById("DoliCartItemsList").innerHTML = response.data.list;
        }
        if (document.getElementById("DoliWidgetCartItems")) {
          document.getElementById("DoliWidgetCartItems").innerHTML = response.data.items;      
        }
        if (document.getElementById("message-dolicart")) {
          document.getElementById("message-dolicart").innerHTML = response.data.message;      
        }
        $("#offcanvasDolicart").offcanvas("show");  
      } else {
        //console.log("error updating qty " + response.data.message);
      }
      $("#DoliconnectLoadingModal").modal("hide");
		}, "json");  
  });
});
});';
print "</script>";

print '<input type="hidden" name="update_membership" value="renew"><button class="btn btn-danger" type="submit">'.__( 'Add to basket', 'doliconnect').'</button></form>
</div></div></div></div>';
}}

}}
add_action( 'wp_footer', 'doliconnect_membership_modal');

?>