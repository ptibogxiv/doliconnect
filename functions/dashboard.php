<?php

if ( !defined("DOLIBUG") ) {
$propal = CallAPI("GET", "/doliconnector/constante/MAIN_MODULE_PROPALE", null, MONTH_IN_SECONDS);
$order = CallAPI("GET", "/doliconnector/constante/MAIN_MODULE_COMMANDE", null, MONTH_IN_SECONDS);
$contract = CallAPI("GET", "/doliconnector/constante/MAIN_MODULE_CONTRAT", null, MONTH_IN_SECONDS);
$member = CallAPI("GET", "/doliconnector/constante/MAIN_MODULE_ADHERENTSPLUS", null, MONTH_IN_SECONDS);
$memberconsumption = CallAPI("GET", "/doliconnector/constante/ADHERENT_CONSUMPTION", null, MONTH_IN_SECONDS);
$donation = CallAPI("GET", "/doliconnector/constante/MAIN_MODULE_DON", null, MONTH_IN_SECONDS);
$help = CallAPI("GET", "/doliconnector/constante/MAIN_MODULE_TICKET", null, MONTH_IN_SECONDS);
}

function informations_menu($arg) {
echo "<A href='".esc_url( add_query_arg( 'module', 'informations', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ($arg=='informations') { echo " active";}
echo "'>".__( 'Personal informations', 'doliconnect' )."</a>";
}
add_action( 'user_doliconnect_menu', 'informations_menu', 1, 1);

function informations_module($url) {
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

if( has_action('mydoliconnectuserform') ) {
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

function avatars_module($url) {
global $wpdb,$current_user;

$ID = $current_user->ID;
$time = current_time( 'timestamp', 1);

require_once ABSPATH . WPINC . '/class-phpass.php';

if ( $_POST["case"] == 'updateavatar' ) {

if ( $_POST['inputavatar']=='delete' ) {

$upload_dir = wp_upload_dir();
$nam=$wpdb->prefix."member_photo";

$files = glob($upload_dir['basedir']."/doliconnect/".$ID."/*");
foreach($files as $file){
if(is_file($file))
unlink($file); 
}

delete_usermeta( $ID, $nam,$current_user->$nam);

if ( constant("DOLIBARR_MEMBER") > 0 ) {
$data = [
    'photo' => ''
	];
$adherent = CallAPI("PUT", "/adherentsplus/".constant("DOLIBARR_MEMBER"), $data, DAY_IN_SECONDS);
}

} elseif ( $_FILES['inputavatar']['tmp_name'] != null ) {
$types = array('image/jpeg', 'image/jpg');
if ( $_FILES['inputavatar']['tmp_name'] != null ) {
list($width, $height) = getimagesize($_FILES['inputavatar']['tmp_name']);
}
if ( ( $width >= '350' && $height >= '350' ) && ( isset($_FILES['inputavatar']['tmp_name'])) && (in_array($_FILES['inputavatar']['type'], $types)) && ($_FILES['inputavatar']['size'] <= 10000000)) {

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
 
if ( ! is_wp_error( $img ) ) {
$exif = exif_read_data($filename);               
if ($exif[Orientation] == '8') {
$img->rotate( 90 );
} elseif ( $exif[Orientation] == '3' ) {
$img->rotate( 180 );
} elseif ( $exif[Orientation] == '6' ) {
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
if ( file_exists($filename) ) {
unlink($filename);
}
}

$minifile=$upload_dir['basedir']."/doliconnect/".$ID."/avatar-$time-72x72.jpg";
$smallfile=$upload_dir['basedir']."/doliconnect/".$ID."/avatar-$time-150x150.jpg";
$avatarfile=$upload_dir['basedir']."/doliconnect/".$ID."/avatar-$time.jpg";

if ( file_exists($avatarfile) ) {
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
if ( file_exists($minifile) ) {
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
if ( file_exists($smallfile) ) {
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

 
if ( constant("DOLIBARR_MEMBER") > 0 ) {
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
if ( null == $current_user->$nam && constant("DOLIBARR_MEMBER") ) {
//echo " required='required'";
}
echo " capture><label class='custom-file-label' for='customFile' data-browse='".__( 'Browse', 'doliconnect' )."'>".__( 'Select a file', 'doliconnect' )."</label></div></div>
<small id='infoavatar' class='form-text text-muted text-justify'>".__( 'Your avatar must be a .jpg/.jpeg file, <10Mo and 350x350pixels minimum.', 'doliconnect' )."</SMALL>";
echo "<div class='custom-control custom-checkbox my-1 mr-sm-2'>
    <input type='checkbox' class='custom-control-input' id='inputavatar' name='inputavatar' value='delete' ";
if ( null == $current_user->$nam ) {
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

function contacts_menu($arg) {
echo "<a href='".esc_url( add_query_arg( 'module', 'contacts', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ($arg=='contacts') { echo " active";}
echo "'>".__( 'Address book', 'doliconnect' )."</a>";
}
add_action( 'user_doliconnect_menu', 'contacts_menu', 2, 1);

function contacts_module($url){
global $current_user;
$delay = WEEK_IN_SECONDS;

if ( $_POST['contact'] == 'new_contact' ) {

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

if ( isset($listcontact->error) || $listcontact == null ) { echo " checked "; }

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

if ( isset($civility) ) { 

echo "<select class='custom-select' id='identity'  name='billing_civility' required>";
foreach ($civility as $postv) {

echo "<option value='".$postv->code."' ";
if ( $current_user->billing_civility == $postv->code && $current_user->billing_civility != null ) {
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
echo "</div></div></li>";

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
} elseif ( !$wp_hasher->CheckPassword($plain_password, $password_hashed) ) {
$msg = "<div class='alert alert-danger'><h4 class='alert-heading'>".__( 'Oops!', 'doliconnect' )."</h4><p>".__( 'Your actual password is incorrect', 'doliconnect' )."</p></div>";
} elseif ( $pwd != $_POST["pwd2"] ) {
$msg = "<div class='alert alert-danger'><h4 class='alert-heading'>".__( 'Oops!', 'doliconnect' )."</h4><p>".__( 'The new passwords entered are different', 'doliconnect' )."</p></div>";
} elseif ( !preg_match("#.*^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#", $pwd) ) {
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

if ( $orderfo->billed != 1 && $orderfo->statut > 0 ) {

if ( function_exists('dolipaymentmodes') ) {

$change = "<small><a href='#' id='button-source-payment' data-toggle='modal' data-target='#orderonlinepay'><span class='fas fa-sync-alt'></span> ".__( 'Change your payment mode', 'doliconnect' )."</a></small>";

echo "<div class='modal fade' id='orderonlinepay' tabindex='-1' role='dialog' aria-labelledby='orderonlinepayLabel' aria-hidden='true'  aria-hidden='true' data-backdrop='static' data-keyboard='false'>
<div class='modal-dialog modal-dialog-centered' role='document'><div class='modal-content'><div class='modal-header border-0'><h4 class='modal-title border-0' id='orderonlinepayLabel'>".__( 'Payment methods', 'doliconnect' )."</h4>
<button id='closemodalonlinepay' type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div><div class='modal-body'>";

$listsource = CallAPI("GET", "/doliconnector/".constant("DOLIBARR")."/sources", null, dolidelay($delay, $_GET["refresh"]));
//echo $listsource;

if ( !empty($orderfo->paymentintent) ) {
dolipaymentmodes( $listsource, $orderfo, $url, $url);
} else {
doligateway($listsource, $orderfo->ref, $orderfo->multicurrency_total_ttc?$orderfo->multicurrency_total_ttc:$orderfo->total_ttc, $orderfo->multicurrency_code, $url.'&id='.$_GET['id'].'&ref='.$_GET['ref'], 'full');
echo doliloading('paymentmodes'); }

echo "</div></div></div></div>";

} else {

$change = "<a href='".get_site_option('dolibarr_public_url')."/public/payment/newpayment.php?source=".esc_attr($_GET['module'])."&ref=".esc_attr($_GET['ref'])."&securekey=".sha1(md5('nw38LmcS3tgow7D1tGZGiBr56GPK059Q' . esc_attr($_GET['module']) . esc_attr($_GET['ref'])))."&entity=".dolibarr_entity()."' target='_blank'><span class='fa fa-credit-card'></span> ".__( 'Pay online', 'doliconnect' )."</a>";

}

if ( $orderfo->mode_reglement_code == 'CHQ' ) {
$chq = CallAPI("GET", "/doliconnector/constante/FACTURE_CHQ_NUMBER", null, dolidelay(MONTH_IN_SECONDS, esc_attr($_GET["refresh"])));

$bank = CallAPI("GET", "/bankaccounts/".$chq->value, null, dolidelay(MONTH_IN_SECONDS, esc_attr($_GET["refresh"])));

echo "<div class='alert alert-danger' role='alert'><p align='justify'>Merci d'envoyer un chèque d'un montant de <b>".doliprice($orderfo->multicurrency_total_ttc?$orderfo->multicurrency_total_ttc:$orderfo->total_ttc,$orderfo->multicurrency_code)."</b> libellé à l'ordre de <b>$bank->proprio</b> sous <b>15 jours</b> en rappelant votre référence <b>$ref</b> à l'adresse suivante :</p><p><b>$bank->owner_address</b></p>$change</div>";
} elseif ( $orderfo->mode_reglement_code == 'VIR' ) { 
$vir = CallAPI("GET", "/doliconnector/constante/FACTURE_RIB_NUMBER", null, dolidelay(MONTH_IN_SECONDS, esc_attr($_GET["refresh"])));

$bank = CallAPI("GET", "/bankaccounts/".$vir->value, null, dolidelay(MONTH_IN_SECONDS, esc_attr($_GET["refresh"])));

echo "<div class='alert alert-danger' role='alert'><p align='justify'>Merci d'effectuer un virement d'un montant de <b>".doliprice($orderfo->multicurrency_total_ttc?$orderfo->multicurrency_total_ttc:$orderfo->total_ttc,$orderfo->multicurrency_code)."</b> sous <b>15 jours</b> en rappelant votre référence <b>$ref</b> sur le compte suivant :</p><p><b>IBAN : $bank->iban</b>";
if ( ! empty($bank->bic) ) { echo "<br><b>BIC/SWIFT : $bank->bic</b>";}
echo "</p>$change</div>";
} else {
echo "<button type='button' id='button-source-payment' class='btn btn-warning btn-block' data-toggle='modal' data-target='#orderonlinepay'><span class='fa fa-credit-card'></span> ".__( 'Pay', 'doliconnect' )."</button><br>";
}

}

echo "</div></div>";
echo '<div class="progress"><div class="progress-bar bg-success" role="progressbar" style="width: '.$orderavancement.'%" aria-valuenow="'.$orderavancement.'" aria-valuemin="0" aria-valuemax="100"></div></div>';
echo "<div class='w-auto text-muted d-none d-sm-block' ><div style='display:inline-block;width:20%'>".__( 'Order', 'doliconnect' )."</div><div style='display:inline-block;width:15%'>".__( 'Payment', 'doliconnect' )."</div><div style='display:inline-block;width:25%'>".__( 'Processing', 'doliconnect' )."</div><div style='display:inline-block;width:20%'>".__( 'Shipping', 'doliconnect' )."</div><div class='text-right' style='display:inline-block;width:20%'>".__( 'Delivery', 'doliconnect' )."</div></div>";

echo "</div><ul class='list-group list-group-flush'>";
 
if ( $orderfo->lines != null ) {
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
$document_order = dolidocdownload($doc[2],$doc[1],$doc[0],$url."&id=".$_GET['id']."&ref=".$orderfo->ref,__( 'Summary', 'doliconnect' ));
} 
    
$fruits[$orderfo->date_commande.o] = array(
"timestamp" => $orderfo->date_creation,
"type" => __( 'Order', 'doliconnect' ),  
"label" => $orderfo->ref,
"document" => $document_order,
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
$document_invoice=dolidocdownload($doc[2],$doc[1],$doc[0],$url."&id=".$_GET['id']."&ref=".$orderfo->ref,__( 'Invoice', 'doliconnect' ));
}  
  
$fruits[$invoice->date_creation.i] = array(
"timestamp" => $invoice->date_creation,
"type" => __( 'Invoice', 'doliconnect' ),  
"label" => $invoice->ref,
"document" => $document_invoice,
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
echo dolirefresh("/contracts/".$_GET['id'], $url."&id=".$_GET['id']."&ref=".$_GET['ref'], $delay);
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
$adherent = dolimembership($_POST["update_membership"], $_POST["typeadherent"], dolidelay($delay, true));

//if ($statut==1) {
$msg = "<div class='alert alert-success' role='alert'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button><p><strong>".__( 'Congratulations!', 'doliconnect' )."</strong> ".__( 'Your membership has been updated.', 'doliconnect' )."</p></div>";
//}

if ( ($_POST["update_membership"]==4) && $_POST["cotisation"] && constant("DOLIBARR_MEMBER") > 0 && $_POST["timestamp_start"] > 0 && $_POST["timestamp_end"] > 0 ) {

$productadhesion = CallAPI("GET", "/doliconnector/constante/ADHERENT_PRODUCT_ID_FOR_SUBSCRIPTIONS", null, MONTH_IN_SECONDS);

addtodolibasket($productadhesion->value, 1, $_POST["cotisation"], $_POST["timestamp_start"], $_POST["timestamp_end"]);
wp_redirect(esc_url(doliconnecturl('dolicart')));
exit;     
} elseif ($_POST["update_membership"]==5 || $_POST["update_membership"]==1) {
$dolibarr = CallAPI("GET", "/doliconnector/".$ID, null, 0); 
}

} 

echo $msg."<div class='card shadow-sm'><div class='card-body'><div class='row'><div class='col-12 col-md-5'>";

if ( !empty(constant("DOLIBARR_MEMBER")) && constant("DOLIBARR_MEMBER") > 0  && constant("DOLIBARR") > 0 ) { 
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
if ( (current_time('timestamp') > $adherent->datecommitment) || null == $adherent->datecommitment ) { echo  __( 'no', 'doliconnect' );
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

if ( $adherent->datefin == null && $adherent->statut == '0' ) {echo  "<a href='#' id='subscribe-button2' class='btn btn text-white btn-warning btn-block' data-toggle='modal' data-target='#activatemember'><b>".__( 'Become a member', 'doliconnect' )."</b></a>";
} elseif ($adherent->statut == '1') {
if ( $time > $adherent->next_subscription_renew && $adherent->datefin != null ) {
echo "<a class='btn btn text-white btn-warning btn-block' data-toggle='modal' data-target='#activatemember'><b>".__( 'Renew my subscription', 'doliconnect' )."</b></a>";
} elseif ( ( $adherent->datefin + 86400 ) > $time ) {
echo  "<a href='#' id='subscribe-button2' class='btn btn text-white btn-warning btn-block' data-toggle='modal' data-target='#activatemember'><b>".__( 'Modify my subscription', 'doliconnect' )."</b></a>";
}else { echo  "<button class='btn btn btn-danger btn-block' data-toggle='modal' data-target='#activatemember'><b>".__( 'Pay my subscription', 'doliconnect' )."</b></button>";}
} elseif ( $adherent->statut == '0' ) {
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
echo dolirefresh("/adherentsplus/".constant("DOLIBARR_MEMBER"), $url, $delay);
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

if ( is_object($donation) && $donation->value == '1' && ( get_option('doliconnectbeta')=='1' ) ) {
add_action( 'options_doliconnect_menu', 'donation_menu', 3, 1);
add_action( 'options_doliconnect_donation', 'donation_module' );
}  

function donation_menu( $arg ) {
echo "<a href='".esc_url( add_query_arg( 'module', 'donation', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ($arg=='donation') { echo " active";}
echo "'>".__( 'Donation', 'doliconnect' )."</a>";
}

function donation_module( $url ) {
global $wpdb,$current_user;
$entity = get_current_blog_id();
$ID = $current_user->ID;

echo "<div class='card shadow-sm'>";


echo "<ul class='list-group list-group-flush'><li class='list-group-item'>";

echo "developpement en cours";

echo "</li></ul></div>";

echo "<small><div class='float-left'>";
echo dolirefresh("/donation/".constant("DOLIBARR"), $url, $delay);
echo "</div><div class='float-right'>";
echo dolihelp('ISSUE');
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

if ( isset($severity) ) { 
$sv= __( 'Critical / blocking', 'doliconnect' ).__( 'High', 'doliconnect' ).__( 'Normal', 'doliconnect' ).__( 'Low', 'doliconnect' );
echo "<select class='custom-select' id='ticket_severity'  name='ticket_severity'>";
foreach ( $severity as $postv ) {
echo "<option value='".$postv->code."' ";
if ( $postv->use_default == 1 ) {
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
if ( $current_user->loginmailalert == 'on' ) { echo " checked"; }        
echo " onChange='demo()' ><label class='custom-control-label w-100' for='loginmailalert'> ".__( 'Receive a email notification at each connection', 'doliconnect' )."</label>
</div></li>";
echo "<li class='list-group-item'><div class='custom-control custom-switch'><input type='checkbox' class='custom-control-input' name='optin1' id='optin1' ";
if ( $current_user->optin1 == 'on' ) { echo " checked"; }        
echo " onChange='demo()' ><label class='custom-control-label w-100' for='optin1'> ".__( 'I would like to receive the newsletter', 'doliconnect' )."</label>
</div></li>";
echo "<li class='list-group-item'><div class='custom-control custom-switch'><input type='checkbox' class='custom-control-input' name='optin2' id='optin2' ";
if ( $current_user->optin2 == 'on' ) { echo " checked"; }        
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
if ( function_exists('pll_the_languages') ) { 
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

if ( !empty($thirdparty->multicurrency_code) ) { 
echo "<li class='list-group-item'>";
//echo $current_user->locale;
echo "<div class='form-group'><label for='inputaddress'><small>".__( 'Default currency', 'doliconnect' )."</small></label>
<div class='input-group'><div class='input-group-prepend'><span class='input-group-text'><i class='fas fa-money-bill-alt fa-fw'></i></span></div>";
echo "<select class='form-control' id='multicurrency_code' name='multicurrency_code' onChange='demo()' >";
echo "<option value='".$thirdparty->multicurrency_code."'>".$thirdparty->multicurrency_code." / ".doliprice(0,$thirdparty->multicurrency_code)."</option>";
echo "</select>";
echo "</div></div>";
echo "<input type='hidden' name='case' value='updatesettings'></li>";
}

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

}

}
add_action( 'settings_doliconnect_settings', 'settings_module' );

function delete_menu($arg) {
echo "<a href='".esc_url( add_query_arg( 'module', 'delete', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ($arg=='delete') { echo " active";}
echo "'>".__( 'Privacy', 'doliconnect' )."</a>";
}
add_action( 'settings_doliconnect_menu', 'delete_menu', 3, 1);

function delete_module($url) {
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