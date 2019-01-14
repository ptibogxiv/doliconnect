<?php

if ( !defined("DOLIBUG") ) {
$propal = CallAPI("GET", "/doliconnector/constante/MAIN_MODULE_PROPALE", null, MONTH_IN_SECONDS);
$order = CallAPI("GET", "/doliconnector/constante/MAIN_MODULE_COMMANDE", null, MONTH_IN_SECONDS);
$contract = CallAPI("GET", "/doliconnector/constante/MAIN_MODULE_CONTRAT", null, MONTH_IN_SECONDS);
$member = CallAPI("GET", "/doliconnector/constante/MAIN_MODULE_ADHERENTSPLUS", null, MONTH_IN_SECONDS);
$memberconsumption = CallAPI("GET", "/doliconnector/constante/ADHERENT_CONSUMPTION", null, MONTH_IN_SECONDS);
$rewards = CallAPI("GET", "/doliconnector/constante/MAIN_MODULE_REWARDS", null, MONTH_IN_SECONDS);
$assiduity = CallAPI("GET", "/doliconnector/constante/MAIN_MODULE_ASSIDUITY", null, MONTH_IN_SECONDS);
$help = CallAPI("GET", "/doliconnector/constante/MAIN_MODULE_TICKET", null, MONTH_IN_SECONDS);
}

