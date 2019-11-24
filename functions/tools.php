<?php

function dolimenu($name, $traduction, $right, $content) {


}

function doliversion($version) {
$ret = false;
$dolibarr = callDoliApi("GET", "/status", null, dolidelay('dolibarr'));
$versiondoli = explode("-", $dolibarr->success->dolibarr_version);
if ( is_object($dolibarr) && version_compare($versiondoli[0], $version) >= 0 ) {
$ret = $versiondoli[0];
}
return $ret;
}
add_action( 'admin_init', 'doliversion', 5, 1); 

function socialconnect( $url ) {
$connect = null;

include( plugin_dir_path( __DIR__ ) . 'includes/hybridauth/src/autoload.php');
include( plugin_dir_path( __DIR__ ) . 'includes/hybridauth/src/config.php');

$hybridauth = new Hybridauth\Hybridauth($config);
$adapters = $hybridauth->getConnectedAdapters();

foreach ($hybridauth->getProviders() as $name) {

if (!isset($adapters[$name])) {
$connect .= "<a href='".doliconnecturl('doliaccount')."?provider=".$name."' onclick='loadingLoginModal()' role='button' class='btn btn-block btn-outline-dark' title='".__( 'Sign in with', 'doliconnect' )." ".$name."'><b><i class='fab fa-".strtolower($name)." fa-lg float-left'></i> ".__( 'Sign in with', 'doliconnect' )." ".$name."</b></a>";
}
}
if (!empty($hybridauth->getProviders())) {
$connect .= '<div><div style="display:inline-block;width:46%;float:left"><hr width="90%" /></div><div style="display:inline-block;width: 8%;text-align: center;vertical-align:90%"><small class="text-muted">'.__( 'or', 'doliconnect' ).'</small></div><div style="display:inline-block;width:46%;float:right" ><hr width="90%"/></div></div>';
}

return $connect;
}

