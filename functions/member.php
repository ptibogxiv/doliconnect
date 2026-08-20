<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function doliconnect_membership($current_user, $statut, $type, $delay) {
$data = array();
  if ($statut == 1) {
    $data['statut'] = '-1';
    $action='POST';
  } elseif ($statut == 2) {
    $data['statut'] = '0';
    $action='PUT';
  } elseif ($statut == 3) {
    $data['statut'] = '-1';
    $action='PUT';
  } elseif ($statut == 4) {
    $data['statut'] = '1';
    $action='PUT';
  } elseif ($statut == 5) {
    $data['statut'] = '1';
    $action='POST';
  } 
  $thirdparty = doliConnect('thirdparty', $current_user);
  if (isset($current_user->billing_birth) && !empty($current_user->billing_birth)) {
    list($year, $month, $day) = explode("-", $current_user->billing_birth);
    $birth = mktime(0, 0, 0, $month, $day, $year);
  } else {
    $birth = null;
  }

  if ($action=='POST') {  
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
    'socid' => $thirdparty->id,
    'typeid' => $type,
    'array_options' => $thirdparty->array_options,
	];
    $newmember = callDoliApi("POST", "/members", $data, 0);
    //print var_dump($newmember);
    $member = callDoliApi("GET", "/members/".$newmember, null, dolidelay('member', true));
  } else {
    $data['typeid'] = $type;
    $member = doliConnect('member', $current_user, false, true);
    $member = callDoliApi("PUT", "/members/".$member->id, $data, 0); 
  }
  return $member;
}

function doliMembershipType($type, $member = null) {
  $type->date_begin = null;
  $type->date_end = null;
  $type->amount = $type->amount;
return $type;
}

function dolimembertypelist($typeadhesion, $adherent = null) {
  if ( doliversion('23.0.0') && isset($typeadhesion->data) ) { $typeadhesion = $typeadhesion->data; } else { $typeadhesion = $typeadhesion; }
  $list = '<ul class="list-group list-group-flush">';
  if ( !isset($typeadhesion->error) ) {
    foreach ($typeadhesion as $postadh) {
      if ( !doliversion('14.0.0') || (!isset($postadh->amount)) ) {
        $postadh->amount = $postadh->price;
      } 
      $postadh = doliMembershipType($postadh, $adherent);
      if ( ( $postadh->subscription == '1' || ( $postadh->subscription != '1' && $adherent->typeid == $postadh->id ) ) && $postadh->status == '1' || ( $postadh->status == '0' && isset($adherent->typeid) && $postadh->id == $adherent->typeid && $adherent->status == '1' ) ) {
        $list .= '<li class="list-group-item list-group-item-action"><div class="d-flex w-100 justify-content-between">';
        $list .= '<h5 class="mb-1">';
        $list .= doliproduct($postadh, 'label');
        if (! empty ($postadh->duration_value)) $list .= " - ".doliduration($postadh);
        $list .= '</h5>';
        $list .= '<small class="text-body-secondary">button here</small></div>';
        $list .= '<p class="mb-1">'.sprintf( __( 'From %s to %s', 'doliconnect' ), wp_date('d/m/Y', $postadh->date_begin), wp_date('d/m/Y', $postadh->date_end) ).'</p>';
        $list .= '<small class="text-body-secondary">';
        if (isset($postadh->description) && !empty($postadh->description)) $list .= doliproduct($postadh, 'description');
        $list .= '</small></li>';
      }
    }
  } else { 
    $list .= "<li class='list-group-item list-group-item-light'><center>".__( 'No available membership type', 'doliconnect')."</center></li>";
  }
  $list .= "</ul>"; 
  return $list; 
}

?>