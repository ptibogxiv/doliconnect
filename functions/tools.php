<?php

function dolimenu($name, $traduction, $right, $content) {


}

function doliconnectuserform($object, $delay, $mode) {
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
print "<div class='form-row'><div class='col-12'><label for='coordonnees'><small><i class='fas fa-building fa-fw'></i> ".__( 'Name of company', 'doliconnect' )."</small></label><input type='text' class='form-control' id='inputcompany' placeholder='".__( 'Name of company', 'doliconnect' )."' name='".$idobject."[name]' value='".$current_user->billing_company."' required></div></div>";
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

$pays = callDoliApi("GET", "/setup/dictionary/countries?sortfield=favorite%2Clabel&sortorder=DESC%2CASC&limit=400&lang=".$lang, null , $delay);

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

if ( !in_array($mode, array('donation')) ) {
print "<li class='list-group-item'>";

if ( !in_array($mode, array('contact', 'member')) ) {
print "<div class='form-row'><div class='col'><label for='description'><small><i class='fas fa-bullhorn fa-fw'></i> ".__( 'About Yourself', 'doliconnect' )."</small></label>
<textarea type='text' class='form-control' name='description' id='description' rows='3' placeholder='".__( 'About Yourself', 'doliconnect' )."'>".$current_user->description."</textarea></div></div>";

print "<div class='form-row'><div class='col'><label for='description'><small><i class='fas fa-link fa-fw'></i> ".__( 'Website', 'doliconnect' )."</small></label>
<input type='url' class='form-control' name='".$idobject."[url]' id='website' placeholder='".__( 'Website', 'doliconnect' )."' value='".stripslashes(htmlspecialchars((isset($object->url) ? $object->url : null), ENT_QUOTES))."'></div></div>";
}

print "<div class='form-row'>";
$facebook = callDoliApi("GET", "/doliconnector/constante/SOCIALNETWORKS_FACEBOOK", null, $delay);
if ( is_object($facebook) && $facebook->value == 1 ) {
print "<div class='col-12 col-md'><label for='inlineFormInputGroup'><small><i class='fab fa-facebook-f fa-fw'></i> Facebook</small></label>
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
print "</div>"; 
print "</li>";
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
}
$bug .='</div></center><br><br><br><br></div>';
return $bug;
}

function Doliconnect_MailAlert( $user_login, $user) {
global $wpdb;

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

$dolibarr = callDoliApi("GET", "/status", null, dolidelay('dolibarr'));
$versiondoli = explode("-", $dolibarr->success->dolibarr_version);
if ( is_object($dolibarr) && version_compare($versiondoli[0], '11.0.0') >= 0 ) {
$doc = callDoliApi("GET", "/documents/download?modulepart=$type&original_file=$ref/$fichier", null, 0);
} else {
$doc = callDoliApi("GET", "/documents/download?module_part=$type&original_file=$ref/$fichier", null, 0);
}

if ( isset($_GET["download"]) && $_GET["securekey"] ==  hash('sha256', $ID.$type.$_GET["download"]) && $_GET["download"] == "$ref/$fichier" ) {

if ( !empty($refresh) ) {
$dolibarr = callDoliApi("GET", "/status", null, dolidelay('dolibarr'));
$versiondoli = explode("-", $dolibarr->success->dolibarr_version);
if ( is_object($dolibarr) && version_compare($versiondoli[0], '11.0.0') >= 0 ) {
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
$dolibarr = callDoliApi("GET", "/status", null, dolidelay('dolibarr'));
$versiondoli = explode("-", $dolibarr->success->dolibarr_version);
if ( is_object($dolibarr) && version_compare($versiondoli[0], '11.0.0') >= 0 ) {
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
} else { $document = ""; }

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

$help = "<a href='".$link."' role='button' title='".__( 'Help?', 'doliconnect')."'><i class='fas fa-question-circle'></i> ".__( 'Need help?', 'doliconnect')."</a>";

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
$refresh .= __( 'Last modified', 'doliconnect' ).": ".date_i18n('d/m/Y - H:i', $element->date_modification, false);
} elseif ( get_option("_transient_timeout_".$origin) > 0 ) {
$refresh .= __( 'Last modified', 'doliconnect' ).": ".date_i18n('d/m/Y - H:i', get_option("_transient_timeout_".$origin)-$delay, false);
} elseif (is_user_logged_in() ) {
$refresh .= __( 'Refresh', 'doliconnect' );
}
 
if (is_user_logged_in() ) {
$refresh .= " <a onClick='refreshloader()' href='".esc_url( add_query_arg( 'refresh', true, $url) )."' title='".__( 'Refresh', 'doliconnect' )."'><i class='fas fa-sync-alt'></i></a>";
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

function dolialert ($type = 'success', $msg) { //__( 'Oops!', 'doliconnect' )
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
$total .="<b>".__( 'Total incl. tax', 'doliconnect').": ".doliprice($object, 'ttc', isset($object->multicurrency_code) ? $object->multicurrency_code : null)."</b>";
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

if ( function_exists('pll_the_languages') ) { 
$lang = pll_current_language('locale');
$doliline .= '<div class="w-100 justify-content-between"><div class="row"><div class="col"> 
<h6 class="mb-1">'.(isset($line->multilangs->$lang->label) ? $line->multilangs->$lang->label : $line->product_label).'</h6>
<small><p class="mb-1">'.(isset($line->multilangs->$lang->description) ? $line->multilangs->$lang->description : $line->description).'</p>
<i>'.(isset($dates) ? $dates : null).'</i></small></div>';

} else {
$doliline .= '<div class="w-100 justify-content-between"><div class="row"><div class="col"> 
<h6 class="mb-1">'.($line->fk_product ? $line->product_label : $line->custom_label).'</h6>
<small><p class="mb-1">'.$line->description.'</p>
<i>'.(isset($dates) ? $dates : null).'</i></small></div>';
}

if ( $object->statut == 0 && !empty($mode)) {
if ( $line->fk_product > 0 ) {
$product = callDoliApi("GET", "/products/".$line->fk_product."?includestockdata=1", null, 0);
}
$doliline .= '<div class="col d-none d-md-block col-md-2 text-right">'.doliproductstock($product).'</div>';
}

$doliline .= '<div class="col-4 col-md-2 text-right"><h5 class="mb-1">'.doliprice($line, 'subprice', isset($line->multicurrency_code) ? $line->multicurrency_code : null).'</h5>';

if ( $object->statut == 0 && !empty($mode)) {
$doliline .= "<input type='hidden' name='updateorderproduct[".$line->fk_product."][product]' value='".$line->fk_product."'><input type='hidden' name='updateorderproduct[".$line->fk_product."][line]' value='".$line->id."'><input type='hidden' name='updateorderproduct[".$line->fk_product."][price]' value='".$line->subprice."'>";
$doliline .= "<input type='hidden' name='updateorderproduct[".$line->fk_product."][date_start]' value='".$line->date_start."'><input type='hidden' name='updateorderproduct[".$line->fk_product."][date_end]' value='".$line->date_end."'>";
$doliline .= "<select class='form-control' name='updateorderproduct[".$line->fk_product."][qty]' onchange='submit()'>";
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

function doliduration($object) {
if ( !is_null($object->duration_unit) && '0' < ($object->duration_value)) {
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

}
return $duration;
}

?>