function doliuserform($object, $delay, $mode) {
global $current_user;

if ( is_object($object) && $object->id > 0 ) {
$idobject=$mode."[".$object->id."]";
} else { $idobject=$mode; }

print "<ul class='list-group list-group-flush'>";

if ( ! isset($object) && in_array($mode, array('thirdparty')) && empty(get_option('doliconnect_disablepro')) ) {
print "<li class='list-group-item'><div class='form-row'><div class='col-12'>";
if ( isset($_GET["morphy"]) && $_GET["morphy"] == 'mor' && get_option('doliconnect_disablepro') != 'mor' ) {
print "<a href='".wp_registration_url(get_permalink())."&morphy=phy' role='button' title='".__( 'Create a personnal account', 'doliconnect' )."'><small>(".__( 'Create a personnal account', 'doliconnect' )."?)</small></a>";                                                                                                                                                                                                                                                                                                                                     
print "<input type='hidden' id='morphy' name='".$idobject."[morphy]' value='mor'>";
}
elseif (get_option('doliconnect_disablepro') != 'phy') {
print "<a href='".wp_registration_url(get_permalink())."&morphy=mor' role='button' title='".__( 'Create a enterprise / supplier account', 'doliconnect' )."'><small>(".__( 'Create a enterprise / supplier account', 'doliconnect' )."?)</small></a>";
print "<input type='hidden' id='morphy' name='".$idobject."[morphy]' value='phy'>";
}
print "</div></div></li><li class='list-group-item'>";
} elseif ( isset($object) && in_array($mode, array('thirdparty')) && empty(get_option('doliconnect_disablepro')) ) { //|| $mode == 'member'
print "<li class='list-group-item'><div class='form-row'><div class='col-12'><label for='inputMorphy'><small><i class='fas fa-user-tag fa-fw'></i> ".__( 'Type of account', 'doliconnect' )."</small></label><br>";
print "<div class='custom-control custom-radio custom-control-inline'><input type='radio' id='morphy1' name='".$idobject."[morphy]' value='phy' class='custom-control-input'";
if ( $current_user->billing_type != 'mor' || empty($current_user->billing_type) ) { print " checked"; }
print " required><label class='custom-control-label' for='morphy1'>".__( 'Personnal account', 'doliconnect' )."</label>
</div>
<div class='custom-control custom-radio custom-control-inline'><input type='radio' id='morphy2' name='".$idobject."[morphy]' value='mor' class='custom-control-input'";
if ( $current_user->billing_type == 'mor' ) { print " checked"; }
print " required><label class='custom-control-label' for='morphy2'>".__( 'Entreprise account', 'doliconnect' )."</label>
</div>";
print "</div></div></li><li class='list-group-item'>";
} elseif ( in_array($mode, array('thirdparty')) ) { //|| $mode == 'member'
print "<li class='list-group-item'><input type='hidden' id='morphy' name='".$idobject."[morphy]' value='phy'>";
} else {
print "<li class='list-group-item'>";
}

if ( in_array($mode, array('member')) ) {
print "<div class='form-row'><div class='col-12'><label for='coordonnees'><small><i class='fas fa-user-tag fa-fw'></i> ".__( 'Type', 'doliconnect' )."</small></label><select class='custom-select' id='typeid'  name='".$idobject."[typeid]' required>";
$typeadhesion = callDoliApi("GET", "/adherentsplus/type?sortfield=t.libelle&sortorder=ASC&sqlfilters=(t.morphy%3A=%3A'')%20or%20(t.morphy%3Ais%3Anull)%20or%20(t.morphy%3A%3D%3A'".$object->morphy."')", null, $delay);
//print $typeadhesion;
print "<option value='' disabled ";
if ( empty($object->typeid) ) {
print "selected ";}
print ">".__( '- Select -', 'doliconnect' )."</option>";
if ( !isset($typeadhesion->error) ) {
foreach ($typeadhesion as $postadh) {
print "<option value ='".$postadh->id."' ";
if ( isset($object->typeid) && $object->typeid == $postadh->id && $object->typeid != null ) {
print "selected ";
} elseif ( $postadh->family == '1' || $postadh->automatic_renew != '1' || $postadh->automatic != '1' ) { print "disabled "; }
print ">".$postadh->label;
if (! empty ($postadh->duration_value)) print " - ".doliduration($postadh);
print " ";
//if ( ! empty($postadh->note) ) { print ", ".$postadh->note; }
$tx=1;
if ( ( ($postadh->welcome > '0') && ($object->datefin == null )) || (($postadh->welcome > '0') && (current_time( 'timestamp',1) > $object->next_subscription_valid) && (current_time( 'timestamp',1) > $object->datefin) && $object->next_subscription_valid != $object->datefin ) ) { 
print " (";
print doliprice(($tx*$postadh->price)+$postadh->welcome)." ";
print __( 'then', 'doliconnect-pro' )." ".doliprice($postadh->price)." ".__( 'yearly', 'doliconnect-pro' ).")"; 
} else {
print " (".doliprice($postadh->price);
print " ".__( 'yearly', 'doliconnect-pro' ).")";
} 

print "</option>";
}}
print "</select></div></div></li><li class='list-group-item'>";
}

if ( in_array($mode, array('thirdparty', 'donation')) && ($current_user->billing_type == 'mor' || ( isset($_GET["morphy"]) && $_GET["morphy"] == 'mor') || get_option('doliconnect_disablepro') == 'mor' ) ) {
print "<div class='form-row'><div class='col-12'><label for='coordonnees'><small><i class='fas fa-building fa-fw'></i> ".__( 'Name of company', 'doliconnect' )."</small></label><input type='text' class='form-control' id='inputcompany' placeholder='".__( 'Name of company', 'doliconnect' )."' name='".$idobject."[name]' value='".$object->name."' required></div></div>";  //$current_user->billing_company
print "<div class='form-row'><div class='col-12'><label for='coordonnees'><small><i class='fas fa-building fa-fw'></i> ".__( 'Professional ID', 'doliconnect' )."</small></label><input type='text' class='form-control' id='inputcompany' placeholder='".__( 'Professional ID', 'doliconnect' )."' name='".$idobject."[idprof1]' value='".$object->idprof1."' required></div></div>";  //$current_user->billing_company
print "<div class='form-row'><div class='col-12'><label for='coordonnees'><small><i class='fas fa-landmark fa-fw'></i> ".__( 'VAT number', 'doliconnect' )."</small></label><input type='text' class='form-control' id='inputcompany' placeholder='".__( 'VAT number', 'doliconnect' )."' name='".$idobject."[tva_intra]' value='".$object->tva_intra."'></div></div>";
print "</li><li class='list-group-item'>";
}

print "<div class='form-row'><div class='col-12 col-md-3'><label for='inputCivility'><small><i class='fas fa-user fa-fw'></i> ".__( 'Civility', 'doliconnect' )."</small></label>";
$civility = callDoliApi("GET", "/setup/dictionary/civility?sortfield=code&sortorder=ASC&limit=100", null, $delay);
if ( isset($civility->error) ) {
$civility = callDoliApi("GET", "/setup/dictionary/civilities?sortfield=code&sortorder=ASC&limit=100&active=1", null, $delay); 
}

print "<select class='custom-select' id='identity'  name='".$idobject."[civility_id]' required>";
print "<option value='' disabled ";
if ( empty($object->civility_id) ) {
print "selected ";}
print ">".__( '- Select -', 'doliconnect' )."</option>";
if ( !isset($civility->error ) && $civility != null ) { 
foreach ( $civility as $postv ) {

print "<option value='".$postv->code."' ";
if ( (isset($object->civility_id) ? $object->civility_id : $current_user->civility_id) == $postv->code && (isset($object->civility_id) ? $object->civility_id : $current_user->civility_id) != null) {
print "selected ";}
print ">".$postv->label."</option>";

}} else {
print "<option value='MME' ";
if ( $current_user->civility_id == 'MME' && $object->civility_id != null) {
print "selected ";}
print ">".__( 'Miss', 'doliconnect' )."</option>";
print  "<option value='MR' ";
if ( $current_user->civility_id == 'MR' && $object->civility_id != null) {
print "selected ";}
print ">".__( 'Mister', 'doliconnect' )."</option>";
}
print "</select>";
print "</div>
    <div class='col-12 col-md-4'>
      <label for='inputFirstname'><small><i class='fas fa-user fa-fw'></i> ".__( 'Firstname', 'doliconnect' )."</small></label>
      <input type='text' name='".$idobject."[firstname]' class='form-control' placeholder='".__( 'Firstname', 'doliconnect' )."' value='".(isset($object->firstname) ? $object->firstname : stripslashes(htmlspecialchars($current_user->user_firstname, ENT_QUOTES)))."' required>
    </div>
    <div class='col-12 col-md-5'>
      <label for='inputLastname'><small><i class='fas fa-user fa-fw'></i> ".__( 'Lastname', 'doliconnect' )."</small></label>
      <input type='text' name='".$idobject."[lastname]' class='form-control' placeholder='".__( 'Lastname', 'doliconnect' )."' value='".(isset($object->lastname) ? $object->lastname : stripslashes(htmlspecialchars($current_user->user_lastname, ENT_QUOTES)))."' required>
    </div></div>";

if ( !in_array($mode, array('donation')) ) {
if ( !empty($object->birth) ) { $birth = date_i18n('Y-m-d', $object->birth); }
print "<div class='form-row'><div class='col'><label for='inputbirth'><small><i class='fas fa-birthday-cake fa-fw'></i> ".__( 'Birthday', 'doliconnect' )."</small></label><input type='date' name='".$idobject."[birth]' class='form-control' value='".(isset($birth) ? $birth : $current_user->billing_birth)."' id='inputbirth' placeholder='yyyy-mm-dd' autocomplete='off'";
if ( $mode != 'contact' ) { print " required"; } 
print "></div>";
print "<div class='col-12 col-md-7'>";
if ( $mode != 'contact' ) {
print "<label for='inputnickname'><small><i class='fas fa-user-secret fa-fw'></i> ".__( 'Display name', 'doliconnect' )."</small></label><input type='text' class='form-control' id='inputnickname' placeholder='".__( 'Nickname', 'doliconnect' )."' name='user_nicename' value='".stripslashes(htmlspecialchars($current_user->nickname, ENT_QUOTES))."' autocomplete='off' required >";
} else {
print "<label for='inputnickname'><small><i class='fas fa-user-secret fa-fw'></i> ".__( 'Title / Job', 'doliconnect' )."</small></label><input type='text' class='form-control' id='inputtitle/job' placeholder='".__( 'Title / Job', 'doliconnect' )."' name='".$idobject."[poste]' value='".stripslashes(htmlspecialchars(isset($object->poste) ? $object->poste : null, ENT_QUOTES))."' autocomplete='off'>";
}
print "</div></div>";
}

print "<div class='form-row'><div class='col'><label for='inputemail'><small><i class='fas fa-at fa-fw'></i> ".__( 'Email', 'doliconnect' )."</small></label><input type='email' class='form-control' id='inputemail' placeholder='email@example.com' name='".$idobject."[email]' value='".(isset($object->email) ? $object->email : $current_user->user_email)."' autocomplete='off' ";

if ( defined("DOLICONNECT_DEMO") && ''.constant("DOLICONNECT_DEMO").'' == $current_user->ID && is_user_logged_in() && in_array($mode, array('thirdparty')) ) {
print " readonly";
} else {
print " required";
}
print "></div>";
if ( ( !is_user_logged_in() && ((isset($_GET["morphy"])&& $_GET["morphy"] == "mor" && get_option('doliconnect_disablepro') != 'phy') || get_option('doliconnect_disablepro') == 'mor' || (function_exists('dolikiosk') && ! empty(dolikiosk())) ) && in_array($mode, array('thirdparty'))) || (is_user_logged_in() && in_array($mode, array('thirdparty','contact','member','donation'))) ) {
print "<div class='col-12 col-md-5'><label for='inputmobile'><small><i class='fas fa-phone fa-fw'></i> ".__( 'Phone', 'doliconnect' )."</small></label><input type='tel' class='form-control' id='inputmobile' placeholder='".__( 'Phone', 'doliconnect' )."' name='".$idobject."[phone]' value='".(isset($object->phone) ? $object->phone : (isset($object->phone_pro) ? $object->phone_pro: null))."' autocomplete='off'></div>";
}
print "</div></li>";

if ( ( !is_user_logged_in() && ((isset($_GET["morphy"])&& $_GET["morphy"] == "mor" && get_option('doliconnect_disablepro') != 'phy') || get_option('doliconnect_disablepro') == 'mor' || (function_exists('dolikiosk') && ! empty(dolikiosk())) ) && in_array($mode, array('thirdparty'))) || (is_user_logged_in() && in_array($mode, array('thirdparty','contact','member','donation'))) ) {       
print "<li class='list-group-item'>";
 
print "<div class='form-row'><div class='col-12'><label for='inputaddress'><small><i class='fas fa-map-marked fa-fw'></i> ".__( 'Address', 'doliconnect' )."</small></label>
<textarea id='inlineFormInputGroup' name='".$idobject."[address]' class='form-control' rows='3' placeholder='".__( 'Address', 'doliconnect' )."' required>".(isset($object->address) ? $object->address : null)."</textarea></div></div>";

print "<div class='form-row'>
    <div class='col-md-6'><label for='inputaddress'><small><i class='fas fa-map-marked fa-fw'></i> ".__( 'Town', 'doliconnect' )."</small></label>
      <input type='text' class='form-control' placeholder='".__( 'Town', 'doliconnect' )."' name='".$idobject."[town]' value='".(isset($object->town) ? $object->town : null)."' autocomplete='off' required>
    </div>
    <div class='col'><label for='inputaddress'><small><i class='fas fa-map-marked fa-fw'></i> ".__( 'Zipcode', 'doliconnect' )."</small></label>
      <input type='text' class='form-control' placeholder='".__( 'Zipcode', 'doliconnect' )."' name='".$idobject."[zip]' value='".(isset($object->zip) ? $object->zip : null)."' autocomplete='off' required>
    </div>
    <div class='col'><label for='inputaddress'><small><i class='fas fa-map-marked fa-fw'></i> ".__( 'Country', 'doliconnect' )."</small></label>";

if ( function_exists('pll_the_languages') ) { 
$lang = pll_current_language('locale');
} else {
$lang = $current_user->locale;
}

$pays = callDoliApi("GET", "/setup/dictionary/countries?sortfield=favorite%2Clabel&sortorder=DESC%2CASC&limit=400&lang=".$lang, null, $delay);

if ( isset($pays) ) { 
print "<select class='custom-select' id='inputcountry'  name='".$idobject."[country_id]' required>";
print "<option value='' disabled ";
if ( !isset($object->country_id) && ! $object->country_id > 0 || $pays == 0) {
print "selected ";}
print ">".__( '- Select -', 'doliconnect' )."</option>";
foreach ( $pays as $postv ) { 
print "<option value='".$postv->id."' ";
if ( isset($object->country_id) && $object->country_id == $postv->id && $object->country_id != null && $postv->id != '0' ) {
print "selected ";
} elseif ( $postv->id == '0' ) { print "disabled "; }
print ">".$postv->label."</option>";
}
print "</select>";
} else {
print "<input type='text' class='form-control' id='inputcountry' placeholder='".__( 'Country', 'doliconnect' )."' name='".$idobject."[country_id]' value='".$object->country_id."' autocomplete='off' required>";
}
print "</div></div>";

print "</li>";

if( has_action('mydoliconnectuserform') && !in_array($mode, array('donation')) ) {
print "<li class='list-group-item'>";
print do_action('mydoliconnectuserform', $object);
print "</li>";
}

if ( in_array($mode, array('contact')) && doliversion('11.0.0') ) {

$contact_types = callDoliApi("GET", "/setup/dictionary/contact_types?sortfield=code&sortorder=ASC&limit=100&active=1&sqlfilters=(t.source%3A%3D%3A'external')%20AND%20(t.element%3A%3D%3A'commande')", null, $delay);//%20OR%20(t.element%3A%3D%3A'propal')

print "<li class='list-group-item'>";
if ( !isset($contact_types->error ) && $contact_types != null ) {
$typecontact = array();

if ( isset($object->roles) && $object->roles != null ) {
foreach ( $object->roles as $role ) {
$typecontact[] .= $role->id; 
}}

foreach ( $contact_types as $contacttype ) {                                                           //name='".$idobject."[roles][id]'
print "<div class='custom-control custom-checkbox'><input type='checkbox' class='custom-control-input'  value='".$contacttype->rowid."' id='".$idobject."[roles][".$contacttype->rowid."]' ";
if ( isset($object->roles) && $object->roles != null && in_array($contacttype->rowid, $typecontact)) { print " checked"; }
print "><label class='custom-control-label' for='".$idobject."[roles][".$contacttype->rowid."]'>".$contacttype->label."</label></div>";
}
 
}

print "</li>";
}

if ( !in_array($mode, array('contact', 'donation')) ) {
print "<li class='list-group-item'>";

if ( !in_array($mode, array('contact', 'member')) ) {
print "<div class='form-row'><div class='col'><label for='description'><small><i class='fas fa-bullhorn fa-fw'></i> ".__( 'About Yourself', 'doliconnect' )."</small></label>
<textarea type='text' class='form-control' name='description' id='description' rows='3' placeholder='".__( 'About Yourself', 'doliconnect' )."'>".$current_user->description."</textarea></div></div>";

print "<div class='form-row'><div class='col'><label for='description'><small><i class='fas fa-link fa-fw'></i> ".__( 'Website', 'doliconnect' )."</small></label>
<input type='url' class='form-control' name='".$idobject."[url]' id='website' placeholder='".__( 'Website', 'doliconnect' )."' value='".stripslashes(htmlspecialchars((isset($object->url) ? $object->url : null), ENT_QUOTES))."'></div></div>";
}

print "</li>";
}


if ( doliversion('11.0.0') ) { 
$socialnetworks = callDoliApi("GET", "/setup/dictionary/socialnetworks", null, $delay);
if ( !isset($socialnetworks->error) && $socialnetworks != null ) { 
print "<li class='list-group-item'><div class='form-row'>";
foreach ( $socialnetworks as $social ) { 
$code = $social->code;
print "<div class='col-12 col-md-4'><label for='inlineFormInputGroup'><small><i class='fab fa-".$social->code." fa-fw'></i> ".$social->label."</small></label>
<input type='text' name='".$idobject."[socialnetworks][".$social->code."]' class='form-control' id='inlineFormInputGroup' placeholder='".__( 'Username', 'doliconnect' )."' value='".stripslashes(htmlspecialchars((isset($object->socialnetworks->$code) ? $object->socialnetworks->$code : null), ENT_QUOTES))."'></div>";
}
print "</div></li>";
}

} else { 
print "<li class='list-group-item'><div class='form-row'>";
$facebook = callDoliApi("GET", "/doliconnector/constante/SOCIALNETWORKS_FACEBOOK", null, $delay);
if ( is_object($facebook) && $facebook->value == 1 ) {
print "<div class='col-12 col-md'><label for='inlineFormInputGroup'><small><i class='fab fa-facebook fa-fw'></i> Facebook</small></label>
<input type='text' name='".$idobject."[facebook]' class='form-control' id='inlineFormInputGroup' placeholder='".__( 'Username', 'doliconnect' )."' value='".stripslashes(htmlspecialchars((isset($object->facebook) ? $object->facebook : null), ENT_QUOTES))."'></div>";
}
$twitter = callDoliApi("GET", "/doliconnector/constante/SOCIALNETWORKS_TWITTER", null, $delay);
if ( is_object($twitter) && $twitter->value == 1 ) {
print "<div class='col-12 col-md'><label for='inlineFormInputGroup'><small><i class='fab fa-twitter fa-fw'></i> Twitter</small></label>
<input type='text' name='".$idobject."[twitter]' class='form-control' id='inlineFormInputGroup' placeholder='".__( 'Username', 'doliconnect' )."' value='".stripslashes(htmlspecialchars((isset($object->twitter) ? $object->twitter : null), ENT_QUOTES))."'></div>";
}
$skype = callDoliApi("GET", "/doliconnector/constante/SOCIALNETWORKS_SKYPE", null, $delay);
if ( is_object($skype) && $skype->value == 1 ) {
print "<div class='col-12 col-md'><label for='inlineFormInputGroup'><small><i class='fab fa-skype fa-fw'></i> Skype</small></label>
<input type='text' name='".$idobject."[skype]' class='form-control' id='inlineFormInputGroup' placeholder='".__( 'Username', 'doliconnect' )."' value='".stripslashes(htmlspecialchars((isset($object->skype) ? $object->skype : null), ENT_QUOTES))."'></div>";
}
$linkedin = callDoliApi("GET", "/doliconnector/constante/SOCIALNETWORKS_LINKEDIN", null, $delay);
if ( is_object($linkedin) && $linkedin->value == 1 ) {
print "<div class='col-12 col-md'><label for='inlineFormInputGroup'><small><i class='fab fa-linkedin-in fa-fw'></i> Linkedin</small></label>
<input type='text' name='".$idobject."[linkedin]' class='form-control' id='inlineFormInputGroup' placeholder='".__( 'Username', 'doliconnect' )."' value='".stripslashes(htmlspecialchars((isset($object->linkedin) ? $object->linkedin : null), ENT_QUOTES))."'></div>";
}
print "</div></li>";
}

}

if ( function_exists('dolikiosk') && ! isset($object) && ! empty(dolikiosk()) && $mode == 'thirdparty' ) {
print "<li class='list-group-item'><div class='form-row'><div class='col'><label for='pwd1'><small><i class='fas fa-key fa-fw'></i> ".__( 'Password', 'doliconnect' )."</small></label>
<input class='form-control' id='pwd1' type='password' name='pwd1' value ='' placeholder='".__( 'Choose your password', 'doliconnect' )."' autocomplete='off' required>
<small id='pwd1' class='form-text text-justify text-muted'>".__( 'Your password must be between 8 and 20 characters, including at least 1 digit, 1 letter, 1 uppercase.', 'doliconnect' )."</small></div></div>
<div class='form-row'><div class='col'><label for='pwd2'><small><i class='fas fa-key fa-fw'></i> ".__( 'Confirm your password', 'doliconnect' )."</small></label>
<input class='form-control' id='pwd2' type='password' name='pwd2' value ='' placeholder='".__( 'Confirm your password', 'doliconnect' )."' autocomplete='off' required></div>";
print "</div></li>";
}

if ( !is_user_logged_in() && in_array($mode, array('thirdparty')) ) {

if( has_action('register_form') ) {
print "<li class='list-group-item'>";
print do_action( 'register_form' );
print "</li>";
}

print "<li class='list-group-item'><div class='form-row'><div class='custom-control custom-checkbox my-1 mr-sm-2'>
<input type='checkbox' class='custom-control-input' value='1' id='optin1' name='optin1'>
<label class='custom-control-label' for='optin1'> ".__( 'I would like to receive the newsletter', 'doliconnect' )."</label></div></div>";
print "<div class='form-row'><div class='custom-control custom-checkbox my-1 mr-sm-2'>
<input type='checkbox' class='custom-control-input' value='forever' id='validation' name='validation' required>
<label class='custom-control-label' for='validation'> ".__( 'I read and accept the <a href="#" data-toggle="modal" data-target="#cgvumention">Terms & Conditions</a>.', 'doliconnect')."</label></div></div>";

if ( get_option( 'wp_page_for_privacy_policy' ) ) {
print "<div class='modal fade' id='cgvumention' tabindex='-1' role='dialog' aria-labelledby='cgvumention' aria-hidden='true'><div class='modal-dialog modal-lg modal-dialog-centered' role='document'><div class='modal-content'><div class='modal-header'><h5 class='modal-title' id='cgvumentionLabel'>".__( 'Terms & Conditions', 'doliconnect')."</h5><button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>
<div class='modal-body'>";
$post = get_post(get_option( 'wp_page_for_privacy_policy' ));
print $post->post_content;
//print apply_filters('the_content', get_post_field('post_content', get_option( 'wp_page_for_privacy_policy' )));
//print get_the_content( 'Read more', '', get_option( 'wp_page_for_privacy_policy' )); 
print "</div></div></div>";}
print "</li>";
}

print "</ul>";
}
//add_action( 'wp_loaded', 'doliconnectuserform', 10, 2);

