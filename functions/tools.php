<?php
function doliconnectuserform($thirdparty, $delay, $mode) {
global $current_user;

$form = "<li class='list-group-item'><div class='form-group'><div class='row'>";
if ( $current_user->billing_type == 'mor' or (isset($_GET["pro"]) && !get_option('doliconnect_disablepro')) ) {
$form .= "<input type='hidden' name='billing_type' value='mor'><div class='col-12'><label for='coordonnees'><small>".__( 'Company', 'doliconnect' )."</small></label><input type='text' class='form-control' id='inputcompany' placeholder='".__( 'Company', 'doliconnect' )."' name='billing_company' value='".$current_user->billing_company."' required><div class='invalid-tooltip'>".__( 'This field is required.', 'doliconnect' )."</div></div>";
$form .= "</div></div></li><li class='list-group-item'><div class='form-group'><div class='row'>";
} else {
$form .= "<input type='hidden' name='billing_type' value='phy'>";
}
$form .= "<div class='col-12 col-md-12'><label for='inputcivility'><small>".__( 'Identity', 'doliconnect' )."</small></label>
<div class='input-group mb-2'><div class='input-group-prepend'><span class='input-group-text' id='identity'><i class='fas fa-user fa-fw'></i></span></div>";

$civility = CallAPI("GET", "/setup/dictionary/civility?sortfield=code&sortorder=ASC&limit=100", null, $delay);
if ( isset($civility->error) ) {
$civility = CallAPI("GET", "/setup/dictionary/civilities?sortfield=code&sortorder=ASC&limit=100&active=1", null, $delay); 
}

if ( isset($civility)  ) { 
$form .= "<select class='custom-select' id='identity'  name='billing_civility' required>";
foreach ($civility as $postv ) {

$form .= "<option value='".$postv->code."' ";
if ( $current_user->billing_civility == $postv->code && $current_user->billing_civility != null) {
$form .= "selected ";}
$form .= ">".$postv->label."</option>";
}
$form .= "</select>";
} else {
$form .= "<input type='text' class='form-control' id='identity' placeholder='".__( 'Civility', 'doliconnect' )."' name='billing_civility' value='".$current_user->billing_civility."' autocomplete='off' required>";
}

$form .= "<input type='text' name='user_firstname' class='form-control' placeholder='".__( 'Firstname', 'doliconnect' )."' value='".stripslashes(htmlspecialchars($current_user->user_firstname, ENT_QUOTES))."' required>
<input type='text' name='user_lastname' class='form-control' placeholder='".__( 'Lastname', 'doliconnect' )."' value='".stripslashes(htmlspecialchars($current_user->user_lastname, ENT_QUOTES))."' required>
<div class='invalid-tooltip'>".__( 'This field is required.', 'doliconnect' )."</div></div></div></div><div class='row'>";
$form .= "<div class='col-12'><label for='inputbirth'><small>".__( 'Birthday', 'doliconnect' )."</small></label><div class='input-group mb-2'><div class='input-group-prepend'><div class='input-group-text'><i class='fas fa-birthday-cake fa-fw'></i></div></div><input type='date' name='billing_birth' class='form-control' value='".$current_user->billing_birth."' id='inputbirth' placeholder='yyyy-mm-dd' autocomplete='off' required></div></div>";
$form .= "<div class='col-12 col-md-5'><label for='inputnickname'><small>".__( 'Nickname', 'doliconnect' )."</small></label><div class='input-group mb-2'><div class='input-group-prepend'><div class='input-group-text'><i class='fas fa-user-secret fa-fw'></i></div></div><input type='text' class='form-control' id='inputnickname' placeholder='".__( 'Nickname', 'doliconnect' )."' name='user_nicename' value='".stripslashes(htmlspecialchars($current_user->nickname, ENT_QUOTES))."' autocomplete='off' required></div></div>";
$form .= "<div class='col-12 col-md-7'><label for='inputemail'><small>".__( 'Email', 'doliconnect' )."</small></label><div class='input-group mb-2'><div class='input-group-prepend'><div class='input-group-text'><i class='fas fa-at fa-fw'></i></div></div><input type='email' class='form-control' id='inputemail' placeholder='email@example.com' name='user_email' value='".$current_user->user_email."' autocomplete='off' ";
if ( 'DOLICONNECT_DEMO' == $current_user->ID && is_user_logged_in() ) {
$form .= " readonly";
} else {
$form .= " required";
}
$form .= "></div></div>";
$form .= "</div></div>";
$form .= "</li>";
if ( ( isset($_GET["pro"]) && !get_option('doliconnect_disablepro') ) || $mode == 'full' ) {
$form .= "<li class='list-group-item'><div class='form-group'><div class='row'>";
$form .= "<div class='col-12'><label for='inputaddress'><small>".__( 'Address', 'doliconnect' )."</small></label>
<div class='input-group mb-2'><div class='input-group-prepend'><span class='input-group-text'><i class='fas fa-map-marked fa-fw'></i></span></div>
<textarea id='inlineFormInputGroup'  name='billing_address' class='form-control' rows='3' placeholder='".__( 'Address', 'doliconnect' )."' required>".$thirdparty->address."</textarea></div></div>";
$form .= "<div class='col-12'><label for='inputzipcode'><small>".__( 'Zipcode', 'doliconnect' )." / ".__( 'Town', 'doliconnect' )." / ".__( 'Country', 'doliconnect' )."</small></label>
<div class='input-group mb-2'><div class='input-group-prepend'><span class='input-group-text' id='address'><i class='fas fa-map-marked fa-fw'></i></span></div>
<input type='text' class='form-control' id='inputzipcode' placeholder='".__( 'Zipcode', 'doliconnect' )."' name='billing_zipcode' value='".$thirdparty->zip."' autocomplete='off' required>
<input type='text' class='form-control' id='inputcity' placeholder='".__( 'Town', 'doliconnect' )."' name='billing_city' value='".$thirdparty->town."' autocomplete='off' required>";

$pays = CallAPI("GET", "/setup/dictionary/countries?sortfield=favorite%2Clabel&sortorder=DESC%2CASC&limit=500", null , $delay);

if ( isset($pays) ) { 
$form .= "<select class='custom-select' id='inputcountry'  name='billing_country' required>";
foreach ( $pays as $postv ) {
$form .= "<option value='".$postv->id."' ";
if ( $thirdparty->country_id == $postv->id && $thirdparty->country_id != null ) {
$form .= "selected ";}
 if ( $postv->id == '0' ) {$form .= "disabled ";}
$pays=$postv->label;
$form .= ">$pays</option>";
}
$form .= "</select>";
} else {
$form .= "<input type='text' class='form-control' id='inputcountry' placeholder='".__( 'Country', 'doliconnect' )."' name='billing_country' value='".$thirdparty->country_id."' autocomplete='off' required>";
}
$form .= "</div></div>";
$form .= "<div class='col-12'><label for='inputmobile'><small>".__( 'Phone', 'doliconnect' )."</small></label><div class='input-group mb-2'><div class='input-group-prepend'><div class='input-group-text'><i class='fas fa-phone fa-fw'></i></div></div><input type='tel' class='form-control' id='inputmobile' placeholder='".__( 'Phone', 'doliconnect' )."' name='billing_phone' value='".$thirdparty->phone."' autocomplete='off'></div></div>";
$form .= "</div></div></li>";
}
return $form;
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
$bug = '<div id="dolibug" ><br><br><br><br><br><center><div class="align-middle"><i class="fas fa-bug fa-3x fa-fw"></i><h4>'.__( 'Oops, our servers are unreachable. Thank you for coming back in a few minutes.', 'doliconnect' ).'</h4><h6>'.__( 'Error code', 'doliconnect' ).' #'.constant("DOLIBUG").'</h6></div></center><br><br><br><br><br></div>';
return $bug;
}

