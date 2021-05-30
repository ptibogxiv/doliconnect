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
$birth = mktime(0, 0, 0, $month, $day, $year);

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

function doliconnect_membership_modal() {
if ( !empty(doliconst('MAIN_MODULE_ADHERENTSPLUS')) && (is_user_logged_in() && is_page(doliconnectid('doliaccount')) && !empty(doliconnectid('doliaccount')) ) ) {
global $current_user;
doliconnect_enqueues();

$delay = dolidelay('member', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null));
$request = "/adherentsplus/".doliconnector($current_user, 'fk_member', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)); 
if ( !empty(doliconnector($current_user, 'fk_member')) && doliconnector($current_user, 'fk_member') > 0 && doliconnector($current_user, 'fk_soc') > 0 ) {
$adherent = callDoliApi("GET", $request, null, $delay);
}

print "<div class='modal fade' id='activatemember' tabindex='-1' aria-labelledby='activatememberLabel' aria-hidden='true' data-bs-keyboard='false'>
<div class='modal-dialog modal-lg modal-fullscreen-md-down modal-dialog-centered modal-dialog-scrollable'><div class='modal-content'><div class='modal-header'>";
if ( !isset($adherent->datefin) || ( $adherent->datefin>current_time( 'timestamp',1)) || ( $adherent->datefin < current_time( 'timestamp',1)) ) {
$member_id = '';
if (isset($adherent) && $adherent->id > 0) $member_id = "member_id=".$adherent->id;
$morphy = '';
//if (!empty($current_user->billing_type)) $morphy = "&sqlfilters=(t.morphy%3A=%3A'')%20or%20(t.morphy%3Ais%3Anull)%20or%20(t.morphy%3A%3D%3A'".$current_user->billing_type."')";
$typeadhesion = callDoliApi("GET", "/adherentsplus/type?sortfield=t.rowid&sortorder=ASC&nature=all&".$member_id.$morphy, null, $delay);
//print $typeadhesion;
print '<h4 class="modal-title" id="myModalLabel">'.__( 'Prices', 'doliconnect').' '.$typeadhesion[0]->season.'</h4><button id="subscription-close" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>';

print '<div class="modal-body">';
/**
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
print "<table class='table table-striped' id ='subscription-table'>";

if ( !isset($typeadhesion->error) ) {
foreach ($typeadhesion as $postadh) {
if ( ( $postadh->subscription == '1' || ( $postadh->subscription != '1' && $adherent->typeid == $postadh->id ) ) && $postadh->statut == '1' || ( $postadh->statut == '0' && $postadh->id == $adherent->typeid && $adherent->statut == '1' ) ) {
print "<tr><td><div class='row'><div class='col-md-8'><b>";
if ($postadh->morphy == 'mor') {
print "<i class='fas fa-user-tie fa-fw'></i> "; 
} elseif ($postadh->morphy == 'phy') {
print "<i class='fas fa-user fa-fw'></i> "; 
} else {print "<i class='fas fa-user-friends fa-fw'></i> ";}
print doliproduct($postadh, 'label');
if (! empty ($postadh->duration_value)) print " - ".doliduration($postadh);
print " <small>";
if ( !empty($postadh->subscription) ) {
if ($postadh->price_prorata != $postadh->price) { 
print "(";
print doliprice($postadh->price_prorata)." ";
print __( 'then', 'doliconnect')." ".doliprice($postadh->price);
} else {
print "(".doliprice($postadh->price_prorata);
} 
print ")"; } else { print "<span class='badge badge-pill badge-primary'>".__( 'Free', 'doliconnect')."</span>"; }
print "</small></b>";
if (!empty(doliproduct($postadh, 'note'))) print "<br><small class='text-justify text-muted '>".doliproduct($postadh, 'note')."</small>";
if (!empty(number_format($postadh->federal))) print "<br><small class='text-justify text-muted '>".__( 'Including a federal part of', 'doliconnect')." ".doliprice($postadh->federal)."</small>";
print "<br><small class='text-justify text-muted '>".__( 'From', 'doliconnect')." ".wp_date('d/m/Y', $postadh->date_begin)." ".__( 'until', 'doliconnect')." ".wp_date('d/m/Y', $postadh->date_end)."</small>";
print "</div><div class='col-md-4'>";
if ( isset($adherent) && $adherent->datefin != null && $adherent->statut == 1 && $adherent->datefin > $adherent->next_subscription_renew && $adherent->next_subscription_renew > current_time( 'timestamp',1) ) {
print "<button class='btn btn-info btn-block' disabled>".sprintf(__('From %s', 'doliconnect'), wp_date('d/m/Y', $adherent->next_subscription_renew))."</a>";
} elseif ( $postadh->family == '1' ) {
print "<div class='d-grid gap-2'><a href='".doliconnecturl('doliaccount')."?module=ticket&type=COM&create' class='btn btn-info' role='button'>".__( 'Contact us', 'doliconnect')."</a></div>";
} 
elseif ( ( $postadh->statut == '0' && $postadh->id == $adherent->typeid ) ) { 
print "<button class='btn btn-secondary btn-block' disabled>".__( 'Non-renewable', 'doliconnect')."</a>";
} 
elseif ( ( isset($adherent) && $postadh->automatic_renew != '1' && $postadh->id == $adherent->typeid ) ) { //to do add security for avoid loop  in revali
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-warning btn-block' type='submit'>".__( 'Validate', 'doliconnect')."</button></div></form>";
} 
elseif ( ($postadh->automatic == '1' ) && ($postadh->id == $adherent->typeid) ) {
if ( $adherent->statut == '1' ) {
if ( $adherent->datefin == null ) {print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Pay', 'doliconnect')."</button></div></form>";}

else {
if ( $adherent->datefin>current_time( 'timestamp',1) ) {print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Validate', 'doliconnect')."</button></div></form>";}else {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Validate', 'doliconnect')."</button></div></form>";}
}
} elseif ( $adherent->statut == '0' ) {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Validate', 'doliconnect')."</button></div></form>";
} else {print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Validate', 'doliconnect')."</button></div></form>";
}

} elseif (($postadh->automatic == '1') && ( (isset($adherent) && $postadh->id != $adherent->typeid) || !isset($adherent)) ) {

if ( $adherent->statut == '1' ) {

if ( $adherent->datefin == null ) {print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-warning btn-block' type='submit'>".__( 'Update', 'doliconnect')."</button></div></form>";
} else {
if ( $adherent->datefin>current_time( 'timestamp',1) ) { print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'<div class='d-grid gap-2'>><button class='btn btn-warning btn-block' type='submit'>".__( 'Update', 'doliconnect')."</button></div></form>";
} else {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><INPUT type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-warning btn-block' type='submit'>".__( 'Update', 'doliconnect')."</button></div></form>";}
}

} elseif ( $adherent->statut == '0' ) {

print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-warning btn-block' type='submit'>".__( 'Update', 'doliconnect')."</button></div></form>";

} else {print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='5'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-warning btn-block' type='submit'>".__( 'Update', 'doliconnect')."</button></div></form>";
}

} elseif ( ($postadh->automatic != '1' ) && ( isset($adherent) && $postadh->id == $adherent->typeid ) ) {

if ( $adherent->statut == '1' ) {

if ($adherent->datefin == null ) {print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Pay', 'doliconnect')."</button></div></form>";
} else {
if ($adherent->datefin>current_time( 'timestamp',1)) { print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Validate', 'doliconnect')."</button></div></form>";}else {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='4'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-success btn-block' type='submit'>".__( 'Validate', 'doliconnect')."</button></div></form>";}
}

} elseif ( $adherent->statut == '0' ) {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect')."</button></div></form>";
}
elseif ( $adherent->statut == '-1' ) {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='5'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-info btn-block' type='submit' disabled>".__( 'Request submitted', 'doliconnect')."</button></div></form>";
} else {print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='5'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect')."</button></div></form>";
}
}
elseif ( ($postadh->automatic != '1' ) && ( (isset($adherent) && $postadh->id != $adherent->typeid) || !isset($adherent)) ) {
if (isset($adherent) && $adherent->statut == '1') {
if ($adherent->datefin == null ){print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect')."</button></div></form>";}

else {
if ( $adherent->datefin>current_time( 'timestamp',1) ) {print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect')."</button></div></form>";}else {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect')."</button></div></form>";}
}
}
elseif (isset($adherent) && $adherent->statut == '0' ) {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='3'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect')."</button></div></form>";
}
else {
print "<form id='subscription-form' action='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' method='post'><input type='hidden' name='cotisation' value='$postadh->price_prorata'><input type='hidden' name='timestamp_start' value='".$postadh->date_begin."'><input type='hidden' name='timestamp_end' value='".$postadh->date_end."'><input type='hidden' name='update_membership' value='1'><input type='hidden' name='typeadherent' value='$postadh->id'><div class='d-grid gap-2'><button class='btn btn-danger' type='submit'>".__( 'Ask us', 'doliconnect')."</button></div></form>";
} 
}
}
print "</div></div></td></tr>"; 
}
} else { 
print "<li class='list-group-item list-group-item-light'><center>".__( 'No available membership type', 'doliconnect')."</center></li>";
}

}
print "</table>";

print doliloading('subscription'); 

print "</div><div id='subscription-footer' class='modal-footer'><small class='text-justify'>".__( 'Note: the admins reserve the right to change your membership in relation to your personal situation. A validation of the membership may be necessary depending on the cases.', 'doliconnect')."</small></div></div></div></div>";

print '<div class="modal fade" id="PaySubscription" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="staticBackdropLabel">Modal title</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Understood</button>
      </div>
    </div>
  </div>
</div>';

}}
add_action( 'wp_footer', 'doliconnect_membership_modal');
?>