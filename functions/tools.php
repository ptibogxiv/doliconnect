<?php
function doliconnectuserform($object, $delay, $mode) {
global $current_user;

if ( is_object($object) && $object->id > 0 ) {
$idobject=$mode."[".$object->id."]";
} 
else { $idobject=$mode; }

echo "<ul class='list-group list-group-flush'><li class='list-group-item'>";

if ( ! isset($object) && $mode != 'contact' && $mode != 'member' ) {
echo "<div class='form-row'><div class='col-12'>";
if ( isset($_GET["pro"]) && !get_option('doliconnect_disablepro') ) {
echo "<a  href='".wp_registration_url(get_permalink())."' role='button' title='".__( 'Create a personnal account', 'doliconnect' )."'><small>(".__( 'Personnal account', 'doliconnect' )."?)</small></a>";                                                                                                                                                                                                                                                                                                                                     
}
elseif (!get_option('doliconnect_disablepro')) {
echo "<a  href='".wp_registration_url(get_permalink())."&pro' role='button' title='".__( 'Create a pro/supplier account', 'doliconnect' )."'><small>(".__( 'Pro account', 'doliconnect' )."?)</small></a>";
}

echo "</div></div>";
}

if ( $mode != 'mini' ) {
if ( $current_user->billing_type == 'mor' or (isset($_GET["pro"]) && !get_option('doliconnect_disablepro')) ) {
echo "<div class='form-row'><div class='col-12'><input type='hidden' name='".$idobject."[morphy]' value='mor'><label for='coordonnees'><small><i class='fas fa-building'></i> ".__( 'Name of company', 'doliconnect' )."</small></label><input type='text' class='form-control' id='inputcompany' placeholder='".__( 'Name of company', 'doliconnect' )."' name='".$idobject."[name]' value='".$current_user->billing_company."' required></div>";
echo "</div></li><li class='list-group-item'>";
} else {
echo "<input type='hidden' name='".$idobject."[morphy]' value='phy'>";
}
}

echo "<div class='form-row'><div class='col-12 col-md-3'><label for='inputCivility'><small><i class='fas fa-user'></i> ".__( 'Civility', 'doliconnect' )."</small></label>";
$civility = callDoliApi("GET", "/setup/dictionary/civility?sortfield=code&sortorder=ASC&limit=100", null, $delay);
if ( isset($civility->error) ) {
$civility = callDoliApi("GET", "/setup/dictionary/civilities?sortfield=code&sortorder=ASC&limit=100&active=1", null, $delay); 
}

echo "<select class='custom-select' id='identity'  name='".$idobject."[civility_id]' required>";
echo "<option value='' disabled ";
if ( empty($object->civility_id) ) {
echo "selected ";}
echo ">".__( '- Select -', 'doliconnect' )."</option>";
if ( !isset($civility->error ) && $civility != null ) { 
foreach ( $civility as $postv ) {

echo "<option value='".$postv->code."' ";
if ( $current_user->civility_id == $postv->code && $current_user->civility_id != null) {
echo "selected ";}
echo ">".$postv->label."</option>";

}} else {
echo "<option value='MME' ";
if ( $current_user->civility_id == 'MME' && $object->civility_id != null) {
echo "selected ";}
echo ">".__( 'Miss', 'doliconnect' )."</option>";
echo "<option value='MR' ";
if ( $current_user->civility_id == 'MR' && $object->civility_id != null) {
echo "selected ";}
echo ">".__( 'Mister', 'doliconnect' )."</option>";
}
echo "</select>";
echo "</div>
    <div class='col-12 col-md-4'>
      <label for='inputFirstname'><small><i class='fas fa-user'></i> ".__( 'Firstname', 'doliconnect' )."</small></label>
      <input type='text' name='".$idobject."[firstname]' class='form-control' placeholder='".__( 'Firstname', 'doliconnect' )."' value='".(isset($object->firstname) ? $object->firstname : stripslashes(htmlspecialchars($current_user->user_firstname, ENT_QUOTES)))."' required>
    </div>
    <div class='col-12 col-md-5'>
      <label for='inputLastname'><small><i class='fas fa-user'></i> ".__( 'Lastname', 'doliconnect' )."</small></label>
      <input type='text' name='".$idobject."[lastname]' class='form-control' placeholder='".__( 'Lastname', 'doliconnect' )."' value='".(isset($object->lastname) ? $object->lastname : stripslashes(htmlspecialchars($current_user->user_lastname, ENT_QUOTES)))."' required>
    </div></div>";

if ( !empty($object->birth) ) { $birth = date_i18n('Y-m-d', $object->birth); }
echo "<div class='form-row'><div class='col'><label for='inputbirth'><small><i class='fas fa-birthday-cake fa-fw'></i> ".__( 'Birthday', 'doliconnect' )."</small></label><input type='date' name='".$idobject."[birth]' class='form-control' value='".(isset($birth) ? $birth : $current_user->billing_birth)."' id='inputbirth' placeholder='yyyy-mm-dd' autocomplete='off'";
if ( $mode != 'contact' ) { echo " required"; } 
echo "></div>";
echo "<div class='col-12 col-md-7'>";
if ( $mode != 'contact' ) {
echo "<label for='inputnickname'><small><i class='fas fa-user-secret fa-fw'></i> ".__( 'Display name', 'doliconnect' )."</small></label><input type='text' class='form-control' id='inputnickname' placeholder='".__( 'Nickname', 'doliconnect' )."' name='user_nicename' value='".stripslashes(htmlspecialchars($current_user->nickname, ENT_QUOTES))."' autocomplete='off' required >";
} else {
echo "<label for='inputnickname'><small><i class='fas fa-user-secret fa-fw'></i> ".__( 'Title / Job', 'doliconnect' )."</small></label><input type='text' class='form-control' id='inputtitle/job' placeholder='".__( 'Title / Job', 'doliconnect' )."' name='".$idobject."[poste]' value='".stripslashes(htmlspecialchars($object->poste, ENT_QUOTES))."' autocomplete='off'>";
}
echo "</div></div>";

echo "<div class='form-row'><div class='col'><label for='inputemail'><small><i class='fas fa-at fa-fw'></i> ".__( 'Email', 'doliconnect' )."</small></label><input type='email' class='form-control' id='inputemail' placeholder='email@example.com' name='".$idobject."[email]' value='".(isset($object->email) ? $object->email : $current_user->user_email)."' autocomplete='off' ";

if ( defined("DOLICONNECT_DEMO") == $current_user->ID && is_user_logged_in() ) {
echo " readonly";
} else {
echo " required";
}
echo "></div>";
if ( ( isset($_GET["pro"]) && !get_option('doliconnect_disablepro') ) || $mode == 'thirdparty' || $mode == 'contact') {   
echo "<div class='col-12 col-md-5'><label for='inputmobile'><small><i class='fas fa-phone fa-fw'></i> ".__( 'Phone', 'doliconnect' )."</small></label><input type='tel' class='form-control' id='inputmobile' placeholder='".__( 'Phone', 'doliconnect' )."' name='".$idobject."[phone]' value='".$object->phone."' autocomplete='off'></div>";
}
echo "</div></li>";

if ( ( isset($_GET["pro"]) && !get_option('doliconnect_disablepro') ) || $mode == 'thirdparty' || $mode == 'contact') {       
echo "<li class='list-group-item'>";
 
echo "<div class='form-row'><div class='col-12'><label for='inputaddress'><small><i class='fas fa-map-marked fa-fw'></i> ".__( 'Address', 'doliconnect' )."</small></label>
<textarea id='inlineFormInputGroup' name='".$idobject."[address]' class='form-control' rows='3' placeholder='".__( 'Address', 'doliconnect' )."' required>".$object->address."</textarea></div></div>";

echo "<div class='form-row'>
    <div class='col-md-6'><label for='inputaddress'><small><i class='fas fa-map-marked fa-fw'></i> ".__( 'Town', 'doliconnect' )."</small></label>
      <input type='text' class='form-control' placeholder='".__( 'Town', 'doliconnect' )."' name='".$idobject."[town]' value='".$object->town."' autocomplete='off' required>
    </div>
    <div class='col'><label for='inputaddress'><small><i class='fas fa-map-marked fa-fw'></i> ".__( 'Zipcode', 'doliconnect' )."</small></label>
      <input type='text' class='form-control' placeholder='".__( 'Zipcode', 'doliconnect' )."' name='".$idobject."[zip]' value='".$object->zip."' autocomplete='off' required>
    </div>
    <div class='col'><label for='inputaddress'><small><i class='fas fa-map-marked fa-fw'></i> ".__( 'Country', 'doliconnect' )."</small></label>";
$pays = callDoliApi("GET", "/setup/dictionary/countries?sortfield=favorite%2Clabel&sortorder=DESC%2CASC&limit=500", null , $delay);

if ( isset($pays) ) { 
echo "<select class='custom-select' id='inputcountry'  name='".$idobject."[country_id]' required>";
echo "<option value='' disabled ";
if ( ! $object->country_id > 0 || $pays == 0) {
echo "selected ";}
echo ">".__( '- Select -', 'doliconnect' )."</option>";
foreach ( $pays as $postv ) { 
echo "<option value='".$postv->id."' ";
if ( $object->country_id == $postv->id && $object->country_id != null && $postv->id != '0' ) {
echo "selected ";
} elseif ( $postv->id == '0' ) { echo "disabled "; }
echo ">".$postv->label."</option>";
}
echo "</select>";
} else {
echo "<input type='text' class='form-control' id='inputcountry' placeholder='".__( 'Country', 'doliconnect' )."' name='".$idobject."[country_id]' value='".$object->country_id."' autocomplete='off' required>";
}
echo "</div></div>";

echo "</li>";

if ( function_exists('dolikiosk') && ! isset($object) && ! empty(dolikiosk()) ) {
echo "<li class='list-group-item'><div class='form-row'><div class='col'><label for='pwd1'><small><i class='fas fa-key fa-fw'></i> ".__( 'Password', 'doliconnect' )."</small></label>
<input class='form-control' id='pwd1' type='password' name='pwd1' value ='' placeholder='".__( 'Choose your password', 'doliconnect' )."' autocomplete='off' required>
<small id='pwd1' class='form-text text-justify text-muted'>".__( 'Your password must be between 8 and 20 characters, including at least 1 digit, 1 letter, 1 uppercase.', 'doliconnect' )."</small></div></div>
<div class='form-row'><div class='col'><label for='pwd2'><small><i class='fas fa-key fa-fw'></i> ".__( 'Confirm your password', 'doliconnect' )."</small></label>
<input class='form-control' id='pwd2' type='password' name='pwd2' value ='' placeholder='".__( 'Confirm your password', 'doliconnect' )."' autocomplete='off' required></div>";
echo "</div></li>";
}

if( has_action('mydoliconnectuserform') ) {
echo "<li class='list-group-item'>";
do_action('mydoliconnectuserform', $object);
echo "</li>";
}

echo "<li class='list-group-item'>";
if ( $mode != 'mini' ) {
echo "<div class='form-row'><div class='col'><label for='description'><small><i class='fas fa-bullhorn fa-fw'></i> ".__( 'About Yourself', 'doliconnect' )."</small></label>
<textarea type='text' class='form-control' name='description' id='description' rows='3' placeholder='".__( 'About Yourself', 'doliconnect' )."'>".$current_user->description."</textarea></div></div>";

echo "<div class='form-row'><div class='col'><label for='description'><small><i class='fas fa-link fa-fw'></i> ".__( 'Website', 'doliconnect' )."</small></label>
<input type='url' class='form-control' name='".$idobject."[url]' id='website' placeholder='".__( 'Website', 'doliconnect' )."' value='".stripslashes(htmlspecialchars($object->url, ENT_QUOTES))."'></div></div>";
}

echo "<div class='form-row'>";
$facebook = callDoliApi("GET", "/doliconnector/constante/SOCIALNETWORKS_FACEBOOK", null, $delay);
if ( is_object($facebook) && $facebook->value == 1 ) {
echo "<div class='col-12 col-md'><label for='inlineFormInputGroup'><small><i class='fab fa-facebook-f fa-fw'></i> Facebook</small></label>
<input type='text' name='".$idobject."[facebook]' class='form-control' id='inlineFormInputGroup' placeholder='".__( 'Username', 'doliconnect' )."' value='".stripslashes(htmlspecialchars($object->facebook, ENT_QUOTES))."'></div>";
}
$twitter = callDoliApi("GET", "/doliconnector/constante/SOCIALNETWORKS_TWITTER", null, $delay);
if ( is_object($twitter) && $twitter->value == 1 ) {
echo "<div class='col-12 col-md'><label for='inlineFormInputGroup'><small><i class='fab fa-twitter fa-fw'></i> Twitter</small></label>
<input type='text' name='".$idobject."[twitter]' class='form-control' id='inlineFormInputGroup' placeholder='".__( 'Username', 'doliconnect' )."' value='".stripslashes(htmlspecialchars($object->twitter, ENT_QUOTES))."'></div>";
}
$skype = callDoliApi("GET", "/doliconnector/constante/SOCIALNETWORKS_SKYPE", null, $delay);
if ( is_object($skype) && $skype->value == 1 ) {
echo "<div class='col-12 col-md'><label for='inlineFormInputGroup'><small><i class='fab fa-skype fa-fw'></i> Skype</small></label>
<input type='text' name='".$idobject."[skype]' class='form-control' id='inlineFormInputGroup' placeholder='".__( 'Username', 'doliconnect' )."' value='".stripslashes(htmlspecialchars($object->skype, ENT_QUOTES))."'></div>";
}
$linkedin = callDoliApi("GET", "/doliconnector/constante/SOCIALNETWORKS_LINKEDIN", null, $delay);
if ( is_object($linkedin) && $linkedin->value == 1 ) {
echo "<div class='col-12 col-md'><label for='inlineFormInputGroup'><small><i class='fab fa-linkedin fa-fw'></i> Linkedin</small></label>
<input type='text' name='".$idobject."[linkedin]' class='form-control' id='inlineFormInputGroup' placeholder='".__( 'Username', 'doliconnect' )."' value='".stripslashes(htmlspecialchars($object->linkedin, ENT_QUOTES))."'></div>";
}
echo "</div>";

echo "</li>";

}

if ( ! isset($object) ) {
echo "<li class='list-group-item'><div class='form-row'><div class='custom-control custom-checkbox my-1 mr-sm-2'>
<input type='checkbox' class='custom-control-input' value='1' id='optin1' name='optin1'>
<label class='custom-control-label' for='optin1'> ".__( 'I would like to receive the newsletter', 'doliconnect' )."</label></div></div>";
echo "<div class='form-row'><div class='custom-control custom-checkbox my-1 mr-sm-2'>
<input type='checkbox' class='custom-control-input' value='forever' id='validation' name='validation' required>
<label class='custom-control-label' for='validation'> ".__( 'I read and accept the <a href="#" data-toggle="modal" data-target="#cgvumention">Terms & Conditions</a>.', 'doliconnect')."</label></div></div>";

if ( get_option( 'wp_page_for_privacy_policy' ) ) {
echo "<div class='modal fade' id='cgvumention' tabindex='-1' role='dialog' aria-labelledby='cgvumention' aria-hidden='true'><div class='modal-dialog modal-lg modal-dialog-centered' role='document'><div class='modal-content'><div class='modal-header'><h5 class='modal-title' id='cgvumentionLabel'>".__( 'Terms & Conditions', 'doliconnect')."</h5><button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>
<div class='modal-body'>";
echo apply_filters('the_content', get_post_field('post_content', get_option( 'wp_page_for_privacy_policy' ))); 
echo "</div></div></div>";}

echo "</li>";
}

echo "</ul>";
 
//return $form;
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

echo '<div id="DoliconnectLoadingModal" class="modal fade bd-example-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" data-show="true" data-backdrop="static" data-keyboard="false">
<div class="modal-dialog modal-dialog-centered modal">
<div class="text-center text-light w-100">
<div class="spinner-grow text-'.$input[$rand_keys[0]].'" role="status">
  <span class="sr-only">Loading...</span>
</div>
<div class="spinner-grow text-'.$input[$rand_keys[1]].'" role="status">
  <span class="sr-only">Loading...</span>
</div>
<div class="spinner-grow text-'.$input[$rand_keys[2]].'" role="status">
  <span class="sr-only">Loading...</span>
</div>
<div class="spinner-grow text-'.$input[$rand_keys[3]].'" role="status">
  <span class="sr-only">Loading...</span>
</div>
<h4>'.__( 'Processing', 'doliconnect' ).'</h4>
</div>
  </div>
</div>';

}
add_action( 'wp_footer', 'doliconnect_loading' );