function doliloading($id=loading) {
$loading = '<div id="doliloading-'.$id.'" style="display:none"><br><br><br><br><center><div class="align-middle">';
$loading .= '<div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div>'; 
//$loading .= '<i class="fas fa-spinner fa-pulse fa-3x fa-fw"></i>';
$loading .= '<h4>'.__( 'Loading', 'doliconnect' ).'</h4></div></center><br><br><br><br></div>';
return $loading;
}

function doliconnect_loading() {

doliconnect_enqueues();

$input = array("primary", "secondary", "success", "warning", "danger", "info", "light", "dark"); //
$rand_keys = array_rand($input, 4);

print '<div id="DoliconnectLoadingModal" class="modal fade bd-example-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-show="true" data-backdrop="static" data-keyboard="false">
<div class="modal-dialog modal-dialog-centered modal">
<div class="text-center text-light w-100">
<div class="spinner-grow text-'.$input[$rand_keys[0]].'" role="status"><span class="sr-only">Loading...</span></div>
<div class="spinner-grow text-'.$input[$rand_keys[1]].'" role="status"><span class="sr-only">Loading...</span></div>
<div class="spinner-grow text-'.$input[$rand_keys[2]].'" role="status"><span class="sr-only">Loading...</span></div>
<div class="spinner-grow text-'.$input[$rand_keys[3]].'" role="status"><span class="sr-only">Loading...</span></div>
<h4>'.__( 'Processing', 'doliconnect' ).'</h4>
</div></div></div>';

}
add_action( 'wp_footer', 'doliconnect_loading' );

function dolibug($msg = null) {
//header('Refresh: 180; URL='.esc_url(get_permalink()).'');
$bug = '<div id="dolibug" ><br><br><br><br><center><div class="align-middle"><i class="fas fa-bug fa-3x fa-fw"></i><h4>';
if ( ! empty($msg) ) {
$bug .= $msg;
} else { $bug .= __( 'Oops, our servers are unreachable. Thank you for coming back in a few minutes.', 'doliconnect'); }
$bug .= '</h4>';
if ( defined("DOLIBUG") && ! empty(constant("DOLIBUG")) ) {
$bug .= '<h6>'.__( 'Error code', 'doliconnect').' #'.constant("DOLIBUG").'</h6>';
$bug .= '<h6>'.$msg.'</h6>';
}
$bug .='</div></center><br><br><br><br></div>';
return $bug;
}

function Doliconnect_MailAlert( $user_login, $user) {

if ( $user->loginmailalert == 'on'  ) { //&& $user->ID != ''.constant("DOLICONNECT_DEMO").''
$sitename = get_option('blogname');
$siteurl = get_option('siteurl');
$subject = "[$sitename] ".__( 'Connection notification', 'doliconnect' );
$body = __( 'It appears that you have just logged on to our site the following IP address:', 'doliconnect' )."<br /><br />".$_SERVER['REMOTE_ADDR']."<br /><br />".__( 'If you have not made this action, please change your password immediately.', 'doliconnect' )."<br /><br />".sprintf(__('Your %s\'s team', 'doliconnect'), $sitename)."<br />$siteurl";				
$headers = array('Content-Type: text/html; charset=UTF-8');
$mail =  wp_mail($user->user_email, $subject, $body, $headers);
}

}
add_action('wp_login', 'Doliconnect_MailAlert', 10, 2);