function Doliconnect_MailAlert( $user_login, $user ) {
global $wpdb;

if ( $user->loginmailalert == 'on' && $user->ID != DOLICONNECT_DEMO ) {
$sitename = get_option('blogname');
$siteurl = get_option('siteurl');
$subject = "[$sitename] ".__( 'Connection notification', 'doliconnect' );
$body = __( 'It appears that you have just logged on to our site from the following IP address:', 'doliconnect' )."<br /><br />".$_SERVER['REMOTE_ADDR']."<br /><br />".__( 'If you have not made this action, please change your password immediately.', 'doliconnect' )."<br /><br />".sprintf(__('Your %s\'s team', 'doliconnect'), $sitename)."<br />$siteurl";				
$headers = array('Content-Type: text/html; charset=UTF-8');
$mail =  wp_mail($user->user_email, $subject, $body, $headers);
}

}
add_action('wp_login', 'Doliconnect_MailAlert', 10, 2);

function dolidocdownload($type, $ref=null, $fichier=null, $url=null, $name=null) {
global $wpdb;
$ID = get_current_user_id();
 
if ( $name == null ) { $name=$fichier; } 

if ( isset($_GET["download"]) && $_GET["securekey"] ==  hash('sha256', $ID.$type.$_GET["download"]) && $_GET["download"] == "$ref/$fichier" ) {

$doc = CallAPI("GET", "/documents/download?module_part=$type&original_file=$ref/$fichier", null, 0);

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

if ( isset($ref) && isset($fichier) ) { $document = "<a class='btn btn-outline-secondary btn-block' href='".esc_url( add_query_arg( array('download' => $ref."/".$fichier, 'securekey' => hash('sha256', $ID.$type.$ref."/".$fichier)), $url) )."' >$name <i class='fas fa-file-pdf' aria-hidden='true'></i></a>"; }

return $document;
}

function dolihelp($type) {

$aide = CallAPI("GET", "/doliconnector/constante/MAIN_MODULE_TICKET", null, MONTH_IN_SECONDS);

if ( is_object($aide) && is_user_logged_in() && $aide->value == 1 ) {
$arr_params = array( 'module' => 'ticket', 'type' => $type, 'create' => true); 
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

function dolidelay($delay, $refresh = false, $protect = false) {
$array = get_option('doliconnect_ipkiosk');
if ( is_array($array) && in_array($_SERVER['REMOTE_ADDR'], $array) ) {
$delay=0;
}
if ( $refresh && is_user_logged_in() ) {
$delay=$delay*-1;
}

return $delay;
}

function dolirefresh( $origin, $url, $delay) {

if ( get_option("_transient_timeout_".$origin) > 0 && is_user_logged_in() ) {

$refresh = __( 'Updated', 'doliconnect' ).": ".date_i18n('d/m/Y - H:i', get_option("_transient_timeout_".$origin)-$delay, false)." <a href='".esc_url( add_query_arg( 'refresh', true, $url) )."'><i class='fas fa-sync-alt'></i></a>";
} else {
$refresh = __( 'Updated', 'doliconnect' ).": ".date_i18n('d/m/Y - H:i', false, true);
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