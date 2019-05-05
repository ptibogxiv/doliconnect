<?php

if ( !defined("DOLIBUG") ) {
$proposal = callDoliApi("GET", "/doliconnector/constante/MAIN_MODULE_PROPALE", null, dolidelay('constante'));
$order = callDoliApi("GET", "/doliconnector/constante/MAIN_MODULE_COMMANDE", null, dolidelay('constante'));
$contract = callDoliApi("GET", "/doliconnector/constante/MAIN_MODULE_CONTRAT", null, dolidelay('constante'));
$member = callDoliApi("GET", "/doliconnector/constante/MAIN_MODULE_ADHERENTSPLUS", null, dolidelay('constante'));
$memberconsumption = callDoliApi("GET", "/doliconnector/constante/ADHERENT_CONSUMPTION", null, dolidelay('constante'));
$linkedmember = callDoliApi("GET", "/doliconnector/constante/ADHERENT_LINKEDMEMBER", null, dolidelay('constante'));
$donation = callDoliApi("GET", "/doliconnector/constante/MAIN_MODULE_DON", null, dolidelay('constante'));
$help = callDoliApi("GET", "/doliconnector/constante/MAIN_MODULE_TICKET", null, dolidelay('constante'));
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

$request = "/thirdparties/".doliconnector($current_user, 'fk_soc');

$thirdparty=$_POST['thirdparty'][''.doliconnector($current_user, 'fk_soc').''];

if ( isset($_POST["case"]) && $_POST["case"] == 'updateuser' ) {
wp_update_user( array( 'ID' => $ID, 'user_email' => sanitize_email($thirdparty['email'])));
wp_update_user( array( 'ID' => $ID, 'nickname' => sanitize_user($_POST['user_nicename'])));
wp_update_user( array( 'ID' => $ID, 'display_name' => ucfirst(strtolower($thirdparty['firstname']))." ".strtoupper($thirdparty['lastname'])));
wp_update_user( array( 'ID' => $ID, 'first_name' => ucfirst(sanitize_user(strtolower($thirdparty['firstname'])))));
wp_update_user( array( 'ID' => $ID, 'last_name' => strtoupper(sanitize_user($thirdparty['lastname']))));
wp_update_user( array( 'ID' => $ID, 'description' => sanitize_textarea_field($_POST['description'])));
wp_update_user( array( 'ID' => $ID, 'user_url' => sanitize_textarea_field($thirdparty['url'])));
update_user_meta( $ID, 'civility_id', sanitize_text_field($thirdparty['civility_id']));
update_user_meta( $ID, 'billing_type', sanitize_text_field($thirdparty['morphy']));
if ( isset($thirdparty['name']) ) { update_user_meta( $ID, 'billing_company', sanitize_text_field($thirdparty['name'])); }
update_user_meta( $ID, 'billing_birth', $thirdparty['birth']);

do_action('wp_dolibarr_sync', $thirdparty);

if ( isset($_GET['return']) ) {
wp_redirect(doliconnecturl('doliaccount').'?module='.$_GET['return']);
exit;
} else {
$msg = "<div class='alert alert-success'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button><p><strong>".__( 'Congratulations!', 'doliconnect' )."</strong> ".__( 'Your informations have been updated.', 'doliconnect' )."</p></div>";
}
}

if ( isset($_GET['return']) ) {
$url = esc_url( add_query_arg( 'return', $_GET['return'], $url) );
}

if ( doliconnector($current_user, 'fk_soc') > '0' ) {
$thirdparty = callDoliApi("GET", $request, null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));  
}

echo "<form action='".$url."' id='informations-form' method='post' class='was-validated' enctype='multipart/form-data'><input type='hidden' name='case' value='updateuser'>";

if ( isset($msg) ) { echo $msg; }

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
echo "</script>";

echo "<div class='card shadow-sm'>";

echo doliconnectuserform( $thirdparty, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), true), 'thirdparty');

echo "<div class='card-body'><input type='hidden' name='userid' value='$ID'><button class='btn btn-danger btn-block' type='submit'><b>".__( 'Update', 'doliconnect' )."</b></button></div>";
echo "</div></form>";

echo "<small><div class='float-left'>";
echo dolirefresh($request, $url, dolidelay('thirdparty'), $thirdparty);
echo "</div><div class='float-right'>";
echo dolihelp('ISSUE');
echo "</div></small>";

}
add_action( 'user_doliconnect_informations', 'informations_module');

function avatars_module($url) {
global $wpdb,$current_user;

$ID = $current_user->ID;
$time = current_time( 'timestamp', 1);

require_once ABSPATH . WPINC . '/class-phpass.php';

if ( isset($_POST["case"]) && $_POST["case"] == 'updateavatar' ) {

if ( $_POST['inputavatar']=='delete' ) {

$upload_dir = wp_upload_dir();
$nam=$wpdb->prefix."member_photo";

$files = glob($upload_dir['basedir']."/doliconnect/".$ID."/*");
foreach($files as $file){
if(is_file($file))
unlink($file); 
}

delete_usermeta( $ID, $nam,$current_user->$nam);

if ( doliconnector($current_user, 'fk_member') > 0 ) {
$data = [
    'photo' => ''
	];
$adherent = callDoliApi("PUT", "/adherentsplus/".doliconnector($current_user, 'fk_member'), $data, dolidelay('member'));
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
update_user_meta( $_POST["userid"], $wpdb->prefix."member_photo","avatar-$time.jpg");
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
  'ref' => doliconnector($current_user, 'fk_member'),
  'subdir' => 'photos',
  'filecontent' => $imgData,
  'fileencoding' => 'base64',
  'overwriteifexists'=> 1
	];
$photo = callDoliApi("POST", "/documents/upload", $datat, 0);
}
if ( file_exists($minifile) ) {
$imgData = base64_encode(file_get_contents("$minifile"));
$datat = [
  'filename' => 'avatar_mini.jpg',
  'modulepart' => 'member',
  'subdir' => doliconnector($current_user, 'fk_member').'/photos/thumbs',
  'filecontent' => $imgData,
  'fileencoding' => 'base64',
  'overwriteifexists'=> 1
	];
$photo = callDoliApi("POST", "/documents/upload", $datat, 0);
}
if ( file_exists($smallfile) ) {
$imgData = base64_encode(file_get_contents("$smallfile"));
$datat = [
  'filename' => 'avatar_small.jpg',
  'modulepart' => 'member',
  'subdir' => doliconnector($current_user, 'fk_member').'/photos/thumbs',
  'filecontent' => $imgData,
  'fileencoding' => 'base64',
  'overwriteifexists'=> 1
	];
$photo = callDoliApi("POST", "/documents/upload", $datat, 0);
}

 
if ( doliconnector($current_user, 'fk_member') > 0 ) {
$data = [
    'photo' => 'avatar.jpg'
	];
$adherent = callDoliApi("PUT", "/adherentsplus/".doliconnector($current_user, 'fk_member'), $data, dolidelay('member'));
}

} else {
$msg .= "<div class='alert alert-warning'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button><p><strong>".__( 'Oops', 'doliconnect' )."</strong> Votre photo n'a pu être chargée. Elle doit obligatoirement être au format .jpg et faire moins de 10 Mo. Taille minimum requise 350x350 pixels.</p></div>";
}
}

$msg .= "<div class='alert alert-success'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button><p><strong>".__( 'Congratulations!', 'doliconnect' )."</strong> ".__( 'Your informations have been updated.', 'doliconnect' )."</p></div>";   
}

echo "<form action='".$url."' id='avatar-form' method='post' class='was-validated' enctype='multipart/form-data'><input type='hidden' name='case' value='updateavatar'>";

if ( isset($msg) ) { echo $msg; }

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
echo "</script>";

echo "<div class='card shadow-sm'><ul class='list-group list-group-flush'>";
echo "<li class='list-group-item'>";
echo "<label for='description'><small>".__( 'Profile Picture', 'doliconnect' )."</small></label><div class='form-group'>
<div class='input-group mb-2'><div class='input-group-prepend'><span class='input-group-text'><i class='fas fa-camera fa-fw'></i></span></div><div class='custom-file'>
<input type='file' name='inputavatar' class='custom-file-input' id='customFile' accept='image/*' ";
$table_prefix = $wpdb->get_blog_prefix( get_current_blog_id() ); 
$upload_dir = wp_upload_dir();
$nam=$table_prefix."member_photo";
if ( null == $current_user->$nam && doliconnector($current_user, 'fk_member') ) {
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
echo "</ul><div class='card-body'><input type='hidden' name='userid' value='$ID'><button class='btn btn-danger btn-block' type='submit'><b>".__( 'Update', 'doliconnect' )."</b></button></div>";
echo "</div></form>";

echo '<div class="input-group">
  <div class="input-group-prepend">
    <div class="input-group-text">
      <input type="radio" id="customRadio1" name="customRadio" aria-label="Radio button for following text input">
    </div>
  </div>
  <input type="file" class="form-control" id="customFile">
    <div class="input-group-prepend">
    <div class="input-group-text">
      <input type="radio" id="customRadio1" name="customRadio" aria-label="Radio button for following text input">
    </div>
  </div>
  <input type="text" class="form-control" aria-label="Text input with radio button">
</div>';

echo "<p class='text-right'><small>";
echo dolihelp('ISSUE');
echo "</small></p>";

}
add_action( 'user_doliconnect_avatars', 'avatars_module');