function informations_menu($arg){
echo "<A href='".esc_url( add_query_arg( 'module', 'informations', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ($arg=='informations') { echo " active";}
echo "'>".__( 'Personal informations', 'doliconnect' )."</a>";
}
add_action( 'user_doliconnect_menu', 'informations_menu', 1, 1);

function informations_module($url){
global $wpdb,$current_user,$doliconnect;
$ID = $current_user->ID;
$delay = DAY_IN_SECONDS;

if ( $_POST["case"] == 'updateuser'  ) {
wp_update_user( array( 'ID' => $ID, 'user_email' => sanitize_email($_POST['user_email'])));
wp_update_user( array( 'ID' => $ID, 'nickname' => sanitize_user($_POST['user_nicename'])));
wp_update_user( array( 'ID' => $ID, 'display_name' => ucfirst(strtolower($_POST['user_firstname']))." ".strtoupper($_POST['user_lastname'])));
wp_update_user( array( 'ID' => $ID, 'first_name' => ucfirst(sanitize_user(strtolower($_POST['user_firstname'])))));
wp_update_user( array( 'ID' => $ID, 'last_name' => strtoupper(sanitize_user($_POST['user_lastname']))));
wp_update_user( array( 'ID' => $ID, 'description' => sanitize_textarea_field($_POST['description'])));
update_usermeta( $ID, 'billing_civility', sanitize_text_field($_POST['billing_civility']));
update_usermeta( $ID, 'billing_type', sanitize_text_field($_POST['billing_type']));
update_usermeta( $ID, 'billing_company', sanitize_text_field($_POST['billing_company']));
update_usermeta( $ID, 'billing_address', sanitize_textarea_field($_POST['billing_address']));
update_usermeta( $ID, 'billing_zipcode', sanitize_text_field($_POST['billing_zipcode']));
update_usermeta( $ID, 'billing_city', sanitize_text_field($_POST['billing_city']));
update_usermeta( $ID, 'billing_country', sanitize_text_field($_POST['billing_country'] ));
update_usermeta( $ID, 'billing_phone', sanitize_text_field($_POST['billing_phone'])); 
update_usermeta( $ID, 'billing_birth', $_POST['billing_birth']);
if ($_POST['array_options']) {
foreach ($_POST['array_options'] as $label => $value)
{
update_usermeta( $ID, $wpdb->prefix.'doliextra_'.$label, $value);
}
}
do_action('wp_dolibarr_sync',constant("DOLIBARR"));

if ( isset($_GET['return']) ) {
wp_redirect(doliconnecturl('doliaccount').'?module='.$_GET['return']);
exit;
} else {
$msg .= "<div class='alert alert-success'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button><p><strong>".__( 'Congratulations!', 'doliconnect' )."</strong> ".__( 'Your informations have been updated.', 'doliconnect' )."</p></div>";
}
}

if ( isset($_GET['return']) ) {
$url = esc_url( add_query_arg( 'return', $_GET['return'], $url) );
}

if ( constant("DOLIBARR") > '0' ) {
$thirdparty = CallAPI("GET", "/thirdparties/".constant("DOLIBARR"), null, dolidelay( $delay, esc_attr($_GET["refresh"])));  
}

echo "<form action='".$url."' id='informations-form' method='post' class='was-validated' enctype='multipart/form-data'><input type='hidden' name='case' value='updateuser'>";
echo $msg;
echo "<script>";
?>

window.setTimeout(function () {
    $(".alert-success").fadeTo(500, 0).slideUp(500, function () {
        $(this).remove();
    });
}, 5000);

var form = document.getElementById('informations-form');
form.addEventListener('submit', function(event) {

jQuery('#DoliconnectLoadingModal').modal('show');
jQuery(window).scrollTop(0); 
console.log("submit");
form.submit();

});

<?php
echo "</script><div class='card shadow-sm'><ul class='list-group list-group-flush'>";

echo doliconnectuserform($thirdparty, dolidelay(MONTH_IN_SECONDS, esc_attr($_GET["refresh"]), true), 'full');

if(has_action('mydoliconnectuserform')) {
echo "<li class='list-group-item'>";
do_action( 'mydoliconnectuserform');
echo "</li>";
}

echo "<li class='list-group-item'>";
echo "<div class='form-group'>
<label for='description'><small>".__( 'About Yourself', 'doliconnect' )."</small></label><div class='input-group mb-2'><div class='input-group-prepend'><span class='input-group-text'><i class='fas fa-bullhorn fa-fw'></i></span></div>
<textarea type='text' class='form-control' name='description' id='description' rows='3'>".$current_user->description."</textarea></div></div>";
echo "<div class='form-group'>
<label for='description'><small>".__( 'Website', 'doliconnect' )."</small></label><div class='input-group mb-2'><div class='input-group-prepend'><span class='input-group-text'><i class='fas fa-link fa-fw'></i></span></div>
<input type='url' class='form-control' name='website' id='website' placeholder='".__( 'Website', 'doliconnect' )."' value='".stripslashes(htmlspecialchars($current_user->user_url, ENT_QUOTES))."'></div></div>";
echo "<div class='form-group'><div class='row'>
<div class='col-12 col-md-4'><label for='inlineFormInputGroup'><small>Facebook</small></label>
<div class='input-group mb-2'><div class='input-group-prepend'><div class='input-group-text'><i class='fab fa-facebook-f fa-fw'></i></div></div>
<input type='text' name='facebook' class='form-control' id='inlineFormInputGroup' placeholder='".__( 'Username', 'doliconnect' )."' value='".stripslashes(htmlspecialchars($thirdparty->facebook, ENT_QUOTES))."'></div></div>
<div class='col-12 col-md-4'><label for='inlineFormInputGroup'><small>Twitter</small></label>
<div class='input-group mb-2'><div class='input-group-prepend'><div class='input-group-text'><i class='fab fa-twitter fa-fw'></i></div></div>
<input type='text' name='twitter' class='form-control' id='inlineFormInputGroup' placeholder='".__( 'Username', 'doliconnect' )."' value='".stripslashes(htmlspecialchars($thirdparty->twitter, ENT_QUOTES))."'></div></div>
<div class='col-12 col-md-4'><label for='inlineFormInputGroup'><small>Skype</small></label>
<div class='input-group mb-2'><div class='input-group-prepend'><div class='input-group-text'><i class='fab fa-skype fa-fw'></i></div></div>
<input type='text' name='linkedin' class='form-control' id='inlineFormInputGroup' placeholder='".__( 'Username', 'doliconnect' )."' value='".stripslashes(htmlspecialchars($thirdparty->skype, ENT_QUOTES))."'></div></div>
</div>
</div>";
echo "</li>";
echo "</ul><div class='card-body'><input type='hidden' name='userid' value='$ID'><button class='btn btn-danger btn-block' type='submit'><b>".__( 'Update', 'doliconnect' )."</b></button></div>";

echo "</div>";

echo "<small><div class='float-left'>";
echo dolirefresh("/thirdparties/".constant("DOLIBARR"),$url,$delay);
echo "</div><div class='float-right'>";
echo dolihelp('ISSUE');
echo "</div></small>";

echo "</form>";
}
add_action( 'user_doliconnect_informations', 'informations_module');

function avatars_module($url){
global $wpdb,$current_user;

$ID = $current_user->ID;
$time = current_time( 'timestamp', 1);

require_once ABSPATH . WPINC . '/class-phpass.php';

if ($_POST["case"] == 'updateavatar') {

if ($_POST['inputavatar']=='delete'){

$upload_dir = wp_upload_dir();
$nam=$wpdb->prefix."member_photo";

$files = glob($upload_dir['basedir']."/doliconnect/".$ID."/*");
foreach($files as $file){
if(is_file($file))
unlink($file); 
}

delete_usermeta( $ID, $nam,$current_user->$nam);

if (constant("DOLIBARR_MEMBER")>0){
$data = [
    'photo' => ''
	];
$adherent = CallAPI("PUT", "/adherentsplus/".constant("DOLIBARR_MEMBER"), $data, DAY_IN_SECONDS);
}

} elseif ($_FILES['inputavatar']['tmp_name']!=NULL) {
$types = array('image/jpeg', 'image/jpg');
if ($_FILES['inputavatar']['tmp_name']!=NULL){
list($width, $height) = getimagesize($_FILES['inputavatar']['tmp_name']);
}
if (($width >= '350' && $height >= '350') && (isset($_FILES['inputavatar']['tmp_name'])) && (in_array($_FILES['inputavatar']['type'], $types)) && ($_FILES['inputavatar']['size'] <= 10000000)) {

$upload_dir = wp_upload_dir();
$nam=$wpdb->prefix."member_photo";

if (file_exists($upload_dir['basedir']."/doliconnect/".$ID."/".$current_user->$nam)){
$files = glob($upload_dir['basedir']."/doliconnect/".$ID."/*");
foreach($files as $file){
if(is_file($file))
unlink($file); 
}}

if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
$uploadedfile = $_FILES['inputavatar'];
   
add_filter('wp_handle_upload_prefilter', 'custom_upload_filter' );
function custom_upload_filter( $file ){

    $file['name'] = "avatar.jpg";
    return $file;
}

function dolipropal_upload_dir($fileup) {
	$fileup['subdir']		= '/doliconnect/'.$_POST["userid"];
	$fileup['path']		= $fileup['basedir'] . $fileup['subdir'];
	$fileup['url']		= $fileup['baseurl'] . $fileup['subdir'];
return $fileup;
}
 
$upload_overrides = array( 'test_form' => false );
add_filter('upload_dir', 'dolipropal_upload_dir');
$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
remove_filter('upload_dir', 'dolipropal_upload_dir');

$filename=$upload_dir['basedir']."/doliconnect/".$ID."/avatar.jpg";
$img = wp_get_image_editor($filename);
 
if ( ! is_wp_error( $img )) {
$exif = exif_read_data($filename);               
if ($exif[Orientation] == '8') {
$img->rotate( 90 );
} elseif ($exif[Orientation] == '3') {
$img->rotate( 180 );
} elseif ($exif[Orientation] == '6') {
$img->rotate( -90 );
} 

$img->resize( 350, 350, true );
$avatar = $img->generate_filename($time,$upload_dir['basedir']."/doliconnect/".$ID."/", NULL );
$img->save($avatar);
update_usermeta( $_POST["userid"], $wpdb->prefix."member_photo","avatar-$time.jpg");
$filename2=$upload_dir['basedir']."/doliconnect/".$ID."/avatar-$time.jpg";
$img = wp_get_image_editor($filename2);
$img->resize( 72, 72, true );
$avatar1 = $img->generate_filename('72x72',$upload_dir['basedir']."/doliconnect/".$ID."/", NULL );
$img->save($avatar1);
$img = wp_get_image_editor($filename2);
$img->resize( 150, 150, true );
$avatar2 = $img->generate_filename('150x150',$upload_dir['basedir']."/doliconnect/".$ID."/", NULL );
$img->save($avatar2);
if (file_exists($filename)){
unlink($filename);
}
}

$minifile=$upload_dir['basedir']."/doliconnect/".$ID."/avatar-$time-72x72.jpg";
$smallfile=$upload_dir['basedir']."/doliconnect/".$ID."/avatar-$time-150x150.jpg";
$avatarfile=$upload_dir['basedir']."/doliconnect/".$ID."/avatar-$time.jpg";

if (file_exists($avatarfile)) {
$imgData = base64_encode(file_get_contents("$avatarfile"));
$datat = [
  'filename' => 'avatar.jpg',
  'modulepart' => 'member',
  'ref' => constant("DOLIBARR_MEMBER"),
  'subdir' => 'photos',
  'filecontent' => $imgData,
  'fileencoding' => 'base64',
  'overwriteifexists'=> 1
	];
$photo = CallAPI("POST", "/documents/upload", $datat, 0);
}
if (file_exists($minifile)) {
$imgData = base64_encode(file_get_contents("$minifile"));
$datat = [
  'filename' => 'avatar_mini.jpg',
  'modulepart' => 'member',
  'subdir' => constant("DOLIBARR_MEMBER").'/photos/thumbs',
  'filecontent' => $imgData,
  'fileencoding' => 'base64',
  'overwriteifexists'=> 1
	];
$photo = CallAPI("POST", "/documents/upload", $datat, 0);
}
if (file_exists($smallfile)) {
$imgData = base64_encode(file_get_contents("$smallfile"));
$datat = [
  'filename' => 'avatar_small.jpg',
  'modulepart' => 'member',
  'subdir' => constant("DOLIBARR_MEMBER").'/photos/thumbs',
  'filecontent' => $imgData,
  'fileencoding' => 'base64',
  'overwriteifexists'=> 1
	];
$photo = CallAPI("POST", "/documents/upload", $datat, 0);
}

 
if (constant("DOLIBARR_MEMBER")>0){
$data = [
    'photo' => 'avatar.jpg'
	];
$adherent = CallAPI("PUT", "/adherentsplus/".constant("DOLIBARR_MEMBER"), $data, DAY_IN_SECONDS);
}

} else {
$msg .= "<div class='alert alert-warning'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button><p><strong>".__( 'Oops', 'doliconnect' )."</strong> Votre photo n'a pu être chargée. Elle doit obligatoirement être au format .jpg et faire moins de 10 Mo. Taille minimum requise 350x350 pixels.</p></div>";
}
}

$msg .= "<div class='alert alert-success'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button><p><strong>".__( 'Congratulations!', 'doliconnect' )."</strong> ".__( 'Your informations have been updated.', 'doliconnect' )."</p></div>";   
}

echo "<form action='".$url."' id='avatar-form' method='post' class='was-validated' enctype='multipart/form-data'><input type='hidden' name='case' value='updateavatar'>";
echo $msg;
echo "<script>";
?>

var form = document.getElementById('avatar-form');
form.addEventListener('submit', function(event) {

jQuery('#DoliconnectLoadingModal').modal('show');
jQuery(window).scrollTop(0);  
console.log("submit");
form.submit();

});

<?php
echo "</script><div class='card shadow-sm'><ul class='list-group list-group-flush'>";
echo "<li class='list-group-item'>";
echo "<label for='description'><small>".__( 'Profile Picture', 'doliconnect' )."</small></label><div class='form-group'>
<div class='input-group mb-2'><div class='input-group-prepend'><span class='input-group-text'><i class='fas fa-camera fa-fw'></i></span></div><div class='custom-file'>
<input type='file' name='inputavatar' class='custom-file-input' id='customFile' accept='image/*' ";
$table_prefix = $wpdb->get_blog_prefix( $entity ); 
$upload_dir = wp_upload_dir();
$nam=$table_prefix."member_photo";
if (NULL == $current_user->$nam && constant("DOLIBARR_MEMBER")) {
//echo " required='required'";
}
echo " capture><label class='custom-file-label' for='customFile' data-browse='".__( 'Browse', 'doliconnect' )."'>".__( 'Select a file', 'doliconnect' )."</label></div></div>
<small id='infoavatar' class='form-text text-muted text-justify'>".__( 'Your avatar must be a .jpg/.jpeg file, <10Mo and 350x350pixels minimum.', 'doliconnect' )."</SMALL>";
echo "<div class='custom-control custom-checkbox my-1 mr-sm-2'>
    <input type='checkbox' class='custom-control-input' id='inputavatar' name='inputavatar' value='delete' ";
if (NULL == $current_user->$nam) {
echo " disabled='disabled'";
}
echo "><label class='custom-control-label' for='inputavatar'>".__( 'Delete your picture', 'doliconnect' )."</label></div></div>";
echo "</li>";
echo "</ul><div class='card-body'><input type='hidden' name='userid' value='$ID'><button class='btn btn-danger btn-block' type='submit'><b>".__( 'Update', 'doliconnect' )."</b></button></div></div>";
echo "<p class='text-right'><small>";
echo dolihelp('ISSUE');
echo "</small></p>";
echo "</form>";
}
add_action( 'user_doliconnect_avatars', 'avatars_module');

function contacts_menu($arg){
echo "<a href='".esc_url( add_query_arg( 'module', 'contacts', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ($arg=='contacts') { echo " active";}
echo "'>".__( 'Address book', 'doliconnect' )."</a>";
}
add_action( 'user_doliconnect_menu', 'contacts_menu', 2, 1);

function contacts_module($url){
global $current_user;
$delay = WEEK_IN_SECONDS;

if ($_POST['contact'] == 'new_contact'){

$data = [
    'firstname' => ucfirst(sanitize_user(strtolower($_POST['contact_firstname']))),
    'lastname' => strtoupper(sanitize_user($_POST['contact_lastname'])),
    'socid' => constant("DOLIBARR"),
    'poste' => sanitize_textarea_field($_POST['contact_poste']), 
    'address' => sanitize_textarea_field($_POST['contact_address']),    
    'zip' => sanitize_text_field($_POST['contact_zip']),
    'town' => sanitize_text_field($_POST['contact_town']),
    'country_id' => sanitize_text_field($_POST['contact_country_id']),
    'email' => sanitize_email($_POST['contact_email']),
    'phone_pro' => sanitize_text_field($_POST['contact_phone'])
	];
$contact = CallAPI("POST", "/contacts", $data, 0);
$listcontact = CallAPI("GET", "/contacts?sortfield=t.rowid&sortorder=ASC&limit=100&thirdparty_ids=".constant("DOLIBARR"), null, dolidelay($delay, true));

} elseif ($_POST['contact'] > 0 ) {

$delete = CallAPI("DELETE", "/contacts/".$_POST['contact'], null, HOUR_IN_SECONDS);
$listcontact = CallAPI("GET", "/contacts?sortfield=t.rowid&sortorder=ASC&limit=100&thirdparty_ids=".constant("DOLIBARR"), null, dolidelay($delay, true));

} else {

$listcontact = CallAPI("GET", "/contacts?sortfield=t.rowid&sortorder=ASC&limit=100&thirdparty_ids=".constant("DOLIBARR"), null, dolidelay($delay, esc_attr($_GET["refresh"])));

}

if ( constant("DOLIBARR") > 0 ) {
$thirdparty = CallAPI("GET", "/thirdparties/".constant("DOLIBARR"), null, dolidelay( $delay, esc_attr($_GET["refresh"])));  
}

echo "<form role='form' action='$url' id='contact-form' method='post' novalidate>";

echo "<script>";
?>

window.setTimeout(function() {
    $(".alert").fadeTo(500, 0).slideUp(500, function(){
        $(this).remove(); 
    });
}, 5000);

var form = document.getElementById('contact-form');

function ShowHideDiv() {
var ContactForm = document.getElementById("ContactForm");
if (ContactForm){
if (new_contact.checked){
document.getElementById("SaveFormButton").style.display = "block";
document.getElementById("dvDelete").style.display = "none";
document.getElementById("ContactAddForm").style.display = "block";
} else {
document.getElementById("SaveFormButton").style.display = "none";
document.getElementById("dvDelete").style.display = "block";
document.getElementById("ContactAddForm").style.display = "none";
}
}
}

window.onload=ShowHideDiv; 

form.addEventListener('submit', function(event) {

jQuery('#DoliconnectLoadingModal').modal('show');
jQuery(window).scrollTop(0);
console.log("submit");
form.submit();

});

document.getElementById("SaveFormButton").style.display = "block";

<?php
echo "</script>";

echo "<div class='card shadow-sm'><ul class='list-group list-group-flush'>";

if ( !isset($listcontact->error) && $listcontact != null ) {
$idcontact=1;
foreach ($listcontact as $contact) {
echo "<li class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='$contact->id' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='contact' value='$contact->id' ";
if ( $idcontact=='1' ) { echo " checked "; }
echo " ><label class='custom-control-label w-100' for='$contact->id'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
echo "<center><i class='fas fa-address-card fa-3x fa-fw'></i></center>";
echo "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>$contact->civility_code $contact->firstname $contact->lastname";
if ( $contact->poste!=null ) {echo " / $contact->poste";}
echo "</h6><small class='text-muted'>$contact->address<br>$contact->zip $contact->town - $contact->country<br>$contact->email $contact->phone_pro</small>";
echo '</div></div></label></div></li>';
$idcontact++;
}}
if ( count($listcontact) < 5 ) {
echo "<li id='ContactForm' class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='new_contact' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='contact' value='new_contact' ";
if ( isset($listcontact->error) || $listcontact==null ) { echo " checked "; }
echo " ><label class='custom-control-label w-100' for='new_contact'><div class='row'>";
echo "<div class='col-3 col-md-2 col-xl-2 align-middle'>";
echo "<center><i class='far fa-address-card fa-3x fa-fw'></i></center>";
echo "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Add a new contact/address', 'doliconnect' )."</h6><small class='text-muted'>".__( 'Alternatives contacts for order, billing, shipping, newsletter...', 'doliconnect' )."</small>";
echo '</div></div></label></div></li>';
echo '<li class="list-group-item list-group-item-secondary" id="ContactAddForm" style="display: none">';

echo "<div class='row'>";
echo "<div class='col-12'><label for='inputnickname'><small>".__( 'Title/Job', 'doliconnect' )."</small></label><div class='input-group mb-2'><div class='input-group-prepend'><div class='input-group-text'><i class='fas fa-user-secret fa-fw'></i></div></div><input type='text' class='form-control' id='inputnickname' placeholder='".__( 'Title/Job', 'doliconnect' )."' name='contact_poste' value='' autocomplete='off' required><div class='invalid-feedback'>".__( 'This field is required.', 'doliconnect' )."</div></div></div>";

echo "<div class='col-12 col-md-12'><label for='inputcivility'><small>".__( 'Identity', 'doliconnect' )."</small></label>
<div class='input-group mb-2'><div class='input-group-prepend'><span class='input-group-text' id='identity'><i class='fas fa-user fa-fw'></i></span></div>";

$civility = CallAPI("GET", "/setup/dictionary/civility?sortfield=code&sortorder=ASC&limit=100", null , MONTH_IN_SECONDS);
if (isset($civility)) { 
echo "<select class='custom-select' id='identity'  name='billing_civility' required>";
foreach ($civility as $postv) {

echo "<option value='".$postv->code."' ";
if ($current_user->billing_civility == $postv->code && $current_user->billing_civility!=NULL) {
echo "selected ";}
if ($postv->id=='0'){$form .= "disabled ";}
echo ">$postv->label</option>";
}
echo "</select>";
} else {
echo "<input type='text' class='form-control' id='identity' placeholder='".__( 'Civility', 'doliconnect' )."' name='billing_civility' value='".$current_user->billing_civility."' autocomplete='off' required>";
}

echo "<input type='text' name='contact_firstname' class='form-control' placeholder='".__( 'Firstname', 'doliconnect' )."' value='".stripslashes(htmlspecialchars($current_user->user_firstname, ENT_QUOTES))."' required>
<input type='text' name='contact_lastname' class='form-control' placeholder='".__( 'Lastname', 'doliconnect' )."' value='".stripslashes(htmlspecialchars($current_user->user_lastname, ENT_QUOTES))."' required>
</div></div></div><div class='row'>";
//echo "<div class='col-12'><label for='inputbirth'><small>".__( 'Birthday', 'doliconnect' )."</small></label><div class='input-group mb-2'><div class='input-group-prepend'><div class='input-group-text'><i class='fas fa-birthday-cake fa-fw'></i></div></div><input type='date' name='billing_birth' class='form-control' value='' id='inputbirth' placeholder='yyyy-mm-dd' autocomplete='off' required><div class='invalid-feedback'>".__( 'This field is required.', 'doliconnect' )."</div></div></div>";
echo "<div class='col-12 col-md-5'><label for='inputphone'><small>".__( 'Phone', 'doliconnect' )."</small></label><div class='input-group mb-2'><div class='input-group-prepend'><div class='input-group-text'><i class='fas fa-phone fa-fw'></i></div></div><input type='text' class='form-control' id='inputphone' placeholder='".__( 'Phone', 'doliconnect' )."' name='contact_phone' value='".$thirdparty->phone."' autocomplete='off' required><div class='invalid-feedback'>".__( 'This field is required.', 'doliconnect' )."</div></div></div>";
echo "<div class='col-12 col-md-7'><label for='inputemail'><small>".__( 'Email', 'doliconnect' )."</small></label><div class='input-group mb-2'><div class='input-group-prepend'><div class='input-group-text'><i class='fas fa-at fa-fw'></i></div></div><input type='email' class='form-control' id='inputemail' placeholder='email@example.com' name='contact_email' value='".$current_user->user_email."' autocomplete='off' required><div class='invalid-feedback'>".__( 'This field is required.', 'doliconnect' )."</div></div></div>";
echo "</div>";

echo "<div class='form-group'><div class='row'>";
echo "<div class='col-12'><label for='inputaddress'><small>".__( 'Address', 'doliconnect' )."</small></label>
<div class='input-group mb-2'><div class='input-group-prepend'><span class='input-group-text'><i class='fas fa-map-marked fa-fw'></i></span></div>
<textarea id='inlineFormInputGroup'  name='contact_address' class='form-control' rows='3' placeholder='".__( 'Address', 'doliconnect' )."' required>".$thirdparty->address."</textarea></div></div>";
echo "<div class='col-12'><label for='inputzipcode'><small>".__( 'Zipcode', 'doliconnect' )." / ".__( 'Town', 'doliconnect' )." / ".__( 'Country', 'doliconnect' )."</small></label>
<div class='input-group mb-2'><div class='input-group-prepend'><span class='input-group-text' id='address'><i class='fas fa-map-marked fa-fw'></i></span></div>
<input type='text' class='form-control' id='inputzipcode' placeholder='".__( 'Zipcode', 'doliconnect' )."' name='contact_zip' value='".$thirdparty->zip."' autocomplete='off' required>
<input type='text' class='form-control' id='inputcity' placeholder='".__( 'Town', 'doliconnect' )."' name='contact_town' value='".stripslashes(htmlspecialchars($thirdparty->town, ENT_QUOTES))."' autocomplete='off' required>";

$pays = CallAPI("GET", "/setup/dictionary/countries?sortfield=favorite%2Clabel&sortorder=DESC%2CASC&limit=500", null, MONTH_IN_SECONDS);

if ( isset($pays) ) { 
echo "<select class='custom-select' id='inputcountry'  name='contact_country_id' required>";
foreach ($pays as $postv) {
echo "<option value='".$postv->id."' ";
if ( $current_user->billing_country == $postv->id && $thirdparty->country_id != null ) {
echo "selected ";}
 if ($postv->id=='0'){$form .= "disabled ";}
$pays=$postv->label;
echo ">$pays</option>";
}
echo "</select>";
} else {
echo "<input type='text' class='form-control' id='inputcountry' placeholder='".__( 'Country', 'doliconnect' )."' name='billing_country' value='".$thirdparty->country."' autocomplete='off' required>";
}
echo "</div></div>";

echo '</li>';
}
echo "</ul><div class='card-body'>";
if ( $listcontact != null ) {
echo "<div id='dvDelete'><button class='btn btn-danger btn-block' type='submit'><b>".__( 'Delete', 'doliconnect' )."</b></button></div>";
} 
echo "<div id='SaveFormButton' style='display: none'><input type='hidden' name='source' value='validation'><input type='hidden' name='cart' value='validation'><input type='hidden' name='info' value='validation'><button class='btn btn-warning btn-block' type='submit'><b>".__( 'Add contact', 'doliconnect' )."</b></button></div>";
echo "</div></div>";

echo "<small><div class='float-left'>";
echo dolirefresh("/contacts?sortfield=t.rowid&sortorder=ASC&limit=100&thirdparty_ids=".constant("DOLIBARR"),$url,$delay);
echo "</div><div class='float-right'>";
echo dolihelp('ISSUE');
echo "</div></small>";

echo "</form>";
echo doliloading('contact');
}
add_action( 'user_doliconnect_contacts', 'contacts_module' );

function password_menu( $arg ){
echo "<a href='".esc_url( add_query_arg( 'module', 'password', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ($arg=='password') { echo " active";}
echo "'>".__( 'Password', 'doliconnect' )."</a>";
}
add_action( 'user_doliconnect_menu', 'password_menu', 3, 1);

function password_module( $url ){
global $wp_hasher,$current_user;
$ID = $current_user->ID;

$msg = null;

if ($_POST["case"] == 'updatepwd'){
$pwd = sanitize_text_field($_POST["pwd1"]);
$pwd0 = sanitize_text_field($_POST["pwd0"]);
$pwd2 = sanitize_text_field($_POST["pwd2"]);
$wp_hasher = new PasswordHash(8, TRUE);
$password_hashed = $current_user->user_pass ;
$plain_password = $pwd0;
if ( ($wp_hasher->CheckPassword($plain_password, $password_hashed)) && ($pwd == $pwd2) && (preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{8,20}/', $pwd)) ) {
$hash = md5($pwd);
//wp_set_password($pwd, $ID);
$update_user = wp_update_user( array (
					'ID' => $ID, 
					'user_pass' => $pwd
				)
			);
if (constant("DOLIBARR_USER") > '0'){
$data = [
    'pass' => $pwd
	];
$doliuser = CallAPI("PUT", "/users/".constant("DOLIBARR_USER"), $data, 0);
}

$msg = "<div class='alert alert-success'><h4 class='alert-heading'>".__( 'Congratulations!', 'doliconnect' )."</h4><p>".__( 'Your password has been changed', 'doliconnect' )."</p></div>";
}
elseif ( !$wp_hasher->CheckPassword($plain_password, $password_hashed) ) {
$msg = "<div class='alert alert-danger'><h4 class='alert-heading'>".__( 'Oops!', 'doliconnect' )."</h4><p>".__( 'Your actual password is incorrect', 'doliconnect' )."</p></div>";
}
elseif ($pwd != $_POST["pwd2"]){
$msg = "<div class='alert alert-danger'><h4 class='alert-heading'>".__( 'Oops!', 'doliconnect' )."</h4><p>".__( 'The new passwords entered are different', 'doliconnect' )."</p></div>";
}
elseif (!preg_match("#.*^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#", $pwd)){
$msg = "<div class='alert alert-danger'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button><span class='fa fa-times-circle'></span> Votre nouveau mot de passe doit comporter entre 8 et 20 caractères dont au moins 1 chiffre, 1 lettre, 1 majuscule et 1 symbole.</div>";
}
}

echo "<form class='was-validated' action='".$url."' id='payment-form' method='post'><input type='hidden' name='case' value='updatepwd'>";

echo $msg;

echo "<script>";
?>

window.setTimeout(function () {
$(".alert-success").fadeTo(500, 0).slideUp(500, function () {
        $(this).remove();
    });
}, 5000);

var form = document.getElementById('payment-form');
form.addEventListener('submit', function(event) {

jQuery('#DoliconnectLoadingModal').modal('show');
jQuery(window).scrollTop(0);  
console.log("submit");
form.submit();

});

<?php
echo "</script><div class='card shadow-sm'><ul class='list-group list-group-flush'>";
if (constant("DOLIBARR_USER") > '0') {
echo "<li class='list-group-item list-group-item-info'><i class='fas fa-info-circle'></i> <b>".__( 'Your password will be synchronized with your Dolibarr account', 'doliconnect' )."</b></li>";
} elseif  ( DOLICONNECT_DEMO == $ID ) {
echo "<li class='list-group-item list-group-item-info'><i class='fas fa-info-circle'></i> <b>".__( 'Password cannot be modified in demo mode', 'doliconnect' )."</b></li>";
} 
echo '<li class="list-group-item"><div class="form-group"><div class="row"><div class="col-12"><label for="passwordHelpBlock1"><small>'.__( 'Confirm your current password', 'doliconnect' ).'</small></label>
<div class="input-group mb-2"><div class="input-group-prepend"><div class="input-group-text"><i class="fas fa-key fa-fw"></i></div></div><input type="password" id="pwd0" name="pwd0" class="form-control" aria-describedby="passwordHelpBlock1" autocomplete="off" placeholder="'.__( 'Confirm your current password', 'doliconnect' ).'" ';
if ( DOLICONNECT_DEMO == $ID ) {
echo ' readonly';
} else {
echo ' required';
}
echo '></div></div></div></div><div class="form-group"><div class="row"><div class="col-12"><label for="passwordHelpBlock2"><small>'.__( 'Change your password', 'doliconnect' ).'</small></label>
<div class="input-group mb-2"><div class="input-group-prepend"><div class="input-group-text"><i class="fas fa-key fa-fw"></i></div></div><input type="password" id="pwd1" name="pwd1" class="form-control" aria-describedby="passwordHelpBlock2" autocomplete="off" placeholder="'.__( 'Choose your new password', 'doliconnect' ).'" ';
if ( DOLICONNECT_DEMO == $ID ) {
echo ' readonly';
} else {
echo ' required';
}
echo '></div><small id="passwordHelpBlock3" class="form-text text-justify text-muted">
'.__( 'Your password must be between 8 and 20 characters, including at least 1 digit, 1 letter, 1 uppercase.', 'doliconnect' ).'
</small><div class="invalid-feedback">'.__( 'This field is required.', 'doliconnect' ).'</div></div></div><div class="row"><div class="col-12"><label for="passwordHelpBlock3"></label>';
echo '<div class="input-group mb-2"><div class="input-group-prepend"><div class="input-group-text"><i class="fas fa-key fa-fw"></i></div></div><input type="password" id="pwd2" name="pwd2"  class="form-control" aria-describedby="passwordHelpBlock3" autocomplete="off" placeholder="'.__( 'Confirme your new password', 'doliconnect' ).'" ';
if ( DOLICONNECT_DEMO == $ID ) {
echo ' readonly';
} else {
echo ' required';
}
echo '></div></div></div></li>';
echo "</ul><div class='card-body'><button class='btn btn-danger btn-block' type='submit' ";
if ( DOLICONNECT_DEMO == $ID ) {
echo ' disabled';
}
echo "><b>".__( 'Update', 'doliconnect' )."</b></button></div></div>";
echo "<p class='text-right'><small>";
echo dolihelp('ISSUE');
echo "</small></p>";
echo "</form>";
echo doliloading('password');
}
add_action( 'user_doliconnect_password', 'password_module');
//*****************************************************************************************
if ( is_object($propal) && $propal->value == 1 ) {
add_action( 'compta_doliconnect_menu', 'propal_menu', 1, 1);
add_action( 'compta_doliconnect_propal', 'propal_module' );
}

function propal_menu( $arg ) {
echo "<a href='".esc_url( add_query_arg( 'module', 'propal', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ($arg=='propal') { echo " active";}
echo "'>".__( 'Propals tracking', 'doliconnect' )."</a>";
}

function propal_module( $url ) {
$delay = HOUR_IN_SECONDS;

if ($_GET['id'] > 0){
$propalfo = CallAPI("GET", "/proposals/".$_GET['id'], null, dolidelay($delay, esc_attr($_GET["refresh"])));
//echo $propalfo;
}

if ( ( $_GET['id'] != null ) && ( $_GET['ref'] != null ) && ( constant("DOLIBARR") == $propalfo->socid ) && ( $_GET['ref'] == $propalfo->ref ) && $propalfo->statut !=0 ) {
echo "<div class='card shadow-sm'><div class='card-body'><h5 class='card-title'>$propalfo->ref</h5><div class='row'><div class='col-md-5'>";
$datecreation =  date_i18n('d/m/Y', $propalfo->date_creation);
$datevalidation =  date_i18n('d/m/Y', $propalfo->date_validation);
$datevalidite =  date_i18n('d/m/Y', $propalfo->fin_validite);
echo "<b>".__( 'Date of creation', 'doliconnect' ).":</b> $datecreation<br>";
//echo "<b>".__( 'Validation', 'doliconnect' )." : </b> $datevalidation<br>";
echo "<b>Date de fin de validité:</b> $datevalidite";
//echo "<b>".__( 'Status', 'doliconnect' )." : </b> ";
if ( $propalfo->statut == 3 ) { $propalinfo=__( 'Refused', 'doliconnect' );
$propalavancement=0; }
elseif ( $propalfo->statut == 2 ) { $propalinfo=__( 'Processing', 'doliconnect' );
$propalavancement=65; }
elseif ( $propalfo->statut == 1 ) { $propalinfo=__( 'Sign before', 'doliconnect' )." ".$datevalidite;
$propalavancement=42; }
elseif ( $propalfo->statut == 0 ) { $propalinfo=__( 'Processing', 'doliconnect' );
$propalavancement=22; }
elseif ( $propalfo->statut == -1 ) { $propalinfo=__( 'Canceled', 'doliconnect' );
$propalavancement=0; }
echo "<br><br>";
//echo "<b>Moyen de paiement : </b> $propalfo[mode_reglement]<br>";
echo "</div><div class='col-md-7'>";

if ( isset($propalinfo) ) {
echo "<h3 class='text-right'>".$propalinfo."</h3>";
}

$TTC = number_format($propalfo->multicurrency_total_ttc, 2, ',', ' ');
$currency = strtolower($propalfo->multicurrency_code);
echo "</div></div>";
echo '<div class="progress"><div class="progress-bar bg-success" role="progressbar" style="width: '.$propalavancement.'%" aria-valuenow="'.$propalavancement.'" aria-valuemin="0" aria-valuemax="100"></div></div>';
echo "<div class='w-auto text-muted d-none d-sm-block' ><div style='display:inline-block;width:16%'>".__( 'Propal', 'doliconnect' )."</div><div style='display:inline-block;width:21%'>".__( 'Processing', 'doliconnect' )."</div><div style='display:inline-block;width:19%'>".__( 'Validation', 'doliconnect' )."</div><div style='display:inline-block;width:24%'>".__( 'Processing', 'doliconnect' )."</div><div class='text-right' style='display:inline-block;width:20%'>".__( 'Billing', 'doliconnect' )."</div></div>";
echo "</div><ul class='list-group list-group-flush'>";
 
if ( $propalfo->lines != null ) {
foreach ( $propalfo->lines as $line ) {
echo "<li class='list-group-item'>";     
if ( $line->date_start != '' && $line->date_end !='' )
{
$start = date_i18n('d/m/Y', $line->date_start);
$end = date_i18n('d/m/Y', $line->date_end);
$dates =" <i>(Du $start au $end)</i>";
}

echo '<div class="w-100 justify-content-between"><div class="row"><div class="col-8 col-md-10"> 
<h6 class="mb-1">'.$line->libelle.'</h6>
<p class="mb-1">'.$line->desc.'</p>
<small>'.$dates.'</small>'; 
echo '</div><div class="col-4 col-md-2 text-right"><h5 class="mb-1">'.doliprice($line->multicurrency_total_ttc?$line->multicurrency_total_ttc:$line->total_ttc,$propalfo->multicurrency_code).'</h5>';
echo '<h5 class="mb-1">x'.$line->qty.'</h5>'; 
echo "</div></div></li>";
}
}

echo "<li class='list-group-item list-group-item-info'>";
echo "<b>".__( 'Total excl. tax', 'doliconnect').": ".doliprice($propalfo->multicurrency_total_ht?$propalfo->multicurrency_total_ht:$propalfo->total_ht,$propalfo->multicurrency_code)."</b><br />";
echo "<b>".__( 'Total tax', 'doliconnect').": ".doliprice($propalfo->multicurrency_total_tva?$propalfo->multicurrency_total_tva:$propalfo->total_tva,$propalfo->multicurrency_code)."</b><br />";
echo "<b>".__( 'Total incl. tax', 'doliconnect').": ".doliprice($propalfo->multicurrency_total_ttc?$propalfo->multicurrency_total_ttc:$propalfo->total_ttc,$propalfo->multicurrency_code)."</b>";
echo "</li>";

if ( $propalfo->last_main_doc != null ) {
$doc = array_reverse( explode("/", $propalfo->last_main_doc) );      
$document = dolidocdownload($doc[2],$doc[1],$doc[0],$url."&id=".$_GET['id']."&ref=".$propalfo->ref,__( 'Summary', 'doliconnect' ));
} 
    
$fruits[$propal->date_creation.p] = array(
"timestamp" => $propalfo->date_creation,
"type" => __( 'Propal', 'doliconnect' ),  
"label" => $propalfo->ref,
"document" => $document,
);

sort($fruits, SORT_NUMERIC | SORT_FLAG_CASE);
foreach ( $fruits as $key => $val ) {
echo "<li class='list-group-item'><div class='row'><div class='col-6 col-md-3'>" . date_i18n('d/m/Y H:i', $val[timestamp]) . "</div><div class='col-6 col-md-2'>" . $val[type] . "</div>";
echo "<div class='col-md-7'><h5>" . $val[label] . "</h5>" . $val[description] ."" . $val[document] ."</div></div></li>";
} 
//var_dump($fruits);
echo "</ul></div>";

echo "<small><div class='float-left'>";
echo dolirefresh("/proposals/".$_GET['id'],$url."&id=".$_GET['id']."&ref=".$_GET['ref'],$delay);
echo "</div><div class='float-right'>";
echo dolihelp('COM');
echo "</div></small>";

} else {

$delay = DAY_IN_SECONDS;

if ( $_GET['pg'] ) { $page="&page=".$_GET['pg']; }

$listpropal = CallAPI("GET", "/proposals?sortfield=t.rowid&sortorder=ASC&limit=8&thirdparty_ids=".constant("DOLIBARR")."&sqlfilters=(t.fk_statut!=0)", null, dolidelay($delay, esc_attr($_GET["refresh"])));

echo '<div class="card shadow-sm"><ul class="list-group list-group-flush">';  
if ( !isset( $listpropal->error ) && $listpropal != null ) {
foreach ( $listpropal as $postpropal ) { 

$arr_params = array( 'id' => $postpropal->id, 'ref' => $postpropal->ref);  
$return = esc_url( add_query_arg( $arr_params, $url) );
                
echo "<a href='$return' class='list-group-item d-flex justify-content-between lh-condensed list-group-item-action'><div><i class='fa fa-shopping-bag fa-3x fa-fw'></i></div><div><h6 class='my-0'>$postpropal->ref</h6><small class='text-muted'>du ".date_i18n('d/m/Y', $postpropal->date_creation)."</small></div><span>".doliprice($postpropal->multicurrency_total_ttc?$postpropal->multicurrency_total_ttc:$postpropal->total_ttc,$postpropal->multicurrency_code)."</span><span>";
if ( $postpropal->statut == 3 ) {
if ( $postpropal->billed == 1 ) { echo "<span class='fa fa-check-circle fa-fw text-success'></span><span class='fa fa-eur fa-fw text-success'></span><span class='fa fa-truck fa-fw text-success'></span><span class='fa fa-file-text fa-fw text-success'></span>"; } 
else { echo "<span class='fa fa-check-circle fa-fw text-success'></span><span class='fa fa-eur fa-fw text-success'></span><span class='fa fa-truck fa-fw text-success'></span><span class='fa fa-file-text fa-fw text-warning'></span>"; } }
elseif ( $postpropal->statut == 2 ) { echo "<span class='fa fa-check-circle fa-fw text-success'></span><span class='fa fa-eur fa-fw text-success'></span><span class='fa fa-truck fa-fw text-warning'></span><span class='fa fa-file-text fa-fw text-danger'></span>"; }
elseif ( $postpropal->statut == 1 ) { echo "<span class='fa fa-check-circle fa-fw text-success'></span><span class='fa fa-eur fa-fw text-warning'></span><span class='fa fa-truck fa-fw text-danger'></span><span class='fa fa-file-text fa-fw text-danger'></span>"; }
elseif ( $postpropal->statut == 0 ) { echo "<span class='fa fa-check-circle fa-fw text-warning'></span><span class='fa fa-eur fa-fw text-danger'></span><span class='fa fa-truck fa-fw text-danger'></span><span class='fa fa-file-text fa-fw text-danger'></span>"; }
elseif ( $postpropal->statut == -1 ) { echo "<span class='fa fa-check-circle fa-fw text-secondary'></span><span class='fa fa-eur fa-fw text-secondary'></span><span class='fa fa-truck fa-fw text-secondary'></span><span class='fa fa-file-text fa-fw text-secondary'></span>"; }
echo "</span></a>";
}}
else{
echo "<li class='list-group-item list-group-item-light'><center>".__( 'No propal', 'doliconnect' )."</center></li>";
}
echo  "</ul></div>";

echo "<small><div class='float-left'>";
echo dolirefresh("/proposals?sortfield=t.rowid&sortorder=ASC&limit=8&thirdparty_ids=".constant("DOLIBARR")."&sqlfilters=(t.fk_statut!=0)", $url, $delay);
echo "</div><div class='float-right'>";
echo dolihelp('COM');
echo "</div></small>";
}
}

if ( is_object($order) && $order->value == 1 ) {
add_action( 'compta_doliconnect_menu', 'order_menu', 2, 1);
add_action( 'compta_doliconnect_order', 'order_module' );
}

function order_menu( $arg ) {
echo "<a href='".esc_url( add_query_arg( 'module', 'order', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ($arg == 'order') { echo " active";}
echo "'>".__( 'Orders tracking', 'doliconnect' )."</a>";
}

function order_module( $url ) {
$delay = HOUR_IN_SECONDS;

if ( $_GET['id']>0 ) {
$orderfo = CallAPI("GET", "/orders/".$_GET['id'], null, dolidelay($delay, esc_attr($_GET["refresh"])));
//echo $orderfo;
}

if ( ( $_GET['id'] != null ) && ( $_GET['ref'] != null ) && (constant("DOLIBARR") == $orderfo->socid ) && ($_GET['ref'] == $orderfo->ref) && $orderfo->statut != 0 ) {
echo "<div class='card shadow-sm'><div class='card-body'><h5 class='card-title'>$orderfo->ref</h5><div class='row'><div class='col-md-5'>";
$datecommande =  date_i18n('d/m/Y', $orderfo->date_creation);
echo "<b>".__( 'Date of order', 'doliconnect' ).": </b> $datecommande<br>";
if ( $orderfo->statut > 0 ) {
if ( $orderfo->billed == 1 ) {
if ( $orderfo->statut >1 ) { $orderinfo=__( 'Shipped', 'doliconnect' ); 
$orderavancement=100; }
else { $orderinfo=__( 'Processing', 'doliconnect' );
$orderavancement=40; }
}
else { $orderinfo=null;
$orderinfo=null;
$orderavancement=25;
}
}
elseif ( $orderfo->statut == 0 ) { $orderinfo=__( 'Validation', 'doliconnect' );
$orderavancement=7; }
elseif ( $orderfo->statut == -1 ) { $orderinfo=__( 'Canceled', 'doliconnect' );
$orderavancement=0;  }

echo "<b>".__( 'Payment method', 'doliconnect' ).":</b> $orderfo->mode_reglement<br><br></div><div class='col-md-7'>";

if ( isset($orderinfo) ) {
echo "<h3 class='text-right'>".$orderinfo."</h3>";
}

$ref="$orderfo->ref";
if ( $orderfo->billed != 1 && $orderfo->statut > 0 && function_exists('dolipaymentmodes') ) {
$change = "<small><a href='#' id='button-source-payment' data-toggle='modal' data-target='#orderonlinepay'><span class='fa fa-credit-card'></span> ".__( 'Change your payment mode', 'doliconnect' )."</a></small>";
if ( $orderfo->mode_reglement_code == 'CHQ' ) {
$chq = CallAPI("GET", "/doliconnector/constante/FACTURE_CHQ_NUMBER", null, dolidelay(MONTH_IN_SECONDS, esc_attr($_GET["refresh"])));

$bank = CallAPI("GET", "/bankaccounts/".$chq->value, null, dolidelay(MONTH_IN_SECONDS, esc_attr($_GET["refresh"])));

echo "<div class='alert alert-danger' role='alert'><p align='justify'>Merci d'envoyer un chèque d'un montant de <b>".doliprice($orderfo->multicurrency_total_ttc?$orderfo->multicurrency_total_ttc:$orderfo->total_ttc,$orderfo->multicurrency_code)."</b> libellé à l'ordre de <b>$bank->proprio</b> sous <b>15 jours</b> en rappelant votre référence <b>$ref</b> à l'adresse suivante :</p><p><b>$bank->owner_address</b></p>$change</div>";
} elseif ( $orderfo->mode_reglement_code == 'VIR' ) { 
$vir = CallAPI("GET", "/doliconnector/constante/FACTURE_RIB_NUMBER", null, dolidelay(MONTH_IN_SECONDS, esc_attr($_GET["refresh"])));

$bank = CallAPI("GET", "/bankaccounts/".$vir->value, null, dolidelay(MONTH_IN_SECONDS, esc_attr($_GET["refresh"])));

echo "<div class='alert alert-danger' role='alert'><p align='justify'>Merci d'effectuer un virement d'un montant de <b>".doliprice($orderfo->multicurrency_total_ttc?$orderfo->multicurrency_total_ttc:$orderfo->total_ttc,$orderfo->multicurrency_code)."</b> sous <b>15 jours</b> en rappelant votre référence <b>$ref</b> sur le compte suivant :</p><p><b>IBAN : $bank->iban</b>";
if ( ! empty($bank->bic) ) { echo "<br><b>SWIFT/BIC : $bank->bic</b>";}
echo "</p>$change</div>";
} else {
//echo "token:".$_POST['token']." /stripesource:".$_POST['stripeSource']." /modepayment:".$_POST['modepayment'];
if ( isset($_POST['token']) || $_POST['modepayment']=='src_newcard' || $_POST['modepayment']=='src_newbank' ) {
if ( isset($_POST['token']) ) {
$source=$_POST['token'];
} else {
$source=$_POST['stripeSource'];
}

if ( $_POST['savethesource']=='ok' ) {

$src = [
'token' => $_POST['stripeSource'],
'default' => $_POST['setasdefault']
];

$addsource = CallAPI("POST", "/doliconnector/".constant("DOLIBARR")."/sources", $src, 0);
}

}
else{
$source=$_POST['modepayment'];
}
//echo "<br/>sourcefinal:".$source;
if ( $source && ($_POST['modepayment'] !='src_vir' && $_POST['modepayment'] != 'src_chq') ) {
$successurl=doliconnecturl('dolicart')."?validation&order=".$_GET['id'];
$src = [
    'source' => "".$source."",
    'url' => "".$successurl.""
	];
$pay = CallAPI("POST", "/doliconnector/".constant("DOLIBARR")."/pay/order/".$_GET['id'], $src, 0);
//echo $pay;
 
if ($pay["statut"]=='error'){
echo "<center>erreur de paiement<br>$pay->message</center><br >";
} else {
header('Location: '.$pay->redirect_url);
exit;
}
}

echo "<button type='button' id='button-source-payment' class='btn btn-warning btn-block' data-toggle='modal' data-target='#orderonlinepay'><span class='fa fa-credit-card'></span> ".__( 'Pay', 'doliconnect' )."</button><br>";
}

echo "<div class='modal fade' id='orderonlinepay' tabindex='-1' role='dialog' aria-labelledby='orderonlinepayLabel' aria-hidden='true'  aria-hidden='true' data-backdrop='static' data-keyboard='false'>
<div class='modal-dialog modal-dialog-centered' role='document'><div class='modal-content'><div class='modal-header border-0'><h4 class='modal-title border-0' id='orderonlinepayLabel'>".__( 'Payment methods', 'doliconnect' )."</h4>
<button id='closemodalonlinepay' type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div><div class='modal-body'>";

if ( !empty($orderfo->paymentintent) ) {
dolipaymentmodes($orderfo, $url, $url, dolidelay($delay, esc_attr($_GET["refresh"])));
} else {
doligateway($orderfo->ref,$orderfo->multicurrency_total_ttc?$orderfo->multicurrency_total_ttc:$orderfo->total_ttc,$orderfo->multicurrency_code,$url.'&id='.$_GET['id'].'&ref='.$_GET['ref'],'full');
echo doliloading('paymentmodes'); }
 
echo "</div></div></div></div>";
 
}
echo "</div></div>";
echo '<div class="progress"><div class="progress-bar bg-success" role="progressbar" style="width: '.$orderavancement.'%" aria-valuenow="'.$orderavancement.'" aria-valuemin="0" aria-valuemax="100"></div></div>';
echo "<div class='w-auto text-muted d-none d-sm-block' ><div style='display:inline-block;width:20%'>".__( 'Order', 'doliconnect' )."</div><div style='display:inline-block;width:15%'>".__( 'Payment', 'doliconnect' )."</div><div style='display:inline-block;width:25%'>".__( 'Processing', 'doliconnect' )."</div><div style='display:inline-block;width:20%'>".__( 'Shipping', 'doliconnect' )."</div><div class='text-right' style='display:inline-block;width:20%'>".__( 'Delivery', 'doliconnect' )."</div></div>";

echo "</div><ul class='list-group list-group-flush'>";
 
if ( $orderfo->lines != null) {
foreach ( $orderfo->lines as $line ) {
echo "<li class='list-group-item'>";     
if ( $line->date_start != '' && $line->date_end != '' )
{
$start = date_i18n('d/m/Y', $line->date_start);
$end = date_i18n('d/m/Y', $line->date_end);
$dates =" <i>(Du $start au $end)</i>";
}

echo '<div class="w-100 justify-content-between"><div class="row"><div class="col-8 col-md-10"> 
<h6 class="mb-1">'.$line->libelle.'</h6>
<p class="mb-1">'.$line->description.'</p>
<small>'.$dates.'</small>'; 
echo '</div><div class="col-4 col-md-2 text-right"><h5 class="mb-1">'.doliprice($line->multicurrency_total_ttc?$line->multicurrency_total_ttc:$line->total_ttc,$orderfo->multicurrency_code).'</h5>';
echo '<h5 class="mb-1">x'.$line->qty.'</h5>'; 
echo "</div></div></li>";
}
}

echo "<li class='list-group-item list-group-item-info'>";
echo "<b>".__( 'Total excl. tax', 'doliconnect').": ".doliprice($orderfo->multicurrency_total_ht?$orderfo->multicurrency_total_ht:$orderfo->total_ht,$orderfo->multicurrency_code)."</b><br />";
echo "<b>".__( 'Total tax', 'doliconnect').": ".doliprice($orderfo->multicurrency_total_tva?$orderfo->multicurrency_total_tva:$orderfo->total_tva,$orderfo->multicurrency_code)."</b><br />";
echo "<b>".__( 'Total incl. tax', 'doliconnect').": ".doliprice($orderfo->multicurrency_total_ttc?$orderfo->multicurrency_total_ttc:$orderfo->total_ttc,$orderfo->multicurrency_code)."</b>";
echo "</li>";

if ( $orderfo->last_main_doc != null ) {
$doc = array_reverse(explode("/", $orderfo->last_main_doc)); 
$document = dolidocdownload($doc[2],$doc[1],$doc[0],$url."&id=".$_GET['id']."&ref=".$orderfo->ref,__( 'Summary', 'doliconnect' ));
} 
    
$fruits[$orderfo->date_commande.o] = array(
"timestamp" => $orderfo->date_creation,
"type" => __( 'Order', 'doliconnect' ),  
"label" => $orderfo->ref,
"document" => $document,
);

$fac=$orderfo->linkedObjectsIds->facture;
if ( $fac != null ) {
foreach ($fac as $f => $value) {

if ($value > 0) {
$invoice = CallAPI("GET", "/invoices/".$value, null, dolidelay($delay, esc_attr($_GET["refresh"])));
//echo $invoice;
$payment = CallAPI("GET", "/invoices/".$value."/payments", null, dolidelay($delay, esc_attr($_GET["refresh"])));
//echo $payment;
}

if ( $payment != null ) { 
foreach ( $payment as $pay ) {
$fruits[strtotime($pay->date).p] = array(
"timestamp" => strtotime($pay->date),
"type" => __( 'Payment', 'doliconnect' ),  
"label" => "$pay->type de ".doliprice($pay->amount,$orderfo->multicurrency_code),
"description" => $pay->num,
); 
}
}

if ( $invoice->last_main_doc != null ) {
$doc = array_reverse(explode("/", $invoice->last_main_doc)); 
$document=dolidocdownload($doc[2],$doc[1],$doc[0],$url."&id=".$_GET['id']."&ref=".$orderfo->ref,__( 'Invoice', 'doliconnect' ));
}  
  
$fruits[$invoice->date_creation.i] = array(
"timestamp" => $invoice->date_creation,
"type" => __( 'Invoice', 'doliconnect' ),  
"label" => $invoice->ref,
"document" => $document,
);  
} 
} 
 
$shipments=$orderfo->linkedObjectsIds->shipping;
if ( $shipments != null ) {
foreach ( $shipments as $s => $value ) {

if ($value > 0) {
$ship = CallAPI("GET", "/shipments/".$value, null, dolidelay($delay, esc_attr($_GET["refresh"])));
//echo $invoice;
}

$lnship ="<ul>";
foreach ( $ship->lines as $slinee ) {
$lnship .="<li>".$sline->qty_shipped."x ".$sline->libelle."</li>";
}
$lnship .="</ul>";
if ( $ship->trueWeight != null ) {
$poids=" ".__( 'of', 'doliconnect' )." ".$ship->trueWeight."kg";
} else {$poids='';}
if ( $ship->trueSize !=null ) {
$dimensions=" - ".__( 'size', 'doliconnect' )." ".$ship->trueSize."m";
} else  {$dimensions=''; }
//$doc = array_reverse(explode("/", $ship['last_main_doc']));      
//dolidocdownload($doc[2],$doc[1],$doc[0],$url."&id=".$_GET['id']."&ref=".$orderfo['ref'],__( 'Shipment', 'doliconnect' ));
if ( $ship->statut > 0 ) {
$fruits[$ship->date_creation] = array(
"timestamp" => $ship->date_creation,
"type" => __( 'Shipment', 'doliconnect' ),  
"label" => $ship->ref." ".$ship->tracking_url,
"description" => "<small>".$lnship.__( 'Parcel', 'doliconnect' )." ".$ship->shipping_method.$poids.$dimensions."</small>",
);
} else {
$fruits[$ship->date_creation] = array(
"timestamp" => $ship->date_creation,
"type" => __( 'Shipment', 'doliconnect' ),  
"label" => __( 'Packaging in progress', 'doliconnect' ),
"description" => null,
);
}
 } 
 }

sort($fruits, SORT_NUMERIC | SORT_FLAG_CASE);
foreach ( $fruits as $key => $val ) {
echo "<li class='list-group-item'><div class='row'><div class='col-6 col-md-3'>" . date_i18n('d/m/Y H:i', $val[timestamp]) . "</div><div class='col-6 col-md-2'>" . $val[type] . "</div>";
echo "<div class='col-md-7'><h5>" . $val[label] . "</h5>" . $val[description] ."" . $val[document] ."</div></div></li>";
} 
//var_dump($fruits);
echo "</ul></div>";

echo "<small><div class='float-left'>";
echo dolirefresh("/orders/".$_GET['id'],$url."&id=".$_GET['id']."&ref=".$_GET['ref'],$delay);
echo "</div><div class='float-right'>";
echo dolihelp('COM');
echo "</div></small>";

} else {

$delay = DAY_IN_SECONDS;

if ($_GET['pg'] > 0) { $page="&page=".$_GET['pg'];}

$listorder = CallAPI("GET", "/orders?sortfield=t.rowid&sortorder=DESC&limit=8".$page."&thirdparty_ids=".constant("DOLIBARR")."&sqlfilters=(t.fk_statut!=0)", null, dolidelay($delay, esc_attr($_GET["refresh"])));

echo '<div class="card shadow-sm"><ul class="list-group list-group-flush">';
if ( !isset($listorder->error ) && $listorder != null ) {
foreach ( $listorder as $postorder ) {

$arr_params = array( 'id' => $postorder->id, 'ref' => $postorder->ref);  
$return = esc_url( add_query_arg( $arr_params, $url) );
                                                                                                                                                      
echo "<a href='$return' class='list-group-item d-flex justify-content-between lh-condensed list-group-item-action'><div><i class='fa fa-shopping-bag fa-3x fa-fw'></i></div><div><h6 class='my-0'>$postorder->ref</h6><small class='text-muted'>du ".date_i18n('d/m/Y', $postorder->date_commande)."</small></div><span>".doliprice($postorder->multicurrency_total_ttc?$postorder->multicurrency_total_ttc:$postorder->total_ttc,$postorder->multicurrency_code)."</span><span>";
if ( $postorder->statut > 0 ) { echo "<span class='fas fa-check-circle fa-fw text-success'></span> ";
if ( $postorder->billed == 1 ) { echo "<span class='fas fa-money-bill-alt fa-fw text-success'></span> "; 
if ( $postorder->statut > 1 ) { echo "<span class='fas fa-shipping-fast fa-fw text-success'></span> "; }
else { echo "<span class='fas fa-shipping-fast fa-fw text-warning'></span> "; }
}
else { echo "<span class='fas fa-money-bill-alt fa-fw text-warning'></span> "; 
if ( $postorder->statut > 1 ) { echo "<span class='fas fa-shipping-fast fa-fw text-success'></span> "; }
else { echo "<span class='fas fa-shipping-fast fa-fw text-danger'></span> "; }
}}
elseif ( $postorder->statut == 0 ) { echo "<span class='fas fa-check-circle fa-fw text-warning'></span> <span class='fas fa-money-bill-alt fa-fw text-danger'></span> <span class='fas fa-shipping-fast fa-fw text-danger'></span>"; }
elseif ( $postorder->statut == -1 ) { echo "<span class='fas fa-check-circle fa-fw text-secondary'></span> <span class='fas fa-money-bill-alt fa-fw text-secondary'></span> <span class='fas fa-shipping-fast fa-fw text-secondary'></span>"; }
echo "</span></a>";
}}
else{
echo "<li class='list-group-item list-group-item-light'><center>".__( 'No order', 'doliconnect' )."</center></li>";
}
echo  "</ul></div>";

echo "<small><div class='float-left'>";
echo dolirefresh("/orders?sortfield=t.rowid&sortorder=DESC&limit=8".$page."&thirdparty_ids=".constant("DOLIBARR")."&sqlfilters=(t.fk_statut!=0)",$url,$delay);
echo "</div><div class='float-right'>";
echo dolihelp('COM');
echo "</div></small>";

//echo '<br /><nav aria-label="Page navigation example">
//  <ul class="pagination">
//  <li class="page-item disabled">
//      <a class="page-link" href="#" aria-label="Previous">
//        <span aria-hidden="true">&laquo;</span>
//        <span class="sr-only">Previous</span>
//     </a>
//  </li>
//    <li class="page-item"><a class="page-link" href="'.$url.'&pg=1">1</a></li>
//    <li class="page-item"><a class="page-link" href="'.$url.'&pg=2">3</a></li>
//    <li class="page-item"><a class="page-link" href="'.$url.'&pg=3">3</a></li>    
//  <li class="page-item disabled">
//      <a class="page-link" href="#" aria-label="Next">
//        <span aria-hidden="true">&raquo;</span>
//        <span class="sr-only">Next</span>
//      </a>
//  </li>
//  </ul>
//</nav>';
}
}

if ( is_object($contract) && $contract->value == 1 && get_option('doliconnectbeta') =='1' ) {
add_action( 'compta_doliconnect_menu', 'contract_menu', 2, 1);
add_action( 'compta_doliconnect_contract', 'contract_module' );
}

function contract_menu( $arg ) {
echo "<a href='".esc_url( add_query_arg( 'module', 'contract', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ($arg=='contract') { echo " active";}
echo "'>".__( 'Contracts tracking', 'doliconnect' )."</a>";
}

function contract_module( $url ) {
$delay = HOUR_IN_SECONDS;

if ( $_GET['id'] > 0 ) {
$contractfo = CallAPI("GET", "/contracts/".$_GET['id'], null, dolidelay($delay, esc_attr($_GET["refresh"])));
//echo $contractfo;
}

if ( ($_GET['id'] != null ) && ( $_GET['ref'] != null ) && (constant("DOLIBARR") == $contractfo->socid) && ($_GET['ref'] == $contractfo->ref) ) {
echo "<div class='card shadow-sm'><div class='card-body'><h5 class='card-title'>$contractfo->ref</h5><div class='row'><div class='col-md-5'>";
$datecontract =  date_i18n('d/m/Y', $contractfo->date_creation);
echo "<b>".__( 'Date of creation', 'doliconnect' ).": </b> ".date_i18n('d/m/Y', $contractfo->date_creation)."<br>";
if ( $orderfo->statut > 0 ) {
if ( $orderfo->billed == 1 ) {
if ( $orderfo->statut > 1 ) { $orderinfo=__( 'Shipped', 'doliconnect' ); 
$orderavancement=100; }
else { $orderinfo=__( 'Processing', 'doliconnect' );
$contractavancement=40; }
}
else { $contractinfo=null;
$contractinfo=null;
$contractavancement=25;
}
}
elseif ( $contractfo->statut == 0 ) { $contractinfo=__( 'Validation', 'doliconnect' );
$contractavancement=7; }
elseif ( $contractfo->statut == -1) { $contractinfo=__( 'Canceled', 'doliconnect' );
$contractavancement=0; }

echo "</div></div>";
echo '<div class="progress"><div class="progress-bar bg-success" role="progressbar" style="width: '.$contractavancement.'%" aria-valuenow="'.$contractavancement.'" aria-valuemin="0" aria-valuemax="100"></div></div>';
echo "<div class='w-auto text-muted d-none d-sm-block' ><div style='display:inline-block;width:20%'>".__( 'Order', 'doliconnect' )."</div><div style='display:inline-block;width:15%'>".__( 'Payment', 'doliconnect' )."</div><div style='display:inline-block;width:25%'>".__( 'Processing', 'doliconnect' )."</div><div style='display:inline-block;width:20%'>".__( 'Shipping', 'doliconnect' )."</div><div class='text-right' style='display:inline-block;width:20%'>".__( 'Delivery', 'doliconnect' )."</div></div>";

echo "</div><ul class='list-group list-group-flush'>";

if ( $contractfo->lines != null ) {
foreach ($contractfo->lines as $line) {
echo "<li class='list-group-item'>";     
if ( $line->date_start != '' && $line->date_end != '' )
{
$start = date_i18n('d/m/Y', $line->date_start);
$end = date_i18n('d/m/Y', $line->date_end);
$dates =" <i>(Du $start au $end)</i>";
}

echo '<div class="w-100 justify-content-between"><div class="row"><div class="col-8 col-md-10"> 
<h6 class="mb-1">'.$line->product_label.'</h6>
<p class="mb-1">'.$line->description.'</p>
<small>'.$dates.'</small>'; 
echo '</div><div class="col-4 col-md-2 text-right"><h5 class="mb-1">'.doliprice($line->multicurrency_total_ttc?$line->multicurrency_total_ttc:$line->total_ttc,$contractfo->multicurrency_code).'</h5>';
echo '<h5 class="mb-1">x'.$line->qty.'</h5>'; 
echo "</div></div></li>";
}
}

echo "<li class='list-group-item list-group-item-info'>";
echo "<b>".__( 'Total excl. tax', 'doliconnect').": ".doliprice($contractfo->multicurrency_total_ht?$contractfo->multicurrency_total_ht:$contractfo->total_ht,$contractfo->multicurrency_code)."</b><br />";
echo "<b>".__( 'Total tax', 'doliconnect').": ".doliprice($contractfo->multicurrency_total_tva?$contractfo->multicurrency_total_tva:$contractfo->total_tva,$contractfo->multicurrency_code)."</b><br />";
echo "<b>".__( 'Total incl. tax', 'doliconnect').": ".doliprice($contractfo->multicurrency_total_ttc?$contractfo->multicurrency_total_ttc:$contractfo->total_ttc,$contractfo->multicurrency_code)."</b>";
echo "</li>";

//var_dump($fruits);
echo "</ul></div>";

echo "<small><div class='float-left'>";
echo dolirefresh("/contracts/".$_GET['id'],$url."&id=".$_GET['id']."&ref=".$_GET['ref'],$delay);
echo "</div><div class='float-right'>";
echo dolihelp('COM');
echo "</div></small>";

} else {

$delay = DAY_IN_SECONDS;
if ($_GET['pg']) { $page="&page=".$_GET['pg'];}
                                 
$listcontract = CallAPI("GET", "/contracts?sortfield=t.rowid&sortorder=DESC&limit=8".$page."&thirdparty_ids=".constant("DOLIBARR"), null, dolidelay($delay, esc_attr($_GET["refresh"])));

echo '<div class="card shadow-sm"><ul class="list-group list-group-flush">';
if ( !isset($listcontract->error) && $listcontract != null ) {
foreach ($listcontract  as $postcontract) {                                                                                 

$arr_params = array( 'id' => $postcontract->id, 'ref' => $postcontract->ref);  
$return = esc_url( add_query_arg( $arr_params, $url) );
                                                                                                                                                      
echo "<a href='$return' class='list-group-item d-flex justify-content-between lh-condensed list-group-item-action'><div><i class='fa fa-shopping-bag fa-3x fa-fw'></i></div><div><h6 class='my-0'>$postcontract->ref</h6><small class='text-muted'>du ".date_i18n('d/m/Y', $postcontract->date_creation)."</small></div><span>".doliprice($postcontract->multicurrency_total_ttc?$postcontract->multicurrency_total_ttc:$postcontract->total_ttc,$postcontract->multicurrency_code)."</span><span>";
if ( $postcontract->statut > 0 ) {echo "<span class='fas fa-check-circle fa-fw text-success'></span> ";
if ( $postcontract->billed == 1 ) { echo "<span class='fas fa-money-bill-alt fa-fw text-success'></span> "; 
if ( $postcontract->statut > 1 ) { echo "<span class='fas fa-shipping-fast fa-fw text-success'></span> "; }
else { echo "<span class='fas fa-shipping-fast fa-fw text-warning'></span> "; }
}
else { echo "<span class='fas fa-money-bill-alt fa-fw text-warning'></span> "; 
if ( $postcontract->statut > 1 ) { echo "<span class='fas fa-shipping-fast fa-fw text-success'></span> "; }
else { echo "<span class='fas fa-shipping-fast fa-fw text-danger'></span> "; }
}}
elseif ( $postcontract->statut == 0 ) { echo "<span class='fas fa-check-circle fa-fw text-warning'></span> <span class='fas fa-money-bill-alt fa-fw text-danger'></span> <span class='fas fa-shipping-fast fa-fw text-danger'></span>";}
elseif ( $postcontract->statut == -1 ) {echo "<span class='fas fa-check-circle fa-fw text-secondary'></span> <span class='fas fa-money-bill-alt fa-fw text-secondary'></span> <span class='fas fa-shipping-fast fa-fw text-secondary'></span>";}
echo "</span></a>";
}}
else{
echo "<li class='list-group-item list-group-item-light'><center>".__( 'No contract', 'doliconnect' )."</center></li>";
}
echo  "</ul></div>";

//echo '<br /><nav aria-label="Page navigation example">
//  <ul class="pagination">
//    <li class="page-item disabled">
//      <a class="page-link" href="#" aria-label="Previous">
//        <span aria-hidden="true">&laquo;</span>
//        <span class="sr-only">Previous</span>
//     </a>
 //   </li>
//    <li class="page-item"><a class="page-link" href="'.$url.'&pg=1">1</a></li>
//    <li class="page-item"><a class="page-link" href="'.$url.'&pg=2">3</a></li>
//    <li class="page-item"><a class="page-link" href="'.$url.'&pg=3">3</a></li>    
//    <li class="page-item disabled">
//      <a class="page-link" href="#" aria-label="Next">
//        <span aria-hidden="true">&raquo;</span>
//        <span class="sr-only">Next</span>
//      </a>
//    </li>
//  </ul>
//</nav>';

echo "<small><div class='float-left'>";
echo dolirefresh("/contracts?sortfield=t.rowid&sortorder=DESC&limit=8".$page."&thirdparty_ids=".constant("DOLIBARR"),$url,$delay);
echo "</div><div class='float-right'>";
echo dolihelp('COM');
echo "</div></small>";
}
}
//*****************************************************************************************
if ( is_object($member) && $member->value == '1' ) {
add_action( 'options_doliconnect_menu', 'membership_menu', 1, 1);
add_action( 'options_doliconnect_membership', 'membership_module' );
}

function membership_menu( $arg ) {
echo "<a href='".esc_url( add_query_arg( 'module', 'membership', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ($arg=='membership') { echo " active";}
echo "'>".__( 'Membership', 'doliconnect' )."</a>";
}

function membership_module( $url ) {
global $current_user;
$ID = $current_user->ID;
$time = current_time( 'timestamp',1);
$delay = DAY_IN_SECONDS;

if ($_POST["update_membership"] && function_exists('dolimembership') ) {
$adherent = dolimembership($_POST["update_membership"],$_POST["typeadherent"], dolidelay($delay, true));

//if ($statut==1) {
$msg = "<div class='alert alert-success' role='alert'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button><p><strong>".__( 'Congratulations!', 'doliconnect' )."</strong> ".__( 'Your membership has been updated.', 'doliconnect' )."</p></div>";
//}

if (($_POST["update_membership"]==4) && $_POST["cotisation"] && constant("DOLIBARR_MEMBER") > 0 && $_POST["timestamp_start"] > 0 && $_POST["timestamp_end"] > 0) {

$productadhesion = CallAPI("GET", "/doliconnector/constante/ADHERENT_PRODUCT_ID_FOR_SUBSCRIPTIONS", null, MONTH_IN_SECONDS);

addtodolibasket($productadhesion->value, 1, $_POST["cotisation"], $_POST["timestamp_start"], $_POST["timestamp_end"]);
wp_redirect(esc_url(doliconnecturl('dolicart')));
exit;     
} elseif ($_POST["update_membership"]==5 || $_POST["update_membership"]==1) {
$dolibarr = CallAPI("GET", "/doliconnector/".$ID, null, 0); 
}

} 

echo $msg."<div class='card shadow-sm'><div class='card-body'><div class='row'><div class='col-12 col-md-5'>";

if (!empty(constant("DOLIBARR_MEMBER")) && constant("DOLIBARR_MEMBER") > 0  && constant("DOLIBARR") > 0) { 
$adherent = CallAPI("GET", "/adherentsplus/".constant("DOLIBARR_MEMBER"), null, dolidelay($delay, esc_attr($_GET["refresh"])));
}

echo "<b>".__( 'Status', 'doliconnect' ).":</b> ";
if ( $adherent->statut == '1') {
if  ($adherent->datefin == null ) {echo  "<span class='badge badge-danger'>".__( 'Waiting payment', 'doliconnect' )."</span>";}
else {
if ( $adherent->datefin+86400>$time){echo  "<span class='badge badge-success'>".__( 'Active', 'doliconnect' )."</span>";}else {echo  "<span class='badge badge-danger'>".__( 'Waiting payment', 'doliconnect' )."</span>";}
}}
elseif ( $adherent->statut == '0' ) {
echo  "<span class='badge badge-dark'>".__( 'Terminated', 'doliconnect' )."</span>";}
elseif ( $adherent->statut == '-1' ) {
echo  "<span class='badge badge-warning'>".__( 'Waiting validation', 'doliconnect' )."</span>";}
else {echo  "<span class='badge badge-dark'>".__( 'No membership', 'doliconnect' )."</span>";}
echo  "<br />";
$type=(! empty($adherent->type) ? $adherent->type : __( 'nothing', 'doliconnect' ));
echo  "<b>".__( 'Type', 'doliconnect' ).":</b> ".$type."<br />";
echo  "<b>".__( 'End of membership', 'doliconnect' ).":</b> ";
if ( $adherent->datefin == null ) { echo  "***";
} else {
$datefin =  date_i18n('d/m/Y', $adherent->datefin);
echo  "$datefin"; }
echo  "<br /><b>".__( 'Seniority', 'doliconnect' ).":</b> ";
echo  "<br /><b>".__( 'Commitment', 'doliconnect' ).":</b> ";
if ((current_time('timestamp') > $adherent->datecommitment) || null == $adherent->datecommitment) { echo  __( 'no', 'doliconnect' );
} else {
$datefin =  date_i18n('d/m/Y', $adherent->datecommitment);
echo  "$datefin"; }

echo "</div><div class='col-12 col-md-7'>";

if ( function_exists('dolimembership_modal') ) {
dolimembership_modal($adherent);

echo "<script>";
?>

window.setTimeout(function() {
    $(".alert").fadeTo(500, 0).slideUp(500, function(){
        $(this).remove(); 
    });
}, 5000);

<?php
echo "</script>";

if ($adherent->datefin == null && $adherent->statut == '0') {echo  "<a href='#' id='subscribe-button2' class='btn btn text-white btn-warning btn-block' data-toggle='modal' data-target='#activatemember'><b>".__( 'Become a member', 'doliconnect' )."</b></a>";
} elseif ($adherent->statut == '1') {
if ( $time > $adherent->next_subscription_renew && $adherent->datefin != null ) {
echo "<a class='btn btn text-white btn-warning btn-block' data-toggle='modal' data-target='#activatemember'><b>".__( 'Renew my subscription', 'doliconnect' )."</b></a>";
} elseif ( ( $adherent->datefin + 86400 ) > $time ) {
echo  "<a href='#' id='subscribe-button2' class='btn btn text-white btn-warning btn-block' data-toggle='modal' data-target='#activatemember'><b>".__( 'Modify my subscription', 'doliconnect' )."</b></a>";
}else {echo  "<button class='btn btn btn-danger btn-block' data-toggle='modal' data-target='#activatemember'><b>".__( 'Pay my subscription', 'doliconnect' )."</b></button>";}
} elseif ( $adherent->statut == '0') {
if ( ( $adherent->datefin + 86400) > $time ) {
echo "<form id='subscription-form' action='".doliconnecturl('doliaccount')."?module=membership' method='post'><input type='hidden' name='update_membership' value='4'><button id='resiliation-button' class='btn btn btn-warning btn-block' type='submit'><b>".__( 'Reactivate my subscription', 'doliconnect' )."</b></button></form>";
} else {
echo  "<a href='#' class='btn btn text-white btn-warning btn-block' data-toggle='modal' data-target='#activatemember'><b>".__( 'Renew my subscription', 'doliconnect' )."</b></a>";
}
} elseif ( $adherent->statut == '-1' ) {
echo '<div class="clearfix"><div class="spinner-border float-left" role="status">
<span class="sr-only">Loading...</span></div>'.__('Your request has been registered. You will be notified at validation.', 'doliconnect').'</div>';
} else {
if ( empty($current_user->billing_address) || empty($current_user->billing_zipcode) || empty($current_user->billing_city) || empty($current_user->billing_country) || empty($current_user->billing_birth) || empty($current_user->user_firstname) || empty($current_user->user_lastname) || empty($current_user->user_email)) {
echo "Pour adhérer, tous les champs doivent être renseignés dans vos <a href='".esc_url( get_permalink(get_option('doliaccount')))."?module=informations&return=membership' class='alert-link'>".__( 'Personal informations', 'doliconnect' )."</a></div><div class='col-sm-6 col-md-7'>";
} else { 
echo "<a href='#' class='btn btn text-white btn-warning btn-block' data-toggle='modal' data-target='#activatemember'><b>".__( 'Become a member', 'doliconnect' )."</b></a>";
}
}


if ( $adherent->datefin != null && $adherent->statut == 1 && $adherent->datefin > $adherent->next_subscription_renew && $adherent->next_subscription_renew > current_time( 'timestamp',1) ) {
echo "<center><small>".sprintf(__('Renew from %s', 'doliconnect'), date_i18n('d/m/Y', $adherent->next_subscription_renew))."</small></center>";
}
}

echo "</div></div>";
if ($adherent->ref != $adherent->id ) { 
echo "<label for='license'><small>N° de licence</small></label><div class='input-group mb-2'><div class='input-group-prepend'><div class='input-group-text'><i class='fas fa-key fa-fw'></i></div></div><input class='form-control' type='text' value='".$adherent->ref."' readonly></div>";
}
do_action('mydoliconnectmemberform', $adherent);
echo "</div><ul class='list-group list-group-flush'>";

if (constant("DOLIBARR_MEMBER") > 0) {
$listcotisation = CallAPI("GET", "/adherentsplus/".constant("DOLIBARR_MEMBER")."/subscriptions", null, dolidelay($delay, esc_attr($_GET["refresh"])));
} 

if ( !isset($listcotisation->error) && $listcotisation != null ) { 
foreach ( $listcotisation as $cotisation ) {                                                                                 
$dated =  date_i18n('d/m/Y', $cotisation->dateh);
$datef =  date_i18n('d/m/Y', $cotisation->datef);
echo "<li class='list-group-item'><table width='100%'><tr><td>$cotisation->label</td><td>$dated ".__( 'to', 'doliconnect' )." $datef";
echo "</td><td class='text-right'><b>".doliprice($cotisation->amount)."</b></td></tr></table><span></span></li>";
}
}
else { 
echo "<li class='list-group-item list-group-item-light'><center>".__( 'No subscription', 'doliconnect' )."</center></li>";
}
echo  "</ul></div>";

echo "<small><div class='float-left'>";
echo dolirefresh("/adherentsplus/".constant("DOLIBARR_MEMBER"),$url,$delay);
echo "</div><div class='float-right'>";
echo dolihelp('COM');
echo "</div></small>";
}

if ( is_object($memberconsumption) && $memberconsumption->value == '1' ) {
add_action( 'options_doliconnect_menu', 'membershipconsumption_menu', 2, 1);
add_action( 'options_doliconnect_membershipconsumption', 'membershipconsumption_module' );
}  

function membershipconsumption_menu( $arg ) {
echo "<a href='".esc_url( add_query_arg( 'module', 'membershipconsumption', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ($arg=='membershipconsumption') { echo " active";}
echo "'>".__( 'Consumptions monitoring', 'doliconnect' )."</a>";
}

function membershipconsumption_module( $url ) {
$delay = HOUR_IN_SECONDS;

echo "<div class='card shadow-sm'><div class='card-body'>";
echo "<b>".__( 'Next billing date', 'doliconnect' ).": </b> $datecommande<br>";

echo "</div><ul class='list-group list-group-flush'>";

if (constant("DOLIBARR_MEMBER") > 0) {
$listconsumption = CallAPI("GET", "/adherentsplus/".constant("DOLIBARR_MEMBER")."/consumptions", null, dolidelay($delay, esc_attr($_GET["refresh"])));
} 

if ( !isset($listconsumption->error) && $listconsumption != null ) { 
foreach ( $listconsumption as $consumption ) {                                                                                 
$datec =  date_i18n('d/m/Y H:i', $consumption->date_creation);
echo "<li class='list-group-item'><table width='100%'><tr><td>$datec</td><td>$consumption->label</td><td>";

if ( !empty($consumption->value) ) {
echo $consumption->value." ".$consumption->unit;
} else {
echo "x$consumption->qty";
}

echo "</td>";
echo "<td class='text-right'><b>".doliprice($consumption->amount)."</b></td></tr></table><span></span></li>";
}
} else { 
echo "<li class='list-group-item list-group-item-light'><center>".__( 'No consumption', 'doliconnect' )."</center></li>";
}

echo  "</ul></div>";

echo "<small><div class='float-left'>";
echo dolirefresh("/adherentsplus/".constant("DOLIBARR_MEMBER")."/consumptions",$url,$delay);
echo "</div><div class='float-right'>";
echo dolihelp('COM');
echo "</div></small>";
}

//*****************************************************************************************

if ( is_object($help) && $help->value == '1' ) {
add_action( 'settings_doliconnect_menu', 'ticket_menu', 1, 1);
add_action( 'settings_doliconnect_ticket', 'ticket_module');
}

function ticket_menu( $arg ) {
echo "<a href='".esc_url( add_query_arg( 'module', 'ticket', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ($arg=='ticket') { echo " active";}
echo "'>".__( 'Help', 'doliconnect' )."</a>";
}

function ticket_module( $url ) {
$delay = HOUR_IN_SECONDS;

if ($_GET['id']>0) {
$ticket = CallAPI("GET", "/tickets/".$_GET['id'], null, dolidelay($delay, esc_attr($_GET["refresh"])));
//echo $ticket;
}

if ( ($_GET['id'] != null ) && ($_GET['ref'] != null ) && ( constant("DOLIBARR") == $ticket->socid ) && ($_GET['ref'] == $ticket->ref ) ) {
echo "<div class='card shadow-sm'><div class='card-body'><h5 class='card-title'>$ticket->ref</h5><div class='row'><div class='col-md-6'>";
$dateticket =  date_i18n('d/m/Y', $ticket->datec);
echo "<b>".__( 'Date of creation', 'doliconnect' ).": </b> $dateticket<br>";
echo "<b>".__( 'Type and category', 'doliconnect' ).": </b> ".__($ticket->type_label, 'doliconnect' ).", ".__($ticket->category_label, 'doliconnect' )."<br>";
echo "<b>".__( 'Severity', 'doliconnect' ).": </b> ".__($ticket->severity_label, 'doliconnect' )."<br>";
echo "</div><div class='col-md-6'><h3 class='text-right'>";
if ( $ticket->fk_statut == 9 ) { echo "<span class='label label-default'>".__( 'Deleted', 'doliconnect' )."</span>"; }
elseif ( $ticke->fk_statut == 8 ) { echo "<span class='label label-success'>".__( 'Closed', 'doliconnect' )."</span>"; }
elseif ( $ticket->fk_statut == 6 ) { echo "<span class='label label-warning'>".__( 'Waiting', 'doliconnect' )."</span>"; }
elseif ( $ticket->fk_statut == 5 ) { echo "<span class='label label-warning'>".__( 'In progress', 'doliconnect' )."</span>"; }
elseif ( $ticket->fk_statut == 4 ) { echo "<span class='label label-warning'>".__( 'Assigned', 'doliconnect' )."</span>"; }
elseif ( $ticket->fk_statut == 3 ) { echo "<span class='label label-warning'>".__( 'Answered', 'doliconnect' )."</span>"; }
elseif ( $ticket->fk_statut == 1 ) { echo "<span class='label label-warning'>".__( 'Read', 'doliconnect' )."</span>"; }
elseif ( $ticket->fk_statut == 0 ) { echo "<span class='label label-danger'>".__( 'Unread', 'doliconnect' )."</span>"; }
echo "</h3></div></div>";
echo '<BR/><div class="progress"><div class="progress-bar bg-success" role="progressbar" style="width: '.$ticket->progress.'%" aria-valuenow="'.$ticket->progress.'" aria-valuemin="0" aria-valuemax="100"></div></div>';
echo "</div><ul class='list-group list-group-flush'>
<li class='list-group-item'><h5 class='mb-1'>".__( 'Subject', 'doliconnect' ).": $ticket->subject</h5>
<p class='mb-1'>".__( 'Initial message', 'doliconnect' ).": $ticket->message</p></li>";
if ( $ticket->fk_statut < '8' && $ticket->fk_statut > '0' ) {
echo "<li class='list-group-item'>";
echo '<form id="message-ticket-form" action="'.$url.'&id='.$ticket->id.'&ref='.$ticket->ref.'" method="post">';
echo "<script>";
?>

window.setTimeout(function() {
    $(".alert").fadeTo(500, 0).slideUp(500, function(){
        $(this).remove(); 
    });
}, 5000);

var form = document.getElementById('message-ticket-form');
form.addEventListener('submit', function(event) {

jQuery('#DoliconnectLoadingModal').modal('show'); 
console.log("submit");
form.submit();

});

<?php
echo "</SCRIPT>";
echo '<div class="form-group"><label for="ticketnewmessage"><small>'.__( 'New message', 'doliconnect' ).'</small></label>
<div class="input-group mb-2"><div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-comment fa-fw"></i></span></div><textarea class="form-control" id="ticketnewmessage" rows="3"></textarea>
</div></div><button class="btn btn-danger btn-block" type="submit"><b>'.__( 'Send', 'doliconnect' ).'</b></button></form>';
echo doliloading('ticket');
echo "</li>";
}
if ( $ticket->messages != null ) {
foreach ( $ticket->messages as $msg ) {
$datemsg =  date_i18n('d/m/Y - H:i', $msg->datec);  
echo  "<li class='list-group-item'><b>$datemsg $msg->fk_user_action_string</b><br>$msg->message</li>";
}} 
echo "</ul></div>";
} elseif (isset($_GET['create']))  {
if ($_POST["case"] == 'createticket') {
$rdr = [
    'fk_soc' => constant("DOLIBARR"),
    'type_code' => $_POST['ticket_type'],
    'category_code' => $_POST['ticket_category'],
    'severity_code' => $_POST['ticket_severity'],
    'subject' => sanitize_text_field($_POST['ticket_subject']),
    'message' => sanitize_textarea_field($_POST['ticket_message']),
	];                  
$ticketid = CallAPI("POST", "/tickets", $rdr, dolidelay($delay, true));
//echo $ticketid;

if ( $ticketid > 0 ) {
$msg = "<div class='alert alert-success' role='alert'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button><p><strong>".__( 'Congratulations!', 'doliconnect' )."</strong> ".__( 'Your ticket has been submitted.', 'doliconnect' )."</p></div>"; 
} }
echo "<form id='ticket-form' action='".$url."&create' method='post'>";
echo $msg;
echo "<script>";
?>

window.setTimeout(function() {
    $(".alert").fadeTo(500, 0).slideUp(500, function(){
        $(this).remove(); 
    });
}, 5000);

var form = document.getElementById('ticket-form');
form.addEventListener('submit', function(event) {

jQuery('#DoliconnectLoadingModal').modal('show'); 
console.log("submit");
form.submit();

});

<?php
echo "</SCRIPT>";
echo "<div class='card shadow-sm'><ul class='list-group list-group-flush'><li class='list-group-item'><h5 class='card-title'>".__( 'Open a new ticket', 'doliconnect' )."</h5>";
echo "<div class='form-group'><label for='inputcivility'><small>".__( 'Type and category', 'doliconnect' )."</small></label>
<div class='input-group mb-2'><div class='input-group-prepend'><span class='input-group-text' id='identity'><i class='fas fa-info-circle fa-fw'></i></span></div>";
$type = CallAPI("GET", "/setup/dictionary/ticket_types?sortfield=pos&sortorder=ASC&limit=100", null, MONTH_IN_SECONDS);
//echo $type;

if ( isset($type) ) { 
$tp= __( 'Issue or problem', 'doliconnect' ).__( 'Commercial question', 'doliconnect' ).__( 'Change or enhancement request', 'doliconnect' ).__( 'Project', 'doliconnect' ).__( 'Other', 'doliconnect' );
echo "<select class='custom-select' id='ticket_type'  name='ticket_type'>";
foreach ($type as $postv) {
echo "<option value='".$postv->code."' ";
if ( $_GET['type'] == $postv->code ) {
echo "selected ";
} elseif ( $postv->use_default == 1 ) {
echo "selected ";}
echo ">".__($postv->label, 'doliconnect' )."</option>";
}
echo "</select>";
}

$cat = CallAPI("GET", "/setup/dictionary/ticket_categories?sortfield=pos&sortorder=ASC&limit=100", null, MONTH_IN_SECONDS);

if (isset($cat)) { 
echo "<select class='custom-select' id='ticket_cat'  name='ticket_category'>";
foreach ( $cat as $postv ) {
echo "<option value='".$postv->code."' ";
if ( $postv->use_default == 1 ) {
echo "selected ";}
echo ">".__($postv->label, 'doliconnect' )."</option>";
}
echo "</select>";
} 
echo "</div></div>";
echo "<div class='form-group'><label for='inputcivility'><small>".__( 'Severity', 'doliconnect' )."</small></label>
<div class='input-group mb-2'><div class='input-group-prepend'><span class='input-group-text' id='identity'><i class='fas fa-bug fa-fw'></i></span></div>";
$severity = CallAPI("GET", "/setup/dictionary/ticket_severities?sortfield=pos&sortorder=ASC&limit=100", null, MONTH_IN_SECONDS);

if (isset($severity)) { 
$sv= __( 'Critical / blocking', 'doliconnect' ).__( 'High', 'doliconnect' ).__( 'Normal', 'doliconnect' ).__( 'Low', 'doliconnect' );
echo "<select class='custom-select' id='ticket_severity'  name='ticket_severity'>";
foreach ( $severity as $postv ) {
echo "<option value='".$postv->code."' ";
if ( $postv->use_default ==1 ) {
echo "selected ";}
echo ">".__($postv->label, 'doliconnect' )."</option>";
}
echo "</select>";
}
echo "</div></div>";

echo "<div class='form-group'><label for='ticket_subject'><small>".__( 'Subject', 'doliconnect' )."</small></label><div class='input-group mb-2'><div class='input-group-prepend'><div class='input-group-text'><i class='fas fa-bullhorn fa-fw'></i></div></div><input type='text' class='form-control' id='ticket_subject' name='ticket_subject' value='' autocomplete='off' required></div></div>";

echo "<div class='form-group'>
<label for='description'><small>".__( 'Message', 'doliconnect' )."</small></label><div class='input-group mb-2'><div class='input-group-prepend'><span class='input-group-text'><i class='fas fa-file-alt fa-fw'></i></span></div>
<textarea type='text' class='form-control' name='ticket_message' id='ticket_message' rows='8' required></textarea></div></div></li></ul>";

echo "<div class='card-body'><input type='hidden' name='case' value='createticket'><button type='submit' class='btn btn-block btn-warning'><b>".__( 'Send', 'doliconnect' )."</b></button></div>";

echo "</div></form>";
echo doliloading('ticket');

} else {
$delay = HOUR_IN_SECONDS;

$listticket = CallAPI("GET", "/tickets?socid=".constant("DOLIBARR")."&sortfield=s.rowid&sortorder=DESC&limit=10", null, dolidelay($delay, esc_attr($_GET["refresh"])));
//echo $listticket;

echo '<div class="card shadow-sm"><ul class="list-group list-group-flush">';
//if ($help>0) {
echo "<li class='list-group-item list-group-item-light'><a href='".$url."&create' class='btn btn-info btn-block'><b>".__( 'Open a new ticket', 'doliconnect' )."</b></a></li>";
//}
if ( !isset($listticket->error) && $listticket != null ) {
foreach ($listticket as $postticket) {                                                                                 

$arr_params = array( 'id' => $postticket->id, 'ref' => $postticket->ref);  
$return = esc_url( add_query_arg( $arr_params, $url) );

if ( $postticket->severity_code == BLOCKING ) { $color="text-danger"; } 
elseif ( $postticket->severity_code == HIGH ) { $color="text-warning"; }
elseif ( $postticket->severity_code == NORMAL ) { $color="text-success"; }
elseif ( $postticket->severity_code == LOW ) { $color="text-info"; } else { $color="text-dark"; }
echo "<a href='$return' class='list-group-item d-flex justify-content-between lh-condensed list-group-item-action'><div><i class='fas fa-question-circle $color fa-3x fa-fw'></i></div><div><h6 class='my-0'>$postticket->subject</h6><small class='text-muted'>du ".date_i18n('d/m/Y', $postticket->datec)."</small></div><span class='text-center'>".__($postticket->type_label, 'doliconnect' )."<br/>".__($postticket->category_label, 'doliconnect' )."</span><span>";
if ( $postticket->fk_statut == 9 ) { echo "<span class='label label-default'>".__( 'Deleted', 'doliconnect' )."</span>"; }
elseif ( $postticket->fk_statut == 8 ) { echo "<span class='label label-success'>".__( 'Closed', 'doliconnect' )."</span>"; }
elseif ( $postticket->fk_statut == 6 ) { echo "<span class='label label-warning'>".__( 'Waiting', 'doliconnect' )."</span>"; }
elseif ( $postticket->fk_statut == 5 ) { echo "<span class='label label-warning'>".__( 'Progress', 'doliconnect' )."</span>"; }
elseif ( $postticket->fk_statut == 4 ) { echo "<span class='label label-warning'>".__( 'Assigned', 'doliconnect' )."</span>"; }
elseif ( $postticket->fk_statut == 3 ) { echo "<span class='label label-warning'>".__( 'Answered', 'doliconnect' )."</span>"; }
elseif ( $postticket->fk_statut == 1 ) { echo "<span class='label label-warning'>".__( 'Read', 'doliconnect' )."</span>"; }
elseif ( $postticket->fk_statut == 0 ) { echo "<span class='label label-danger'>".__( 'Unread', 'doliconnect' )."</span>"; }
echo "</span></a>";
}}
else{
echo "<li class='list-group-item list-group-item-light'><center>".__( 'No ticket', 'doliconnect' )."</center></li>";
}
echo  "</ul></div>";

echo "<small><div class='float-left'>";
echo dolirefresh("/tickets?socid=".constant("DOLIBARR")."&sortfield=s.rowid&sortorder=DESC&limit=10",$url,$delay);
echo "</div><div class='float-right'>";
echo dolihelp('COM');
echo "</div></small>";
}
}

function settings_menu($arg) {
echo "<a href='".esc_url( add_query_arg( 'module', 'settings', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ($arg=='settings') { echo " active"; }
echo "'>".__( 'Settings & security', 'doliconnect' )."</a>";
}
add_action( 'settings_doliconnect_menu', 'settings_menu', 2, 1);

function settings_module($url) {
global $wp,$wpdb,$current_user;
$ID = $current_user->ID;

if ( $_POST["case"] == 'updatesettings' ) {
update_usermeta( $ID, 'loginmailalert', sanitize_text_field($_POST['loginmailalert']) );
update_usermeta( $ID, 'optin1', sanitize_text_field($_POST['optin1']) );
update_usermeta( $ID, 'optin2', sanitize_text_field($_POST['optin2']) );
if ( isset($_POST['locale']) ) { update_usermeta( $ID, 'locale', sanitize_text_field($_POST['locale']) ); }  
//if (isset($_POST['multicurrency_code'])) {update_usermeta( $ID, 'multicurrency_code', sanitize_text_field($_POST['multicurrency_code']) );}

if ( constant("DOLIBARR") > 0 ) {
$info = [
    'default_lang'  => sanitize_text_field($_POST['locale']),
    'multicurrency_code'  => sanitize_text_field($_POST['multicurrency_code']),
	];
$thirparty = CallAPI("PUT", "/thirdparties/".constant("DOLIBARR"), $info, MONTH_IN_SECONDS);
}

}

echo "<form id='settings-form' action='".$url."' method='post'>";
echo $msg;
echo "<script>";
?>
window.setTimeout(function() {
    $(".alert").fadeTo(500, 0).slideUp(500, function(){
        $(this).remove(); 
    });
}, 5000);

var form = document.getElementById('settings-form');
form.addEventListener('submit', function(event) {

form.submit();
});

function demo(){
 
jQuery('#DoliconnectLoadingModal').modal('show'); 
this.form.submit();

}
<?php
echo "</SCRIPT>";
echo "<div class='card shadow-sm'><ul class='list-group list-group-flush'>";
echo "<li class='list-group-item'><div class='custom-control custom-switch'><input type='checkbox' class='custom-control-input' name='loginmailalert' id='loginmailalert' ";
if ( $current_user->loginmailalert == on ) { echo " checked"; }        
echo " onChange='demo()' ><label class='custom-control-label w-100' for='loginmailalert'> ".__( 'Receive a email notification at each connection', 'doliconnect' )."</label>
</div></li>";
echo "<li class='list-group-item'><div class='custom-control custom-switch'><input type='checkbox' class='custom-control-input' name='optin1' id='optin1' ";
if ( $current_user->optin1 == on ) { echo " checked"; }        
echo " onChange='demo()' ><label class='custom-control-label w-100' for='optin1'> ".__( 'I would like to receive the newsletter', 'doliconnect' )."</label>
</div></li>";
echo "<li class='list-group-item'><div class='custom-control custom-switch'><input type='checkbox' class='custom-control-input' name='optin2' id='optin2' ";
if ( $current_user->optin2 == on ) { echo " checked"; }        
echo " onChange='demo()' ><label class='custom-control-label w-100' for='optin2'> ".__( 'I would like to receive the offers of our partners', 'doliconnect' )."</label>
</div></li>";
$privacy=$wpdb->prefix."doliprivacy";
if ( $current_user->$privacy ) {
echo "<li class='list-group-item'>";
echo "".__( 'Approval of the Privacy Policy the', 'doliconnect' )." ".date_i18n( get_option( 'date_format' ).', '.get_option('time_format'), $current_user->$privacy, false);
echo "</li>";
}
echo "<li class='list-group-item'>";
//echo $current_user->locale;
echo "<div class='form-group'><label for='inputaddress'><small>".__( 'Default language', 'doliconnect' )."</small></label>
<div class='input-group'><div class='input-group-prepend'><span class='input-group-text'><i class='fas fa-language fa-fw'></i></span></div>";
if (function_exists('pll_the_languages')) { 
echo "<select class='form-control' id='locale' name='locale' onChange='demo()' >";
echo "<option value=''>".__( 'Default / Browser language', 'doliconnect' )."</option>";
$translations = pll_the_languages( array( 'raw' => 1 ) );
foreach ($translations as $key => $value) {
echo "<option value='".str_replace("-","_",$value[locale])."' ";
if  ( $current_user->locale == str_replace("-","_",$value[locale]) ) {echo " selected";}
echo ">".$value[name]."</option>";
}
echo "</select>";
} else {
echo "<input class='form-control' type='text' value='".__( 'Default / Browser language', 'doliconnect' )."' readonly>";
}
echo "</div></div>";
//echo pll_default_language('locale');
echo "</li>";

if ( constant("DOLIBARR") > '0' ) {
$thirdparty = CallAPI("GET", "/thirdparties/".constant("DOLIBARR"), null, dolidelay( DAY_IN_SECONDS, esc_attr($_GET["refresh"])));
}

//if (get_option('doliconnectbeta')=='1') { 
echo "<li class='list-group-item'>";
//echo $current_user->locale;
echo "<div class='form-group'><label for='inputaddress'><small>".__( 'Default currency', 'doliconnect' )."</small></label>
<div class='input-group'><div class='input-group-prepend'><span class='input-group-text'><i class='fas fa-money-bill-alt fa-fw'></i></span></div>";
echo "<select class='form-control' id='multicurrency_code' name='multicurrency_code' onChange='demo()' >";
echo "<option value='".$thirdparty->multicurrency_code."'>".doliprice(0,$thirdparty->multicurrency_code)." / ".$thirdparty->multicurrency_code."</option>";
echo "</select>";
echo "</div></div>";
//echo pll_default_language('locale');
echo "<input type='hidden' name='case' value='updatesettings'></li>";
//}

echo "</ul></div>";
echo "<p class='text-right'><small>";
echo dolihelp('ISSUE');
echo "</small></p>";
echo "</form>";



if ( get_option('doliconnectbeta')=='1' ) {
echo '<div class="accordion" id="accordionExample">
<div class="card shadow-sm"><ul class="list-group list-group-flush">
<button id="headingOne" type="button" class="list-group-item list-group-item-action flex-column align-items-start" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
Dapibus ac facilisis in
</button>
<li class="list-group-item list-group-item-action flex-column align-items-start" id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably heard of them accusamus labore sustainable VHS.
Cras justo odio
</li>
  
<button id="headingTwo" type="button" class="list-group-item list-group-item-action flex-column align-items-start collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
Dapibus ac facilisis in
</button>
<li class="list-group-item list-group-item-action flex-column align-items-start collapse" id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably heard of them accusamus labore sustainable VHS.
Cras justo odio
</li>

</div>';
echo '<script src="//mozilla.github.io/pdf.js/build/pdf.js"></script>

<h6>PDF.js example</h6>

<canvas id="the-canvas"></canvas><script>';
?>

var pdfData = atob(
  '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RDoyMDE4MTIyMjAyMDUxOSswMScwMCcpIC9Nb2REYXRlIChEOjIwMTgxMjIyMDIwNTE5KzAxJzAwJykgL1RyYXBwZWQgL0ZhbHNlID4+CmVuZG9iagoxMCAwIG9iago8PCAvVHlwZSAvTWV0YWRhdGEgL1N1YnR5cGUgL1hNTCAvTGVuZ3RoIDQzMjQgPj4gc3RyZWFtCjw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+Cjx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDQuMi4xLWMwNDMgNTIuMzcyNzI4LCAyMDA5LzAxLzE4LTE1OjA4OjA0Ij4KCTxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+CgkJPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6ZGM9Imh0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvIj4KCQkJPGRjOmZvcm1hdD5hcHBsaWNhdGlvbi9wZGY8L2RjOmZvcm1hdD4KCQkJPGRjOnRpdGxlPgoJCQkJPHJkZjpBbHQ+CgkJCQkJPHJkZjpsaSB4bWw6bGFuZz0ieC1kZWZhdWx0Ij5DTzE4MTItMDAyNDwvcmRmOmxpPgoJCQkJPC9yZGY6QWx0PgoJCQk8L2RjOnRpdGxlPgoJCQk8ZGM6Y3JlYXRvcj4KCQkJCTxyZGY6U2VxPgoJCQkJCTxyZGY6bGk+PC9yZGY6bGk+CgkJCQk8L3JkZjpTZXE+CgkJCTwvZGM6Y3JlYXRvcj4KCQkJPGRjOmRlc2NyaXB0aW9uPgoJCQkJPHJkZjpBbHQ+CgkJCQkJPHJkZjpsaSB4bWw6bGFuZz0ieC1kZWZhdWx0Ij5Db21tYW5kZTwvcmRmOmxpPgoJCQkJPC9yZGY6QWx0PgoJCQk8L2RjOmRlc2NyaXB0aW9uPgoJCQk8ZGM6c3ViamVjdD4KCQkJCTxyZGY6QmFnPgoJCQkJCTxyZGY6bGk+Q08xODEyLTAwMjQgQ29tbWFuZGUgVGVzdCBURVNUPC9yZGY6bGk+CgkJCQk8L3JkZjpCYWc+CgkJCTwvZGM6c3ViamVjdD4KCQk8L3JkZjpEZXNjcmlwdGlvbj4KCQk8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iPgoJCQk8eG1wOkNyZWF0ZURhdGU+MjAxOC0xMi0yMlQwMjowNToxOSswMTowMDwveG1wOkNyZWF0ZURhdGU+CgkJCTx4bXA6Q3JlYXRvclRvb2w+RG9saWJhcnIgMTAuMC4wLWFscGhhPC94bXA6Q3JlYXRvclRvb2w+CgkJCTx4bXA6TW9kaWZ5RGF0ZT4yMDE4LTEyLTIyVDAyOjA1OjE5KzAxOjAwPC94bXA6TW9kaWZ5RGF0ZT4KCQkJPHhtcDpNZXRhZGF0YURhdGU+MjAxOC0xMi0yMlQwMjowNToxOSswMTowMDwveG1wOk1ldGFkYXRhRGF0ZT4KCQk8L3JkZjpEZXNjcmlwdGlvbj4KCQk8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczpwZGY9Imh0dHA6Ly9ucy5hZG9iZS5jb20vcGRmLzEuMy8iPgoJCQk8cGRmOktleXdvcmRzPkNPMTgxMi0wMDI0IENvbW1hbmRlIFRlc3QgVEVTVDwvcGRmOktleXdvcmRzPgoJCQk8cGRmOlByb2R1Y2VyPlRDUERGIDYuMi4xNyAoaHR0cDovL3d3dy50Y3BkZi5vcmcpPC9wZGY6UHJvZHVjZXI+CgkJPC9yZGY6RGVzY3JpcHRpb24+CgkJPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iPgoJCQk8eG1wTU06RG9jdW1lbnRJRD51dWlkOmM3OTNmMmI4LTNhMGYtZmNmMy01M2ZkLTQ0NGZmZjdmZTU2MDwveG1wTU06RG9jdW1lbnRJRD4KCQkJPHhtcE1NOkluc3RhbmNlSUQ+dXVpZDpjNzkzZjJiOC0zYTBmLWZjZjMtNTNmZC00NDRmZmY3ZmU1NjA8L3htcE1NOkluc3RhbmNlSUQ+CgkJPC9yZGY6RGVzY3JpcHRpb24+CgkJPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6cGRmYUV4dGVuc2lvbj0iaHR0cDovL3d3dy5haWltLm9yZy9wZGZhL25zL2V4dGVuc2lvbi8iIHhtbG5zOnBkZmFTY2hlbWE9Imh0dHA6Ly93d3cuYWlpbS5vcmcvcGRmYS9ucy9zY2hlbWEjIiB4bWxuczpwZGZhUHJvcGVydHk9Imh0dHA6Ly93d3cuYWlpbS5vcmcvcGRmYS9ucy9wcm9wZXJ0eSMiPgoJCQk8cGRmYUV4dGVuc2lvbjpzY2hlbWFzPgoJCQkJPHJkZjpCYWc+CgkJCQkJPHJkZjpsaSByZGY6cGFyc2VUeXBlPSJSZXNvdXJjZSI+CgkJCQkJCTxwZGZhU2NoZW1hOm5hbWVzcGFjZVVSST5odHRwOi8vbnMuYWRvYmUuY29tL3BkZi8xLjMvPC9wZGZhU2NoZW1hOm5hbWVzcGFjZVVSST4KCQkJCQkJPHBkZmFTY2hlbWE6cHJlZml4PnBkZjwvcGRmYVNjaGVtYTpwcmVmaXg+CgkJCQkJCTxwZGZhU2NoZW1hOnNjaGVtYT5BZG9iZSBQREYgU2NoZW1hPC9wZGZhU2NoZW1hOnNjaGVtYT4KCQkJCQk8L3JkZjpsaT4KCQkJCQk8cmRmOmxpIHJkZjpwYXJzZVR5cGU9IlJlc291cmNlIj4KCQkJCQkJPHBkZmFTY2hlbWE6bmFtZXNwYWNlVVJJPmh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS88L3BkZmFTY2hlbWE6bmFtZXNwYWNlVVJJPgoJCQkJCQk8cGRmYVNjaGVtYTpwcmVmaXg+eG1wTU08L3BkZmFTY2hlbWE6cHJlZml4PgoJCQkJCQk8cGRmYVNjaGVtYTpzY2hlbWE+WE1QIE1lZGlhIE1hbmFnZW1lbnQgU2NoZW1hPC9wZGZhU2NoZW1hOnNjaGVtYT4KCQkJCQkJPHBkZmFTY2hlbWE6cHJvcGVydHk+CgkJCQkJCQk8cmRmOlNlcT4KCQkJCQkJCQk8cmRmOmxpIHJkZjpwYXJzZVR5cGU9IlJlc291cmNlIj4KCQkJCQkJCQkJPHBkZmFQcm9wZXJ0eTpjYXRlZ29yeT5pbnRlcm5hbDwvcGRmYVByb3BlcnR5OmNhdGVnb3J5PgoJCQkJCQkJCQk8cGRmYVByb3BlcnR5OmRlc2NyaXB0aW9uPlVVSUQgYmFzZWQgaWRlbnRpZmllciBmb3Igc3BlY2lmaWMgaW5jYXJuYXRpb24gb2YgYSBkb2N1bWVudDwvcGRmYVByb3BlcnR5OmRlc2NyaXB0aW9uPgoJCQkJCQkJCQk8cGRmYVByb3BlcnR5Om5hbWU+SW5zdGFuY2VJRDwvcGRmYVByb3BlcnR5Om5hbWU+CgkJCQkJCQkJCTxwZGZhUHJvcGVydHk6dmFsdWVUeXBlPlVSSTwvcGRmYVByb3BlcnR5OnZhbHVlVHlwZT4KCQkJCQkJCQk8L3JkZjpsaT4KCQkJCQkJCTwvcmRmOlNlcT4KCQkJCQkJPC9wZGZhU2NoZW1hOnByb3BlcnR5PgoJCQkJCTwvcmRmOmxpPgoJCQkJCTxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgoJCQkJCQk8cGRmYVNjaGVtYTpuYW1lc3BhY2VVUkk+aHR0cDovL3d3dy5haWltLm9yZy9wZGZhL25zL2lkLzwvcGRmYVNjaGVtYTpuYW1lc3BhY2VVUkk+CgkJCQkJCTxwZGZhU2NoZW1hOnByZWZpeD5wZGZhaWQ8L3BkZmFTY2hlbWE6cHJlZml4PgoJCQkJCQk8cGRmYVNjaGVtYTpzY2hlbWE+UERGL0EgSUQgU2NoZW1hPC9wZGZhU2NoZW1hOnNjaGVtYT4KCQkJCQkJPHBkZmFTY2hlbWE6cHJvcGVydHk+CgkJCQkJCQk8cmRmOlNlcT4KCQkJCQkJCQk8cmRmOmxpIHJkZjpwYXJzZVR5cGU9IlJlc291cmNlIj4KCQkJCQkJCQkJPHBkZmFQcm9wZXJ0eTpjYXRlZ29yeT5pbnRlcm5hbDwvcGRmYVByb3BlcnR5OmNhdGVnb3J5PgoJCQkJCQkJCQk8cGRmYVByb3BlcnR5OmRlc2NyaXB0aW9uPlBhcnQgb2YgUERGL0Egc3RhbmRhcmQ8L3BkZmFQcm9wZXJ0eTpkZXNjcmlwdGlvbj4KCQkJCQkJCQkJPHBkZmFQcm9wZXJ0eTpuYW1lPnBhcnQ8L3BkZmFQcm9wZXJ0eTpuYW1lPgoJCQkJCQkJCQk8cGRmYVByb3BlcnR5OnZhbHVlVHlwZT5JbnRlZ2VyPC9wZGZhUHJvcGVydHk6dmFsdWVUeXBlPgoJCQkJCQkJCTwvcmRmOmxpPgoJCQkJCQkJCTxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgoJCQkJCQkJCQk8cGRmYVByb3BlcnR5OmNhdGVnb3J5PmludGVybmFsPC9wZGZhUHJvcGVydHk6Y2F0ZWdvcnk+CgkJCQkJCQkJCTxwZGZhUHJvcGVydHk6ZGVzY3JpcHRpb24+QW1lbmRtZW50IG9mIFBERi9BIHN0YW5kYXJkPC9wZGZhUHJvcGVydHk6ZGVzY3JpcHRpb24+CgkJCQkJCQkJCTxwZGZhUHJvcGVydHk6bmFtZT5hbWQ8L3BkZmFQcm9wZXJ0eTpuYW1lPgoJCQkJCQkJCQk8cGRmYVByb3BlcnR5OnZhbHVlVHlwZT5UZXh0PC9wZGZhUHJvcGVydHk6dmFsdWVUeXBlPgoJCQkJCQkJCTwvcmRmOmxpPgoJCQkJCQkJCTxyZGY6bGkgcmRmOnBhcnNlVHlwZT0iUmVzb3VyY2UiPgoJCQkJCQkJCQk8cGRmYVByb3BlcnR5OmNhdGVnb3J5PmludGVybmFsPC9wZGZhUHJvcGVydHk6Y2F0ZWdvcnk+CgkJCQkJCQkJCTxwZGZhUHJvcGVydHk6ZGVzY3JpcHRpb24+Q29uZm9ybWFuY2UgbGV2ZWwgb2YgUERGL0Egc3RhbmRhcmQ8L3BkZmFQcm9wZXJ0eTpkZXNjcmlwdGlvbj4KCQkJCQkJCQkJPHBkZmFQcm9wZXJ0eTpuYW1lPmNvbmZvcm1hbmNlPC9wZGZhUHJvcGVydHk6bmFtZT4KCQkJCQkJCQkJPHBkZmFQcm9wZXJ0eTp2YWx1ZVR5cGU+VGV4dDwvcGRmYVByb3BlcnR5OnZhbHVlVHlwZT4KCQkJCQkJCQk8L3JkZjpsaT4KCQkJCQkJCTwvcmRmOlNlcT4KCQkJCQkJPC9wZGZhU2NoZW1hOnByb3BlcnR5PgoJCQkJCTwvcmRmOmxpPgoJCQkJPC9yZGY6QmFnPgoJCQk8L3BkZmFFeHRlbnNpb246c2NoZW1hcz4KCQk8L3JkZjpEZXNjcmlwdGlvbj4KCTwvcmRmOlJERj4KPC94OnhtcG1ldGE+Cjw/eHBhY2tldCBlbmQ9InciPz4KZW5kc3RyZWFtCmVuZG9iagoxMSAwIG9iago8PCAvVHlwZSAvQ2F0YWxvZyAvVmVyc2lvbiAvMS43IC9QYWdlcyAxIDAgUiAvTmFtZXMgPDwgPj4gL1ZpZXdlclByZWZlcmVuY2VzIDw8IC9EaXJlY3Rpb24gL0wyUiA+PiAvUGFnZUxheW91dCAvU2luZ2xlUGFnZSAvUGFnZU1vZGUgL1VzZU5vbmUgL09wZW5BY3Rpb24gWzYgMCBSIC9GaXRIIG51bGxdIC9NZXRhZGF0YSAxMCAwIFIgPj4KZW5kb2JqCnhyZWYKMCAxMgowMDAwMDAwMDAwIDY1NTM1IGYgCjAwMDAwMDE5MjYgMDAwMDAgbiAKMDAwMDAyNzI1MiAwMDAwMCBuIAowMDAwMDAxOTg1IDAwMDAwIG4gCjAwMDAwMDIwOTEgMDAwMDAgbiAKMDAwMDAyNzM3NiAwMDAwMCBuIAowMDAwMDAwMDE1IDAwMDAwIG4gCjAwMDAwMDA0ODMgMDAwMDAgbiAKMDAwMDAwMjIwMiAwMDAwMCBuIAowMDAwMDI3NTkyIDAwMDAwIG4gCjAwMDAwMjc5ODYgMDAwMDAgbiAKMDAwMDAzMjM5MyAwMDAwMCBuIAp0cmFpbGVyCjw8IC9TaXplIDEyIC9Sb290IDExIDAgUiAvSW5mbyA5IDAgUiAvSUQgWyA8Yzc5M2YyYjgzYTBmZmNmMzUzZmQ0NDRmZmY3ZmU1NjA+IDxjNzkzZjJiODNhMGZmY2YzNTNmZDQ0NGZmZjdmZTU2MD4gXSA+PgpzdGFydHhyZWYKMzI2MDIKJSVFT0YK');

var pdfjsLib = window['pdfjs-dist/build/pdf'];

pdfjsLib.GlobalWorkerOptions.workerSrc = '//mozilla.github.io/pdf.js/build/pdf.worker.js';


var loadingTask = pdfjsLib.getDocument({data: pdfData});
loadingTask.promise.then(function(pdf) {
  console.log('PDF loaded');
  
 
  var pageNumber = 1;
  pdf.getPage(pageNumber).then(function(page) {
    console.log('Page loaded');
    
    var scale = 1.5;
    var viewport = page.getViewport({scale: scale});

    var canvas = document.getElementById('the-canvas');
    var context = canvas.getContext('2d');
    canvas.height = viewport.height;
    canvas.width = viewport.width;

    var renderContext = {
      canvasContext: context,
      viewport: viewport
    };
    var renderTask = page.render(renderContext);
    renderTask.promise.then(function () {
      console.log('Page rendered');
    });
  });
}, function (reason) {
 
  console.error(reason);
});
<?php
echo'</script>';



function generate_license($suffix = null) {
    // Default tokens contain no "ambiguous" characters: 1,i,0,o
    if(isset($suffix)){
        // Fewer segments if appending suffix
        $num_segments = 3;
        $segment_chars = 6;
    }else{
        $num_segments = 5;
        $segment_chars = 5;
    }
    $tokens = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $license_string = '';
    // Build Default License String
    for ($i = 0; $i < $num_segments; $i++) {
        $segment = '';
        for ($j = 0; $j < $segment_chars; $j++) {
            $segment .= $tokens[rand(0, strlen($tokens)-1)];
        }
        $license_string .= $segment;
        if ($i < ($num_segments - 1)) {
            $license_string .= '-';
        }
    }
    // If provided, convert Suffix
    if(isset($suffix)){
        if(is_numeric($suffix)) {   // Userid provided
            $license_string .= '-'.strtoupper(base_convert($suffix,10,36));
        }else{
            $long = sprintf("%u\n", ip2long($suffix),true);
            if($suffix === long2ip($long) ) {
                $license_string .= '-'.strtoupper(base_convert($long,10,36));
            }else{
                $license_string .= '-'.strtoupper(str_ireplace(' ','-',$suffix));
            }
        }
    }
    return $license_string;
}

class WP_Query_Multisite {
	function __construct() {
		add_filter('query_vars', array($this, 'query_vars'));
		add_action('pre_get_posts', array($this, 'pre_get_posts'), 100);
		add_filter('posts_clauses', array($this, 'posts_clauses'), 10, 2);
		add_filter('posts_request', array($this, 'posts_request'), 10, 2);
		add_action('the_post', array($this, 'the_post'));
		add_action('loop_end', array($this, 'loop_end'));
	}
	function query_vars($vars) {
		$vars[] = 'multisite';
		$vars[] = 'sites__not_in';
		$vars[] = 'sites__in';
		return $vars;
	}
	function pre_get_posts($query) {
		if($query->get('multisite')) {
			global $wpdb, $blog_id;
			$this->loop_end = false;
			$this->blog_id = $blog_id;
			$site_IDs = $wpdb->get_col( "select blog_id from $wpdb->blogs" );
			if ( $query->get('sites__not_in') )
				foreach($site_IDs as $key => $site_ID )
					if (in_array($site_ID, $query->get('sites__not_in')) ) unset($site_IDs[$key]);
			if ( $query->get('sites__in') )
				foreach($site_IDs as $key => $site_ID )
					if ( ! in_array($site_ID, $query->get('sites__in')) )
						unset($site_IDs[$key]);
			$site_IDs = array_values($site_IDs);
			$this->sites_to_query = $site_IDs;
		}
	}
	function posts_clauses($clauses, $query) {
		if($query->get('multisite')) {
			global $wpdb;
			// Start new mysql selection to replace wp_posts on posts_request hook
			$this->ms_select = array();
			$root_site_db_prefix = $wpdb->prefix;
			foreach($this->sites_to_query as $site_ID) {
				switch_to_blog($site_ID);
				$ms_select = $clauses['join'] . ' WHERE 1=1 '. $clauses['where'];
				if($clauses['groupby'])
					$ms_select .= ' GROUP BY ' . $clauses['groupby'];
				$ms_select = str_replace($root_site_db_prefix, $wpdb->prefix, $ms_select);
				$ms_select = " SELECT $wpdb->posts.*, '$site_ID' as site_ID FROM $wpdb->posts $ms_select ";
				$this->ms_select[] = $ms_select;
				restore_current_blog();
			}
			// Clear join, where and groupby to populate with parsed ms select on posts_request hook;
			$clauses['join'] = '';
			$clauses['where'] = '';
			$clauses['groupby'] = '';
			// Orderby for tables (not wp_posts)
			$clauses['orderby'] = str_replace($wpdb->posts, 'tables', $clauses['orderby']);
		}
		return $clauses;
	}
	function posts_request($sql, $query) {
		if($query->get('multisite')) {
			global $wpdb;
			// Clean up remanescent WHERE request
			$sql = str_replace('WHERE 1=1', '', $sql);
			// Multisite request
			$sql = str_replace("$wpdb->posts.* FROM $wpdb->posts", 'tables.* FROM ( ' . implode(" UNION ", $this->ms_select) . ' ) tables', $sql);
		}
		return $sql;
	}
	function the_post($post) {
		global $blog_id;
		if( isset( $this->loop_end ) && !$this->loop_end && $post->site_ID && $blog_id !== $post->site_ID) {
			switch_to_blog($post->site_ID);
		}
	}
	function loop_end($query) {
		global $switched;
		if($query->get('multisite')) {
			$this->loop_end = true;
			if($switched) {
				switch_to_blog($this->blog_id);
			}
		}
	}
}
new WP_Query_Multisite();

$args = array(
    'multisite' => '1',
    'tax_query' => array(
'taxonomy' => 'post_format',
'field' => 'slug',
'terms' => array('post-format-quote','post-format-audio','post-format-gallery','post-format-image','post-format-link','post-format-video'),
'operator' => 'NOT IN'
)
);

$query = new WP_Query( $args );
while($query->have_posts()) : $query->the_post();
echo "<a href='".get_the_permalink()."'>".get_the_title()."</a><br>";
endwhile; 
wp_reset_postdata();

//echo generate_license();
echo '<div class="btn-group d-flex" role="group" aria-label="Button group with nested dropdown">
  <button type="button" class="btn btn-secondary w-100">1</button>
  <button type="button" class="btn btn-secondary w-100">2</button>

  <div class="btn-group w-100" role="group">
    <button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle w-100" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      Dropdown
    </button>
    <div class="dropdown-menu w-100" aria-labelledby="btnGroupDrop1">
      <a class="dropdown-item w-100" href="#">Dropdown link</a>
      <a class="dropdown-item w-100" href="#">Dropdown link</a>
    </div>
  </div>
</div>';

echo '<div class="row bg-dark"><div class="col-8" style="padding: 0;margin: 0"><div class="jumbotron jumbotron-fluid" style="margin: 0">
  <div class="container">
    <h1 class="display-4">Fluid jumbotron</h1>
    <p class="lead">This is a modified jumbotron that occupies the entire horizontal space of its parent.</p>
  </div>
</div>
</div><div class="col-4 mh-100" style="padding: 0;margin: 0; height:100%"><div class="h-33 bg-warning" style="padding: 0;margin: 0">
test
</div><div class="h-33" style="padding: 0;margin: 0">
test
</div><div class="h-33" style="padding: 0;margin: 0">
test
</div></div>';
}

}
add_action( 'settings_doliconnect_settings', 'settings_module' );

function delete_menu($arg){
echo "<a href='".esc_url( add_query_arg( 'module', 'delete', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ($arg=='delete') { echo " active";}
echo "'>".__( 'Privacy', 'doliconnect' )."</a>";
}
add_action( 'settings_doliconnect_menu', 'delete_menu', 3, 1);

function delete_module($url){
global $current_user;
echo "<div class='card shadow-sm'><ul class='list-group list-group-flush'>";
if ( function_exists( 'wp_create_user_request' ) ) {
echo "<form action='".esc_url( admin_url( 'admin-ajax.php' ) )."' method='post' id='gdrf-form'>";
echo "<li class='list-group-item list-group-item-action flex-column align-items-start'><div class='custom-control custom-radio'>
<input id='export_personal_data' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='gdrf_data_type' value='export_personal_data' checked>";
echo "<label class='custom-control-label w-100' for='export_personal_data'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
echo "<center><i class='fas fa-download fa-3x fa-fw'></i></center>";
echo "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Export your personal data', 'doliconnect' )."</h6><small class='text-muted'>".__( 'You will receive an email with a secure link to your data', 'doliconnect' )."</small>";
echo '</div></div></label></div></li>';

echo "<li class='list-group-item list-group-item-action flex-column align-items-start disabled'><div class='custom-control custom-radio'>
<input id='remove_personal_data' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='gdrf_data_type' value='remove_personal_data' disabled>";
echo "<label class='custom-control-label w-100' for='remove_personal_data'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
echo "<center><i class='fas fa-eraser fa-3x fa-fw'></i></center>";
echo "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Remove your personal data', 'doliconnect' )."</h6><small class='text-muted'>".__( 'Soon, you will be able to erase your account', 'doliconnect' )."</small>";
echo '</div></div></label></div></li>';

echo "<li class='list-group-item list-group-item-action flex-column align-items-start disabled'><div class='custom-control custom-radio'>
<input id='remove_personal_data' onclick='ShowHideDiv()' class='custom-control-input' type='radio' name='gdrf_data_type' value='delete_personal_data' disabled>";
echo "<label class='custom-control-label w-100' for='remove_personal_data'><div class='row'><div class='col-3 col-md-2 col-xl-2 align-middle'>";
echo "<center><i class='fas fa-trash fa-3x fa-fw'></i></center>";
echo "</div><div class='col-9 col-md-10 col-xl-10 align-middle'><h6 class='my-0'>".__( 'Delete your account', 'doliconnect' )."</h6><small class='text-muted'>".__( 'Soon, you will be able to delete your account', 'doliconnect' )."</small>";
echo '</div></div></label></div></li>';

echo "</ul>";
echo "<div class='card-body'>";
echo "<input type='hidden' name='action' value='gdrf_data_request'><input type='hidden' id='gdrf_data_email' name='gdrf_data_email' value='".$current_user->user_email."'>
<input type='hidden' name='gdrf_data_nonce' id='gdrf_data_nonce' value='".wp_create_nonce( 'gdrf_nonce' )."' >";
echo "<button id='gdrf-submit-button' class='btn btn-danger btn-block' type='submit'><b>".__( 'Validate the request', 'doliconnect' )."</b></button></div></form>";
} else {
echo "</ul>";
} 
echo "</div>";  
echo "<p class='text-right'><small>";
echo dolihelp('ISSUE');
echo "</small></p>";
}
add_action( 'settings_doliconnect_delete', 'delete_module' );
?>