function dolidocdownload($type, $ref=null, $fichier=null, $url=null, $name=null, $refresh = false) {
global $wpdb;
$ID = get_current_user_id();
 
if ( $name == null ) { $name=$fichier; } 

if ( doliversion('11.0.0') ) {
$doc = callDoliApi("GET", "/documents/download?modulepart=$type&original_file=$ref/$fichier", null, 0);
} else {
$doc = callDoliApi("GET", "/documents/download?module_part=$type&original_file=$ref/$fichier", null, 0);
}

if ( isset($_GET["download"]) && $_GET["securekey"] ==  hash('sha256', $ID.$type.$_GET["download"]) && $_GET["download"] == "$ref/$fichier" ) {

if ( !empty($refresh) ) {
if ( doliversion('11.0.0') ) {
$rdr = [
    'modulepart'  => $type,
    'originalfile' => $ref.'/'.$fichier,
    //'doctemplate'  => $type,
    //'langcode' => '',
	];
} else {
$rdr = [
    'module_part'  => $type,
    'original_file' => $ref.'/'.$fichier
	];
}
$doc = callDoliApi("PUT", "/documents/builddoc", $rdr, 0);
} else {
if ( doliversion('11.0.0') ) {
$doc = callDoliApi("GET", "/documents/download?modulepart=$type&original_file=$ref/$fichier", null, 0);
} else {
$doc = callDoliApi("GET", "/documents/download?module_part=$type&original_file=$ref/$fichier", null, 0);
}
} 

$decoded = base64_decode($doc->content);      
$up_dir = wp_upload_dir();
    if (!file_exists($up_dir['basedir'].'/doliconnect/'.$ID)) {
        mkdir($up_dir['basedir'].'/doliconnect/'.$ID, 0777, true);
    }
$upload_dir = wp_upload_dir(); 
$file=$upload_dir['basedir']."/doliconnect/".$ID."/".$doc->filename;
file_put_contents($file, $decoded);

if ( file_exists($file) ) {
header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename='.$doc->filename);
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
//header('Content-Length: '.$doc->filesize);
ob_clean();
flush();
readfile($file);
unlink($file);
}

}

if ( isset($ref) && isset($fichier) && isset($doc->content) ) { 
$document = "<a class='btn btn btn-outline-dark btn-sm btn-block' href='".esc_url( add_query_arg( array('download' => $ref."/".$fichier, 'securekey' => hash('sha256', $ID.$type.$ref."/".$fichier)), $url) )."' >$name <i class='fas fa-file-download'></i></a>";
} else { $document = "no document"; }

return $document;
}

function dolihelp($type) {

$aide = callDoliApi("GET", "/doliconnector/constante/MAIN_MODULE_TICKET", null, dolidelay('constante'));

if ( is_object($aide) && is_user_logged_in() && $aide->value == 1 ) {
$arr_params = array( 'module' => 'tickets', 'type' => $type, 'create' => true); 
$link=esc_url( add_query_arg( $arr_params, doliconnecturl('doliaccount'))); 
} elseif ( !empty(get_option('dolicontact')) ) {
$arr_params = array( 'create' => true); //'type' => $postorder->id,  
$link=esc_url( add_query_arg( $arr_params, doliconnecturl('dolicontact')));
} else {
$link='#';
}

$help = "<a href='".$link."' role='button' title='".__( 'Help?', 'doliconnect')."'><div class='d-block d-sm-block d-xs-block d-md-none'><i class='fas fa-question-circle'></i></div><div class='d-none d-md-block'><i class='fas fa-question-circle'></i> ".__( 'Need help?', 'doliconnect')."</div></a>";

return $help;
}

function dolidelay($delay = null, $refresh = false, $protect = false) {

if (! is_numeric($delay)) {

if (false ===  get_site_option('doliconnect_delay_'.$delay) ) {

if ($delay == 'constante') { $delay = MONTH_IN_SECONDS; }
elseif ($delay == 'dolibarr') { $delay = HOUR_IN_SECONDS; }
elseif ($delay == 'doliconnector') { $delay = HOUR_IN_SECONDS; }
elseif ($delay == 'paymentmethods') { $delay = WEEK_IN_SECONDS; }
elseif ($delay == 'thirdparty') { $delay = DAY_IN_SECONDS; }
elseif ($delay == 'contact') { $delay = WEEK_IN_SECONDS; }
elseif ($delay == 'proposal') { $delay = HOUR_IN_SECONDS; }
elseif ($delay == 'order') { $delay = HOUR_IN_SECONDS; }
elseif ($delay == 'contract') { $delay = HOUR_IN_SECONDS; }
elseif ($delay == 'member') { $delay = DAY_IN_SECONDS; }
elseif ($delay == 'donation') { $delay = DAY_IN_SECONDS; }
elseif ($delay == 'ticket') { $delay = HOUR_IN_SECONDS; }
elseif ($delay == 'product') { $delay = DAY_IN_SECONDS; }
elseif ($delay == 'cart') { $delay = 20 * MINUTE_IN_SECONDS; }
} else {
$delay = HOUR_IN_SECONDS;
//$delay = get_site_option('doliconnect_delay_'.$delay);
}
 
}

$array = get_option('doliconnect_ipkiosk');
if ( is_array($array) && in_array($_SERVER['REMOTE_ADDR'], $array) ) {
$delay=0;
}
if ( $refresh && is_user_logged_in() ) {
$delay=$delay*-1;
}

return $delay;
}

function dolirefresh( $origin, $url, $delay, $element = null) {

$refresh = "<script>";
$refresh .= 'function refreshloader(){
jQuery("#DoliconnectLoadingModal").modal("show");
jQuery(window).scrollTop(0); 
this.form.submit();
}';
$refresh .= "</script>";

if ( isset($element->date_modification) && !empty($element->date_modification) ) {
$refresh .= "<i class='fas fa-database'></i> ".date_i18n( get_option( 'date_format' ).' - '.get_option('time_format'), $element->date_modification, false);
} elseif ( get_option("_transient_timeout_".$origin) > 0 ) {
$refresh .= "<i class='fas fa-database'></i> ".date_i18n( get_option( 'date_format' ).' - '.get_option('time_format'), get_option("_transient_timeout_".$origin)-$delay, false);
} elseif (is_user_logged_in() ) {
$refresh .= __( 'Refresh', 'doliconnect' );
}
 
if (is_user_logged_in() ) {
$refresh .= " <a onClick='refreshloader()' href='".esc_url( add_query_arg( 'refresh', true, $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) )."' title='".__( 'Refresh datas', 'doliconnect' )."'><i class='fas fa-sync-alt'></i></a>";
}

return $refresh;
}

function dolikiosk() {
$array = get_option('doliconnect_ipkiosk');
if ( is_array($array) && in_array($_SERVER['REMOTE_ADDR'], $array) ) {
return true;
} else {
return false;
}
}

function dolialert ($type, $msg) { //__( 'Oops!', 'doliconnect' )
$alert ='<div class="alert alert-'.$type.' alert-dismissible fade show" role="alert">
<strong>'.__( 'Congratulations!', 'doliconnect' ).'</strong> '.$msg.'
<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
return $alert;
}

function doliloaderscript($idform) {
$loader = "<script>";
$loader .= 'window.setTimeout(function () {
    $(".alert-success").fadeTo(500, 0).slideUp(500, function () {
        $(this).remove();
    });
}, 5000);';

$loader .= 'var form = document.getElementById("'.$idform.'");';
$loader .= 'form.addEventListener("submit", function(event) {
jQuery("#DoliconnectLoadingModal").modal("show");
jQuery(window).scrollTop(0); 
console.log("submit");
form.submit();
});';
$loader .= "</script>";
return $loader;
}

function dolimodalloaderscript($idform) {
print "<script>";
?>
var form = document.getElementById('<?php print $idform; ?>');
form.addEventListener('submit', function(event) { 
jQuery(window).scrollTop(0);
jQuery('#Close<?php print $idform; ?>').hide(); 
jQuery('#Footer<?php print $idform; ?>').hide();
jQuery('#<?php print $idform; ?>').hide(); 
jQuery('#doliloading-<?php print $idform; ?>').show(); 
console.log("submit");
form.submit();
});
<?php
print "</script>";
}

function doliaddress($object) {
if ( !empty($object->name) ) {
$address = "<b><i class='fas fa-building fa-fw'></i> ".$object->name;
} else {
$address = "<b><i class='fas fa-building fa-fw'></i> ".($object->civility ? $object->civility : $object->civility_code)." ".$object->firstname." ".$object->lastname;
}
if ( !empty($object->default) ) { $address .= " <i class='fas fa-star fa-1x fa-fw' style='color:Gold'></i>"; }
if ( !empty($object->poste) ) { $address .= "<br>".$object->poste; }
if ( !empty($object->type) ) { $address .= "<br>".__( 'Type', 'doliconnect').": ".$object->type; }
$address .= "</b><br>";
$address .= "<small class='text-muted'>".$object->address.", ".$object->zip." ".$object->town." - ".$object->country."<br>".$object->email." - ".(isset($object->phone) ? $object->phone : $object->phone_pro)."</small>";
return $address;
}

function dolicontact($id, $refresh = false) {
$object = callDoliApi("GET", "/contacts/".$id, null, dolidelay('contact', esc_attr(isset($refresh) ? $refresh : null)));  
$address = "<b><i class='fas fa-address-book fa-fw'></i> ".($object->civility ? $object->civility : $object->civility_code)." ".$object->firstname." ".$object->lastname;
if ( !empty($object->default) ) { $address .= " <i class='fas fa-star fa-1x fa-fw' style='color:Gold'></i>"; }
if ( !empty($object->poste) ) { $address .= ", ".$object->poste; }
$address .= "</b><br>";
$address .= "<small class='text-muted'>".$object->address.", ".$object->zip." ".$object->town." - ".$object->country."<br>".$object->email." - ".$object->phone_pro."</small>";
return $address;
}