function dolibug(){
//header('Refresh: 180; URL='.esc_url(get_permalink()).'');
$bug = '<div id="dolibug" ><br><br><br><br><br><center><div class="align-middle"><i class="fas fa-bug fa-3x fa-fw"></i><h4>'.__( 'Oops, our servers are unreachable. Thank you for coming back in a few minutes.', 'doliconnect').'</h4>';
if ( ! empty(constant("DOLIBUG")) ) {
$bug .= '<h6>'.__( 'Error code', 'doliconnect').' #'.constant("DOLIBUG").'</h6>';
}
$bug .='</div></center><br><br><br><br><br></div>';
return $bug;
}

function Doliconnect_MailAlert( $user_login, $user ) {
global $wpdb;

if ( $user->loginmailalert == 'on' && $user->ID != defined("DOLICONNECT_DEMO") ) {
$sitename = get_option('blogname');
$siteurl = get_option('siteurl');
$subject = "[$sitename] ".__( 'Connection notification', 'doliconnect' );
$body = __( 'It appears that you have just logged on to our site from the following IP address:', 'doliconnect' )."<br /><br />".$_SERVER['REMOTE_ADDR']."<br /><br />".__( 'If you have not made this action, please change your password immediately.', 'doliconnect' )."<br /><br />".sprintf(__('Your %s\'s team', 'doliconnect'), $sitename)."<br />$siteurl";				
$headers = array('Content-Type: text/html; charset=UTF-8');
$mail =  wp_mail($user->user_email, $subject, $body, $headers);
}

}
add_action('wp_login', 'Doliconnect_MailAlert', 10, 2);