function contacts_menu($arg) {
echo "<a href='".esc_url( add_query_arg( 'module', 'contacts', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ( $arg == 'contacts' ) { echo " active"; }
echo "'>".__( 'Manage address book', 'doliconnect' )."</a>";
}
add_action( 'user_doliconnect_menu', 'contacts_menu', 2, 1);

function contacts_module($url){
global $current_user;

$request = "/contacts?sortfield=t.rowid&sortorder=ASC&limit=100&thirdparty_ids=".doliconnector($current_user, 'fk_soc')."&includecount=1&sqlfilters=t.statut=1";

if ( isset ($_POST['add_contact']) && $_POST['add_contact'] == 'new_contact' ) {
$contactv=$_POST['contact'][''.doliconnector($current_user, 'fk_soc').''];
$data = [
    'civility_id'  => $contactv['civility_id'],     
    'firstname' => ucfirst(sanitize_user(strtolower($contactv['firstname']))),
    'lastname' => strtoupper(sanitize_user($contactv['lastname'])),
    'socid' => doliconnector($current_user, 'fk_soc'),
    'poste' => sanitize_textarea_field($contactv['poste']), 
    'address' => sanitize_textarea_field($contactv['address']),    
    'zip' => sanitize_text_field($contactv['zip']),
    'town' => sanitize_text_field($contactv['town']),
    'country_id' => sanitize_text_field($contactv['country_id']),
    'email' => sanitize_email($contactv['email']),
    'birthday' => $contactv['birth'],
    'phone_pro' => sanitize_text_field($contactv['phone'])
	];
$contactv = callDoliApi("POST", "/contacts", $data, 0);
$listcontact = callDoliApi("GET", $request, null, dolidelay('contact', true));
if ( $contactv > 0 ) {
$msg = "<div class='alert alert-success'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button><p><strong>".__( 'Congratulations!', 'doliconnect' )."</strong> ".__( 'Your informations have been updated.', 'doliconnect' )."</p></div>";
}
} elseif ( isset ($_POST['delete_contact']) && $_POST['delete_contact'] > 0 ) {
$contactv = callDoliApi("GET", "/contacts/".$_POST['delete_contact'], null, 0);
if ( $contactv->socid == doliconnector($current_user, 'fk_soc') ) {
// try deleting
$delete = callDoliApi("DELETE", "/contacts/".$contactv->id, null, 0);

$msg = "<div class='alert alert-success'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button><p><strong>".__( 'Congratulations!', 'doliconnect' )."</strong> ".__( 'Your informations have been updated.', 'doliconnect' )."</p></div>";

} else {
// fail deleting
}
$listcontact = callDoliApi("GET", $request, null, dolidelay('contact', true));
} elseif ( isset ($_POST['update_contact']) && $_POST['update_contact'] > 0 ) {
$contactv=$_POST['contact'][''.$_POST['update_contact'].''];
$data = [
    'civility_id'  => $contactv['civility_id'],     
    'firstname' => ucfirst(sanitize_user(strtolower($contactv['firstname']))),
    'lastname' => strtoupper(sanitize_user($contactv['lastname'])),
    'socid' => doliconnector($current_user, 'fk_soc'),
    'poste' => sanitize_textarea_field($contactv['poste']), 
    'address' => sanitize_textarea_field($contactv['address']),    
    'zip' => sanitize_text_field($contactv['zip']),
    'town' => sanitize_text_field($contactv['town']),
    'country_id' => sanitize_text_field($contactv['country_id']),
    'email' => sanitize_email($contactv['email']),
    'birthday' => $contactv['birth'],
    'phone_pro' => sanitize_text_field($contactv['phone'])
	];
$contactv = callDoliApi("PUT", "/contacts/".$_POST['update_contact'], $data, 0);
if ( $contactv->socid == doliconnector($current_user, 'fk_soc') ) {
// try deleting

$msg = "<div class='alert alert-success'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button><p><strong>".__( 'Congratulations!', 'doliconnect' )."</strong> ".__( 'Your informations have been updated.', 'doliconnect' )."</p></div>";

} else {
// fail deleting
}
$listcontact = callDoliApi("GET", $request, null, dolidelay('contact', true));
} else {

$listcontact = callDoliApi("GET", $request, null, dolidelay('contact', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

}

if ( doliconnector($current_user, 'fk_soc') > 0 ) {
$thirdparty = callDoliApi("GET", "/thirdparties/".doliconnector($current_user, 'fk_soc'), null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));  
}

echo "<form role='form' action='$url' id='contact-form' method='post'>";
//$nonce = wp_create_nonce( 'my-nonce' );

// This code would go in the target page.
// We need to verify the nonce.
//$nonce = $nonce;//$_REQUEST['_wpnonce'];
//if ( ! wp_verify_nonce( $nonce, 'my-nonce' ) ) {
    // This nonce is not valid.
 //   die( 'Security check' ); 
//} else {

//echo $nonce;
//} 

if ( isset($msg) ) { echo $msg; }                       

echo "<script>";
?>

window.setTimeout(function() {
    $(".alert").fadeTo(500, 0).slideUp(500, function(){
        $(this).remove(); 
    });
}, 5000);

var form = document.getElementById('contact-form'); 

form.addEventListener('submit', function(event) {

jQuery('#DoliconnectLoadingModal').modal('show');
jQuery(window).scrollTop(0);
console.log("submit");
form.submit();

});

<?php
echo "</script>";

echo "<div class='card shadow-sm'><ul class='list-group list-group-flush'>";

class myCounter implements Countable {
	public function count() {
		static $count = 0;
		return ++$count;
	}
}
 
$counter = new myCounter;

if ( count($counter) < 5 ) {
echo '<button type="button" class="list-group-item lh-condensed list-group-item-action list-group-item-primary" data-toggle="modal" data-target="#addcontactadress"><center><i class="fas fa-plus-circle"></i> '.__( 'New contact', 'doliconnect' ).'</center></button>';
}

if ( !isset($listcontact->error) && $listcontact != null ) {
foreach ( $listcontact as $contact ) { 
$count=$contact->ref_facturation+$contact->ref_contrat+$contact->ref_commande+$contact->ref_propal;
echo "<li class='list-group-item d-flex justify-content-between lh-condensed list-group-item-action'>";
echo "<div class='d-none d-md-block col-md-2 col-lg-1'><i class='fas fa-address-card $color fa-3x fa-fw'></i></div><h6 class='my-0'>".($contact->civility ? $contact->civility : $contact->civility_code)." ".$contact->firstname." ".$contact->lastname;
if ( !empty($contact->default) ) { echo " <i class='fas fa-star fa-1x fa-fw' style='color:Gold'></i>"; }
if ( !empty($contact->poste) ) { echo "<br>".$contact->poste; }
echo "</h6>";
echo "<small class='text-muted'>".$contact->address."<br>".$contact->zip." ".$contact->town." - ".$contact->country."<br>".$contact->email." ".$contact->phone_pro."</small>";
if (1 == 1) {
echo "<div class='col-4 col-sm-3 col-md-2 btn-group-vertical' role='group'>";
echo "<button type='button' class='btn btn-light text-primary' data-toggle='modal' data-target='#contact-".$contact->id."' title='".__( 'Edit', 'doliconnect' )." ".$contact->firstname." ".$contact->lastname."'><i class='fas fa-edit fa-fw'></i></a>
<button name='delete_contact' value='".$contact->id."' class='btn btn-light text-danger' type='submit' title='".__( 'Delete', 'doliconnect' )." ".$contact->firstname." ".$contact->lastname."'><i class='fas fa-trash fa-fw'></i></button>";
echo "</div>";
}
echo "</li>";
}
} else {
echo "<li class='list-group-item list-group-item-light'><center>".__( 'No contact', 'doliconnect' )."</center></li>";
}
echo "</ul></div></form>";

if ( !isset($listcontact->error) && $listcontact != null ) {
foreach ( $listcontact as $contact ) { 
echo "<form class='was-validated' role='form' action='$url' id='contact-".$contact->id."-form' method='post'>";
echo '<div class="modal fade" id="contact-'.$contact->id.'" tabindex="-1" role="dialog" aria-labelledby="contact-'.$contact->id.'Title" aria-hidden="true">
<div class="modal-dialog modal-lg modal-dialog-centered" role="document"><div class="modal-content"><div class="modal-header">
<h5 class="modal-title" id="contact-'.$contact->id.'Title">'.__( 'Update contact', 'doliconnect' ).'</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
<div class="modal-body">';
echo doliconnectuserform($contact, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), true), 'contact');      
echo "</div>
<div class='modal-footer'><button name='update_contact' value='".$contact->id."' class='btn btn-warning btn-block' type='submit'><b>".__( 'Update', 'doliconnect' )."</b></button></form></div>
</div></div></div>";
}}

if ( count($counter) < 5 ) {
echo "<form class='was-validated' role='form' action='$url' id='contact-add-form' method='post'>";
echo "<div class='modal fade' id='addcontactadress' tabindex='-1' role='dialog' aria-labelledby='addcontactadressTitle' aria-hidden='true'>
<div class='modal-dialog modal-lg modal-dialog-centered' role='document'>
<div class='modal-content'><div class='modal-header'>
<h5 class='modal-title' id='addcontactadressTitle'>".__( 'New contact', 'doliconnect' )."</h5><button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
</div><div class='modal-body'>";
echo doliconnectuserform($thirdparty, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), true), 'contact');
echo "</div>
<div class='modal-footer'><button name='add_contact' value='new_contact' class='btn btn-warning btn-block' type='submit'><b>".__( 'Add', 'doliconnect' )."</b></button></form></div>
</div></div></div>";
}

echo "<small><div class='float-left'>";
echo dolirefresh($request, $url, dolidelay('contact'));
echo "</div><div class='float-right'>";
echo dolihelp('ISSUE');
echo "</div></small>";

}
add_action( 'user_doliconnect_contacts', 'contacts_module' );