function dolitotal($object) {
$total = "<b>".__( 'Total excl. tax', 'doliconnect').": ".doliprice($object, 'ht', isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</b><br>";
$total .= "<b>".__( 'Total VAT', 'doliconnect').": ".doliprice($object, 'tva', isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</b><br>";
$total .= "<b>".__( 'Total incl. tax', 'doliconnect').": ".doliprice($object, 'ttc', isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</b><br>";
if ( ! empty($object->cond_reglement_id) ) { $total .= "<b>".__( 'Terms of the settlement', 'doliconnect').":</b> ".$object->cond_reglement; }
return $total;
}

function doliline($object, $mode = null) {
global $current_user;

$doliline=null;

if ( isset($object) && is_object($object) && $object->lines != null && (doliconnector($current_user, 'fk_soc') == $object->socid) ) {
foreach ( $object->lines as $line ) {
$doliline .= "<li class='list-group-item'>";     
if ( $line->date_start != '' && $line->date_end != '' )
{
$start = date_i18n('d/m/Y', $line->date_start);
$end = date_i18n('d/m/Y', $line->date_end);
$dates =" <i>(Du $start au $end)</i>";
}

$doliline .= '<div class="w-100 justify-content-between"><div class="row"><div class="col-1"><center><i class="fa fa-cube fa-fw fa-2x"></i></center></div><div class="col"> 
<h6 class="mb-1">'.doliproduct($line, 'product_label').'</h6>
<small><p class="mb-1">'.doliproduct($line, 'product_desc').'</p>
<i>'.(isset($dates) ? $dates : null).'</i></small></div>';

if ( $object->statut == 0 && !empty($mode)) {
if ( $line->fk_product > 0 ) {
$includestock = 0;
if ( ! empty(doliconnectid('dolicart')) ) {
$includestock = 1;
}
$product = callDoliApi("GET", "/products/".$line->fk_product."?includestockdata=".$includestock, null, 0);
}
$doliline .= '<div class="col d-none d-md-block col-md-2 text-right"><center>'.doliproductstock($product).'</center></div>';
}

$doliline .= '<div class="col-4 col-md-2 text-right"><h5 class="mb-1">'.doliprice($line, 'total_ttc', isset($line->multicurrency_code) ? $line->multicurrency_code : null).'</h5>';

if ( $object->statut == 0 && !empty($mode)) {
$doliline .= "<input type='hidden' name='updateorderproduct[".$line->fk_product."][product]' value='".$line->fk_product."'><input type='hidden' name='updateorderproduct[".$line->fk_product."][line]' value='".$line->id."'><input type='hidden' name='updateorderproduct[".$line->fk_product."][price]' value='".$line->subprice."'>";
$doliline .= "<input type='hidden' name='updateorderproduct[".$line->fk_product."][remise_percent]' value='".$line->remise_percent."'><input type='hidden' name='updateorderproduct[".$line->fk_product."][date_start]' value='".$line->date_start."'><input type='hidden' name='updateorderproduct[".$line->fk_product."][date_end]' value='".$line->date_end."'>";
$doliline .= "<select class='form-control form-control-sm' name='updateorderproduct[".$line->fk_product."][qty]' onchange='submit()'>";
if ( ($product->stock_reel-$line->qty > '0' && $product->type == '0') ) {
if ( $product->stock_reel-$line->qty >= '10' || (is_object($stock) && $stock->value != 1) ) {
$m2 = 10;
} elseif ($product->stock_reel>$line->qty) {
$m2 = $product->stock_reel;
} else { $m2 = $line->qty; }
} else {
if ($line->qty>1){$m2=$line->qty;}
else {$m2 = 1;}
}
	for($i=0;$i<=$m2;$i++){
		if ($i==$line->qty){
$doliline .= "<option value='$i' selected='selected'>$i</option>";
		}else{
$doliline .= "<option value='$i' >$i</option>";
		}
	}
$doliline .= "</select>";
} else {
$doliline .= '<h5 class="mb-1">x'.$line->qty.'</h5>';
}

$doliline .= "</div></div></li>";
}
} else {
$doliline .= "<li class='list-group-item list-group-item-light'><br><br><br><br><br><center><h5>".__( 'Your basket is empty.', 'doliconnect' )."</h5><br/><small>".dolihelp('COM')."</small></center>";
if ( !is_user_logged_in() ) {
$doliline .= '<center>'.__( 'If you already have an account,', 'doliconnect' ).' ';

if ( get_option('doliloginmodal') == '1' ) {
       
$doliline .= '<a href="#" data-toggle="modal" data-target="#DoliconnectLogin" data-dismiss="modal" title="'.__('Sign in', 'ptibogxivtheme').'" role="button">'.__( 'log in', 'doliconnect' ).'</a> ';
} else {
$doliline .= "<a href='".wp_login_url( doliconnecturl('dolicart') )."?redirect_to=".doliconnecturl('dolicart')."' >".__( 'log in', 'doliconnect' ).'</a> ';
}
$doliline .= __( 'to see your basket.', 'doliconnect' ).'</center>';
}
$doliline .= "<br><br><br><br><br></li>";
} 
return $doliline;
}

function doliunit($scale, $type, $refresh = null) {
$unit = callDoliApi("GET", "/setup/dictionary/units?sortfield=rowid&sortorder=ASC&limit=1&active=1&sqlfilters=(t.scale%3A%3D%3A'".$scale."')%20AND%20(t.unit_type%3A%3D%3A'".$type."')", null, dolidelay('constante', $refresh));
return $unit[0]->short_label;
}

function doliduration($object) {
if ( !is_null($object->duration_unit) && 0 < ($object->duration_value)) {
$duration = $object->duration_value.' ';
if ( $object->duration_value > 1 ) {
if ( $object->duration_unit == 'y' ) { $duration .=__( 'years', 'doliconnect' ); }
elseif ( $object->duration_unit == 'm' )  { $duration .=__( 'months', 'doliconnect' ); }
elseif ( $object->duration_unit == 'd' )  { $duration .=__( 'days', 'doliconnect' ); }
elseif ( $object->duration_unit == 'h' )  { $duration .=__( 'hours', 'doliconnect' ); }
elseif ( $object->duration_unit == 'i' )  { $duration .=__( 'minutes', 'doliconnect' ); }
} else {
if ( $object->duration_unit == 'y' ) { $duration .=__( 'year', 'doliconnect' );}
elseif ( $object->duration_unit == 'm' )  { $duration .=__( 'month', 'doliconnect' ); }
elseif ( $object->duration_unit == 'd' )  { $duration .=__( 'day', 'doliconnect' ); }
elseif ( $object->duration_unit == 'h' )  { $duration .=__( 'hour', 'doliconnect' ); }
elseif ( $object->duration_unit == 'i' )  { $duration .=__( 'minute', 'doliconnect' ); }
}

if ( $object->duration_unit == 'i' ) {
$altdurvalue=60/$object->duration_value; 
}

} else {
$duration = '';
}
return $duration;
}

function doliconnect_langs($arg) {

if (function_exists('pll_the_languages')) {       

print '<div class="modal fade" id="DoliconnectSelectLang" tabindex="-1" role="dialog" aria-labelledby="DoliconnectSelectLangLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
<div class="modal-content border-0"><div class="modal-header border-0">
<h5 class="modal-title" id="DoliconnectSelectLangLabel">'.__('Change language', 'doliconnect').'</h5><button id="closemodalSelectLang" type="button" class="close" data-dismiss="modal" aria-label="Close">
<span aria-hidden="true">&times;</span></button></div>';
 
print '<script>';
?>
function loadingSelectLangModal() {
jQuery("#closemodalSelectLang").hide();
jQuery("#SelectLangmodal-form").hide();
jQuery("#loadingSelectLang").show();  
}
<?php
print '</script>';

print '<div class="modal-body"><div class="card" id="SelectLangmodal-form"><ul class="list-group list-group-flush">';
$translations = pll_the_languages( array( 'raw' => 1 ) );
foreach ($translations as $key => $value) {
print "<a href='".$value['url']."?".$_SERVER["QUERY_STRING"]."' onclick='loadingSelectLangModal()' class='list-group-item list-group-item-action list-group-item-light'>
<img src='".$value['flag']."' class='img-fluid' alt='".$value['name']."'> ".$value['name'];
if ( $value['current_lang'] == true ) { print " <i class='fas fa-language fa-fw'></i>"; }
print "</a>";
}      

print '</ul></div>
<div id="loadingSelectLang" style="display:none"><br><br><br><center><div class="align-middle"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div><h4>'.__('Loading', 'doliconnect').'</h4></div></center><br><br><br></div>
</div></div></div></div>';

}    

}
add_action( 'wp_footer', 'doliconnect_langs', 10, 1);

function dolipaymentmethods($object = null, $module = null, $url = null, $refresh = false) {
global $current_user;

$request = "/doliconnector/".doliconnector($current_user, 'fk_soc')."/paymentmethods";
 
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$request .= "?type=".$module."&rowid=".$object->id;
$currency=strtolower($object->multicurrency_code?$object->multicurrency_code:'eur');  
$stripeAmount=($object->multicurrency_total_ttc?$object->multicurrency_total_ttc:$object->total_ttc)*100;
}

$listpaymentmethods = callDoliApi("GET", $request, null, dolidelay('paymentmethods', $refresh));
//print $listsource;

$lock = dolipaymentmodes_lock(); 

class myCounter implements Countable {
	public function count() {
		static $count = 0;
		return ++$count;
	}
}
 
$counter = new myCounter;

$paymentmethods = "<script src='https://js.stripe.com/v3/'></script>";
 
$paymentmethods .= doliloaderscript('doliconnect-paymentmethodsform');

$paymentmethods .='<div class="card shadow-sm"><ul class="list-group list-group-flush panel-group" id="accordion">';
if ( isset($listpaymentmethods->stripe) && empty($listpaymentmethods->stripe->live) ) {
$paymentmethods .="<li class='list-group-item list-group-item-info'><i class='fas fa-info-circle'></i> <b>".__( "Stripe's in sandbox mode", 'doliconnect')."</b></li>";
}
 
$pm = array();
if ( $listpaymentmethods->payment_methods != null ) {
$i = 0;
foreach ( $listpaymentmethods->payment_methods as $method ) {
$pm[] .= "".$method->id."";                                                                                                                      
$paymentmethods .="<li class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>";
$paymentmethods .='<input onclick="ShowHideDivPM(\''.$method->id.'\')" type="radio" id="'.$method->id.'" name="paymentmode" value="'.$method->id.'" class="custom-control-input" data-toggle="collapse" data-parent="#accordion" href="#'.$method->id.'" ';
if ( date('Y/n') >= $method->expiration && !empty($object) && !empty($method->expiration) ) { $paymentmethods .=" disabled "; }
elseif ( !empty($method->default_source) ) { $paymentmethods .=" checked "; }
$paymentmethods .=" ><label class='custom-control-label w-100' for='".$method->id."'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethods .='<center><i ';
if ( $method->type == 'sepa_debit' ) {
$paymentmethods .='class="fas fa-university fa-3x fa-fw" style="color:DarkGrey"';
} else {

if ( $method->brand == 'visa' ) { $paymentmethods .='class="fab fa-cc-visa fa-3x fa-fw" style="color:#172274"'; }
else if ( $method->brand == 'mastercard' ) { $paymentmethods .='class="fab fa-cc-mastercard fa-3x fa-fw" style="color:#FF5F01"'; }
else if ( $method->brand == 'amex' ) { $paymentmethods .='class="fab fa-cc-amex fa-3x fa-fw" style="color:#2E78BF"'; }
else { $paymentmethods .='class="fab fa-cc-amex fa-3x fa-fw"';}
}
$paymentmethods .='></i></center>';
$paymentmethods .='</div><div class="col-9 col-sm-7 col-md-8 col-xl-8 align-middle"><h6 class="my-0">';
if ( $method->type == 'sepa_debit' ) {
$paymentmethods .=__( 'Account', 'doliconnect' ).' '.$method->reference.'<small> <a href="'.$method->mandate_url.'" title="'.__( 'Mandate', 'doliconnect' ).' '.$method->mandate_reference.'" target="_blank"><i class="fas fa-info-circle"></i></a></small>';
} else {
$paymentmethods .=__( 'Card', 'doliconnect' ).' '.$method->reference;
}
if ( !empty($method->expiration) ) { $paymentmethods .=" - ".date("m/Y", strtotime($method->expiration.'/1')); }
$paymentmethods .="</h6><small class='text-muted'>".$method->holder."</small></div>";
$paymentmethods .="<div class='d-none d-sm-block col-2 align-middle text-right'>";
$paymentmethods .="<img src='".plugins_url('doliconnect/images/flag/'.strtolower($method->country).'.png')."' class='img-fluid' alt='".$method->country."'>";
$paymentmethods .="</div></div></label></div></li>";
$paymentmethods .='<li id="'.$method->id.'Panel" class="list-group-item list-group-item-secondary panel-collapse collapse"><div class="panel-body">';
$paymentmethods .='<div class="btn-group btn-block" role="group" aria-label="actions buttons">';
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .='<button type="button" onclick="PayPM(\''.$method->id.'\')" class="btn btn-danger"><b>'.__( 'Pay', 'doliconnect' )." ".doliprice($object, 'ttc', $currency).'</b></button>';
} else {
$paymentmethods .='<button type="button" onclick="DefaultPM(\''.$method->id.'\')" class="btn btn-warning"';
if ( !empty($method->default_source) ) { $paymentmethods .=" disabled"; }
$paymentmethods .='><b>'.__( "Favourite", 'doliconnect').'</b></button>
<button type="button" onclick="DeletePM(\''.$method->id.'\')" class="btn btn-danger"><b>'.__( 'Delete', 'doliconnect' ).'</b></button>';
}
$paymentmethods .='</div>';
$paymentmethods .='</div></li>';
$i++;
}} else {
$paymentmethods .='<li class="list-group-item list-group-item-light flex-column align-items-start"><div class="custom-control custom-radio">
<input type="radio" id="none" name="paymentmode" value="none" class="custom-control-input" data-toggle="collapse" data-parent="#accordion" href="#none" checked>
<label class="custom-control-label w-100" for="none"><div class="row"><div class="col-3 col-md-2 col-xl-2 align-middle">
<center><i class="fas fa-border-none fa-3x fa-fw"></i></center></div><div class="col-auto align-middle"><h6 class="my-0">'.__( 'No payment method', 'doliconnect').'</h6><small class="text-muted"></small></div></div></label>
</div></li>';
}
if ( isset($listpaymentmethods->stripe) && in_array('card', $listpaymentmethods->stripe->types) ) {
$paymentmethods .= '<li class="list-group-item list-group-item-action flex-column align-items-start"><div class="custom-control custom-radio">
<input type="radio" id="card" name="paymentmode" value="card" class="custom-control-input" data-toggle="collapse" data-parent="#accordion" href="#card">
<label class="custom-control-label w-100" for="card"><div class="row"><div class="col-3 col-md-2 col-xl-2 align-middle">
<center><i class="fas fa-credit-card fa-3x fa-fw"></i></center></div><div class="col-auto align-middle"><h6 class="my-0">'.__( 'Credit/debit card', 'doliconnect' ).'</h6><small class="text-muted">Visa, Mastercard, Amex...</small></div></div></label>
</div></li>';
$paymentmethods .= '<li id="cardPanel" class="list-group-item list-group-item-secondary panel-collapse collapse"><div class="panel-body">';
$paymentmethods .= '<input id="cardholder-name" name="cardholder-name" value="" type="text" class="form-control" placeholder="'.__( "Card's owner", 'doliconnect').'" autocomplete="off" required>
<label for="card-element"></label>
<div class="form-control" id="card-element"><!-- a Stripe Element will be inserted here. --></div>';
$paymentmethods .= "<p class='text-justify'>";
$blogname=get_bloginfo('name');
$paymentmethods .= '<small>'.sprintf( esc_html__( 'By providing your card and confirming this form, you are authorizing %s and Stripe, our payment service provider, to send instructions to the financial institution that issued your card to take payments from your card account in accordance with those instructions. You are entitled to a refund from your financial institution under the terms and conditions of your agreement with your financial institution. A refund must be claimed within 90 days starting from the date on which your card was debited.', 'doliconnect'), $blogname).'</small>';
$paymentmethods .= "</p>";
$paymentmethods .= '<p><div class="custom-control custom-radio custom-control-inline">
  <input type="radio" id="cardDefault0" name="cardDefault" value="0"  class="custom-control-input" checked>
  <label class="custom-control-label" for="cardDefault0">'.__( "Save", 'doliconnect').'</label>
</div>
<div class="custom-control custom-radio custom-control-inline">
  <input type="radio" id="cardDefault1" name="cardDefault" value="1" class="custom-control-input">
  <label class="custom-control-label" for="cardDefault1">'.__( "Save as favourite", 'doliconnect').'</label>
</div></p>';
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .='<button id="cardPayButton" class="btn btn-danger btn-block" ><b>'.__( 'Pay', 'doliconnect' )." ".doliprice($object, 'ttc', $currency).'</b></button>';
} else {
$paymentmethods .="<button id='cardButton' class='btn btn-warning btn-block' title='".__( 'Add', 'doliconnect')."'><b>".__( 'Add', 'doliconnect')."</b></button>";
}
$paymentmethods .='</div></li>';
}
if ( isset($listpaymentmethods->stripe) && in_array('sepa_debit', $listpaymentmethods->stripe->types) ) {
$paymentmethods .='<li class="list-group-item list-group-item-action flex-column align-items-start"><div class="custom-control custom-radio">
<input type="radio" id="iban" name="paymentmode" value="iban" class="custom-control-input" data-toggle="collapse" data-parent="#accordion" href="#iban">
<label class="custom-control-label w-100" for="iban"><div class="row"><div class="col-3 col-md-2 col-xl-2 align-middle">
<center><i class="fas fa-university fa-3x fa-fw"></i></center></div><div class="col-auto align-middle"><h6 class="my-0">'.__( 'Bank account', 'doliconnect' ).'</h6><small class="text-muted">Via SEPA Direct Debit</small></div></div></label>
</div></li>';
$paymentmethods .='<li id="ibanPanel" class="list-group-item list-group-item-secondary panel-collapse collapse"><div class="panel-body">';
$paymentmethods .='<input id="ibanholder-name" name="ibanholder-name" value="" type="text" class="form-control" placeholder="'.__( "Bank's owner", 'doliconnect').'" autocomplete="off" required>
<label for="iban-element"></label>
<div class="form-control" id="iban-element"><!-- a Stripe Element will be inserted here. --></div>';
$paymentmethods .="<p class='text-justify'>";
$blogname=get_bloginfo('name');
$paymentmethods .='<small>'.sprintf( esc_html__( 'By providing your IBAN and confirming this form, you are authorizing %s and Stripe, our payment service provider, to send instructions to your bank to debit your account and your bank to debit your account in accordance with those instructions. You are entitled to a refund from your bank under the terms and conditions of your agreement with your bank. A refund must be claimed within 8 weeks starting from the date on which your account was debited.', 'doliconnect'), $blogname).'</small>';
$paymentmethods .="</p><div id='bank-name'><!-- a Stripe Message will be inserted here. --></div>";
$paymentmethods .= '<p><div class="custom-control custom-radio custom-control-inline">
  <input type="radio" id="ibanDefault0" name="ibanDefault" value="0" class="custom-control-input" checked>
  <label class="custom-control-label" for="ibanDefault0">'.__( "Save", 'doliconnect').'</label>
</div>
<div class="custom-control custom-radio custom-control-inline">
  <input type="radio" id="ibanDefault1" name="ibanDefault" value="1" class="custom-control-input">
  <label class="custom-control-label" for="ibanDefault1">'.__( "Save as favourite", 'doliconnect').'</label>
</div></p>';
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .='<button id="ibanPayButton" class="btn btn-danger btn-block" ><b>'.__( 'Pay', 'doliconnect' )." ".doliprice($object, 'ttc', $currency).'</b></button>';
} else {
$paymentmethods .="<button id='ibanButton' class='btn btn-warning btn-block' title='".__( 'Add', 'doliconnect')."'><b>".__( 'Add', 'doliconnect')."</b></button>";
}
$paymentmethods .='</div></li>';
}
if ( isset($listpaymentmethods->stripe) && in_array('ideal', $listpaymentmethods->stripe->types) && !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .='<li class="list-group-item list-group-item-action flex-column align-items-start"><div class="custom-control custom-radio">
<input type="radio" id="ideal" name="paymentmode" value="ideal" class="custom-control-input" data-toggle="collapse" data-parent="#accordion" href="#ideal">
<label class="custom-control-label w-100" for="ideal"><div class="row"><div class="col-3 col-md-2 col-xl-2 align-middle">
<center><i class="fas fa-university fa-3x fa-fw"></i></center></div><div class="col-auto align-middle"><h6 class="my-0">'.__( 'iDEAL', 'doliconnect' ).'</h6><small class="text-muted">iDEAL PAYMENT</small></div></div></label>
</div></li>';
$paymentmethods .='<li id="idealPanel" class="list-group-item list-group-item-secondary panel-collapse collapse"><div class="panel-body">';
$paymentmethods .='<input id="idealholder-name" name="idealholder-name" value="" type="text" class="form-control" placeholder="'.__( "Bank's owner", 'doliconnect').'" autocomplete="off" required>
<label for="ideal-element"></label>
<div class="form-control" id="ideal-element"><!-- a Stripe Element will be inserted here. --></div>';
$paymentmethods .="<p class='text-justify'>";
$paymentmethods .="</p>";
$paymentmethods .='<button id="idealPayButton" class="btn btn-danger btn-block" ><b>'.__( 'Pay', 'doliconnect' )." ".doliprice($object, 'ttc', $currency).'</b></button>';
$paymentmethods .='</div></li>';
}