function dolidocdownload($type, $ref=null, $fichier=null, $url=null, $name=null, $refresh = false) {
global $wpdb;
$ID = get_current_user_id();
 
if ( $name == null ) { $name=$fichier; } 

if ( isset($_GET["download"]) && $_GET["securekey"] ==  hash('sha256', $ID.$type.$_GET["download"]) && $_GET["download"] == "$ref/$fichier" ) {

if ( !empty($refresh) ) {
$rdr = [
    'module_part'  => $type,
    'original_file' => $ref.'/'.$fichier
	];
$doc = callDoliApi("PUT", "/documents/builddoc", $rdr, 0);
} else {
$doc = callDoliApi("GET", "/documents/download?module_part=$type&original_file=$ref/$fichier", null, 0);
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

if ( isset($ref) && isset($fichier) ) { $document = "<a class='btn btn btn-outline-dark btn-sm btn-block' href='".esc_url( add_query_arg( array('download' => $ref."/".$fichier, 'securekey' => hash('sha256', $ID.$type.$ref."/".$fichier)), $url) )."' >$name <i class='fas fa-file-download'></i></a>"; }

return $document;
}

function dolihelp($type) {

$aide = callDoliApi("GET", "/doliconnector/constante/MAIN_MODULE_TICKET", null, dolidelay('constante'));

if ( is_object($aide) && is_user_logged_in() && $aide->value == 1 ) {
$arr_params = array( 'module' => 'tickets', 'type' => $type, 'create' => true); 
$link=esc_url( add_query_arg( $arr_params, doliconnecturl('doliaccount')) ); 
} elseif ( !empty(get_option('dolicontact')) ) {
$arr_params = array( 'type' => $postorder->id, 'create' => true); 
$link=esc_url( add_query_arg( $arr_params, doliconnecturl('dolicontact')) );
} else {
$link='#';
}

$help = "<a href='".$link."' role='button' title='".__( 'Help?', 'doliconnect' )."'><i class='fas fa-question-circle'></i> ".__( 'Need help?', 'doliconnect' )."</a>";

return $help;
}

function dolidelay($delay = null, $refresh = false, $protect = false) {

if (! is_numeric($delay)) {

if (false ===  get_site_option('doliconnect_delay_'.$delay) ) {

if ($delay == 'constante') { $delay = MONTH_IN_SECONDS; }
elseif ($delay == 'doliconnector') { $delay = HOUR_IN_SECONDS; }
elseif ($delay == 'source') { $delay = WEEK_IN_SECONDS; }
elseif ($delay == 'thirdparty') { $delay = DAY_IN_SECONDS; }
elseif ($delay == 'contact') { $delay = WEEK_IN_SECONDS; }
elseif ($delay == 'proposal') { $delay = HOUR_IN_SECONDS; }
elseif ($delay == 'order') { $delay = HOUR_IN_SECONDS; }
elseif ($delay == 'contract') { $delay = HOUR_IN_SECONDS; }
elseif ($delay == 'member') { $delay = DAY_IN_SECONDS; }
elseif ($delay == 'donation') { $delay = DAY_IN_SECONDS; }
elseif ($delay == 'ticket') { $delay = HOUR_IN_SECONDS; }
elseif ($delay == 'product') { $delay = DAY_IN_SECONDS; }
} else {
$delay = get_site_option('doliconnect_delay_'.$delay);
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
if ( isset($element->date_modification) && !empty($element->date_modification) ) {
$refresh = __( 'Updated', 'doliconnect' ).": ".date_i18n('d/m/Y - H:i', $element->date_modification, false);
} elseif ( get_option("_transient_timeout_".$origin) > 0 ) {
$refresh = __( 'Updated', 'doliconnect' ).": ".date_i18n('d/m/Y - H:i', get_option("_transient_timeout_".$origin)-$delay, false);
}
 
if (is_user_logged_in() ) {
$refresh .= " <a href='".esc_url( add_query_arg( 'refresh', true, $url) )."' title='".__( 'Refresh', 'doliconnect' )."'><i class='fas fa-sync-alt'></i></a>";
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

?>