function password_menu( $arg ){
echo "<a href='".esc_url( add_query_arg( 'module', 'password', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ($arg=='password') { echo " active";}
echo "'>".__( 'Modify the password', 'doliconnect' )."</a>";
}
add_action( 'user_doliconnect_menu', 'password_menu', 3, 1);

function password_module( $url ){
global $current_user;
$ID = $current_user->ID;

$msg = null;

if ( isset($_POST["case"]) && $_POST["case"] == 'updatepwd' ) {
$pwd1 = sanitize_text_field($_POST["pwd1"]);
$pwd0 = sanitize_text_field($_POST["pwd0"]);
$pwd2 = sanitize_text_field($_POST["pwd2"]);

if ( (wp_check_password( $pwd0, $current_user->user_pass, $current_user->ID ) ) && ($pwd1 == $pwd2) && (preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9]).{8,20}/', $pwd)) ) {
wp_set_password($pwd1, $ID);

if (doliconnector($current_user, 'fk_user') > '0'){
$data = [
    'pass' => $pwd1
	];
$doliuser = callDoliApi("PUT", "/users/".doliconnector($current_user, 'fk_user'), $data, 0);
}

$msg = "<div class='alert alert-success'><h4 class='alert-heading'>".__( 'Congratulations!', 'doliconnect' )."</h4><p>".__( 'Your password has been changed', 'doliconnect' )."</p></div>";
} elseif ( ! wp_check_password( $pwd0, $current_user->user_pass, $current_user->ID ) ) {
$msg = "<div class='alert alert-danger'><h4 class='alert-heading'>".__( 'Oops!', 'doliconnect' )."</h4><p>".__( 'Your actual password is incorrect', 'doliconnect' )."</p></div>";
} elseif ( $pwd1 != $_POST["pwd2"] ) {
$msg = "<div class='alert alert-danger'><h4 class='alert-heading'>".__( 'Oops!', 'doliconnect' )."</h4><p>".__( 'The new passwords entered are different', 'doliconnect' )."</p></div>";
} elseif ( !preg_match("#.*^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#", $pwd1) ) {
$msg = "<div class='alert alert-danger'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button><span class='fa fa-times-circle'></span> Votre nouveau mot de passe doit comporter entre 8 et 20 caractères dont au moins 1 chiffre, 1 lettre, 1 majuscule et 1 symbole.</div>";
}
}

echo "<form class='was-validated' action='".$url."' id='password-form' method='post'><input type='hidden' name='case' value='updatepwd'>";

if ( isset($msg) ) { echo $msg; }

echo "<script>";
?>

window.setTimeout(function () {
$(".alert-success").fadeTo(500, 0).slideUp(500, function () {
        $(this).remove();
    });
}, 5000);

var form = document.getElementById('password-form');
form.addEventListener('submit', function(event) {

jQuery('#DoliconnectLoadingModal').modal('show');
jQuery(window).scrollTop(0);  
console.log("submit");
form.submit();

});

<?php
echo "</script>"; 

echo "<div class='card shadow-sm'><ul class='list-group list-group-flush'>";
if ( doliconnector($current_user, 'fk_user') > '0' ) {
echo "<li class='list-group-item list-group-item-info'><i class='fas fa-info-circle'></i> <b>".__( 'Your password will be synchronized with your Dolibarr account', 'doliconnect' )."</b></li>";
} elseif  ( defined("DOLICONNECT_DEMO") == $ID ) {
echo "<li class='list-group-item list-group-item-info'><i class='fas fa-info-circle'></i> <b>".__( 'Password cannot be modified in demo mode', 'doliconnect' )."</b></li>";
} 
echo '<li class="list-group-item"><div class="form-group"><div class="row"><div class="col-12"><label for="passwordHelpBlock1"><small>'.__( 'Confirm your current password', 'doliconnect' ).'</small></label>
<div class="input-group mb-2"><div class="input-group-prepend"><div class="input-group-text"><i class="fas fa-key fa-fw"></i></div></div><input type="password" id="pwd0" name="pwd0" class="form-control" aria-describedby="passwordHelpBlock1" autocomplete="off" placeholder="'.__( 'Confirm your current password', 'doliconnect' ).'" ';
if ( defined("DOLICONNECT_DEMO") == $ID ) {
echo ' readonly';
} else {
echo ' required';
}
echo '></div></div></div></div><div class="form-group"><div class="row"><div class="col-12"><label for="passwordHelpBlock2"><small>'.__( 'Change your password', 'doliconnect' ).'</small></label>
<div class="input-group mb-2"><div class="input-group-prepend"><div class="input-group-text"><i class="fas fa-key fa-fw"></i></div></div><input type="password" id="pwd1" name="pwd1" class="form-control" aria-describedby="passwordHelpBlock2" autocomplete="off" placeholder="'.__( 'Choose your new password', 'doliconnect' ).'" ';
if ( defined("DOLICONNECT_DEMO") == $ID ) {
echo ' readonly';
} else {
echo ' required';
}
echo '></div><small id="passwordHelpBlock3" class="form-text text-justify text-muted">
'.__( 'Your password must be between 8 and 20 characters, including at least 1 digit, 1 letter, 1 uppercase.', 'doliconnect' ).'
</small><div class="invalid-feedback">'.__( 'This field is required.', 'doliconnect' ).'</div></div></div><div class="row"><div class="col-12"><label for="passwordHelpBlock3"></label>';
echo '<div class="input-group mb-2"><div class="input-group-prepend"><div class="input-group-text"><i class="fas fa-key fa-fw"></i></div></div><input type="password" id="pwd2" name="pwd2"  class="form-control" aria-describedby="passwordHelpBlock3" autocomplete="off" placeholder="'.__( 'Confirme your new password', 'doliconnect' ).'" ';
if ( defined("DOLICONNECT_DEMO") == $ID ) {
echo ' readonly';
} else {
echo ' required';
}
echo '></div></div></div></li>';
echo "</ul><div class='card-body'><button class='btn btn-danger btn-block' type='submit' ";
if ( defined("DOLICONNECT_DEMO") == $ID ) {
echo ' disabled';
}
echo "><b>".__( 'Update', 'doliconnect' )."</b></button></div></div>";

echo "<p class='text-right'><small>";
echo dolihelp('ISSUE');
echo "</small></p>";
echo "</form>";

}
add_action( 'user_doliconnect_password', 'password_module');
//*****************************************************************************************
if ( is_object($proposal) && $proposal->value == 1 ) {
add_action( 'customer_doliconnect_menu', 'proposals_menu', 1, 1);
add_action( 'customer_doliconnect_proposals', 'proposals_module' );
}

function proposals_menu( $arg ) {
echo "<a href='".esc_url( add_query_arg( 'module', 'proposals', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ( $arg == 'proposals' ) { echo " active";}
echo "'>".__( 'Propals tracking', 'doliconnect' )."</a>";
}

function proposals_module( $url ) {
global $current_user;

$request = "/proposals/".esc_attr($_GET['id'])."?contact_list=0";

if ( isset($_GET['id']) && $_GET['id'] > 0 ) {
$proposalfo = callDoliApi("GET", $request, null, dolidelay('proposal', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//echo $proposalfo;
}

if ( !isset($proposalfo->error) && isset($_GET['id']) && isset($_GET['ref']) && ( doliconnector($current_user, 'fk_soc') == $proposalfo->socid ) && ( $_GET['ref'] == $proposalfo->ref ) && $proposalfo->statut !=0 ) {
echo "<div class='card shadow-sm'><div class='card-body'><h5 class='card-title'>$proposalfo->ref</h5><div class='row'><div class='col-md-5'>";
$datevalidation =  date_i18n('d/m/Y', $proposalfo->date_validation);
echo "<b>".__( 'Date of creation', 'doliconnect' ).":</b> ".date_i18n('d/m/Y', $proposalfo->date_creation)."<br>";
//echo "<b>".__( 'Validation', 'doliconnect' )." : </b> $datevalidation<br>";
echo "<b>Date de fin de validité:</b> $datevalidite";
//echo "<b>".__( 'Status', 'doliconnect' )." : </b> ";
if ( $proposalfo->statut == 3 ) { $propalinfo=__( 'Refused', 'doliconnect' );
$propalavancement=0; }
elseif ( $proposalfo->statut == 2 ) { $propalinfo=__( 'Processing', 'doliconnect' );
$propalavancement=65; }
elseif ( $proposalfo->statut == 1 ) { $propalinfo=__( 'Sign before', 'doliconnect' )." ".date_i18n('d/m/Y', $proposalfo->fin_validite);
$propalavancement=42; }
elseif ( $proposalfo->statut == 0 ) { $propalinfo=__( 'Processing', 'doliconnect' );
$propalavancement=22; }
elseif ( $proposalfo->statut == -1 ) { $propalinfo=__( 'Canceled', 'doliconnect' );
$propalavancement=0; }
echo "<br><br>";
//echo "<b>Moyen de paiement : </b> $proposalfo[mode_reglement]<br>";
echo "</div><div class='col-md-7'>";

if ( isset($propalinfo) ) {
echo "<h3 class='text-right'>".$propalinfo."</h3>";
}

$TTC = number_format($proposalfo->multicurrency_total_ttc, 2, ',', ' ');
$currency = strtolower($proposalfo->multicurrency_code);
echo "</div></div>";
echo '<div class="progress"><div class="progress-bar bg-success" role="progressbar" style="width: '.$propalavancement.'%" aria-valuenow="'.$propalavancement.'" aria-valuemin="0" aria-valuemax="100"></div></div>';
echo "<div class='w-auto text-muted d-none d-sm-block' ><div style='display:inline-block;width:16%'>".__( 'Propal', 'doliconnect' )."</div><div style='display:inline-block;width:21%'>".__( 'Processing', 'doliconnect' )."</div><div style='display:inline-block;width:19%'>".__( 'Validation', 'doliconnect' )."</div><div style='display:inline-block;width:24%'>".__( 'Processing', 'doliconnect' )."</div><div class='text-right' style='display:inline-block;width:20%'>".__( 'Billing', 'doliconnect' )."</div></div>";
echo "</div><ul class='list-group list-group-flush'>";
 
if ( $proposalfo->lines != null ) {
foreach ( $proposalfo->lines as $line ) {
echo "<li class='list-group-item'>";     
if ( $line->date_start != '' && $line->date_end !='' )
{
$start = date_i18n('d/m/Y', $line->date_start);
$end = date_i18n('d/m/Y', $line->date_end);
$dates =" <i>(Du $start au $end)</i>";
} else {
$dates ="";
}

echo '<div class="w-100 justify-content-between"><div class="row"><div class="col-8 col-md-10"> 
<h6 class="mb-1">'.$line->libelle.'</h6>
<small><p class="mb-1">'.$line->desc.'</p>
<i>'.$dates.'</i></small>'; 
echo '</div><div class="col-4 col-md-2 text-right"><h5 class="mb-1">'.doliprice($line, 'ttc', isset($line->multicurrency_code) ? $line->multicurrency_code : null).'</h5>';
echo '<h5 class="mb-1">x'.$line->qty.'</h5>'; 
echo "</div></div></li>";
}
}

echo "<li class='list-group-item list-group-item-info'>";
echo "<b>".__( 'Total excl. tax', 'doliconnect').": ".doliprice($proposalfo, 'ht', isset($proposalfo->multicurrency_code) ? $proposalfo->multicurrency_code : null)."</b><br>";
echo "<b>".__( 'Total tax', 'doliconnect').": ".doliprice($proposalfo, 'tva', isset($proposalfo->multicurrency_code) ? $proposalfo->multicurrency_code : null)."</b><br>";
echo "<b>".__( 'Total incl. tax', 'doliconnect').": ".doliprice($proposalfo, 'ttc', isset($proposalfo->multicurrency_code) ? $proposalfo->multicurrency_code : null)."</b>";
echo "</li>";

if ( $proposalfo->last_main_doc != null ) {
$doc = array_reverse( explode("/", $proposalfo->last_main_doc) );      
$document = dolidocdownload($doc[2],$doc[1],$doc[0],$url."&id=".$_GET['id']."&ref=".$proposalfo->ref,__( 'Summary', 'doliconnect' ));
} 
    
$fruits[$proposalfo->date_creation.'p'] = array(
"timestamp" => $proposalfo->date_creation,
"type" => __( 'Propal', 'doliconnect' ),  
"label" => $proposalfo->ref,
"document" => $document,
"description" => null,
);

sort($fruits, SORT_NUMERIC | SORT_FLAG_CASE);
foreach ( $fruits as $key => $val ) {
echo "<li class='list-group-item'><div class='row'><div class='col-6 col-md-3'>" . date_i18n('d/m/Y H:i', $val['timestamp']) . "</div><div class='col-6 col-md-2'>" . $val['type'] . "</div>";
echo "<div class='col-md-7'><h6>" . $val['label'] . "</h6>" . $val['description'] ."" . $val['document'] ."</div></div></li>";
} 
//var_dump($fruits);
echo "</ul></div>";

echo "<small><div class='float-left'>";
echo dolirefresh($request, $url."&id=".$_GET['id']."&ref=".$_GET['ref'], dolidelay('proposal'), $propalfo);
echo "</div><div class='float-right'>";
echo dolihelp('COM');
echo "</div></small>";

} else {

$request = "/proposals?sortfield=t.rowid&sortorder=ASC&limit=8&thirdparty_ids=".doliconnector($current_user, 'fk_soc')."&sqlfilters=(t.fk_statut!=0)";

if ( isset($_GET['pg']) ) { $page="&page=".$_GET['pg']; }

$listpropal = callDoliApi("GET", $request, null, dolidelay('proposal', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

echo '<div class="card shadow-sm"><ul class="list-group list-group-flush">';  
if ( !isset( $listpropal->error ) && $listpropal != null ) {
foreach ( $listpropal as $postproposal ) { 

$arr_params = array( 'id' => $postproposal->id, 'ref' => $postproposal->ref);  
$return = esc_url( add_query_arg( $arr_params, $url) );
                
echo "<a href='$return' class='list-group-item d-flex justify-content-between lh-condensed list-group-item-action'><div><i class='fa fa-shopping-bag fa-3x fa-fw'></i></div><div><h6 class='my-0'>$postproposal->ref</h6><small class='text-muted'>du ".date_i18n('d/m/Y', $postproposal->date_creation)."</small></div><span>".doliprice($postproposal, 'ttc', isset($postproposal->multicurrency_code) ? $postproposal->multicurrency_code : null)."</span><span>";
if ( $postproposal->statut == 3 ) {
if ( $postproposal->billed == 1 ) { echo "<span class='fa fa-check-circle fa-fw text-success'></span><span class='fa fa-eur fa-fw text-success'></span><span class='fa fa-truck fa-fw text-success'></span><span class='fa fa-file-text fa-fw text-success'></span>"; } 
else { echo "<span class='fa fa-check-circle fa-fw text-success'></span><span class='fa fa-eur fa-fw text-success'></span><span class='fa fa-truck fa-fw text-success'></span><span class='fa fa-file-text fa-fw text-warning'></span>"; } }
elseif ( $postproposal->statut == 2 ) { echo "<span class='fa fa-check-circle fa-fw text-success'></span><span class='fa fa-eur fa-fw text-success'></span><span class='fa fa-truck fa-fw text-warning'></span><span class='fa fa-file-text fa-fw text-danger'></span>"; }
elseif ( $postproppsal->statut == 1 ) { echo "<span class='fa fa-check-circle fa-fw text-success'></span><span class='fa fa-eur fa-fw text-warning'></span><span class='fa fa-truck fa-fw text-danger'></span><span class='fa fa-file-text fa-fw text-danger'></span>"; }
elseif ( $postproposal->statut == 0 ) { echo "<span class='fa fa-check-circle fa-fw text-warning'></span><span class='fa fa-eur fa-fw text-danger'></span><span class='fa fa-truck fa-fw text-danger'></span><span class='fa fa-file-text fa-fw text-danger'></span>"; }
elseif ( $postproposal->statut == -1 ) { echo "<span class='fa fa-check-circle fa-fw text-secondary'></span><span class='fa fa-eur fa-fw text-secondary'></span><span class='fa fa-truck fa-fw text-secondary'></span><span class='fa fa-file-text fa-fw text-secondary'></span>"; }
echo "</span></a>";
}}
else{
echo "<li class='list-group-item list-group-item-light'><center>".__( 'No proposal', 'doliconnect' )."</center></li>";
}
echo  "</ul></div>";

echo "<small><div class='float-left'>";
echo dolirefresh($request, $url, dolidelay('proposal'));
echo "</div><div class='float-right'>";
echo dolihelp('COM');
echo "</div></small>";

}
}

if ( is_object($order) && $order->value == 1 ) {
add_action( 'customer_doliconnect_menu', 'orders_menu', 2, 1);
add_action( 'customer_doliconnect_orders', 'orders_module' );
}

function orders_menu( $arg ) {
echo "<a href='".esc_url( add_query_arg( 'module', 'orders', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ( $arg == 'orders' ) { echo " active"; }
echo "'>".__( 'Orders tracking', 'doliconnect' )."</a>";
}

function orders_module( $url ) {
global $current_user;

$request = "/orders/".esc_attr($_GET['id'])."?contact_list=0";

if ( isset($_GET['id']) && $_GET['id'] > 0 ) {
$orderfo = callDoliApi("GET", $request, null, dolidelay('order', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//echo $orderfo;
}

if ( !isset($orderfo->error) && isset($_GET['id']) && isset($_GET['ref']) && (doliconnector($current_user, 'fk_soc') == $orderfo->socid ) && ($_GET['ref'] == $orderfo->ref) && $orderfo->statut != 0 ) {

echo "<div class='card shadow-sm'><div class='card-body'><h5 class='card-title'>$orderfo->ref</h5><div class='row'><div class='col-md-5'>";
echo "<b>".__( 'Date of order', 'doliconnect' ).":</b> ".date_i18n('d/m/Y', $orderfo->date_creation)."<br>";
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

echo "<b>".__( 'Payment method', 'doliconnect' ).":</b> ".__( $orderfo->mode_reglement, 'doliconnect-pro' )."<br><br></div><div class='col-md-7'>";

if ( isset($orderinfo) ) {
echo "<h3 class='text-right'>".$orderinfo."</h3>";
}

if ( $orderfo->billed != 1 && $orderfo->statut > 0 ) {

if ( function_exists('dolipaymentmodes') ) {

$changepm = doliconnecturl('dolicart')."?pay&module=".esc_attr($_GET['module'])."&id=".esc_attr($_GET['id'])."&ref=".esc_attr($_GET['ref']);

} elseif ( isset($orderfo->public_payment_url) && !empty($orderfo->public_payment_url) ) {

$changepm = $orderfo->public_payment_url;

}

if ( $orderfo->mode_reglement_code == 'CHQ' ) {
$chq = callDoliApi("GET", "/doliconnector/constante/FACTURE_CHQ_NUMBER", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

$bank = callDoliApi("GET", "/bankaccounts/".$chq->value, null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

echo "<div class='alert alert-danger' role='alert'><p align='justify'>".sprintf( __( 'Please send your cheque in the amount of <b>%1$s</b> with reference <b>%2$s</b> to <b>%3$s</b> at the following address', 'doliconnect' ), doliprice($orderfo, 'ttc', isset($orderfo->multicurrency_code) ? $orderfo->multicurrency_code : null), $orderfo->ref, $bank->proprio).":</p>";
echo "<p><b>$bank->owner_address</b></p><small><a href='$changepm' id='button-source-payment'><span class='fas fa-sync-alt'></span> ".__( 'Change your payment mode', 'doliconnect' )."</a></small></div>";
} elseif ( $orderfo->mode_reglement_code == 'VIR' ) { 
$vir = callDoliApi("GET", "/doliconnector/constante/FACTURE_RIB_NUMBER", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

$bank = callDoliApi("GET", "/bankaccounts/".$vir->value, null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

echo "<div class='alert alert-danger' role='alert'><p align='justify'>".sprintf( __( 'Please send your transfert in the amount of <b>%1$s</b> with reference <b>%2$s</b> at the following account', 'doliconnect' ), doliprice($orderfo, 'ttc', isset($orderfo->multicurrency_code) ? $orderfo->multicurrency_code : null), $orderfo->ref ).":";
echo "<br><b>".__( 'Bank', 'doliconnect' ).": $bank->bank</b>";
echo "<br><b>IBAN: $bank->iban</b>";
if ( ! empty($bank->bic) ) { echo "<br><b>BIC/SWIFT: $bank->bic</b>";}
echo "</p><small><a href='$changepm' id='button-source-payment'><span class='fas fa-sync-alt'></span> ".__( 'Change your payment mode', 'doliconnect' )."</a></small></div>";
} else {
echo "<a href='$changepm' id='button-source-payment' class='btn btn-warning btn-block' role='button'><span class='fa fa-credit-card'></span> ".__( 'Pay', 'doliconnect' )."</a><br>";
}

}

echo "</div></div>"; 

if (!empty($orderfo->contacts_ids) && is_array($orderfo->contacts_ids)) {
echo "<div class='card-group'>";
foreach ($orderfo->contacts_ids as $contact) {
echo "<div class='card'><div class='card-body'><h6>".$contact->libelle."</h6>".$contact->civility." ".$contact->firstname." ".$contact->lastname."<br>".$contact->address."<br>".$contact->zip."".$contact->town."</div></div>";
}
echo "</div><br>";
}

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
<small><p class="mb-1">'.$line->desc.'</p>
<i>'.$dates.'</i></small>'; 
echo '</div><div class="col-4 col-md-2 text-right"><h5 class="mb-1">'.doliprice($line, 'ttc', isset($line->multicurrency_code) ? $line->multicurrency_code : null).'</h5>';
echo '<h5 class="mb-1">x'.$line->qty.'</h5>'; 
echo "</div></div></li>";
}
}

echo "<li class='list-group-item list-group-item-info'>";
echo "<b>".__( 'Total excl. tax', 'doliconnect').": ".doliprice($orderfo, 'ht', isset($orderfo->multicurrency_code) ? $orderfo->multicurrency_code : null)."</b><br>";
echo "<b>".__( 'Total tax', 'doliconnect').": ".doliprice($orderfo, 'tva', isset($orderfo->multicurrency_code) ? $orderfo->multicurrency_code : null)."</b><br>";
echo "<b>".__( 'Total incl. tax', 'doliconnect').": ".doliprice($orderfo, 'ttc', isset($orderfo->multicurrency_code) ? $orderfo->multicurrency_code : null)."</b>";
echo "</li>";

if ( $orderfo->last_main_doc != null ) {
$doc = array_reverse(explode("/", $orderfo->last_main_doc)); 
$document_order = dolidocdownload($doc[2], $doc[1], $doc[0], $url."&id=".$_GET['id']."&ref=".$orderfo->ref, __( 'Summary', 'doliconnect' ));
} else {
$document_order = dolidocdownload('order', $orderfo->ref, $orderfo->ref.'.pdf', $url."&id=".$_GET['id']."&ref=".$orderfo->ref, __( 'Summary', 'doliconnect' ), true);
} 
    
$fruits[$orderfo->date_commande.'o'] = array(
"timestamp" => $orderfo->date_creation,
"type" => __( 'Order', 'doliconnect' ),  
"label" => $orderfo->ref,
"document" => $document_order,
"description" => null,
);

$fac=$orderfo->linkedObjectsIds->facture;
if ( $fac != null ) {
foreach ($fac as $f => $value) {

if ($value > 0) {
$invoice = callDoliApi("GET", "/invoices/".$value, null, dolidelay('order', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//echo $invoice;
$payment = callDoliApi("GET", "/invoices/".$value."/payments", null, dolidelay('order', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//echo $payment;
}

if ( $payment != null ) { 
foreach ( $payment as $pay ) {
$fruits[strtotime($pay->date).'p'] = array(
"timestamp" => strtotime($pay->date),
"type" => __( 'Payment', 'doliconnect' ),  
"label" => "$pay->type de ".doliprice($pay->amount, isset($orderfo->multicurrency_code) ? $orderfo->multicurrency_code : null),
"description" => $pay->num,
"document" => null,
); 
}
}

if ( $invoice->last_main_doc != null ) {
$doc = array_reverse(explode("/", $invoice->last_main_doc)); 
$document_invoice = dolidocdownload($doc[2], $doc[1], $doc[0], $url."&id=".$_GET['id']."&ref=".$orderfo->ref, __( 'Invoice', 'doliconnect' ));
} else {
$document_invoice = dolidocdownload('invoice', $invoice->ref, $invoice->ref.'.pdf', $url."&id=".$_GET['id']."&ref=".$orderfo->ref, __( 'Invoice', 'doliconnect' ), true);
} 
  
$fruits[$invoice->date_creation.'i'] = array(
"timestamp" => $invoice->date_creation,
"type" => __( 'Invoice', 'doliconnect' ),  
"label" => $invoice->ref,
"document" => $document_invoice,
"description" => null,
);  
} 
} 
 
if ( isset($orderfo->linkedObjectsIds->shipping) ) {
foreach ( $orderfo->linkedObjectsIds->shipping as $s => $value ) {

if ($value > 0) {
$ship = callDoliApi("GET", "/shipments/".$value, null, dolidelay('order', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//echo $invoice;
}

$lnship ="<ul>";
foreach ( $ship->lines as $sline ) {
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
echo "<li class='list-group-item'><div class='row'><div class='col-6 col-md-3'>" . date_i18n('d/m/Y H:i', $val['timestamp']) . "</div><div class='col-6 col-md-2'>" . $val['type'] . "</div>";
echo "<div class='col-md-7'><h6>" . $val['label'] . "</h6>" . $val['description'] ."" . $val['document'] ."</div></div></li>";
} 
//var_dump($fruits);
echo "</ul></div>";

echo "<small><div class='float-left'>";
echo dolirefresh($request, $url."&id=".$_GET['id']."&ref=".$_GET['ref'], dolidelay('order'), $orderfo);
echo "</div><div class='float-right'>";
echo dolihelp('COM');
echo "</div></small>";

} else {

$request= "/orders?sortfield=t.rowid&sortorder=DESC&limit=8".$page."&thirdparty_ids=".doliconnector($current_user, 'fk_soc')."&sqlfilters=(t.fk_statut!=0)";

if ( isset($_GET['pg']) && $_GET['pg'] > 0) { $page="&page=".$_GET['pg'];}  else { $page=""; }

$listorder = callDoliApi("GET", $request, null, dolidelay('order', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

echo '<div class="card shadow-sm"><ul class="list-group list-group-flush">';
if ( !isset($listorder->error ) && $listorder != null ) {
foreach ( $listorder as $postorder ) {

$arr_params = array( 'id' => $postorder->id, 'ref' => $postorder->ref);  
$return = esc_url( add_query_arg( $arr_params, $url) );
                                                                                                                                                      
echo "<a href='$return' class='list-group-item d-flex justify-content-between lh-condensed list-group-item-action'><div><i class='fa fa-shopping-bag fa-3x fa-fw'></i></div><div><h6 class='my-0'>$postorder->ref</h6><small class='text-muted'>du ".date_i18n('d/m/Y', $postorder->date_commande)."</small></div><span>".doliprice($postorder, 'ttc', isset($postorder->multicurrency_code) ? $postorder->multicurrency_code : null)."</span><span>";
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
echo dolirefresh($request, $url, dolidelay('order'));
echo "</div><div class='float-right'>";
echo dolihelp('COM');
echo "</div></small>";

//echo '<br><nav aria-label="Page navigation example">
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
add_action( 'customer_doliconnect_menu', 'contracts_menu', 2, 1);
add_action( 'customer_doliconnect_contracts', 'contracts_module' );
}

function contracts_menu( $arg ) {
echo "<a href='".esc_url( add_query_arg( 'module', 'contracts', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ( $arg == 'contracts' ) { echo " active"; }
echo "'>".__( 'Contracts tracking', 'doliconnect' )."</a>";
}

function contracts_module( $url ) {
global $current_user;

$request = "/contracts/".esc_attr($_GET['id'])."?contact_list=0";

if ( isset($_GET['id']) && $_GET['id'] > 0 ) {
$contractfo = callDoliApi("GET", $request, null, dolidelay('contract', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//echo $contractfo;
}

if ( !isset($contractfo->error) && isset($_GET['id']) && isset($_GET['id']) && isset($_GET['ref']) && (doliconnector($current_user, 'fk_soc') == $contractfo->socid) && ($_GET['ref'] == $contractfo->ref) ) {
echo "<div class='card shadow-sm'><div class='card-body'><h5 class='card-title'>$contractfo->ref</h5><div class='row'><div class='col-md-5'>";
echo "<b>".__( 'Date of creation', 'doliconnect' ).": </b> ".date_i18n('d/m/Y', $contractfo->date_creation)."<br>";
if ( $contractfo->statut > 0 ) {
//if ( $contractfo->billed == 1 ) {
//if ( $contractfo->statut > 1 ) { $contractfo=__( 'Shipped', 'doliconnect' ); 
//$orderavancement=100; }
//else { $orderinfo=__( 'Processing', 'doliconnect' );
//$contractavancement=40; }
//}
//else { $contractinfo=null;
//$contractinfo=null;
//$contractavancement=25;
//}
$contractavancement=0; 
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
<small><p class="mb-1">'.$line->desc.'</p>
<i>'.$dates.'</i></small>'; 
echo '</div><div class="col-4 col-md-2 text-right"><h5 class="mb-1">'.doliprice($line, 'ttc', isset($line->multicurrency_code) ? $line->multicurrency_code : null).'</h5>';
echo '<h5 class="mb-1">x'.$line->qty.'</h5>'; 
echo "</div></div></li>";
}
}

echo "<li class='list-group-item list-group-item-info'>";
echo "<b>".__( 'Total excl. tax', 'doliconnect').": ".doliprice($contractfo, 'ht', isset($contractfo->multicurrency_code) ? $contractfo->multicurrency_code : null)."</b><br>";
echo "<b>".__( 'Total tax', 'doliconnect').": ".doliprice($contractfo, 'tva',isset($postcontract->multicurrency_code) ? $contractfo->multicurrency_code : null)."</b><br>";
echo "<b>".__( 'Total incl. tax', 'doliconnect').": ".doliprice($contractfo, 'ttc', isset($contractfo->multicurrency_code) ? $contractfo->multicurrency_code : null)."</b>";
echo "</li>";

//var_dump($fruits);
echo "</ul></div>";

echo "<small><div class='float-left'>";
echo dolirefresh($request, $url."&id=".$_GET['id']."&ref=".$_GET['ref'], dolidelay('contract'), $contractfo);
echo "</div><div class='float-right'>";
echo dolihelp('COM');
echo "</div></small>";

} else {

$request = "/contracts?sortfield=t.rowid&sortorder=DESC&limit=8".$page."&thirdparty_ids=".doliconnector($current_user, 'fk_soc');

if ( isset($_GET['pg']) && $_GET['pg'] ) { $page="&page=".$_GET['pg'];} else { $page=""; }
                                 
$listcontract = callDoliApi("GET", $request, null, dolidelay('contract', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

echo '<div class="card shadow-sm"><ul class="list-group list-group-flush">';
if ( !isset($listcontract->error) && $listcontract != null ) {
foreach ($listcontract  as $postcontract) {                                                                                 

$arr_params = array( 'id' => $postcontract->id, 'ref' => $postcontract->ref);  
$return = esc_url( add_query_arg( $arr_params, $url) );
                                                                                                                                                      
echo "<a href='$return' class='list-group-item d-flex justify-content-between lh-condensed list-group-item-action'><div><i class='fa fa-shopping-bag fa-3x fa-fw'></i></div><div><h6 class='my-0'>$postcontract->ref</h6><small class='text-muted'>du ".date_i18n('d/m/Y', $postcontract->date_creation)."</small></div><span>".doliprice($postcontract, 'ttc', isset($postcontract->multicurrency_code) ? $postcontract->multicurrency_code : null)."</span><span>";
if ( $postcontract->statut > 0 ) {echo "<span class='fas fa-check-circle fa-fw text-success'></span> ";
//if ( $postcontract->billed == 1 ) { echo "<span class='fas fa-money-bill-alt fa-fw text-success'></span> "; 
//if ( $postcontract->statut > 1 ) { echo "<span class='fas fa-shipping-fast fa-fw text-success'></span> "; }
//else { echo "<span class='fas fa-shipping-fast fa-fw text-warning'></span> "; }
//}
//else { echo "<span class='fas fa-money-bill-alt fa-fw text-warning'></span> "; 
//if ( $postcontract->statut > 1 ) { echo "<span class='fas fa-shipping-fast fa-fw text-success'></span> "; }
//else { echo "<span class='fas fa-shipping-fast fa-fw text-danger'></span> "; }
//}
}
elseif ( $postcontract->statut == 0 ) { echo "<span class='fas fa-check-circle fa-fw text-warning'></span> <span class='fas fa-money-bill-alt fa-fw text-danger'></span> <span class='fas fa-shipping-fast fa-fw text-danger'></span>";}
elseif ( $postcontract->statut == -1 ) {echo "<span class='fas fa-check-circle fa-fw text-secondary'></span> <span class='fas fa-money-bill-alt fa-fw text-secondary'></span> <span class='fas fa-shipping-fast fa-fw text-secondary'></span>";}
echo "</span></a>";
}}
else{
echo "<li class='list-group-item list-group-item-light'><center>".__( 'No contract', 'doliconnect' )."</center></li>";
}
echo  "</ul></div>";

//echo '<br><nav aria-label="Page navigation example">
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
echo dolirefresh($request, $url, dolidelay('contract'));
echo "</div><div class='float-right'>";
echo dolihelp('COM');
echo "</div></small>";

}
}

if ( is_object($donation) && $donation->value == '1' && get_option('doliconnectbeta') =='1' ) {
add_action( 'customer_doliconnect_menu', 'donations_menu', 5, 1);
add_action( 'customer_doliconnect_donations', 'donations_module' );
}  

function donations_menu( $arg ) {
echo "<a href='".esc_url( add_query_arg( 'module', 'donations', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ($arg=='donations') { echo " active";}
echo "'>".__( 'Donations tracking', 'doliconnect' )."</a>";
}

function donations_module( $url ) {
global $wpdb, $current_user;
$entity = get_current_blog_id();
$ID = $current_user->ID;

$request = "/donations/".esc_attr($_GET['id']);

if ( isset($_GET['id']) && $_GET['id'] > 0 ) {
$donationfo = callDoliApi("GET", $request, null, dolidelay('donation', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//echo $donationfo;
}

if ( !isset($donationfo->error) && isset($_GET['id']) && isset($_GET['ref']) && (doliconnector($current_user, 'fk_soc') == $donationfo->socid ) && ($_GET['ref'] == $donationfo->ref) && $donationfo->statut != 0 ) {

echo "<div class='card shadow-sm'><div class='card-body'><h5 class='card-title'>$donationfo->ref</h5><div class='row'><div class='col-md-5'>";
$datecommande =  date_i18n('d/m/Y', $donationfo->date_creation);
echo "<b>".__( 'Date of order', 'doliconnect' ).":</b> $datecommande<br>";
if ( $donationfo->statut > 0 ) {
if ( $donationfo->billed == 1 ) {
if ( $donationfo->statut >1 ) { $orderinfo=__( 'Shipped', 'doliconnect' ); 
$orderavancement=100; }
else { $orderinfo=__( 'Processing', 'doliconnect' );
$orderavancement=40; }
}
else { $orderinfo=null;
$orderinfo=null;
$orderavancement=25;
}
}
elseif ( $donationfo->statut == 0 ) { $orderinfo=__( 'Validation', 'doliconnect' );
$orderavancement=7; }
elseif ( $donationfo->statut == -1 ) { $orderinfo=__( 'Canceled', 'doliconnect' );
$orderavancement=0;  }

echo "<b>".__( 'Payment method', 'doliconnect' ).":</b> ".__( $donationfo->mode_reglement, 'doliconnect-pro' )."<br><br></div><div class='col-md-7'>";

if ( isset($orderinfo) ) {
echo "<h3 class='text-right'>".$orderinfo."</h3>";
}

if ( $donationfo->billed != 1 && $donationfo->statut > 0 ) {

if ( function_exists('dolipaymentmodes') ) {

$change = "<small><a href='#' id='button-source-payment' data-toggle='modal' data-target='#orderonlinepay'><span class='fas fa-sync-alt'></span> ".__( 'Change your payment mode', 'doliconnect' )."</a></small>";

echo "<div class='modal fade' id='orderonlinepay' tabindex='-1' role='dialog' aria-labelledby='orderonlinepayLabel' aria-hidden='true'  aria-hidden='true' data-backdrop='static' data-keyboard='false'>
<div class='modal-dialog modal-dialog-centered' role='document'><div class='modal-content'><div class='modal-header border-0'><h4 class='modal-title border-0' id='orderonlinepayLabel'>".__( 'Payment methods', 'doliconnect' )."</h4>
<button id='closemodalonlinepay' type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div><div class='modal-body'>";

$listsource = callDoliApi("GET", "/doliconnector/".doliconnector($current_user, 'fk_soc')."/sources", null, dolidelay('source', isset($_GET["refresh"]) ? $_GET["refresh"] : null));
//echo $listsource;

if ( !empty($donationfo->paymentintent) ) {
dolipaymentmodes( $listsource, $donationfo, $url, $url);
} else {
doligateway($listsource, $donationfo->ref, $donationfo->multicurrency_total_ttc?$donationfo->multicurrency_total_ttc:$donationfo->total_ttc, $donationfo->multicurrency_code, $url.'&id='.$_GET['id'].'&ref='.$_GET['ref'], 'full');
echo doliloading('paymentmodes'); }

echo "</div></div></div></div>";

} else {

$change = "<a href='".get_site_option('dolibarr_public_url')."/public/payment/newpayment.php?source=".esc_attr($_GET['module'])."&ref=".esc_attr($_GET['ref'])."&securekey=".sha1(md5('nw38LmcS3tgow7D1tGZGiBr56GPK059Q' . esc_attr($_GET['module']) . esc_attr($_GET['ref'])))."&entity=".dolibarr_entity()."' target='_blank'><span class='fa fa-credit-card'></span> ".__( 'Pay online', 'doliconnect' )."</a>";

}

if ( $donationfo->mode_reglement_code == 'CHQ' ) {
$chq = callDoliApi("GET", "/doliconnector/constante/FACTURE_CHQ_NUMBER", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

$bank = callDoliApi("GET", "/bankaccounts/".$chq->value, null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

echo "<div class='alert alert-danger' role='alert'><p align='justify'>".sprintf( __( 'Please send your cheque in the amount of <b>%1$s</b> with reference <b>%2$s</b> to <b>%3$s</b> at the following address', 'doliconnect' ), doliprice($donationfo->multicurrency_total_ttc?$donationfo->multicurrency_total_ttc:$donationfo->total_ttc,$donationfo->multicurrency_code), $donationfo->ref, $bank->proprio).":</p><p><b>$bank->owner_address</b></p>$change</div>";
} elseif ( $donationfo->mode_reglement_code == 'VIR' ) { 
$vir = callDoliApi("GET", "/doliconnector/constante/FACTURE_RIB_NUMBER", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

$bank = callDoliApi("GET", "/bankaccounts/".$vir->value, null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

echo "<div class='alert alert-danger' role='alert'><p align='justify'>".sprintf( __( 'Please send your transfert in the amount of <b>%1$s</b> with reference <b>%2$s</b> at the following account', 'doliconnect' ), doliprice($donationfo->multicurrency_total_ttc?$donationfo->multicurrency_total_ttc:$donationfo->total_ttc,$donationfo->multicurrency_code), $donationfo->ref ).":";
echo "<br><b>IBAN: $bank->iban</b>";
if ( ! empty($bank->bic) ) { echo "<br><b>BIC/SWIFT: $bank->bic</b>";}
echo "</p>$change</div>";
} else {
echo "<button type='button' id='button-source-payment' class='btn btn-warning btn-block' data-toggle='modal' data-target='#orderonlinepay'><span class='fa fa-credit-card'></span> ".__( 'Pay', 'doliconnect' )."</button><br>";
}

}

echo "</div></div>";
echo '<div class="progress"><div class="progress-bar bg-success" role="progressbar" style="width: '.$orderavancement.'%" aria-valuenow="'.$orderavancement.'" aria-valuemin="0" aria-valuemax="100"></div></div>';
echo "<div class='w-auto text-muted d-none d-sm-block' ><div style='display:inline-block;width:20%'>".__( 'Order', 'doliconnect' )."</div><div style='display:inline-block;width:15%'>".__( 'Payment', 'doliconnect' )."</div><div style='display:inline-block;width:25%'>".__( 'Processing', 'doliconnect' )."</div><div style='display:inline-block;width:20%'>".__( 'Shipping', 'doliconnect' )."</div><div class='text-right' style='display:inline-block;width:20%'>".__( 'Delivery', 'doliconnect' )."</div></div>";

echo "</div><ul class='list-group list-group-flush'>";
 
if ( $donationfo->lines != null ) {
foreach ( $donationfo->lines as $line ) {
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
echo '</div><div class="col-4 col-md-2 text-right"><h5 class="mb-1">'.doliprice($line, 'ttc', isset($line->multicurrency_code) ? $line->multicurrency_code : null).'</h5>';
echo '<h5 class="mb-1">x'.$line->qty.'</h5>'; 
echo "</div></div></li>";
}
}

echo "<li class='list-group-item list-group-item-info'>";
echo "<b>".__( 'Amount', 'doliconnect').": ".doliprice($donationfo, 'amount', isset($donationfo->multicurrency_code) ? $donationfo->multicurrency_code : null)."</b>";
echo "</li>";
echo "</ul></div>";

echo "<small><div class='float-left'>";
echo dolirefresh($request, $url, dolidelay('donation'), $donationfo);
echo "</div><div class='float-right'>";
echo dolihelp('COM');
echo "</div></small>";

} else {

if ( isset($_GET['pg']) ) { $page="&page=".$_GET['pg']; }

$request= "/donations?sortfield=t.rowid&sortorder=DESC&limit=8".$page."&thirdparty_ids=".doliconnector($current_user, 'fk_soc')."&sqlfilters=(t.fk_statut!=0)";//

$listdonation = callDoliApi("GET", $request, null, dolidelay('donation', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//print var_dump($listdonation);

echo '<div class="card shadow-sm"><ul class="list-group list-group-flush">';
echo '<a href="#" class="list-group-item lh-condensed list-group-item-action list-group-item-primary disabled"><center><i class="fas fa-plus-circle"></i> '.__( 'Donate', 'doliconnect' ).'</center></a>';  
if ( !isset( $listdonation->error ) && $listdonation != null ) {
foreach ( $listdonation as $postdonation ) { 

$arr_params = array( 'id' => $postdonation->id, 'ref' => $postdonation->ref);  
$return = esc_url( add_query_arg( $arr_params, $url) );
                
echo "<a href='$return' class='list-group-item d-flex justify-content-between lh-condensed list-group-item-action'><div><i class='fa fa-shopping-bag fa-3x fa-fw'></i></div><div><h6 class='my-0'>$postdonation->ref</h6><small class='text-muted'>du ".date_i18n('d/m/Y', $postdonation->date_creation)."</small></div><span>".doliprice($postdonation, 'amount', isset($postdonation->multicurrency_code) ? $postdonation->multicurrency_code : null)."</span><span>";
if ( $postdonation->statut == 3 ) {
if ( $postdonation->billed == 1 ) { echo "<span class='fa fa-check-circle fa-fw text-success'></span><span class='fa fa-eur fa-fw text-success'></span><span class='fa fa-truck fa-fw text-success'></span><span class='fa fa-file-text fa-fw text-success'></span>"; } 
else { echo "<span class='fa fa-check-circle fa-fw text-success'></span><span class='fa fa-eur fa-fw text-success'></span><span class='fa fa-truck fa-fw text-success'></span><span class='fa fa-file-text fa-fw text-warning'></span>"; } }
elseif ( $postdonation->statut == 2 ) { echo "<span class='fa fa-check-circle fa-fw text-success'></span><span class='fa fa-eur fa-fw text-success'></span><span class='fa fa-truck fa-fw text-warning'></span><span class='fa fa-file-text fa-fw text-danger'></span>"; }
elseif ( $postdonation->statut == 1 ) { echo "<span class='fa fa-check-circle fa-fw text-success'></span><span class='fa fa-eur fa-fw text-warning'></span><span class='fa fa-truck fa-fw text-danger'></span><span class='fa fa-file-text fa-fw text-danger'></span>"; }
elseif ( $postdonation->statut == 0 ) { echo "<span class='fa fa-check-circle fa-fw text-warning'></span><span class='fa fa-eur fa-fw text-danger'></span><span class='fa fa-truck fa-fw text-danger'></span><span class='fa fa-file-text fa-fw text-danger'></span>"; }
elseif ( $postdonation->statut == -1 ) { echo "<span class='fa fa-check-circle fa-fw text-secondary'></span><span class='fa fa-eur fa-fw text-secondary'></span><span class='fa fa-truck fa-fw text-secondary'></span><span class='fa fa-file-text fa-fw text-secondary'></span>"; }
echo "</span></a>";
}}
else{
echo "<li class='list-group-item list-group-item-light'><center>".__( 'No donation', 'doliconnect' )."</center></li>";
}
echo "</ul></div>";

echo "<small><div class='float-left'>";
echo dolirefresh($request, $url, dolidelay('donation'));
echo "</div><div class='float-right'>";
echo dolihelp('COM');
echo "</div></small>";

}
}
//*****************************************************************************************
if ( is_object($member) && $member->value == '1' ) {
add_action( 'options_doliconnect_menu', 'members_menu', 1, 1);
add_action( 'options_doliconnect_members', 'members_module' );
}

function members_menu( $arg ) {
echo "<a href='".esc_url( add_query_arg( 'module', 'members', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ($arg=='members') { echo " active";}
echo "'>".__( 'Membership', 'doliconnect' )."</a>";
}

function members_module( $url ) {
global $current_user;

$time = current_time( 'timestamp',1);

$request = "/adherentsplus/".doliconnector($current_user, 'fk_member'); 

$productadhesion = callDoliApi("GET", "/doliconnector/constante/ADHERENT_PRODUCT_ID_FOR_SUBSCRIPTIONS", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
$order = callDoliApi("GET", "/doliconnector/constante/MAIN_MODULE_COMMANDE", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if ( isset($_POST["update_membership"]) && function_exists('dolimembership') ) {
$adherent = dolimembership($current_user, $_POST["update_membership"], $_POST["typeadherent"], dolidelay('member', true));

//if ($statut==1) {
$msg = "<div class='alert alert-success' role='alert'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button><p><strong>".__( 'Congratulations!', 'doliconnect' )."</strong> ".__( 'Your membership has been updated.', 'doliconnect' )."</p></div>";
//}

if ( ($_POST["update_membership"]==4) && isset($_POST["cotisation"]) && doliconnector($current_user, 'fk_member') > 0 && $_POST["timestamp_start"] > 0 && $_POST["timestamp_end"] > 0 ) {

addtodolibasket($productadhesion->value, 1, $_POST["cotisation"], $url, $_POST["timestamp_start"], $_POST["timestamp_end"]);
wp_redirect(esc_url(doliconnecturl('dolicart')));
exit;     
} elseif ( $_POST["update_membership"]==5 || $_POST["update_membership"]==1 ) {
$dolibarr = callDoliApi("GET", "/doliconnector/".$current_user->ID, null, 0); 
}

} 

if ( isset($msg) ) { echo $msg; }

echo "<div class='card shadow-sm'><div class='card-body'><div class='row'><div class='col-12 col-md-5'>";

if ( !empty(doliconnector($current_user, 'fk_member')) && doliconnector($current_user, 'fk_member') > 0 && doliconnector($current_user, 'fk_soc') > 0 ) { 
$adherent = callDoliApi("GET", $request, null, dolidelay('member', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
}

echo "<b>".__( 'Status', 'doliconnect' ).":</b> ";
if ( $adherent->statut == '1') {
if  ($adherent->datefin == null ) { echo  "<span class='badge badge-danger'>".__( 'Waiting payment', 'doliconnect' )."</span>";}
else {
if ( $adherent->datefin+86400>$time){ echo  "<span class='badge badge-success'>".__( 'Active', 'doliconnect' )."</span>"; } else { echo  "<span class='badge badge-danger'>".__( 'Waiting payment', 'doliconnect' )."</span>";}
}}
elseif ( $adherent->statut == '0' ) {
echo  "<span class='badge badge-dark'>".__( 'Terminated', 'doliconnect' )."</span>";}
elseif ( $adherent->statut == '-1' ) {
echo  "<span class='badge badge-warning'>".__( 'Waiting validation', 'doliconnect' )."</span>";}
else {echo  "<span class='badge badge-dark'>".__( 'No membership', 'doliconnect' )."</span>";}
echo  "<br>";
$type=(! empty($adherent->type) ? $adherent->type : __( 'nothing', 'doliconnect' ));
echo  "<b>".__( 'Type', 'doliconnect' ).":</b> ".$type."<br>";
echo  "<b>".__( 'End of membership', 'doliconnect' ).":</b> ";
if ( $adherent->datefin == null ) { echo  "***";
} else { echo  date_i18n('d/m/Y', $adherent->datefin); }
echo  "<br><b>".__( 'Seniority', 'doliconnect' ).":</b> ";
echo  "<br><b>".__( 'Commitment', 'doliconnect' ).":</b> ";
if ( (current_time('timestamp') > $adherent->datecommitment) || null == $adherent->datecommitment ) { echo  __( 'no', 'doliconnect' );
} else {
$datefin =  date_i18n('d/m/Y', $adherent->datecommitment);
echo  "$datefin"; }

echo "</div><div class='col-12 col-md-7'>";

if ( function_exists('dolimembership_modal') && is_object($order) && $order->value == 1 && !empty($productadhesion->value) ) {
dolimembership_modal($current_user, $adherent, dolidelay('member', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

echo "<script>";
?>

window.setTimeout(function () {
    $(".alert-success").fadeTo(500, 0).slideUp(500, function () {
        $(this).remove();
    });
}, 5000);

<?php
echo "</script>";


if ( $adherent->datefin == null && $adherent->statut == '0' ) {echo  "<a href='#' id='subscribe-button2' class='btn btn text-white btn-warning btn-block' data-toggle='modal' data-target='#activatemember'><b>".__( 'Become a member', 'doliconnect' )."</b></a>";
} elseif ($adherent->statut == '1') {
if ( $time > $adherent->next_subscription_renew && $adherent->datefin != null ) {
echo "<button class='btn btn text-white btn-warning btn-block' data-toggle='modal' data-target='#activatemember'><b>".__( 'Renew my subscription', 'doliconnect' )."</b></button>";
} elseif ( ( $adherent->datefin + 86400 ) > $time ) {
echo  "<button id='subscribe-button2' class='btn btn text-white btn-warning btn-block' data-toggle='modal' data-target='#activatemember'><b>".__( 'Modify my subscription', 'doliconnect' )."</b></button>";
}else { echo  "<button class='btn btn btn-danger btn-block' data-toggle='modal' data-target='#activatemember'><b>".__( 'Pay my subscription', 'doliconnect' )."</b></button>";}
} elseif ( $adherent->statut == '0' ) {
if ( ( $adherent->datefin + 86400) > $time ) {
echo "<form id='subscription-form' action='".doliconnecturl('doliaccount')."?module=members' method='post'><input type='hidden' name='update_membership' value='4'><button id='resiliation-button' class='btn btn btn-warning btn-block' type='submit'><b>".__( 'Reactivate my subscription', 'doliconnect' )."</b></button></form>";
} else {
echo  "<button class='btn btn text-white btn-warning btn-block' data-toggle='modal' data-target='#activatemember'><b>".__( 'Renew my subscription', 'doliconnect' )."</b></button>";
}
} elseif ( $adherent->statut == '-1' ) {
echo '<div class="clearfix"><div class="spinner-border float-left" role="status">
<span class="sr-only">Loading...</span></div>'.__('Your request has been registered. You will be notified at validation.', 'doliconnect').'</div>';
} else { 

if ( doliconnector($current_user, 'fk_soc') > 0 ) {
$thirdparty = callDoliApi("GET", "/thirdparties/".doliconnector($current_user, 'fk_soc'), null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));  
}

if ( empty($thirdparty->address) || empty($thirdparty->zip) || empty($thirdparty->town) || empty($thirdparty->country_id) || empty($current_user->billing_type) || empty($current_user->billing_birth) || empty($current_user->user_firstname) || empty($current_user->user_lastname) || empty($current_user->user_email)) {
echo "Pour adhérer, tous les champs doivent être renseignés dans vos <a href='".esc_url( get_permalink(get_option('doliaccount')))."?module=informations&return=members' class='alert-link'>".__( 'Personal informations', 'doliconnect' )."</a></div><div class='col-sm-6 col-md-7'>";
} else { 
echo "<button class='btn btn text-white btn-warning btn-block' data-toggle='modal' data-target='#activatemember'><b>".__( 'Become a member', 'doliconnect' )."</b></button>";
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

if (doliconnector($current_user, 'fk_member') > 0) {
$listcotisation = callDoliApi("GET", "/adherentsplus/".doliconnector($current_user, 'fk_member')."/subscriptions", null, dolidelay('member', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
} 

if ( !isset($listcotisation->error) && $listcotisation != null ) { 
foreach ( $listcotisation as $cotisation ) {                                                                                 
$dated =  date_i18n('d/m/Y', $cotisation->dateh);
$datef =  date_i18n('d/m/Y', $cotisation->datef);
echo "<li class='list-group-item'><table width='100%' border='0'><tr><td>$cotisation->label</td><td>$dated ".__( 'to', 'doliconnect' )." $datef";
echo "</td><td class='text-right'><b>".doliprice($cotisation->amount)."</b></td></tr></table><span></span></li>";
}
}
else { 
echo "<li class='list-group-item list-group-item-light'><center>".__( 'No subscription', 'doliconnect' )."</center></li>";
}
echo  "</ul></div>";

echo "<small><div class='float-left'>";
echo dolirefresh($request, $url, dolidelay('member'), $adherent);
echo "</div><div class='float-right'>";
echo dolihelp('COM');
echo "</div></small>";

}

if ( is_object($memberconsumption) && $memberconsumption->value == '1' && get_option('doliconnectbeta') =='1' ) {
add_action( 'options_doliconnect_menu', 'membershipconsumption_menu', 2, 1);
add_action( 'options_doliconnect_membershipconsumption', 'membershipconsumption_module' );
}  

function membershipconsumption_menu( $arg ) {
echo "<a href='".esc_url( add_query_arg( 'module', 'membershipconsumption', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ($arg=='membershipconsumption') { echo " active";}
echo "'>".__( 'Consumptions monitoring', 'doliconnect' )."</a>";
}

function membershipconsumption_module( $url ) {
global $current_user;

$request = "/adherentsplus/".doliconnector($current_user, 'fk_member')."/consumptions";

echo "<div class='card shadow-sm'><div class='card-body'>";
echo "<b>".__( 'Next billing date', 'doliconnect' ).": </b> <br>";

echo "</div><ul class='list-group list-group-flush'>";

if (doliconnector($current_user, 'fk_member') > 0) {
$listconsumption = callDoliApi("GET", $request, null, dolidelay('member', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
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
echo dolirefresh( $request, $url, dolidelay('member'));
echo "</div><div class='float-right'>";
echo dolihelp('COM');
echo "</div></small>";

}

if ( is_object($linkedmember) && $linkedmember->value == '1' ) {
add_action( 'options_doliconnect_menu', 'linkedmember_menu', 3, 1);
add_action( 'options_doliconnect_linkedmember', 'linkedmember_module' );
}  

function linkedmember_menu( $arg ) {
echo "<a href='".esc_url( add_query_arg( 'module', 'linkedmember', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ($arg=='linkedmember') { echo " active";}
echo "'>".__( 'Manage linked members', 'doliconnect' )."</a>";
}

function linkedmember_module( $url ) {
global $current_user;

$request = "/adherentsplus/".doliconnector($current_user, 'fk_member')."/linkedmember";

if ( isset ($_POST['unlink_member']) && $_POST['unlink_member'] > 0 ) {
//$memberv = callDoliApi("GET", "/adherentsplus/".esc_attr($_POST['unlink_member']), null, 0);
//if ( $memberv->socid == doliconnector($current_user, 'fk_soc') ) {
// try deleting
$delete = callDoliApi("DELETE", $request."/".esc_attr($_POST['unlink_member']), null, 0);

$msg = "<div class='alert alert-success'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button><p><strong>".__( 'Congratulations!', 'doliconnect' )."</strong> ".__( 'Your informations have been updated.', 'doliconnect' )."</p></div>";

//} else {
// fail deleting
//}
$linkedmember = callDoliApi("GET", $request, null, dolidelay('member', true));

} elseif ( isset ($_POST['update_member']) && $_POST['update_member'] > 0 ) {

$memberv=$_POST['member'][''.$_POST['update_member'].''];

$memberv = callDoliApi("PUT", "/adherentsplus/".esc_attr($_POST['update_member']), $memberv, dolidelay('member', true));
if ( false === $memberv ) {
// fail deleting

} else {
$msg = "<div class='alert alert-success'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button><p><strong>".__( 'Congratulations!', 'doliconnect' )."</strong> ".__( 'Your informations have been updated.', 'doliconnect' )."</p></div>";
$linkedmember = callDoliApi("GET", $request, null, dolidelay('member', true));
}

} elseif (doliconnector($current_user, 'fk_member') > 0) {

$linkedmember= callDoliApi("GET", $request, null, dolidelay('member', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

}

echo "<form role='form' action='$url' id='linkedmember-form' method='post'>"; 

if ( isset($msg) ) { echo $msg; }                       

echo "<script>";
?>

window.setTimeout(function() {
    $(".alert").fadeTo(500, 0).slideUp(500, function(){
        $(this).remove(); 
    });
}, 5000);

var form = document.getElementById('linkedmember-form'); 

form.addEventListener('submit', function(event) {

jQuery('#DoliconnectLoadingModal').modal('show');
jQuery(window).scrollTop(0);
console.log("submit");
form.submit();

});

<?php
echo "</script>";

echo "<div class='card shadow-sm'><ul class='list-group list-group-flush'>";

echo "<li class='list-group-item list-group-item-info'><i class='fas fa-info-circle'></i> <b>".__( 'Please contact us to add/link a member', 'doliconnect' )."</b></li>"; 

if ( !isset($linkedmember->error) && $linkedmember != null ) { 
foreach ( $linkedmember as $member ) {                                                                                 
echo "<li class='list-group-item d-flex justify-content-between lh-condensed list-group-item-action'>";
echo "<div class='d-none d-md-block col-md-2 col-lg-1'><i class='fas fa-address-card $color fa-3x fa-fw'></i></div><h6 class='my-0'>".($member->civility ? $member->civility : $member->civility_code)." ".$member->firstname." ".$member->lastname;
//if ( !empty($contact->poste) ) { echo "<br>".$contact->poste; }
echo "</h6>";
echo "<small class='text-muted'>".$member->address."<br>".$member->zip." ".$member->town." - ".$member->country."<br>".$member->email." ".$member->phone_pro."</small>";
if (1 == 1) {
echo "<div class='col-4 col-sm-3 col-md-2 btn-group-vertical' role='group'>";
echo "<button type='button' class='btn btn-light text-primary' data-toggle='modal' data-target='#member-".$member->id."' title='".__( 'Edit', 'doliconnect' )." ".$member->firstname." ".$member->lastname."'><i class='fas fa-edit fa-fw'></i></a>
<button name='unlink_member' value='".$member->id."' class='btn btn-light text-danger' type='submit' title='".__( 'Unlink', 'doliconnect' )." ".$member->firstname." ".$member->lastname."'><i class='fas fa-unlink'></i></button>";
echo "</div>";
}
echo "</li>";
}
} else { 
echo "<li class='list-group-item list-group-item-light'><center>".__( 'No linked member', 'doliconnect' )."</center></li>";
}
echo  "</ul></div></form>";

if ( !isset($linkedmember->error) && $linkedmember != null ) { 
foreach ( $linkedmember as $member ) {
echo "<form class='was-validated' role='form' action='$url' id='member-".$member->id."-form' method='post'>";
echo '<div class="modal fade" id="member-'.$member->id.'" tabindex="-1" role="dialog" aria-labelledby="member-'.$member->id.'Title" aria-hidden="true">
<div class="modal-dialog modal-lg modal-dialog-centered" role="document"><div class="modal-content"><div class="modal-header">
<h5 class="modal-title" id="member-'.$member->id.'Title">'.__( 'Update member', 'doliconnect' ).'</h5><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
<div class="modal-body">';
echo doliconnectuserform($member, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null), true), 'member');      
echo "</div>
<div class='modal-footer'><button name='update_member' value='".$member->id."' class='btn btn-warning btn-block' type='submit'><b>".__( 'Update', 'doliconnect' )."</b></button></form></div>
</div></div></div>";
}}

echo "<small><div class='float-left'>";
echo dolirefresh($request, $url, dolidelay('member'));
echo "</div><div class='float-right'>";
echo dolihelp('COM');
echo "</div></small>";

}
//*****************************************************************************************

if ( is_object($help) && $help->value == '1' ) {
add_action( 'settings_doliconnect_menu', 'tickets_menu', 1, 1);
add_action( 'settings_doliconnect_tickets', 'tickets_module');
}

function tickets_menu( $arg ) {
echo "<a href='".esc_url( add_query_arg( 'module', 'tickets', doliconnecturl('doliaccount')) )."' class='list-group-item list-group-item-action";
if ( $arg == 'tickets' ) { echo " active"; }
echo "'>".__( 'Help', 'doliconnect' )."</a>";
}

function tickets_module( $url ) {
global $current_user;

$request = "/tickets/".esc_attr($_GET['id']);

if ( isset($_GET['id']) && $_GET['id'] > 0 ) {
$ticketfo = callDoliApi("GET", $request, null, dolidelay('ticket', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//echo $ticket;
}

if ( isset($_GET['id']) && isset($_GET['ref']) && ( doliconnector($current_user, 'fk_soc') == $ticketfo->socid ) && ($_GET['ref'] == $ticketfo->ref ) ) {

if ( isset($_POST["case"]) && $_POST["case"] == 'messageticket' ) {
$rdr = [
    'message' => sanitize_textarea_field($_POST['ticket_newmessage']),
	];                  
$ticketid = callDoliApi("POST", "tickets/newmessage", $rdr, dolidelay('ticket', true));
//echo $ticketid;

if ( $ticketid > 0 ) {
$msg = "<div class='alert alert-success' role='alert'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button><p><strong>".__( 'Congratulations!', 'doliconnect' )."</strong> ".__( 'Your message has been send.', 'doliconnect' )."</p></div>"; 
} }

echo "<div class='card shadow-sm'><div class='card-body'><h5 class='card-title'>".$ticketfo->ref."</h5><div class='row'><div class='col-md-6'>";
$dateticket =  date_i18n('d/m/Y', $ticketfo->datec);
echo "<b>".__( 'Date of creation', 'doliconnect' ).": </b> $dateticket<br>";
echo "<b>".__( 'Type and category', 'doliconnect' ).": </b> ".__($ticketfo->type_label, 'doliconnect' ).", ".__($ticketfo->category_label, 'doliconnect' )."<br>";
echo "<b>".__( 'Severity', 'doliconnect' ).": </b> ".__($ticketfo->severity_label, 'doliconnect' )."<br>";
echo "</div><div class='col-md-6'><h3 class='text-right'>";
if ( $ticketfo->fk_statut == 9 ) { echo "<span class='label label-default'>".__( 'Deleted', 'doliconnect' )."</span>"; }
elseif ( $ticketfo->fk_statut == 8 ) { echo "<span class='label label-success'>".__( 'Closed', 'doliconnect' )."</span>"; }
elseif ( $ticketfo->fk_statut == 6 ) { echo "<span class='label label-warning'>".__( 'Waiting', 'doliconnect' )."</span>"; }
elseif ( $ticketfo->fk_statut == 5 ) { echo "<span class='label label-warning'>".__( 'In progress', 'doliconnect' )."</span>"; }
elseif ( $ticketfo->fk_statut == 4 ) { echo "<span class='label label-warning'>".__( 'Assigned', 'doliconnect' )."</span>"; }
elseif ( $ticketfo->fk_statut == 3 ) { echo "<span class='label label-warning'>".__( 'Answered', 'doliconnect' )."</span>"; }
elseif ( $ticketfo->fk_statut == 1 ) { echo "<span class='label label-warning'>".__( 'Read', 'doliconnect' )."</span>"; }
elseif ( $ticketfo->fk_statut == 0 ) { echo "<span class='label label-danger'>".__( 'Unread', 'doliconnect' )."</span>"; }
echo "</h3></div></div>";
echo '<BR/><div class="progress"><div class="progress-bar bg-success" role="progressbar" style="width: '.$ticketfo->progress.'%" aria-valuenow="'.$ticketfo->progress.'" aria-valuemin="0" aria-valuemax="100"></div></div>';
echo "</div><ul class='list-group list-group-flush'>
<li class='list-group-item'><h5 class='mb-1'>".__( 'Subject', 'doliconnect' ).": ".$ticketfo->subject."</h5>
<p class='mb-1'>".__( 'Initial message', 'doliconnect' ).": ".$ticketfo->message."</p></li>";

if ( $ticketfo->fk_statut < '8' && $ticketfo->fk_statut > '0' && !empty(get_option('doliconnectbeta')) ) {
echo "<li class='list-group-item'>";

echo '<form id="message-ticket-form" action="'.$url.'&id='.$ticketfo->id.'&ref='.$ticketfo->ref.'" method="post">';

if ( isset($msg) ) { echo $msg; }

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
echo "</script>";

echo '<div class="form-group"><label for="ticketnewmessage"><small>'.__( 'Response', 'doliconnect' ).'</small></label>
<div class="input-group mb-2"><div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-comment fa-fw"></i></span></div><textarea class="form-control" name="ticket_newmessage" id="ticket_newmessage" rows="5"></textarea>
</div></div><input type="hidden" name="case" value="messageticket"><button class="btn btn-danger btn-block" type="submit"><b>'.__( 'Send', 'doliconnect' ).'</b></button></form>';
echo "</li>";

}

if ( isset($ticketfo->messages) ) {
foreach ( $ticketfo->messages as $msg ) {
$datemsg =  date_i18n('d/m/Y - H:i', $msg->datec);  
echo  "<li class='list-group-item'><b>$datemsg $msg->fk_user_action_string</b><br>$msg->message</li>";
}} 
echo "</ul></div>";

echo "<small><div class='float-left'>"; 
echo dolirefresh($request, $url."&id=".esc_attr($_GET['id'])."&ref=".esc_attr($_GET['ref']), dolidelay('ticket'), $ticketfo);
echo "</div><div class='float-right'>";
echo dolihelp('COM');
echo "</div></small>";

} elseif ( isset($_GET['create']) ) {

if ( isset($_POST["case"]) && $_POST["case"] == 'createticket' ) {
$rdr = [
    'fk_soc' => doliconnector($current_user, 'fk_soc'),
    'type_code' => $_POST['ticket_type'],
    'category_code' => $_POST['ticket_category'],
    'severity_code' => $_POST['ticket_severity'],
    'subject' => sanitize_text_field($_POST['ticket_subject']),
    'message' => sanitize_textarea_field($_POST['ticket_message']),
	];                  
$ticketid = callDoliApi("POST", "/tickets", $rdr, dolidelay('ticket', true));
//echo $ticketid;

if ( $ticketid > 0 ) {
$msg = "<div class='alert alert-success' role='alert'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button><p><strong>".__( 'Congratulations!', 'doliconnect' )."</strong> ".__( 'Your ticket has been submitted.', 'doliconnect' )."</p></div>"; 
} }

echo "<form class='was-validated' id='ticket-form' action='".$url."&create' method='post'>";

if ( isset($msg) ) { echo $msg; }

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
echo "</script>";

echo "<div class='card shadow-sm'><ul class='list-group list-group-flush'><li class='list-group-item'><h5 class='card-title'>".__( 'Open a new ticket', 'doliconnect' )."</h5>";
echo "<div class='form-group'><label for='inputcivility'><small>".__( 'Type and category', 'doliconnect' )."</small></label>
<div class='input-group mb-2'><div class='input-group-prepend'><span class='input-group-text' id='identity'><i class='fas fa-info-circle fa-fw'></i></span></div>";

$type = callDoliApi("GET", "/setup/dictionary/ticket_types?sortfield=pos&sortorder=ASC&limit=100", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//echo $type;

if ( isset($type) ) { 
$tp= __( 'Issue or problem', 'doliconnect' ).__( 'Commercial question', 'doliconnect' ).__( 'Change or enhancement request', 'doliconnect' ).__( 'Project', 'doliconnect' ).__( 'Other', 'doliconnect' );
echo "<select class='custom-select' id='ticket_type'  name='ticket_type'>";
echo "<option value='' disabled selected >".__( '- Select -', 'doliconnect' )."</option>";
foreach ($type as $postv) {
echo "<option value='".$postv->code."' ";
if ( isset($_GET['type']) && $_GET['type'] == $postv->code ) {
echo "selected ";
} elseif ( $postv->use_default == 1 ) {
echo "selected ";}
echo ">".__($postv->label, 'doliconnect' )."</option>";
}
echo "</select>";
}

$cat = callDoliApi("GET", "/setup/dictionary/ticket_categories?sortfield=pos&sortorder=ASC&limit=100", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if ( isset($cat) ) { 
echo "<select class='custom-select' id='ticket_cat'  name='ticket_category'>";
echo "<option value='' disabled selected >".__( '- Select -', 'doliconnect' )."</option>";
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

$severity = callDoliApi("GET", "/setup/dictionary/ticket_severities?sortfield=pos&sortorder=ASC&limit=100", null, dolidelay('constante', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));

if ( isset($severity) ) { 
$sv= __( 'Critical / blocking', 'doliconnect' ).__( 'High', 'doliconnect' ).__( 'Normal', 'doliconnect' ).__( 'Low', 'doliconnect' );
echo "<select class='custom-select' id='ticket_severity'  name='ticket_severity'>";
echo "<option value='' disabled selected >".__( '- Select -', 'doliconnect' )."</option>";
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

} else {

$request = "/tickets?socid=".doliconnector($current_user, 'fk_soc')."&sortfield=s.rowid&sortorder=DESC&limit=10";

$listticket = callDoliApi("GET", $request, null, dolidelay('ticket', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
//echo $listticket;

echo '<div class="card shadow-sm"><ul class="list-group list-group-flush">';
//if ($help>0) {
echo '<a href="'.$url.'&create" class="list-group-item lh-condensed list-group-item-action list-group-item-primary"><center><i class="fas fa-plus-circle"></i> '.__( 'New ticket', 'doliconnect' ).'</center></a>';  
//}
if ( !isset($listticket->error) && $listticket != null ) {
foreach ($listticket as $postticket) {                                                                                 

$arr_params = array( 'id' => $postticket->id, 'ref' => $postticket->ref);  
$return = esc_url( add_query_arg( $arr_params, $url) );

if ( $postticket->severity_code == 'BLOCKING' ) { $color="text-danger"; } 
elseif ( $postticket->severity_code == 'HIGH' ) { $color="text-warning"; }
elseif ( $postticket->severity_code == 'NORMAL' ) { $color="text-success"; }
elseif ( $postticket->severity_code == 'LOW' ) { $color="text-info"; } else { $color="text-dark"; }
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
echo dolirefresh($request, $url, dolidelay('ticket'));
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
global $wpdb, $current_user;

$ID = $current_user->ID;

if ( isset($_POST["case"]) && $_POST["case"] == 'updatesettings' ) {
update_user_meta( $ID, 'loginmailalert', sanitize_text_field($_POST['loginmailalert']) );
update_user_meta( $ID, 'optin1', sanitize_text_field($_POST['optin1']) );
update_user_meta( $ID, 'optin2', sanitize_text_field($_POST['optin2']) );
if ( isset($_POST['locale']) ) { update_user_meta( $ID, 'locale', sanitize_text_field($_POST['locale']) ); }  
//if (isset($_POST['multicurrency_code'])) {vupdate_user_meta( $ID, 'multicurrency_code', sanitize_text_field($_POST['multicurrency_code']) );v}

if ( doliconnector($current_user, 'fk_soc') > 0 ) {
$info = [
    'default_lang'  => sanitize_text_field($_POST['locale']),
    'multicurrency_code'  => sanitize_text_field($_POST['multicurrency_code']),
	];
$thirparty = callDoliApi("PUT", "/thirdparties/".doliconnector($current_user, 'fk_soc'), $info, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
}
}

echo "<form id='settings-form' action='".$url."' method='post'>";

if ( isset($msg) ) { echo $msg; }

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
echo "</script>";

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

if ( doliconnector($current_user, 'fk_soc') > 0 ) {
$thirdparty = callDoliApi("GET", "/thirdparties/".doliconnector($current_user, 'fk_soc'), null, dolidelay('thirdparty', esc_attr(isset($_GET["refresh"]) ? $_GET["refresh"] : null)));
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
echo "</li>";
}

echo "<input type='hidden' name='case' value='updatesettings'></ul></div>";
echo "<p class='text-right'><small>";
echo dolihelp('ISSUE');
echo "</small></p>";
echo "</form>";

if ( !empty(get_option('doliconnectbeta')) ) { 

echo '<style>';
?>
.blur{
  -webkit-filter: blur(5px);
  -moz-filter: blur(5px);
  -o-filter: blur(5px);
  -ms-filter: blur(5px);
  filter: blur(5px);
}
<?php
echo '</style>';


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