//offline payment methods
if ( isset($listpaymentmethods->RIB) && $listpaymentmethods->RIB != null ) {
$paymentmethods .= "<li class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input type='radio' id='vir' name='paymentmode' value='vir' class='custom-control-input' data-toggle='collapse' data-parent='#accordion' ";
if ( $listpaymentmethods->payment_methods == null && empty($listpaymentmethods->card) ) { $paymentmethods .= " checked"; }
$paymentmethods .= " href='#vir'><label class='custom-control-label w-100' for='vir'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethods .= '<center><i class="fas fa-university fa-3x fa-fw" style="color:DarkGrey"></i></center>';
$paymentmethods .= "</div><div class='col-auto align-middle'><h6 class='my-0'>".__( 'Transfer', 'doliconnect' )."</h6><small class='text-muted'>".__( 'See your receipt', 'doliconnect' )."</small>";
$paymentmethods .= '</div></div></label></div></li>';
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .='<li id="virPanel" class="list-group-item list-group-item-secondary panel-collapse collapse"><div class="panel-body">';
$paymentmethods .='<button type="button" onclick="PayPM(\'vir\')" class="btn btn-danger btn-block"><b>'.__( 'Pay', 'doliconnect' )." ".doliprice($object, 'ttc', $currency).'</b></button>';
$paymentmethods .='</div></li>';
}}
if ( isset($listpaymentmethods->CHQ) && $listpaymentmethods->CHQ != null ) {
$paymentmethods .= "<li class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input type='radio' id='chq' name='paymentmode' value='chq' class='custom-control-input' data-toggle='collapse' data-parent='#accordion' ";
if ( $listpaymentmethods->payment_methods == null && $listpaymentmethods->card != 1 && $listpaymentmethods->RIB == null ) { $paymentmethods .= " checked"; }
$paymentmethods .= " href='#chq'><label class='custom-control-label w-100' for='chq'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethods .= '<center><i class="fas fa-money-check fa-3x fa-fw" style="color:Tan"></i></center>';
$paymentmethods .= "</div><div class='col-auto align-middle'><h6 class='my-0'>".__( 'Check', 'doliconnect' )."</h6><small class='text-muted'>".__( 'See your receipt', 'doliconnect' )."</small>";
$paymentmethods .= '</div></div></label></div></li>';
if ( !empty($module) && is_object($object) && isset($object->id) ) {
$paymentmethods .='<li id="chqPanel" class="list-group-item list-group-item-secondary panel-collapse collapse"><div class="panel-body">';
$paymentmethods .='<button type="button" onclick="PayPM(\'chq\')" class="btn btn-danger btn-block"><b>'.__( 'Pay', 'doliconnect' )." ".doliprice($object, 'ttc', $currency).'</b></button>';
$paymentmethods .='</div></li>';
}}
if ( ! empty(dolikiosk()) ) {
$paymentmethod .= "<li class='list-group-item list-group-item-action flex-column align-items-startt'><div class='custom-control custom-radio'>
<input type='radio' id='liq' name='paymentmode' value='liq' class='custom-control-input' data-toggle='collapse' data-parent='#accordion' ";
if ( $listpaymentmethods->payment_methods == null && empty($listpaymentmethods->card) && $listpaymentmethods->CHQ == null && $listpaymentmethods->RIB == null ) { $paymentmethods .= " checked"; }
$paymentmethods .= " href='#liq'><label class='custom-control-label w-100' for='liq'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
$paymentmethods .= '<center><i class="fas fa-money-bill-alt fa-3x fa-fw" style="color:#85bb65"></i></center>';
$paymentmethods .= "</div><div class='col-auto align-middle'><h6 class='my-0'>".__( 'Cash', 'doliconnect' )."</h6><small class='text-muted'>".__( 'Go to reception desk', 'doliconnect' )."</small>";
$paymentmethods .= '</div></div></label></div></li>';
}

$paymentmethods .='</ul><div class="card-footer text-muted">';
$paymentmethods .="<small><div class='float-left'>";
$paymentmethods .=dolirefresh($request, $url, dolidelay('paymentmethods'));
$paymentmethods .="</div><div class='float-right'>";
$paymentmethods .=dolihelp('ISSUE');
$paymentmethods .="</div></small>";
$paymentmethods .='</div></div>';
$paymentmethods .="<div id='error-message' role='alert'><!-- a Stripe Message will be inserted here. --></div>";

$paymentmethods .="<script>";

if ( !empty($listpaymentmethods->stripe->account) ) {
$paymentmethods .="var stripe = Stripe('".$listpaymentmethods->stripe->publishable_key."', {
  stripeAccount: '".$listpaymentmethods->stripe->account."'
});";
} else {
$paymentmethods .="var stripe = Stripe('".$listpaymentmethods->stripe->publishable_key."');";
}

$paymentmethods .='var style = {
  base: {
    color: "#32325d",
    lineHeight: "18px",
    fontSmoothing: "antialiased",
    fontSize: "16px",
    "::placeholder": {
      color: "#aab7c4"
    }
  },
  invalid: {
    color: "#fa755a",
    iconColor: "#fa755a"
  }
};'; 

$paymentmethods .='var options = {
  style: style,
  supportedCountries: ["SEPA"],
  placeholderCountry: "'.$listpaymentmethods->thirdparty->countrycode.'",
};';

$paymentmethods .="function HideDivPM(controle = null) {
var listpm = ".json_encode($pm).";
var mpx;
for (mpx of listpm) {
if (mpx != controle) jQuery('#' + mpx + 'Panel').collapse('hide');
}
}";

$paymentmethods .="jQuery('#none,#card,#iban,#ideal,#vir,#chq,#liq').on('click', function (e) {
          e.stopPropagation();
var elements = stripe.elements(); 
var clientSecret = '".$listpaymentmethods->stripe->client_secret."';
var displayError = document.getElementById('error-message');
displayError.textContent = '';
HideDivPM(this.id);
          if(this.id == 'card'){
var cardElement = elements.create('card', options);
cardElement.mount('#card-element');
var cardholderName = document.getElementById('cardholder-name');
var cardButton = document.getElementById('cardButton');
cardElement.addEventListener('change', function(event) {
  // Handle real-time validation errors from the card Element.
    console.log('Reset error message');
    displayError.textContent = '';
  if (event.error) {
    displayError.textContent = event.error.message;
    displayError.classList.add('visible');
    cardButton.disabled = true;
  } else {
    displayError.textContent = '';
    displayError.classList.remove('visible');
    cardButton.disabled = false;
  }
});
              jQuery('#ibanPanel').collapse('hide');
              jQuery('#idealPanel').collapse('hide');
              jQuery('#cardPanel').collapse('show');
              jQuery('#virPanel').collapse('hide');
              jQuery('#chqPanel').collapse('hide');
cardholderName.addEventListener('change', function(event) {
    console.log('Reset error message');
    displayError.textContent = '';
    cardButton.disabled = false; 
});
cardButton.addEventListener('click', function(event) {
console.log('We click on cardButton');
cardButton.disabled = true; 
        if (cardholderName.value == '')
        	{        
				console.log('Field Card holder is empty');
				displayError.textContent = 'We need an owner as on your card';
        cardButton.disabled = false; 
        jQuery('#DoliconnectLoadingModal').modal('hide');   
        	}
        else
        	{
  stripe.confirmCardSetup(
    clientSecret,
    {
      payment_method: {
        card: cardElement,
        billing_details: {name: cardholderName.value}
      }
    }
  ).then(function(result) {
    if (result.error) {
      // Display error.message
jQuery('#DoliconnectLoadingModal').modal('hide');
console.log('Error occured when adding card');
displayError.textContent = 'Your card number seems to be wrong';    
    } else {
      // The setup has succeeded. Display a success message.
jQuery('#DoliconnectLoadingModal').modal('show');
var form = document.createElement('form');
form.setAttribute('action', '".$url."');
form.setAttribute('method', 'post');
form.setAttribute('id', 'doliconnect-paymentmethodsform');
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'add_paymentmethod');
inputvar.setAttribute('value', result.setupIntent.payment_method);
form.appendChild(inputvar);
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'default');
inputvar.setAttribute('value', jQuery('input:radio[name=cardDefault]:checked').val());
form.appendChild(inputvar);
document.body.appendChild(form);
form.submit();
    }
  }); 
          }
});
              //alert('1');
          }else if(this.id == 'iban'){
var ibanElement = elements.create('iban', options);
ibanElement.mount('#iban-element'); 
var ibanholderName = document.getElementById('ibanholder-name');
var ibanButton = document.getElementById('ibanButton'); 
var bankName = document.getElementById('bank-name');
bankName.textContent = '';
ibanElement.addEventListener('change', function(event) {
  // Handle real-time validation errors from the iban Element.
    console.log('Reset error message');
    displayError.textContent = '';
    bankName.textContent = '';
  if (event.error) {
    displayError.textContent = event.error.message;
    displayError.classList.add('visible');
    ibanButton.disabled = true;
  } else {
    displayError.textContent = '';
    displayError.classList.remove('visible');
    ibanButton.disabled = false;
  }

  // Display bank name corresponding to IBAN, if available.
  if (event.bankName) {
    bankName.textContent = event.bankName;
    bankName.classList.add('visible');
  } else {
    bankName.classList.remove('visible');
  }
});
              jQuery('#cardPanel').collapse('hide');
              jQuery('#idealPanel').collapse('hide');
              jQuery('#ibanPanel').collapse('show');
              jQuery('#virPanel').collapse('hide');
              jQuery('#chqPanel').collapse('hide');
ibanholderName.addEventListener('change', function(event) {
    console.log('Reset error message');
    displayError.textContent = '';
    ibanButton.disabled = false; 
});
ibanButton.addEventListener('click', function(event) {
console.log('We click on ibanButton');
ibanButton.disabled = true; 
        if (ibanholderName.value == '')
        	{        
				console.log('Field iban holder is empty');
				displayError.textContent = 'We need an owner as on your account';
        ibanButton.disabled = false; 
        jQuery('#DoliconnectLoadingModal').modal('hide');   
        	}
        else
        	{
  stripe.confirmSepaDebitSetup(
    clientSecret,
    {
      payment_method: {
        sepa_debit: ibanElement,
        billing_details: {
          name: ibanholderName.value,
          email: '".$listpaymentmethods->thirdparty->email."'
        }
      }
    }
  ).then(function(result) {
    if (result.error) {
      // Display error.message
jQuery('#DoliconnectLoadingModal').modal('hide');
console.log('Error occured when adding card');
displayError.textContent = 'We need an owner as on your account';    
    } else {
      // The setup has succeeded. Display a success message.
jQuery('#DoliconnectLoadingModal').modal('show');
var form = document.createElement('form');
form.setAttribute('action', '".$url."');
form.setAttribute('method', 'post');
form.setAttribute('id', 'doliconnect-paymentmethodsform');
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'add_paymentmethod');
inputvar.setAttribute('value', result.setupIntent.payment_method);
form.appendChild(inputvar);
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'default');
inputvar.setAttribute('value', jQuery('input:radio[name=ibanDefault]:checked').val());
form.appendChild(inputvar);
document.body.appendChild(form);
form.submit();
    }
  }); 
          }
});
              //alert('2');
          }else if(this.id == 'ideal'){
var idealElement = elements.create('idealBank', options);
idealElement.mount('#ideal-element'); 
var idealholderName = document.getElementById('idealholder-name');
              jQuery('#cardPanel').collapse('hide');
              jQuery('#ibanPanel').collapse('hide');
              jQuery('#virPanel').collapse('hide');
              jQuery('#chqPanel').collapse('hide');
              jQuery('#idealPanel').collapse('show');
              //alert('3');
          }else if(this.id == 'vir'){               
              jQuery('#cardPanel').collapse('hide');
              jQuery('#ibanPanel').collapse('hide');
              jQuery('#idealPanel').collapse('hide');
              jQuery('#chqPanel').collapse('hide');
              jQuery('#virPanel').collapse('show');
              //alert('3');
          }else if(this.id == 'chq'){
              jQuery('#cardPanel').collapse('hide');
              jQuery('#ibanPanel').collapse('hide');
              jQuery('#idealPanel').collapse('hide');
              jQuery('#virPanel').collapse('hide');
              jQuery('#chqPanel').collapse('show'); 
              //alert('3');   
          }else {
              jQuery('#cardPanel').collapse('hide');
              jQuery('#ibanPanel').collapse('hide');
              jQuery('#idealPanel').collapse('hide');
              jQuery('#virPanel').collapse('hide');
              jQuery('#chqPanel').collapse('hide');
              //alert('4');
          }
        })

function ShowHideDivPM(pm) {
              var displayError = document.getElementById('error-message');
              displayError.textContent = '';
              HideDivPM(pm);
              jQuery('#cardPanel').collapse('hide');
              jQuery('#ibanPanel').collapse('hide');
              jQuery('#idealPanel').collapse('hide');
              jQuery('#virPanel').collapse('hide');
              jQuery('#chqPanel').collapse('hide');
              jQuery('#' + pm + 'Panel').collapse('show');
        }
        
function DefaultPM(pm) {
jQuery('#DoliconnectLoadingModal').modal('show');
var form = document.createElement('form');
form.setAttribute('action', '".$url."');
form.setAttribute('method', 'post');
form.setAttribute('id', 'doliconnect-paymentmethodsform');
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'default_paymentmethod');
inputvar.setAttribute('value', pm);
form.appendChild(inputvar);
document.body.appendChild(form);
form.submit();
        }

function DeletePM(pm) {
jQuery('#DoliconnectLoadingModal').modal('show');
var form = document.createElement('form');
form.setAttribute('action', '".$url."');
form.setAttribute('method', 'post');
form.setAttribute('id', 'doliconnect-paymentmethodsform');
var inputvar = document.createElement('input');
inputvar.setAttribute('type', 'hidden');
inputvar.setAttribute('name', 'delete_paymentmethod');
inputvar.setAttribute('value', pm);
form.appendChild(inputvar);
document.body.appendChild(form);
form.submit();
        }
        
function PayPM(pm) {
jQuery('#DoliconnectLoadingModal').modal('show');
        }
";    
                 
$paymentmethods .="</script>";

return $paymentmethods;
}

function gdrf_data_request_form( $args = array() ) {
global $current_user;

	wp_enqueue_script( 'gdrf-public-scripts' );
 
	// Captcha
	$number_one = wp_rand( 1, 9 );
	$number_two = wp_rand( 1, 9 );

	// Default strings
	$defaults = array(
		'form_id'              => 'gdrf-form',
		'label_select_request' => esc_html__( 'Select your request:', 'doliconnect'),
		'label_select_export'  => esc_html__( 'Export Personal Data', 'doliconnect'),
		'label_select_remove'  => esc_html__( 'Remove Personal Data', 'doliconnect'),
		'label_input_email'    => esc_html__( 'Your email address (required)', 'doliconnect'),
		'label_input_captcha'  => esc_html__( 'Human verification (required):', 'doliconnect'),
		'value_submit'         => esc_html__( 'Send Request', 'doliconnect'),
		'request_type'         => 'both',
	);

	// Filter string array
	$args = wp_parse_args( $args, array_merge( $defaults, apply_filters( 'privacy_data_request_form_defaults', $defaults ) ) );

	// Check is 4.9.6 Core function wp_create_user_request() exists
	if ( function_exists( 'wp_create_user_request' ) ) {

		// Display the form
		ob_start();
		?>
		<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="<?php echo $args['form_id']; ?>">
			<input type="hidden" name="action" value="gdrf_data_request" />
			<input type="hidden" name="gdrf_data_human_key" id="gdrf_data_human_key" value="<?php echo $number_one . '000' . $number_two; ?>" />
			<input type="hidden" name="gdrf_data_nonce" id="gdrf_data_nonce" value="<?php echo wp_create_nonce( 'gdrf_nonce' ); ?>" />
    <div class="card shadow-sm"><ul class="list-group list-group-flush">
		<?php if ( 'export' === $args['request_type'] ) : ?>
			<input type="hidden" name="gdrf_data_type" value="export_personal_data" id="gdrf-data-type-export" />
		<?php elseif ( 'remove' === $args['request_type'] ) : ?>
			<input type="hidden" name="gdrf_data_type" value="remove_personal_data" id="gdrf-data-type-remove" />
		<?php else : ?>
<li class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='gdrf-data-type-export' class='custom-control-input' type='radio' name='gdrf_data_type' value='export_personal_data' checked>
<label class='custom-control-label w-100' for='gdrf-data-type-export'><div class='row'><div class='d-none d-sm-block col-sm-3 col-md-2 align-middle'>
<center><i class='fas fa-download fa-3x fa-fw'></i></center>
</div><div class='col-auto align-middle'><h6 class='my-0'><?php echo __( 'Export your data', 'doliconnect' ); ?></h6><small class='text-muted'><?php echo __( 'You will receive an email with a secure link to your data', 'doliconnect' ); ?></small>
</div></div></label></div></li>
<li class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='gdrf-data-type-remove' class='custom-control-input' type='radio' name='gdrf_data_type' value='remove_personal_data' disabled>
<label class='custom-control-label w-100' for='gdrf-data-type-remove'><div class='row'><div class='d-none d-sm-block col-sm-3 col-md-2 align-middle'>
<center><i class='fas fa-eraser fa-3x fa-fw'></i></center>
</div><div class='col-auto align-middle'><h6 class='my-0'><?php echo __( 'Erase your data', 'doliconnect' ); ?></h6><small class='text-muted'><?php echo __( 'Soon, you will be able to erase your account', 'doliconnect' ); ?></small>
</div></div></label></div></li>
<li class='list-group-item list-group-item-action flex-column align-items-start disabled'><div class='custom-control custom-radio'>
<input id='gdrf-data-type-delete' class='custom-control-input' type='radio' name='gdrf_data_type' value='delete_personal_data' disabled>
<label class='custom-control-label w-100' for='gdrf-data-type-delete'><div class='row'><div class='d-none d-sm-block col-sm-3 col-md-2 align-middle'>
<center><i class='fas fa-trash fa-3x fa-fw'></i></center>
</div><div class='col-auto align-middle'><h6 class='my-0'><?php echo __( 'Delete your account', 'doliconnect' ); ?></h6><small class='text-muted'><?php echo __( 'Soon, you will be able to delete your account', 'doliconnect' ); ?></small>
</div></div></label></div></li>
		<?php endif; ?>
    
    <?php if ( empty($current_user->user_email) ) : ?>
<li class='list-group-item list-group-item-action flex-column align-items-start'>
		<label for="gdrf_data_email">
			<?php echo esc_html( $args['label_input_email'] ); ?>
		</label>
				<input type="email" id="gdrf_data_email" name="gdrf_data_email" required />
</li>
		<?php else : ?>
      <input type='hidden' id='gdrf_data_email' name='gdrf_data_email' value='<?php echo $current_user->user_email; ?>'>
		<?php endif; ?>
       	<li class='list-group-item list-group-item-action flex-column align-items-start'>
				<label for="gdrf_data_human">
					<?php echo esc_html( $args['label_input_captcha'] ); ?>   
					<?php echo $number_one . ' + ' . $number_two . ' = ?'; ?>
				</label>
				<input type="text" id="gdrf_data_human" name="gdrf_data_human" required />
			</li>
      </ul>
			<div class="card-body">
        <input id="gdrf-submit-button" class="btn btn-danger btn-block" type="submit" value="<?php echo __( 'Validate the request', 'doliconnect' ); ?>"/>
      </div>
<div class="card-footer text-muted">
<small><div class='float-left'>
</div><div class='float-right'>
<?php echo dolihelp('ISSUE'); ?>
</div></small>
</div></div>
      
		</form>
		<?php
		return ob_get_clean();
	} else {
		// Display error message
		return esc_html__( 'This plugin requires WordPress 4.9.6.', 'doliconnect');
	}

}

?>
